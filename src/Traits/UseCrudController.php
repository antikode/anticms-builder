<?php

namespace AntiCmsBuilder\Traits;

use AntiCmsBuilder\Forms\FormBuilder;
use AntiCmsBuilder\Tables\TableBuilder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

trait UseCrudController
{
    protected $actions = [];

    private function getDefaultSharedResource(): string
    {
        return str(class_basename($this->model))->snake('-')->lower();
    }

    private function getSharedResource(): string
    {
        return Inertia::getShared('resource', $this->getDefaultSharedResource());
    }

    private function setInertiaResource(): void
    {
        $tableName = (new $this->model)->getTable();

        Inertia::share('resource', $this->getSharedResource());
        Inertia::share('title', str(Inertia::getShared('title', $this->getDefaultSharedResource()))->replace('-', ' ')->replace('_', ' ')->title());
        Inertia::share('hasMeta', method_exists($this->model, 'meta'));
        Inertia::share('hasStatus', Schema::hasColumn($tableName, 'status'));
    }

    private function bootsrapp(): void
    {
        $this->setInertiaResource();
        $this->actions = $this->addAction();
    }

    protected function addAction(): array
    {
        return [];
    }

    protected function statusOptions(): array
    {
        return [
            ['id' => 'draft', 'name' => 'Draft'],
            ['id' => 'publish', 'name' => 'Publish'],
            ['id' => 'schedule', 'name' => 'Schedule'],
        ];
    }

    private function permisssionCheck(): array
    {
        $hasCreatePermission = true;
        $hasEditPermission = true;
        $hasDeletePermission = true;
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'create')) {
            $hasCreatePermission = Gate::allows('create', $this->model);
        }
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'update')) {
            $hasEditPermission = Gate::allows('update', $this->model);
        }
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'delete')) {
            $hasDeletePermission = Gate::allows('delete', $this->model);
        }

        return [
            'hasCreatePermission' => $hasCreatePermission,
            'hasEditPermission' => $hasEditPermission,
            'hasDeletePermission' => $hasDeletePermission,
        ];
    }

    /**
     * Detect relationships needed based on form field configurations
     */
    private function getFormRelationships($formBuilder): array
    {
        $relationships = [];
        $forms = $formBuilder->getForms();

        foreach ($forms as $form) {
            // Check for relation fields (SelectField, MultiSelectField with relations)
            if (isset($form['attribute']['relation']['relation'])) {
                $relationName = $form['attribute']['relation']['relation'];
                $relationships[] = $relationName;

                // If the relation field specifies a label field, it might need translations
                if (isset($form['attribute']['relation']['label']) &&
                    str_contains($form['attribute']['relation']['label'], 'translations.')) {
                    $relationships[] = $relationName.'.translations';
                }
            }

            // Check for repeater fields that might have relations
            if (isset($form['fields']) && is_array($form['fields'])) {
                $repeaterRelations = $this->getRepeaterRelationships($form);
                $relationships = array_merge($relationships, $repeaterRelations);
            }
        }

        return array_unique($relationships);
    }

    /**
     * Get relationships from repeater field configurations
     */
    private function getRepeaterRelationships($repeaterForm): array
    {
        $relationships = [];

        if (isset($repeaterForm['attribute']['relation'])) {
            $relationName = $repeaterForm['attribute']['relation'];
            $relationships[] = $relationName;

            // If repeater has multilanguage support, load translations
            if (isset($repeaterForm['multilanguage']) && $repeaterForm['multilanguage']) {
                $relationships[] = $relationName.'.translations';
            }
        }

        return $relationships;
    }

    /**
     * Override this method in controllers to specify additional relationships
     * that should be loaded for edit forms
     */
    protected function getAdditionalRelationships($model): array
    {
        return [];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $permissions = $this->permisssionCheck();

        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'viewAny')) {
            Gate::authorize('viewAny', $this->model);
        }

        $this->bootsrapp();

        $tables = $this
            ->tables(TableBuilder::make($this->model))
            ->build();

        return Inertia::render('Packages/AntiCmsBuilder/CRUD/Index', [
            'tables' => $tables,
            'permissions' => $permissions,
            'actions' => $this->actions,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'create')) {
            $this->authorize('create', $this->model);
        }

        $this->bootsrapp();

        $forms = $this->forms(FormBuilder::make($this->model)->loadValues())
            ->getForms();

        return Inertia::render('Packages/AntiCmsBuilder/CRUD/Create', [
            'customFields' => $forms,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    private function slugValidation($data = null)
    {
        if (! Inertia::getShared('slug')) {
            return [];
        }

        return [
            'required',
            'min:3',
            'max:255',
            Rule::unique((new $this->model)->getTable(), 'slug')
                ->ignore($data)
                ->withoutTrashed(),
        ];
    }

    private function metaValidation(): array
    {
        if (! method_exists($this->model, 'meta')) {
            return [];
        }

        return [
            'meta.canonical' => 'nullable|url',
            'meta.table_of_content' => 'nullable|boolean',
            'meta.image' => 'nullable|string',
            'meta.image_alt' => 'nullable|string',
            'meta.robots' => 'nullable|string',
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->bootsrapp();

        /** @var \App\Services\JsonBuilder\Forms\FormBuilder $formBuilder */
        $formBuilder = $this->forms(FormBuilder::make($this->model));
        $rules = $formBuilder->getRules();
        $defaultValidation = array_merge($this->defaultValidation(), $this->metaValidation());
        $request->validate(
            array_merge($defaultValidation, $rules),
            $formBuilder->getMessages(),
            $formBuilder->getResolvedAttributes()
        );
        $route = $this->getSharedResource().'.index';
        $message = __('Record has been created successfully');

        try {
            DB::beginTransaction();
            $formBuilder
                ->saveForm($request);

            $success = true;

            DB::commit();
        } catch (Exception $e) {
            $success = false;
            $message = $e->getMessage();
            Log::error($message);
            DB::rollBack();
            $route = $this->getSharedResource().'.create';
        }

        return to_route($route)->with('message', [
            'success' => $success,
            'message' => $message,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $this->bootsrapp();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $data = $this->model::find($id);
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'update')) {
            $this->authorize('update', $data);
        }
        $this->bootsrapp();

        $formBuilder = $this->forms(FormBuilder::make($this->model)->loadValues());
        $formRelationships = $this->getFormRelationships($formBuilder);

        $additionalRelationships = $this->getAdditionalRelationships($data);

        $relationships = array_unique(array_merge($formRelationships, $additionalRelationships));

        if (! empty($relationships)) {
            $data->load($relationships);
        }

        $fields = $formBuilder->getFields($data);
        $customFields = $formBuilder->getForms();

        return Inertia::render('Packages/AntiCmsBuilder/CRUD/Edit', [
            'resources' => $data,
            'fields' => $fields,
            'customFields' => $customFields,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    private function defaultValidation($data = null): array
    {
        return [
            'slug' => $this->slugValidation($data),
            // TODO: add logic to handle the right side form
            // 'status' => 'required|in:draft,publish,schedule',
            // 'published_at' => 'required_if:status,schedule|date',
            // 'user_id' => 'required|exists:users,id',
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $this->model::find($id);
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'update')) {
            $this->authorize('update', $data);
        }
        $this->bootsrapp();

        /** @var \App\Services\JsonBuilder\Forms\FormBuilder $formBuilder */
        $formBuilder = $this->forms(FormBuilder::make($this->model));
        $rules = $formBuilder->getRules();
        $defaultValidation = array_merge($this->defaultValidation($data), $this->metaValidation());

        $request->validate(
            array_merge($defaultValidation, $rules),
            $formBuilder->getMessages(),
            $formBuilder->getResolvedAttributes()
        );

        try {
            DB::beginTransaction();
            $route = to_route($this->getSharedResource().'.index');
            $formBuilder->updateForm($data, $request);

            $success = true;
            $message = __('Record has been updated successfully');
            DB::commit();
        } catch (\Throwable $th) {
            $success = false;
            $message = $th->getMessage();
            Log::error($message);
            DB::rollBack();
            $route = to_route($this->getSharedResource().'.edit', [
                'id' => $data->id,
            ]);
        }

        return $route->with('message', [
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function delete($id)
    {
        $data = $this->model::find($id);
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'delete')) {
            $this->authorize('delete', $data);
        }
        $this->bootsrapp();
        $tableName = ($data)->getTable();
        if (Schema::hasColumn($tableName, 'status')) {
            $data->update([
                'status' => 'draft',
            ]);
        }
        $data->delete();

        return back()
            ->with('message', [
                'success' => true,
                'message' => __('Record has been deleted successfully'),
            ]);
    }

    public function forceDelete(string $id)
    {
        $this->bootsrapp();
        if (Gate::getPolicyFor($this->model) && method_exists(Gate::getPolicyFor($this->model), 'delete')) {
            $this->authorize('delete', $this->model);
        }
        $this->model::where('id', $id)->withTrashed()->forceDelete();

        return back()
            ->with('message', [
                'success' => true,
                'message' => __('Record has been permanently deleted successfully'),
            ]);
    }

    public function restore(string $id)
    {
        $this->bootsrapp();
        $this->authorize('restore', $this->model);
        $this->model::where('id', $id)->withTrashed()->restore();

        return back();
    }

    // abstract public function forms(FormBuilder $builder): FormBuilder;

    abstract public function tables(TableBuilder $builder): TableBuilder;
}
