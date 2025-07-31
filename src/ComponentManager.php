<?php

namespace AntiCmsBuilder;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

class ComponentManager
{
    protected FieldManager $fieldManager;

    public function __construct()
    {
        $this->fieldManager = new FieldManager;
    }

    public function createComponent(): array
    {
        $component = [
            'keyName' => text('Enter Component Key Name'),
            'label' => text('Enter Component Label'),
            'section' => text('Enter Section Number'),
            'fields' => [],
        ];

        while (confirm('Do you want to add a field?')) {
            $component['fields'][] = $this->fieldManager->addField();
        }

        return $component;
    }

    public function editComponent(array &$json): void
    {
        if (empty($json)) {
            echo '⚠ No components available to edit.';

            return;
        }

        $componentIndex = select(
            'Choose component to edit:',
            array_map(fn ($c, $i) => "#$i {$c['keyName']}", $json, array_keys($json))
        );

        $index = intval(explode('#', explode(' ', $componentIndex)[0])[1]);

        $json[$index]['keyName'] = text(
            label: 'Enter new Key Name',
            default: $json[$index]['keyName']
        );
        $json[$index]['label'] = text(
            label: 'Enter new Label',
            default: $json[$index]['label']
        );
        $json[$index]['section'] = text(
            label: 'Enter new Section',
            default: $json[$index]['section']
        );

        while (confirm('Do you want to edit fields?')) {
            $this->fieldManager->editField($json[$index]['fields']);
        }
    }

    public function sortComponents(array &$components): void
    {
        $sortBy = select('Sort by:', ['label', 'section']);
        $order = select('Order:', ['asc', 'desc']);

        SortHelper::sortComponents($components, $sortBy, $order);
    }
}
