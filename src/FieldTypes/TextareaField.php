<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<TextareaField>
 */
class TextareaField extends FieldType
{
    protected string $type = 'textarea';

    /** @return TextareaField */
    public function max(int $max = 255): static
    {
        $this->attributes['max'] = $max;

        return $this;
    }

    /** @return TextareaField */
    public function min(int $min = 255): static
    {
        $this->attributes['min'] = $min;

        return $this;
    }

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'rows' => 5,
            'cols' => 50,
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
            'defaultValue' => '',
            'value' => '',
        ];
    }

    public function rows(int $rows = 5): static
    {
        $this->attributes['rows'] = $rows;

        return $this;
    }

    public function cols(int $cols = 50): static
    {
        $this->attributes['cols'] = $cols;

        return $this;
    }
}
