import { PropertiesListPage } from './PropertiesListPage'
import { PROPERTY_STATUS } from '@/api/properties/propertiesAPIHandler'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Property' },
]

export default function PropertyApprovedPage() {
  return (
    <PropertiesListPage
      breadcrumb={BREADCRUMB}
      statusFilter={PROPERTY_STATUS.APPROVED}
      statusLabel="Approved"
      statusTone="approved"
    />
  )
}
