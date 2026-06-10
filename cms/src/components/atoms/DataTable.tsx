import type { ReactNode } from "react"
import { ArrowDown, ArrowUp, ChevronsUpDown } from "lucide-react"
import { Input } from "@/components/ui/input"
import { SelectDropdown } from "@/components/atoms/SelectDropdown"
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from "@/components/ui/table"
import { Pagination } from "./Pagination"
import type { SortDir } from "@/types/api"
import { cn } from "@/lib/utils"

// ── Column definition ────────────────────────────────────────────────────────

export interface DataTableColumn<T> {
  key: string
  header: string
  sortable?: boolean
  // Fixed column width (e.g. "80px", "20%"). Required for the fixed table
  // layout to lock columns so they don't shift between skeleton/data states.
  // Columns without `width` share the remaining space equally.
  width?: string
  cell?: (row: T) => ReactNode
  headerClassName?: string
  cellClassName?: string
}

// ── Props ────────────────────────────────────────────────────────────────────

interface DataTableProps<T> {
  columns: DataTableColumn<T>[]
  data: T[]
  page: number
  perPage: number
  total: number
  from: number | null
  to: number | null
  lastPage: number
  search: string
  loading?: boolean
  sortKey?: string
  sortDir?: SortDir
  onPageChange: (page: number) => void
  onPerPageChange: (perPage: number) => void
  onSearchChange: (search: string) => void
  onSortChange?: (key: string, dir: SortDir) => void
}

const PER_PAGE_OPTIONS = [
  { value: "5", label: "5" },
  { value: "10", label: "10" },
  { value: "25", label: "25" },
  { value: "50", label: "50" },
]

// ── Component ────────────────────────────────────────────────────────────────

export function DataTable<T extends { id: number | string }>({
  columns,
  data,
  page,
  perPage,
  total,
  from,
  to,
  lastPage,
  search,
  loading,
  sortKey,
  sortDir,
  onPageChange,
  onPerPageChange,
  onSearchChange,
  onSortChange,
}: DataTableProps<T>) {
  const handleSortClick = (col: DataTableColumn<T>) => {
    if (!col.sortable || !onSortChange) return
    // Toggle direction if same column, otherwise default to asc.
    const nextDir: SortDir =
      sortKey === col.key && sortDir === "asc" ? "desc" : "asc"
    onSortChange(col.key, nextDir)
  }

  return (
    <div className="app-border rounded-card bg-white px-4 shadow-sm">
      {/* Toolbar */}
      <div className="flex flex-wrap items-center justify-between gap-4 py-3">
        <div className="flex items-center gap-2 text-sm text-gray-600">
          <span>Show</span>
          <SelectDropdown
            options={PER_PAGE_OPTIONS}
            value={String(perPage)}
            onChange={(e) => onPerPageChange(Number(e.target.value))}
            className="w-20 !py-1.5"
          />
          <span>entries</span>
        </div>

        <Input
          type="search"
          placeholder="Search..."
          value={search}
          onChange={(e) => onSearchChange(e.target.value)}
          className="w-56 !py-1.5"
        />
      </div>

      {/* Table */}
      <Table className="table-fixed">
          <colgroup>
            {columns.map((col) => (
              <col key={col.key} style={col.width ? { width: col.width } : undefined} />
            ))}
          </colgroup>
          <TableHeader>
            <TableRow className="border-t !border-b-3 border-gray-400 bg-gray-100 hover:bg-gray-100">
              {columns.map((col) => {
                const isActive = col.sortable && sortKey === col.key
                return (
                  <TableHead
                    key={col.key}
                    className={cn(
                      "px-4",
                      col.sortable && "cursor-pointer select-none",
                      col.headerClassName
                    )}
                    onClick={() => handleSortClick(col)}
                  >
                    <span className="inline-flex items-center gap-1.5">
                      {col.header}
                      {col.sortable && <SortIcon active={isActive} dir={sortDir} />}
                    </span>
                  </TableHead>
                )
              })}
            </TableRow>
          </TableHeader>
          <TableBody>
            {/*
              Render strategy:
                - Loading (initial or refetch)  → skeleton rows with one cell
                  per column. Per-column cells keep header widths stable, and
                  the previous data is replaced so the user doesn't see stale
                  rows while new data is fetched.
                - Empty (not loading, no data)  → centered "No records found".
                - Loaded                        → real data rows.
            */}
            {loading ? (
              Array.from({ length: skeletonRowCount(data.length, perPage) }).map(
                (_, i) => (
                  <TableRow key={`skeleton-${i}`}>
                    {columns.map((col) => (
                      <TableCell
                        key={col.key}
                        className={cn("px-4", col.cellClassName)}
                      >
                        <div className="h-4 w-3/5 animate-pulse rounded bg-gray-200" />
                      </TableCell>
                    ))}
                  </TableRow>
                )
              )
            ) : data.length === 0 ? (
              <TableRow>
                <TableCell
                  colSpan={columns.length}
                  className="h-32 px-4 text-center text-gray-400"
                >
                  No records found
                </TableCell>
              </TableRow>
            ) : (
              data.map((row) => (
                <TableRow key={row.id}>
                  {columns.map((col) => (
                    <TableCell
                      key={col.key}
                      className={cn("px-4", col.cellClassName)}
                    >
                      {col.cell
                        ? col.cell(row)
                        : String((row as Record<string, unknown>)[col.key] ?? "")}
                    </TableCell>
                  ))}
                </TableRow>
              ))
            )}
          </TableBody>
        </Table>

      {/* Pagination */}
      <Pagination
        currentPage={page}
        lastPage={lastPage}
        from={from}
        to={to}
        total={total}
        onPageChange={onPageChange}
      />
    </div>
  )
}

// ── Skeleton row count ───────────────────────────────────────────────────────
// On initial load there's no prior data, so fall back to 5. On refetch use the
// previous row count so the table height doesn't jump. Cap at perPage as a
// sensible upper bound.

function skeletonRowCount(currentRows: number, perPage: number): number {
  if (currentRows === 0) return Math.min(5, perPage)
  return Math.min(currentRows, perPage)
}

// ── Sort icon ────────────────────────────────────────────────────────────────

function SortIcon({ active, dir }: { active: boolean | undefined; dir: SortDir | undefined }) {
  if (!active) {
    return <ChevronsUpDown size={14} className="text-gray-400" />
  }
  return dir === "asc" ? (
    <ArrowUp size={14} className="text-gray-700" />
  ) : (
    <ArrowDown size={14} className="text-gray-700" />
  )
}
