<?php

namespace AntiCmsBuilder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeFieldPresetCommand extends Command
{
    protected $signature = 'make:field-preset {type? : The preset type (color-picker, file-upload, rich-editor, data-picker)} {name? : The name of the field} {--force : Overwrite existing files}';

    protected $description = 'Create a programmable field from a preset template';

    protected Filesystem $files;

    protected array $presets = [
        'color-picker' => 'Color Picker Field',
        'file-upload' => 'File Upload Field',
        'rich-editor' => 'Rich Text Editor Field',
        'data-picker' => 'Database Picker Field',
        'image-gallery' => 'Image Gallery Field',
        'map-location' => 'Map Location Field',
        'rating' => 'Rating Field',
        'tags' => 'Tags Input Field',
    ];

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $name = $this->argument('name');
        $force = $this->option('force');

        // If type is missing, show available presets and ask for it
        if (empty($type)) {
            $type = $this->askForPresetType();
            
            if (empty($type)) {
                $this->error('Preset type is required to generate a field.');
                $this->info('Usage: php artisan make:field-preset <type> <name>');
                $this->info('Example: php artisan make:field-preset color-picker ThemeColor');
                return 1;
            }
        }

        // If name is missing, ask for it
        if (empty($name)) {
            $name = $this->askForFieldName();
            
            if (empty($name)) {
                $this->error('Field name is required to generate a field.');
                $this->info('Usage: php artisan make:field-preset <type> <name>');
                $this->info('Example: php artisan make:field-preset color-picker ThemeColor');
                return 1;
            }
        }

        // Validate field name format
        if (!$this->isValidFieldName($name)) {
            $this->error('Invalid field name format.');
            $this->info('Field name should be in PascalCase (e.g., ThemeColor, BrandPicker, DocumentUpload)');
            $this->info('It should contain only letters and numbers, starting with a letter.');
            return 1;
        }

        if (!isset($this->presets[$type])) {
            $this->error("Unknown preset type: {$type}");
            $this->info('Available presets:');
            foreach ($this->presets as $key => $description) {
                $this->info("  {$key} - {$description}");
            }
            return 1;
        }

        $this->info("Creating {$this->presets[$type]} with name: {$name}");

        switch ($type) {
            case 'color-picker':
                return $this->createColorPickerField($name, $force);
            case 'file-upload':
                return $this->createFileUploadField($name, $force);
            case 'rich-editor':
                return $this->createRichEditorField($name, $force);
            case 'data-picker':
                return $this->createDataPickerField($name, $force);
            case 'image-gallery':
                return $this->createImageGalleryField($name, $force);
            case 'map-location':
                return $this->createMapLocationField($name, $force);
            case 'rating':
                return $this->createRatingField($name, $force);
            case 'tags':
                return $this->createTagsField($name, $force);
            default:
                $this->error("Preset {$type} is not yet implemented");
                return 1;
        }
    }

    protected function createColorPickerField(string $name, bool $force): int
    {
        $className = Str::studly($name) . 'Field';
        $componentName = Str::studly($name) . 'Input';

        // PHP Field
        $phpPath = app_path("Fields/{$className}.php");
        $phpContent = $this->getColorPickerPhpStub($className, $componentName, $name);

        // JSX Component
        $jsxPath = resource_path("js/Components/fields/types/custom/{$componentName}.jsx");
        $jsxContent = $this->getColorPickerJsxStub($componentName);

        return $this->createFiles([
            [$phpPath, $phpContent, 'PHP Field'],
            [$jsxPath, $jsxContent, 'JSX Component'],
        ], $force, $componentName);
    }

    protected function createFileUploadField(string $name, bool $force): int
    {
        $className = Str::studly($name) . 'Field';
        $componentName = Str::studly($name) . 'Upload';

        $phpPath = app_path("Fields/{$className}.php");
        $phpContent = $this->getFileUploadPhpStub($className, $componentName, $name);

        $jsxPath = resource_path("js/Components/fields/types/custom/{$componentName}.jsx");
        $jsxContent = $this->getFileUploadJsxStub($componentName);

        return $this->createFiles([
            [$phpPath, $phpContent, 'PHP Field'],
            [$jsxPath, $jsxContent, 'JSX Component'],
        ], $force, $componentName);
    }

    protected function createRichEditorField(string $name, bool $force): int
    {
        $className = Str::studly($name) . 'Field';
        $componentName = Str::studly($name) . 'Editor';

        $phpPath = app_path("Fields/{$className}.php");
        $phpContent = $this->getRichEditorPhpStub($className, $componentName, $name);

        $jsxPath = resource_path("js/Components/fields/types/custom/{$componentName}.jsx");
        $jsxContent = $this->getRichEditorJsxStub($componentName);

        return $this->createFiles([
            [$phpPath, $phpContent, 'PHP Field'],
            [$jsxPath, $jsxContent, 'JSX Component'],
        ], $force, $componentName);
    }

    protected function createDataPickerField(string $name, bool $force): int
    {
        $className = Str::studly($name) . 'Field';
        $componentName = Str::studly($name) . 'Picker';

        $phpPath = app_path("Fields/{$className}.php");
        $phpContent = $this->getDataPickerPhpStub($className, $componentName, $name);

        $jsxPath = resource_path("js/Components/fields/types/custom/{$componentName}.jsx");
        $jsxContent = $this->getDataPickerJsxStub($componentName);

        return $this->createFiles([
            [$phpPath, $phpContent, 'PHP Field'],
            [$jsxPath, $jsxContent, 'JSX Component'],
        ], $force, $componentName);
    }

    protected function createImageGalleryField(string $name, bool $force): int
    {
        $this->error("Image Gallery preset '{$name}' is not yet implemented" . ($force ? ' (force mode)' : ''));
        return 1;
    }

    protected function createMapLocationField(string $name, bool $force): int
    {
        $this->error("Map Location preset '{$name}' is not yet implemented" . ($force ? ' (force mode)' : ''));
        return 1;
    }

    protected function createRatingField(string $name, bool $force): int
    {
        $this->error("Rating preset '{$name}' is not yet implemented" . ($force ? ' (force mode)' : ''));
        return 1;
    }

    protected function createTagsField(string $name, bool $force): int
    {
        $this->error("Tags preset '{$name}' is not yet implemented" . ($force ? ' (force mode)' : ''));
        return 1;
    }

    protected function createFiles(array $files, bool $force, string $componentName): int
    {
        foreach ($files as [$path, $content, $type]) {
            if ($this->files->exists($path) && !$force) {
                if (!$this->confirm("{$type} already exists. Overwrite?")) {
                    continue;
                }
            }

            $this->files->ensureDirectoryExists(dirname($path));
            $this->files->put($path, $content);
            $this->info("✅ Created {$type}: {$path}");
        }

        $this->info('');
        $this->info('✅ Field preset created successfully!');
        $this->info('');
        $this->info('Don\'t forget to register the component:');
        $this->comment("ProgrammableFieldService::registerComponent('{$componentName}', '@/Components/fields/types/custom/{$componentName}.jsx');");

        return 0;
    }

    protected function getColorPickerPhpStub(string $className, string $componentName, string $fieldName): string
    {
        $stubPath = __DIR__ . '/../../stubs/presets/color-picker.php.stub';
        return $this->files->get($stubPath);
    }

    protected function getColorPickerJsxStub(string $componentName): string
    {
        $stubPath = __DIR__ . '/../../stubs/presets/color-picker.jsx.stub';
        return $this->files->get($stubPath);
    }

    protected function getFileUploadPhpStub(string $className, string $componentName, string $fieldName): string
    {
        $stubPath = __DIR__ . '/../../stubs/presets/file-upload.php.stub';
        return $this->files->get($stubPath);
    }

    protected function getFileUploadJsxStub(string $componentName): string
    {
        $stubPath = __DIR__ . '/../../stubs/presets/file-upload.jsx.stub';
        return $this->files->get($stubPath);
    }

    protected function getRichEditorPhpStub(string $className, string $componentName, string $fieldName): string
    {
        return "<?php\n\n// Rich Editor Field {$className} with component {$componentName} for {$fieldName} - Implementation needed";
    }

    protected function getRichEditorJsxStub(string $componentName): string
    {
        return "// Rich Editor Component {$componentName} - Implementation needed";
    }

    protected function getDataPickerPhpStub(string $className, string $componentName, string $fieldName): string
    {
        return "<?php\n\n// Data Picker Field {$className} with component {$componentName} for {$fieldName} - Implementation needed";
    }

    protected function getDataPickerJsxStub(string $componentName): string
    {
        return "// Data Picker Component {$componentName} - Implementation needed";
    }

    protected function askForPresetType(): ?string
    {
        $this->info('🎨 Let\'s create a field from a preset template!');
        $this->info('');
        $this->info('Available preset types:');
        $this->info('');
        
        foreach ($this->presets as $key => $description) {
            $status = in_array($key, ['color-picker', 'file-upload']) ? '✅' : '🚧';
            $this->info("  {$status} {$key} - {$description}");
        }
        
        $this->info('');
        $this->info('✅ = Fully implemented');
        $this->info('🚧 = Coming soon');
        $this->info('');
        
        $type = $this->ask('Which preset type would you like to use?', 'color-picker');
        
        // Keep asking until we get a valid type or user cancels
        while (!empty($type) && !isset($this->presets[$type])) {
            $this->error("'{$type}' is not a valid preset type.");
            $this->info('Available types: ' . implode(', ', array_keys($this->presets)));
            
            $type = $this->ask('Which preset type would you like to use?');
        }
        
        return $type;
    }

    protected function askForFieldName(): ?string
    {
        $this->info('📝 What should we call your field?');
        $this->info('');
        $this->info('Field names should be descriptive and use PascalCase format.');
        $this->info('Examples: ThemeColor, BrandPicker, DocumentUpload, UserAvatar');
        $this->info('');
        
        $name = $this->ask('What should the field name be?');
        
        // Keep asking until we get a valid name or user cancels
        while (!empty($name) && !$this->isValidFieldName($name)) {
            $this->error("'{$name}' is not a valid field name.");
            $this->info('Please use PascalCase format with only letters and numbers.');
            $this->info('Examples: ThemeColor, BrandPicker, DocumentUpload');
            
            $name = $this->ask('What should the field name be?');
        }
        
        return $name;
    }

    protected function isValidFieldName(string $name): bool
    {
        // Check if name follows PascalCase format and contains only letters/numbers
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) && strlen($name) > 1;
    }
}