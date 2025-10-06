import React, { useEffect } from 'react';
import { useProgrammableFieldRegistration } from './hooks/useProgrammableFieldRegistration';

/**
 * Component to handle initial setup and registration of programmable field components
 * This should be included in your app's root or layout component
 */
export const ProgrammableFieldSetup = ({ children }) => {
  const { registerMultiple, registerDirect } = useProgrammableFieldRegistration();

  useEffect(() => {
    // Register built-in components that aren't in the backend registry
    // This is useful for components that should always be available
    const builtInComponents = {
      // Example: Register components that are always available
      // 'DefaultInput': './types/InputField',
      // 'DefaultTextarea': './types/TextareaField',
    };

    // Register any built-in components
    if (Object.keys(builtInComponents).length > 0) {
      registerMultiple(builtInComponents);
    }

    // Example: Register a component directly (useful for dynamically created components)
    // const DynamicComponent = ({ value, setValue }) => (
    //   <input value={value} onChange={(e) => setValue(e.target.value)} />
    // );
    // registerDirect('DynamicInput', DynamicComponent);

  }, [registerMultiple, registerDirect]);

  return children;
};

/**
 * Higher-order component for registering field components
 */
export const withProgrammableFieldRegistration = (componentsToRegister) => (WrappedComponent) => {
  return function WithProgrammableFieldRegistration(props) {
    const { registerMultiple } = useProgrammableFieldRegistration();

    useEffect(() => {
      registerMultiple(componentsToRegister);
    }, [registerMultiple]);

    return <WrappedComponent {...props} />;
  };
};

/**
 * Example usage component for demonstration
 */
export const ExampleProgrammableFieldRegistration = () => {
  const { register, getRegistered, isRegistered } = useProgrammableFieldRegistration();

  useEffect(() => {
    // Example: Register a component from a dynamic path
    // This could be useful for plugin systems
    const registerExampleComponents = async () => {
      try {
        // Register ColorPickerInput if it exists
        register('ColorPickerInput', './custom/ColorPickerInput.jsx');
        
        // You could also register from external URLs or dynamic imports
        // register('ExternalComponent', () => import('https://example.com/component.js'));
        
        console.log('Registered components:', getRegistered());
        console.log('ColorPickerInput registered:', isRegistered('ColorPickerInput'));
      } catch (error) {
        console.warn('Failed to register example components:', error);
      }
    };

    registerExampleComponents();
  }, [register, getRegistered, isRegistered]);

  return null; // This component doesn't render anything
};

export default ProgrammableFieldSetup;