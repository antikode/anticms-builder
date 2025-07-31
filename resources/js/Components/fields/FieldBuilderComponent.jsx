import { pluck } from "@/lib/utils";
import { Accordion } from "@/Components/ui/accordion";
import Builder from "./Builder.jsx";
import Fields from "@/Components/fields/Fields.jsx";

export default function FieldBuilderComponent({ components, data, setData, errors, languages, defaultLanguage, selectedIndex, setSelectedIndex }) {
  const plucked = pluck(components, "keyName");
  return (
    <div className={`flex flex-col mt-4`}>
      {components.filter(item => !plucked.includes(item.keyName)).map(function(item, index) {
        return (
          <Builder
            key={index}
            data={data}
            setData={setData}
            item={item}
            errors={errors}
            languages={languages}
            defaultLanguage={defaultLanguage}
            selectedIndex={selectedIndex}
            setSelectedIndex={setSelectedIndex} />
        );
      })}
      {components.filter(item => plucked.includes(item.keyName)).map(function(item, index) {
        return (
          <Accordion defaultValue={plucked} type="multiple" key={index}>
            <Fields
              data={data}
              setData={setData}
              item={item}
              errors={errors}
              languages={languages}
              defaultLanguage={defaultLanguage}
              selectedIndex={selectedIndex}
              setSelectedIndex={setSelectedIndex} />
          </Accordion>
        );
      })}
    </div>
  );
}
