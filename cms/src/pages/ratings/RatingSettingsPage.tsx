import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/atoms/Button'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import { ratingsAPI, type Rating } from '@/api/ratings/ratingsAPIHandler'
import { ROUTES } from '@/constants/routes'
import type { DataTablesColumnRequest, DataTablesRequest, SortDir } from '@/types/api'

const BREADCRUMB = [{ label: 'Home', to: ROUTES.root }, { label: 'Rating Setting' }]

function decodeHtml(text: string): string {
  const el = document.createElement('textarea')
  el.innerHTML = text
  return el.value
}

const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'id', searchable: true, orderable: false },
  { name: 'name', searchable: true, orderable: true },
  { name: 'action', searchable: false, orderable: false },
]

export default function RatingSettingsPage() {
  const [data, setData] = useState<Rating[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  // No default sort: first fetch sends order: [] (backend order preserved).
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [ratingToDelete, setRatingToDelete] = useState<Rating | null>(null)

  const drawRef = useRef(0)

  const openDeleteDialog = useCallback((rating: Rating) => {
    setRatingToDelete(rating)
    setDeleteDialogOpen(true)
  }, [])

  const closeDeleteDialog = useCallback(() => {
    setDeleteDialogOpen(false)
    setRatingToDelete(null)
  }, [])

  const columns: DataTableColumn<Rating>[] = useMemo(
    () => [
      {
        key: 'id',
        header: 'ID',
        width: '80px',
        cellClassName: 'font-medium text-gray-700',
      },
      {
        key: 'name',
        header: 'Name',
        sortable: true,
        cell: (row) => decodeHtml(row.name),
      },
      {
        key: 'action',
        header: 'Action',
        width: '180px',
        cell: (row) => (
          <div className="flex items-center gap-2">
            <Link to={ROUTES.rating.edit(row.id)}>
              <Button color="primary" size="sm" className="h-auto py-1.5">
                Edit
              </Button>
            </Link>
            <Button
              color="destructive"
              size="sm"
              className="h-auto py-1.5"
              onClick={() => openDeleteDialog(row)}
            >
              Delete
            </Button>
          </div>
        ),
      },
    ],
    [openDeleteDialog]
  )

  const fetchRatings = useCallback(() => {
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

    ratingsAPI
      .datatable(payload)
      .then((res) => {
        if (res.draw !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir])

  useEffect(() => {
    fetchRatings()
  }, [fetchRatings])

  const handleConfirmDelete = async () => {
    if (!ratingToDelete) return

    try {
      await ratingsAPI.delete(ratingToDelete.id, {
        successMessage: 'Rating deleted successfully',
        customErrorMessage: 'Failed to delete rating',
      })
      fetchRatings()
    } catch (err) {
      // Toast already shown by the API wrapper / global interceptor.
      // Re-throw so the ConfirmDialog knows the action failed.
      throw err
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
      <Breadcrumb items={BREADCRUMB} />
      <div className="flex justify-end">
        <Link to={ROUTES.rating.add}>
          <Button color="primary" className="h-auto px-4 py-2">
            Add
          </Button>
        </Link>
      </div>

      <DataTable<Rating>
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
        open={deleteDialogOpen}
        title="Delete?"
        description="Delete this rating? This cannot be undone."
        confirmLabel="Delete"
        cancelLabel="Cancel"
        onOpenChange={(open) => {
          if (!open) closeDeleteDialog()
        }}
        onConfirm={handleConfirmDelete}
      />
    </div>
  )
}
