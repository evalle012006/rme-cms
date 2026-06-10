import { useCallback, useEffect, useRef, useState } from "react"
import { Link } from "react-router-dom"
import { Button } from "@/components/atoms/Button"
import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { usersAPI, type User } from "@/api/users/usersAPIHandler"
import { ROUTES } from "@/constants/routes"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Users" },
]

// ── Column config ────────────────────────────────────────────────────────────
// `key` is also the DataTables.net `name` field — keep them in sync with the
// backend's expected column names.

const columns: DataTableColumn<User>[] = [
  {
    key: "id",
    header: "ID",
    width: "80px",
    cellClassName: "font-medium text-gray-700",
  },
  {
    key: "email",
    header: "Email",
    sortable: true,
  },
  {
    key: "change_request",
    header: "Change Requests",
    width: "220px",
    cell: (row) => row.has_change_request,
  },
  {
    key: "action",
    header: "Action",
    width: "120px",
    cell: (row) => (
      <Link to={ROUTES.users.edit(row.id)}>
        <Button color="primary" size="sm" className="h-auto py-1.5">
          Edit
        </Button>
      </Link>
    ),
  },
]

// Column metadata for DataTables.net request. Mirrors the `columns` array above.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "id",             searchable: true,  orderable: false },
  { name: "email",          searchable: true,  orderable: true  },
  { name: "change_request", searchable: false, orderable: false },
  { name: "action",         searchable: false, orderable: false },
]

export default function UsersPage() {
  const [data, setData] = useState<User[]>([])
  const [page, setPage] = useState(1)
  const [perPage, setPerPage] = useState(10)
  const [search, setSearch] = useState("")
  // No default sort: initial fetch sends order: [] and no header arrow is active.
  const [sortKey, setSortKey] = useState<string | undefined>(undefined)
  const [sortDir, setSortDir] = useState<SortDir | undefined>(undefined)
  const [total, setTotal] = useState(0)
  const [loading, setLoading] = useState(true)

  // Increments per request; sent back as-is by the backend so we can detect
  // stale responses and ignore them.
  const drawRef = useRef(0)

  const fetchUsers = useCallback(() => {
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
      order: sortIndex >= 0 && sortDir
        ? [{ column: sortIndex, dir: sortDir }]
        : [],
      columns: COLUMN_META.map((meta, index) => ({
        data: index,
        name: meta.name,
        searchable: meta.searchable,
        orderable: meta.orderable,
        search: { value: "", regex: false },
      })),
    }

    usersAPI
      .datatable(payload)
      .then((res) => {
        // Ignore stale responses (user typed/paged again before this one returned)
        if (res.draw !== draw) return
        setData(res.data)
        setTotal(res.recordsFiltered)
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [page, perPage, search, sortKey, sortDir])

  useEffect(() => {
    fetchUsers()
  }, [fetchUsers])

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

  // Derived pagination fields from total + page + perPage.
  const lastPage = Math.max(1, Math.ceil(total / perPage))
  const from = total === 0 ? null : (page - 1) * perPage + 1
  const to = total === 0 ? null : Math.min(page * perPage, total)

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <div className="flex justify-end">
        <Link to={ROUTES.users.add}>
          <Button color="primary" className="h-auto px-6 py-2">
            Add
          </Button>
        </Link>
      </div>

      <DataTable<User>
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
