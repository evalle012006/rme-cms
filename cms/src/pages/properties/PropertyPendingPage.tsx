import { PropertiesListPage } from './PropertiesListPage'
import { PROPERTY_STATUS } from '@/api/properties/propertiesAPIHandler'
import { ROUTES } from '@/constants/routes'

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.root },
  { label: 'Project' },
]

export default function PropertyPendingPage() {
  return (
    <PropertiesListPage
      breadcrumb={BREADCRUMB}
      statusFilter={PROPERTY_STATUS.PENDING}
      statusLabel="Pending"
      statusTone="pending"
      flipStatusTo={PROPERTY_STATUS.APPROVED}
      flipConfirmMessage="Are you sure you want to change the status?"
      flipSuccessMessage="Project approved."
    />
  )
}
