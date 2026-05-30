<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\User\Database\Seeders\UserSeeder;

/**
 * Root seeder = composition root for seeding.
 *
 * It owns no domain data itself; it only orchestrates the per-module seeders.
 * Register a new module's seeder here.
 *
 * NOTE on the namespace: this file physically lives in the Platform module, but
 * deliberately keeps the `Database\Seeders` namespace (mapped to this path in
 * composer.json) because `php artisan db:seed` / `migrate --seed` hard-code
 * `Database\Seeders\DatabaseSeeder` as the default class. Keeping the namespace
 * lets those commands work with no `--class` flag.
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
        ]);
    }
}
