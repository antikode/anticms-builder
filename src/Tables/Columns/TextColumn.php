<?php

namespace AntiCmsBuilder\Tables\Columns;

use Closure;
use Illuminate\Support\Str;

class TextColumn
{
    protected array $column = [];

    protected bool $hasCustomId = false;

    protected bool $hasCustomLabel = false;

    public static function make(): self
    {
        return new self;
    }

    public function name(string $name): self
    {
        $this->column['column'] = $name;

        if (! $this->hasCustomId) {
            $this->column['id'] = str_replace(['.', '->'], '_', $name);
        }

        if (! $this->hasCustomLabel) {
            $labelKey = collect(explode('.', $name))->last();
            $this->column['header'] = Str::title(str_replace('_', ' ', $labelKey));
        }

        return $this;
    }

    public function label(string $label): self
    {
        $this->column['header'] = $label;
        $this->hasCustomLabel = true;

        return $this;
    }

    public function id(string $id): self
    {
        $this->column['id'] = $id;
        $this->hasCustomId = true;

        return $this;
    }

    public function searchable(bool $state = true): self
    {
        $this->column['searchable'] = $state;

        return $this;
    }

    public function sortable(bool $state = true): self
    {
        $this->column['sortable'] = $state;

        return $this;
    }

    public function description(Closure $callback): self
    {
        $this->column['description'] = $callback;

        return $this;
    }

    public function format(Closure $callback): self
    {
        $this->column['format'] = $callback;

        return $this;
    }

    public function toArray(): array
    {
        return $this->column;
    }
}
