<?php

namespace AntiCmsBuilder\Tests\Unit;

use AntiCmsBuilder\FieldService;
use AntiCmsBuilder\Tests\Support\TestModel;
use AntiCmsBuilder\Tests\TestCase;
use App\Services\CustomFieldService;
use Mockery;

class FieldServiceTest extends TestCase
{
    private FieldService $fieldService;
    private TestModel $testModel;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fieldService = new FieldService();
        $this->testModel = new TestModel();
    }

    public function test_can_instantiate_field_service()
    {
        $this->assertInstanceOf(FieldService::class, $this->fieldService);
    }

    public function test_template_form_fields_with_empty_form()
    {
        $result = $this->fieldService->templateFormFields([], $this->testModel);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_template_form_fields_filters_forms_without_fields()
    {
        $form = [
            ['keyName' => 'section1'], // No fields property
            ['keyName' => 'section2', 'fields' => []] // Empty fields
        ];
        
        $result = $this->fieldService->templateFormFields($form, $this->testModel);
        
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function test_template_form_fields_processes_non_multilanguage_fields()
    {
        // Mock CustomFieldService
        $mockCustomFieldService = Mockery::mock(CustomFieldService::class);
        $mockCustomFieldService->shouldReceive('customFieldHandler')
            ->once()
            ->with(
                Mockery::type(TestModel::class),
                Mockery::any(),
                'test_section',
                Mockery::any()
            )
            ->andReturn('processed_value');
        
        $this->app->instance(CustomFieldService::class, $mockCustomFieldService);

        $form = [
            [
                'keyName' => 'test_section',
                'fields' => [
                    [
                        'name' => 'test_field',
                        'multilanguage' => false
                    ]
                ]
            ]
        ];
        
        $result = $this->fieldService->templateFormFields($form, $this->testModel);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('cf__test_section__test_field', $result);
        $this->assertEquals('processed_value', $result['cf__test_section__test_field']);
    }

    public function test_template_form_fields_processes_multilanguage_fields()
    {
        // Mock Translation model
        $mockTranslation = Mockery::mock('alias:\App\Models\Translations\Translation');
        $mockTranslation->shouldReceive('getLanguages')
            ->once()
            ->andReturn([
                'languages' => [
                    ['code' => 'en'],
                    ['code' => 'id']
                ]
            ]);

        // Mock CustomFieldService
        $mockCustomFieldService = Mockery::mock(CustomFieldService::class);
        $mockCustomFieldService->shouldReceive('customFieldHandler')
            ->twice() // Called for each language
            ->andReturn('processed_multilang_value');
        
        $this->app->instance(CustomFieldService::class, $mockCustomFieldService);

        $form = [
            [
                'keyName' => 'test_section',
                'fields' => [
                    [
                        'name' => 'test_field',
                        'multilanguage' => true
                    ]
                ]
            ]
        ];
        
        $result = $this->fieldService->templateFormFields($form, $this->testModel);
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('translations', $result);
        $this->assertArrayHasKey('en', $result['translations']);
        $this->assertArrayHasKey('id', $result['translations']);
        $this->assertArrayHasKey('cf__test_section__test_field', $result['translations']['en']);
        $this->assertArrayHasKey('cf__test_section__test_field', $result['translations']['id']);
    }

    public function test_template_form_fields_handles_multiple_sections_and_fields()
    {
        // Mock CustomFieldService
        $mockCustomFieldService = Mockery::mock(CustomFieldService::class);
        $mockCustomFieldService->shouldReceive('customFieldHandler')
            ->times(3) // Called for each field
            ->andReturn('processed_value');
        
        $this->app->instance(CustomFieldService::class, $mockCustomFieldService);

        $form = [
            [
                'keyName' => 'section1',
                'fields' => [
                    ['name' => 'field1', 'multilanguage' => false],
                    ['name' => 'field2', 'multilanguage' => false]
                ]
            ],
            [
                'keyName' => 'section2', 
                'fields' => [
                    ['name' => 'field3', 'multilanguage' => false]
                ]
            ]
        ];
        
        $result = $this->fieldService->templateFormFields($form, $this->testModel);
        
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertArrayHasKey('cf__section1__field1', $result);
        $this->assertArrayHasKey('cf__section1__field2', $result);
        $this->assertArrayHasKey('cf__section2__field3', $result);
    }

    public function test_field_name_formatting_replaces_spaces_with_underscores()
    {
        // Mock CustomFieldService
        $mockCustomFieldService = Mockery::mock(CustomFieldService::class);
        $mockCustomFieldService->shouldReceive('customFieldHandler')
            ->once()
            ->andReturn('processed_value');
        
        $this->app->instance(CustomFieldService::class, $mockCustomFieldService);

        $form = [
            [
                'keyName' => 'test section',
                'fields' => [
                    ['name' => 'test field', 'multilanguage' => false]
                ]
            ]
        ];
        
        $result = $this->fieldService->templateFormFields($form, $this->testModel);
        
        $this->assertArrayHasKey('cf__test__section__test__field', $result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}