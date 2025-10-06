<?php

namespace AntiCmsBuilder\Console\Commands;

use Illuminate\Console\Command;

class ProgrammableFieldHelpCommand extends Command
{
    protected $signature = 'programmable-fields:help {topic? : Help topic (commands, examples, presets)}';

    protected $description = 'Show help and examples for programmable fields';

    public function handle(): int
    {
        $topic = $this->argument('topic');

        if (empty($topic)) {
            $this->showMainHelp();
            return 0;
        }

        switch ($topic) {
            case 'commands':
                $this->showCommandHelp();
                break;
            case 'examples':
                $this->showExamples();
                break;
            case 'presets':
                $this->showPresets();
                break;
            default:
                $this->error("Unknown help topic: {$topic}");
                $this->info('Available topics: commands, examples, presets');
                return 1;
        }

        return 0;
    }

    protected function showMainHelp(): void
    {
        $this->info('🎨 AntiCMS Builder - Programmable Fields Help');
        $this->info('');
        $this->info('Create custom input fields with PHP backend logic and JSX frontend components.');
        $this->info('');
        
        $this->info('📋 Quick Commands:');
        $this->info('  php artisan make:programmable-field              # Create custom field (interactive)');
        $this->info('  php artisan make:field-preset color-picker Theme # Create from preset');
        $this->info('  php artisan programmable-fields:list             # List registered fields');
        $this->info('');
        
        $this->info('📚 Get detailed help:');
        $this->info('  php artisan programmable-fields:help commands    # Available commands');
        $this->info('  php artisan programmable-fields:help examples    # Usage examples');
        $this->info('  php artisan programmable-fields:help presets     # Available presets');
        $this->info('');
        
        $this->info('🚀 Quick Start:');
        $this->comment('  1. Run: php artisan make:field-preset color-picker ThemeColor');
        $this->comment('  2. Register component in AppServiceProvider');
        $this->comment('  3. Use ThemeColorField::make() in your controller');
        $this->info('');
        
        $this->info('📖 Full documentation: docs/CUSTOM_FIELDS.md');
    }

    protected function showCommandHelp(): void
    {
        $this->info('📋 Available Commands:');
        $this->info('');
        
        $commands = [
            'make:programmable-field [name]' => [
                'description' => 'Create a new custom programmable field',
                'options' => ['--component=Name', '--force'],
                'examples' => [
                    'php artisan make:programmable-field ProductPicker',
                    'php artisan make:programmable-field UserSearch --component=UserSearchInput',
                ],
            ],
            'make:field-preset <type> [name]' => [
                'description' => 'Generate field from preset template',
                'options' => ['--force'],
                'examples' => [
                    'php artisan make:field-preset color-picker ThemeColor',
                    'php artisan make:field-preset file-upload DocumentUpload',
                ],
            ],
            'programmable-fields:list' => [
                'description' => 'List all registered fields and components',
                'options' => ['--components'],
                'examples' => [
                    'php artisan programmable-fields:list',
                    'php artisan programmable-fields:list --components',
                ],
            ],
        ];

        foreach ($commands as $command => $details) {
            $this->info("🔹 {$command}");
            $this->comment("   {$details['description']}");
            
            if (!empty($details['options'])) {
                $this->info("   Options: " . implode(', ', $details['options']));
            }
            
            $this->info("   Examples:");
            foreach ($details['examples'] as $example) {
                $this->comment("     {$example}");
            }
            $this->info('');
        }
        
        $this->info('💡 All commands support interactive mode when arguments are missing.');
    }

    protected function showExamples(): void
    {
        $this->info('💡 Usage Examples:');
        $this->info('');
        
        $this->info('🎨 1. Color Picker Field');
        $this->comment('   # Generate the field');
        $this->comment('   php artisan make:field-preset color-picker ThemeColor');
        $this->info('');
        $this->comment('   # Use in controller');
        $this->comment('   ThemeColorField::make()');
        $this->comment('       ->name(\'theme_color\')');
        $this->comment('       ->label(\'Theme Color\')');
        $this->comment('       ->required()');
        $this->comment('       ->customAttribute(\'presetColors\', [\'#ff0000\', \'#00ff00\']);');
        $this->info('');
        
        $this->info('📁 2. File Upload Field');
        $this->comment('   # Generate the field');
        $this->comment('   php artisan make:field-preset file-upload DocumentUpload');
        $this->info('');
        $this->comment('   # Use in controller');
        $this->comment('   DocumentUploadField::make()');
        $this->comment('       ->name(\'document\')');
        $this->comment('       ->label(\'Upload Document\')');
        $this->comment('       ->customAttribute(\'maxFileSize\', 1024 * 1024 * 10)');
        $this->comment('       ->customAttribute(\'acceptedTypes\', [\'application/pdf\']);');
        $this->info('');
        
        $this->info('🔍 3. Custom Search Field');
        $this->comment('   # Generate the field');
        $this->comment('   php artisan make:programmable-field ProductSearch');
        $this->info('');
        $this->comment('   # Customize PHP class with search logic');
        $this->comment('   # Customize JSX component with search UI');
        $this->info('');
        $this->comment('   # Use in controller');
        $this->comment('   ProductSearchField::make()');
        $this->comment('       ->name(\'selected_product\')');
        $this->comment('       ->label(\'Product\')');
        $this->comment('       ->customAttribute(\'searchEndpoint\', \'/api/products/search\');');
        $this->info('');
        
        $this->info('⚙️  4. Component Registration (AppServiceProvider)');
        $this->comment('   use AntiCmsBuilder\\Services\\ProgrammableFieldService;');
        $this->info('');
        $this->comment('   public function boot() {');
        $this->comment('       ProgrammableFieldService::registerComponent(');
        $this->comment('           \'ThemeColorInput\',');
        $this->comment('           \'@/Components/fields/types/custom/ThemeColorInput.jsx\'');
        $this->comment('       );');
        $this->comment('   }');
    }

    protected function showPresets(): void
    {
        $this->info('🎨 Available Field Presets:');
        $this->info('');
        
        $presets = [
            'color-picker' => [
                'status' => '✅ Available',
                'description' => 'Advanced color picker with palettes, validation, and random generation',
                'features' => ['Hex color validation', 'Preset color palettes', 'HTML5 color input', 'Random color generation'],
                'example' => 'php artisan make:field-preset color-picker BrandColor',
            ],
            'file-upload' => [
                'status' => '✅ Available', 
                'description' => 'File upload with progress tracking and validation',
                'features' => ['File size validation', 'MIME type restrictions', 'Upload progress', 'Drag and drop'],
                'example' => 'php artisan make:field-preset file-upload DocumentUpload',
            ],
            'rich-editor' => [
                'status' => '🚧 Coming Soon',
                'description' => 'Rich text editor with toolbar and media integration',
                'features' => ['WYSIWYG editing', 'Media insertion', 'Custom toolbar', 'Content sanitization'],
                'example' => 'php artisan make:field-preset rich-editor ContentEditor',
            ],
            'data-picker' => [
                'status' => '🚧 Coming Soon',
                'description' => 'Database search picker with autocomplete',
                'features' => ['Real-time search', 'Multiple selection', 'Custom filters', 'Pagination'],
                'example' => 'php artisan make:field-preset data-picker UserPicker',
            ],
            'image-gallery' => [
                'status' => '🚧 Coming Soon',
                'description' => 'Image gallery management with sorting and cropping',
                'features' => ['Multiple images', 'Drag to reorder', 'Image cropping', 'Alt text editing'],
                'example' => 'php artisan make:field-preset image-gallery ProductGallery',
            ],
            'map-location' => [
                'status' => '🚧 Coming Soon',
                'description' => 'Interactive map location picker',
                'features' => ['Address search', 'GPS coordinates', 'Map markers', 'Zoom controls'],
                'example' => 'php artisan make:field-preset map-location StoreLocation',
            ],
        ];

        foreach ($presets as $key => $preset) {
            $this->info("{$preset['status']} {$key}");
            $this->comment("   {$preset['description']}");
            $this->info("   Features: " . implode(', ', $preset['features']));
            $this->comment("   Example: {$preset['example']}");
            $this->info('');
        }
        
        $this->info('💡 Tips:');
        $this->info('• Start with available presets for common use cases');
        $this->info('• Use make:programmable-field for completely custom fields');
        $this->info('• All presets can be customized after generation');
        $this->info('• Check the generated code for implementation details');
    }
}