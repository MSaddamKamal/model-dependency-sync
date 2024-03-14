<?php

namespace MSaddamKamal\ModelDependencySync;

class BaseModelDependencyHandler
{
    public function handleModelUpdated($models, $isRecursiveCall = false): void
    {
        foreach ($models as $model) {
            $dependencies = config('model_dependencies');
            
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

    protected function applyChanges($model, $affects, $listenColumn): void
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
