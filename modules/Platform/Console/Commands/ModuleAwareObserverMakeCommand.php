<?php

namespace Modules\Platform\Console\Commands;

use Illuminate\Foundation\Console\ObserverMakeCommand;
use Modules\Platform\Console\Concerns\ScansModuleModels;

/**
 * Internal helper used by make:module: an observer generator whose interactive
 * model picker lists module models and whose --model accepts module FQCNs. It
 * still writes to app/; make:module relocates the result into the module.
 */
class ModuleAwareObserverMakeCommand extends ObserverMakeCommand
{
    use ScansModuleModels;

    protected $name = 'make:module-observer';

    protected $hidden = true;

    protected $description = 'Internal: module-aware observer generator (used by make:module)';
}
