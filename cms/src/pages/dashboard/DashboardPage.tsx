import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Building2, TrendingUp, Star, Users } from 'lucide-react'
import { toast } from 'sonner'
import { Button } from '@/components/atoms/Button'
import { StatCard } from '@/components/atoms/StatCard'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { PropertyReviewsSection } from '@/pages/dashboard/components/PropertyReviewsSection'
import { ReviewCountSection } from '@/pages/dashboard/components/ReviewCountSection'
import { RatingAttributesSection } from '@/pages/dashboard/components/RatingAttributesSection'
import {
  dashboardAPI,
  normalizeDashboardCounters,
  type DashboardCounters,
  type DashboardData,
} from '@/api/dashboard/dashboardAPIHandler'
import { ROUTES } from '@/constants/routes'
// import {
//   captureDashboardSnapshot,
//   exportDashboardPdf,
// } from '@/pages/dashboard/shared/dashboardExport'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Dashboard' },
]

const defaultCounters: DashboardCounters = {
  company: 0,
  property: 0,
  review: 0,
  reviewer: 0,
}

export default function DashboardPage() {
  const [counters, setCounters] = useState<DashboardCounters>(defaultCounters)
  const [dashboardData, setDashboardData] = useState<DashboardData | null>(null)
  const [loading, setLoading] = useState(true)
  const [exporting, setExporting] = useState(false)

  useEffect(() => {
    dashboardAPI
      .get({ showErrorToast: false })
      .then((data) => {
        setDashboardData(data)
        setCounters(normalizeDashboardCounters(data))
      })
      .catch(() => {})
      .finally(() => setLoading(false))
  }, [])

  const stats = [
    {
      icon: Building2,
      count: counters.company,
      label: 'Companies',
      iconClassName: 'bg-teal-500',
      countClassName: 'text-teal-500',
      accentColor: '#14b8a6',
    },
    {
      icon: TrendingUp,
      count: counters.property,
      label: 'Projects',
      iconClassName: 'bg-teal-600',
      countClassName: 'text-teal-600',
      accentColor: '#0d9488',
    },
    {
      icon: Star,
      count: counters.review,
      label: 'Reviews',
      iconClassName: 'bg-amber-400',
      countClassName: 'text-amber-500',
      accentColor: '#f59e0b',
    },
    {
      icon: Users,
      count: counters.reviewer,
      label: 'Reviewers',
      iconClassName: 'bg-rose-400',
      countClassName: 'text-rose-500',
      accentColor: '#f43f5e',
    },
  ]

  const handleExport = async () => {
    setExporting(true)
    try {
      // const snapshot = await captureDashboardSnapshot(
      //   stats.map((stat) => ({
      //     label: stat.label,
      //     count: loading ? '—' : stat.count,
      //     accentColor: stat.accentColor,
      //   }))
      // )
      // await exportDashboardPdf(snapshot)
      // toast.success('Dashboard exported as PDF')
      const { url: csvUrl } = await dashboardAPI.exportCsv({
        showErrorToast: false,
      })

      const fullUrl = csvUrl.startsWith('http')
        ? csvUrl
        : `${import.meta.env.VITE_API_BASE_URL}/storage/${csvUrl}`

      const link = document.createElement('a')
      link.href = fullUrl
      link.download = 'admin-export.csv'
      document.body.appendChild(link)
      link.click()
      link.remove()

      toast.success('Dashboard exported as CSV')
    } catch {
      // toast.error('Could not export dashboard PDF')
      toast.error('Could not export dashboard data')
    } finally {
      setExporting(false)
    }

    // PDF export (disabled for now)
    // const snapshot = await captureDashboardSnapshot(
    //   stats.map((stat) => ({
    //     label: stat.label,
    //     count: loading ? '—' : stat.count,
    //     accentColor: stat.accentColor,
    //   }))
    // )
    // await exportDashboardPdf(snapshot)
    // toast.success('Dashboard exported as PDF')
  }

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <div className="flex flex-wrap items-end gap-4">
        <Link to={ROUTES.users.list}>
          <Button className="app-border rounded-card w-90 cursor-pointer justify-start bg-white px-5 py-7 text-gray-700 hover:bg-gray-100">
            Users
          </Button>
        </Link>

        <Link to={ROUTES.propertySettings.categories.list}>
          <Button className="app-border rounded-card w-90 cursor-pointer justify-start bg-white px-5 py-7 text-gray-700 hover:bg-gray-100">
            Project Categories
          </Button>
        </Link>

        <div className="ml-auto">
          <Button
            color="primary"
            className="h-auto rounded-card px-6 py-3.5"
            onClick={() => void handleExport()}
            disabled={exporting}
          >
            {exporting ? 'Exporting...' : 'Export All Data'}
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        {stats.map((stat) => (
          <StatCard
            key={stat.label}
            icon={stat.icon}
            count={loading ? '—' : stat.count}
            label={stat.label}
            iconClassName={stat.iconClassName}
            countClassName={stat.countClassName}
          />
        ))}
      </div>

      <PropertyReviewsSection 
       initialData={dashboardData?.properties_bar_chart?.data}
       initialCompanies={dashboardData?.companies}
       initialStates={dashboardData?.options?.state}
      />

      <ReviewCountSection />

      <RatingAttributesSection
        initialData={dashboardData?.chart2?.data}
        initialCompanies={dashboardData?.companies}
        initialStates={dashboardData?.options?.state}
        initialProjects={dashboardData?.properties}
      />
    </div>
  )
}
