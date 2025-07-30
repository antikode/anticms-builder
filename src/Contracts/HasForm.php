<?php

namespace AntiCmsBuilder\Contracts;

interface HasForm
{
    public function getFormFieldsAttribute(): array;
    
    public function setFormFieldsAttribute($value): void;
}