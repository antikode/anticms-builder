<?php

namespace AntiCmsBuilder\Tests\Unit\FieldTypes;

use AntiCmsBuilder\FieldTypes\SelectField;
use AntiCmsBuilder\Tests\TestCase;

class SelectFieldTest extends TestCase
{
    public function test_can_create_select_field_instance()
    {
        $field = SelectField::make();
        
        $this->assertInstanceOf(SelectField::class, $field);
    }

    public function test_select_field_has_correct_default_type()
    {
        $field = SelectField::make()->name('test_field');
        $array = $field->toArray();
        
        $this->assertEquals('select', $array['field']);
    }

    public function test_can_set_options()
    {
        $options = [
            ['value' => '1', 'label' => 'Option 1'],
            ['value' => '2', 'label' => 'Option 2']
        ];
        
        $field = SelectField::make()->name('test_field')->options($options);
        $array = $field->toArray();
        
        $this->assertEquals($options, $array['attribute']['options']);
    }

    public function test_has_default_attributes()
    {
        $field = SelectField::make()->name('test_field');
        $array = $field->toArray();
        
        $expectedDefaults = [
            'options' => [],
            'defaultValue' => '',
            'value' => '',
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
        ];
        
        foreach ($expectedDefaults as $key => $value) {
            $this->assertArrayHasKey($key, $array['attribute']);
            $this->assertEquals($value, $array['attribute'][$key]);
        }
    }

    public function test_options_method_returns_self_for_chaining()
    {
        $field = SelectField::make();
        $options = [['value' => '1', 'label' => 'Option 1']];
        
        $this->assertSame($field, $field->options($options));
    }

    public function test_can_chain_methods_with_options()
    {
        $options = [
            ['value' => 'active', 'label' => 'Active'],
            ['value' => 'inactive', 'label' => 'Inactive']
        ];
        
        $field = SelectField::make()
            ->name('status')
            ->label('Status')
            ->options($options)
            ->placeholder('Select status')
            ->required();
        
        $array = $field->toArray();
        
        $this->assertEquals('status', $array['name']);
        $this->assertEquals('Status', $array['label']);
        $this->assertEquals($options, $array['attribute']['options']);
        $this->assertEquals('Select status', $array['attribute']['placeholder']);
        $this->assertTrue($array['attribute']['is_required']);
    }

    public function test_empty_options_array_by_default()
    {
        $field = SelectField::make()->name('test_field');
        $array = $field->toArray();
        
        $this->assertIsArray($array['attribute']['options']);
        $this->assertEmpty($array['attribute']['options']);
    }
}