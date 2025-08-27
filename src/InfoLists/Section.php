<?php

namespace AntiCmsBuilder\InfoLists;

use AntiCmsBuilder\InfoLists\Entries\Entry;
use Closure;

class Section
{
    protected array $section = [];

    public static function make(string $title = null): self
    {
        return new self($title);
    }

    public function __construct(string $title = null)
    {
        if ($title) {
            $this->section['title'] = $title;
        }
    }

    public function title(string $title): self
    {
        $this->section['title'] = $title;

        return $this;
    }

    public function description(string $description): self
    {
        $this->section['description'] = $description;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->section['icon'] = $icon;

        return $this;
    }

    public function collapsed(bool $collapsed = true): self
    {
        $this->section['collapsed'] = $collapsed;

        return $this;
    }

    public function entries(array $entries): self
    {
        $this->section['entries'] = array_map(fn($entry) => $entry, $entries);

        return $this;
    }

    public function visible(Closure $callback): self
    {
        $this->section['visible'] = $callback;

        return $this;
    }

    public function hidden(Closure $callback): self
    {
        $this->section['hidden'] = $callback;

        return $this;
    }

    public function toArray(): array
    {
        return $this->section;
    }
}
