import { useState } from "react";
import { router } from "@inertiajs/react";
import Builder from "../fields/Builder.jsx";
import Modal from "@/Components/Modal.jsx";
import { Button } from "@/Components/ui/button";

/**
 * Dynamic row actions component for handling row-specific actions
 * Works with client's ActionDropdown component pattern
 * Handles form-based actions with modal dialogs
 *
 * @param {Array} actions - Row action configurations
 * @param {Object} row - The row data object
 * @param {Function} ActionDropdown - Client's ActionDropdown component
 * @param {string} [className] - Additional CSS classes
 */
export default function DynamicRowActions({
  actions = [],
  row,
  ActionDropdown,
  className = ""
}) {
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

  /**
   * Transform plugin actions to client ActionDropdown format
   * Handles special cases like forms, callbacks, and custom routing
   */
  const transformActions = () => {
    return actions.map((action, idx) => {
      // Pass through separators
      if (action.type === 'separator') {
        return action;
      }

      // Handle form-based actions - add callback to open modal
      const hasForm = Array.isArray(action.form) && action.form.length > 0;
      if (hasForm) {
        return {
          ...action,
          type: 'callback',
          callback: () => {
            setFormData({ row_id: row.id });
            setOpenModal(idx);
          }
        };
      }

      // Handle custom routing with target="_blank" or useHref
      if (action.target === "_blank" || action.useHref) {
        const method = (action.method || "GET").toLowerCase();
        return {
          ...action,
          type: 'callback',
          callback: () => {
            if (action.confirmation) {
              if (!confirm(action.confirmationMessage || action.confirmText || "Are you sure?")) {
                return;
              }
            }

            if (method === "get") {
              if (action.target === "_blank") {
                window.open(action.route || action.url, "_blank");
              } else if (action.useHref) {
                window.location.href = action.route || action.url;
              }
            } else {
              const data = { row_id: row.id, _method: method };
              router.post(action.route || action.url, data, {
                preserveScroll: true
              });
            }
          }
        };
      }

      // Handle custom method actions (non-standard HTTP methods)
      const method = (action.method || "GET").toLowerCase();
      if (method !== "get" && method !== "delete" && action.type !== 'delete') {
        return {
          ...action,
          type: 'callback',
          callback: () => {
            if (action.confirmation) {
              if (!confirm(action.confirmationMessage || action.confirmText || "Are you sure?")) {
                return;
              }
            }

            const requestData = { row_id: row.id };
            const requestOptions = {
              preserveScroll: true
            };

            if (method === "post") {
              router.post(action.route || action.url, requestData, requestOptions);
            } else {
              router.post(
                action.route || action.url,
                { ...requestData, _method: method },
                requestOptions
              );
            }
          }
        };
      }

      // Pass through standard action and delete types
      // Ensure data contains row id if not already provided
      return {
        ...action,
        data: action.data || { id: row.id }
      };
    });
  };

  if (!actions || actions.length === 0) {
    return null;
  }

  // If ActionDropdown component is not provided, return null
  if (!ActionDropdown) {
    console.warn('DynamicRowActions: ActionDropdown component is required');
    return null;
  }

  const transformedActions = transformActions();

  return (
    <>
      <ActionDropdown
        data={row}
        actions={transformedActions}
        className={className}
      />

      {/* Modal for actions with forms */}
      {actions.map((action, idx) => {
        if (!Array.isArray(action.form) || action.form.length === 0) return null;

        return (
          <Modal
            key={idx}
            show={openModal === idx}
            closeable={!loading}
            onclose={() => setOpenModal(null)}
            title={action.name}
          >
            <form
              onSubmit={(e) => {
                e.preventDefault();
                console.log(action)
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
                  <Button
                    type="button"
                    variant="ghost"
                    className="btn_secondary mr-2"
                    onClick={() => setOpenModal(null)}
                    disabled={loading}
                  >
                    Cancel
                  </Button>
                  <Button
                    type="submit"
                    // className={action.styles?.buttonClass || `btn_${action.color || 'primary'}`}
                    disabled={loading}
                  >
                    {loading ? "Processing..." : "Confirm"}
                  </Button>
                </div>
              </div>
            </form>
          </Modal>
        );
      })}
    </>
  );
}
