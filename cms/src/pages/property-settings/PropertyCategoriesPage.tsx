import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { Button } from '@/components/atoms/Button'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import {
  categoriesAPI,
  type PropertyCategory,
} from '@/api/property-settings/categoriesAPIHandler'
import type { DataTablesColumnRequest, DataTablesRequest, SortDir } from '@/types/api'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root }, 
  { label: 'Project Category' }
]

const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'id', searchable: true, orderable: false },
  { name: 'name', searchable: true, orderable: true },
  { name: 'action', searchable: false, orderable: false },
]

const DEFAULT_SORT_KEY = 'name'
const DEFAULT_SORT_DIR: SortDir = 'asc'

export default function PropertyCategoriesPage() {
  const [data, setData] = useState<PropertyCategory[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  const [sortKey, setSortKey] = useState<string>(DEFAULT_SORT_KEY)
  const [sortDir, setSortDir] = useState<SortDir>(DEFAULT_SORT_DIR)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  const drawRef = useRef(0)

  const columns: DataTableColumn<PropertyCategory>[] = useMemo(
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
        cell: (row) => row.name,
      },
      {
        key: 'action',
        header: 'Action',
        width: '120px',
        cell: (row) => (
          <Link to={ROUTES.propertySettings.categories.edit(row.id)}>
            <Button color="primary" size="sm" className="h-auto py-1.5">
              Edit
            </Button>
          </Link>
        ),
      },
    ],
    []
  )

  const fetchCategories = useCallback(() => {
    setLoading(true)
    drawRef.current += 1
    const draw = drawRef.current

    const sortIndex = COLUMN_META.findIndex((c) => c.name === sortKey)
    const start = (page - 1) * perPage

    const payload: DataTablesRequest = {
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
    }

    categoriesAPI
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
    fetchCategories()
  }, [fetchCategories])

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
        <Link to={ROUTES.propertySettings.categories.add}>
          <Button color="primary" className="h-auto px-6 py-2">
            Add
          </Button>
        </Link>
      </div>

      <DataTable<PropertyCategory>
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
    </div>
  )
}
