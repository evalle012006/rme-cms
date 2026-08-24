import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Breadcrumb, type BreadcrumbItem } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import { StatusBadge, type StatusTone } from '@/components/atoms/StatusBadge'
import {
  propertiesAPI,
  PROPERTY_STATUS,
  type Property,
  type PropertyStatusFilter,
} from '@/api/properties/propertiesAPIHandler'
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from '@/types/api'
import { ROUTES } from '@/constants/routes'
import { PropertyImportModal } from './components/PropertyImportModal'

// ── Column metadata for `/datatable-property-pending` and
// `/datatable-property-approve` ────────────────────────────────────────────
// The legacy backend uses `name: "id"` for every non-`project_name` column —
// they're not really addressed by name, they're addressed by their index in
// this array (see the `data` field below). Only the first column is sortable.
const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'project_name', searchable: true,  orderable: true  },
  { name: 'id',           searchable: true,  orderable: false },
  { name: 'id',           searchable: true,  orderable: false },
  { name: 'id',           searchable: true,  orderable: false },
]

// ── Category pill ────────────────────────────────────────────────────────────
// Properties typically have a single allowed category in the legacy data, but
// the relation is many-to-many so we render however many come back.

function CategoryPills({ row }: { row: Property }) {
  const categories = row.property_allowed_category ?? []
  if (categories.length === 0) {
    return <span className="text-gray-400">—</span>
  }
  return (
    <div className="flex flex-wrap gap-1.5">
      {categories.map((cat) => (
        <span
          key={cat.id}
          className="inline-flex items-center rounded-md bg-amber-400 px-3 py-1 text-xs font-semibold text-white shadow-sm"
        >
          {cat.name}
        </span>
      ))}
    </div>
  )
}

// ── Shared list page ─────────────────────────────────────────────────────────
// Powers both Pending and Approved tabs of "Properties". The two pages differ
// only by breadcrumb, status filter (1 vs 0), and the status badge.

interface PropertiesListPageProps {
  breadcrumb: BreadcrumbItem[]
  statusFilter: PropertyStatusFilter
  statusLabel: string
  statusTone: StatusTone
  flipStatusTo?: PropertyStatusFilter
  flipConfirmMessage?: string
  flipSuccessMessage?: string
}

export function PropertiesListPage({
  breadcrumb,
  statusFilter,
  statusLabel,
  statusTone,
  flipStatusTo,
  flipConfirmMessage,
  flipSuccessMessage,
}: PropertiesListPageProps) {
  const [data, setData] = useState<Property[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [confirmId, setConfirmId] = useState<number | null>(null)
  const [propertyToDelete, setPropertyToDelete] = useState<Property | null>(null)
  const [importModalOpen, setImportModalOpen] = useState(false)

  // Increments per request; sent back as-is by the backend so we can detect
  // stale responses and ignore them.
  const drawRef = useRef(0)

  const fetchProperties = useCallback(() => {
    setLoading(true)
    drawRef.current += 1
    const draw = drawRef.current

    const sortIndex = sortKey
      ? COLUMN_META.findIndex((c) => c.name === sortKey)
      : -1
    const start = (page - 1) * perPage

    const payload: DataTablesRequest = {
      draw,
      start,
      length: perPage,
      search: { value: search, regex: false },
      order:
        sortIndex >= 0 && sortDir
          ? [{ column: sortIndex, dir: sortDir }]
          : [],
      columns: COLUMN_META.map((meta, index) => ({
        data: index,
        name: meta.name,
        searchable: meta.searchable,
        orderable: meta.orderable,
        search: { value: '', regex: false },
      })),
    }

    propertiesAPI
      .datatable(payload, statusFilter)
      .then((res) => {
        if (res.draw !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir, statusFilter])

  // Reset paging/sort/search when the tab (status filter) changes.
  useEffect(() => {
    setPage(1)
    setSortKey(undefined)
    setSortDir(undefined)
    setSearch('')
  }, [statusFilter])

  useEffect(() => {
    fetchProperties()
  }, [fetchProperties])

  const confirmStatusFlip = useCallback(async () => {
    if (confirmId === null || flipStatusTo === undefined) return
    try {
      if (
        statusFilter === PROPERTY_STATUS.PENDING &&
        flipStatusTo === PROPERTY_STATUS.APPROVED
      ) {
        await propertiesAPI.approve(
          { id: confirmId },
          { successMessage: flipSuccessMessage }
        )
      }
      setConfirmId(null)
      fetchProperties()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [confirmId, flipStatusTo, flipSuccessMessage, fetchProperties, statusFilter])

  const confirmDelete = useCallback(async () => {
    if (!propertyToDelete) return
    try {
      await propertiesAPI.delete(
        { id: propertyToDelete.id },
        {
          successMessage: 'Project deleted successfully',
          customErrorMessage: 'Failed to delete project',
        }
      )
      setPropertyToDelete(null)
      fetchProperties()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [propertyToDelete, fetchProperties])

  const isPendingList = statusFilter === PROPERTY_STATUS.PENDING

  const columns = useMemo<DataTableColumn<Property>[]>(
    () => [
      {
        key: 'project_name',
        header: 'Project Name',
        sortable: true,
        cell: (row) => row.project_name || '—',
        cellClassName: 'font-medium text-gray-700',
      },
      {
        key: 'status',
        header: 'Status',
        width: '160px',
        cell: (row) => (
          <StatusBadge
            label={statusLabel}
            tone={statusTone}
            onClick={
              flipStatusTo !== undefined ? () => setConfirmId(row.id) : undefined
            }
          />
        ),
      },
      {
        key: 'property_category',
        header: 'Project Category',
        cell: (row) => <CategoryPills row={row} />,
      },
      {
        key: 'action',
        header: 'Action',
        width: isPendingList ? '160px' : '120px',
        cell: (row) => (
          <div className="flex items-center gap-2">
            <Link
              to={`${ROUTES.properties.edit(row.id)}?from=${
                statusFilter === PROPERTY_STATUS.APPROVED ? 'approved' : 'pending'
              }`}
            >
              <Button color="primary" size="sm" className="h-auto py-1.5">
                Edit
              </Button>
            </Link>
            {isPendingList && (
              <Button
                type="button"
                color="destructive"
                size="sm"
                className="h-auto py-1.5"
                onClick={() => setPropertyToDelete(row)}
              >
                Delete
              </Button>
            )}
          </div>
        ),
      },
    ],
    [isPendingList, statusFilter, statusLabel, statusTone, flipStatusTo]
  )

  const handlePerPageChange = (newPerPage: number) => {
    setPerPage(newPerPage)
    setPage(1)
  }

  const handleSearchChange = (newSearch: string) => {
    setSearch(newSearch)
    setPage(1)
  }

  const handleSortChange = (key: string, dir: SortDir) => {
    setSortKey(key)
    setSortDir(dir)
    setPage(1)
  }

  const lastPage = Math.max(1, Math.ceil(total / perPage))
  const from = total === 0 ? null : (page - 1) * perPage + 1
  const to = total === 0 ? null : Math.min(page * perPage, total)

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />

      <div className="flex justify-end gap-2">
        <Link
          to={`${ROUTES.properties.add}?from=${
            statusFilter === PROPERTY_STATUS.APPROVED ? 'approved' : 'pending'
          }&status=${statusFilter}`}
        >
          <Button color="primary" className="h-auto px-6 py-2">
            Add
          </Button>
        </Link>
        {isPendingList && (
          <Button
            type="button"
            color="primary"
            className="h-auto px-6 py-2"
            onClick={() => setImportModalOpen(true)}
          >
            Import
          </Button>
        )}
      </div>

      <DataTable<Property>
        columns={columns}
        data={data}
        page={page}
        perPage={perPage}
        total={total}
        from={from}
        to={to}
        lastPage={lastPage}
        search={search}
        loading={loading}
        sortKey={sortKey}
        sortDir={sortDir}
        onPageChange={setPage}
        onPerPageChange={handlePerPageChange}
        onSearchChange={handleSearchChange}
        onSortChange={handleSortChange}
      />

      <ConfirmDialog
        open={confirmId !== null}
        onOpenChange={(next) => {
          if (!next) setConfirmId(null)
        }}
        title="Change status ?"
        description={flipConfirmMessage}
        confirmLabel="Yes"
        cancelLabel="Cancel"
        onConfirm={confirmStatusFlip}
      />

      <PropertyImportModal
        open={importModalOpen}
        onClose={() => setImportModalOpen(false)}
        onSuccess={fetchProperties}
      />

      <ConfirmDialog
        open={propertyToDelete !== null}
        onOpenChange={(next) => {
          if (!next) setPropertyToDelete(null)
        }}
        title="Delete project?"
        description={
          propertyToDelete?.project_name
            ? `Are you sure you want to delete "${propertyToDelete.project_name}"? This action cannot be undone.`
            : 'Are you sure you want to delete this project? This action cannot be undone.'
        }
        tone="warning"
        confirmLabel="Confirm"
        cancelLabel="Cancel"
        onConfirm={confirmDelete}
      />
    </div>
  )
}
