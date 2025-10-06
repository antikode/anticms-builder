<?php

namespace AntiCmsBuilder\Console\Commands;

use Illuminate\Console\Command;
use AntiCmsBuilder\Services\ProgrammableFieldService;

class ListProgrammableFieldsCommand extends Command
{
    protected $signature = 'programmable-fields:list {--components : Show registered components}';

    protected $description = 'List all registered programmable fields and components';

    public function handle(): int
    {
        $showComponents = $this->option('components');

        if ($showComponents) {
            $this->displayComponents();
        } else {
            $this->displayFields();
        }

        return 0;
    }

    protected function displayFields(): void
    {
        $fields = app(ProgrammableFieldService::class)->getRegisteredFields();

        if (empty($fields)) {
            $this->info('No programmable fields are currently registered.');
            $this->info('Use `php artisan make:programmable-field <name>` to create a new field.');
            return;
        }

        $this->info('Registered Programmable Fields:');
        $this->info('');

        $tableData = [];
        foreach ($fields as $name => $field) {
            $fieldArray = $field->toArray();
            $tableData[] = [
                $name,
                $field::class,
                $fieldArray['attribute']['componentName'] ?? 'N/A',
                $fieldArray['attribute']['bridgeEndpoint'] ?? 'N/A',
                count($fieldArray['attribute']['customMethods'] ?? []),
            ];
        }

        $this->table([
            'Field Name',
            'Class',
            'Component',
            'Bridge Endpoint',
            'Custom Methods'
        ], $tableData);

        $this->info('');
        $this->info('Use --components flag to view registered components.');
    }

    protected function displayComponents(): void
    {
        $components = app(ProgrammableFieldService::class)->getComponentRegistry();

        if (empty($components)) {
            $this->info('No JSX components are currently registered.');
            $this->info('Register components in your ServiceProvider using:');
            $this->comment('ProgrammableFieldService::registerComponent($name, $path)');
            return;
        }

        $this->info('Registered JSX Components:');
        $this->info('');

        $tableData = [];
        foreach ($components as $name => $path) {
            $tableData[] = [$name, $path];
        }

        $this->table(['Component Name', 'Path'], $tableData);

        $this->info('');
        $this->info('These components can be used with the ->component() method on programmable fields.');
    }
}
