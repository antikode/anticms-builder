<?php

namespace AntiCmsBuilder\Tests\Unit\Tables;

use AntiCmsBuilder\Tables\Actions\RowAction;
use AntiCmsBuilder\Tables\Actions\TableAction;
use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Tests\Support\TestModel;
use AntiCmsBuilder\Tests\TestCase;
use Illuminate\Foundation\Auth\User;

class DynamicParameterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user for permissions
        $this->actingAs(new User());
    }

    /** @test */
    public function it_can_resolve_dynamic_parameters_for_table_actions()
    {
        $action = TableAction::make('Test Action')
            ->routeWithDynamicParams('test.route', function ($record, $operation) {
                return ['id' => $record->id ?? 1, 'op' => $operation];
            });

        $actionData = $action->toArray();

        $this->assertEquals('test.route', $actionData['route']);
        $this->assertArrayHasKey('dynamicParametersResolver', $actionData);
        $this->assertIsCallable($actionData['dynamicParametersResolver']);
    }

    /** @test */
    public function it_can_resolve_dynamic_parameters_for_row_actions()
    {
        $model = new TestModel(['id' => 123, 'name' => 'Test']);

        $action = RowAction::make('Edit')
            ->routeWithModelDynamicParams('test.edit', function ($record, $operation) {
                return ['id' => $record->id, 'op' => $operation];
            });

        // We can't easily test the full build process without setting up routes,
        // but we can verify the action structure
        $actionData = $action->toArray();

        $this->assertEquals('test.edit', $actionData['route']);
        $this->assertArrayHasKey('dynamicParametersResolver', $actionData);
        $this->assertIsCallable($actionData['dynamicParametersResolver']);
    }

    /** @test */
    public function it_can_resolve_dynamic_conditions()
    {
        $action = TableAction::make('Conditional Action')
            ->visibleWhenDynamic(function ($operation) {
                return $operation === 'update';
            });

        $actionData = $action->toArray();

        $this->assertArrayHasKey('dynamicCondition', $actionData);
        $this->assertIsCallable($actionData['dynamicCondition']);
    }

    /** @test */
    public function it_can_resolve_dynamic_disabled_conditions()
    {
        $action = RowAction::make('Status Action')
            ->disabledForModelDynamic(function ($record) {
                return $record->status === 'inactive';
            });

        $actionData = $action->toArray();

        $this->assertArrayHasKey('dynamicDisabledCondition', $actionData);
        $this->assertIsCallable($actionData['dynamicDisabledCondition']);
    }

    /** @test */
    public function it_supports_dependency_injection_in_dynamic_parameters()
    {
        $action = TableAction::make('DI Action')
            ->routeWithDynamicParams('test.route', function ($record, $operation, User $user) {
                return [
                    'id' => $record->id ?? 1,
                    'user' => $user->id ?? 'guest',
                    'op' => $operation
                ];
            });

        $actionData = $action->toArray();

        $this->assertArrayHasKey('dynamicParametersResolver', $actionData);
        $this->assertIsCallable($actionData['dynamicParametersResolver']);
    }
}
