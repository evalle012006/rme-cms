import { BuildersListPage } from "./BuildersListPage"
import { BUILDER_STATUS } from "@/api/builders/buildersAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Approved Company Users" },
]

export default function BuilderApprovedPage() {
  return (
    <BuildersListPage
      breadcrumb={BREADCRUMB}
      statusFilter={BUILDER_STATUS.APPROVED}
      statusLabel="Approved"
      statusTone="approved"
      flipStatusTo={BUILDER_STATUS.PENDING}
      flipConfirmMessage="Are you sure you want to move this company user to pendings ?"
      flipSuccessMessage="Builder moved to pending."
    />
  )
}
