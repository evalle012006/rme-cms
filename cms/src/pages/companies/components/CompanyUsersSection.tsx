import { Switch } from '@/components/ui/switch'
import type { CompanyUser } from '@/api/companies/companiesAPIHandler'

export type CompanyUserStatus = {
  id: number
  status: boolean
}

export type CompanyUsersSectionProps = {
  users: CompanyUser[]
  userStatuses: CompanyUserStatus[]
  disabled?: boolean
  onStatusChange: (userId: number, status: boolean) => void
}

function formatUserName(user: CompanyUser): string {
  const trimmed = user.full_name?.trim()
  if (trimmed) return trimmed
  return [user.firstname, user.lastname].filter(Boolean).join(' ') || user.email
}

export function CompanyUsersSection({
  users,
  userStatuses,
  disabled = false,
  onStatusChange,
}: CompanyUsersSectionProps) {
  if (users.length === 0) {
    return (
      <div className="border-t border-gray-200 px-6 py-5">
        <h3 className="mb-3 text-sm font-medium text-gray-700">Company Users</h3>
        <p className="text-sm text-gray-500">No company users found.</p>
      </div>
    )
  }

  return (
    <div className="border-t border-gray-200 px-6 py-5">
      <h3 className="mb-4 text-sm font-medium text-gray-700">Company Users</h3>
      <div className="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
        {users.map((user) => {
          const active =
            userStatuses.find((entry) => entry.id === user.id)?.status ?? false

          return (
            <div
              key={user.id}
              className="flex items-center justify-between gap-4 rounded-lg border border-gray-200 bg-white px-4 py-4"
            >
              <div className="min-w-0">
                <p className="truncate text-sm font-semibold text-gray-900">
                  {formatUserName(user)}
                </p>
                <p className="truncate text-sm text-gray-500">{user.email}</p>
              </div>
              <Switch
                checked={active}
                disabled={disabled}
                onCheckedChange={(checked) => onStatusChange(user.id, checked)}
                aria-label={`Toggle status for ${formatUserName(user)}`}
              />
            </div>
          )
        })}
      </div>
    </div>
  )
}
