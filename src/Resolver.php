<?php

namespace AntiCmsBuilder;

use Illuminate\Database\Eloquent\Model;

class Resolver
{
    public function params(string|Model $model, callable $function, array $addedParams = []): array
    {
        $ref = new \ReflectionFunction(\Closure::fromCallable($function));
        $args = [];

        $modelInstance = $model instanceof Model ? $model : $this->getRecord($model);

        $availableParams = array_merge([
            'operation' => $this->getOperation(),
            'record' => $modelInstance,
        ], $addedParams);

        foreach ($ref->getParameters() as $key => $param) {
            $name = $param->getName();
            if ($param->getType() && ! $param->getType()->isBuiltin()) {
                $paramTypeName = $param->getType()->getName();
                $modelClassName = is_string($model) ? $model : $model::class;

                if ($paramTypeName == $modelClassName) {
                    $args[$key] = $modelInstance;
                } else {
                    $args[$key] = $availableParams[$name] ?? app($paramTypeName);
                }
            } else {
                $args[$key] = $availableParams[$name] ?? null;
            }
        }

        return $args;
    }

    private function getRecord(string $model): ?object
    {
        $record = new $model;
        if ($this->getOperation() == 'update') {
            $id = request()->route()->parameter('id');
            if ($id) {
                $record = $model::find($id);
            }
        }

        return $record;
    }

    private function getOperation(): string
    {
        $isEdit = false;
        if (in_array(strtoupper(request()->method()), ['PUT', 'PATCH']) || str(request()->route()->getAction('uses'))->contains('@edit')) {
            $isEdit = true;
        }

        return $isEdit ? 'update' : 'create';
    }
}
