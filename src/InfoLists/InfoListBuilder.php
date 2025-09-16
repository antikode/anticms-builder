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

        // Auto-detect file type and add metadata if not explicitly set
        if (!isset($entry['type']) || $entry['type'] === 'text') {
            $detectedType = $this->detectEntryType($value, $name);
            if ($detectedType !== 'text') {
                $entry['type'] = $detectedType;
            }
        }

        // Add file metadata for file-type entries
        $fileTypes = ['file', 'media', 'image', 'video', 'audio', 'document', 'archive'];
        if (in_array($entry['type'] ?? 'text', $fileTypes)) {
            $fileMetadata = $this->getFileMetadata($value);
            if ($fileMetadata) {
                $entry['file_metadata'] = $fileMetadata;
                // Override value with structured data for frontend
                $entry['value'] = $fileMetadata;
            }
        }

        if (!isset($entry['file_metadata'])) {
            $entry['value'] = $value;
        }
        
        $entry['display_value'] = $this->formatDisplayValue($entry, $value);

        return $entry;
    }

    /**
     * Auto-detect entry type based on value and field name
     *
     * @param mixed $value The field value
     * @param string $name The field name
     * @return string The detected type
     */
    private function detectEntryType($value, string $name): string
    {
        if ($value === null || $value === '') {
            return 'text';
        }

        // Type detection based on field name patterns
        if (str_contains($name, 'image') || str_contains($name, 'photo') || str_contains($name, 'avatar') || str_contains($name, 'picture')) {
            return 'image';
        }

        if (str_contains($name, 'file') || str_contains($name, 'document') || str_contains($name, 'attachment') || 
            str_contains($name, 'media') || str_contains($name, 'upload')) {
            
            // Try to detect specific file type
            $fileMetadata = $this->getFileMetadata($value);
            if ($fileMetadata && isset($fileMetadata['mime_type'])) {
                return $this->getTypeFromMimeType($fileMetadata['mime_type']);
            }
            
            if ($fileMetadata && isset($fileMetadata['url'])) {
                return $this->getTypeFromExtension($fileMetadata['url']);
            }
            
            return 'file';
        }

        // Check if value structure suggests it's a file
        if (is_object($value) && method_exists($value, 'getUrl')) {
            $mimeType = $value->mime_type ?? null;
            return $mimeType ? $this->getTypeFromMimeType($mimeType) : 'file';
        }

        if (is_array($value) && (isset($value['url']) || isset($value['src']) || isset($value['path']))) {
            $mimeType = $value['mime_type'] ?? $value['mimeType'] ?? $value['type'] ?? null;
            return $mimeType ? $this->getTypeFromMimeType($mimeType) : 'file';
        }

        return 'text';
    }

    /**
     * Get entry type from MIME type
     *
     * @param string $mimeType
     * @return string
     */
    private function getTypeFromMimeType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }
        
        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }
        
        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }
        
        if ($mimeType === 'application/pdf' || str_contains($mimeType, 'document') || 
            str_contains($mimeType, 'spreadsheet') || str_contains($mimeType, 'presentation')) {
            return 'document';
        }
        
        if (str_contains($mimeType, 'zip') || str_contains($mimeType, 'compressed') || str_contains($mimeType, 'archive')) {
            return 'archive';
        }
        
        return 'file';
    }

    /**
     * Get entry type from file extension
     *
     * @param string $url
     * @return string
     */
    private function getTypeFromExtension(string $url): string
    {
        $pathInfo = pathinfo(parse_url($url, PHP_URL_PATH));
        $extension = strtolower($pathInfo['extension'] ?? '');
        
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
        $videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v'];
        $audioTypes = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'];
        $documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];
        $archiveTypes = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
        
        if (in_array($extension, $imageTypes)) return 'image';
        if (in_array($extension, $videoTypes)) return 'video';
        if (in_array($extension, $audioTypes)) return 'audio';
        if (in_array($extension, $documentTypes)) return 'document';
        if (in_array($extension, $archiveTypes)) return 'archive';
        
        return 'file';
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
     * @return string The formatted display value
     */
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

            case 'file':
            case 'media':
            case 'image':
            case 'video':
            case 'audio':
            case 'document':
            case 'archive':
                return $this->formatFileValue($value);

            default:
                return (string) $value;
        }
    }

    /**
     * Format file values and add metadata for frontend processing
     *
     * @param mixed $value The file value (could be Media model, array, or string)
     * @return string The formatted display value
     */
    private function formatFileValue($value): string
    {
        // Handle Spatie Media Library models
        if (is_object($value) && method_exists($value, 'getUrl')) {
            return $value->name ?? $value->file_name ?? basename($value->getUrl());
        }

        // Handle array format (common in file uploads)
        if (is_array($value)) {
            return $value['name'] ?? $value['filename'] ?? $value['original_name'] ?? 'File';
        }

        // Handle URL strings
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            return basename(parse_url($value, PHP_URL_PATH)) ?: 'File';
        }

        return (string) $value;
    }

    /**
     * Detect if a value represents a file and get its metadata
     *
     * @param mixed $value The value to analyze
     * @return array|null File metadata or null if not a file
     */
    private function getFileMetadata($value): ?array
    {
        $metadata = null;

        // Handle Spatie Media Library models
        if (is_object($value) && method_exists($value, 'getUrl')) {
            $metadata = [
                'url' => $value->getUrl(),
                'name' => $value->name ?? $value->file_name ?? basename($value->getUrl()),
                'size' => $value->size ?? null,
                'mime_type' => $value->mime_type ?? null,
                'original_name' => $value->file_name ?? null,
            ];

            // Add custom properties if available
            if (method_exists($value, 'getCustomProperty')) {
                $metadata['alt'] = $value->getCustomProperty('alt');
            }
        }
        // Handle array format
        elseif (is_array($value) && (isset($value['url']) || isset($value['src']) || isset($value['path']))) {
            $metadata = [
                'url' => $value['url'] ?? $value['src'] ?? $value['path'] ?? null,
                'name' => $value['name'] ?? $value['filename'] ?? $value['original_name'] ?? null,
                'size' => $value['size'] ?? $value['file_size'] ?? null,
                'mime_type' => $value['mime_type'] ?? $value['mimeType'] ?? $value['type'] ?? null,
                'original_name' => $value['original_name'] ?? $value['filename'] ?? null,
            ];
        }
        // Handle URL strings
        elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_URL)) {
            $pathInfo = pathinfo(parse_url($value, PHP_URL_PATH));
            $metadata = [
                'url' => $value,
                'name' => $pathInfo['basename'] ?? 'File',
                'extension' => $pathInfo['extension'] ?? null,
            ];
        }

        return $metadata;
    }
}
