import { Link } from "react-router-dom"
import { Fragment } from "react"

export interface BreadcrumbItem {
  label: string
  to?: string
}

interface BreadcrumbProps {
  items: BreadcrumbItem[]
}

export function Breadcrumb({ items }: BreadcrumbProps) {
  return (
    // Negative horizontal margins counter the parent `<main>`'s `px-8`
    // so the white background spans the full row width.
    <nav className="-mx-8 flex items-center gap-1.5 app-border-b bg-white px-8 py-3 text-sm">
      {items.map((item, i) => (
        <Fragment key={item.label}>
          {i > 0 && <span className="text-gray-400">/</span>}
          {item.to ? (
            <Link to={item.to} className="text-primary-500 hover:underline">
              {item.label}
            </Link>
          ) : (
            <span className="text-gray-500">{item.label}</span>
          )}
        </Fragment>
      ))}
    </nav>
  )
}
