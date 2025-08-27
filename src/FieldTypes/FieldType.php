<?php

namespace AntiCmsBuilder\FieldTypes;

use AntiCmsBuilder\FieldManager;
use Illuminate\Support\Arr;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * @template T
 */
abstract class FieldType
{
    protected string $name;

    protected string $label;

    protected string $type;

    protected string $fieldType;

    protected bool $multilanguage = false;

    protected array $attributes = [];

    protected FieldManager $fieldManager;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
        $this->fieldManager = new FieldManager;
        $this->setDefaultAttributes();
    }

    abstract public function setDefaultAttributes(): void;

    public function setAttributes(array $attributes = []): void
    {
        $this->attributes = $attributes;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label != '' ? $this->label : str($this->name)->replace('_', ' ')->replace('-', ' ')->title()->value(),
            'field' => $this->type,
            'multilanguage' => $this->multilanguage,
            'attribute' => $this->attributes,
        ];
    }

    /** @return T */
    public static function make(): static
    {
        /** @var T */
        return new static('','');
    }

    /** @return T */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /** @return T */
    public function name(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /** @return T */
    public function placeholder(string $placeholder): self
    {
        $this->attributes['placeholder'] = $placeholder;

        return $this;
    }

    /** @return T  */
    public function required(): self
    {
        $this->attributes['is_required'] = true;

        return $this;
    }

    /** @return T  */
    public function disabled(): self
    {
        $this->attributes['disabled'] = true;

        return $this;
    }

    /** @return T  */
    public function readonly(): self
    {
        $this->attributes['readonly'] = true;

        return $this;
    }

    /**
     * @param  mixed  $value
     * @return T
     */
    public function value($value): self
    {
        $this->attributes['value'] = $value;

        return $this;
    }

    /**
     * @param  mixed  $value
     * @return T
     */
    public function defaultValue($value): self
    {
        $this->attributes['defaultValue'] = $value;

        return $this;
    }

    /** @return T */
    public function caption(string $caption): self
    {
        $this->attributes['caption'] = $caption;

        return $this;
    }

    /** @return T */
    public function multilanguage(bool $multilanguage = true): self
    {
        $this->multilanguage = $multilanguage;

        return $this;
    }

    private function getAttrDefaultValue($fields, $attribute)
    {
        if (count($fields) == 0) {
            return null;
        }

        return Arr::get($fields['attribute'], $attribute) ?? null;
    }

    public function control(array $fields): array
    {
        $modifiedFields = [];
        foreach (collect($this->toArray()) as $key => $attributes) {
            switch ($key) {
                case 'attribute':
                    foreach ($attributes as $attribute => $value) {
                        switch ($attribute) {
                            case 'is_required':
                                $modifiedFields[$key][$attribute] = confirm(
                                    label: 'Do you want to make this field required?',
                                    default: $this->getAttrDefaultValue($fields, 'is_required') ?? $value
                                );
                                break;
                            case 'type':
                                $modifiedFields[$key][$attribute] = select(
                                    label: 'Enter Input Type',
                                    options: ['text', 'url', 'date', 'number', 'email', 'phone'],
                                    default: $this->getAttrDefaultValue($fields, 'type') ?? $value
                                );
                                break;
                            case 'fileSize':
                                $modifiedFields[$key][$attribute] = text(
                                    label: 'Enter maximum file size in KB',
                                    default: $this->getAttrDefaultValue($fields, 'fileSize') ?? $value
                                );
                                break;
                            case 'accept':
                                $value = $this->getAttrDefaultValue($fields, 'accept') ?? $value;
                                $modifiedFields[$key][$attribute] = multiselect(
                                    label: 'Enter accepted formats (e.g., image/png, image/jpeg)',
                                    options: ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/svg+xml', 'image/webp'],
                                    default: $value,
                                    required: 'You must select at least one format'
                                );
                                break;
                            case 'resolution':
                                foreach (['minWidth', 'maxWidth', 'minHeight', 'maxHeight'] as $dimension) {
                                    $modifiedFields[$key][$attribute][$dimension] = text(
                                        label: "Enter {$dimension}",
                                        default: $this->getAttrDefaultValue($fields, "resolution.{$dimension}") ?? $value[$dimension],
                                    );
                                }
                                break;
                            case 'fields':
                                $repeaterFields = $this->getAttrDefaultValue($fields, 'fields');
                                $this->fieldManager->editField($repeaterFields);
                                $modifiedFields[$key][$attribute] = $repeaterFields;
                                break;
                            default:
                                $modifiedFields[$key][$attribute] = text(
                                    label: "Enter the {$attribute} for this field",
                                    default: $this->getAttrDefaultValue($fields, $attribute) ?? $value
                                );
                                break;
                        }
                    }
                    break;
                case 'multilanguage':
                    $modifiedFields[$key] = confirm(
                        label: 'Do you want to make this field multilanguage?',
                        default: Arr::get($fields, 'multilanguage') ?? $attributes
                    );
                    break;
                default:
                    $modifiedFields[$key] = $attributes;
                    break;
            }
        }

        return $modifiedFields;
    }

    public function rules(array $rules): self
    {
        $this->attributes['rules'] = $rules;

        return $this;
    }

    public function messages(array $messages): self
    {
        $this->attributes['messages'] = $messages;

        return $this;
    }

    public function rule(string $rule, string $messages = ""): self
    {
        if (isset($this->attributes['rules']) === false) {
            $this->attributes['rules'] = [];
        }
        if (isset($this->attributes['messages']) === false) {
            $this->attributes['messages'] = [];
        }

        array_push($this->attributes['rules'], $rule);
        array_push($this->attributes['messages'], array_merge([
            $rule => $messages,
        ]));

        return $this;
    }
}
