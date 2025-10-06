<?php

namespace AntiCmsBuilder\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeProgrammableFieldCommand extends Command
{
    protected $signature = 'make:programmable-field {name? : The name of the programmable field} {--component= : The JSX component name} {--force : Overwrite existing files}';

    protected $description = 'Create a new programmable field with PHP class and JSX component';

    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle(): int
    {
        $name = $this->argument('name');

        // If name is missing, ask for it
        if (empty($name)) {
            $name = $this->askForFieldName();

            // If still empty after asking, show error
            if (empty($name)) {
                $this->error('Field name is required to generate a programmable field.');
                $this->info('Usage: php artisan make:programmable-field <name>');
                $this->info('Example: php artisan make:programmable-field ProductPicker');

                return 1;
            }
        }

        // Validate field name format
        if (! $this->isValidFieldName($name)) {
            $this->error('Invalid field name format.');
            $this->info('Field name should be in PascalCase (e.g., ProductPicker, ColorSelector, FileUploader)');
            $this->info('It should contain only letters and numbers, starting with a letter.');

            return 1;
        }

        $componentName = $this->option('component') ?: $this->ask('What should the JSX component be called?', Str::studly($name).'Input');
        $force = $this->option('force');

        $this->info("Creating programmable field: {$name}");
        $this->info("JSX component: {$componentName}");

        // Create PHP field class
        $phpCreated = $this->createPhpField($name, $componentName, $force);

        // Create JSX component
        $jsxCreated = $this->createJsxComponent($name, $componentName, $force);

        // Create example usage
        $exampleCreated = $this->createExampleUsage($name, $componentName, $force);

        if ($phpCreated && $jsxCreated) {
            $this->info('');
            $this->info('✅ Programmable field created successfully!');
            $this->info('');
            $this->info('Next steps:');
            $this->info('1. Register the component in your ServiceProvider:');
            $this->comment("   ProgrammableFieldService::register('{$componentName}', {$this->getFieldClassName($name)::make()}");
            $this->info('2. Use the field in your controller:');
            $this->comment("   {$this->getFieldClassName($name)}::make()->name('field_name')->label('Field Label')");

            if ($exampleCreated) {
                $this->info('3. Check the example usage in:');
                $this->comment("   app/Http/Controllers/Examples/{$this->getFieldClassName($name)}ExampleController.php");
            }

            return 0;
        }

        $this->error('Failed to create some files');

        return 1;
    }

    protected function createPhpField(string $name, string $componentName, bool $force): bool
    {
        $className = $this->getFieldClassName($name);
        $path = app_path("Fields/{$className}.php");

        if ($this->files->exists($path) && ! $force) {
            if (! $this->confirm('PHP field class already exists. Overwrite?')) {
                $this->warn('Skipping PHP field creation');

                return true;
            }
        }

        $stub = $this->getPhpStub();
        $content = $this->replacePlaceholders($stub, [
            '{{ className }}' => $className,
            '{{ componentName }}' => $componentName,
            '{{ fieldName }}' => Str::snake($name),
            '{{ fieldLabel }}' => Str::title(Str::snake($name, ' ')),
            '{{ namespace }}' => 'App\\Fields',
            '{{ jsxPath }}' => 'resources/js/types/custom/'.$componentName.'.jsx',
        ]);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);

        $this->info("✅ Created PHP field: {$path}");

        return true;
    }

    protected function createJsxComponent(string $name, string $componentName, bool $force): bool
    {
        $path = resource_path("js/Components/fields/types/custom/{$componentName}.jsx");

        if ($this->files->exists($path) && ! $force) {
            if (! $this->confirm('JSX component already exists. Overwrite?')) {
                $this->warn('Skipping JSX component creation');

                return true;
            }
        }

        $stub = $this->getJsxStub();
        $content = $this->replacePlaceholders($stub, [
            '{{ componentName }}' => $componentName,
            '{{ fieldName }}' => Str::snake($name),
            '{{ fieldLabel }}' => Str::title(Str::snake($name, ' ')),
        ]);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);

        $this->info("✅ Created JSX component: {$path}");

        return true;
    }

    protected function createExampleUsage(string $name, string $componentName, bool $force): bool
    {
        if (! $this->confirm('Create example usage controller?', true)) {
            return false;
        }

        $className = $this->getFieldClassName($name);
        $controllerName = "{$className}ExampleController";
        $path = app_path("Http/Controllers/Examples/{$controllerName}.php");

        if ($this->files->exists($path) && ! $force) {
            if (! $this->confirm('Example controller already exists. Overwrite?')) {
                $this->warn('Skipping example controller creation');

                return false;
            }
        }

        $stub = $this->getExampleStub();
        $content = $this->replacePlaceholders($stub, [
            '{{ controllerName }}' => $controllerName,
            '{{ className }}' => $className,
            '{{ fieldName }}' => Str::snake($name),
            '{{ fieldLabel }}' => Str::title(Str::snake($name, ' ')),
            '{{ componentName }}' => $componentName,
        ]);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $content);

        $this->info("✅ Created example controller: {$path}");

        return true;
    }

    protected function getFieldClassName(string $name): string
    {
        return Str::studly($name).'Field';
    }

    protected function replacePlaceholders(string $content, array $replacements): string
    {
        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    protected function askForFieldName(): ?string
    {
        $this->info('📝 Let\'s create your custom programmable field!');
        $this->info('');
        $this->info('Field names should be descriptive and use PascalCase format.');
        $this->info('Examples: ProductPicker, ColorSelector, FileUploader, UserSearch');
        $this->info('');

        $name = $this->ask('What should the field name be?');

        // Keep asking until we get a valid name or user cancels
        while (! empty($name) && ! $this->isValidFieldName($name)) {
            $this->error("'{$name}' is not a valid field name.");
            $this->info('Please use PascalCase format with only letters and numbers.');
            $this->info('Examples: ProductPicker, ColorSelector, FileUploader');

            $name = $this->ask('What should the field name be?');
        }

        return $name;
    }

    protected function isValidFieldName(string $name): bool
    {
        // Check if name follows PascalCase format and contains only letters/numbers
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name) && strlen($name) > 1;
    }

    protected function getPhpStub(): string
    {
        $stubPath = __DIR__.'/../../../stubs/programmable-field.php.stub';

        return $this->files->get($stubPath);
    }

    protected function getJsxStub(): string
    {
        $stubPath = __DIR__.'/../../../stubs/programmable-field.jsx.stub';

        return $this->files->get($stubPath);
    }

    protected function getExampleStub(): string
    {
        $stubPath = __DIR__.'/../../../stubs/example-controller.php.stub';

        return $this->files->get($stubPath);
    }
}
