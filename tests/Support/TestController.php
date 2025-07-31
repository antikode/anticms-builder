<?php

namespace AntiCmsBuilder\Tests\Support;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Tables\TableBuilder;
use AntiCmsBuilder\Traits\UseCrudController;

class TestController
{
    use UseCrudController;

    public string $model = TestModel::class;

    public function tables(TableBuilder $builder): TableBuilder
    {
        return $builder->columns([
            ['column' => 'name', 'label' => 'Name'],
            ['column' => 'email', 'label' => 'Email'],
        ]);
    }

    public function forms(FormBuilder $builder): FormBuilder
    {
        return $builder->forms([
            ['field' => 'input', 'name' => 'name', 'label' => 'Name'],
            ['field' => 'input', 'name' => 'email', 'label' => 'Email'],
        ]);
    }
}
