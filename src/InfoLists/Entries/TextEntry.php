<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * TextEntry displays text content in info lists.
 * Supports plain text, markdown, and HTML rendering with optional character limits.
 * 
 * @extends Entry<TextEntry>
 */
class TextEntry extends Entry
{
    /**
     * Initialize TextEntry with text type
     */
    public function __construct()
    {
        $this->entry['type'] = 'text';
    }

    /**
     * Set a character limit for the displayed text
     *
     * @param int $limit Maximum number of characters to display
     * @return self
     */
    public function limit(int $limit): self
    {
        $this->entry['limit'] = $limit;

        return $this;
    }

    /**
     * Make the text copyable (adds copy to clipboard functionality)
     *
     * @return self
     */
    public function copyable(): self
    {
        $this->entry['copyable'] = true;

        return $this;
    }

    /**
     * Render content as markdown
     *
     * @return self
     */
    public function markdown(): self
    {
        $this->entry['type'] = 'markdown';

        return $this;
    }

    /**
     * Render content as HTML
     *
     * @return self
     */
    public function html(): self
    {
        $this->entry['type'] = 'html';

        return $this;
    }
}