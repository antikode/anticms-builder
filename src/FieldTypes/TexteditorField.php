<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<TexteditorField>
 */
class TexteditorField extends FieldType
{
    protected string $type = 'editor';

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
}
