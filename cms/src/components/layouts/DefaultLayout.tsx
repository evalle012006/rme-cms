import { useState } from 'react'
import { Outlet } from 'react-router-dom'
import { cn } from '@/lib/utils'
import { sidebarMarginClass } from '@/components/layouts/sidebar/sidebarLayout'
import Sidebar from './sidebar/Sidebar'
import TopBar from './TopBar'

export default function DefaultLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(true)
  const [sidebarCollapsed, setSidebarCollapsed] = useState(false)

  return (
    <div className="min-h-screen bg-surface">
      <TopBar
        sidebarOpen={sidebarOpen}
        sidebarCollapsed={sidebarCollapsed}
        onToggleSidebar={() => setSidebarOpen((open) => !open)}
      />
      <Sidebar
        open={sidebarOpen}
        collapsed={sidebarCollapsed}
        onCollapsedChange={setSidebarCollapsed}
      />

      <div
        className={cn(
          'pt-14 min-h-screen flex flex-col transition-[margin] duration-300 ease-in-out',
          sidebarMarginClass(sidebarOpen, sidebarCollapsed)
        )}
      >
        <main className="flex-1 px-8 pb-8">
          <Outlet />
        </main>

        <footer className="app-border-t bg-gray-100 px-8 py-4 text-right text-sm text-primary-500">
          Rate My Estate &copy; 2019.
        </footer>
      </div>
    </div>
  )
}
