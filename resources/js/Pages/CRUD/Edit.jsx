import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { useForm, router } from "@inertiajs/react";
import { useState, useCallback, useMemo } from 'react';
import CreateEditFormWithBuilder from "../../Components/form/CreateEditFormWithBuilder";

export default function Edit({ resources, authors, fields, customFields, title, resource, slug, hasMeta, hasStatus, languages, defaultLanguage }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  console.log("OKS")
  console.log("SAYAP")

  // Extract query parameters from URL
  const queryParams = useMemo(() => {
    if (typeof window === 'undefined') return {};
    const params = new URLSearchParams(window.location.search);
    const paramsObject = {};
    for (const [key, value] of params.entries()) {
      paramsObject[key] = value;
    }
    return paramsObject;
  }, []);

  const initialFormState = useMemo(() => {
    // Ensure we have valid data to prevent hydration mismatches
    if (!resources || !languages) {
      return {};
    }

    const transformedObject = {
      ...resources,
      translations: {},
    }

    languages.forEach(lang => {
      transformedObject.translations[lang.code] = {
        ...(fields?.translations?.[lang.code] || {})
      };
    });

    if (!fields) {
      return transformedObject;
    }

    // Create a copy of fields to avoid mutating the original
    const fieldsCopy = { ...fields };
    delete fieldsCopy.translations;

    return {
      ...transformedObject,
      ...fieldsCopy
    };

  }, [resources, fields, languages]);

  const { data, setData, errors, put } = useForm(initialFormState);

  const submit = useCallback(async (e) => {
    e.preventDefault();
    setIsSubmitting(true);

    try {
      // Merge form data with query parameters
      const dataWithParams = {
        ...data,
        ...queryParams
      };

      // Use Inertia router directly to send custom data
      router.put(route(`${resource}.update`, resources.id), dataWithParams, {
        preserveState: true,
        preserveScroll: true,
        onError: (errors) => {
          for (const lang of languages) {
            const hasError = [
              'title',
              'description',
              'meta.title',
              'meta.description',
              'meta.keywords'
            ].some(field => errors[`translations.${lang.code}.${field}`]);

            if (hasError) {
              setSelectedIndex(languages.findIndex(l => l.code === lang.code));
              break;
            }
          }
        },
        onFinish: () => {
          setIsSubmitting(false);
        }
      });
    } catch (error) {
      setIsSubmitting(false);
    }
  }, [data, queryParams, setSelectedIndex, resource, resources.id, languages]);

  return (
    <AuthenticatedLayout header={`Edit ${title}`}>
      <CardWhite>
        <CreateEditFormWithBuilder
          title={`Edit ${title}`}
          data={data}
          setData={setData}
          languages={languages}
          defaultLanguage={defaultLanguage}
          selectedIndex={selectedIndex}
          setSelectedIndex={setSelectedIndex}
          isSubmitting={isSubmitting}
          submit={submit}
          errors={errors}
          setIsSubmitting={setIsSubmitting}
          isEdit={true}
          components={customFields}
          authors={authors}
          sluggify={slug ?? false}
          hasMeta={hasMeta}
          hasStatus={hasStatus}
          resource={resource}
          hasAuthors={!!authors?.length}
        />
      </CardWhite>
    </AuthenticatedLayout>
  );
}

