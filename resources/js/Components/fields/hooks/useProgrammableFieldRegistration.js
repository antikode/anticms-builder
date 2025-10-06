import { useCallback } from 'react';
import { useProgrammableFieldRegistry } from '../ProgrammableFieldRegistry';

/**
 * Hook for dynamically registering programmable field components at runtime
 * Useful for plugins or dynamically loaded modules
 */
export const useProgrammableFieldRegistration = () => {
  const { registerComponent, unregisterComponent, getAvailableComponents } = useProgrammableFieldRegistry();

  /**
   * Register a component with dynamic import
   * @param {string} name - Component name
   * @param {string|function} pathOrImporter - Path to component or import function
   */
  const register = useCallback((name, pathOrImporter) => {
    let importer;

    if (typeof pathOrImporter === 'function') {
      importer = pathOrImporter;
    } else if (typeof pathOrImporter === 'string') {
      importer = () => import(/* @vite-ignore */ pathOrImporter);
    } else {
      throw new Error('pathOrImporter must be a string path or function');
    }

    registerComponent(name, importer);
  }, [registerComponent]);

  /**
   * Register a component with a direct component reference
   * @param {string} name - Component name 
   * @param {React.Component} component - Direct component reference
   */
  const registerDirect = useCallback((name, component) => {
    registerComponent(name, () => Promise.resolve({ default: component }));
  }, [registerComponent]);

  /**
   * Register multiple components at once
   * @param {Object} components - Object with name -> path/importer mappings
   */
  const registerMultiple = useCallback((components) => {
    Object.entries(components).forEach(([name, pathOrImporter]) => {
      register(name, pathOrImporter);
    });
  }, [register]);

  /**
   * Unregister a component
   * @param {string} name - Component name to unregister
   */
  const unregister = useCallback((name) => {
    unregisterComponent(name);
  }, [unregisterComponent]);

  /**
   * Check if a component is registered
   * @param {string} name - Component name
   * @returns {boolean}
   */
  const isRegistered = useCallback((name) => {
    return getAvailableComponents().includes(name);
  }, [getAvailableComponents]);

  /**
   * Get all registered component names
   * @returns {string[]}
   */
  const getRegistered = useCallback(() => {
    return getAvailableComponents();
  }, [getAvailableComponents]);

  return {
    register,
    registerDirect,
    registerMultiple,
    unregister,
    isRegistered,
    getRegistered,
  };
};

export default useProgrammableFieldRegistration;