<?php

namespace Modules\Platform\Console\Commands;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Modules\Platform\Console\Concerns\ScansModuleModels;

/**
 * Internal helper used by make:module: a controller generator whose interactive
 * model picker lists module models and whose --model accepts module FQCNs. It
 * never prompts to create a missing model (in a modular app models are created
 * explicitly via `make:module <module> model`). make:module runs it inside a
 * temp sandbox and relocates the result into the module.
 */
class ModuleAwareControllerMakeCommand extends ControllerMakeCommand
{
    use ScansModuleModels;

    protected $name = 'make:module-controller';

    protected $hidden = true;

    protected $description = 'Internal: module-aware controller generator (used by make:module)';

    /**
     * Same as the parent but without the "model does not exist, generate it?"
     * prompt/auto-creation.
     *
     * @param  array<string, string>  $replace
     * @return array<string, string>
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->option('model'));

        $replace = $this->buildFormRequestReplacements($replace, $modelClass);

        return array_merge($replace, [
            'DummyFullModelClass' => $modelClass,
            '{{ namespacedModel }}' => $modelClass,
            '{{namespacedModel}}' => $modelClass,
            'DummyModelClass' => class_basename($modelClass),
            '{{ model }}' => class_basename($modelClass),
            '{{model}}' => class_basename($modelClass),
            'DummyModelVariable' => lcfirst(class_basename($modelClass)),
            '{{ modelVariable }}' => lcfirst(class_basename($modelClass)),
            '{{modelVariable}}' => lcfirst(class_basename($modelClass)),
        ]);
    }

    /**
     * Same as the parent but without the parent-model auto-creation prompt.
     *
     * @return array<string, string>
     */
    protected function buildParentReplacements()
    {
        $parentModelClass = $this->parseModel($this->option('parent'));

        return [
            'ParentDummyFullModelClass' => $parentModelClass,
            '{{ namespacedParentModel }}' => $parentModelClass,
            '{{namespacedParentModel}}' => $parentModelClass,
            'ParentDummyModelClass' => class_basename($parentModelClass),
            '{{ parentModel }}' => class_basename($parentModelClass),
            '{{parentModel}}' => class_basename($parentModelClass),
            'ParentDummyModelVariable' => lcfirst(class_basename($parentModelClass)),
            '{{ parentModelVariable }}' => lcfirst(class_basename($parentModelClass)),
            '{{parentModelVariable}}' => lcfirst(class_basename($parentModelClass)),
        ];
    }
}
