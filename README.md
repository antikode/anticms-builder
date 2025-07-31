# AntiCmsBuilder

[![CI](https://github.com/antikode/anti-cms-builder/workflows/CI/badge.svg)](https://github.com/antikode/anti-cms-builder/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)
[![License](https://img.shields.io/packagist/l/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)

A powerful Laravel package for building dynamic CRUD interfaces with minimal boilerplate code. This package provides form builders, table builders, and React components to accelerate development while ensuring consistency across your application.

## Features

- 🚀 **Rapid CRUD Development** - Build complete CRUD interfaces in minutes
- 📝 **Dynamic Form Builder** - Create complex forms with JSON configuration
- 📊 **Advanced Table Builder** - Sortable, searchable, filterable data tables
- 🌍 **Multilingual Support** - Built-in translation management
- ⚛️ **React Components** - Pre-built UI components for Inertia.js
- 🔧 **Extensible** - Easy to customize and extend
- 🎯 **Laravel 11 Ready** - Full compatibility with latest Laravel

## Table of Contents

1. [Installation](#installation)
2. [Quick Start](#quick-start)
3. [FormBuilder Documentation](#formbuilder-documentation)
4. [TableBuilder Usage](#tablebuilder-usage)
5. [React Components](#react-components)
6. [Advanced Features](#advanced-features)

## Installation

> **Note**: This package is currently in development and not yet published to Packagist. The installation method below is not effective yet.

### From Packagist (Not Available Yet)

```bash
composer require antikode/anti-cms-builder
```

### From Private Repository

Since this package is in a private repository, you need to add it to your `composer.json` manually:

1. Add the repository to your `composer.json`:

**For HTTPS (requires authentication token):**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/antikode/anti-cms-builder"
        }
    ]
}
```

**For SSH (recommended):**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:antikode/anti-cms-builder.git"
        }
    ]
}
```

2. **Authentication Setup:**

**For HTTPS method:**
- Create a GitHub Personal Access Token with repository access
- Configure Composer authentication:
```bash
composer config github-oauth.github.com YOUR_GITHUB_TOKEN
```

**For SSH method (recommended):**
- Ensure your SSH key is added to your GitHub account
- Test SSH connection: `ssh -T git@github.com`

3. Require the package:

```bash
composer require antikode/anti-cms-builder:dev-main
```

The package will automatically register its service provider through Laravel's package discovery.

### Publish Resources (Mandatory)

If you want to customize the React components, publish them to your resources directory:

```bash
php artisan vendor:publish --tag=anti-cms-builder-resources
```

This will publish the CRUD React components to `resources/js/vendor/anti-cms-builder/`.

## Requirements

- PHP 8.2+
- Laravel 10.0+ or 11.0+
- React + Inertia.js (for frontend components)

## Configuration

Optionally, you can publish the configuration file:

```bash
php artisan vendor:publish --tag=anti-cms-builder-config
```

This will create a `config/anti-cms-builder.php` file where you can customize:

- Default models for File, Media, and Translation
- Service implementations
- Default and available languages
- File upload settings

## Usage

### Basic CRUD Controller

Create a controller that uses the `UseCrudController` trait:

```php
<?php

namespace App\Http\Controllers;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Traits\UseCrudController;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\FieldTypes\SelectField;
use AntiCmsBuilder\Tables\Columns\TextColumn;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    use UseCrudController;

    protected string $model = Product::class;

    public function forms(FormBuilder $builder): FormBuilder
    {
        return $builder->forms([
            InputField::make()
                ->name('name')
                ->label('Product Name')
                ->placeholder('Enter product name')
                ->required()
                ->multilanguage()
                ->toArray(),

            SelectField::make()
                ->name('category_id')
                ->label('Category')
                ->placeholder('Select category')
                ->loadOptionFromRelation('category', 'name')
                ->toArray(),
        ]);
    }

    public function tables(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->query(fn (Builder $query) => $query->with('category'))
            ->columns([
                TextColumn::make()
                    ->name('name')
                    ->searchable()
                    ->sortable()
                    ->toArray(),

                TextColumn::make()
                    ->name('category.name')
                    ->label('Category')
                    ->toArray(),
            ]);
    }
}
```

### Register Routes

Use the `Route::crud()` macro to register CRUD routes:

```php
Route::crud('/products', ProductController::class, [
    'middleware' => ['auth'],
    'as' => 'admin.products'
]);
```

This generates all necessary CRUD routes:
- `GET /products` - Index page
- `GET /products/create` - Create form
- `POST /products` - Store new record
- `GET /products/details/{id}/edit` - Edit form
- `PUT /products/details/{id}/update` - Update record
- `DELETE /products/details/{id}/delete` - Delete record

### Field Types

The package includes various field types:

```php
// Input Field
InputField::make()
    ->name('title')
    ->label('Title')
    ->type('text') // text, email, number, password, etc.
    ->required()
    ->multilanguage()
    ->toArray(),

// Textarea Field
TextareaField::make()
    ->name('description')
    ->label('Description')
    ->rows(5)
    ->multilanguage()
    ->toArray(),

// Select Field
SelectField::make()
    ->name('category_id')
    ->label('Category')
    ->loadOptionFromRelation('category', 'name')
    ->required()
    ->toArray(),

// Image Field
ImageField::make()
    ->name('featured_image')
    ->label('Featured Image')
    ->required()
    ->toArray(),

// Toggle Field
ToggleField::make()
    ->name('is_active')
    ->label('Active Status')
    ->default(true)
    ->toArray(),

// Repeater Field
RepeaterField::make()
    ->name('specifications')
    ->label('Product Specifications')
    ->fields([
        InputField::make()->name('key')->label('Property')->toArray(),
        InputField::make()->name('value')->label('Value')->toArray(),
    ])
    ->toArray(),
```

### Multilingual Support

Enable multilingual support for any field:

```php
InputField::make()
    ->name('title')
    ->multilanguage() // This field will support multiple languages
    ->toArray(),
```

Data is automatically stored in the translations table with language keys.

### Custom Validation

Add custom validation rules:

```php
InputField::make()
    ->name('email')
    ->type('email')
    ->rules(['required', 'email', 'unique:users,email'])
    ->toArray(),
```

### Lifecycle Hooks

Customize save and update behavior:

```php
public function forms(FormBuilder $builder): FormBuilder
{
    return $builder
        ->forms([...])
        ->save(function (Request $request) {
            // Custom save logic
            $product = Product::create($request->validated());
            // Additional processing...
            return $product;
        })
        ->afterSave(function ($record, $operation, $request) {
            // Execute after save/update
            if ($operation === 'create') {
                // Send notification for new records
            }
        });
}
```

## Testing

Run the tests with:

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Antikode](https://github.com/antikode)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Overview

The AntiCmsBuilder package consists of three main components:

### 1. **FormBuilder**
Handles dynamic form creation, validation, and data persistence for Laravel models. It provides a fluent API for building complex forms with support for multilingual content, relationships, custom fields, media handling, and advanced validation.

### 2. **TableBuilder**
Enables the creation of queryable, filterable tables with customizable columns and sorting.

### 3. **UseCrudController Trait**
Provides reusable CRUD controller methods (`index`, `create`, `store`, `edit`, `update`, `destroy`) integrated with the builders.

## Quick Start

### 1. Controller Setup

Create a controller that uses the `UseCrudController` trait:

```php
<?php

namespace App\Http\Controllers;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Traits\UseCrudController;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\FieldTypes\SelectField;
use AntiCmsBuilder\Tables\Columns\TextColumn;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductController extends Controller
{
    use UseCrudController;

    protected string $model = Product::class;

    public function forms(FormBuilder $builder): FormBuilder
    {
        return $builder->forms([
            InputField::make()
                ->name('name')
                ->label('Product Name')
                ->placeholder('Enter product name')
                ->required()
                ->multilanguage()
                ->toArray(),

            SelectField::make()
                ->name('category_id')
                ->label('Category')
                ->placeholder('Select category')
                ->loadOptionFromRelation('category', 'name')
                ->toArray(),
        ]);
    }

    public function tables(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->query(fn (Builder $query) => $query->with('category'))
            ->columns([
                TextColumn::make()
                    ->name('name')
                    ->searchable()
                    ->sortable()
                    ->toArray(),

                TextColumn::make()
                    ->name('category.name')
                    ->label('Category')
                    ->toArray(),
            ]);
    }
}
```

### 2. Route Registration

Use the `Route::crud()` macro to register CRUD routes:

```php
Route::crud('/products', ProductController::class, [
    'middleware' => ['auth'],
    'as' => 'admin.products'
]);
```

## Route Details

The `Route::crud()` method generates RESTful routes:

| HTTP Method | URL Path                         | Controller Method | Route Name         |
|-------------|----------------------------------|-------------------|--------------------|
| GET         | /{url}/                          | index             | {$as}.index        |
| GET         | /{url}/create                    | create            | {$as}.create       |
| POST        | /{url}/                          | store             | {$as}.store        |
| GET         | /{url}/details/{id}/edit         | edit              | {$as}.edit         |
| PUT         | /{url}/details/{id}/update       | update            | {$as}.update       |
| DELETE      | /{url}/details/{id}/delete       | delete            | {$as}.delete       |
| DELETE      | /{url}/details/{id}/force-delete | forceDelete       | {$as}.delete.force |
| GET         | /{url}/details/{id}/restore      | restore           | {$as}.restore      |

## FormBuilder Documentation

### Core Methods

#### `make(string $model): self`

Creates a new FormBuilder instance for the specified model.

```php
$builder = FormBuilder::make(Product::class);
```

#### `forms(array|callable $forms): self`

Defines the form fields. Accepts either an array of field definitions or a callable that returns an array.

**Array Usage:**
```php
$builder->forms([
    InputField::make()->name('title')->toArray(),
    TextareaField::make()->name('description')->toArray(),
]);
```

**Callable Usage with Dependency Injection:**
```php
$builder->forms(function ($record, $operation) {
    $fields = [
        InputField::make()->name('title')->toArray(),
    ];
    
    if ($operation === 'update') {
        $fields[] = InputField::make()->name('slug')->disabled()->toArray();
    }
    
    return $fields;
});
```

**Supported Injectable Parameters:**
- `$record` (Model|null): The current model instance
- `$operation` (string): Either 'create' or 'update'

#### `save(callable $save): self`

Defines a custom save callback for create operations.

```php
$builder->save(function (Request $request) {
    $product = Product::create($request->validated());
    
    // Custom logic after creation
    $this->sendNotification($product);
    
    return $product;
});
```

#### `update(callable $update): self`

Defines a custom update callback for update operations.

```php
$builder->update(function (Request $request, Model $model) {
    $model->update($request->validated());
    
    // Custom logic after update
    $this->logUpdate($model);
    
    return $model;
});
```

#### `afterSave(callable $afterSave): self`

Defines a callback to execute after both save and update operations.

```php
$builder->afterSave(function ($record, $operation, $request) {
    if ($operation === 'create') {
        // Logic for new records
        $this->sendWelcomeEmail($record);
    } else {
        // Logic for updated records
        $this->logUpdate($record);
    }
});
```

**Supported Injectable Parameters:**
- `$record` (Model): The saved/updated model instance
- `$operation` (string): Either 'create' or 'update'
- `$request` (Request): The HTTP request instance

### Field Types and Configuration

#### Basic Field Structure

All fields follow this basic structure:

```php
[
    'field' => 'input',           // Field type
    'name' => 'field_name',       // Field name/key
    'label' => 'Field Label',     // Display label
    'attribute' => [              // Field attributes
        'placeholder' => 'Enter value',
        'required' => true,
        'max' => 255,
        // ... other attributes
    ],
    'multilanguage' => true,      // Enable multilingual support
]
```

#### Input Field
```php
InputField::make()
    ->name('title')
    ->label('Title')
    ->placeholder('Enter title')
    ->type('text')              // text, email, number, password, etc.
    ->required()
    ->max(255)
    ->min(3)
    ->multilanguage()
    ->toArray()
```

#### Textarea Field
```php
TextareaField::make()
    ->name('description')
    ->label('Description')
    ->placeholder('Enter description')
    ->rows(5)
    ->required()
    ->max(1000)
    ->multilanguage()
    ->toArray()
```

#### Select Field
```php
SelectField::make()
    ->name('category_id')
    ->label('Category')
    ->placeholder('Select category')
    ->loadOptionFromRelation('category', 'name')
    ->required()
    ->toArray()
```

#### Multi-Select Field
```php
SelectField::make()
    ->name('tags')
    ->label('Tags')
    ->multiple()
    ->loadOptionFromRelation('tags', 'name')
    ->toArray()
```

#### Image Field
```php
ImageField::make()
    ->name('featured_image')
    ->label('Featured Image')
    ->required()
    ->toArray()
```

#### Toggle/Switch Field
```php
ToggleField::make()
    ->name('is_active')
    ->label('Active Status')
    ->default(true)
    ->toArray()
```

#### Repeater Field
```php
RepeaterField::make()
    ->name('specifications')
    ->label('Product Specifications')
    ->fields([
        InputField::make()->name('key')->label('Property')->toArray(),
        InputField::make()->name('value')->label('Value')->toArray(),
    ])
    ->toArray()
```

### Relationship Handling

#### BelongsTo Relationships

```php
SelectField::make()
    ->name('category_id')
    ->label('Category')
    ->loadOptionFromRelation('category', 'name')
    ->toArray()
```

#### BelongsToMany Relationships

```php
SelectField::make()
    ->name('tags')
    ->label('Tags')
    ->multiple()
    ->loadOptionFromRelation('tags', 'name')
    ->toArray()
```

#### HasMany Relationships with Repeater

```php
RepeaterField::make()
    ->name('variants')
    ->label('Product Variants')
    ->relation('variants')  // HasMany relation
    ->fields([
        InputField::make()->name('name')->label('Variant Name')->multilanguage()->toArray(),
        InputField::make()->name('price')->label('Price')->type('number')->toArray(),
        ImageField::make()->name('image')->label('Variant Image')->toArray(),
    ])
    ->toArray()
```

#### Custom Relation Queries

```php
SelectField::make()
    ->name('category_id')
    ->label('Category')
    ->loadOptionFromRelation('category', 'name', function ($query) {
        return $query->where('is_active', true)->orderBy('name');
    })
    ->toArray()
```

### Multilingual Support

#### Enabling Multilingual Fields

```php
InputField::make()
    ->name('title')
    ->label('Title')
    ->multilanguage()  // Enable multilingual support
    ->toArray()
```

#### How Multilingual Data is Stored

Multilingual fields are automatically stored in the `translations` table with the following structure:

```php
// Database structure
translations: [
    'en' => ['title' => 'English Title', 'description' => 'English Description'],
    'ar' => ['title' => 'Arabic Title', 'description' => 'Arabic Description'],
]
```

### Validation

#### Automatic Validation

FormBuilder automatically generates validation rules based on field attributes:

```php
InputField::make()
    ->name('email')
    ->type('email')
    ->required()
    ->max(255)
    ->toArray()

// Generates: 'email' => ['required', 'email', 'max:255']
```

#### Custom Validation Rules

```php
InputField::make()
    ->name('username')
    ->rules(['required', 'unique:users,username', 'min:3'])
    ->toArray()
```

#### Multilingual Validation

```php
InputField::make()
    ->name('title')
    ->multilanguage()
    ->required()
    ->toArray()

// Generates validation for each language:
// 'translations.en.title' => ['required']
// 'translations.ar.title' => ['required']
```

### Complete Product Form Example

```php
public function forms(FormBuilder $builder): FormBuilder
{
    return $builder->forms([
        // Basic Information
        InputField::make()
            ->name('name')
            ->label('Product Name')
            ->placeholder('Enter product name')
            ->required()
            ->max(255)
            ->multilanguage()
            ->toArray(),
            
        TextareaField::make()
            ->name('description')
            ->label('Description')
            ->placeholder('Enter product description')
            ->rows(5)
            ->max(1000)
            ->multilanguage()
            ->toArray(),
            
        // Relationships
        SelectField::make()
            ->name('category_id')
            ->label('Category')
            ->placeholder('Select category')
            ->loadOptionFromRelation('category', 'name')
            ->required()
            ->toArray(),
            
        SelectField::make()
            ->name('tags')
            ->label('Tags')
            ->multiple()
            ->loadOptionFromRelation('tags', 'name')
            ->toArray(),
            
        // Media
        ImageField::make()
            ->name('featured_image')
            ->label('Featured Image')
            ->required()
            ->toArray(),
            
        // Pricing
        InputField::make()
            ->name('price')
            ->label('Price')
            ->type('number')
            ->step('0.01')
            ->min(0)
            ->required()
            ->toArray(),
            
        // Status
        ToggleField::make()
            ->name('is_active')
            ->label('Active')
            ->default(true)
            ->toArray(),
            
        // Specifications (Repeater)
        RepeaterField::make()
            ->name('specifications')
            ->label('Product Specifications')
            ->fields([
                InputField::make()
                    ->name('key')
                    ->label('Property')
                    ->required()
                    ->multilanguage()
                    ->toArray(),
                    
                InputField::make()
                    ->name('value')
                    ->label('Value')
                    ->required()
                    ->multilanguage()
                    ->toArray(),
            ])
            ->toArray(),
    ]);
}
```



## TableBuilder Usage

### Column Configuration

```php
public function tables(TableBuilder $builder): TableBuilder
{
    return $builder
        ->query(fn (Builder $query) => $query->with(['category', 'user']))
        ->columns([
            TextColumn::make()
                ->name('title')
                ->label('Title')
                ->searchable()
                ->sortable()
                ->description(fn ($record) => $record->slug)
                ->toArray(),

            TextColumn::make()
                ->name('category.name')
                ->label('Category')
                ->toArray(),

            TextColumn::make()
                ->name('created_at')
                ->label('Created Date')
                ->sortable()
                ->format(fn ($date) => $date->format('Y-m-d H:i'))
                ->toArray(),
        ])
        ->filters([
            SelectField::make()
                ->name('category_id')
                ->label('Filter by Category')
                ->loadOptionFromRelation('category', 'name')
                ->query(fn (Builder $query, $value) => 
                    $query->where('category_id', $value)
                )
                ->toArray(),
        ]);
}
```

### Column Features

- **searchable()**: Enables text search on the column
- **sortable()**: Allows column sorting
- **description(Closure)**: Adds a description callback
- **format(Closure)**: Custom formatting for display

## React Components

The package includes pre-built React components for CRUD operations:

### CRUD Pages
- **Index.jsx**: Data table with search, filter, and pagination
- **Create.jsx**: Form for creating new records
- **Edit.jsx**: Form for editing existing records
- **ActionBuilders.jsx**: Action buttons and dropdowns

### Form Components
- **CreateEditFormWithBuilder.jsx**: Advanced form component with multilingual support, tabs, and integrated field builder

These components are automatically used when you use the `UseCrudController` trait and provide a complete CRUD interface with:

- Multilingual content management
- Dynamic form building
- SEO settings integration
- Status and slug management
- Category and author assignment
- Responsive design with sticky headers

## Console Commands

### Page Builder Command

The package includes an interactive CLI command for building JSON page structures:

```bash
php artisan page:build
```

This command provides an interactive interface to:

- Create new JSON page templates
- Edit existing templates
- Add, edit, and sort components
- Preview JSON structure
- Save templates to `storage/app/json/pages/`

The command uses Laravel Prompts for a user-friendly CLI experience and integrates with the `ComponentManager` for component management.

### Conditional Fields

```php
$builder->forms(function ($record, $operation) {
    $fields = [
        InputField::make()->name('name')->toArray(),
    ];
    
    // Add slug field only for updates
    if ($operation === 'update') {
        $fields[] = InputField::make()
            ->name('slug')
            ->disabled()
            ->toArray();
    }
    
    // Add status field only for existing records
    if ($record && $record->exists) {
        $fields[] = SelectField::make()
            ->name('status')
            ->options([
                ['value' => 'draft', 'label' => 'Draft'],
                ['value' => 'published', 'label' => 'Published'],
            ])
            ->toArray();
    }
    
    return $fields;
});
```

### Lifecycle Hooks

#### Save Process Flow

1. **Before Save**: Custom validation, data preparation
2. **Save/Update**: Model creation/update with fillable fields
3. **Process Relations**: Handle relationships, media, translations
4. **After Save**: Execute afterSave callback

#### Custom Save Logic

```php
$builder->save(function (Request $request) {
    // Custom save logic
    $model = new Product();
    $model->name = $request->name;
    $model->slug = Str::slug($request->name);
    $model->save();
    
    return $model;
});
```

#### After Save Hooks

```php
$builder->afterSave(function ($record, $operation, $request) {
    // Send notifications
    if ($operation === 'create') {
        Mail::to($record->user)->send(new ProductCreated($record));
    }
    
    // Update search index
    $record->searchable();
    
    // Log activity
    activity()
        ->performedOn($record)
        ->log($operation === 'create' ? 'Product created' : 'Product updated');
});
```

### Media and File Handling

#### Image Fields

```php
ImageField::make()
    ->name('featured_image')
    ->label('Featured Image')
    ->required()
    ->toArray()
```

#### Image with Alt Text

The system automatically handles alt text for images:

```php
// Request data structure
[
    'featured_image' => 123,           // File ID
    'featured_image_alt' => 'Alt text' // Alt text (automatically handled)
]
```

#### Multiple Images in Repeater

```php
RepeaterField::make()
    ->name('gallery')
    ->label('Image Gallery')
    ->fields([
        ImageField::make()->name('image')->label('Image')->toArray(),
        InputField::make()->name('caption')->label('Caption')->multilanguage()->toArray(),
    ])
    ->toArray()
```

### Custom Fields

Models implementing `HasCustomField` contract can use template-based custom fields:

```php
// In your model
class Product extends Model implements HasCustomField
{
    use HasCustomFieldTrait;
}

// In FormBuilder
RepeaterField::make()
    ->name('custom_template')
    ->label('Custom Fields')
    ->template('product_specifications') // References a template
    ->toArray()
```

## Custom Field Types

Create custom field types by extending the base `FieldType` class:

```php
<?php

namespace AntiCmsBuilder\FieldTypes;

class CustomField extends FieldType
{
    protected string $type = 'custom';

    public function customMethod(): self
    {
        $this->attributes['custom_attribute'] = true;
        return $this;
    }
}
```

## Extending Controllers

You can override default behavior in your controllers:

```php
class ProductController extends Controller
{
    use UseCrudController;

    protected string $model = Product::class;

    // Override default validation
    protected function defaultValidation($data = null): array
    {
        return [
            'custom_field' => 'required|string|max:255',
        ];
    }

    // Add custom logic after saving
    public function afterSave($record, $request, $operation): void
    {
        // Custom logic here
        if ($operation === 'create') {
            // Do something after creating
        }
    }

    // Custom status options
    protected function statusOptions(): array
    {
        return [
            ['value' => 'draft', 'label' => 'Draft'],
            ['value' => 'published', 'label' => 'Published'],
        ];
    }
}
```

## Package Structure

```
packages/AntiCmsBuilder/
├── src/
│   ├── AntiCmsBuilderServiceProvider.php
│   ├── FieldService.php
│   ├── ComponentManager.php
│   ├── Console/
│   │   └── Commands/
│   │       └── PageBuilderCommand.php
│   ├── FieldTypes/
│   │   ├── FieldType.php
│   │   ├── InputField.php
│   │   ├── SelectField.php
│   │   └── ...
│   ├── Forms/
│   │   └── FormBuilder.php
│   ├── Tables/
│   │   ├── TableBuilder.php
│   │   └── Columns/
│   │       └── TextColumn.php
│   ├── Filters/
│   │   └── SelectField.php
│   └── Traits/
│       └── UseCrudController.php
├── resources/
│   └── js/
│       ├── Components/
│       │   └── form/
│       │       └── CreateEditFormWithBuilder.jsx
│       └── Pages/
│           └── CRUD/
│               ├── Index.jsx
│               ├── Create.jsx
│               ├── Edit.jsx
│               └── Actions/
│                   └── ActionBuilders.jsx
├── composer.json
└── README.md
```

## Best Practices

1. **Field Organization**: Group related fields logically
2. **Validation**: Use appropriate validation rules for each field type
3. **Multilingual**: Enable multilingual support for user-facing content
4. **Relationships**: Use proper relation methods for better performance
5. **Custom Logic**: Use lifecycle hooks for complex business logic
6. **Error Handling**: Implement proper error handling in custom callbacks
7. **Performance**: Use eager loading for relationship options
8. **Security**: Validate and sanitize all input data

## Troubleshooting

### Common Issues

1. **Relation not found**: Ensure the relation method exists on the model
2. **Validation errors**: Check field attributes and custom rules
3. **Translation issues**: Verify the model has translation support
4. **Media upload problems**: Check file permissions and storage configuration
5. **Custom field errors**: Ensure the model implements `HasCustomField`

### Debugging Tips

1. Use `dd($formBuilder->getForms())` to inspect form structure
2. Check `$formBuilder->getRules()` for validation rules
3. Enable Laravel debugging for detailed error messages
4. Use database queries log to debug relation issues
5. Check storage logs for media upload problems

## Requirements

- PHP 8.2+
- Laravel 11+
- React + Inertia.js (for frontend components)

## License

MIT License

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Support

For issues and questions, please create an issue in the repository or contact the development team.

This documentation covers the complete functionality of the AntiCmsBuilder package. For more specific use cases or advanced customizations, refer to the source code and related services in the package directory.
