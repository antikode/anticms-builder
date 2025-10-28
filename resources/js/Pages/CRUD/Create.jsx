import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { useForm, router } from "@inertiajs/react";
import { format } from "date-fns"
import { useState, useCallback, useMemo, useEffect } from 'react';
import { pluck } from "@/lib/utils";
import CreateEditFormWithBuilder from "../../Components/form/CreateEditFormWithBuilder";

export default function Create({ auth, authors, title, customFields, resource, slug, hasMeta, hasStatus, languages, defaultLanguage }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [isSubmitting, setIsSubmitting] = useState(false);

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

  let defaultValues = {};
  pluck(customFields, 'name').forEach((c, i) => {
    if (customFields[i].field == 'repeater') {
      // For repeater fields, initialize as empty array
      // The RepeaterField component will handle creating items with proper structure
      defaultValues[c] = [];
    } else {
      defaultValues[c] = '';
    }
  });

  const initialFormState = useMemo(() => {
    const translations = {};
    languages.forEach(lang => {
      translations[lang.code] = {
        title: '',
        description: '',
        meta: {
          title: '',
        }
      }
    });

    return {
      ...defaultValues, // Custom fields at root level
      translations,
      slug: '',
      status: 'draft',
      published_at: format(new Date(), 'yyyy-M-dd'),
      user_id: auth?.user?.id,
      tags: [],
      categories: [],
      meta: {
        canonical: '',
        table_of_content: false,
        image: '',
        image_alt: '',
        robots: 'index,follow'
      }
    };

  }, [auth?.user?.id]);

  const { data, setData, errors, post } = useForm(initialFormState);

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
      router.post(route(`${resource}.store`), dataWithParams, {
        preserveState: true,
        preserveScroll: true,
        onError: (errors) => {
          console.log(errors)
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
  }, [data, queryParams, setSelectedIndex, resource, languages]);

  return (
    <AuthenticatedLayout header={`Create ${title}`}>
      <CardWhite>
        <CreateEditFormWithBuilder
          title={`Create ${title}`}
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
          isEdit={false}
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

