<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<CustomField>
 */
class CustomField extends FieldType
{
    protected string $type = 'custom_field';

    protected string $keyName = '';

    protected int $sectionNumber = 0;

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'min' => 1,
            'max' => 5,
            'fields' => [],
        ];
    }

    public function name(string $name): static
    {
        $this->keyName = $name;

        return $this;
    }

    public function sectionNumber(int $sectionNumber): static
    {
        $this->sectionNumber = $sectionNumber;

        return $this;
    }

    /** @param  array<FieldType>  $fields */
    public function fields(array $fields): static
    {
        $this->attributes['fields'] = $fields;

        return $this;
    }

    public function toArray(): array
    {
        $build = [
            'keyName' => $this->keyName,
            'label' => $this->label != '' ? $this->label : str($this->keyName)->title(),
            'section' => $this->sectionNumber,
            'fields' => $this->attributes['fields'],
        ];

        return $build;
    }
}
