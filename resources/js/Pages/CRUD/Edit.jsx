import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { useForm, usePage } from "@inertiajs/react";
import { useState, useCallback, useMemo } from 'react';
import CreateEditFormWithBuilder from "../../Components/form/CreateEditFormWithBuilder";

export default function Edit({ resources, authors, fields, customFields, title, resource, slug, hasMeta, hasStatus }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const { languages, defaultLanguage } = usePage().props.app.languages;

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
      put(route(`${resource}.update`, resources.id), {
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
        }
      });
    } finally {
      setIsSubmitting(false);
    }
  }, [data, setSelectedIndex]);

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

