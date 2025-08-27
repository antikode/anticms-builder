<?php

namespace AntiCmsBuilder\InfoLists\Entries;

use Closure;

abstract class Entry
{
    protected array $entry = [];

    protected bool $hasCustomLabel = false;

    public static function make(): static
    {
        return new static;
    }

    public function name(string $name): self
    {
        $this->entry['name'] = $name;

        if (! $this->hasCustomLabel) {
            $this->entry['label'] = $this->generateLabel($name);
        }

        return $this;
    }

    public function label(string $label): self
    {
        $this->entry['label'] = $label;
        $this->hasCustomLabel = true;

        return $this;
    }

    public function format(Closure $callback): self
    {
        $this->entry['format'] = $callback;

        return $this;
    }

    public function state(Closure $callback): self
    {
        $this->entry['state'] = $callback;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->entry['placeholder'] = $placeholder;

        return $this;
    }

    public function helperText(string $text): self
    {
        $this->entry['helper_text'] = $text;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->entry['icon'] = $icon;

        return $this;
    }

    public function color(string $color): self
    {
        $this->entry['color'] = $color;

        return $this;
    }

    public function visible(Closure $callback): self
    {
        $this->entry['visible'] = $callback;

        return $this;
    }

    public function hidden(Closure $callback): self
    {
        $this->entry['hidden'] = $callback;

        return $this;
    }

    protected function generateLabel(string $name): string
    {
        return \Illuminate\Support\Str::title(str_replace(['_', '.'], ' ', $name));
    }

    public function toArray(): array
    {
        return $this->entry;
    }
}