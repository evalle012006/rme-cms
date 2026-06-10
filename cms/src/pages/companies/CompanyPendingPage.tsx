import { COMPANY_STATUS } from '@/api/companies/companiesAPIHandler'
import { CompaniesPageContent } from './components/CompaniesPageContent'

export default function CompanyPendingPage() {
  return (
    <CompaniesPageContent
      statusFilter={COMPANY_STATUS.PENDING}
      breadcrumbLabel="Company Pending"
      statusLabel="Pending"
      statusTone="pending"
      flipStatusTo={COMPANY_STATUS.APPROVED}
      flipConfirmMessage="Are you sure you want to add this company to approved ?"
      flipSuccessMessage="Company approved."
    />
  )
}
