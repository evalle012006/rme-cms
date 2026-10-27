import { ReviewsListPage } from "./ReviewsListPage"
import { REVIEW_STATUS } from "@/api/reviews/reviewsAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Review" },
]

export default function ReviewApprovedPage() {
  return (
    <ReviewsListPage
      breadcrumb={BREADCRUMB}
      statusFilter={REVIEW_STATUS.APPROVED}
      statusLabel="Approved"
      statusTone="approved"
      flipStatusTo={REVIEW_STATUS.PENDING}
      flipConfirmMessage="Are you sure you want to set this review to pending ?"
      flipSuccessMessage="Review status updated successfully"
      allowDeactivate
    />
  )
}
