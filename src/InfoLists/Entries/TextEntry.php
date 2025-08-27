<?php

namespace AntiCmsBuilder\InfoLists\Entries;

class TextEntry extends Entry
{
    public function __construct()
    {
        $this->entry['type'] = 'text';
    }

    public function limit(int $limit): self
    {
        $this->entry['limit'] = $limit;

        return $this;
    }

    public function copyable(): self
    {
        $this->entry['copyable'] = true;

        return $this;
    }

    public function markdown(): self
    {
        $this->entry['type'] = 'markdown';

        return $this;
    }

    public function html(): self
    {
        $this->entry['type'] = 'html';

        return $this;
    }
}