<?php

namespace AntiCmsBuilder\InfoLists\Entries;

class ImageEntry extends Entry
{
    public function __construct()
    {
        $this->entry['type'] = 'image';
    }

    public function height(int $height): self
    {
        $this->entry['height'] = $height;

        return $this;
    }

    public function width(int $width): self
    {
        $this->entry['width'] = $width;

        return $this;
    }

    public function circular(): self
    {
        $this->entry['circular'] = true;

        return $this;
    }

    public function square(): self
    {
        $this->entry['square'] = true;

        return $this;
    }
}
