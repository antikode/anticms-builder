import React from 'react';
import { ProgrammableFieldRegistryProvider } from '../ProgrammableFieldRegistry';
import { ProgrammableFieldSetup } from '../ProgrammableFieldSetup';
import ProgrammableField from '../types/ProgrammableField';

/**
 * Example showing how to set up and use the ProgrammableField system
 */

// Example 1: App-level setup (put this in your main App.jsx or layout)
export const AppWithProgrammableFields = ({ children }) => {
  return (
    <ProgrammableFieldRegistryProvider>
      <ProgrammableFieldSetup>
        {children}
      </ProgrammableFieldSetup>
    </ProgrammableFieldRegistryProvider>
  );
};

// Example 2: Using ProgrammableField in a form
export const ExampleForm = () => {
  const [data, setData] = React.useState({});
  const [errors, setErrors] = React.useState({});

  const setValue = (fieldName, value, langCode) => {
    if (langCode) {
      setData(prev => ({
        ...prev,
        translations: {
          ...prev.translations,
          [langCode]: {
            ...prev.translations?.[langCode],
            [fieldName]: value
          }
        }
      }));
    } else {
      setData(prev => ({
        ...prev,
        [fieldName]: value
      }));
    }
  };

  return (
    <div className="space-y-6 p-6">
      <h2 className="text-2xl font-bold">Programmable Field Examples</h2>

      {/* Example: Custom Field */}
      <ProgrammableField
        data={data}
        errors={errors}
        name="custom_field"
        label="Custom Field"
        setValue={setValue}
        componentName="CustomInput"
        customAttributes={{
          placeholder: "Enter value...",
          maxLength: 255
        }}
        bridgeEndpoint="/custom-field/bridge"
        validationEndpoint="/custom-field/validate"
      />
    </div>
  );
};

export default { AppWithProgrammableFields, ExampleForm };