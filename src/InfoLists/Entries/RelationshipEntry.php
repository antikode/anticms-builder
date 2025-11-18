<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * RelationshipEntry displays related model data in info lists.
 * Provides options to customize the display column, render as badges, and load full relation data.
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

    /**
     * Display the full relationship as a formatted list/table
     * Supports BelongsToMany and HasMany relationships
     *
     * @param array $columns The columns to display (e.g., ['name', 'email'])
     * @return self
     */
    public function list(array $columns = []): self
    {
        $this->entry['display_mode'] = 'list';
        $this->entry['columns'] = $columns;

        return $this;
    }

    /**
     * Display related items as a comma-separated list with a specific column
     *
     * @param string $column The column to use for display (defaults to 'name')
     * @param string $separator The separator between items (defaults to ', ')
     * @return self
     */
    public function asList(string $column = 'name', string $separator = ', '): self
    {
        $this->entry['display_mode'] = 'comma_list';
        $this->entry['display_column'] = $column;
        $this->entry['separator'] = $separator;

        return $this;
    }

    /**
     * Set the number of items to display before showing "and X more"
     *
     * @param int $limit The number of items to show
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->entry['item_limit'] = $limit;

        return $this;
    }
}