import * as Tabs from '@radix-ui/react-tabs';

export default function LanguageTab({ languages, children, selectedIndex, setSelectedIndex }) {
  return (
    <Tabs.Root value={languages[selectedIndex]?.code} onValueChange={(value) => {
      const newIndex = languages.findIndex(lang => lang.code === value);
      setSelectedIndex(newIndex);
    }}>
      {languages.map((lang) => (
        <Tabs.Content
          key={lang.code}
          value={lang.code}
          className="rounded-xl transition-opacity duration-200 ease-in-out
            data-[state=active]:opacity-100 data-[state=inactive]:opacity-0"
        >
          {typeof children === 'function' ? children(lang) : children}
        </Tabs.Content>
      ))}
    </Tabs.Root>
  );
}