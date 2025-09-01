<?php

namespace AntiCmsBuilder\Forms;

use AntiCmsBuilder\Contracts\HasCustomField;
use AntiCmsBuilder\FieldService;
use AntiCmsBuilder\Resolver;
use App\Models\File;
use App\Models\Media;
use App\Models\Translations\Translation;
use App\Services\PostService;
use App\Services\TemplateService;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class FormBuilder
{
    public array $forms;

    public string $model;

    private bool $templateOnly = true;

    public $saveFunction = null;

    public $updateFunction = null;

    public $afterSave = null;

    public bool $disable = false;

    private array $rules = [];

    public static function make(string $model): self
    {
        return new self($model);
    }

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return new $this->model;
    }

    public function loadValues()
    {
        $this->templateOnly = false;

        return $this;
    }

    public function disable(): self
    {
        $this->disable = true;

        return $this;
    }

    private function optionsFromRelation(array $attributes)
    {
        $relationName = $attributes['relation']['relation'];
        $label = $attributes['relation']['label'];

        /** @var \Illuminate\Database\Eloquent\Model $instance */
        $instance = app($this->model);

        if (! method_exists($instance, $relationName)) {
            throw new \Exception("The {$relationName} relation is undefined.");
        }

        $relation = $instance->$relationName();
        $relatedClass = $relation->getRelated();

        $relationData = $relatedClass::when(str_contains($label, '.'), function ($query) use ($label) {
            [$relationKey, $field] = explode('.', $label);
            $query->with($relationKey);
        })
            ->when($attributes['relation']['query'] instanceof Closure, function (Builder $builder) use ($attributes) {
                if (is_callable($attributes['relation']['query'])) {
                    $query = $attributes['relation']['query'];

                    return $query($builder);
                }

                return $builder;
            })
            ->get()
            ->map(function ($item) use ($label) {
                if (str_contains($label, '.')) {
                    [$relationKey, $field] = explode('.', $label);

                    $translated = $item->$relationKey?->firstWhere('lang', app()->getLocale());
                    $labelValue = $translated?->$field ?? '—'; // Fallback if not found
                } else {
                    $labelValue = $item->$label;
                }

                return [
                    'value' => $item->id,
                    'label' => $labelValue,
                ];
            });

        return $relationData->toArray();
    }

    protected function buildForms(array $forms): array
    {
        $forms = Arr::map($forms, function ($form) {
            if (isset($form['attribute']['relation'])) {
                if (in_array($form['field'], ['multi_select', 'select'])) {
                    $form['attribute'] = [
                        ...$form['attribute'],
                        'relation' => [
                            ...$form['attribute']['relation'],
                            'class' => $this->model,
                        ],
                        'options' => ! $this->templateOnly ?
                            $this->optionsFromRelation($form['attribute'])
                            : [],
                    ];
                }
                $form['attribute'] = [
                    ...$form['attribute'],
                    'relation' => [
                        ...$form['attribute']['relation'],
                        'class' => $this->model,
                    ],
                ];
                unset($form['attribute']['relation']['label']);
            }

            return $form;
        });

        return $forms;
    }

    /**
     * Set or generate the form fields.
     *
     * This method accepts either:
     * - An array of fields directly
     * - A callback that can optionally accept `$record` and/or `$operation` as parameters, in any order.
     *
     * Supported injected parameters for the callback:
     * - `record` (Model|null): The Eloquent model instance, if available
     * - `operation` (string): The operation type, either 'create' or 'update'
     *
     * @return static
     */
    public function forms(array|callable $forms): self
    {
        if (is_array($forms)) {
            $this->forms = $this->buildForms($forms);
        }

        if (is_callable($forms)) {
            $args = $this->resolveParams($forms, $this->model);

            $this->forms = $this->buildForms(call_user_func_array($forms, $args));
        }

        return $this;
    }

    public function getForms(): array
    {
        return $this->forms;
    }

    /**
     * Set a custom save callback.
     *
     * This allows you to override the default save logic by providing a closure
     * that will be executed instead of the built-in `saveForm()` method.
     *
     * Example:
     * ```php
     * $formBuilder->save(function (Request $request) {
     *     // Custom logic to handle saving
     * });
     * ```
     *
     * @param  callable  $save  The callback function to handle saving.
     * @return static
     */
    public function save(callable $save): self
    {
        $this->saveFunction = $save;

        return $this;
    }

    /**
     * Handle saving a new form record.
     *
     * Automatically fills fields, handles relations, and returns the model.
     */
    public function saveForm(Request $request): ?Model
    {
        if ($this->saveFunction) {
            $save = $this->saveFunction;
            $model = $save($request);
        } else {
            $model = $this->model::create($request->only((new $this->model)->getFillable()));
            $model = $this->processFormRelations($model, $request);
        }

        if ($this->afterSave) {
            $afterSave = $this->afterSave;
            $args = $this->resolveParams($afterSave, $model, [
                'request' => $request,
                'model' => $model,
                'operation' => 'create',
            ]);

            call_user_func_array($afterSave, $args);
        }

        return $model;
    }

    /**
     * Set a custom update callback.
     *
     * This allows you to override the default update logic by providing a closure
     * that will be executed instead of the built-in `updateForm()` method.
     *
     * Example:
     * ```php
     * $formBuilder->update(function (Model $model, Request $request) {
     *     // Custom update logic
     * });
     * ```
     *
     * @param  callable  $update  The callback function to handle updating.
     * @return static
     */
    public function update(callable $update): self
    {
        $this->updateFunction = $update;

        return $this;
    }

    /**
     * Handle updating an existing form record.
     *
     * Automatically updates fillable fields and related data.
     *
     * @param  Model  $model
     */
    public function updateForm($model, Request $request): ?Model
    {
        if ($this->updateFunction) {
            $update = $this->updateFunction;
            $model = $update($request, $model);
        } else {
            $model->update($request->only((new $this->model)->getFillable()));
            $model = $this->processFormRelations($model, $request);
        }

        if ($this->afterSave) {
            $afterSave = $this->afterSave;
            $args = $this->resolveParams($afterSave, $model, [
                'request' => $request,
                'record' => $model,
                'operation' => 'update',
            ]);

            call_user_func_array($afterSave, $args);
        }

        return $model;
    }

    private function processFormRelations($model, Request $request): Model
    {
        $postService = new PostService;

        $this->saveTranslation($model, $request->all());
        $this->saveNotTranslations($model, $request);
        $this->saveMeta($model, $request, $postService);
        $this->saveCustomFields($model, $request, $postService);
        $this->handleRelationships($model, $request, $this->forms);

        return $model;
    }

    private function getMultipleLanguageField($forms): array
    {
        $form = Arr::where($forms, fn ($f) => isset($f['multilanguage']) && $f['multilanguage']);

        return $form;
    }

    private function getNoneMultipleLanguageField($forms): array
    {
        $form = Arr::where($forms, fn ($f) => isset($f['multilanguage']) && ! $f['multilanguage']);

        return $form;
    }

    private function saveNotTranslations($model, Request $request): Model
    {
        $forms = array_filter($this->forms, fn ($f) => ! isset($f['fields']));
        $form = Arr::where($forms, fn ($f) => isset($f['multilanguage']) && ! $f['multilanguage']);
        foreach ($form as $component) {
            if ($component['field'] != 'image' && $component['field'] != 'repeater') {
                // TODO: handle for builder form that not using translation
            }
            // TODO:
            // $repeaterForm = array_values(Arr::where($forms, fn ($f) => $f['field'] == 'repeater'));
            // foreach ($repeaterForm as $component) {
            //     $fields = $component['attribute']['fields'];
            //     $noneMultilanguageForm = $this->getNoneMultipleLanguageField($fields);
            //
            //     $multilanguageForm = $this->getMultipleLanguageField($fields);
            //     foreach ($request->get($component['name']) as $field) {
            //         $translationForm = $this->buildTranslationFields($field, $multilanguageForm);
            //         dd($translationForm, $field, $component['name'], $multilanguageForm);
            //     }
            //
            //     $this->saveTranslation($model, $multilanguageForm);
            //     dd('OK');
            // }

            if ($component['field'] == 'image') {
                $file = File::find($request->get($component['name']));
                if ($model instanceof HasMedia && $file) {
                    $customProperties = [
                        'fileId' => $request->get($component['name']),
                    ];
                    if ($request->has($component['name'].'_alt')) {
                        $customProperties['alt'] = $request->get($component['name'].'_alt');
                    }
                    if ($model->media->where('collection_name', $component['name'])->isNotEmpty()) {
                        $model->media()->where('collection_name', $component['name'])->delete();
                    }
                    $model->addMediaFromUrl($file->getFirstMediaUrl('file'))
                        ->withCustomProperties($customProperties)
                        ->toMediaCollection($component['name']);
                } else {
                    if ($model?->media?->where('collection_name', $component['name'])->isNotEmpty() && ! $request->get($component['name'])) {
                        $model->media()->where('collection_name', $component['name'])->delete();
                    }
                    if ($file) {
                        $model->{$component['name']} = $file->id;
                    } else {
                        // Log::warning('File not found for ID: '.$request->get($component['name']), [
                        //     'model' => $model,
                        //     'component' => $component,
                        // ]);
                    }
                }
            }
            // TODO: handle for repeater
        }

        return $model;
    }

    private function saveMeta($model, Request $request, PostService $postService): Model
    {
        if (method_exists($model, 'meta')) {
            $postService->updateMetaPost($request, $model);
        }

        return $model;
    }

    private function saveCustomFields($model, Request $request, PostService $postService): Model
    {
        if ($model instanceof HasCustomField) {
            $model->customFields()->delete();
            $postService->customFieldHandler($model, $request);
        }

        return $model;
    }

    private function relationForms(): Collection
    {
        return collect($this->forms)->filter(function ($form) {
            return isset($form['attribute']['relation']);
        })->values();
    }

    /**
     * Process and sync model relationships based on form definitions.
     *
     * Supports BelongsTo, BelongsToMany, and repeater relations.
     *
     * @param  Model  $model
     */
    private function handleRelationships($model, Request $request, array $forms, bool $isFromRepeater = false): void
    {
        try {
            foreach ($forms as $form) {
                if (! isset($form['attribute']['relation']) && ! $isFromRepeater) {
                    continue;
                }

                $relationInfo = $form['attribute']['relation'];
                $relationName = $relationInfo['relation'];

                if (! method_exists($model, $relationName)) {
                    continue;
                }

                $relationMethod = $model->{$relationName}();

                if ($form['field'] === 'repeater' && isset($form['attribute']['fields'])) {
                    $formName = $form['attribute']['relation']['form_name'];
                    // Handle case where form_name is boolean true (default) - use the foreign key
                    if ($formName === true) {
                        $formName = $relationMethod->getForeignKeyName();
                    }
                    if ($relationMethod instanceof HasMany) {
                        $ids = [];
                        $sortHasMany = 0;
                        $relationMethod->delete();
                        foreach ($request->get($form['name']) ?? [] as $valuesFieldKey => $valuesField) {
                            if (count($valuesField) == 0 && $valuesField != null) {
                                continue;
                            }
                            $relationModel = $relationMethod->getModel()::class;
                            foreach ($valuesField as $valueKey => $value) {
                                if ($valueKey != 'translations') {
                                    $field = Arr::first($form['attribute']['fields'], fn ($f) => $f['name'] == $valueKey);
                                    if (isset($field['field']) && $field['field'] == 'image') {
                                        /** @var File $file */
                                        $file = File::find($value);
                                        if ($file) {
                                            /** @var Media $media */
                                            $media = $file->getMedia('file')->first();
                                            if (! $media) {
                                                Log::warning('Media not found for file ID: '.$value, [
                                                    'model' => $model,
                                                    'form' => $form,
                                                    'field' => $field,
                                                ]);

                                                continue;
                                            }
                                            // TODO: handle for media to save alt text
                                            $media->setCustomProperty('alt', $request->get($form['name'])[$valuesFieldKey][$valueKey.'_alt'] ?? '');
                                            $media->save();
                                        } else {
                                            Log::warning('File not found for ID: '.$value, [
                                                'model' => $model,
                                                'form' => $form,
                                                'field' => $field,
                                            ]);

                                            continue;
                                        }

                                    }
                                    $ids[$valueKey] = $value;
                                }
                            }
                            $ids['order'] = $sortHasMany;
                            $relationModel = $relationMethod->save(new $relationModel($ids));
                            foreach ($valuesField['translations'] ?? [] as $key => $translation) {
                                $relationModel->translations()->updateOrCreate(
                                    ['lang' => $key],
                                    array_merge($translation)
                                );
                            }
                            $sortHasMany++;
                        }
                    } else {
                        $ids = [];
                        $index = 0;
                        foreach ($request->get($form['name']) as $valuesField) {
                            $ids[$valuesField[$formName]] = array_merge($valuesField, [
                                // TODO: fix later
                                'order' => $index,
                            ]);
                            $index++;
                        }
                        $relationMethod->sync($ids);
                    }
                } elseif ($relationMethod instanceof BelongsToMany) {
                    $values = $request->{$form['name']};
                    $ids = is_string($values) ? json_decode($values, true) : (array) $values;
                    $relationMethod->sync($ids);
                } elseif ($relationMethod instanceof BelongsTo) {
                    $related = $relationMethod->getRelated()::find($request->get($form['name']));
                    $relationMethod->associate($related);
                }
            }

            $model->save();

        } catch (\Throwable $th) {
            Log::error('Error processing relationships', [
                'model' => $model,
                'forms' => $forms,
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            throw new \Exception('Error processing relationships: '.$th->getMessage(), 0, $th);
        }
    }

    private function buildTranslationFields(array $request, $forms): array
    {
        $translationFormData = collect(Translation::getLanguages()['languages'])
            ->map(function ($language) use ($request, $forms) {
                $notRepeaterForm = array_values(Arr::where($forms, fn ($f) => $f['field'] != 'repeater' && isset($f['multilanguage']) && $f['multilanguage']));
                $fields = [
                    'lang' => $language['code'],
                ];
                foreach ($notRepeaterForm as $component) {
                    if ($component['field'] != 'image') {
                        $fields[$component['name']] = $request['translations'][$language['code']][$component['name']] ?? null;
                    }
                }

                return $fields;
            });

        return $translationFormData->toArray();
    }

    public function saveTranslation($model, array $request): Model
    {
        $forms = array_filter($this->forms, fn ($f) => ! isset($f['fields']));
        if (method_exists($model, 'translations')) {
            $translationFormData = $this->buildTranslationFields($request, $forms);

            $model->translations()->delete();
            $model->translations()
                ->createMany($translationFormData);

            return $model;
        }

        return $model;
    }

    public function getFields($model)
    {
        $fields = [
            'translations' => [],
        ];
        $forms = $this->forms;

        if (method_exists($model, 'translations') && method_exists($model, 'meta')) {
            $translations = Translation::getLanguages()['languages'];
            foreach ($translations as $lang) {
                $translation = $model->translations->firstWhere('lang', $lang['code']);
                $fields['translations'][$lang['code']] = $translation?->toArray() ?? [];

                if (method_exists($this->model, 'meta')) {
                    $metaTranslations = [];
                    $metaNotTranslations = [];

                    foreach ($model->meta as $meta) {
                        if ($meta->translations->whereNotNull('lang')->isNotEmpty()) {
                            $metaTranslations[] = $meta->key;
                        } else {
                            $metaNotTranslations[] = $meta->key;
                        }
                    }

                    foreach ($metaTranslations as $meta) {
                        $translateValue = $model->meta->firstWhere('key', $meta)?->translations->firstWhere('lang', $lang['code'])?->value;
                        $fields['translations'][$lang['code']]['meta'][str($meta)->after('meta_')->value()] = $translateValue;
                    }

                    foreach ($metaNotTranslations as $meta) {
                        $translateValue = $model->meta->firstWhere('key', $meta)?->translations->firstWhere('lang', null)?->value;
                        $fields['meta'][str($meta)->after('meta_')->value()] = $translateValue;
                    }
                }
            }
        }

        if (in_array(InteractsWithMedia::class, class_uses($model))) {
            foreach ($forms as $f) {
                if (! isset($f['fields']) && $f['field'] === 'image') {
                    $media = $model->media->firstWhere('collection_name', $f['name']);
                    if ($media) {
                        $fields[$f['name']] = [
                            'fileId' => $media->custom_properties['fileId'] ?? $media->uuid,
                            'name' => $media->name,
                            'url' => $media->getUrl(),
                        ];
                    }
                }
            }
        }

        if ($model instanceof HasCustomField) {
            $cpt = new FieldService;
            $template = array_values(array_filter($forms, fn ($f) => isset($f['fields'])));
            $customFields = $cpt->templateFormFields($template, $model);
            if (isset($customFields['translations'])) {
                foreach ($fields['translations'] as $lang => $langFields) {
                    $fields['translations'][$lang] = array_merge(
                        $langFields,
                        $customFields['translations'][$lang] ?? []
                    );
                }
                unset($customFields['translations']);
            }
            $fields = array_merge($fields, $customFields);
        }

        $fields = array_merge($fields, $this->resolveRelationFields($model, $this->relationForms()->toArray()));

        return $fields;
    }

    protected function resolveRelationFields($model, array $relationForms): array
    {
        $fields = [];

        foreach ($relationForms as $form) {
            $relationName = $form['attribute']['relation']['relation'] ?? null;
            if (! $relationName || ! isset($model->{$relationName})) {
                continue;
            }

            $relatedData = $model->{$relationName};
            $formName = $form['name'];

            if ($form['field'] === 'repeater') {
                $fields[$formName] = collect($relatedData)->map(function ($item) use ($form) {
                    $entry = [];
                    foreach ($form['attribute']['fields'] as $subfield) {
                        $fieldName = $subfield['name'];

                        if ($subfield['field'] === 'repeater') {
                            // $nestedRelationName = $subfield['attribute']['relation']['relation'] ?? null;
                            // if ($nestedRelationName && $item->{$nestedRelationName}) {
                            //     $entry[$fieldName] = collect($item->{$nestedRelationName})->map(function ($nestedItem) {
                            //         return $nestedItem->toArray(); // Customize if needed
                            //     })->toArray();
                            // }
                        } elseif (in_array($subfield['field'], ['select', 'multi_select'])) {
                            $entry[$fieldName] = $item->getKey();
                            // $relation = $subfield['attribute']['relation']['relation'] ?? null;
                            // if ($relation && $item->{$relation}) {
                            //     $entry[$fieldName] = $item->{$relation} instanceof \Illuminate\Support\Collection
                            //         ? $item->{$relation}->pluck('id')->toArray()
                            //         : $item->{$relation}->id;
                            // }
                        } else {
                            if ($item->pivot) {
                                $entry[$fieldName] = $item->pivot->{$fieldName};
                            } else {
                                if ($subfield['multilanguage']) {
                                    if (method_exists($item, 'translations')) {
                                        foreach (Translation::getLanguages()['languages'] as $i => $language) {
                                            if ($item->translations->where('lang', $language['code'])->isNotEmpty()) {
                                                $translation = $item->translations->firstWhere('lang', $language['code']);
                                                $entry['translations'][$language['code']][$fieldName] = $translation->{$fieldName} ?? null;
                                            }
                                        }
                                    } else {
                                        $entry[$fieldName] = $item->{$fieldName} ?? null;
                                    }
                                } else {
                                    $entry[$fieldName] = $item->{$fieldName} ?? null;
                                }
                            }
                        }
                    }

                    return $entry;
                })->toArray();
            }

            // Handle non-repeater relations
            elseif (in_array($form['field'], ['select', 'multi_select'])) {
                if ($relatedData instanceof \Illuminate\Support\Collection) {
                    $fields[$formName] = $relatedData->pluck('id')->toArray();
                } else {
                    $fields[$formName] = $relatedData->id ?? null;
                }
            }
        }

        return $fields;
    }

    public function customFieldForms(): array
    {
        $forms = array_filter($this->forms, fn ($f) => isset($f['fields']));

        return array_values($forms);
    }

    public function getRules(): array
    {
        // TODO: this is redundant code, this code is already in \App\Services\TemplateService::validationRequest
        $validationBuilder = function ($field, $name) {
            $arr = [];
            $validation = [];
            if (isset($field->attribute->is_required)) {
                if ($field->attribute->is_required) {
                    $validation[] = 'required';
                } else {
                    $validation[] = 'nullable';
                }
            } else {
                $validation[] = 'nullable';
            }
            $templateService = new TemplateService;

            $validation = $templateService->fieldValidationRequest($field, $validation);
            if ($field->field === 'repeater') {
                foreach ($field->attribute->fields as $item) {
                    $validity = [];
                    if (isset($item->attribute->is_required)) {
                        if ($item->attribute->is_required) {
                            $validity[] = 'required';
                        } else {
                            $validity[] = 'nullable';
                        }
                    } else {
                        $validity[] = 'nullable';
                    }
                    $validity = $templateService->fieldValidationRequest($item, $validity);

                    if (isset($item->attribute->rules)) {
                        $validity = $item->attribute->rules;
                    }

                    if (isset($item->multilanguage) && $item->multilanguage == true) {
                        foreach (Translation::getLanguages()['languages'] as $i => $language) {
                            $arr[$name.'.*.translations.'.$language['code'].'.'.$item->name] = $validity;
                        }
                    } else {
                        $arr[$name.'.*.'.$item->name] = $validity;
                    }
                }
            }

            if (isset($field->attribute->rules)) {
                $validation = $field->attribute->rules;
            }

            if (isset($field->multilanguage) && $field->multilanguage == true) {
                foreach (Translation::getLanguages()['languages'] as $i => $language) {
                    $arr['translations.'.$language['code'].'.'.$name] = $validation;
                }
            } else {
                $arr[$name] = $validation;
            }

            return $arr;
        };
        $forms = json_decode(json_encode($this->forms));

        $rules = [];
        foreach ($forms as $field) {
            if (property_exists($field, 'name')) {
                $rule = $validationBuilder($field, $field->name);
            } else {
                foreach ($field->fields as $item) {
                    $name = str_replace(' ', '__', 'cf '.$field->keyName.' '.$item->name);
                    $rule = $validationBuilder($item, $name);
                    $rules = array_merge($rules, $rule);
                }
            }
            $rules = array_merge($rules, $rule);
        }

        $this->rules = $rules;

        return $rules;
    }

    /**
     * Set a callback to be executed after saving the model.
     *
     * This allows you to perform additional actions after the model has been saved,
     * such as logging, sending notifications, or any other custom logic.
     *
     * Example:
     * ```php
     * $formBuilder->afterSave(function (Request $request, Model $model) {
     *     // Custom logic after saving
     * });
     * ```
     * Supported injected parameters for the callback:
     * - `record` (Model|null): The Eloquent model instance, if available
     * - `operation` (string): The operation type, either 'create' or 'update'
     * - `request` (Request): The HTTP request instance containing the submitted data
     *
     * @param  callable  $afterSave  The callback function to execute after saving.
     * @return static
     */
    public function afterSave(callable $afterSave)
    {
        $this->afterSave = $afterSave;

        return $this;
    }

    private function resolveParams(callable $function, $model, $addedParams = []): array
    {
        return (new Resolver)->params($model, $function, $addedParams);
    }

    /**
     * TODO: This method is not used anywhere, remove it if not needed.
     * Get validation messages for the form fields.
     *
     * This method can be overridden to provide custom validation messages.
     *
     * @deprecated
     */
    public function getMessages(): array
    {
        $messages = [];

        return $messages;
    }

    public function getResolvedAttributes(): array
    {
        $attributes = [];
        foreach ($this->rules as $name => $rule) {
            $translatedName = str($name)->afterLast('.')->afterLast('__')->replace('_', ' ')->replace('-', ' ');
            $attributes[$name] = $translatedName->value();
        }

        return $attributes;
    }
}
