import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import Builder from "../../../Components/fields/Builder.jsx";
import Modal from "@/Components/Modal.jsx";

/**
 * Renders dynamic CRUD actions with handling for modal forms.
 *
 * @param {Array} actions - Action configs from backend (see PHP addAction array)
 * @param {Object} [modalProps] - Optional override for Modal props
 * @param {Array} [languages] - Supported languages for form fields (optional, if your forms are multilingual)
 * @param {string} [defaultLanguage] - Default language code
 */
export default function ActionBuilders({ actions, modalProps, languages, defaultLanguage }) {
  const [openModal, setOpenModal] = useState(null);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleSubmit = (action, idx) => {
    setLoading(true);
    setErrors({});
    if (method === "post") {
      router.post(action.route, formData, {
        onError: (e) => setErrors(e),
        onFinish: () => setLoading(false),
        onSuccess: () => setOpenModal(null),
        preserveScroll: true
      });
    } else if (["put", "patch", "delete"].includes(method)) {
      // Method spoofing for Inertia.js forms
      router.post(
        action.route,
        { ...formData, _method: method },
        {
          onError: (e) => setErrors(e),
          onFinish: () => setLoading(false),
          onSuccess: () => setOpenModal(null),
          preserveScroll: true
        }
      );
    }
  };


  return (
    <>
      {actions?.map((action, idx) => {
        if (action.hide) return null;
        const hasForm = Array.isArray(action.form) && action.form.length > 0;
        const method = (action.method || "get").toLowerCase();
        if (hasForm) {
          let formProps = {
            method,
            onSubmit: (e) => {
              e.preventDefault();
              if (method === "get") {
                window.location.href = action.route + "?" + new URLSearchParams(formData).toString();
              } else {
                handleSubmit(action, idx);
              }
            }
          }
          return (
            <span key={idx}>
              <button
                type="button"
                className={`btn_${action.color} text-sm mr-2`}
                onClick={() => {
                  setFormData({});
                  setOpenModal(idx);
                }}
                disabled={action.disabled}
                hidden={action.hide}
              >
                {action.name}
              </button>
              <Modal
                show={openModal === idx}
                closeable={false}
                onclose={() => setOpenModal(null)}
                title={action.name}
                {...modalProps}
              >
                <form
                  {...formProps}
                >
                  <div className="flex justify-center bg-primary text-white py-3">
                    {action.name}
                  </div>
                  <div className="p-4">
                    {action.form.map((item, i) => (
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
                    <div className="flex justify-end mt-4">
                      <button
                        type="button"
                        className="btn_secondary mr-2"
                        onClick={() => setOpenModal(null)}
                      >
                        Cancel
                      </button>
                      <button
                        type="submit"
                        className={`btn_${action.color}`}
                        disabled={loading}
                      >
                        {loading ? "Submitting..." : "Submit"}
                      </button>
                    </div>
                  </div>
                </form>
              </Modal>
            </span>
          );
        }

        if (method === "get") {
          return (
            <Link
              key={idx}
              href={action.route}
              className={`btn_${action.color} text-sm mr-2`}
              disabled={action.disabled}
              hidden={action.hide}
            >
              {action.name}
            </Link>
          );
        } else {
          return (
            <button
              type="button"
              key={idx}
              className={`btn_${action.color} text-sm mr-2`}
              onClick={() => router.visit(action.route, { method })}
              disabled={action.disabled}
              hidden={action.hide}
            >
              {action.name}
            </button>
          );
        }
      })}
    </>
  );
}
