<?php

namespace AntiCmsBuilder\FieldTypes;

use AntiCmsBuilder\FieldTypes\Traits\SelectOptionTrait;

/**
 * @extends FieldType<MultiSelectField>
 */
class MultiSelectField extends FieldType
{
    use SelectOptionTrait;

    protected string $type = 'multi_select';

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'options' => [],
            'defaultValue' => '',
            'value' => '',
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
        ];
    }

    /** @return MultiSelectField */
    public function setOptions(array $options): static
    {
        $this->attributes['options'] = $options;

        return $this;
    }
}
