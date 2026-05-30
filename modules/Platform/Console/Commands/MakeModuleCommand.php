<?php

namespace Modules\Platform\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * Runs any Laravel make:* generator and places the result in a module.
 *
 * Laravel's generators always write under app_path()/database_path() with the
 * App\ namespace. To keep the project's app/ directory untouched, this command
 * temporarily repoints app_path()/database_path() at a throwaway system temp
 * directory, runs the native generator there (forwarding supported options,
 * including cascades like --migration/--factory), then moves every generated
 * file into modules/{Module}/ and rewrites its namespace. Diffing the temp dir
 * means it covers every current and future make:* generator with no per-type
 * subclass.
 *
 *   php artisan make:module User model Product --migration --factory
 *   php artisan make:module User controller ProductController --resource
 *   php artisan make:module User migration create_products_table
 *   php artisan make:module User action RegisterUser   (custom, no native make)
 */
#[AsCommand(name: 'make:module')]
class MakeModuleCommand extends Command
{
    protected $signature = 'make:module
        {module : The target module (e.g. User)}
        {type : The generator type (model, controller, migration, action, ...)}
        {name? : The name of the generated class/file}
        {--model= : The model the file relates to}
        {--event= : The event a listener handles}
        {--resource : Generate a resource controller}
        {--api : Generate an API resource controller}
        {--invokable : Generate a single-action controller}
        {--requests : Generate form requests for a controller}
        {--migration : Also create a migration (model)}
        {--factory : Also create a factory (model)}
        {--seed : Also create a seeder (model)}
        {--controller : Also create a controller (model)}
        {--policy : Also create a policy (model)}
        {--all : Generate all related classes (model)}
        {--pivot : Generate a pivot model}
        {--queued : Generate a queued listener/notification}
        {--force : Overwrite existing files}';

    protected $description = 'Run a Laravel make:* generator and place the result in a module';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $module = Str::studly((string) $this->argument('module'));
        $type = (string) $this->argument('type');

        if (! $this->files->isDirectory(base_path("modules/{$module}"))) {
            $this->components->error("Module [{$module}] does not exist at modules/{$module}.");

            return self::FAILURE;
        }

        // "action" has no native generator — write it from our stub.
        if ($type === 'action') {
            return $this->makeAction($module);
        }

        $generator = $this->generatorFor($type);

        try {
            $forward = $this->forwardedInput($generator);
        } catch (CommandNotFoundException) {
            $this->components->error("Unknown generator: make:{$type}");

            return self::FAILURE;
        }

        ['status' => $status, 'sandbox' => $sandbox, 'created' => $created] =
            $this->generateInSandbox($generator, $forward);

        if ($status !== self::SUCCESS) {
            return $status;
        }

        if ($created === []) {
            $this->components->warn('Nothing was generated to relocate.');

            return self::SUCCESS;
        }

        foreach ($created as $path) {
            $this->relocate($path, $module, $sandbox);
        }

        $this->files->deleteDirectory($sandbox);

        return self::SUCCESS;
    }

    /**
     * Run the native generator with app/database paths pointed at a temp dir,
     * so nothing is ever written under the project's app/ or database/.
     *
     * @return array{status: int, sandbox: string, created: list<string>}
     */
    private function generateInSandbox(string $generator, array $forward): array
    {
        $sandbox = sys_get_temp_dir().DIRECTORY_SEPARATOR.'make-module-'.getmypid();

        $this->files->deleteDirectory($sandbox);

        foreach (['app/Models', 'database/migrations', 'database/factories', 'database/seeders'] as $dir) {
            $this->files->ensureDirectoryExists($sandbox.DIRECTORY_SEPARATOR.$dir);
        }

        /** @var Application $app */
        $app = $this->laravel;

        $originalApp = $app->path();
        $originalDatabase = $app->databasePath();

        $app->getNamespace();

        try {
            $app->useAppPath($sandbox.DIRECTORY_SEPARATOR.'app');
            $app->useDatabasePath($sandbox.DIRECTORY_SEPARATOR.'database');

            $before = $this->snapshot($sandbox);

            $status = $this->call($generator, $forward);

            $created = $status === self::SUCCESS
                ? array_values(array_diff($this->snapshot($sandbox), $before))
                : [];
        } finally {
            $app->useAppPath($originalApp);
            $app->useDatabasePath($originalDatabase);
        }

        // On failure or no-op, nothing will be relocated — drop the sandbox now.
        if ($created === []) {
            $this->files->deleteDirectory($sandbox);
        }

        return ['status' => $status, 'sandbox' => $sandbox, 'created' => $created];
    }

    /**
     * Model-aware generators are routed to internal module-aware variants whose
     * interactive picker lists module models and whose --model accepts module
     * FQCNs. Everything else runs the native make:* generator.
     */
    private function generatorFor(string $type): string
    {
        return [
            'controller' => 'make:module-controller',
            'policy' => 'make:module-policy',
            'observer' => 'make:module-observer',
        ][$type] ?? 'make:'.$type;
    }

    /**
     * Build the input for the generator, forwarding only the options it actually
     * defines (so e.g. --model isn't passed to a command without it).
     *
     * @return array<string, mixed>
     */
    private function forwardedInput(string $generator): array
    {
        $definition = $this->getApplication()->find($generator)->getDefinition();

        $candidates = [
            'name' => $this->argument('name'),
            '--model' => $this->option('model'),
            '--event' => $this->option('event'),
            '--resource' => $this->option('resource'),
            '--api' => $this->option('api'),
            '--invokable' => $this->option('invokable'),
            '--requests' => $this->option('requests'),
            '--migration' => $this->option('migration'),
            '--factory' => $this->option('factory'),
            '--seed' => $this->option('seed'),
            '--controller' => $this->option('controller'),
            '--policy' => $this->option('policy'),
            '--all' => $this->option('all'),
            '--pivot' => $this->option('pivot'),
            '--queued' => $this->option('queued'),
            '--force' => $this->option('force'),
        ];

        $input = [];

        foreach ($candidates as $key => $value) {
            if ($value === null || $value === false || $value === '') {
                continue;
            }

            $isOption = str_starts_with($key, '--');

            if ($isOption ? $definition->hasOption(substr($key, 2)) : $definition->hasArgument($key)) {
                $input[$key] = $value;
            }
        }

        return $input;
    }

    /** @return list<string> */
    private function snapshot(string $base): array
    {
        if (! $this->files->isDirectory($base)) {
            return [];
        }

        $found = [];

        foreach ($this->files->allFiles($base) as $file) {
            $found[] = $file->getPathname();
        }

        return $found;
    }

    private function relocate(string $path, string $module, string $sandbox): void
    {
        $rel = Str::after(str_replace('\\', '/', $path), str_replace('\\', '/', $sandbox).'/');

        $dest = match (true) {
            str_starts_with($rel, 'app/') => "modules/{$module}/".Str::after($rel, 'app/'),
            str_starts_with($rel, 'database/migrations/') => "modules/{$module}/Database/Migrations/".basename($rel),
            str_starts_with($rel, 'database/factories/') => "modules/{$module}/Database/Factories/".Str::after($rel, 'database/factories/'),
            str_starts_with($rel, 'database/seeders/') => "modules/{$module}/Database/Seeders/".Str::after($rel, 'database/seeders/'),
            default => null,
        };

        if ($dest === null) {
            $this->components->warn("Skipped (no module mapping): {$rel}");

            return;
        }

        $this->files->ensureDirectoryExists(dirname(base_path($dest)));
        $this->files->put(base_path($dest), $this->rewriteNamespaces($this->files->get($path), $module));

        $this->components->twoColumnDetail(class_basename($dest), $dest);
    }

    private function rewriteNamespaces(string $code, string $module): string
    {
        return strtr($code, [
            'namespace App\\' => "namespace Modules\\{$module}\\",
            'namespace App;' => "namespace Modules\\{$module};",
            'namespace Database\\Factories' => "namespace Modules\\{$module}\\Database\\Factories",
            'namespace Database\\Seeders' => "namespace Modules\\{$module}\\Database\\Seeders",
            'App\\Http\\Controllers\\Controller' => 'Modules\\Shared\\Http\\Controllers\\Controller',
            'App\\Models\\' => "Modules\\{$module}\\Models\\",
        ]);
    }

    private function makeAction(string $module): int
    {
        $name = (string) $this->argument('name');

        if ($name === '') {
            $this->components->error('The "name" argument is required for an action.');

            return self::FAILURE;
        }

        $relative = str_replace('\\', '/', $name);
        $class = Str::studly(class_basename($relative));
        $subPath = trim(Str::beforeLast($relative, '/'), '/');
        $subPath = $subPath === $relative ? '' : $subPath;

        $namespace = 'Modules\\'.$module.'\\Actions'.($subPath !== '' ? '\\'.str_replace('/', '\\', $subPath) : '');
        $dest = base_path("modules/{$module}/Actions/".($subPath !== '' ? $subPath.'/' : '').$class.'.php');

        if ($this->files->exists($dest) && ! $this->option('force')) {
            $this->components->error("Action already exists: {$class}");

            return self::FAILURE;
        }

        $stub = strtr($this->files->get(__DIR__.'/../stubs/action.stub'), [
            '{{ namespace }}' => $namespace,
            '{{ class }}' => $class,
        ]);

        $this->files->ensureDirectoryExists(dirname($dest));
        $this->files->put($dest, $stub);

        $this->components->info("Action [modules/{$module}/Actions/".($subPath !== '' ? $subPath.'/' : '')."{$class}.php] created successfully.");

        return self::SUCCESS;
    }
}
