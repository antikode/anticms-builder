<?php

namespace AntiCmsBuilder\Tests\Support;

use AntiCmsBuilder\Contracts\HasCustomField;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class TestModel extends Model implements HasCustomField
{
    protected $table = 'test_models';

    protected $fillable = ['name', 'email', 'status'];

    public function customFields(): MorphMany
    {
        // Mock MorphMany relationship for testing
        return $this->morphMany('App\Models\CustomField\CustomField', 'model');
    }

    public function getRootCustomFields(): Collection
    {
        // Return Eloquent Collection instead of Support Collection
        return new Collection([
            (object) [
                'keyName' => 'test_section',
                'name' => 'test_field',
                'value' => 'test_value',
            ],
        ]);
    }

    // Mock custom fields attribute for FieldService testing
    public function getCustomFieldsAttribute(): array
    {
        return [
            [
                'keyName' => 'test_section',
                'name' => 'test_field',
                'value' => 'test_value',
            ],
        ];
    }

    public function setCustomFieldsAttribute($value): void
    {
        // Mock implementation for testing
    }
}
