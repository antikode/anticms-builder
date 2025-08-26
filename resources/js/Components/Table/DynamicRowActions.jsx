import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import Builder from "../fields/Builder.jsx";
import Modal from "@/Components/Modal.jsx";

/**
 * Dynamic row actions component for handling row-specific actions
 *
 * @param {Array} actions - Row action configurations
 * @param {Object} row - The row data object
 * @param {string} [className] - Additional CSS classes
 */
export default function DynamicRowActions({ actions = [], row, className = "" }) {
  const [openModal, setOpenModal] = useState(null);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const handleSubmit = (action, idx) => {
    setLoading(true);
    setErrors({});

    const method = (action.method || "GET").toLowerCase();
    const data = { ...formData, row_id: row.id };

    const requestOptions = {
      onError: (e) => setErrors(e),
      onFinish: () => setLoading(false),
      onSuccess: () => setOpenModal(null),
      preserveScroll: true
    };

    if (method === "get") {
      if (action.target === "_blank") {
        const url = new URL(action.route || action.url);
        Object.keys(data).forEach(key => {
          url.searchParams.append(key, data[key]);
        });
        window.open(url.toString(), "_blank");
      } else if (action.useHref) {
        const url = new URL(action.route || action.url);
        Object.keys(data).forEach(key => {
          url.searchParams.append(key, data[key]);
        });
        window.location.href = url.toString();
      } else {
        router.get(action.route || action.url, data, requestOptions);
      }
    } else {
      if (method === "post") {
        router.post(action.route || action.url, data, requestOptions);
      } else {
        router.post(
          action.route || action.url,
          { ...data, _method: method },
          requestOptions
        );
      }
    }
  };

  const handleActionClick = (action, idx) => {
    // Handle confirmation dialogs
    if (action.confirmation) {
      if (!confirm(action.confirmationMessage || "Are you sure?")) {
        return;
      }
    }

    const hasForm = Array.isArray(action.form) && action.form.length > 0;

    if (hasForm) {
      setFormData({ row_id: row.id });
      setOpenModal(idx);
    } else {
      const method = (action.method || "GET").toLowerCase();

      if (method === "get") {
        if (action.target === "_blank") {
          window.open(action.route || action.url, "_blank");
        } else if (action.useHref) {
          window.location.href = action.route || action.url;
        } else {
          router.visit(action.route || action.url);
        }
      } else {
        handleSubmit(action, idx);
      }
    }
  };

  if (!actions || actions.length === 0) {
    return null;
  }

  return (
    <>
      <div className={`flex flex-wrap gap-1 ${className}`}>
        {actions.map((action, idx) => {
          console.log('Rendering action:', action);
          if (action.hide) return null;

          // Build button classes with styling support
          const baseSize = action.styles?.size === 'xs' ? 'text-xs px-1 py-0.5' :
                          action.styles?.size === 'sm' ? 'text-sm px-2 py-1' :
                          action.styles?.size === 'md' ? 'text-base px-3 py-2' :
                          action.styles?.size === 'lg' ? 'text-lg px-4 py-2' :
                          'text-xs px-2 py-1'; // default

          const colorClass = action.styles?.variant === 'outline' ? 
                           `btn_outline_${action.color || 'primary'}` :
                           action.styles?.variant === 'ghost' ?
                           `btn_ghost_${action.color || 'primary'}` :
                           `btn_${action.color || 'primary'}`; // solid variant (default)

          const buttonClass = action.styles?.buttonClass || 
                            `${colorClass} ${baseSize}`;

          return (
            <div key={idx} className="relative">
              <button
                type="button"
                className={buttonClass}
                onClick={() => handleActionClick(action, idx)}
                disabled={action.disabled}
                title={action.tooltip}
              >
                {action.icon && (
                  <i className={`${action.icon} ${action.name ? 'mr-1' : ''} ${action.styles?.iconClass || ''}`}></i>
                )}
                {action.name}
              </button>

              {/* Modal for actions with forms */}
              {Array.isArray(action.form) && action.form.length > 0 && (
                <Modal
                  show={openModal === idx}
                  closeable={!loading}
                  onclose={() => setOpenModal(null)}
                  title={action.name}
                >
                  <form
                    onSubmit={(e) => {
                      e.preventDefault();
                      handleSubmit(action, idx);
                    }}
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
                        />
                      ))}

                      <div className="flex justify-end mt-4">
                        <button
                          type="button"
                          className="btn_secondary mr-2"
                          onClick={() => setOpenModal(null)}
                          disabled={loading}
                        >
                          Cancel
                        </button>
                        <button
                          type="submit"
                          className={action.styles?.buttonClass || `btn_${action.color || 'primary'}`}
                          disabled={loading}
                        >
                          {loading ? "Processing..." : "Confirm"}
                        </button>
                      </div>
                    </div>
                  </form>
                </Modal>
              )}
            </div>
          );
        })}
      </div>
    </>
  );
}
