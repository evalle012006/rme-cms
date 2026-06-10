import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Button } from '@/components/atoms/Button'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { DataTable, type DataTableColumn } from '@/components/atoms/DataTable'
import { tagsAPI, type Tag } from '@/api/tags/tagsAPIHandler'
import { TagFormModal } from '@/pages/tags/TagFormModal'
import { ROUTES } from '@/constants/routes'
import type { DataTablesColumnRequest, DataTablesRequest, SortDir } from '@/types/api'

const BREADCRUMB = [{ label: 'Home', to: ROUTES.root }, { label: 'Manage Tags' }]

const COLUMN_META: Pick<DataTablesColumnRequest, 'name' | 'searchable' | 'orderable'>[] = [
  { name: 'id', searchable: true, orderable: false },
  { name: 'name', searchable: true, orderable: true },
  { name: 'type', searchable: true, orderable: true },
  { name: 'action', searchable: false, orderable: false },
]

export default function TagsPage() {
  const [data, setData] = useState<Tag[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState('')
  // No default sort: first fetch sends order: [] (backend order preserved).
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  const [modalOpen, setModalOpen] = useState(false)
  const [editingTagId, setEditingTagId] = useState<number | null>(null)

  const drawRef = useRef(0)

  const openAddModal = () => {
    setEditingTagId(null)
    setModalOpen(true)
  }

  const openEditModal = (id: number) => {
    setEditingTagId(id)
    setModalOpen(true)
  }

  const closeModal = () => {
    setModalOpen(false)
    setEditingTagId(null)
  }

  const columns: DataTableColumn<Tag>[] = useMemo(
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
      },
      {
        key: 'type',
        header: 'Type',
        width: '160px',
        sortable: true,
        cell: (row) => <span className="text-gray-700 capitalize">{row.type}</span>,
      },
      {
        key: 'action',
        header: 'Action',
        width: '120px',
        cell: (row) => (
          <Button
            color="primary"
            size="sm"
            className="h-auto py-1.5"
            onClick={() => openEditModal(row.id)}
          >
            Edit
          </Button>
        ),
      },
    ],
    []
  )

  const fetchTags = useCallback(() => {
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

    tagsAPI
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
    fetchTags()
  }, [fetchTags])

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
      <div className='flex justify-end'>
        <Button color="primary" className="h-auto px-6 py-2" onClick={openAddModal}>
          Add
        </Button>
      </div>

      <DataTable<Tag>
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

      <TagFormModal
        open={modalOpen}
        tagId={editingTagId}
        onClose={closeModal}
        onSuccess={fetchTags}
      />
    </div>
  )
}
