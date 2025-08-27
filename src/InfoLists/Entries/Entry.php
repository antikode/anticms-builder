<?php

namespace AntiCmsBuilder\InfoLists\Entries;

use Closure;

/**
 * @template T
 * 
 * Base abstract class for all info list entry types.
 * Provides common functionality for displaying data in info lists.
 */
abstract class Entry
{
    /**
     * The entry configuration array
     *
     * @var array
     */
    protected array $entry = [];

    /**
     * Whether a custom label has been set
     *
     * @var bool
     */
    protected bool $hasCustomLabel = false;

    /**
     * Create a new Entry instance
     *
     * @return static
     */
    public static function make(): static
    {
        return new static;
    }

    /**
     * Set the field name for this entry
     *
     * @param string $name The field name
     * @return self
     */
    public function name(string $name): self
    {
        $this->entry['name'] = $name;

        if (! $this->hasCustomLabel) {
            $this->entry['label'] = $this->generateLabel($name);
        }

        return $this;
    }

    /**
     * Set a custom label for this entry
     *
     * @param string $label The display label
     * @return self
     */
    public function label(string $label): self
    {
        $this->entry['label'] = $label;
        $this->hasCustomLabel = true;

        return $this;
    }

    /**
     * Set a custom format callback for the entry value
     *
     * @param Closure $callback Callback that receives (value, record) and returns formatted value
     * @return self
     */
    public function format(Closure $callback): self
    {
        $this->entry['format'] = $callback;

        return $this;
    }

    /**
     * Set a state callback to customize the value retrieval
     *
     * @param Closure $callback Callback that receives the record and returns the value
     * @return self
     */
    public function state(Closure $callback): self
    {
        $this->entry['state'] = $callback;

        return $this;
    }

    /**
     * Set a placeholder value when the field is empty
     *
     * @param string $placeholder The placeholder text
     * @return self
     */
    public function placeholder(string $placeholder): self
    {
        $this->entry['placeholder'] = $placeholder;

        return $this;
    }

    /**
     * Set helper text for this entry
     *
     * @param string $text The helper text
     * @return self
     */
    public function helperText(string $text): self
    {
        $this->entry['helper_text'] = $text;

        return $this;
    }

    /**
     * Set an icon for this entry
     *
     * @param string $icon The icon name or class
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->entry['icon'] = $icon;

        return $this;
    }

    /**
     * Set a color for this entry
     *
     * @param string $color The color value or class
     * @return self
     */
    public function color(string $color): self
    {
        $this->entry['color'] = $color;

        return $this;
    }

    /**
     * Set a callback to determine entry visibility
     *
     * @param Closure $callback Callback that receives the record and returns boolean
     * @return self
     */
    public function visible(Closure $callback): self
    {
        $this->entry['visible'] = $callback;

        return $this;
    }

    /**
     * Set a callback to determine if entry should be hidden
     *
     * @param Closure $callback Callback that receives the record and returns boolean
     * @return self
     */
    public function hidden(Closure $callback): self
    {
        $this->entry['hidden'] = $callback;

        return $this;
    }

    /**
     * Generate a display label from the field name
     *
     * @param string $name The field name
     * @return string The generated label
     */
    protected function generateLabel(string $name): string
    {
        return \Illuminate\Support\Str::title(str_replace(['_', '.'], ' ', $name));
    }

    /**
     * Convert the entry to array representation
     *
     * @return array The entry configuration array
     */
    public function toArray(): array
    {
        return $this->entry;
    }
}