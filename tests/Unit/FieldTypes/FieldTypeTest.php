<?php

namespace AntiCmsBuilder\Tests\Unit\FieldTypes;

use AntiCmsBuilder\FieldTypes\FieldType;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\Tests\TestCase;

class FieldTypeTest extends TestCase
{
    public function test_can_set_name()
    {
        $field = InputField::make()->name('test_field');
        $array = $field->toArray();
        
        $this->assertEquals('test_field', $array['name']);
    }

    public function test_can_set_label()
    {
        $field = InputField::make()->name('test_field')->label('Test Label');
        $array = $field->toArray();
        
        $this->assertEquals('Test Label', $array['label']);
    }

    public function test_auto_generates_label_from_name_when_empty()
    {
        $field = InputField::make()->name('user_first_name');
        $array = $field->toArray();
        
        $this->assertEquals('User First Name', $array['label']);
    }

    public function test_can_set_placeholder()
    {
        $field = InputField::make()->name('test_field')->placeholder('Enter value');
        $array = $field->toArray();
        
        $this->assertEquals('Enter value', $array['attribute']['placeholder']);
    }

    public function test_can_set_required()
    {
        $field = InputField::make()->name('test_field')->required();
        $array = $field->toArray();
        
        $this->assertTrue($array['attribute']['is_required']);
    }

    public function test_can_set_disabled()
    {
        $field = InputField::make()->name('test_field')->disabled();
        $array = $field->toArray();
        
        $this->assertTrue($array['attribute']['disabled']);
    }

    public function test_can_set_readonly()
    {
        $field = InputField::make()->name('test_field')->readonly();
        $array = $field->toArray();
        
        $this->assertTrue($array['attribute']['readonly']);
    }

    public function test_can_set_value()
    {
        $field = InputField::make()->name('test_field')->value('test value');
        $array = $field->toArray();
        
        $this->assertEquals('test value', $array['attribute']['value']);
    }

    public function test_can_set_default_value()
    {
        $field = InputField::make()->name('test_field')->defaultValue('default value');
        $array = $field->toArray();
        
        $this->assertEquals('default value', $array['attribute']['defaultValue']);
    }

    public function test_can_set_caption()
    {
        $field = InputField::make()->name('test_field')->caption('Help text');
        $array = $field->toArray();
        
        $this->assertEquals('Help text', $array['attribute']['caption']);
    }

    public function test_can_set_multilanguage()
    {
        $field = InputField::make()->name('test_field')->multilanguage(true);
        $array = $field->toArray();
        
        $this->assertTrue($array['multilanguage']);
    }

    public function test_multilanguage_defaults_to_false()
    {
        $field = InputField::make()->name('test_field');
        $array = $field->toArray();
        
        $this->assertFalse($array['multilanguage']);
    }

    public function test_can_disable_multilanguage()
    {
        $field = InputField::make()->name('test_field')->multilanguage(false);
        $array = $field->toArray();
        
        $this->assertFalse($array['multilanguage']);
    }

    public function test_can_set_rules()
    {
        $rules = ['required', 'string', 'max:255'];
        $field = InputField::make()->name('test_field')->rules($rules);
        $array = $field->toArray();
        
        $this->assertEquals($rules, $array['attribute']['rules']);
    }

    public function test_can_set_messages()
    {
        $messages = ['required' => 'This field is required'];
        $field = InputField::make()->name('test_field')->messages($messages);
        $array = $field->toArray();
        
        $this->assertEquals($messages, $array['attribute']['messages']);
    }

    public function test_can_add_single_rule_with_message()
    {
        $field = InputField::make()
            ->name('test_field')
            ->rule('required', 'This field is required')
            ->rule('max:255', 'Maximum 255 characters');
        
        $array = $field->toArray();
        
        $this->assertEquals(['required', 'max:255'], $array['attribute']['rules']);
        $this->assertIsArray($array['attribute']['messages']);
        $this->assertCount(2, $array['attribute']['messages']);
    }

    public function test_to_array_structure()
    {
        $field = InputField::make()
            ->name('test_field')
            ->label('Test Field')
            ->placeholder('Enter test')
            ->required()
            ->multilanguage(true);
        
        $array = $field->toArray();
        
        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('field', $array);
        $this->assertArrayHasKey('multilanguage', $array);
        $this->assertArrayHasKey('attribute', $array);
        
        $this->assertEquals('test_field', $array['name']);
        $this->assertEquals('Test Field', $array['label']);
        $this->assertEquals('input', $array['field']);
        $this->assertTrue($array['multilanguage']);
        $this->assertIsArray($array['attribute']);
    }

    public function test_all_fluent_methods_return_self()
    {
        $field = InputField::make();
        
        $this->assertSame($field, $field->name('test'));
        $this->assertSame($field, $field->label('Test'));
        $this->assertSame($field, $field->placeholder('Test'));
        $this->assertSame($field, $field->required());
        $this->assertSame($field, $field->disabled());
        $this->assertSame($field, $field->readonly());
        $this->assertSame($field, $field->value('test'));
        $this->assertSame($field, $field->defaultValue('test'));
        $this->assertSame($field, $field->caption('test'));
        $this->assertSame($field, $field->multilanguage());
        $this->assertSame($field, $field->rules([]));
        $this->assertSame($field, $field->messages([]));
        $this->assertSame($field, $field->rule('test', 'message'));
    }
}