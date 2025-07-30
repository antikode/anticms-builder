<?php

namespace AntiCmsBuilder\FieldTypes;

/**
 * @extends FieldType<RepeaterField>
 */
class RepeaterField extends FieldType
{
    protected string $type = 'repeater';

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'min' => 1,
            'max' => 5,
            'fields' => [],
        ];
    }

    /** @param  array<FieldType>  $fields */
    public function fields(array $fields): static
    {
        $this->attributes['fields'] = $fields;

        return $this;
    }

    /** @return RepeaterField */
    public function min(int $min = 0): static
    {
        $this->attributes['min'] = $min;

        return $this;
    }

    /** @return RepeaterField */
    public function max(int $max = 1): static
    {
        $this->attributes['max'] = $max;

        return $this;
    }

    public function relation(string|bool $formName = true, ?string $relationName = null): static
    {
        $relationName = $relationName  ?? $this->name;
        // /** @var \Illuminate\Database\Eloquent\Model $instance */
        // $instance = app($class);
        //
        // if (! method_exists($instance, $relationName)) {
        //     throw new \Exception("The {$relationName} relation is undefined.");
        // }
        $this->attributes['relation'] = [
            // 'class' => $class,
            'relation' => $relationName,
            'form_name' => $formName
        ];

        return $this;
    }

    public function toArray(): array
    {
        return collect(parent::toArray())->except('multilanguage')->toArray();
    }
}
