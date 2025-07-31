<?php

namespace AntiCmsBuilder\Console\Commands;

use AntiCmsBuilder\ComponentManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\suggest;
use function Laravel\Prompts\text;

class PageBuilderCommand extends Command
{
    protected $signature = 'page:build';

    protected $description = 'Interactive CLI to build a JSON structure using Laravel Prompts';

    protected ComponentManager $componentManager;

    public function __construct()
    {
        parent::__construct();
        $this->componentManager = new ComponentManager;
    }

    public function handle()
    {
        $this->info('🚀 Welcome to JSON Builder CLI!');

        $availableJson = collect(File::allFiles(storage_path('app/json/pages')))->map(fn ($file) => $file->getFilenameWithoutExtension())->toArray();

        $templateName = suggest('Enter template name', $availableJson);
        $filePath = storage_path("app/json/pages/{$templateName}.json");

        if (File::exists($filePath)) {
            $json = json_decode(File::get($filePath), true);
            $this->info('📂 Existing template found. You can edit components.');

            if (confirm('Do you want edit the json file?')) {
                $this->info('📂 Editing json file');
                $json = [
                    'name' => text('Enter template name', $json['name']),
                    'label' => text('Enter template label', $json['label']),
                    'is_content' => confirm('Is this a content template?', $json['is_content']),
                    'multilanguage' => confirm('Is this template multilanguage?', $json['multilanguage']),
                    'description' => text('Enter template description', '', $json['description']),
                    'components' => $json['components'],
                ];
                $filePath = storage_path("app/json/pages/{$json['name']}.json");
                File::delete(storage_path("app/json/pages/{$templateName}.json"));
            }

            while (confirm('Do you want to edit or add components?')) {
                $action = select('Choose Action:', ['Edit', 'Add', 'Sort', 'Preview']);

                match ($action) {
                    'Edit' => $this->componentManager->editComponent($json['components']),
                    'Add' => $json['components'][] = $this->componentManager->createComponent(),
                    'Sort' => $this->componentManager->sortComponents($json['components']),
                    'Preview' => $this->previewJson($json)
                };
            }

            $this->previewJson($json);
            if (confirm('Do you want to save changes?')) {
                File::put($filePath, json_encode($json, JSON_PRETTY_PRINT));
                $this->info('✅ JSON updated successfully.');
            } else {
                $this->warn('⚠ Changes were not saved.');
            }
        } else {
            $json = [
                'name' => $templateName,
                'label' => text('Enter template label'),
                'is_content' => confirm('Is this a content template?'),
                'multilanguage' => confirm('Is this template multilanguage?'),
                'description' => text('Enter template description', ''),
                'components' => [],
            ];

            while (confirm('Do you want to add a component?')) {
                $json['components'][] = $this->componentManager->createComponent();
            }

            $this->previewJson($json);
            if (confirm('Do you want to save this JSON?')) {
                File::put($filePath, json_encode($json, JSON_PRETTY_PRINT));
                $this->info('✅ JSON saved successfully.');
            } else {
                $this->warn('⚠ JSON was not saved.');
            }
        }
    }

    private function previewJson($json)
    {
        $jsonString = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $process = new \Symfony\Component\Process\Process(['bash', '-c', 'echo "$1" | less -R', '_', $jsonString]);
        $process->setTty(true);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Failed to preview JSON.');
        }
    }
}
