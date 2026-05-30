<?php

namespace Modules\Platform\Console\Commands;

use Illuminate\Foundation\Console\PolicyMakeCommand;
use Modules\Platform\Console\Concerns\ScansModuleModels;

/**
 * Internal helper used by make:module: a policy generator whose interactive
 * model picker lists module models and whose --model accepts module FQCNs. It
 * still writes to app/; make:module relocates the result into the module.
 */
class ModuleAwarePolicyMakeCommand extends PolicyMakeCommand
{
    use ScansModuleModels;

    protected $name = 'make:module-policy';

    protected $hidden = true;

    protected $description = 'Internal: module-aware policy generator (used by make:module)';
}
