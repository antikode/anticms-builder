import React, { useState, useEffect } from "react";
import FieldWrapper from "../FieldWrapper";
import LanguageTab from "../LanguageTab";
import ErrorField from "./ErrorField";
import { useProgrammableFieldRegistry } from "../ProgrammableFieldRegistry";
import ProgrammableFieldErrorBoundary from "../ProgrammableFieldErrorBoundary";

export default function ProgrammableField({
  data,
  errors,
  languages,
  defaultLanguage,
  name,
  id,
  label,
  disabled = false,
  required,
  placeholder,
  caption,
  defaultValue,
  setValue,
  selectedIndex,
  setSelectedIndex,
  itemIndex,
  // Programmable-specific props
  componentName,
  customAttributes = {},
  bridgeEndpoint,
  validationEndpoint,
  customMethods = [],
}) {
  const [componentState, setComponentState] = useState({});
  const [isLoading, setIsLoading] = useState(false);
  const [validationErrors, setValidationErrors] = useState([]);

  // Use component registry from context
  const {
    getComponent,
    getAvailableComponents,
    isLoading: registryLoading,
    error: registryError
  } = useProgrammableFieldRegistry();

  // Bridge API call function
  const callBridgeMethod = async (method, params = {}) => {
    if (!bridgeEndpoint) return null;

    setIsLoading(true);
    try {
      const response = await fetch(bridgeEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          method,
          params,
          fieldName: name,
        }),
      });

      const result = await response.json();
      setIsLoading(false);
      return result;
    } catch (error) {
      setIsLoading(false);
      console.error('Bridge method call failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Custom validation function
  const validateValue = async (value, langCode) => {
    if (!validationEndpoint) return true;

    try {
      const response = await fetch(validationEndpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          value,
          fieldName: name,
          langCode,
        }),
      });

      const result = await response.json();

      if (result.valid) {
        setValidationErrors([]);
        return true;
      } else {
        setValidationErrors(result.errors || [result.message]);
        return false;
      }
    } catch (error) {
      console.error('Validation failed:', error);
      setValidationErrors(['Validation request failed']);
      return false;
    }
  };

  // Call custom method
  const callCustomMethod = async (methodName, params = {}) => {
    const endpoint = `/programmable-field/${name}/method/${methodName}`;

    try {
      const response = await fetch(endpoint, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({
          params,
          fieldName: name,
        }),
      });

      const result = await response.json();
      return result;
    } catch (error) {
      console.error('Custom method call failed:', error);
      return { success: false, error: error.message };
    }
  };

  // Enhanced setValue with validation
  const handleSetValue = async (fieldName, value, langCode) => {
    // Run custom validation if endpoint is provided
    const isValid = await validateValue(value, langCode);

    if (isValid) {
      setValue(fieldName, value, langCode);
    }
  };

  // Component props to pass to custom component
  const componentProps = {
    // Standard props
    data,
    errors: [...(errors?.[name] || []), ...validationErrors],
    languages,
    defaultLanguage,
    name,
    id,
    label,
    disabled,
    required,
    placeholder,
    caption,
    defaultValue,
    setValue: handleSetValue,
    selectedIndex,
    setSelectedIndex,
    itemIndex,

    // Programmable-specific props
    customAttributes,
    isLoading,
    componentState,
    setComponentState,

    // API functions
    callBridgeMethod,
    validateValue,
    callCustomMethod,

    // Helper functions
    getValue: (langCode = null) => {
      return langCode
        ? data?.translations?.[langCode]?.[name] !== undefined
          ? data.translations[langCode][name]
          : defaultValue
        : data?.[name] !== undefined
          ? data[name]
          : defaultValue;
    },
  };

  // Dynamic component loading
  const [DynamicComponent, setDynamicComponent] = useState(null);
  const [loadError, setLoadError] = useState(null);

  useEffect(() => {
    if (componentName && !registryLoading) {
      const componentImporter = getComponent(componentName);

      if (componentImporter) {
        componentImporter()
          .then(module => {
            console.log('Loaded component:', componentName, module);
            setDynamicComponent(() => {
                return module.default;
            });
            setLoadError(null);
          })
          .catch(error => {
            console.error('Failed to load component:', componentName, error);
            setLoadError(`Failed to load component: ${componentName}. ${error.message}`);
          });
      } else {
        const availableComponents = getAvailableComponents();
        setLoadError(
          `Component '${componentName}' not found in registry.${availableComponents.length > 0
            ? ` Available components: ${availableComponents.join(', ')}`
            : ' No components are currently registered.'
          }${registryError
            ? ` Registry error: ${registryError}`
            : ''
          }`
        );
      }
    }
  }, [componentName, registryLoading, getComponent, getAvailableComponents, registryError]);

  const renderField = (langCode = null) => {
    if (loadError) {
      return (
        <div className="p-4 bg-red-50 border border-red-200 rounded-md">
          <p className="text-red-800">{loadError}</p>
        </div>
      );
    }

    if (componentName && !DynamicComponent) {
      if (registryLoading) {
        return (
          <div className="p-4 bg-blue-50 border border-blue-200 rounded-md">
            <div className="flex items-center space-x-2">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600"></div>
              <p className="text-blue-700">Loading component registry...</p>
            </div>
          </div>
        );
      }

      if (!registryLoading && !loadError) {
        return (
          <div className="p-4 bg-gray-50 border border-gray-200 rounded-md">
            <div className="flex items-center space-x-2">
              <div className="animate-spin rounded-full h-4 w-4 border-b-2 border-gray-600"></div>
              <p className="text-gray-600">Loading component: {componentName}...</p>
            </div>
          </div>
        );
      }
    }

    if (DynamicComponent) {
      return (
        <ProgrammableFieldErrorBoundary componentName={componentName}>
          <DynamicComponent {...componentProps} langCode={langCode} />
        </ProgrammableFieldErrorBoundary>
      );
    }

    // Fallback to basic input if no custom component is specified
    return (
      <div className="space-y-2">
        <input
          type="text"
          id={langCode ? `${id}_${langCode}` : id}
          name={langCode ? `${name}_${langCode}` : name}
          value={componentProps.getValue(langCode) || ''}
          onChange={(e) => handleSetValue(name, e.target.value, langCode)}
          placeholder={placeholder}
          disabled={disabled || isLoading}
          required={required}
          className="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        {isLoading && (
          <p className="text-sm text-gray-500">Processing...</p>
        )}
        {validationErrors.length > 0 && (
          <div className="text-sm text-red-600">
            {validationErrors.map((error, index) => (
              <p key={index}>{error}</p>
            ))}
          </div>
        )}
      </div>
    );
  };

  if (languages && languages.length > 1) {
    return (
      <FieldWrapper label={label} required={required} caption={caption}>
        <LanguageTab
          languages={languages}
          selectedIndex={selectedIndex}
          setSelectedIndex={setSelectedIndex}
        >
          <div className="mt-4 space-y-2">
            {renderField(languages[selectedIndex].code)}
          </div>
        </LanguageTab>
        <ErrorField errors={errors} name={name} />
      </FieldWrapper>
    );
  }

  return (
    <FieldWrapper label={label} required={required} caption={caption}>
      {renderField()}
      <ErrorField errors={errors} name={name} />
    </FieldWrapper>
  );
}
