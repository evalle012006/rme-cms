import { COMPANY_STATUS } from '@/api/companies/companiesAPIHandler'
import { CompaniesPageContent } from './components/CompaniesPageContent'

export default function CompanyApprovedPage() {
  return (
    <CompaniesPageContent
      statusFilter={COMPANY_STATUS.APPROVED}
      breadcrumbLabel="Company Approved"
      statusLabel="Approved"
      statusTone="approved"
      flipStatusTo={COMPANY_STATUS.PENDING}
      flipConfirmMessage="Are you sure you want to add this company to pendings ?"
      flipSuccessMessage="Company moved to pending."
    />
  )
}
