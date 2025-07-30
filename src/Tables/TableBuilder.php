<?php

namespace AntiCmsBuilder\Tables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;

final class TableBuilder
{
    public array $tables;

    public $query;

    public string $model;

    public static function make(string $model): self
    {
        return new self($model);
    }

    public function __construct(string $model)
    {
        $this->model = $model;
        $this->tables = [
            // 'filter' => [],
            'rows' => [],
            'meta' => [],
        ];
    }

    private function defaultQuery()
    {
        return $this->model::query();
    }

    public function columns(array $columns): self
    {
        $this->tables['headers'] = $columns;

        return $this;
    }

    public function filters(array $filters): self
    {
        $this->tables['filter'] = $filters;

        return $this;
    }

    public function query($query): self
    {
        $this->query = $query($this->defaultQuery());

        return $this;
    }

    public function build(): array
    {
        $query = $this->query ?? $this->defaultQuery();
        $request = request();
        $tables = $this->tables;

        $searchableColumns = Arr::pluck(Arr::where($tables['headers'], fn ($header) => isset($header['searchable']) && $header['searchable'] == true), 'column');
        if (count($searchableColumns) > 0) {
            $query->when($request->filled('q'), function (Builder $q) use ($request, $searchableColumns) {
                $q->where(function (Builder $query) use ($request, $searchableColumns) {
                    foreach ($searchableColumns as $column) {
                        $this->addNestedOrWhereHas($query, $column, $request->q);
                    }
                });
            });
        }

        if ($request->has(['field', 'direction'])) {
            $field = $request->field;
            $direction = $request->direction;

            $query = $this->applyDynamicSort($query, $field, $direction);
        }
        $hasFilter = Arr::where($request->all(), function ($value, $key) {
            if (str($key)->contains('filter_')) {
                return true;
            }

            return false;
        });

        if (count($hasFilter) > 0) {
            foreach ($tables['filter'] as $filter) {
                foreach ($hasFilter as $key => $value) {
                    if (str($filter['name'])->after('.') == str($key)->after('_')) {
                        $selectField = $filter;
                        $selectField['attribute']['query']($query, str($selectField['name'])->after('.'), $value);
                    }
                }
            }
        }

        $paging = $query->paginate($request->limit ?? 10);

        /** @var Model $item */
        foreach ($paging as $keyI => $item) {
            $tables['meta'] = $paging->toArray();
            foreach ($tables['headers'] as $key => $table) {
                $tables['headers'][$key]['accessorKey'] = $table['id'];
                if (str($table['column'])->contains('.')) {
                    $explode = str($table['column'])->explode('.');
                    /** @var Model|Collection<int, Model> $relation */
                    $relation = null;
                    foreach ($explode as $key => $value) {
                        if ($key != count($explode) - 1) {
                            if ($relation != null) {
                                $relation->load($value);
                                $relation = $relation->getRelation($value);
                            } else {
                                $item->load($value);
                                $relation = $item->getRelation($value);
                            }
                        }
                    }
                    $property = $explode[count($explode) - 1];
                    $relatedModel = $relation instanceof Collection ? $relation->first() : $relation;

                    $td = $relatedModel?->{$property} ?? null;

                    if (isset($table['format']) && $relatedModel?->{$property}) {
                        $td = $table['format']($relatedModel->{$property});
                    }
                } else {
                    $td = $item->{$table['column']};
                    if (isset($table['format'])) {
                        $td = $table['format']($item->{$table['column']});
                    }
                }
                if (isset($table['description'])) {
                    $table['description']($item);
                    $tables['rows'][$keyI][$table['id']] = [
                        'value' => $td,
                        'description' => $table['description']($item),
                    ];
                } else {
                    $tables['rows'][$keyI][$table['id']] = $td;
                }
            }
            $tables['rows'][$keyI]['id'] = $item->getKey();
            $tables['rows'][$keyI]['deleted_at'] = $item->deleted_at;
        }

        return $tables;
    }

    private function applyDynamicSort(Builder $query, string $field, string $direction): Builder
    {
        $parts = explode('.', $field);
        if (count($parts) === 1) {
            return $query->orderBy($field, $direction);
        }

        return $query;

        // TODO: fix the deep sort
    }

    private function addNestedOrWhereHas(Builder $query, string $column, string $value): void
    {
        $parts = explode('.', $column);

        if (count($parts) > 1) {
            $field = array_pop($parts);
            $relationPath = implode('.', $parts);

            $query->orWhereHas($relationPath, function ($q2) use ($field, $value) {
                $q2->whereRaw('LOWER('.$field.') LIKE ?', ['%'.strtolower($value).'%']);
            });
        } else {
            $query->orWhereRaw('LOWER('.$column.') LIKE ?', ['%'.strtolower($value).'%']);
        }
    }

    public function noActions($noAction = true): self
    {
        $this->tables['noAction'] = $noAction;

        return $this;
    }
}
