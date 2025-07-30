<?php

namespace AntiCmsBuilder\Tests\Unit;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Tests\Support\TestModel;
use AntiCmsBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

class FormBuilderTest extends TestCase
{
    public function test_can_create_form_builder_instance()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        $this->assertInstanceOf(FormBuilder::class, $formBuilder);
        $this->assertEquals(TestModel::class, $formBuilder->model);
    }

    public function test_can_create_form_builder_via_constructor()
    {
        $formBuilder = new FormBuilder(TestModel::class);
        
        $this->assertInstanceOf(FormBuilder::class, $formBuilder);
        $this->assertEquals(TestModel::class, $formBuilder->model);
    }

    public function test_can_get_model_instance()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        $model = $formBuilder->getModel();
        
        $this->assertInstanceOf(TestModel::class, $model);
    }

    public function test_load_values_sets_template_only_to_false()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to check private property
        $reflection = new \ReflectionClass($formBuilder);
        $templateOnlyProperty = $reflection->getProperty('templateOnly');
        $templateOnlyProperty->setAccessible(true);
        
        // Initially should be true
        $this->assertTrue($templateOnlyProperty->getValue($formBuilder));
        
        // After loadValues() should be false
        $formBuilder->loadValues();
        $this->assertFalse($templateOnlyProperty->getValue($formBuilder));
    }

    public function test_load_values_returns_self_for_chaining()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        $result = $formBuilder->loadValues();
        
        $this->assertSame($formBuilder, $result);
    }

    public function test_disable_sets_disable_property_to_true()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to check property
        $reflection = new \ReflectionClass($formBuilder);
        $disableProperty = $reflection->getProperty('disable');
        $disableProperty->setAccessible(true);
        
        // Initially should be false
        $this->assertFalse($disableProperty->getValue($formBuilder));
        
        // After disable() should be true
        $formBuilder->disable();
        $this->assertTrue($disableProperty->getValue($formBuilder));
    }

    public function test_disable_returns_self_for_chaining()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        $result = $formBuilder->disable();
        
        $this->assertSame($formBuilder, $result);
    }

    public function test_forms_method_sets_forms_property()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        $forms = [
            ['field' => 'input', 'name' => 'test_field']
        ];
        
        $result = $formBuilder->forms($forms);
        
        $this->assertSame($formBuilder, $result);
        $this->assertEquals($forms, $formBuilder->forms);
    }

    public function test_can_chain_methods()
    {
        $forms = [
            ['field' => 'input', 'name' => 'test_field']
        ];
        
        $formBuilder = FormBuilder::make(TestModel::class)
            ->forms($forms)
            ->loadValues()
            ->disable();
        
        $this->assertInstanceOf(FormBuilder::class, $formBuilder);
        $this->assertEquals($forms, $formBuilder->forms);
    }

    public function test_throws_exception_for_undefined_relation()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The nonExistentRelation relation is undefined.');
        
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to test private method
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('optionsFromRelation');
        $method->setAccessible(true);
        
        $attributes = [
            'relation' => [
                'relation' => 'nonExistentRelation',
                'label' => 'name'
            ]
        ];
        
        $method->invoke($formBuilder, $attributes);
    }

    public function test_build_forms_handles_empty_array()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildForms');
        $method->setAccessible(true);
        
        $result = $method->invoke($formBuilder, []);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_build_forms_processes_forms_without_relations()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildForms');
        $method->setAccessible(true);
        
        $forms = [
            [
                'field' => 'input',
                'name' => 'test_field',
                'attribute' => ['placeholder' => 'Test placeholder']
            ]
        ];
        
        $result = $method->invoke($formBuilder, $forms);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals($forms[0], $result[0]);
    }

    public function test_build_forms_processes_forms_with_relations()
    {
        $formBuilder = FormBuilder::make(TestModel::class);
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildForms');
        $method->setAccessible(true);
        
        $forms = [
            [
                'field' => 'select',
                'name' => 'test_field',
                'attribute' => [
                    'relation' => [
                        'relation' => 'testRelation',
                        'label' => 'name'
                    ]
                ]
            ]
        ];
        
        $result = $method->invoke($formBuilder, $forms);
        
        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('attribute', $result[0]);
        $this->assertArrayHasKey('relation', $result[0]['attribute']);
        $this->assertEquals(TestModel::class, $result[0]['attribute']['relation']['class']);
        $this->assertArrayNotHasKey('label', $result[0]['attribute']['relation']);
    }
}