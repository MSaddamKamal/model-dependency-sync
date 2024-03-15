<?php

if (!function_exists('getModelUpdateEffects')) {
    /**
     * Generate the effects descriptions for updating a model field.
     *
     * @return array Array of effects descriptions for updating the model field.
     */
    function getModelUpdateEffects($modelClass): array
    {

        $modelSimpleName = basename(str_replace('\\', '/', $modelClass));
        $config = config('model_dependencies');
        $effectsDescriptions = [];

        if (!isset($config[$modelClass])) {
            return ["No configuration found for {$modelClass}."];
        }

        $dependencies = $config[$modelClass];
        $listenFields = $dependencies['listen'] ?? [];

        foreach ($listenFields as $field) {
            foreach ($dependencies['affect'] as $affectedClass => $details) {
                if (isset($details["actions.{$field}"])) {
                    $actionDetails = $details["actions.{$field}"];
                    foreach ($actionDetails as $action => $actionConfig) {
                        $affectedClassSimpleName = basename(str_replace('\\', '/', $affectedClass));
                        $effectsDescriptions[] = "Updating {$field} in {$modelSimpleName} will {$action} the {$affectedClassSimpleName}'s {$field}.";
                    }
                }
            }
        }

        return $effectsDescriptions;
    }
}
