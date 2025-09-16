import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import { usePage, Link } from "@inertiajs/react";
import { useState, useMemo } from 'react';
import { Button } from "@/Components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";

export default function Show({ resources, infoList, statusOptions, title, resource, hasMeta, hasStatus }) {
  const [selectedIndex, setSelectedIndex] = useState(0);
  const { languages, defaultLanguage } = usePage().props.app.languages;

  const getFileTypeFromExtension = (extension) => {
    if (!extension) return 'file';
    
    const ext = extension.toLowerCase();
    const imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
    const videoTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv', 'm4v'];
    const audioTypes = ['mp3', 'wav', 'ogg', 'aac', 'flac', 'm4a', 'wma'];
    const documentTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'];
    const archiveTypes = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
    
    if (imageTypes.includes(ext)) return 'image';
    if (videoTypes.includes(ext)) return 'video';
    if (audioTypes.includes(ext)) return 'audio';
    if (documentTypes.includes(ext)) return 'document';
    if (archiveTypes.includes(ext)) return 'archive';
    
    return 'file';
  };

  const getFileTypeFromMimeType = (mimeType) => {
    if (!mimeType) return 'file';
    
    if (mimeType.startsWith('image/')) return 'image';
    if (mimeType.startsWith('video/')) return 'video';
    if (mimeType.startsWith('audio/')) return 'audio';
    if (mimeType === 'application/pdf') return 'document';
    if (mimeType.includes('document') || mimeType.includes('spreadsheet') || mimeType.includes('presentation')) return 'document';
    if (mimeType.includes('zip') || mimeType.includes('compressed')) return 'archive';
    
    return 'file';
  };

  const detectFileType = (value, key) => {
    // Handle string URLs
    if (typeof value === 'string') {
      const extension = value.split('.').pop();
      return getFileTypeFromExtension(extension);
    }
    
    // Handle file objects
    if (typeof value === 'object' && value !== null) {
      // Check MIME type first (most reliable)
      if (value.mime_type || value.mimeType) {
        return getFileTypeFromMimeType(value.mime_type || value.mimeType);
      }
      
      // Check file extension from URL or name
      const fileUrl = value.url || value.src || value.path;
      const fileName = value.name || value.filename;
      
      if (fileUrl) {
        const extension = fileUrl.split('.').pop();
        return getFileTypeFromExtension(extension);
      }
      
      if (fileName) {
        const extension = fileName.split('.').pop();
        return getFileTypeFromExtension(extension);
      }
    }
    
    return 'file'; // Generic file type
  };

  const formatFileSize = (bytes) => {
    if (!bytes || bytes === 0) return '0 B';
    
    const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    const size = (bytes / Math.pow(1024, i)).toFixed(i === 0 ? 0 : 1);
    
    return `${size} ${sizes[i]}`;
  };

  const detectFieldType = (value, key) => {
    if (value === null || value === undefined) return 'text';

    if (typeof value === 'boolean') return 'boolean';

    // Enhanced file and media detection
    if (key.includes('file') || key.includes('document') || key.includes('attachment') || 
        key.includes('media') || key.includes('upload') || key.includes('download')) {
      return detectFileType(value, key);
    }

    // Enhanced image detection (keep existing logic but also check file type)
    if (key.includes('image') || key.includes('photo') || key.includes('avatar') || key.includes('picture')) {
      const fileType = detectFileType(value, key);
      return fileType === 'image' ? 'image' : fileType;
    }

    // Detect file objects by structure
    if (typeof value === 'object' && value !== null && 
        (value.url || value.src || value.path || value.name || value.filename || 
         value.mime_type || value.mimeType || value.fileId || value.file_id)) {
      return detectFileType(value, key);
    }

    // Detect URLs that might be files
    if (typeof value === 'string' && value.match(/^https?:\/\/.*\.[a-zA-Z0-9]+$/)) {
      const extension = value.split('.').pop();
      if (extension && extension.length <= 5) {
        return detectFileType(value, key);
      }
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

    const getFileUrl = (fileValue) => {
      if (typeof fileValue === 'string') return fileValue;
      return fileValue?.url || fileValue?.src || fileValue?.path || '';
    };

    const getFileName = (fileValue) => {
      if (typeof fileValue === 'string') {
        return fileValue.split('/').pop() || 'File';
      }
      return fileValue?.name || fileValue?.filename || fileValue?.original_name || 'File';
    };

    const getFileSize = (fileValue) => {
      if (typeof fileValue === 'object' && fileValue !== null) {
        return fileValue?.size || fileValue?.file_size || null;
      }
      return null;
    };

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
          <div className="space-y-2">
            <img
              height={entry.height || 128}
              width={entry.width || 128}
              src={getFileUrl(value)}
              alt={value.alt || getFileName(value)}
              className="max-w-xs max-h-32 object-cover rounded border"
              onError={(e) => {
                e.target.style.display = 'none';
                e.target.nextSibling.style.display = 'block';
              }}
            />
            <div style={{ display: 'none' }} className="flex items-center space-x-2 text-sm text-gray-600">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <a 
                href={getFileUrl(value)} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline"
              >
                {getFileName(value)}
              </a>
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No image</span>;

      case 'video':
        return value ? (
          <div className="space-y-2">
            <video 
              controls 
              className="max-w-xs max-h-32 rounded border"
              src={getFileUrl(value)}
              onError={(e) => {
                e.target.style.display = 'none';
                e.target.nextSibling.style.display = 'flex';
              }}
            >
              Your browser does not support video playback.
            </video>
            <div style={{ display: 'none' }} className="flex items-center space-x-2 text-sm text-gray-600">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
              <a 
                href={getFileUrl(value)} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline"
              >
                {getFileName(value)}
              </a>
              {getFileSize(value) && (
                <span className="text-gray-500">({formatFileSize(getFileSize(value))})</span>
              )}
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No video</span>;

      case 'audio':
        return value ? (
          <div className="space-y-2">
            <audio controls className="w-full max-w-xs" src={getFileUrl(value)}>
              Your browser does not support audio playback.
            </audio>
            <div className="flex items-center space-x-2 text-sm text-gray-600">
              <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M9 9v6l4.5-3L9 9z" />
              </svg>
              <span>{getFileName(value)}</span>
              {getFileSize(value) && (
                <span className="text-gray-500">({formatFileSize(getFileSize(value))})</span>
              )}
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No audio</span>;

      case 'document':
        return value ? (
          <div className="flex items-center space-x-2">
            <svg className="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
            </svg>
            <div className="flex-1">
              <a 
                href={getFileUrl(value)} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline font-medium"
                download
              >
                {getFileName(value)}
              </a>
              {getFileSize(value) && (
                <div className="text-sm text-gray-500">{formatFileSize(getFileSize(value))}</div>
              )}
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No document</span>;

      case 'archive':
        return value ? (
          <div className="flex items-center space-x-2">
            <svg className="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10" />
            </svg>
            <div className="flex-1">
              <a 
                href={getFileUrl(value)} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline font-medium"
                download
              >
                {getFileName(value)}
              </a>
              {getFileSize(value) && (
                <div className="text-sm text-gray-500">{formatFileSize(getFileSize(value))}</div>
              )}
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No archive</span>;

      case 'file':
        return value ? (
          <div className="flex items-center space-x-2">
            <svg className="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <div className="flex-1">
              <a 
                href={getFileUrl(value)} 
                target="_blank" 
                rel="noopener noreferrer"
                className="text-blue-600 hover:underline font-medium"
                download
              >
                {getFileName(value)}
              </a>
              {getFileSize(value) && (
                <div className="text-sm text-gray-500">{formatFileSize(getFileSize(value))}</div>
              )}
            </div>
          </div>
        ) : <span className="text-gray-400 italic">No file</span>;

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
