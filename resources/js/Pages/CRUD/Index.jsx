import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.jsx";
import CardWhite from "@/Components/global/CardWhite.jsx";
import Heading from "@/Components/global/Heading.jsx";
import { Link, router, usePage } from "@inertiajs/react";
import { DataTable } from "@/Components/ui/data-table";
import { useCallback, useEffect, useState } from "react";
import { useMemo } from "react";
import { debounce, pickBy } from "lodash";
import { pluck } from "@/lib/utils";
import Builder from "../../Components/fields/Builder.jsx";
import ActionDropdown from "@/Components/ui/data-table/action-dropdown";
import DynamicTableActions from "../../Components/Table/DynamicTableActions.jsx";
import DynamicRowActions from "../../Components/Table/DynamicRowActions.jsx";
import { Button } from "@/Components/ui/button";

export default function Index({ title, tables, resource, actions, permissions, HasEditPermission, HasDeletePermission }) {
  const { filtered } = tables;
  const [params, setParams] = useState(filtered);
  const { languages, defaultLanguage } = usePage().props.app.languages;
  const [selectedIndex, setSelectedIndex] = useState(0);
  const [selectedRows, setSelectedRows] = useState({});
  const headers = useMemo(() => {
    const cols = tables.headers.map((header) => {
      const headerTitle = header.header;
      return {
        ...header,
        header: ({ column }) => (
          <div
            className="flex flex-row gap-1 items-center cursor-pointer"
            onClick={() => header.sortable ? sort(header.column) : null}
          >
            <span>{headerTitle}</span>
            {header.sortable && (
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="21" fill="none" viewBox="0 0 20 21">
                <path
                  stroke="#000"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                  strokeWidth="1.5"
                  d="M8.75 14.25l-2.5 2.5-2.5-2.5M6.25 4.25v12.5M11.25 6.75l2.5-2.5 2.5 2.5M13.75 16.75V4.25"
                ></path>
              </svg>
            )}
          </div>
        ),
        cell: ({ row }) => {
          const val = row.getValue(header.id);
          if (val?.description !== undefined) {
            return (
              <div className="flex flex-col">
                <span>{val.value}</span>
                <span className="font-semibold">{val.description}</span>
              </div>
            );
          }
          return <span dangerouslySetInnerHTML={{ __html: val }}></span>;
        }
      };
    });

    if (!tables.noAction) {
      cols.push({
        id: "action",
        header: "Action",
        cell: ({ row }) => {
          const origin = row.original;
          const id = row.original.id;
          const rowData = { ...origin, id };

          if (rowData._actions && rowData._actions.length > 0) {
            return <DynamicRowActions actions={rowData._actions} row={rowData} />;
          }

          const actions = [
            {
              type: 'action',
              text: 'Edit',
              routeKey: `${resource}.edit`,
              data: { id }
            },
            {
              type: 'separator'
            },
            {
              type: 'delete',
              text: 'Delete',
              routeKey: `${resource}.delete`,
              data: { id }
            }
          ];

          // Add restore and permanent delete for soft deleted items
          if (rowData?.deleted_at != null) {
            return <ActionDropdown
              data={rowData}
              actions={[
                {
                  type: 'action',
                  text: 'Restore',
                  routeKey: `${resource}.restore`,
                  className: '!text-orange-400',
                  data: { id }
                },
                {
                  type: 'delete',
                  text: 'Delete Permanently',
                  routeKey: `${resource}.delete.force`,
                  confirmText: `Are you sure you want to delete "${rowData.title || "this item"}" permanently? This action cannot be undone.`
                }
              ]}
            />;
          }

          return <ActionDropdown data={rowData} actions={actions} />;
        },
      });
    }

    return cols;
  }, [tables, resource]);

  const reload = useCallback(
    debounce((query) => {
      router.get(
        route(route().current()),
        { ...pickBy(query), page: query.q ? 1 : query.page },
        {
          preserveState: true,
          preserveScroll: true,
        }
      );
    }, 150),
    []
  );

  const sort = (e) => {
    setParams({
      ...params,
      field: e,
      direction: params?.direction == "asc" ? "desc" : "asc",
    });
    router.get(
      route(route().current()),
      { ...params, field: e, direction: params?.direction == "asc" ? "desc" : "asc" },
      {
        preserveState: true,
        preserveScroll: true,
      }
    );
  };

  const plucked = pluck(tables.filter, "keyName");

  useEffect(() => reload(params), [params]);

  return (
    <AuthenticatedLayout header={title}>
      <CardWhite breadcrumbs={true}>
        <Heading title={title}>
          {(tables.actions || tables.bulkActions) && (
            <div className="">
              <DynamicTableActions
                actions={[...(tables.actions || []), ...(tables.bulkActions || [])]}
                selectedRows={selectedRows}
                onBulkActionStart={(action, selectedIds) => {
                  console.log('Bulk action started:', action.name, selectedIds);
                }}
                onBulkActionComplete={(action, selectedIds) => {
                  console.log('Bulk action completed:', action.name, selectedIds);
                  setSelectedRows({});
                }}
              />
            </div>
          )}
          {permissions.hasCreatePermission && (
            <Link href={route(`${resource}.create`)} className={`btn_primary text-sm`}>
              <Button>Create {title}</Button>
            </Link>
          )}
        </Heading>

        {/* Dynamic table actions */}
        <DataTable
          params={params}
          setParams={setParams}
          columns={headers}
          data={tables?.rows}
          filter={
            (!tables?.filter || tables.filter.length === 0) ? null : (
              <>
                {tables.filter.filter(item => !plucked.includes(item.keyName)).map((item, index) => (
                  <Builder
                    hideLabel={true}
                    key={index}
                    data={params}
                    setData={setParams}
                    item={item}
                    // errors={errors}
                    languages={languages}
                    defaultLanguage={defaultLanguage}
                    selectedIndex={selectedIndex}
                    setSelectedIndex={setSelectedIndex}
                  />
                ))}
              </>
            )
          }
          pagination={tables?.meta}
        />
      </CardWhite>
    </AuthenticatedLayout>
  );
}

