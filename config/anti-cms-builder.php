<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Models
    |--------------------------------------------------------------------------
    |
    | Configure the default models used by the package. You can override
    | these in your application if you have custom models.
    |
    */
    'models' => [
        'file' => 'App\\Models\\File',
        'media' => 'App\\Models\\Media',
        'translation' => 'App\\Models\\Translations\\Translation',
    ],

    /*
    |--------------------------------------------------------------------------
    | Services
    |--------------------------------------------------------------------------
    |
    | Configure the services used by the package. These can be overridden
    | in your application if you have custom implementations.
    |
    */
    'services' => [
        'post' => 'App\\Services\\PostService',
        'template' => 'App\\Services\\TemplateService',
        'custom_field' => 'App\\Services\\CustomFieldService',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Language
    |--------------------------------------------------------------------------
    |
    | The default language for multilingual fields.
    |
    */
    'default_language' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Available Languages
    |--------------------------------------------------------------------------
    |
    | List of available languages for multilingual fields.
    |
    */
    'languages' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads.
    |
    */
    'uploads' => [
        'disk' => 'public',
        'path' => 'uploads',
    ],
];
