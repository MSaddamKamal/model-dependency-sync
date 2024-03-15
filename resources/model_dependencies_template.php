<?php

// Define how to handle your model dependencies here.
return [
    // Example usage:
    //    \App\Models\Example::class => [
    //        'listen' => ['test_id'], // Assuming we want to act on changes to the Example's test_id column
    //        'affect' => [
    //            \App\Models\ExampleAssociation::class => [
    //                'relation' => 'example_assoicate', // Direct or inverse relation,
    //                'actions.test_id' => [
    //                    'update' => [
    //                        'test_id' => function ($model, $relatedModel) {
    //                           // source and target model
    //                            return $model->test_id;
    //                        },
    //                    ],
    //                ],
    //            ],
    //        ],
    //    ],
];
