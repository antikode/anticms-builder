import InputField from "@/Components/fields/types/InputField";
import TextareaField from "@/Components/fields/types/TextareaField";
import TextEditorField from "@/Components/fields/types/TextEditorField";
import SelectField from "@/Components/fields/types/SelectField";
import ImageField from "@/Components/fields/types/ImageField";
import FileField from "@/Components/fields/types/FileField";
import ToggleField from "@/Components/fields/types/ToggleField";
import RepeaterField from "@/Components/fields/types/RepeaterField";
import RelationshipField from "@/Components/fields/types/RelationshipField";
import PostObjectField from "@/Components/fields/types/PostObjectField";
import MultiSelectField from "@/Components/fields/types/MultiSelectField";
import PostRelatedField from "@/Components/fields/types/PostRelatedField";
import BaseField from "@/Components/fields/BaseField";
import MediaField from "@/Components/fields/types/MediaField";
import TableField from "@/Components/fields/types/TableField";
import { useCallback } from "react";
import ProgrammableField from "./types/ProgrammableField";

export default function Builder({ item, data, setData, errors, languages, defaultLanguage, selectedIndex, setSelectedIndex, hideLabel = false }) {
  const updateValue = useCallback((name, value, langCode) => {
    setData(prevData => ({
      ...prevData,
      ...(langCode ? {
        translations: {
          ...prevData.translations,
          [langCode]: {
            ...prevData.translations[langCode],
            [name]: value
          }
        }
      } : {
        [name]: value
      })
    }));
  }, [setData]);

  const renderField = useCallback((field, fieldName) => {
    const commonProps = {
      data,
      errors,
      languages: field?.multilanguage ? languages : null,
      defaultLanguage,
      selectedIndex,
      setSelectedIndex,
      id: fieldName + '_' + languages[selectedIndex].code,
      name: fieldName,
      label: hideLabel ? <div className="pb-3"></div> : field?.label,
      required: field?.attribute?.is_required,
      placeholder: field?.attribute?.placeholder,
      caption: field?.attribute?.caption,
      defaultValue: field?.attribute?.defaultValue,
      setValue: updateValue
    };

    switch (field.field) {
      case "input":
        return (
          <InputField
            {...commonProps}
            type={field?.attribute?.type}
            max={field?.attribute?.max}
            maxLength={field?.attribute?.maxLength}
            min={field?.attribute?.min}
            minLength={field?.attribute?.minLength}
          />
        );
      case "textarea":
        return (
          <TextareaField
            {...commonProps}
            rows={field?.attribute?.rows}
            cols={field?.attribute?.cols}
            maxLength={field?.attribute?.max}
          />
        );
      case "texteditor":
        return (
          <TextEditorField {...commonProps} type={field?.attribute?.type} />
        );
      case "editor":
        return (
          <TextEditorField {...commonProps} type={field?.attribute?.type} />
        );
      case "select":
        return (
          <SelectField {...commonProps} options={field?.attribute?.options} searchable={field?.attribute?.searchable} />
        );
      case "image":
        return (
          <ImageField
            {...commonProps}
            acceptedFile={field?.attribute?.accept}
            resolution={field?.attribute?.resolution}
            fileSize={field?.attribute?.fileSize}
            convertToWebp={field?.attribute?.convertToWebp}
          />
        );
      case "file":
        return (
          <FileField
            {...commonProps}
            type={field?.attribute?.type}
            acceptedFile={field?.attribute?.accept}
            fileSize={field?.attribute?.fileSize}
          />
        );
      case "media":
        return (
          <MediaField
            {...commonProps}
            acceptedFile={field?.attribute?.accept}
            resolution={field?.attribute?.resolution}
          />
        );
      case "toggle":
        return <ToggleField {...commonProps} />;
      case "repeater":
        return (
          <RepeaterField
            {...commonProps}
            languages={languages}
            fields={field?.attribute?.fields}
            min={field?.attribute?.min}
            max={field?.attribute?.max}
          />
        );
      case "relationship":
        return (
          <RelationshipField
            {...commonProps}
            filter={field?.attribute?.filter}
            min={field?.attribute?.min}
            max={field?.attribute?.max}
          />
        );
      case "post_object":
        return (
          <PostObjectField
            {...commonProps}
            filter={field?.attribute?.filter}
            min={field?.attribute?.min}
            max={field?.attribute?.max}
            multiple={field?.attribute?.multiple}
          />
        );
      case "multi_select":
        return (
          <MultiSelectField
            {...commonProps}
            options={field?.attribute?.options}
            min={field?.attribute?.min}
            max={field?.attribute?.max}
            multiple={field?.attribute?.multiple}
          />
        );
      case "post_related":
        return <PostRelatedField {...commonProps} />;
      case "table":
        return (
          <TableField
            {...commonProps}
            filter={field?.attribute?.filter}
            min={field?.attribute?.min}
            max={field?.attribute?.max}
            columns={field?.attribute?.columns}
          />
        );
      case "group":
        return (
          <RepeaterField
            {...commonProps}
            languages={languages}
            fields={field?.attribute?.fields}
            min={1}
            max={1}
          />
        );
      case "programmable":
        console.log(field)
        return (
          <ProgrammableField
            {...commonProps}
            componentName={field?.attribute?.componentName}
            customAttributes={field?.attribute?.customAttributes}
            bridgeEndpoint={field?.attribute?.bridgeEndpoint}
            validationEndpoint={field?.attribute?.validationEndpoint}
            customMethods={field?.attribute?.customMethods}
          />
        );
      default:
        return null;
    }
  }, [data, errors, languages, defaultLanguage, updateValue]);

  return (
    <div
      value={item?.keyName}
      className="border-b-0 mb-2"
    >
      <BaseField>
        {renderField(item, item.name)}
      </BaseField>
    </div>
  );
}

