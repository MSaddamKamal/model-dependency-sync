<?php

namespace MSaddamKamal\ModelDependencySync;

class BaseModelDependencyHandler
{
    /**
     * Handle when models are updated.
     *
     * @param array $models The array of models to handle
     * @param bool $isRecursiveCall Flag indicating if the call is recursive
     * @return void
     */
    public function handleModelUpdated(array $models, bool $isRecursiveCall = false): void
    {
        foreach ($models as $model) {
            // Load the dynamic model dependencies configuration
            $customConfigPath = app_path('model_dependencies.php');
            $dependencies = [];
            if (file_exists($customConfigPath)) {
                $dependencies = require $customConfigPath;
            }
            $modelClass = get_class($model);

            if (isset($dependencies[$modelClass])) {
                $config = $dependencies[$modelClass];
                $listenColumns = $config['listen'] ?? [];

                $modelChanges = $model->getChanges();
                foreach ($listenColumns as $column) {
                    if (isset($modelChanges[$column]) || $isRecursiveCall) {
                        $this->applyChanges($model, $config['affect'], $column);
                        break;
                    }
                }
            }
        }
    }

    /**
     * Apply changes to the model based on affected models and actions.
     *
     * @param mixed $model The model to apply changes to
     * @param array $affects Array of affected models and actions
     * @param string $listenColumn The column to listen for changes
     * @return void
     * @throws \Exception
     */
    protected function applyChanges($model, array $affects, string $listenColumn): void
    {
        foreach ($affects as $affectedModelClass => $affectedDetails) {
            $relationName = $affectedDetails['relation'] ?? false;
            $actions = $affectedDetails['actions.' . $listenColumn] ?? false;

            if ($actions) {
                if ($relationName) {
                    $relatedModels = $model->$relationName()->get();
                } else {
                    // This assumes the relation is identifiable by a foreign key convention (e.g., 'user_id')
                    // else defined in the affectedDetails array
                    $foreignKey = $affectedDetails['foreignKey'] ?? strtolower(class_basename($model)) . '_id';
                    $relatedModels = $affectedModelClass::where($foreignKey, $model->id)->get();
                }

                foreach ($actions as $action => $params) {
                    if ($action === 'update') {
                        $updateData = [];
                        foreach ($params as $field => $value) {
                            // Check if the value is a callable function
                            if (is_callable($value)) {
                                // Call the function with source and target model
                                $updateData[$field] = $value($model, $relatedModels);
                            } else {
                                $updateData[$field] = $value;
                            }
                        }
                        if ($relationName) {
                            $model->$relationName()->update($updateData);
                        } else {
                            $affectedModelClass::where($foreignKey, $model->id)->update($updateData);
                        }

                    } elseif ($action === 'delete') {
                        // TODO: Implement delete
                    }
                    // Optionally, handle recursive updates
                }
            }
        }
    }
}
