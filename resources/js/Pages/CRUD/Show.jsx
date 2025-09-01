import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { usePage, Link } from "@inertiajs/react";
import { useState, useMemo } from 'react';
import { Button } from "@/Components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";

export default function Show({ resources, infoList, statusOptions, title, resource, hasMeta, hasStatus }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const { languages, defaultLanguage } = usePage().props.app.languages;

  const detectFieldType = (value, key) => {
    if (value === null || value === undefined) return 'text';

    if (typeof value === 'boolean') return 'boolean';

    if (key.includes('image') || key.includes('photo') || key.includes('avatar') || key.includes('picture')) {
      return 'image';
    }

    if (key.includes('date') || key.includes('created_at') || key.includes('updated_at') || key.includes('published_at')) {
      return (typeof value === 'string' && (value.includes('T') || value.includes(' '))) ? 'datetime' : 'date';
    }

    if (key.includes('email')) return 'email';
    if (key.includes('url') || key.includes('link')) return 'url';
    if (key.includes('description') || key.includes('content') || key.includes('body')) return 'textarea';

    if (Array.isArray(value)) return 'array';
    if (typeof value === 'object' && value !== null) return 'relationship';

    return 'text';
  };

  const generateAutoInfoList = useMemo(() => {
    if (!resources) {
      return null;
    }

    // Only generate auto infolist if no infolist is provided OR if it's empty
    const hasExistingEntries = infoList?.entries?.length > 0;
    const hasExistingSections = infoList?.sections?.length > 0;

    if (hasExistingEntries || hasExistingSections) {
      return null;
    }

    const excludeFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'password', 'remember_token', 'email_verified_at'];
    const entries = [];

    Object.keys(resources).forEach(key => {
      if (!excludeFields.includes(key) && key !== 'translations') {
        const value = resources[key];
        const type = detectFieldType(value, key);

        entries.push({
          name: key,
          label: key.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' '),
          type: type,
          value: value,
          display_value: value
        });
      }
    });

    return {
      entries: entries,
      sections: []
    };
  }, [resources, infoList]);

  // Use generated infoList if provided infoList is empty or has no content
  const hasExistingEntries = infoList?.entries?.length > 0;
  const hasExistingSections = infoList?.sections?.length > 0;
  const effectiveInfoList = (hasExistingEntries || hasExistingSections) ? infoList : generateAutoInfoList;

  const renderInfoEntry = (entry) => {
    const { value, display_value, type } = entry;

    if (value === null || value === undefined || value === '') {
      return <span className="text-gray-400 italic">Not set</span>;
    }

    switch (type) {
      case 'boolean':
        return (
          <span className={`px-2 py-1 rounded text-xs font-medium ${value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
            }`}>
            {value ? 'Yes' : 'No'}
          </span>
        );

      case 'image':
        return value ? (
          <img
            height={entry.height || 128}
            width={entry.width || 128}
            src={value.url || value}
            alt={value.alt || ''}
            className="max-w-xs max-h-32 object-cover rounded"
          />
        ) : <span className="text-gray-400 italic">No image</span>;

      case 'date':
        return <span>{new Date(value).toLocaleDateString()}</span>;

      case 'datetime':
        return <span>{new Date(value).toLocaleString()}</span>;

      case 'email':
        return <a href={`mailto:${value}`} className="text-blue-600 hover:underline">{value}</a>;

      case 'url':
        return <a href={value} target="_blank" rel="noopener noreferrer" className="text-blue-600 hover:underline">{value}</a>;

      case 'array':
        return (
          <div className="space-y-1">
            {Array.isArray(value) ? value.map((item, index) => (
              <span key={index} className="inline-block bg-gray-100 px-2 py-1 rounded text-sm mr-1">
                {typeof item === 'object' ? JSON.stringify(item) : item}
              </span>
            )) : <span>{value}</span>}
          </div>
        );

      case 'relationship':
        if (typeof value === 'object' && value !== null) {
          return <span>{value.name || value.title || value.label || JSON.stringify(value)}</span>;
        }
        return <span>{value}</span>;

      case 'textarea':
        return (
          <div className="whitespace-pre-wrap max-h-32 overflow-y-auto">
            {value && value.length > 200 ? `${value.substring(0, 200)}...` : value}
          </div>
        );

      case 'markdown':
      case 'html':
        return <div dangerouslySetInnerHTML={{ __html: value }} />;

      default:
        return <span>{display_value || value}</span>;
    }
  };

  return (
    <AuthenticatedLayout header={`View ${title}`}>
      <Tabs
        value={languages[selectedIndex].code}
        onValueChange={(value) => {
          setSelectedIndex(languages.findIndex((lang) => lang.code === value));
        }}
        className="w-full"
      >
        <CardWhite>
          <div className="space-y-6">
            <div className="flex justify-between items-center">
              <h2 className="text-2xl font-bold text-gray-900">View {title}</h2>
              <div className="flex gap-2">
                <Link
                  href={route(`${resource}.index`)}
                  className={`!py-1`}
                >
                  <Button variant="outline">Back to list</Button>
                </Link>
              </div>
            </div>
            <TabsList>
              {languages.map((lang) => (
                <TabsTrigger
                  key={lang.code}
                  value={lang.code}
                >
                  <span className="flex items-center justify-center gap-2">
                    {lang.name}
                  </span>
                </TabsTrigger>
              ))}
            </TabsList>

            <div className="space-y-6">
              {effectiveInfoList?.sections && effectiveInfoList.sections.map((section, sectionIndex) => (
                <div key={sectionIndex} className="space-y-4">
                  {section.title && (
                    <h3 className="text-lg font-medium text-gray-900 border-b pb-2">
                      {section.title}
                    </h3>
                  )}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {section.entries && section.entries.map((entry, entryIndex) => (
                      <div key={entryIndex} className="space-y-2">
                        <label className="block text-sm font-medium text-gray-700">
                          {entry.label}
                        </label>
                        <div className="bg-gray-50 p-3 rounded border">
                          {renderInfoEntry(entry)}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              ))}

              {effectiveInfoList?.entries && effectiveInfoList.entries.length > 0 && (
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {effectiveInfoList.entries.map((entry, entryIndex) => (
                      <div key={entryIndex} className="space-y-2">
                        <label className="block text-sm font-medium text-gray-700">
                          {entry.label}
                        </label>
                        <div className="bg-gray-50 p-3 rounded border">
                          {renderInfoEntry(entry)}
                        </div>
                      </div>
                    ))}
                  </div>
                </div>
              )}
            </div>

            {hasStatus && resources?.status && (
              <div className="space-y-2">
                <label className="block text-sm font-medium text-gray-700">Status</label>
                <div className="bg-gray-50 p-3 rounded border">
                  <span className={`px-2 py-1 rounded text-sm font-medium ${resources.status === 'publish'
                    ? 'bg-green-100 text-green-800'
                    : resources.status === 'draft'
                      ? 'bg-yellow-100 text-yellow-800'
                      : 'bg-blue-100 text-blue-800'
                    }`}>
                    {statusOptions.find(option => option.id === resources.status)?.name || resources.status}
                  </span>
                </div>
              </div>
            )}
          </div>
        </CardWhite>
      </Tabs>
    </AuthenticatedLayout>
  );
}
