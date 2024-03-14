# Model Dependency Sync for Laravel

A Laravel package designed to sync model dependencies and provide insights on updates, making it easier to manage complex data relationships within your Laravel applications.

## Features

- Automatically sync related model data on updates.
- Provides insights into how model updates affect related data.
- Easy configuration and customization through a published config file.

## Requirements

- PHP version 7.4 or higher.
- Laravel 8.0 or higher.


## Installation

You can install the package via Composer:

```bash
composer require msaddamkamal/model-dependency-sync
```

This package uses Laravel's auto-discovery feature, so the service provider and facade will automatically be registered.

## Publishing the Configuration
To publish the package configuration file to your application's config directory, run:
```bash
php artisan vendor:publish --tag=model-dependency-sync-config
```

## Configuration
After publishing, the configuration file can be found at `config/model_dependencies.php`. Here, you can define how your models are related and what actions should be taken on updates.

```bash
return [
    // Example Model Class
    \App\Models\ExampleModel::class => [
        'listen' => ['field_to_listen_for_changes'],
        'affect' => [
            // Related Model Class
            \App\Models\RelatedModel::class => [
                'relation' => 'relationMethodOnExampleModel',
                'actions.field_to_listen_for_changes' => [
                    'update' => [
                        'related_model_field' => function ($exampleModelInstance, $relatedModelInstance) {
                            // Logic to handle the update
                            return $newFieldValue;
                        },
                    ],
                ],
            ],
        ],
    ],
];
```

## Extending Functionality with a Custom Handler

If the default handling mechanism doesn't fully meet your requirements, you can extend the package's functionality by specifying a custom handler class in the configuration file. 
Your custom handler class should extend the package's base handler class `(MSaddamKamal\ModelDependencySync\BaseModelDependencyHandler)` and override necessary methods to implement your custom logic.

To use your custom handler, define it in the `config/model_dependencies.php` file like so:
```bash
return [
    'handler' => \App\Handlers\MyCustomModelDependencyHandler::class,
    // Other configuration...
];

```

Here's an example of what a custom handler might look like:

```bash
namespace App\Handlers;

use MSaddamKamal\ModelDependencySync\BaseModelDependencyHandler;

class MyCustomModelDependencyHandler extends BaseModelDependencyHandler
{
    public function handleModelUpdated($model)
    {
        // Your custom logic here
    }
}

```

By extending and defining a custom handler, you gain full control over how model updates are handled and can tailor the behavior to fit the specific needs of your application.

## License
The Model Dependency Sync for Laravel is open-sourced software licensed under the MIT license.