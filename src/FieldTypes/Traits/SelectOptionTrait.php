<?php

namespace AntiCmsBuilder\FieldTypes\Traits;

trait SelectOptionTrait
{
    public function loadOptionFromRelation(string $relationName, string $label, ?callable $query = null): static
    {
        $this->attributes['relation'] = [
            'label' => $label,
            'relation' => $relationName,
            'query' => $query,
        ];

        return $this;
    }

    public function searchable($searchable = true): static
    {
        $this->attributes['searchable'] = $searchable;

        return $this;
    }
}
