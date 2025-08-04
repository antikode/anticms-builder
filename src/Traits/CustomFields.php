<?php

namespace AntiCmsBuilder\Traits;

use Illuminate\Database\Eloquent\Collection;

trait CustomFields
{
    public function customFields(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(config('antin-cms-builder.models.custom_field', 'App\\Models\\CustomField\\CustomField'), 'model');
    }

    public function getRootCustomFields(): Collection
    {
        return $this->customFields()
            ->with('children')
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->get();
    }
}
