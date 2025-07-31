<?php

namespace AntiCmsBuilder\Contracts;

interface HasMeta
{
    public function getMetaAttribute(): array;

    public function setMetaAttribute($value): void;
}
