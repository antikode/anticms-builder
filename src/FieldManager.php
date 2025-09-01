<?php

namespace AntiCmsBuilder;

use AntiCmsBuilder\FieldTypes\FileField;
use AntiCmsBuilder\FieldTypes\ImageField;
use AntiCmsBuilder\FieldTypes\InputField;
use AntiCmsBuilder\FieldTypes\MediaField;
use AntiCmsBuilder\FieldTypes\RepeaterField;
use AntiCmsBuilder\FieldTypes\TextareaField;
use AntiCmsBuilder\FieldTypes\ToggleField;

use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class FieldManager
{
    public static function getAvailableFields()
    {
        return ['input', 'image', 'textarea', 'repeater', 'toggle', 'file', 'media', 'relationship', 'post_related'];
    }

    public function addField(): array
    {
        $fieldType = select('Choose Field Type', static::getAvailableFields());

        $fieldClass = match ($fieldType) {
            'input' => InputField::class,
            'image' => ImageField::class,
            'textarea' => TextareaField::class,
            'repeater' => RepeaterField::class,
            'toggle' => ToggleField::class,
            'file' => FileField::class,
            'media' => MediaField::class,
            default => InputField::class
        };

        $field = new $fieldClass(text('Enter Field Name'), text('Enter Field Label'));
        $field->setAttributes();

        return $field->control([]);
    }

    public function editField(array &$fields): void
    {
        if (empty($fields)) {
            warning('⚠ No fields available to edit.');

            return;
        }

        $fieldIndex = select('Choose field to edit:', array_map(fn ($f, $i) => "#$i {$f['name']}", $fields, array_keys($fields)));
        $index = intval(explode('#', explode(' ', $fieldIndex)[0])[1]);

        info('Editing field: '.$fields[$index]['name']);

        $fieldType = select(
            label: 'Choose Field Type',
            options: static::getAvailableFields(),
            default: $fields[$index]['field']
        );

        $fieldClass = match ($fieldType) {
            'input' => InputField::class,
            'image' => ImageField::class,
            'textarea' => TextareaField::class,
            'repeater' => RepeaterField::class,
            'toggle' => ToggleField::class,
            'file' => FileField::class,
            'media' => MediaField::class,
            default => InputField::class
        };
        $fieldClass = new $fieldClass(
            text(
                label: 'Enter new Field Name',
                default: $fields[$index]['name']
            ),
            text(
                label: 'Enter new Field Label',
                default: $fields[$index]['label']
            )
        );

        $fieldClass->setAttributes();
        $fields[$index] = $fieldClass->control($fields[$index]);
    }
}
