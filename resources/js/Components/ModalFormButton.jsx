import { useState } from "react";
import { router } from "@inertiajs/react";
import { cn } from "@/lib/utils.jsx";
import Modal from "@/Components/Modal.jsx";
import { Button } from "@/Components/ui/button";
import Builder from "@/vendor/anti-cms-builder/Components/fields/Builder.jsx";

export default function ModalFormButton({
  routeKey,
  data,
  text = "Action",
  method = "post",
  className,
  form = [],
  languages,
  defaultLanguage,
  title,
  color = "primary"
}) {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleSubmit = (e) => {
    e.preventDefault();
    setLoading(true);
    setErrors({});

    // Handle both route keys and direct URLs
    const routeUrl = routeKey.startsWith('http') || routeKey.startsWith('/')
      ? routeKey
      : route(routeKey, data);
    const requestMethod = method.toLowerCase();

    if (requestMethod === "post") {
      router.post(routeUrl, formData, {
        onError: (e) => setErrors(e),
        onFinish: () => setLoading(false),
        onSuccess: () => {
          setOpen(false);
          setFormData({});
        },
        preserveScroll: true
      });
    } else if (["put", "patch", "delete"].includes(requestMethod)) {
      router.post(routeUrl, { ...formData, _method: requestMethod }, {
        onError: (e) => setErrors(e),
        onFinish: () => setLoading(false),
        onSuccess: () => {
          setOpen(false);
          setFormData({});
        },
        preserveScroll: true
      });
    } else if (requestMethod === "get") {
      const queryString = new URLSearchParams(formData).toString();
      window.location.href = routeUrl + (queryString ? "?" + queryString : "");
    }
  };

  return (
    <>
      <span
        onClick={() => {
          setFormData({});
          setOpen(true);
        }}
        className={cn(
          "cursor-pointer text-primary flex justify-start",
          className
        )}
      >
        {text}
      </span>

      <Modal
        show={open}
        closeable={!loading}
        onClose={() => setOpen(false)}
      >
        <div className="bg-white dark:bg-gray-800">
          <div className="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-600">
            <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">
              {title || text}
            </h3>
            <button
              type="button"
              className="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              onClick={() => setOpen(false)}
              disabled={loading}
            >
              <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <form onSubmit={handleSubmit}>
            <div className="p-4 space-y-4">
              {form.map((item, i) => (
                <Builder
                  key={i}
                  item={item}
                  data={formData}
                  setData={setFormData}
                  errors={errors}
                  languages={languages}
                  selectedIndex={0}
                  defaultLanguage={defaultLanguage}
                />
              ))}
            </div>
            <div className="flex items-center justify-end gap-2 p-4 border-t border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700">
              <Button
                type="button"
                variant="outline"
                onClick={() => setOpen(false)}
                disabled={loading}
              >
                Cancel
              </Button>
              <Button
                type="submit"
                variant={color === 'primary' ? 'default' : color === 'success' ? 'success' : 'secondary'}
                loading={loading}
              >
                Submit
              </Button>
            </div>
          </form>
        </div>
      </Modal>
    </>
  );
}
