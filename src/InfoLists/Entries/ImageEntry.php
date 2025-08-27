<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * ImageEntry displays images in info lists with customizable dimensions and styles.
 * Supports various display modes including circular and square aspect ratios.
 * 
 * @extends Entry<ImageEntry>
 */
class ImageEntry extends Entry
{
    /**
     * Initialize ImageEntry with image type
     */
    public function __construct()
    {
        $this->entry['type'] = 'image';
    }

    /**
     * Set the display height of the image
     *
     * @param int $height The height in pixels
     * @return self
     */
    public function height(int $height): self
    {
        $this->entry['height'] = $height;

        return $this;
    }

    /**
     * Set the display width of the image
     *
     * @param int $width The width in pixels
     * @return self
     */
    public function width(int $width): self
    {
        $this->entry['width'] = $width;

        return $this;
    }

    /**
     * Display the image in a circular format
     *
     * @return self
     */
    public function circular(): self
    {
        $this->entry['circular'] = true;

        return $this;
    }

    /**
     * Display the image in a square aspect ratio
     *
     * @return self
     */
    public function square(): self
    {
        $this->entry['square'] = true;

        return $this;
    }
}
