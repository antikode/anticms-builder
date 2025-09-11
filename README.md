# AntiCmsBuilder

[![CI](https://github.com/antikode/anti-cms-builder/workflows/CI/badge.svg)](https://github.com/antikode/anti-cms-builder/actions)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)
[![Total Downloads](https://img.shields.io/packagist/dt/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)
[![License](https://img.shields.io/packagist/l/antikode/anti-cms-builder.svg?style=flat-square)](https://packagist.org/packages/antikode/anti-cms-builder)

A powerful Laravel package for building dynamic CRUD interfaces with minimal boilerplate code. This package provides form builders, info lists, table builders, React components, and advanced custom field management to accelerate development while ensuring consistency across your application.

> **🚧 Development Status**: This package is actively maintained and published to Packagist. While stable for production use, new features and improvements are added regularly. Please review the changelog when updating versions.

## Features

- 🚀 **Rapid CRUD Development** - Build complete CRUD interfaces in minutes
- 📝 **Dynamic Form Builder** - Create complex forms with JSON configuration
- 📊 **Advanced Table Builder** - Sortable, searchable, filterable data tables with dynamic actions  
- 📋 **InfoList System** - Structured data display with sections, entry types, and automatic formatting
- 🌍 **Multilingual Support** - Built-in translation management with language tabs
- ⚛️ **React Components** - Pre-built UI components for Inertia.js
- 🔧 **Extensible** - Easy to customize and extend with custom field types
- 🎯 **Laravel 11 Ready** - Full compatibility with latest Laravel
- 🗂️ **Custom Fields System** - Template-based custom field management with hierarchical structure
- 📋 **Field Builder Component** - Interactive field builder for dynamic form creation
- 🎨 **Rich Text Editor** - Built-in WYSIWYG editor support
- ⚙️ **Configuration Management** - Comprehensive configuration system for models, services, and languages

## Table of Contents

1. [Installation](#installation)
2. [Quick Start](#quick-start)
3. [Configuration](#configuration)
4. [FormBuilder Documentation](#formbuilder-documentation)
5. [InfoList Documentation](#infolist-documentation)
6. [TableBuilder Usage](#tablebuilder-usage)
7. [React Components](#react-components)
8. [Custom Fields System](#custom-fields-system)
9. [Console Commands](#console-commands)
10. [Advanced Features](#advanced-features)

## Installation

> **Note**: This package is currently in active development. While published to Packagist, new features and breaking changes may be introduced frequently. Please check the changelog before updating.

### From Packagist (Recommended)

```bash
composer require antikode/anticms-builder
```

### For Development/Latest Features

If you want to use the latest development version with cutting-edge features:

1. Add the repository to your `composer.json`:

**For HTTPS:**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/antikode/anticms-builder.git"
        }
    ]
}
```

**For SSH:**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:antikode/anticms-builder.git"
        }
    ]
}
```

2. Require the development version:

```bash
composer require antikode/anticms-builder:dev-main
```

The package will automatically register its service provider through Laravel's package discovery.

### Database Requirements

For full functionality, ensure you have these database structures:

**Required for Custom Fields:**
- `custom_fields` table for storing custom field data

**Required for Multilingual Support:**
- `translations` table for multilingual content

**Required for Media Features:**
- Media/file storage tables (implementation-dependent)

**Example Migration Structure:**
```php
// Custom fields table
Schema::create('custom_fields', function (Blueprint $table) {
    $table->id();
    $table->morphs('customfieldable');
    $table->string('template')->nullable();
    $table->json('data');
    $table->unsignedBigInteger('parent_id')->nullable();
    $table->integer('sort')->default(0);
    $table->timestamps();
});

// Translations table (example)
Schema::create('translations', function (Blueprint $table) {
    $table->id();
    $table->morphs('translatable');
    $table->string('locale');
    $table->json('data');
    $table->timestamps();
});
```

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

```php
return [
    // Default models used by the package
    'models' => [
        'file' => 'App\\Models\\File',
        'media' => 'App\\Models\\Media',
        'translation' => 'App\\Models\\Translations\\Translation',
        'custom_field' => 'App\\Models\\CustomField\\CustomField',
    ],

    // Service implementations
    'services' => [
        'post' => 'App\\Services\\PostService',
        'template' => 'App\\Services\\TemplateService',
        'custom_field' => 'App\\Services\\CustomFieldService',
    ],

    // Language settings
    'default_language' => 'en',
    'languages' => [
        'en' => 'English',
        'ar' => 'Arabic',
    ],

    // File upload configuration
    'uploads' => [
        'disk' => 'public',
        'path' => 'uploads',
    ],
];
```

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

// Rich Text Editor
TexteditorField::make()
    ->name('content')
    ->label('Content')
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

The package includes comprehensive tests covering all major functionality.

### Running Tests

Run the full test suite:

```bash
composer test
```

Or run specific test groups:

```bash
# Run only unit tests
./vendor/bin/phpunit tests/Unit

# Run only feature tests  
./vendor/bin/phpunit tests/Feature

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage
```

### Test Structure

- **Unit Tests**: Test individual classes and methods
  - `FieldTypes/`: Tests for all field type classes
  - `Tables/`: Tests for table builder functionality
  - Core service tests (FieldService, FormBuilder, TableBuilder)

- **Feature Tests**: Test complete workflows
  - `UseCrudControllerTest.php`: End-to-end CRUD operations

- **Support Files**: Test models, controllers, and migrations for testing environment

### Writing Tests

The package uses Laravel's testing framework. Test models and controllers are provided in `tests/Support/` for testing custom functionality.

## Continuous Integration

The package uses GitHub Actions for continuous integration. The CI workflow:

- Tests against multiple PHP versions (8.2, 8.3)
- Tests against multiple Laravel versions (10.x, 11.x)
- Runs PHPUnit tests
- Checks code style and static analysis
- Generates test coverage reports

See `.github/workflows/ci.yml` for the complete configuration.

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

#### Rich Text Editor Field
```php
TexteditorField::make()
    ->name('content')
    ->label('Content')
    ->placeholder('Enter rich content')
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



## InfoList Documentation

The InfoList system provides a powerful way to display structured information about your models in a clean, organized format. It supports various entry types, sections, custom formatting, and automatic data processing.

### Overview

InfoLists are used to display model data in detail views, providing a structured way to present information with different entry types and sections. The system automatically handles data formatting, relationships, multilingual content, and conditional visibility.

### Basic Usage

Create an InfoList in your controller:

```php
<?php

namespace App\Http\Controllers;

use AntiCmsBuilder\InfoLists\InfoListBuilder;
use AntiCmsBuilder\InfoLists\Section;
use AntiCmsBuilder\InfoLists\Entries\TextEntry;
use AntiCmsBuilder\InfoLists\Entries\BooleanEntry;
use AntiCmsBuilder\InfoLists\Entries\ImageEntry;
use AntiCmsBuilder\InfoLists\Entries\RelationshipEntry;
use AntiCmsBuilder\InfoLists\Entries\DateEntry;
use AntiCmsBuilder\Traits\UseCrudController;
use App\Models\Product;

class ProductController extends Controller
{
    use UseCrudController;

    protected string $model = Product::class;

    public function infoList(InfoListBuilder $builder): InfoListBuilder
    {
        return $builder
            ->record($builder->record)
            ->sections([
                Section::make('Product Information')
                    ->entries([
                        TextEntry::make()
                            ->name('name')
                            ->label('Product Name')
                            ->toArray(),

                        TextEntry::make()
                            ->name('description')
                            ->label('Description')
                            ->limit(200)
                            ->toArray(),

                        TextEntry::make()
                            ->name('price')
                            ->label('Price')
                            ->format(fn ($value) => '$' . number_format($value, 2))
                            ->toArray(),
                    ])
                    ->toArray(),

                Section::make('Status & Availability')
                    ->entries([
                        BooleanEntry::make()
                            ->name('is_active')
                            ->label('Active Status')
                            ->trueLabel('Active')
                            ->falseLabel('Inactive')
                            ->toArray(),
                    ])
                    ->toArray(),
            ]);
    }
}
```

### Entry Types

#### TextEntry

Displays text content with optional formatting and character limits:

```php
TextEntry::make()
    ->name('title')
    ->label('Title')
    ->limit(100)           // Character limit
    ->copyable()           // Add copy to clipboard functionality
    ->markdown()           // Render as markdown
    ->html()               // Render as HTML
    ->format(fn ($value) => strtoupper($value))  // Custom formatting
    ->toArray()
```

#### BooleanEntry

Displays boolean values with customizable labels and colors:

```php
BooleanEntry::make()
    ->name('is_active')
    ->label('Status')
    ->trueLabel('Active')
    ->falseLabel('Inactive')
    ->trueColor('success')
    ->falseColor('danger')
    ->toArray()
```

#### DateEntry

Displays dates with customizable formatting:

```php
DateEntry::make()
    ->name('created_at')
    ->label('Created Date')
    ->dateFormat('F j, Y g:i A')  // Custom date format
    ->toArray()
```

#### ImageEntry

Displays images with customizable dimensions and styling:

```php
ImageEntry::make()
    ->name('featured_image')
    ->label('Featured Image')
    ->height(200)
    ->width(300)
    ->circular()    // Display as circular image
    ->square()      // Display with square aspect ratio
    ->toArray()
```

#### RelationshipEntry

Displays related model data:

```php
RelationshipEntry::make()
    ->name('category')
    ->label('Category')
    ->displayUsing('name')  // Specify which column to display
    ->badge()               // Display as a badge/tag
    ->toArray()
```

### Sections

Group related entries into sections with titles, descriptions, and icons:

```php
Section::make('Product Details')
    ->description('Basic product information')
    ->icon('heroicon-o-information-circle')
    ->collapsed(false)      // Whether section is collapsed by default
    ->entries([
        // Entry definitions...
    ])
    ->visible(fn ($record) => $record->is_published)  // Conditional visibility
    ->toArray()
```

### Advanced Entry Configuration

#### Custom State and Formatting

```php
TextEntry::make()
    ->name('status')
    ->label('Status')
    ->state(fn ($record) => $record->getStatusLabel())  // Custom value retrieval
    ->format(fn ($value, $record) => 
        "<span class='badge badge-{$record->status_color}'>{$value}</span>"
    )
    ->html()  // Render as HTML
    ->toArray()
```

#### Nested Relationships

```php
TextEntry::make()
    ->name('category.parent.name')  // Access nested relationships
    ->label('Parent Category')
    ->toArray()

RelationshipEntry::make()
    ->name('user.profile')
    ->label('Author Profile')
    ->displayUsing('display_name')
    ->toArray()
```

#### Conditional Visibility

```php
TextEntry::make()
    ->name('internal_notes')
    ->label('Internal Notes')
    ->visible(fn ($record) => auth()->user()->can('view-internal-notes'))
    ->hidden(fn ($record) => !$record->has_internal_notes)
    ->toArray()
```

### Complete Example

```php
public function infoList(InfoListBuilder $builder): InfoListBuilder
{
    return $builder
        ->record($builder->record)
        ->sections([
            // Basic Information Section
            Section::make('Product Information')
                ->description('Basic product details and specifications')
                ->icon('heroicon-o-cube')
                ->entries([
                    TextEntry::make()
                        ->name('name')
                        ->label('Product Name')
                        ->copyable()
                        ->toArray(),

                    TextEntry::make()
                        ->name('description')
                        ->label('Description')
                        ->limit(300)
                        ->markdown()
                        ->toArray(),

                    TextEntry::make()
                        ->name('sku')
                        ->label('SKU')
                        ->copyable()
                        ->toArray(),

                    TextEntry::make()
                        ->name('price')
                        ->label('Price')
                        ->format(fn ($value) => $value ? '$' . number_format($value, 2) : '—')
                        ->toArray(),
                ])
                ->toArray(),

            // Status Section
            Section::make('Status & Availability')
                ->entries([
                    BooleanEntry::make()
                        ->name('is_active')
                        ->label('Active Status')
                        ->trueLabel('Active')
                        ->falseLabel('Inactive')
                        ->trueColor('success')
                        ->falseColor('danger')
                        ->toArray(),

                    BooleanEntry::make()
                        ->name('in_stock')
                        ->label('Stock Status')
                        ->trueLabel('In Stock')
                        ->falseLabel('Out of Stock')
                        ->toArray(),
                ])
                ->toArray(),

            // Relationships Section
            Section::make('Relationships')
                ->entries([
                    RelationshipEntry::make()
                        ->name('category')
                        ->label('Category')
                        ->displayUsing('name')
                        ->badge()
                        ->toArray(),

                    RelationshipEntry::make()
                        ->name('user')
                        ->label('Created By')
                        ->displayUsing('name')
                        ->toArray(),
                ])
                ->toArray(),

            // Media Section
            Section::make('Media')
                ->collapsed(true)
                ->entries([
                    ImageEntry::make()
                        ->name('featured_image')
                        ->label('Featured Image')
                        ->height(200)
                        ->square()
                        ->toArray(),
                ])
                ->visible(fn ($record) => $record->featured_image)
                ->toArray(),

            // Timestamps Section
            Section::make('System Information')
                ->collapsed(true)
                ->entries([
                    DateEntry::make()
                        ->name('created_at')
                        ->label('Created Date')
                        ->dateFormat('F j, Y g:i A')
                        ->toArray(),

                    DateEntry::make()
                        ->name('updated_at')
                        ->label('Last Updated')
                        ->dateFormat('F j, Y g:i A')
                        ->toArray(),
                ])
                ->toArray(),
        ])
        ->entries([
            // Global entries (displayed outside of sections)
            TextEntry::make()
                ->name('slug')
                ->label('URL Slug')
                ->copyable()
                ->format(fn ($value) => url($value))
                ->toArray(),
        ]);
}
```

### Data Processing

The InfoListBuilder automatically processes data based on entry types:

#### Automatic Formatting

- **Boolean values**: Converted to "Yes/No" or custom labels
- **Dates**: Formatted using Carbon (default: "M d, Y")
- **Arrays**: Converted to comma-separated strings
- **Relationships**: Displays related model's name, title, or ID
- **Null/Empty values**: Displays "—" placeholder

#### Dot Notation Support

Access nested relationships and properties:

```php
TextEntry::make()
    ->name('category.parent.name')  // Nested relationship
    ->toArray()

TextEntry::make()
    ->name('translations.en.title')  // Multilingual content
    ->toArray()
```

### Best Practices

1. **Organize with Sections**: Group related information logically
2. **Use Appropriate Entry Types**: Choose the right entry type for each data type
3. **Add Descriptions**: Provide context with section descriptions
4. **Conditional Visibility**: Hide irrelevant information based on permissions or data state
5. **Custom Formatting**: Use format callbacks for complex display logic
6. **Performance**: Use eager loading for relationships in your table query
7. **User Experience**: Use collapsed sections for less important information

### Integration with CRUD

When using the `UseCrudController` trait, InfoLists are automatically integrated into the detail view. The system will:

1. Call your `infoList` method to build the structure
2. Set the current record automatically
3. Process all entries with the record data
4. Render the InfoList in your detail view

### Styling and Customization

InfoLists work with your existing CSS framework. Entry types include semantic classes for styling:

- `.info-entry-text` for text entries
- `.info-entry-boolean` for boolean entries  
- `.info-entry-image` for image entries
- `.info-entry-relationship` for relationship entries
- `.info-section` for sections
- `.info-section-collapsed` for collapsed sections

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
- **Index.jsx**: Data table with search, filter, pagination, and dynamic row/table actions
- **Create.jsx**: Form for creating new records
- **Edit.jsx**: Form for editing existing records
- **ActionBuilders.jsx**: Configurable action buttons and dropdowns

### Form Components
- **CreateEditFormWithBuilder.jsx**: Advanced form component with multilingual support, tabs, and integrated field builder
- **FieldBuilderComponent.jsx**: Interactive field builder for creating custom forms
- **Builder.jsx**: Core builder component for dynamic content creation

### Table Components
- **DynamicRowActions.jsx**: Dynamic row-level actions (edit, delete, custom actions)
- **DynamicTableActions.jsx**: Dynamic table-level bulk actions

These components are automatically used when you use the `UseCrudController` trait and provide a complete CRUD interface with:

- Multilingual content management with language tabs
- Dynamic form building with real-time validation
- SEO settings integration
- Status and slug management
- Category and author assignment
- Responsive design with sticky headers
- Advanced table features with sorting, filtering, and pagination
- Custom field management with template support

## Custom Fields System

The package includes a powerful custom fields system that allows you to create template-based custom fields with hierarchical structure.

### Implementing Custom Fields

To enable custom fields on your model, implement the `HasCustomField` contract:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use AntiCmsBuilder\Contracts\HasCustomField;
use AntiCmsBuilder\Traits\CustomFields;

class Product extends Model implements HasCustomField
{
    use CustomFields;
    
    // Your model implementation
}
```

### Custom Field Contract

The `HasCustomField` contract requires two methods:

```php
interface HasCustomField
{
    public function customFields(): \Illuminate\Database\Eloquent\Relations\MorphMany;
    public function getRootCustomFields(): Collection;
}
```

The `CustomFields` trait provides default implementations:

- `customFields()`: Morphs to many custom field records
- `getRootCustomFields()`: Returns root-level custom fields with their children, ordered by sort

### Using Custom Fields in Forms

```php
// In your FormBuilder
RepeaterField::make()
    ->name('custom_template')
    ->label('Custom Fields')
    ->template('product_specifications') // References a template
    ->toArray()
```

### Custom Field Configuration

The custom field model can be configured in `config/anti-cms-builder.php`:

```php
'models' => [
    'custom_field' => 'App\\Models\\CustomField\\CustomField',
],

'services' => [
    'custom_field' => 'App\\Services\\CustomFieldService',
],
```

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

### Custom Fields Integration

When using models that implement `HasCustomField`, the system automatically handles custom field data:

```php
// Data is automatically processed and stored in custom_fields relationship
// with proper hierarchical structure and template association
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
anticms-builder/
├── .github/
│   └── workflows/
│       └── ci.yml                     # GitHub Actions CI workflow
├── config/
│   └── anti-cms-builder.php           # Package configuration file
├── resources/
│   └── js/
│       ├── Components/
│       │   ├── fields/
│       │   │   ├── Builder.jsx        # Core builder component
│       │   │   └── FieldBuilderComponent.jsx
│       │   ├── form/
│       │   │   └── CreateEditFormWithBuilder.jsx
│       │   └── Table/
│       │       ├── DynamicRowActions.jsx
│       │       └── DynamicTableActions.jsx
│       ├── Pages/
│       │   └── CRUD/
│       │       ├── Actions/
│       │       │   └── ActionBuilders.jsx
│       │       ├── Create.jsx
│       │       ├── Edit.jsx
│       │       ├── Index.jsx
│       │       └── TestPackagePage.jsx
│       └── utils/
│           └── resolverPage.js
├── src/
│   ├── Console/
│   │   └── Commands/
│   │       └── PageBuilderCommand.php
│   ├── Contracts/
│   │   ├── HasCustomField.php
│   │   ├── HasForm.php
│   │   └── HasMeta.php
│   ├── FieldTypes/
│   │   ├── Traits/
│   │   │   └── SelectOptionTrait.php
│   │   ├── CustomField.php
│   │   ├── FieldType.php
│   │   ├── FileField.php
│   │   ├── ImageField.php
│   │   ├── InputField.php
│   │   ├── MultiSelectField.php
│   │   ├── RepeaterField.php
│   │   ├── Section.php
│   │   ├── SelectField.php
│   │   ├── TextareaField.php
│   │   ├── TexteditorField.php
│   │   └── ToggleField.php
│   ├── Filters/
│   │   └── SelectField.php
│   ├── Forms/
│   │   └── FormBuilder.php
│   ├── Support/
│   │   └── Color.php
│   ├── Tables/
│   │   ├── Actions/
│   │   │   ├── BulkAction.php
│   │   │   ├── RowAction.php
│   │   │   └── TableAction.php
│   │   ├── Columns/
│   │   │   └── TextColumn.php
│   │   └── TableBuilder.php
│   ├── Traits/
│   │   ├── CustomFields.php
│   │   └── UseCrudController.php
│   ├── AntiCmsBuilderServiceProvider.php
│   ├── ComponentManager.php
│   ├── FieldManager.php
│   ├── FieldService.php
│   ├── Resolver.php
│   └── SortHelper.php
├── tests/
│   ├── Feature/
│   │   └── UseCrudControllerTest.php
│   ├── Support/
│   │   ├── migrations/
│   │   │   └── 2024_01_01_000000_create_test_models_table.php
│   │   ├── TestController.php
│   │   └── TestModel.php
│   ├── Unit/
│   │   ├── FieldTypes/
│   │   │   ├── FieldTypeTest.php
│   │   │   ├── InputFieldTest.php
│   │   │   └── SelectFieldTest.php
│   │   ├── Tables/
│   │   │   └── DynamicParameterTest.php
│   │   ├── FieldServiceTest.php
│   │   ├── FormBuilderTest.php
│   │   ├── ServiceProviderTest.php
│   │   └── TableBuilderTest.php
│   ├── README.md
│   └── TestCase.php
├── composer.json
├── phpunit.xml
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
5. **Custom field errors**: Ensure the model implements `HasCustomField` and uses the `CustomFields` trait
6. **Configuration not found**: Run `php artisan vendor:publish --tag=anti-cms-builder-config`
7. **React components not found**: Run `php artisan vendor:publish --tag=anti-cms-builder-resources`

### Debugging Tips

1. Use `dd($formBuilder->getForms())` to inspect form structure
2. Check `$formBuilder->getRules()` for validation rules
3. Enable Laravel debugging for detailed error messages
4. Use database queries log to debug relation issues
5. Check storage logs for media upload problems

## Requirements

- PHP 8.2+
- Laravel 10.0+ or 11.0+
- React + Inertia.js (for frontend components)
- Composer 2.0+

## License

MIT License

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for recent changes and version history.

## Support

For issues and questions:
- Create an issue in the [GitHub repository](https://github.com/antikode/anti-cms-builder/issues)
- Check the [CI status](https://github.com/antikode/anti-cms-builder/actions) for known issues
- Review the test files in `tests/` for usage examples

## Documentation

This documentation covers the complete functionality of the AntiCmsBuilder package. For more specific use cases or advanced customizations:
- Refer to the source code in the `src/` directory
- Check the React components in `resources/js/`
- Review the test files for working examples
- Examine the configuration file at `config/anti-cms-builder.php`
