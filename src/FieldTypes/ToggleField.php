<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<ToggleField>
 */
class ToggleField extends FieldType
{
    protected string $type = 'toggle';

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'is_required' => false,
            'defaultValue' => false,
            'value' => false,
            'caption' => '',
        ];
    }

    public function toArray(): array
    {
        return collect(parent::toArray())->except('multilanguage')->toArray();
    }
}
