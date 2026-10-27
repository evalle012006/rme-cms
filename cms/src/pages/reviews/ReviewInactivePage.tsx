import { ReviewsListPage } from "./ReviewsListPage"
import { REVIEW_STATUS } from "@/api/reviews/reviewsAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Review" },
  { label: "Inactive" },
]

// Reviews an admin has hidden from the site. They are not shown publicly and
// do not count towards ratings, but remain here so they can be reactivated
// (badge click -> Approved) or deleted with a reason.
export default function ReviewInactivePage() {
  return (
    <ReviewsListPage
      breadcrumb={BREADCRUMB}
      statusFilter={REVIEW_STATUS.INACTIVE}
      statusLabel="Inactive"
      statusTone="inactive"
      flipStatusTo={REVIEW_STATUS.APPROVED}
      flipConfirmMessage="Reactivate this review? It will be visible on the site again and count towards ratings."
      flipSuccessMessage="Review reactivated"
    />
  )
}
