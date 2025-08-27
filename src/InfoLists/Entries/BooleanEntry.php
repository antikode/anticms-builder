<?php

namespace AntiCmsBuilder\InfoLists\Entries;

class BooleanEntry extends Entry
{
    public function __construct()
    {
        $this->entry['type'] = 'boolean';
    }

    public function trueLabel(string $label): self
    {
        $this->entry['true_label'] = $label;

        return $this;
    }

    public function falseLabel(string $label): self
    {
        $this->entry['false_label'] = $label;

        return $this;
    }

    public function trueColor(string $color): self
    {
        $this->entry['true_color'] = $color;

        return $this;
    }

    public function falseColor(string $color): self
    {
        $this->entry['false_color'] = $color;

        return $this;
    }
}