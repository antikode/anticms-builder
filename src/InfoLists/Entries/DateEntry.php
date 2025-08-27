<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * DateEntry displays date and datetime values with customizable formatting.
 * Automatically handles Carbon instances and provides custom date formatting options.
 * 
 * @extends Entry<DateEntry>
 */
class DateEntry extends Entry
{
    /**
     * Initialize DateEntry with date type
     */
    public function __construct()
    {
        $this->entry['type'] = 'date';
    }

    /**
     * Set a custom date format for display
     *
     * @param string $format The date format string (e.g., 'Y-m-d', 'M d, Y', 'F j, Y g:i A')
     * @return self
     */
    public function dateFormat(string $format): self
    {
        $this->entry['date_format'] = $format;

        return $this;
    }
}