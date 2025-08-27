<?php

namespace AntiCmsBuilder\InfoLists\Entries;

class RelationshipEntry extends Entry
{
    public function __construct()
    {
        $this->entry['type'] = 'relationship';
    }

    public function displayUsing(string $column): self
    {
        $this->entry['display_column'] = $column;

        return $this;
    }

    public function badge(): self
    {
        $this->entry['badge'] = true;

        return $this;
    }
}