import React, { createContext, useContext, useState, useEffect } from 'react';

const ProgrammableFieldRegistryContext = createContext();

export const useProgrammableFieldRegistry = () => {
  const context = useContext(ProgrammableFieldRegistryContext);
  if (!context) {
    throw new Error('useProgrammableFieldRegistry must be used within a ProgrammableFieldRegistryProvider');
  }
  return context;
};

export const ProgrammableFieldRegistryProvider = ({ children }) => {
  const [componentRegistry, setComponentRegistry] = useState({});
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState(null);

  useEffect(() => {
    const loadComponentRegistry = async () => {
      try {
        setIsLoading(true);
        setError(null);

        const response = await fetch('/programmable-field/components', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
          },
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const registry = {};

        // Convert paths to dynamic imports
        Object.entries(data.components || {}).forEach(([name, path]) => {
          registry[name] = createComponentImporter(path);
        });

        setComponentRegistry(registry);
      } catch (error) {
        console.warn('Could not load programmable field component registry:', error.message);
        setError(error.message);
      } finally {
        setIsLoading(false);
      }
    };

    loadComponentRegistry();
  }, []);

  const createComponentImporter = (path) => {
    return async () => {
      // Convert absolute file path to relative Vite path
      let importPath = path;
      
      // If path contains absolute file system path, extract the relative part
      if (importPath.includes('/resources/js/')) {
        importPath = importPath.substring(importPath.indexOf('/resources/js/'));
      }
      
      // Ensure path starts with /
      if (!importPath.startsWith('/')) {
        importPath = '/' + importPath;
      }

      const modules = import.meta.glob('/resources/js/Components/fields/types/custom/*.{jsx,js}', { eager: false });
      
      const matchedModule = modules[importPath];

      if (matchedModule) {
        try {
          return await matchedModule();
        } catch (error) {
          console.error(`Failed to import component from ${importPath}:`, error);
          throw new Error(`Error loading custom field: ${error.message}`);
        }
      }

      try {
        return await import(/* @vite-ignore */ importPath);
      } catch (error) {
        console.error(`Failed to import component from ${importPath}:`, error);

        throw new Error(`Error load custom import path ${error}`);
      }
    };
  };

  const registerComponent = (name, importer) => {
    setComponentRegistry(prev => ({
      ...prev,
      [name]: importer,
    }));
  };

  const unregisterComponent = (name) => {
    setComponentRegistry(prev => {
      const newRegistry = { ...prev };
      delete newRegistry[name];
      return newRegistry;
    });
  };

  const getComponent = (name) => {
    return componentRegistry[name] || null;
  };

  const getAvailableComponents = () => {
    return Object.keys(componentRegistry);
  };

  const value = {
    componentRegistry,
    isLoading,
    error,
    registerComponent,
    unregisterComponent,
    getComponent,
    getAvailableComponents,
  };

  return (
    <ProgrammableFieldRegistryContext.Provider value={value}>
      {children}
    </ProgrammableFieldRegistryContext.Provider>
  );
};

export default ProgrammableFieldRegistryProvider;
