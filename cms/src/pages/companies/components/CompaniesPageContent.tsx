import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/atoms/Button'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import { StatusBadge, type StatusTone } from '@/components/atoms/StatusBadge'
import {
  companiesAPI,
  COMPANY_STATUS,
  type Company,
} from '@/api/companies/companiesAPIHandler'
import type { DataTablesColumnRequest, SortDir } from '@/types/api'
import { ROUTES } from '@/constants/routes'
import { DeleteCompanyDialog } from './DeleteCompanyDialog'


const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'company_name', searchable: true, orderable: true },
  { name: 'status', searchable: false, orderable: false },
  { name: 'action', searchable: false, orderable: false },
]

const DEFAULT_SORT_KEY = 'company_name'
const DEFAULT_SORT_DIR: SortDir = 'asc'

export type CompaniesPageContentProps = {
  statusFilter: number
  breadcrumbLabel: string
  statusLabel: string
  statusTone: StatusTone
  flipStatusTo?: number
  flipConfirmMessage?: string
  flipSuccessMessage?: string
}

export function CompaniesPageContent({
  statusFilter,
  breadcrumbLabel,
  statusLabel,
  statusTone,
  flipStatusTo,
  flipConfirmMessage,
  flipSuccessMessage,
}: CompaniesPageContentProps) {
  const [data, setData] = useState<Company[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  const [sortKey, setSortKey] = useState<string>(DEFAULT_SORT_KEY)
  const [sortDir, setSortDir] = useState<SortDir>(DEFAULT_SORT_DIR)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)
  const [confirmId, setConfirmId] = useState<number | null>(null)

  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [companyToDelete, setCompanyToDelete] = useState<Company | null>(null)
  // const [deleting, setDeleting] = useState(false)

  const isPendingList = statusFilter === COMPANY_STATUS.PENDING

  const drawRef = useRef(0)

  const breadcrumb = useMemo(
    () => [{ label: 'Home', to: ROUTES.root }, { label: breadcrumbLabel }],
    [breadcrumbLabel]
  )

  const openDeleteDialog = useCallback((company: Company) => {
    setCompanyToDelete(company)
    setDeleteDialogOpen(true)
  }, [])

  const closeDeleteDialog = useCallback(() => {
    setDeleteDialogOpen(false)
    setCompanyToDelete(null)
  }, [])

  const columns: DataTableColumn<Company>[] = useMemo(
    () => [
      {
        key: 'company_name',
        header: 'Company',
        sortable: true,
        cell: (row) => row.company_name,
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
        key: 'action',
        header: 'Action',
        width: isPendingList ? '180px' : '120px',
        cell: (row) => (
          <div className="flex items-center gap-2">
            <Link
              to={`${ROUTES.company.edit(row.id)}?from=${
                statusFilter === COMPANY_STATUS.APPROVED ? 'approved' : 'pending'
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
                onClick={() => openDeleteDialog(row)}
              >
                Delete
              </Button>
            )}
          </div>
        ),
      },
    ],
    [flipStatusTo, isPendingList, openDeleteDialog, statusFilter, statusLabel, statusTone]
  )

  const fetchCompanies = useCallback(() => {
    setLoading(true)
    drawRef.current += 1
    const draw = drawRef.current

    const sortIndex = COLUMN_META.findIndex((c) => c.name === sortKey)
    const start = (page - 1) * perPage

    companiesAPI
      .datatable({
        draw,
        start,
        length: perPage,
        search: { value: search, regex: false },
        order: sortIndex >= 0 ? [{ column: sortIndex, dir: sortDir }] : [],
        columns: COLUMN_META.map((meta, index) => ({
          data: index,
          name: meta.name,
          searchable: meta.searchable,
          orderable: meta.orderable,
          search: { value: '', regex: false },
        })),
        status: statusFilter,
      })
      .then((res) => {
        if (res.draw !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir, statusFilter])

  useEffect(() => {
    fetchCompanies()
  }, [fetchCompanies])

  const confirmStatusFlip = useCallback(async () => {
    if (confirmId === null || flipStatusTo === undefined) return
    try {
      await companiesAPI.updateStatus(
        { id: confirmId, status: flipStatusTo },
        { successMessage: flipSuccessMessage }
      )
      fetchCompanies()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    }
  }, [confirmId, flipStatusTo, flipSuccessMessage, fetchCompanies])

  const handleConfirmDelete = async () => {
    if (!companyToDelete) return

    // setDeleting(true)
    try {
      await companiesAPI.delete(companyToDelete.id, {
        successMessage: 'Company deleted successfully',
        customErrorMessage: 'Failed to delete company',
      })
      closeDeleteDialog()
      fetchCompanies()
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      // setDeleting(false)
    }
  }

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

      <div className="flex justify-end">
        <Link to={`${ROUTES.company.add}?status=${statusFilter}`}>
          <Button color="primary" className="h-auto px-4 py-2">
            Add
          </Button>
        </Link>
      </div>

      <DataTable<Company>
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

      <DeleteCompanyDialog
        open={deleteDialogOpen}
        companyName={companyToDelete?.company_name}
        // loading={deleting}
        onOpenChange={(open) => {
          if (!open) closeDeleteDialog()
        }}
        onConfirm={handleConfirmDelete}
      />
    </div>
  )
}
