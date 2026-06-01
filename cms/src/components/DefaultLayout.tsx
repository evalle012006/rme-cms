import { Outlet } from 'react-router-dom'
import Sidebar from './Sidebar'

export default function DefaultLayout() {
  return (
    <div className="flex min-h-screen bg-gray-50">
      <Sidebar />
      <main className="flex-1 ml-60 p-8 min-h-screen">
        <Outlet />
      </main>
    </div>
  )
}