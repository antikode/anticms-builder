<?php

namespace AntiCmsBuilder\Tables\Actions;

class RowAction extends TableAction
{
    public static function make(string $name): self
    {
        return new self($name);
    }

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->action['type'] = 'row';
    }
}
