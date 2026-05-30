<?php

namespace Modules\Platform\Console\Concerns;

use Symfony\Component\Finder\Finder;

/**
 * Makes the interactive model picker of the make: generators look in each
 * module's Models directory instead of app/Models.
 *
 * Laravel's FindsAvailableModels trait scans app_path('Models') and returns
 * base names. Here we scan every module and return fully-qualified class names
 * (Modules\<Module>\Models\<Model>) so the picker is unambiguous across
 * modules, and let those qualified names pass straight through qualifyModel().
 */
trait ScansModuleModels
{
    /**
     * @return array<int, string>
     */
    protected function findAvailableModels()
    {
        $modulesPath = base_path('modules');

        if (! is_dir($modulesPath)) {
            return [];
        }

        $models = [];

        foreach (glob($modulesPath.'/*/Models', GLOB_ONLYDIR) ?: [] as $dir) {
            $module = basename(dirname($dir));

            foreach (Finder::create()->files()->depth(0)->name('*.php')->in($dir) as $file) {
                $models[] = 'Modules\\'.$module.'\\Models\\'.$file->getBasename('.php');
            }
        }

        sort($models);

        return $models;
    }

    protected function qualifyModel(string $model)
    {
        $model = ltrim(str_replace('/', '\\', $model), '\\');

        if (str_starts_with($model, 'Modules\\')) {
            return $model;
        }

        return parent::qualifyModel($model);
    }
}
