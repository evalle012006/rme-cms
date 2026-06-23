import { cn } from "@/lib/utils"

interface PaginationProps {
  currentPage: number
  lastPage: number
  from: number | null
  to: number | null
  total: number
  onPageChange: (page: number) => void
}

export function Pagination({
  currentPage,
  lastPage,
  from,
  to,
  total,
  onPageChange,
}: PaginationProps) {
  const pages = buildPageNumbers(currentPage, lastPage)

  const handlePageChange = (page: number) => {
    onPageChange(page)
    window.scrollTo(0, 0)
  }

  return (
    <div className="flex flex-wrap items-center justify-between gap-4 py-3">
      <p className="text-sm text-gray-600">
        {total === 0
          ? "No entries found"
          : `Showing ${from ?? 0} to ${to ?? 0} of ${total} entries`}
      </p>

      {lastPage > 0 && (
        <div className="flex items-center gap-1">
          <PageButton
            onClick={() => handlePageChange(1)}
            disabled={currentPage <= 1}
          >
            First
          </PageButton>
          <PageButton
            onClick={() => handlePageChange(currentPage - 1)}
            disabled={currentPage <= 1}
          >
            Previous
          </PageButton>

          {pages.map((page, i) =>
            page === "..." ? (
              <span key={`ellipsis-${i}`} className="px-2 text-sm text-gray-400">
                …
              </span>
            ) : (
              <PageButton
                key={page}
                active={page === currentPage}
                onClick={() => handlePageChange(page as number)}
              >
                {page}
              </PageButton>
            )
          )}

          <PageButton
            onClick={() => handlePageChange(currentPage + 1)}
            disabled={currentPage >= lastPage}
          >
            Next
          </PageButton>
          <PageButton
            onClick={() => handlePageChange(lastPage)}
            disabled={currentPage >= lastPage}
          >
            Last
          </PageButton>
        </div>
      )}
    </div>
  )
}

// ── Page button ──────────────────────────────────────────────────────────────

interface PageButtonProps {
  children: React.ReactNode
  active?: boolean
  disabled?: boolean
  onClick: () => void
}

function PageButton({ children, active, disabled, onClick }: PageButtonProps) {
  return (
    <button
      type="button"
      disabled={disabled}
      onClick={onClick}
      className={cn(
        "min-w-[2rem] rounded px-2.5 py-1.5 text-xs font-medium transition-colors",
        active
          ? "bg-primary-500 text-white"
          : "border border-gray-300 bg-white text-gray-600 hover:bg-gray-50",
        disabled && "pointer-events-none opacity-50"
      )}
    >
      {children}
    </button>
  )
}

// ── Page-number builder (1 … 4 [5] 6 … 10) ─────────────────────────────────

function buildPageNumbers(
  current: number,
  last: number
): (number | "...")[] {
  if (last <= 7) {
    return Array.from({ length: last }, (_, i) => i + 1)
  }

  const pages: (number | "...")[] = [1]

  if (current > 3) pages.push("...")

  const start = Math.max(2, current - 1)
  const end = Math.min(last - 1, current + 1)

  for (let i = start; i <= end; i++) pages.push(i)

  if (current < last - 2) pages.push("...")

  pages.push(last)

  return pages
}
