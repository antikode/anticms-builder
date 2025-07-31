<?php

namespace AntiCmsBuilder\Tests\Unit;

use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Tests\Support\TestModel;
use AntiCmsBuilder\Tests\TestCase;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class TableBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Load migrations for TestModel
        $this->loadMigrationsFrom(__DIR__.'/../Support/migrations');
    }

    public function test_can_create_table_builder_instance()
    {
        $tableBuilder = TableBuilder::make(TestModel::class);

        $this->assertInstanceOf(TableBuilder::class, $tableBuilder);
        $this->assertEquals(TestModel::class, $tableBuilder->model);
    }

    public function test_can_create_table_builder_via_constructor()
    {
        $tableBuilder = new TableBuilder(TestModel::class);

        $this->assertInstanceOf(TableBuilder::class, $tableBuilder);
        $this->assertEquals(TestModel::class, $tableBuilder->model);
    }

    public function test_constructor_initializes_default_tables_structure()
    {
        $tableBuilder = new TableBuilder(TestModel::class);

        $expectedStructure = [
            'rows' => [],
            'meta' => [],
        ];

        $this->assertEquals($expectedStructure, $tableBuilder->tables);
    }

    public function test_columns_method_sets_headers()
    {
        $tableBuilder = TableBuilder::make(TestModel::class);
        $columns = [
            ['column' => 'name', 'label' => 'Name'],
            ['column' => 'email', 'label' => 'Email'],
        ];

        $result = $tableBuilder->columns($columns);

        $this->assertSame($tableBuilder, $result);
        $this->assertEquals($columns, $tableBuilder->tables['headers']);
    }

    public function test_filters_method_sets_filter()
    {
        $tableBuilder = TableBuilder::make(TestModel::class);
        $filters = [
            ['name' => 'status', 'type' => 'select'],
        ];

        $result = $tableBuilder->filters($filters);

        $this->assertSame($tableBuilder, $result);
        $this->assertEquals($filters, $tableBuilder->tables['filter']);
    }

    public function test_query_method_applies_closure_to_default_query()
    {
        $tableBuilder = TableBuilder::make(TestModel::class);

        $result = $tableBuilder->query(function ($query) {
            return $query->where('status', 'active');
        });

        $this->assertSame($tableBuilder, $result);
        $this->assertInstanceOf(Builder::class, $tableBuilder->query);
    }

    public function test_can_chain_methods()
    {
        $columns = [['column' => 'name', 'label' => 'Name']];
        $filters = [['name' => 'status', 'type' => 'select']];

        $tableBuilder = TableBuilder::make(TestModel::class)
            ->columns($columns)
            ->filters($filters);

        $this->assertInstanceOf(TableBuilder::class, $tableBuilder);
        $this->assertEquals($columns, $tableBuilder->tables['headers']);
        $this->assertEquals($filters, $tableBuilder->tables['filter']);
    }

    public function test_default_query_returns_model_query_builder()
    {
        $tableBuilder = new TableBuilder(TestModel::class);

        // Use reflection to test private method
        $reflection = new \ReflectionClass($tableBuilder);
        $method = $reflection->getMethod('defaultQuery');
        $method->setAccessible(true);

        $result = $method->invoke($tableBuilder);

        $this->assertInstanceOf(Builder::class, $result);
    }

    public function test_build_method_returns_array_structure()
    {
        // Create a real request instance
        $request = Request::create('/', 'GET', ['limit' => 10]);
        $this->app->instance('request', $request);

        $tableBuilder = TableBuilder::make(TestModel::class);
        $tableBuilder->tables['headers'] = [
            ['column' => 'name', 'label' => 'Name'],
        ];
        $tableBuilder->tables['filter'] = [];

        $result = $tableBuilder->build();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertArrayHasKey('meta', $result);
    }
}
