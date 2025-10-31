import Heading from "@/Components/global/Heading.jsx";
import CopyContentButton from "@/Components/form/CopyContentButton.jsx";
import SEOSettings from "@/Components/form/SEOSettings.jsx";
import SlugSection from "@/Components/form/SlugSection.jsx";
import StatusSection from "@/Components/form/StatusSection";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import FieldBuilderComponent from "../fields/FieldBuilderComponent";
import { Link } from "@inertiajs/react";
import CategoryTags from "@/Components/form/CategoryTags";
import AuthorsSection from "@/Components/form/AuthorsSection";
import { Button } from "@/Components/ui/button";

export default function CreateEditFormWithBuilder({
  languages,
  title,
  data,
  setData,
  errors,
  submit,
  isSubmitting,
  defaultLanguage,
  selectedIndex,
  setSelectedIndex,
  setting,
  components,
  type,
  authors,
  resource,
  sluggify = 'title',
  hasMeta = true,
  hasStatus = true,
  hasAuthors = true,
  statusOptions = [],
}) {
  return (
    <div className="flex flex-col gap-4 w-full relative">
      <Tabs
        value={languages[selectedIndex].code}
        onValueChange={(value) => {
          setSelectedIndex(languages.findIndex((lang) => lang.code === value));
        }}
        className="w-full"
      >
        <div className="sticky -top-6 py-4 z-10 bg-white w-full">
          {/* header section */}
          <div className="flex justify-between items-center">
            <Heading title={title} />
            <div className="flex items-center gap-2">
              <TabsList>
                {languages.map((lang) => (
                  <TabsTrigger
                    key={lang.code}
                    value={lang.code}
                    className={
                      errors?.[`translations.${lang.code}`]
                        ? "ring-2 ring-destructive"
                        : ""
                    }
                  >
                    <span className="flex items-center justify-center gap-2">
                      {lang.name}
                      {errors?.[`translations.${lang.code}`] && (
                        <span className="w-2 h-2 bg-destructive rounded-full" />
                      )}
                    </span>
                  </TabsTrigger>
                ))}
              </TabsList>

              <CopyContentButton
                languages={languages}
                selectedIndex={selectedIndex}
                setData={setData}
              />
            </div>
          </div>
          {/* header section */}
        </div>
        <div className="flex flex-row justify-between gap-4 w-full my-4">
          {/* left section */}
          <div className="flex-1">
            {languages.map((lang) => (
              <TabsContent
                key={lang.code}
                value={lang.code}
                className="space-y-4"
              >
                <FieldBuilderComponent
                  data={data}
                  setData={setData}
                  errors={errors}
                  components={components}
                  languages={languages}
                  defaultLanguage={defaultLanguage}
                  selectedIndex={selectedIndex}
                  setSelectedIndex={setSelectedIndex}
                />
              </TabsContent>
            ))}
            {(!hasStatus && !sluggify && !hasMeta) && (
              <div className="flex gap-x-2 w-1/2 justify-self-end justify-end">
                <Link href={route(`${resource}.index`)}>
                  <Button
                    variant="link"
                    className={`!py-1 !w-full mt-4`}
                  >
                    Cancel
                  </Button>
                </Link>
                <Button
                  onClick={submit}
                  disabled={isSubmitting}
                  loading={isSubmitting}
                  className={`!py-1 mt-4`}
                >
                  Save Changes
                </Button>
              </div>
            )}
          </div>
          {/* left section */}

          {/* right section */}
          {(hasStatus || sluggify || hasMeta) && (
            <div className="flex flex-col gap-4 max-w-[300px] w-full mt-5">
              {hasStatus && (
                <StatusSection
                  options={statusOptions}
                  data={data}
                  setData={setData}
                  errors={errors}
                  onSubmit={submit}
                  processing={isSubmitting}
                />
              )}
              {sluggify && (
                <SlugSection
                  data={data}
                  setData={setData}
                  errors={errors}
                  defaultLanguage={defaultLanguage}
                  sluggifyParam={sluggify}
                />
              )}
              {hasAuthors && (
                <AuthorsSection
                  data={data}
                  setData={setData}
                  authors={authors}
                  errors={errors}
                />
              )}
              {(type === "post") || (hasStatus || sluggify || hasMeta) && (
                <CategoryTags data={data} setData={setData} />
              )}
              {(type === "post" ? setting?.is_enable_seo : hasMeta ?? true) && (
                <SEOSettings
                  data={data}
                  setData={setData}
                  selectedIndex={selectedIndex}
                  setSelectedIndex={setSelectedIndex}
                  languages={languages}
                  errors={errors}
                />
              )}
            </div>
          )}
          {/* right section */}
        </div>
      </Tabs>
    </div>
  );
}

