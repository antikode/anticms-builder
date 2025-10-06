export default function ErrorField({ errors, name, itemIndex, langCode }) {
  let errorField = name;
  if (langCode) {
    if (itemIndex !== undefined) {
      const strArray = name.split('.');
      const lastStr = strArray.pop();
      const newName = strArray.join('.');
      errorField = `${newName}.translations.${langCode}.${lastStr}`;
    } else {
      errorField = `translations.${langCode}.${name}`;
    }
  }
  return errors && errors[errorField] && (
    <span className="text-red-500 text-sm">{errors[errorField]}</span>
  )
}