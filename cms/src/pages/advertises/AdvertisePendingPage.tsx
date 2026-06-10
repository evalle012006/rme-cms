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
    />
  )
}
