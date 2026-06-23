import { useState, useRef, useCallback, useEffect } from 'react'
import { createPortal } from 'react-dom'
import { NavLink, useLocation } from 'react-router-dom'
import { cn } from '@/lib/utils'
import type { NavChild } from '@/constants/sidebarNav'
import { sidebarFlyoutSubItemClassName } from '@/components/layouts/sidebar/sidebarNavStyles'

const HOVER_CLOSE_DELAY_MS = 120
const VIEWPORT_EDGE_PADDING = 16
/** ~5–6 submenu rows before scrolling (CMS Pages, etc.) */
const MAX_LIST_HEIGHT_PX = 220

interface SidebarNavFlyoutProps {
  label: string
  collapsed: boolean
  items?: NavChild[]
  children: React.ReactNode
}

export function SidebarNavFlyout({
  label,
  collapsed,
  items,
  children,
}: SidebarNavFlyoutProps) {
  const { pathname } = useLocation()
  const anchorRef = useRef<HTMLDivElement>(null)
  const closeTimerRef = useRef<ReturnType<typeof setTimeout> | null>(null)
  const [open, setOpen] = useState(false)
  const [coords, setCoords] = useState({ top: 0, left: 0 })
  const [listMaxHeight, setListMaxHeight] = useState(MAX_LIST_HEIGHT_PX)

  const isGroupActive =
    items?.some(
      (item) => pathname === item.to || pathname.startsWith(`${item.to}/`)
    ) ?? false

  const updatePosition = useCallback(() => {
    const el = anchorRef.current
    if (!el) return
    const rect = el.getBoundingClientRect()
    const spaceBelow =
      window.innerHeight - rect.top - VIEWPORT_EDGE_PADDING
    const headerEstimate = 44
    const listSpace = Math.max(
      80,
      Math.min(MAX_LIST_HEIGHT_PX, spaceBelow - headerEstimate)
    )

    setCoords({ top: rect.top, left: rect.right + 6 })
    setListMaxHeight(listSpace)
  }, [])

  const clearCloseTimer = useCallback(() => {
    if (closeTimerRef.current) {
      clearTimeout(closeTimerRef.current)
      closeTimerRef.current = null
    }
  }, [])

  const handleOpen = useCallback(() => {
    clearCloseTimer()
    updatePosition()
    setOpen(true)
  }, [clearCloseTimer, updatePosition])

  const handleClose = useCallback(() => {
    clearCloseTimer()
    closeTimerRef.current = setTimeout(() => setOpen(false), HOVER_CLOSE_DELAY_MS)
  }, [clearCloseTimer])

  useEffect(() => {
    if (!open) return
    const onReposition = () => updatePosition()
    window.addEventListener('scroll', onReposition, true)
    window.addEventListener('resize', onReposition)
    return () => {
      window.removeEventListener('scroll', onReposition, true)
      window.removeEventListener('resize', onReposition)
    }
  }, [open, updatePosition])

  useEffect(() => () => clearCloseTimer(), [clearCloseTimer])

  if (!collapsed) {
    return <>{children}</>
  }

  const flyoutPanel = (
    <div
      style={{ top: coords.top, left: coords.left }}
      className="fixed z-[100] min-w-[200px] rounded bg-[#2f353a] border border-white/10 shadow-xl"
      onMouseEnter={handleOpen}
      onMouseLeave={handleClose}
    >
      <div
        className={cn(
          'px-4 py-2.5 text-sm font-medium border-b border-white/10',
          isGroupActive
            ? 'text-white bg-[#05B0AA]/20 border-l-2 border-primary-400'
            : 'text-gray-200'
        )}
      >
        {label}
      </div>
      {items && items.length > 0 && (
        <div
          className="py-1 overflow-y-auto overscroll-contain flyout-scroll"
          style={{ maxHeight: listMaxHeight }}
        >
          {items.map((child) => (
            <NavLink
              key={child.to}
              to={child.to}
              onClick={() => setOpen(false)}
              className={({ isActive }) => sidebarFlyoutSubItemClassName(isActive)}
            >
              {child.label}
            </NavLink>
          ))}
        </div>
      )}
    </div>
  )

  return (
    <>
      <div
        ref={anchorRef}
        className="relative"
        onMouseEnter={handleOpen}
        onMouseLeave={handleClose}
      >
        {children}
      </div>
      {open && createPortal(flyoutPanel, document.body)}
    </>
  )
}
