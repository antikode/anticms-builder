<?php

namespace AntiCmsBuilder\Tables\Actions;

use AntiCmsBuilder\Support\Color;

class TableAction
{
    protected array $action = [];

    public static function make(string $name): self
    {
        return new self($name);
    }

    public function __construct(string $name)
    {
        $this->action = [
            'name' => $name,
            'type' => 'button',
            'color' => 'primary',
            'icon' => null,
            'method' => 'GET',
            'route' => '',
            'form' => [],
            'confirmation' => false,
            'confirmationMessage' => 'Are you sure?',
            'hide' => false,
            'disabled' => false,
            'tooltip' => null,
            'permission' => null,
            'condition' => null,
            'target' => '_self',
            'useHref' => false,
            'downloadable' => false,
            'bulk' => false,
            'styles' => [
                'buttonClass' => null,
                'iconClass' => null,
                'size' => 'sm',
                'variant' => 'solid',
            ],
        ];
    }

    public function icon(string $icon): self
    {
        $this->action['icon'] = $icon;

        return $this;
    }

    public function color(string|Color $color): self
    {
        $this->action['color'] = $color instanceof Color ? $color->value : $color;

        return $this;
    }

    public function method(string $method): self
    {
        $this->action['method'] = strtoupper($method);

        return $this;
    }

    public function route(string|callable $route, array $parameters = []): self
    {
        $this->action['route'] = $route;
        $this->action['parameters'] = $parameters;

        return $this;
    }

    public function form(array $form): self
    {
        $this->action['form'] = $form;

        return $this;
    }

    public function confirmation(string $message = 'Are you sure?'): self
    {
        $this->action['confirmation'] = true;
        $this->action['confirmationMessage'] = $message;

        return $this;
    }

    public function hide(bool $hide = true): self
    {
        $this->action['hide'] = $hide;

        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->action['disabled'] = $disabled;

        return $this;
    }

    public function tooltip(string $tooltip): self
    {
        $this->action['tooltip'] = $tooltip;

        return $this;
    }

    public function requiresPermission(string $permission): self
    {
        $this->action['permission'] = $permission;

        return $this;
    }

    public function visibleWhen(callable $condition): self
    {
        $this->action['condition'] = $condition;

        return $this;
    }

    public function openInNewTab(): self
    {
        $this->action['target'] = '_blank';

        return $this;
    }

    public function useHref(bool $useHref = true): self
    {
        $this->action['useHref'] = $useHref;

        return $this;
    }

    public function downloadable(): self
    {
        $this->action['downloadable'] = true;

        return $this;
    }

    public function bulk(): self
    {
        $this->action['bulk'] = true;

        return $this;
    }

    public function size(string $size): self
    {
        $this->action['styles']['size'] = $size;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->action['styles']['variant'] = $variant;

        return $this;
    }

    public function buttonClass(string $class): self
    {
        $this->action['styles']['buttonClass'] = $class;

        return $this;
    }

    public function iconClass(string $class): self
    {
        $this->action['styles']['iconClass'] = $class;

        return $this;
    }

    public function styles(array $styles): self
    {
        $this->action['styles'] = array_merge($this->action['styles'], $styles);

        return $this;
    }

    public function toArray(): array
    {
        return $this->action;
    }
}
