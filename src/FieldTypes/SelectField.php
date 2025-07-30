<?php

namespace AntiCmsBuilder\FieldTypes;

use AntiCmsBuilder\FieldTypes\Traits\SelectOptionTrait;

/**
 * @extends FieldType<SelectField>
 */
class SelectField extends FieldType
{
    use SelectOptionTrait;

    protected string $type = 'select';

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

    /** @return SelectField */
    public function options(array $options): static
    {
        // $this->attributes['defaultValue'] = '';
        // array_push($options, [
        //     // 'value' => $this->,
        //     // 'label' => '',
        // ]);
        // dd($options);
        $this->attributes['options'] = $options;
        // "9ee7f5d1-4dca-4824-a205-e9174f5ccac7"

        return $this;
    }
}
