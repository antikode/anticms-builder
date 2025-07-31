<?php

namespace AntiCmsBuilder\Contracts;

interface HasCustomField
{
    public function getCustomFieldsAttribute(): array;

    public function setCustomFieldsAttribute($value): void;
}
