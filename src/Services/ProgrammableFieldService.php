<?php

namespace AntiCmsBuilder\Services;

use AntiCmsBuilder\FieldTypes\ProgrammableField;
use Illuminate\Support\Facades\Route;

class ProgrammableFieldService
{
    protected array $registeredFields = [];
    protected array $componentRegistry = [];

    /**
     * Register a programmable field instance
     */
    public function register(string $fieldName, ProgrammableField $field): void
    {
        $this->registeredFields[$fieldName] = $field;
        $this->registerComponent($fieldName, $field->path());
        $field->registerRoutes();
    }

    /**
     * Register a custom JSX component
     */
    public function registerComponent(string $componentName, string $componentPath): void
    {
        $this->componentRegistry[$componentName] = $componentPath;
    }

    /**
     * Get registered field
     */
    public function getField(string $fieldName): ?ProgrammableField
    {
        return $this->registeredFields[$fieldName] ?? null;
    }

    /**
     * Get all registered fields
     */
    public function getRegisteredFields(): array
    {
        return $this->registeredFields;
    }

    /**
     * Get component registry for frontend
     */
    public function getComponentRegistry(): array
    {
        return $this->componentRegistry;
    }

    /**
     * Register default programmable field routes
     */
    public function registerRoutes(): void
    {
        // Generic bridge endpoint
        Route::post('/programmable-field/bridge', function () {
            return $this->handleGenericBridge();
        })->name('programmable-field.bridge');

        // Component registry endpoint
        Route::get('/programmable-field/components', function () {
            return response()->json([
                'components' => $this->getComponentRegistry(),
            ]);
        })->name('programmable-field.components');

        // Field metadata endpoint
        Route::get('/programmable-field/fields', function () {
            return response()->json([
                'fields' => array_map(fn($field) => $field->toArray(), $this->getRegisteredFields()),
            ]);
        })->name('programmable-field.fields');
    }

    /**
     * Handle generic bridge requests
     */
    protected function handleGenericBridge()
    {
        $request = request();
        $fieldName = $request->get('fieldName');

        $field = $this->getField($fieldName);
        if (!$field) {
            return response()->json([
                'success' => false,
                'error' => "Field '{$fieldName}' not found",
            ], 404);
        }

        try {
            // Call the field's bridge method
            $method = 'handleBridgeRequest';
            if (method_exists($field, $method)) {
                return call_user_func([$field, $method]);
            }

            return response()->json([
                'success' => false,
                'error' => 'Bridge method not available for this field',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new ProgrammableField instance
     */
    public function make(string $name = '', string $label = ''): ProgrammableField
    {
        return new ProgrammableField($name, $label);
    }
}

