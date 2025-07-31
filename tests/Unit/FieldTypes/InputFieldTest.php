<?php

namespace AntiCmsBuilder\Tests\Unit\FieldTypes;

use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\Tests\TestCase;

class InputFieldTest extends TestCase
{
    public function test_can_create_input_field_instance()
    {
        $field = InputField::make();

        $this->assertInstanceOf(InputField::class, $field);
    }

    public function test_input_field_has_correct_default_type()
    {
        $field = InputField::make()->name('test_field');
        $array = $field->toArray();

        $this->assertEquals('input', $array['field']);
        $this->assertEquals('text', $array['attribute']['type']);
    }

    public function test_can_set_max_length()
    {
        $field = InputField::make()->name('test_field')->max(100);
        $array = $field->toArray();

        $this->assertEquals(100, $array['attribute']['max']);
        $this->assertEquals(100, $array['attribute']['maxLength']);
    }

    public function test_can_set_min_length()
    {
        $field = InputField::make()->name('test_field')->min(5);
        $array = $field->toArray();

        $this->assertEquals(5, $array['attribute']['min']);
        $this->assertEquals(5, $array['attribute']['minLength']);
    }

    public function test_can_set_input_type()
    {
        $field = InputField::make()->name('test_field')->type('email');
        $array = $field->toArray();

        $this->assertEquals('email', $array['attribute']['type']);
    }

    public function test_can_chain_methods()
    {
        $field = InputField::make()
            ->name('email')
            ->label('Email Address')
            ->type('email')
            ->placeholder('Enter your email')
            ->required()
            ->max(255)
            ->min(5);

        $array = $field->toArray();

        $this->assertEquals('email', $array['name']);
        $this->assertEquals('Email Address', $array['label']);
        $this->assertEquals('email', $array['attribute']['type']);
        $this->assertEquals('Enter your email', $array['attribute']['placeholder']);
        $this->assertTrue($array['attribute']['is_required']);
        $this->assertEquals(255, $array['attribute']['max']);
        $this->assertEquals(5, $array['attribute']['min']);
    }

    public function test_has_default_attributes()
    {
        $field = InputField::make()->name('test_field');
        $array = $field->toArray();

        $expectedDefaults = [
            'type' => 'text',
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
            'defaultValue' => '',
            'value' => '',
        ];

        foreach ($expectedDefaults as $key => $value) {
            $this->assertArrayHasKey($key, $array['attribute']);
            $this->assertEquals($value, $array['attribute'][$key]);
        }
    }

    public function test_max_and_min_return_self_for_chaining()
    {
        $field = InputField::make();

        $this->assertSame($field, $field->max(100));
        $this->assertSame($field, $field->min(5));
        $this->assertSame($field, $field->type('number'));
    }
}
