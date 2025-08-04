<?php

namespace AntiCmsBuilder\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface HasCustomField
{
    public function customFields(): \Illuminate\Database\Eloquent\Relations\MorphMany;

    public function getRootCustomFields(): Collection;
}
