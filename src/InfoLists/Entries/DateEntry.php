<?php

namespace AntiCmsBuilder\InfoLists\Entries;

class DateEntry extends Entry
{
    public function __construct()
    {
        $this->entry['type'] = 'date';
    }

    public function dateFormat(string $format): self
    {
        $this->entry['date_format'] = $format;

        return $this;
    }
}