<?php

namespace AntiCmsBuilder;

use AntiCmsBuilder\Contracts\HasCustomField;
use App\Services\CustomFieldService;

final class FieldService
{
    public function templateFormFields(array $form, HasCustomField $model): array
    {
        $form = json_decode(json_encode(array_filter($form, fn ($f) => isset($f['fields']))));
        $arr = [];
        foreach ($form as $component) {
            foreach ($component->fields as $field) {
                if (isset($field->multilanguage) && $field->multilanguage) {
                    foreach (\App\Models\Translations\Translation::getLanguages()['languages'] as $i => $language) {
                        $name = str_replace(' ', '__', 'cf '.$component->keyName.' '.$field->name);
                        $arr['translations'][$language['code']][$name] = app(CustomFieldService::class)
                            ->customFieldHandler(
                                model: $model,
                                customFields: $model->customFields,
                                keyName: $component->keyName,
                                field: $field,
                                lang: $language['code'],
                            );
                    }
                } else {
                    $arr[str_replace(' ', '__', 'cf '.$component->keyName.' '.$field->name)] = app(CustomFieldService::class)->customFieldHandler(
                        model: $model,
                        customFields: $model->customFields,
                        keyName: $component->keyName,
                        field: $field
                    );
                }
            }
        }

        return $arr;
    }
}
