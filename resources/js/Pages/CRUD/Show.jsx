import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { usePage, Link } from "@inertiajs/react";
import { useState } from 'react';
import { Button } from "@/Components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";

export default function Show({ resources, infoList, statusOptions, title, resource, hasMeta, hasStatus }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const { languages, defaultLanguage } = usePage().props.app.languages;

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

      case 'markdown':
      case 'html':
        return <div dangerouslySetInnerHTML={{ __html: value }} />;

      default:
        return <span>{display_value}</span>;
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
              {infoList?.sections && infoList.sections.map((section, sectionIndex) => (
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

              {infoList?.entries && infoList.entries.length > 0 && (
                <div className="space-y-4">
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {infoList.entries.map((entry, entryIndex) => (
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
