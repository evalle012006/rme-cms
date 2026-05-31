export default function DashboardPage() {
  return (
    <div>
      <h2 className="font-display text-2xl font-bold text-gray-900 mb-1">Dashboard</h2>
      <p className="text-gray-500 text-sm mb-8">Welcome back. Here's what's happening.</p>
      <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
        {['Companies', 'Properties', 'Reviews'].map((label) => (
          <div key={label} className="bg-white rounded-xl border border-gray-200 p-6">
            <p className="text-sm text-gray-500">{label}</p>
            <p className="text-3xl font-display font-bold text-gray-900 mt-1">—</p>
          </div>
        ))}
      </div>
    </div>
  )
}
