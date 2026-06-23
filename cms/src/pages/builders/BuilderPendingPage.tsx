import { BuildersListPage } from "./BuildersListPage"
import { BUILDER_STATUS } from "@/api/builders/buildersAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Pending Company Users" },
]

export default function BuilderPendingPage() {
  return (
    <BuildersListPage
      breadcrumb={BREADCRUMB}
      statusFilter={BUILDER_STATUS.PENDING}
      statusLabel="Pending"
      statusTone="pending"
      flipStatusTo={BUILDER_STATUS.APPROVED}
      flipConfirmMessage="Are you sure you want to move this company user to approved ?"
      flipSuccessMessage="Builder approved."
    />
  )
}
