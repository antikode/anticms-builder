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

    /**
     * Create a separator item for visual grouping of actions
     */
    public static function separator(): array
    {
        return [
            'type' => 'separator',
        ];
    }

    /**
     * Set a callback function to be executed with the current record
     * The callback receives the record instance and can dynamically compute values
     * 
     * @param callable $callback Function that receives ($record) and returns a value
     * @param string $property Property name to store the callback result (e.g., 'route', 'hide', 'disabled')
     */
    public function using(callable $callback, string $property = 'route'): self
    {
        $this->action['callbacks'][$property] = $callback;

        return $this;
    }

    /**
     * Set the action type (action, delete, callback, separator)
     */
    public function type(string $type): self
    {
        $this->action['type'] = $type;

        return $this;
    }

    /**
     * Set custom data to be passed to the action
     */
    public function data(array|callable $data): self
    {
        $this->action['data'] = $data;

        return $this;
    }

    /**
     * Set the route key for client-side routing
     */
    public function routeKey(string $routeKey): self
    {
        $this->action['routeKey'] = $routeKey;

        return $this;
    }

    /**
     * Set custom confirmation text
     */
    public function confirmText(string $text): self
    {
        $this->action['confirmText'] = $text;
        $this->action['confirmation'] = true;

        return $this;
    }

    /**
     * Set custom class name for styling
     */
    public function className(string $className): self
    {
        $this->action['className'] = $className;

        return $this;
    }
}
