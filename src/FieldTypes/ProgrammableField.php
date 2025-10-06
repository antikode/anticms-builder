<?php

namespace AntiCmsBuilder\FieldTypes;

use Illuminate\Support\Facades\Route;

/**
 * @extends FieldType<ProgrammableField>
 */
abstract class ProgrammableField extends FieldType
{
    protected string $type = 'programmable';

    protected string $componentName;

    protected array $customAttributes = [];

    protected ?string $bridgeEndpoint = null;

    protected ?string $validationEndpoint = null;

    protected array $customMethods = [];

    public function __construct(string $name = '', string $label = '')
    {
        parent::__construct($name, $label);
        $this->setDefaultAttributes();
    }

    public function setDefaultAttributes(): void
    {
        $this->attributes = [
            'is_required' => false,
            'placeholder' => '',
            'caption' => '',
            'defaultValue' => '',
            'value' => '',
            'componentName' => '',
            'customAttributes' => [],
            'bridgeEndpoint' => null,
            'validationEndpoint' => null,
            'customMethods' => [],
        ];
    }

    /**
     * Set the JSX component name to be rendered
     */
    public function component(string $componentName): static
    {
        $this->componentName = $componentName;
        $this->attributes['componentName'] = $componentName;

        return $this;
    }

    /**
     * Set custom attributes for the component
     */
    public function customAttributes(array $attributes): static
    {
        $this->customAttributes = array_merge($this->customAttributes, $attributes);
        $this->attributes['customAttributes'] = $this->customAttributes;

        return $this;
    }

    /**
     * Add individual custom attribute
     */
    public function customAttribute(string $key, $value): static
    {
        $this->customAttributes[$key] = $value;
        $this->attributes['customAttributes'] = $this->customAttributes;

        return $this;
    }

    /**
     * Set bridge endpoint for PHP-JSX communication
     */
    public function bridgeEndpoint(string $endpoint): static
    {
        $this->bridgeEndpoint = $endpoint;
        $this->attributes['bridgeEndpoint'] = $endpoint;

        return $this;
    }

    /**
     * Set validation endpoint for custom validation
     */
    public function validationEndpoint(string $endpoint): static
    {
        $this->validationEndpoint = $endpoint;
        $this->attributes['validationEndpoint'] = $endpoint;

        return $this;
    }

    /**
     * Add custom method that can be called from JSX
     */
    public function method(string $methodName, callable $callback): static
    {
        $this->customMethods[$methodName] = $callback;
        $this->attributes['customMethods'][$methodName] = $methodName; // Only store method name, not callback

        return $this;
    }

    /**
     * Register routes for this field's endpoints
     */
    public function registerRoutes(): void
    {
        if ($this->bridgeEndpoint) {
            Route::post($this->bridgeEndpoint, function () {
                return $this->handleBridgeRequest();
            });
        }

        if ($this->validationEndpoint) {
            Route::post($this->validationEndpoint, function () {
                return $this->handleValidationRequest();
            });
        }

        // Register custom method endpoints
        foreach ($this->customMethods as $methodName => $callback) {
            Route::post("/programmable-field/{$this->name}/method/{$methodName}", function () use ($callback, $methodName) {
                return $this->handleCustomMethodRequest($methodName, $callback);
            });
        }
    }

    /**
     * Handle bridge endpoint requests
     */
    protected function handleBridgeRequest()
    {
        $request = request();
        $method = $request->get('method');
        $params = $request->get('params', []);

        // Override this method in your custom field to handle specific bridge requests
        return $this->onBridgeRequest($method, $params, $request);
    }

    /**
     * Handle validation endpoint requests
     */
    protected function handleValidationRequest()
    {
        $request = request();
        $value = $request->get('value');
        $fieldName = $request->get('fieldName');

        // Override this method in your custom field for custom validation
        return $this->onValidationRequest($value, $fieldName, $request);
    }

    /**
     * Handle custom method requests
     */
    protected function handleCustomMethodRequest(string $methodName, callable $callback)
    {
        $request = request();
        $params = $request->get('params', []);

        try {
            $result = call_user_func($callback, $params, $request);

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Override this method to handle bridge requests
     */
    protected function onBridgeRequest(string $method, array $params, $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Bridge request received',
            'method' => $method,
            'params' => $params,
        ]);
    }

    /**
     * Override this method to handle validation requests
     */
    protected function onValidationRequest($value, string $fieldName, $request)
    {
        return response()->json([
            'valid' => true,
            'message' => 'Validation passed',
            'value' => $value,
            'fieldName' => $fieldName,
        ]);
    }

    public function toArray(): array
    {
        $baseArray = parent::toArray();

        // Add programmable-specific data
        $baseArray['attribute']['componentName'] = $this->componentName;
        $baseArray['attribute']['customAttributes'] = $this->customAttributes;
        $baseArray['attribute']['bridgeEndpoint'] = $this->bridgeEndpoint;
        $baseArray['attribute']['validationEndpoint'] = $this->validationEndpoint;
        $baseArray['attribute']['customMethods'] = array_keys($this->customMethods);

        return $baseArray;
    }

    abstract public function path(): string;
}
