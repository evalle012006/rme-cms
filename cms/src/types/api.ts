// ── DataTables.net request/response shapes ──────────────────────────────────
// Matches the contract used by Yajra/DataTables on the Laravel backend.

export type SortDir = "asc" | "desc"

export interface DataTablesColumnRequest {
  data: number
  name: string
  searchable: boolean
  orderable: boolean
  search: {
    value: string
    regex: boolean
  }
}

export interface DataTablesOrderRequest {
  column: number
  dir: SortDir
}

export interface DataTablesRequest {
  draw: number
  start: number
  length: number
  search: {
    value: string
    regex: boolean
  }
  order: DataTablesOrderRequest[]
  columns: DataTablesColumnRequest[]
}

export interface DataTablesResponse<T> {
  draw: number
  recordsTotal: number
  recordsFiltered: number
  data: T[]
}
