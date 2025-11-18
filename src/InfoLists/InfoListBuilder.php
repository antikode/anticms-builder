<?php

namespace AntiCmsBuilder\InfoLists;

use AntiCmsBuilder\Resolver;
use Illuminate\Database\Eloquent\Model;
use Closure;

/**
 * InfoListBuilder is responsible for building and processing info lists with entries and sections.
 * It handles data transformation from models to display-ready information lists.
 */
final class InfoListBuilder
{
    /**
     * The info list configuration containing entries and sections
     *
     * @var array
     */
    public array $infoList;

    /**
     * The fully qualified model class name
     *
     * @var string
     */
    public string $model;

    /**
     * The model record instance for data processing
     *
     * @var Model|null
     */
    public $record;

    /**
     * Create a new InfoListBuilder instance
     *
     * @param string $model The fully qualified model class name
     * @return self
     */
    public static function make(string $model): self
    {
        return new self($model);
    }

    /**
     * Initialize the InfoListBuilder with a model
     *
     * @param string $model The fully qualified model class name
     */
    public function __construct(string $model)
    {
        $this->model = $model;
        $this->infoList = [
            'entries' => [],
            'sections' => [],
        ];
    }

    /**
     * Get the current record instance
     *
     * @return Model|null
     */
    public function getRecord(): ?Model
    {
        return $this->record;
    }

    /**
     * Set the record instance for data processing
     *
     * @param Model $record The model instance
     * @return self
     */
    public function record(Model $record): self
    {
        $this->record = $record;
        return $this;
    }

    /**
     * Set the entries for the info list
     *
     * @param array $entries Array of Entry instances
     * @return self
     */
    public function entries(array $entries): self
    {
        $this->infoList['entries'] = array_map(fn($entry) => $entry, $entries);
        return $this;
    }

    /**
     * Set the sections for the info list
     *
     * @param array $sections Array of Section instances
     * @return self
     */
    public function sections(array $sections): self
    {
        $this->infoList['sections'] = array_map(fn($section) => $section, $sections);
        return $this;
    }

    /**
     * Build and process the info list with record data
     *
     * @return array The processed info list array
     */
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

    /**
     * Process a single entry with record data and formatting
     *
     * @param array $entry The entry configuration array
     * @return array|null The processed entry or null if invalid
     */
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

    /**
     * Get the value from the record using dot notation or translations
     *
     * @param string $name The field name, supports dot notation (e.g., 'category.name')
     * @return mixed The field value
     */
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

    /**
     * Format the display value based on the entry type
     *
     * @param array $entry The entry configuration
     * @param mixed $value The raw value to format
     * @return string|array The formatted display value
     */
    private function formatDisplayValue(array $entry, $value)
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
                return $this->formatRelationshipValue($entry, $value);

            default:
                return (string) $value;
        }
    }

    /**
     * Format relationship values based on display mode
     *
     * @param array $entry The entry configuration
     * @param mixed $value The relationship value
     * @return string|array The formatted relationship value
     */
    private function formatRelationshipValue(array $entry, $value)
    {
        $displayMode = $entry['display_mode'] ?? 'default';
        $displayColumn = $entry['display_column'] ?? 'name';

        switch ($displayMode) {
            case 'list':
                return $this->formatRelationshipList($value, $entry['columns'] ?? []);

            case 'comma_list':
                return $this->formatRelationshipCommaList($value, $displayColumn, $entry['separator'] ?? ', ', $entry['item_limit'] ?? null);

            default:
                // Default single relationship display
                if (is_object($value) && method_exists($value, 'getDisplayName')) {
                    return $value->getDisplayName();
                }
                if (is_object($value)) {
                    return $value->{$displayColumn} ?? $value->name ?? $value->title ?? $value->id ?? '—';
                }
                return (string) $value;
        }
    }

    /**
     * Format a relationship as a list with multiple columns
     *
     * @param mixed $value The relationship value (collection or model)
     * @param array $columns The columns to display
     * @return array The formatted list
     */
    private function formatRelationshipList($value, array $columns): array
    {
        if (!is_iterable($value)) {
            return [];
        }

        $items = [];
        foreach ($value as $item) {
            if (is_object($item)) {
                $row = [];
                foreach ($columns as $column) {
                    $row[$column] = data_get($item, $column);
                }
                $items[] = $row;
            }
        }

        return $items;
    }

    /**
     * Format a relationship as a comma-separated list
     *
     * @param mixed $value The relationship value
     * @param string $column The column to display
     * @param string $separator The separator between items
     * @param int|null $limit The maximum number of items to show
     * @return string The formatted comma-separated list
     */
    private function formatRelationshipCommaList($value, string $column, string $separator, ?int $limit): string
    {
        if (!is_iterable($value)) {
            return is_object($value) ? (string)($value->{$column} ?? $value->name ?? '—') : '—';
        }

        $items = [];
        $count = 0;
        $totalCount = 0;

        foreach ($value as $item) {
            $totalCount++;
            if ($limit && $count >= $limit) {
                break;
            }
            if (is_object($item)) {
                $items[] = (string)($item->{$column} ?? $item->name ?? '—');
                $count++;
            }
        }

        if ($limit && $totalCount > $limit) {
            $remaining = $totalCount - $limit;
            return implode($separator, $items) . " and {$remaining} more";
        }

        return implode($separator, $items) ?: '—';
    }
}
