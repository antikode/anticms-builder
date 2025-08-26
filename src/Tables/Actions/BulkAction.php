<?php

namespace AntiCmsBuilder\Tables\Actions;

class BulkAction extends TableAction
{
    public static function make(string $name): self
    {
        return new self($name);
    }

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->action['type'] = 'bulk';
        $this->action['bulk'] = true;
        $this->action['method'] = 'POST';
    }

    public function requiresSelection(bool $required = true): self
    {
        $this->action['requiresSelection'] = $required;

        return $this;
    }

    public function maxSelection(int $max): self
    {
        $this->action['maxSelection'] = $max;

        return $this;
    }

    public function minSelection(int $min): self
    {
        $this->action['minSelection'] = $min;

        return $this;
    }
}