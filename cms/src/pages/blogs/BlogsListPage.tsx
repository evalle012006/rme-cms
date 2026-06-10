import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/atoms/Button'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { ConfirmDialog } from '@/components/atoms/ConfirmDialog'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import { blogsAPI, BLOG_STATUS, type Blog } from '@/api/blogs/blogsAPIHandler'
import type { DataTablesColumnRequest, DataTablesRequest, SortDir } from '@/types/api'
import { cn } from '@/lib/utils'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [{ label: 'Home', to: ROUTES.root }, { label: 'Blogs' }]

// Column metadata for `/database-blog` — index order must match the backend.
const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'title', searchable: true, orderable: true },
  { name: 'id', searchable: true, orderable: false },
  { name: 'status', searchable: true, orderable: false },
  { name: 'title', searchable: true, orderable: false },
]

function CategoryBadge({ label }: { label: string }) {
  return (
    <span className="inline-flex items-center rounded-md bg-amber-300 px-3 py-1 text-xs font-semibold text-gray-900 shadow-sm">
      {label}
    </span>
  )
}

function BlogStatusBadge({ status }: { status: string }) {
  const config =
    {
      [BLOG_STATUS.PUBLISHED]: { label: 'Published', className: 'bg-sky-400' },
      [BLOG_STATUS.DRAFT]: { label: 'Draft', className: 'bg-amber-400' },
      [BLOG_STATUS.ARCHIVE]: { label: 'Archive', className: 'bg-gray-400' },
    }[status] ?? { label: 'Unknown', className: 'bg-gray-400' }

  return (
    <span
      className={cn(
        'inline-flex items-center rounded-md px-3 py-1 text-xs font-semibold text-white shadow-sm',
        config.className
      )}
    >
      {config.label}
    </span>
  )
}

export default function BlogsListPage() {
  const [data, setData] = useState<Blog[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  // No default sort: first fetch sends order: [] (matches the backend spec).
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  const [deleteDialogOpen, setDeleteDialogOpen] = useState(false)
  const [blogToDelete, setBlogToDelete] = useState<Blog | null>(null)

  const drawRef = useRef(0)

  const openDeleteDialog = useCallback((blog: Blog) => {
    setBlogToDelete(blog)
    setDeleteDialogOpen(true)
  }, [])

  const closeDeleteDialog = useCallback(() => {
    setDeleteDialogOpen(false)
    setBlogToDelete(null)
  }, [])

  const columns: DataTableColumn<Blog>[] = useMemo(
    () => [
      {
        key: 'title',
        header: 'Title',
        sortable: true,
        cell: (row) => <span className="text-gray-800">{row.title}</span>,
      },
      {
        key: 'category',
        header: 'Category',
        width: '180px',
        cell: (row) => (
          <div className="flex flex-wrap gap-2">
            {row.blog_categories.map((category) => (
              <CategoryBadge key={category.id} label={category.name} />
            ))}
          </div>
        ),
      },
      {
        key: 'status',
        header: 'Status',
        width: '140px',
        cell: (row) => <BlogStatusBadge status={row.status} />,
      },
      {
        key: 'action',
        header: 'Action',
        width: '180px',
        cell: (row) => (
          <div className="flex items-center gap-2">
            <Link to={ROUTES.blogs.edit(row.id)}>
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

  const fetchBlogs = useCallback(() => {
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

    blogsAPI
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
    fetchBlogs()
  }, [fetchBlogs])

  const handleConfirmDelete = async () => {
    if (!blogToDelete) return

    try {
      await blogsAPI.delete(blogToDelete.id, {
        successMessage: 'Blog deleted successfully',
        customErrorMessage: 'Failed to delete blog',
      })
      fetchBlogs()
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
        <Link to={ROUTES.blogs.add}>
          <Button color="primary" className="h-auto px-6 py-2">
            Add
          </Button>
        </Link>
      </div>

      <DataTable<Blog>
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
        description="Do you really want to delete these records?"
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
