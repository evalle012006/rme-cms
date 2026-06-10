import { AdvertisesListPage } from "./AdvertisesListPage"
import { ADVERTISE_STATUS } from "@/api/advertises/advertisesAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Approved Advertise" },
]

export default function AdvertiseApprovedPage() {
  return (
    <AdvertisesListPage
      breadcrumb={BREADCRUMB}
      statusFilter={ADVERTISE_STATUS.APPROVED}
      statusLabel="Approved"
      statusTone="approved"
    />
  )
}
