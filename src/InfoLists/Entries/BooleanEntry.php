<?php

namespace AntiCmsBuilder\InfoLists\Entries;

/**
 * BooleanEntry displays boolean values with customizable labels and colors.
 * Provides options to customize the display of true/false states.
 * 
 * @extends Entry<BooleanEntry>
 */
class BooleanEntry extends Entry
{
    /**
     * Initialize BooleanEntry with boolean type
     */
    public function __construct()
    {
        $this->entry['type'] = 'boolean';
    }

    /**
     * Set the label displayed for true values
     *
     * @param string $label The label for true state (e.g., 'Yes', 'Active', 'Enabled')
     * @return self
     */
    public function trueLabel(string $label): self
    {
        $this->entry['true_label'] = $label;

        return $this;
    }

    /**
     * Set the label displayed for false values
     *
     * @param string $label The label for false state (e.g., 'No', 'Inactive', 'Disabled')
     * @return self
     */
    public function falseLabel(string $label): self
    {
        $this->entry['false_label'] = $label;

        return $this;
    }

    /**
     * Set the color for true values
     *
     * @param string $color The color for true state (e.g., 'green', 'success', '#10b981')
     * @return self
     */
    public function trueColor(string $color): self
    {
        $this->entry['true_color'] = $color;

        return $this;
    }

    /**
     * Set the color for false values
     *
     * @param string $color The color for false state (e.g., 'red', 'danger', '#ef4444')
     * @return self
     */
    public function falseColor(string $color): self
    {
        $this->entry['false_color'] = $color;

        return $this;
    }
}