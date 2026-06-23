import { ReviewsListPage } from "./ReviewsListPage"
import { REVIEW_STATUS } from "@/api/reviews/reviewsAPIHandler"
import { ROUTES } from "@/constants/routes"

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Review" },
]

export default function ReviewPendingPage() {
  return (
    <ReviewsListPage
      breadcrumb={BREADCRUMB}
      statusFilter={REVIEW_STATUS.PENDING}
      statusLabel="Pending"
      statusTone="pending"
      flipStatusTo={REVIEW_STATUS.APPROVED}
      flipConfirmMessage="Are you sure you want to approve this review ?"
      flipSuccessMessage="Review approved successfully"
    />
  )
}
