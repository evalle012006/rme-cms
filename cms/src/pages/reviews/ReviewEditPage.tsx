import { useCallback, useEffect, useMemo, useState } from "react"
import { useNavigate, useParams, useSearchParams } from "react-router-dom"
import { FileText } from "lucide-react"
import { toast } from "sonner"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { Button } from "@/components/atoms/Button"
import { FormControl } from "@/components/atoms/FormControl"
import { FormField } from "@/components/atoms/FormField"
import { MultiSelect, type MultiSelectOption } from "@/components/atoms/MultiSelect"
import { Card } from "@/components/ui/card"
import {
  reviewsAPI,
  REVIEW_STATUS,
  type BuyerTypeOption,
  type ReviewCategory,
  type ReviewDetail,
} from "@/api/reviews/reviewsAPIHandler"
import { statesAPI, type State } from "@/api/states/statesAPIHandler"
import { ROUTES } from "@/constants/routes"
import { imgUrl } from "@/lib/utils"

const IMAGE_EXTENSIONS = new Set(["jpg", "jpeg", "png", "gif", "webp", "svg"])

function fileExtension(path: string | null | undefined): string {
  if (!path) return ""
  const clean = path.split("?")[0] ?? path
  const parts = clean.split(".")
  return (parts[parts.length - 1] ?? "").toLowerCase()
}

function resolveProofUrl(
  fullUrl: string | null | undefined,
  relativePath: string | null | undefined
): string | null {
  return fullUrl || imgUrl(relativePath)
}

// ── Return path ──────────────────────────────────────────────────────────────
// Honour the `?from=approved|pending` hint when present, otherwise infer from
// the review's own status.
function resolveReturnPath(from: string | null, status?: number) {
  if (from === "approved") return ROUTES.reviews.approved
  if (from === "pending") return ROUTES.reviews.pending
  return status === REVIEW_STATUS.APPROVED
    ? ROUTES.reviews.approved
    : ROUTES.reviews.pending
}

const STATUS_OPTIONS = [
  { value: String(REVIEW_STATUS.PENDING), label: "Pending" },
  { value: String(REVIEW_STATUS.APPROVED), label: "Accept" },
]

const RECOMMENDATION_OPTIONS = [
  { value: "1", label: "Yes" },
  { value: "0", label: "No" },
]

interface ReviewFormState {
  status: string
  title: string
  property_id: string
  recommendation: string
  buyer_type: string
  post_code: string
  state: string
  category_id: string
  comment: string
  year_of_completion: string
  suburb: string
  tag_ids: number[]
}

const EMPTY_FORM: ReviewFormState = {
  status: "",
  title: "",
  property_id: "",
  recommendation: "",
  buyer_type: "",
  post_code: "",
  state: "",
  category_id: "",
  comment: "",
  year_of_completion: "",
  suburb: "",
  tag_ids: [],
}

function toFormState(review: ReviewDetail): ReviewFormState {
  const recommended =
    typeof review.recommendation === "boolean"
      ? review.recommendation
      : review.recommendation === 1

  return {
    status: String(review.status),
    title: review.title ?? "",
    property_id: review.property_id ? String(review.property_id) : "",
    recommendation: recommended ? "1" : "0",
    buyer_type: review.buyer_type ? String(review.buyer_type) : "",
    post_code: review.post_code ?? "",
    state: review.state ? String(review.state) : "",
    category_id: review.category_id ? String(review.category_id) : "",
    comment: review.comment ?? "",
    year_of_completion: review.year_of_completion ?? "",
    suburb: review.suburb ?? "",
    tag_ids: (review.review_tags ?? []).map((t) => t.id),
  }
}

export default function ReviewEditPage() {
  const navigate = useNavigate()
  const { id: idParam } = useParams<{ id: string }>()
  const [searchParams] = useSearchParams()
  const reviewId = Number(idParam)

  const [review, setReview] = useState<ReviewDetail | null>(null)
  const [states, setStates] = useState<State[]>([])
  const [buyerTypes, setBuyerTypes] = useState<BuyerTypeOption[]>([])
  const [categories, setCategories] = useState<ReviewCategory[]>([])
  const [form, setForm] = useState<ReviewFormState>(EMPTY_FORM)
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)

  const returnPath = resolveReturnPath(
    searchParams.get("from"),
    review?.status
  )

  const breadcrumb = useMemo(
    () => [
      { label: "Home", to: ROUTES.root },
      { label: "Review", to: returnPath },
      { label: "Edit" },
    ],
    [returnPath]
  )

  useEffect(() => {
    if (!idParam || Number.isNaN(reviewId)) {
      toast.error("Invalid review id")
      navigate(ROUTES.reviews.approved)
      return
    }

    let mounted = true
    setLoading(true)

    Promise.all([
      reviewsAPI.getById(reviewId, { customErrorMessage: "Review not found" }),
      reviewsAPI.getFormMeta(),
      statesAPI.getAll(),
    ])
      .then(([detail, meta, loadedStates]) => {
        if (!mounted) return
        setReview(detail)
        setBuyerTypes(meta.buyertype?.option_select ?? [])
        setCategories(meta.categories ?? [])
        setStates(loadedStates)
        setForm(toFormState(detail))
      })
      .catch(() => {
        if (mounted) navigate(ROUTES.reviews.approved)
      })
      .finally(() => {
        if (mounted) setLoading(false)
      })

    return () => {
      mounted = false
    }
  }, [idParam, reviewId, navigate])

  const setField = useCallback(
    <K extends keyof ReviewFormState>(key: K, value: ReviewFormState[K]) => {
      setForm((prev) => ({ ...prev, [key]: value }))
    },
    []
  )

  const propertyOptions = useMemo(
    () => review?.property_list ?? [],
    [review]
  )
  const suburbOptions = useMemo(() => review?.suburb_list ?? [], [review])
  const tagOptions = useMemo<MultiSelectOption[]>(
    () => (review?.tags_list ?? []).map((t) => ({ value: t.id, label: t.name })),
    [review]
  )

  const userVerified = !!review?.users?.email_verified_at
  const userName =
    review?.users?.full_name || review?.users?.display_name || ""
  const companyName = review?.property?.company?.company_name ?? ""

  const photoProofUrl = resolveProofUrl(
    review?.full_review_image,
    review?.review_image
  )
  const docProofUrl = resolveProofUrl(
    review?.full_review_docs,
    review?.review_docs
  )
  const docExtension = fileExtension(review?.review_docs ?? docProofUrl)
  const docIsImage = IMAGE_EXTENSIONS.has(docExtension)
  const docIsPdf = docExtension === "pdf"

  const fieldsDisabled = loading || submitting

  const handleCancel = useCallback(() => {
    navigate(returnPath)
  }, [navigate, returnPath])

  const handleEmailUser = useCallback(() => {
    const email = review?.users?.email
    if (!email) {
      toast.error("No email available for this user")
      return
    }
    window.location.href = `mailto:${email}`
  }, [review])

  const handleSubmit = useCallback(
    async (event: React.FormEvent) => {
      event.preventDefault()
      if (!review) return

      setSubmitting(true)
      try {
        await reviewsAPI.update(
          {
            id: review.id,
            title: form.title,
            property_id: Number(form.property_id),
            recommendation: form.recommendation === "1",
            comment: form.comment,
            buyer_type: form.buyer_type,
            status: Number(form.status),
            state: Number(form.state),
            suburb: form.suburb,
            post_code: form.post_code,
            year_of_completion: form.year_of_completion,
            review_tags: form.tag_ids,
          },
          {
            successMessage: "Review updated successfully",
            customErrorMessage: "Failed to update review",
          }
        )
        navigate(returnPath)
      } catch {
        // Error toast handled by apiRequest / global interceptor.
      } finally {
        setSubmitting(false)
      }
    },
    [form, navigate, returnPath, review]
  )

  if (loading) {
    return (
      <div className="space-y-6">
        <Breadcrumb items={breadcrumb} />
        <Card className="p-6">
          <div className="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2">
            {Array.from({ length: 12 }).map((_, i) => (
              <div key={i} className="space-y-2">
                <div className="h-4 w-24 animate-pulse rounded bg-gray-200" />
                <div className="h-10 animate-pulse rounded-lg bg-gray-200" />
              </div>
            ))}
          </div>
        </Card>
      </div>
    )
  }

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />

      <form onSubmit={handleSubmit} className="space-y-6" noValidate>
        <Card className="p-6">
          <div className="mb-6 flex justify-end">
            <Button
              type="button"
              size="sm"
              className="h-auto bg-teal-500 px-4 py-2 text-white hover:bg-teal-600"
              onClick={handleEmailUser}
            >
              Email User
            </Button>
          </div>

          <div className="grid grid-cols-1 gap-x-8 gap-y-5 md:grid-cols-2">
            {/* Left column */}
            <FormControl
              variant="select"
              label="Status"
              disabled={fieldsDisabled}
              value={form.status}
              onChange={(e) => setField("status", e.target.value)}
            >
              {STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </FormControl>

            {/* Right column */}
            <FormControl
              variant="select"
              label="Category"
              disabled={fieldsDisabled}
              value={form.category_id}
              onChange={(e) => setField("category_id", e.target.value)}
            >
              <option value="">Select Category</option>
              {categories.map((c) => (
                <option key={c.id} value={c.id}>
                  {c.name}
                </option>
              ))}
            </FormControl>

            <FormControl
              label="Title"
              disabled={fieldsDisabled}
              value={form.title}
              onChange={(e) => setField("title", e.target.value)}
            />

            <FormControl
              label={`User ${userVerified ? "( Verified )" : "( Not Verified )"}`}
              alwaysReadOnly
              value={userName}
            />

            <FormControl
              variant="select"
              label="Project"
              disabled={fieldsDisabled}
              value={form.property_id}
              onChange={(e) => setField("property_id", e.target.value)}
            >
              <option value="">Select Project</option>
              {propertyOptions.map((p) => (
                <option key={p.id} value={p.id}>
                  {p.project_name}
                </option>
              ))}
            </FormControl>

            <FormControl
              label="Company"
              alwaysReadOnly
              value={companyName}
            />

            <FormControl
              variant="select"
              label="Recommendation"
              disabled={fieldsDisabled}
              value={form.recommendation}
              onChange={(e) => setField("recommendation", e.target.value)}
            >
              {RECOMMENDATION_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </FormControl>

            <FormControl
              variant="textarea"
              label="Comment"
              disabled={fieldsDisabled}
              className="min-h-[44px]"
              value={form.comment}
              onChange={(e) => setField("comment", e.target.value)}
            />

            <FormControl
              variant="select"
              label="Buyer Type"
              disabled={fieldsDisabled}
              value={form.buyer_type}
              onChange={(e) => setField("buyer_type", e.target.value)}
            >
              <option value="">Select Buyer Type</option>
              {buyerTypes.map((b) => (
                <option key={b.id} value={b.id}>
                  {b.key_text}
                </option>
              ))}
            </FormControl>

            <FormControl
              label="Year Of Completion"
              disabled={fieldsDisabled}
              value={form.year_of_completion}
              onChange={(e) => setField("year_of_completion", e.target.value)}
            />

            <FormControl
              label="Postcode"
              disabled={fieldsDisabled}
              value={form.post_code}
              onChange={(e) => setField("post_code", e.target.value)}
            />

            <FormControl
              variant="select"
              label="Suburb"
              disabled={fieldsDisabled}
              value={form.suburb}
              onChange={(e) => setField("suburb", e.target.value)}
            >
              <option value="">Select Suburb</option>
              {suburbOptions.map((s) => (
                <option key={s.text} value={s.text}>
                  {s.text}
                </option>
              ))}
            </FormControl>

            <FormControl
              variant="select"
              label="State"
              disabled={fieldsDisabled}
              value={form.state}
              onChange={(e) => setField("state", e.target.value)}
            >
              <option value="">Select State</option>
              {states.map((s) => (
                <option key={s.id} value={s.id}>
                  {s.state_name}
                </option>
              ))}
            </FormControl>

            <FormField label="Comment Tags">
              <MultiSelect
                options={tagOptions}
                value={form.tag_ids}
                onChange={(ids) => setField("tag_ids", ids)}
                disabled={fieldsDisabled}
                placeholder="Select tags…"
              />
            </FormField>

            <FormField label="Photo proof">
              {photoProofUrl ? (
                <a
                  href={photoProofUrl}
                  target="_blank"
                  rel="noopener noreferrer"
                  className="inline-block"
                >
                  <img
                    src={photoProofUrl}
                    alt="Photo proof"
                    className="max-h-40 max-w-full rounded-card object-contain"
                  />
                </a>
              ) : (
                <p className="text-sm text-gray-500">No photo proof uploaded</p>
              )}
            </FormField>

            <FormField label="Document proof">
              {docProofUrl ? (
                docIsImage ? (
                  <a
                    href={docProofUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-block"
                  >
                    <img
                      src={docProofUrl}
                      alt="Document proof"
                      className="max-h-40 max-w-full rounded-card object-contain"
                    />
                  </a>
                ) : (
                  <a
                    href={docProofUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex items-center gap-2 rounded-card border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 hover:border-gray-300 hover:bg-gray-100"
                  >
                    <FileText size={18} className="shrink-0 text-primary-600" />
                    <span>
                      View {docIsPdf ? "PDF" : "document"} proof
                    </span>
                  </a>
                )
              ) : (
                <p className="text-sm text-gray-500">
                  No document proof uploaded
                </p>
              )}
            </FormField>
          </div>

          <div className="mt-8 flex flex-wrap items-center gap-3">
            <Button
              type="submit"
              size="sm"
              className="h-auto bg-teal-500 px-6 py-2.5 text-white hover:bg-teal-600 disabled:cursor-not-allowed disabled:opacity-60"
              disabled={submitting}
            >
              {submitting ? "Updating…" : "Update"}
            </Button>
            <Button
              type="button"
              color="destructive"
              size="sm"
              className="h-auto px-6 py-2.5"
              onClick={handleCancel}
              disabled={submitting}
            >
              Cancel
            </Button>
          </div>
        </Card>
      </form>
    </div>
  )
}
