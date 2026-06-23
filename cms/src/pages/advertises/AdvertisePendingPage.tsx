import { AdvertisesListPage } from "./AdvertisesListPage"
import { ADVERTISE_STATUS } from "@/api/advertises/advertisesAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Pending Advertise" },
]

export default function AdvertisePendingPage() {
  return (
    <AdvertisesListPage
      breadcrumb={BREADCRUMB}
      statusFilter={ADVERTISE_STATUS.PENDING}
      statusLabel="Pending"
      statusTone="pending"
      flipStatusTo={ADVERTISE_STATUS.APPROVED}
      flipConfirmMessage="Are you sure you want to approve this advertise ?"
      flipSuccessMessage="Advertise approved successfully"
    />
  )
}
