import { Outlet } from 'react-router-dom'
import Sidebar from './Sidebar'
import TopBar from './TopBar'

export default function DefaultLayout() {
  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar />

      {/* Main column — offset by sidebar width */}
      <div className="flex-1 ml-60 flex flex-col min-h-screen">
        <TopBar />

        {/* Content — offset by top bar height */}
        <main className="flex-1 p-8 mt-14">
          <Outlet />
        </main>
      </div>
    </div>
  )
}