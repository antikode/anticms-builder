<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<InputField>
 */
class InputField extends FieldType
{
    protected string $type = 'input';

    /** @return InputField */
    public function max(int $max = 255): static
    {
        $this->attributes['max'] = $max;
        $this->attributes['maxLength'] = $max;

        return $this;
    }

    /** @return InputField */
    public function min(int $min = 255): static
    {
        $this->attributes['min'] = $min;
        $this->attributes['minLength'] = $min;

        return $this;
    }

    /** @return InputField */
    public function type(string $type): static
    {
        $this->attributes['type'] = $type;

        return $this;
    }

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'type' => 'text',
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
            'defaultValue' => '',
            'value' => '',
        ];
    }
}
