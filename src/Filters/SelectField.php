<?php

namespace AntiCmsBuilder\Filters;

use AntiCmsBuilder\FieldTypes\FieldType;
use AntiCmsBuilder\FieldTypes\Traits\SelectOptionTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @extends FieldType<SelectField>
 */
class SelectField extends FieldType
{
    use SelectOptionTrait;

    protected string $type = 'select';

    public function __construct($name, $label)
    {
        parent::__construct($name, $label);
        // $this->setDefaultAttributes();
    }

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'options' => [],
            'defaultValue' => '',
            'value' => '',
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
            'query' => function (Builder $query, $column = null, $value = null) {
                $query->where($column, $value);
            }
        ];
    }

    public function name(string $name): FieldType
    {
        $this->name = 'filter.'.$name;

        return $this;
    }

    /**
     * Set the query for the field.
     *
     * @return SelectField
     */
    public function query(callable $query): static
    {
        $this->attributes['query'] = $query;

        return $this;
    }

    /** @return SelectField */
    public function options(array $options): static
    {
        $this->attributes['options'] = $options;

        return $this;
    }
}
