<?php

namespace AntiCmsBuilder\InfoLists;

use AntiCmsBuilder\Resolver;
use Illuminate\Database\Eloquent\Model;
use Closure;

final class InfoListBuilder
{
    public array $infoList;

    public string $model;

    public $record;

    public static function make(string $model): self
    {
        return new self($model);
    }

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->infoList = [
            'entries' => [],
            'sections' => [],
        ];
    }

    public function getRecord(): ?Model
    {
        return $this->record;
    }

    public function record(Model $record): self
    {
        $this->record = $record;
        return $this;
    }

    public function entries(array $entries): self
    {
        $this->infoList['entries'] = array_map(fn($entry) => $entry, $entries);
        return $this;
    }

    public function sections(array $sections): self
    {
        $this->infoList['sections'] = array_map(fn($section) => $section, $sections);
        return $this;
    }

    public function build(): array
    {
        $entries = $this->infoList['entries'];
        $sections = $this->infoList['sections'];

        // Process entries with record data
        if ($this->record) {
            $processedEntries = [];
            foreach ($entries as $entry) {
                $processedEntry = $this->processEntry($entry);
                if ($processedEntry) {
                    $processedEntries[] = $processedEntry;
                }
            }
            $this->infoList['entries'] = $processedEntries;

            // Process sections
            $processedSections = [];
            foreach ($sections as $section) {
                $processedSection = $section;
                if (isset($section['entries'])) {
                    $processedSection['entries'] = array_map(fn($entry) => $this->processEntry($entry), $section['entries']);
                }
                $processedSections[] = $processedSection;
            }
            $this->infoList['sections'] = $processedSections;
        }

        return $this->infoList;
    }

    private function processEntry(array $entry): ?array
    {
        if (!isset($entry['name'])) {
            return null;
        }

        $name = $entry['name'];
        $value = $this->getValueFromRecord($name);

        // Handle closures for custom formatting
        if (isset($entry['format']) && $entry['format'] instanceof Closure) {
            $value = $entry['format']($value, $this->record);
            unset($entry['format']);
        }

        // Handle state callbacks
        if (isset($entry['state']) && $entry['state'] instanceof Closure) {
            $value = $entry['state']($this->record);
            unset($entry['state']);
        }

        $entry['value'] = $value;
        $entry['display_value'] = $this->formatDisplayValue($entry, $value);

        return $entry;
    }

    private function getValueFromRecord(string $name)
    {
        if (!$this->record) {
            return null;
        }

        // Handle nested relationships (e.g., 'category.name')
        if (str_contains($name, '.')) {
            $parts = explode('.', $name);
            $value = $this->record;

            foreach ($parts as $part) {
                if ($value && (is_object($value) || is_array($value))) {
                    $value = data_get($value, $part);
                } else {
                    return null;
                }
            }

            return $value;
        }

        // Handle translations
        if (str_contains($name, 'translations.')) {
            $translationKey = str_replace('translations.', '', $name);
            return $this->record->getTranslation($translationKey);
        }

        return data_get($this->record, $name);
    }

    private function formatDisplayValue(array $entry, $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        $type = $entry['type'] ?? 'text';

        switch ($type) {
            case 'boolean':
                return $value ? 'Yes' : 'No';

            case 'date':
                return $value instanceof \Carbon\Carbon ? $value->format('M d, Y') : $value;

            case 'datetime':
                return $value instanceof \Carbon\Carbon ? $value->format('M d, Y H:i') : $value;

            case 'array':
                return is_array($value) ? implode(', ', $value) : $value;

            case 'relationship':
                if (is_object($value) && method_exists($value, 'getDisplayName')) {
                    return $value->getDisplayName();
                }
                return is_object($value) ? ($value->name ?? $value->title ?? $value->id) : $value;

            default:
                return (string) $value;
        }
    }
}
