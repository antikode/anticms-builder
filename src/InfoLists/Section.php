<?php

namespace AntiCmsBuilder\InfoLists;

use AntiCmsBuilder\InfoLists\Entries\Entry;
use Closure;

/**
 * Section represents a grouped section of entries within an info list.
 * Sections can have titles, descriptions, icons, and can be collapsible.
 */
class Section
{
    /**
     * The section configuration array
     *
     * @var array
     */
    protected array $section = [];

    /**
     * Create a new Section instance
     *
     * @param string|null $title The section title
     * @return self
     */
    public static function make(string $title = null): self
    {
        return new self($title);
    }

    /**
     * Initialize the Section
     *
     * @param string|null $title The section title
     */
    public function __construct(string $title = null)
    {
        if ($title) {
            $this->section['title'] = $title;
        }
    }

    /**
     * Set the section title
     *
     * @param string $title The section title
     * @return self
     */
    public function title(string $title): self
    {
        $this->section['title'] = $title;

        return $this;
    }

    /**
     * Set the section description
     *
     * @param string $description The section description
     * @return self
     */
    public function description(string $description): self
    {
        $this->section['description'] = $description;

        return $this;
    }

    /**
     * Set the section icon
     *
     * @param string $icon The icon name or class
     * @return self
     */
    public function icon(string $icon): self
    {
        $this->section['icon'] = $icon;

        return $this;
    }

    /**
     * Set whether the section should be collapsed by default
     *
     * @param bool $collapsed Whether the section is collapsed
     * @return self
     */
    public function collapsed(bool $collapsed = true): self
    {
        $this->section['collapsed'] = $collapsed;

        return $this;
    }

    /**
     * Set the entries for this section
     *
     * @param array $entries Array of Entry instances
     * @return self
     */
    public function entries(array $entries): self
    {
        $this->section['entries'] = array_map(fn($entry) => $entry, $entries);

        return $this;
    }

    /**
     * Set a callback to determine section visibility
     *
     * @param Closure $callback Callback that receives the record and returns boolean
     * @return self
     */
    public function visible(Closure $callback): self
    {
        $this->section['visible'] = $callback;

        return $this;
    }

    /**
     * Set a callback to determine if section should be hidden
     *
     * @param Closure $callback Callback that receives the record and returns boolean
     * @return self
     */
    public function hidden(Closure $callback): self
    {
        $this->section['hidden'] = $callback;

        return $this;
    }

    /**
     * Convert the section to array representation
     *
     * @return array The section configuration array
     */
    public function toArray(): array
    {
        return $this->section;
    }
}
