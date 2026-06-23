import { useCallback, useEffect, useRef, useState } from "react"
import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { DataTable, type DataTableColumn } from "@/components/atoms/DataTable"
import { contactsAPI, type ContactDetail } from "@/api/contacts/contactsAPIHandler"
import { ROUTES } from "@/constants/routes"
import type {
  DataTablesColumnRequest,
  DataTablesRequest,
  SortDir,
} from "@/types/api"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Contact Us" },
]

// ── Column config ────────────────────────────────────────────────────────────
// `key` is also the DataTables.net `name` field — keep them in sync with the
// backend's expected column names.

const columns: DataTableColumn<ContactDetail>[] = [
  {
    key: "firstname",
    header: "Firstname",
    width: "140px",
    sortable: true,
    cell: (row) => row.firstname || "—",
    cellClassName: "font-medium text-gray-700",
  },
  {
    key: "lastname",
    header: "Lastname",
    width: "140px",
    sortable: true,
    cell: (row) => row.lastname || "—",
  },
  {
    key: "email",
    header: "Email",
    width: "220px",
    sortable: true,
  },
  {
    key: "subject",
    header: "Subject",
    width: "200px",
    sortable: true,
    cell: (row) => row.subject || "—",
  },
  {
    key: "message",
    header: "Message",
    sortable: true,
    cell: (row) => (
      <span className="line-clamp-2 text-gray-600" title={row.message ?? ""}>
        {row.message || "—"}
      </span>
    ),
  },
]

// Column metadata for DataTables.net request. Mirrors the `columns` array above.
const COLUMN_META: Pick<DataTablesColumnRequest, "name" | "searchable" | "orderable">[] = [
  { name: "firstname", searchable: true, orderable: true },
  { name: "lastname",  searchable: true, orderable: true },
  { name: "email",     searchable: true, orderable: true },
  { name: "subject",   searchable: true, orderable: true },
  { name: "message",   searchable: true, orderable: true },
]

export default function ContactUsPage() {
  const [data, setData] = useState<ContactDetail[]>([])
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

  const fetchContacts = useCallback(() => {
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

    contactsAPI
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
    fetchContacts()
  }, [fetchContacts])

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
    <div className="space-y-10">
      <Breadcrumb items={BREADCRUMB} />

      <DataTable<ContactDetail>
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
