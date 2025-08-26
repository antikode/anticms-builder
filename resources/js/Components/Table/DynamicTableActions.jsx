import { useState } from "react";
import { Link, router } from "@inertiajs/react";
import Builder from "../fields/Builder.jsx";
import Modal from "@/Components/Modal.jsx";
import { Button } from "@/Components/ui/button";

/**
 * Dynamic table actions component for handling various table-level actions
 *
 * @param {Array} actions - Table-level action configurations
 * @param {Object} selectedRows - Currently selected rows for bulk actions
 * @param {Function} onBulkActionStart - Callback when bulk action starts
 * @param {Function} onBulkActionComplete - Callback when bulk action completes
 */
export default function DynamicTableActions({
  actions = [],
  selectedRows = {},
  onBulkActionStart,
  onBulkActionComplete
}) {
  const [openModal, setOpenModal] = useState(null);
  const [formData, setFormData] = useState({});
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState({});

  const selectedRowIds = Object.keys(selectedRows).filter(id => selectedRows[id]);

  const handleSubmit = (action, idx) => {
    setLoading(true);
    setErrors({});

    const method = (action.method || "GET").toLowerCase();
    const data = { ...formData };

    // Add selected row IDs for bulk actions
    if (action.bulk && selectedRowIds.length > 0) {
      data.selected_ids = selectedRowIds;
    }

    if (onBulkActionStart && action.bulk) {
      onBulkActionStart(action, selectedRowIds);
    }

    const requestOptions = {
      onError: (e) => setErrors(e),
      onFinish: () => {
        setLoading(false);
        if (onBulkActionComplete && action.bulk) {
          onBulkActionComplete(action, selectedRowIds);
        }
      },
      onSuccess: () => setOpenModal(null),
      preserveScroll: true
    };

    if (method === "get") {
      const url = new URL(action.route || action.url);
      Object.keys(data).forEach(key => {
        url.searchParams.append(key, data[key]);
      });
      window.location.href = url.toString();
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

    // Check bulk action requirements
    if (action.bulk) {
      const minSelection = action.minSelection || 1;
      const maxSelection = action.maxSelection || Infinity;

      if (selectedRowIds.length < minSelection) {
        alert(`Please select at least ${minSelection} item(s)`);
        return;
      }

      if (selectedRowIds.length > maxSelection) {
        alert(`Please select no more than ${maxSelection} item(s)`);
        return;
      }
    }

    const hasForm = Array.isArray(action.form) && action.form.length > 0;

    if (hasForm) {
      setFormData({});
      setOpenModal(idx);
    } else {
      const method = (action.method || "GET").toLowerCase();

      if (method === "get") {
        if (action.target === "_blank") {
          window.open(action.route || action.url, "_blank");
        } else {
          window.location.href = action.route || action.url;
        }
      } else {
        handleSubmit(action, idx);
      }
    }
  };

  return (
    <>
      <div className="flex justify-end flex-wrap gap-2 mb-4">
        {actions.map((action, idx) => {
          if (action.hide) return null;

          const isDisabled = action.disabled || (action.bulk && selectedRowIds.length === 0);
          const buttonClass = `btn_${action.color || 'primary'} text-sm`;

          return (
            <div key={idx} className="relative">
              <Button
                className={buttonClass}
                onClick={() => handleActionClick(action, idx)}
                disabled={isDisabled}
                title={action.tooltip}
              >
                {action.icon && (
                  <i className={`${action.icon} mr-1`}></i>
                )}
                {action.name}
                {action.bulk && selectedRowIds.length > 0 && (
                  <span className="ml-1 bg-white text-primary rounded-full px-2 py-0.5 text-xs">
                    {selectedRowIds.length}
                  </span>
                )}
              </Button>

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

                      {action.bulk && selectedRowIds.length > 0 && (
                        <div className="mt-4 p-3 bg-gray-100 rounded">
                          <p className="text-sm text-gray-600">
                            This action will be applied to {selectedRowIds.length} selected item(s).
                          </p>
                        </div>
                      )}

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
                          className={`btn_${action.color || 'primary'}`}
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
