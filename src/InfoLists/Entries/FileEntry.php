<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * FileEntry displays files in info lists with automatic type detection and appropriate rendering.
 * Supports images, videos, audio, documents, archives and generic files.
 * 
 * @extends Entry<FileEntry>
 */
class FileEntry extends Entry
{
    /**
     * Initialize FileEntry with file type
     */
    public function __construct()
    {
        $this->entry['type'] = 'file';
    }

    /**
     * Set the display height for media files (images, videos)
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
     * Set the display width for media files (images, videos)
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
     * Display images in a circular format
     *
     * @return self
     */
    public function circular(): self
    {
        $this->entry['circular'] = true;

        return $this;
    }

    /**
     * Display images in a square aspect ratio
     *
     * @return self
     */
    public function square(): self
    {
        $this->entry['square'] = true;

        return $this;
    }

    /**
     * Force a specific file type instead of auto-detection
     *
     * @param string $type The file type (image, video, audio, document, archive, file)
     * @return self
     */
    public function fileType(string $type): self
    {
        $allowedTypes = ['image', 'video', 'audio', 'document', 'archive', 'file'];
        
        if (in_array($type, $allowedTypes)) {
            $this->entry['type'] = $type;
        }

        return $this;
    }

    /**
     * Show file size in the display
     *
     * @param bool $show Whether to show file size
     * @return self
     */
    public function showSize(bool $show = true): self
    {
        $this->entry['show_size'] = $show;

        return $this;
    }

    /**
     * Show download link for the file
     *
     * @param bool $show Whether to show download link
     * @return self
     */
    public function downloadable(bool $show = true): self
    {
        $this->entry['downloadable'] = $show;

        return $this;
    }
}