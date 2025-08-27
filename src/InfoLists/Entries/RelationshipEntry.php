<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * RelationshipEntry displays related model data in info lists.
 * Provides options to customize the display column and render as badges.
 * 
 * @extends Entry<RelationshipEntry>
 */
class RelationshipEntry extends Entry
{
    /**
     * Initialize RelationshipEntry with relationship type
     */
    public function __construct()
    {
        $this->entry['type'] = 'relationship';
    }

    /**
     * Specify which column from the related model to display
     *
     * @param string $column The column name to display (e.g., 'name', 'title', 'email')
     * @return self
     */
    public function displayUsing(string $column): self
    {
        $this->entry['display_column'] = $column;

        return $this;
    }

    /**
     * Display the relationship value as a badge/tag
     *
     * @return self
     */
    public function badge(): self
    {
        $this->entry['badge'] = true;

        return $this;
    }
}