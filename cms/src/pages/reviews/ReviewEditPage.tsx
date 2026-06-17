import { useCallback, useEffect, useState } from "react"
import { useNavigate, useParams, useSearchParams } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { Button } from "@/components/atoms/Button"
import { FormControl } from "@/components/atoms/FormControl"
import { Card } from "@/components/ui/card"
import { ConfirmDialog } from "@/components/atoms/ConfirmDialog"
import { ROUTES } from "@/constants/routes"
import {
  reviewsAPI,
  REVIEW_STATUS,
  type ReviewDetail,
  type ReviewTag,
  type BuyerTypeOption,
  type ReviewCategory,
} from "@/api/reviews/reviewsAPIHandler"

const schema = z.object({
  title:              z.string().min(1, "Title is required"),
  comment:            z.string(),
  buyer_type:         z.string().min(1, "Buyer type is required"),
  recommendation:     z.string().min(1, "Recommendation is required"),
  status:             z.string().min(1, "Status is required"),
  state:              z.string().min(1, "State is required"),
  suburb:             z.string().min(1, "Suburb is required"),
  post_code:          z.string().min(1, "Post code is required"),
  year_of_completion: z.string(),
  property_id:        z.string().min(1, "Property is required"),
  category_id:        z.string().min(1, "Category is required"),
})

type FormData = z.infer<typeof schema>

const BREADCRUMB = [
  { label: "Home",    to: ROUTES.root },
  { label: "Reviews", to: ROUTES.reviews.approved },
  { label: "Edit Review" },
]

export default function ReviewEditPage() {
  const { id: idParam }   = useParams<{ id: string }>()
  const [searchParams]    = useSearchParams()
  const navigate          = useNavigate()
  const returnPath        = searchParams.get("from") === "pending"
    ? ROUTES.reviews.pending
    : ROUTES.reviews.approved

  const [review,       setReview]       = useState<ReviewDetail | null>(null)
  const [loading,      setLoading]      = useState(true)
  const [notFound,     setNotFound]     = useState(false)
  const [submitting,   setSubmitting]   = useState(false)
  const [suburbs,      setSuburbs]      = useState<string[]>([])
  const [buyerTypes,   setBuyerTypes]   = useState<BuyerTypeOption[]>([])
  const [categories,   setCategories]   = useState<ReviewCategory[]>([])
  const [states,       setStates]       = useState<{ id: number; state_name: string }[]>([])
  const [allTags,      setAllTags]      = useState<ReviewTag[]>([])
  const [selectedTags, setSelectedTags] = useState<ReviewTag[]>([])
  const [confirmDelete, setConfirmDelete] = useState(false)
  const [confirmStatus, setConfirmStatus] = useState<0 | 1 | null>(null)

  const {
    register,
    handleSubmit,
    reset,
    watch,
    formState: { errors },
  } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: {
      title: "", comment: "", buyer_type: "", recommendation: "",
      status: "", state: "", suburb: "", post_code: "",
      year_of_completion: "", property_id: "", category_id: "",
    },
  })

  const watchedPostCode = watch("post_code")
  const watchedStatus   = Number(watch("status"))

  // Load review + supporting data
  useEffect(() => {
    if (!idParam) { setNotFound(true); setLoading(false); return }
    let mounted = true
    Promise.all([
      reviewsAPI.getById(idParam),
      reviewsAPI.getBuyerTypes(),
      reviewsAPI.getStates(),
    ])
      .then(([rev, buyerData, stateData]) => {
        if (!mounted) return
        setReview(rev)
        setBuyerTypes(buyerData.buyertype.option_select)
        setCategories(buyerData.categories)
        setStates(stateData)
        setAllTags(rev.tags_list ?? [])
        setSelectedTags(rev.reviewTags ?? [])
        setSuburbs((rev.suburb_list ?? []).map(s => s.text))
        reset({
          title:              rev.title              ?? "",
          comment:            rev.comment            ?? "",
          buyer_type:         String(rev.buyer_type  ?? ""),
          recommendation:     rev.recommendation != null ? String(Number(rev.recommendation)) : "",
          status:             String(rev.status),
          state:              rev.state              ?? "",
          suburb:             rev.suburb             ?? "",
          post_code:          rev.post_code          ?? "",
          year_of_completion: rev.year_of_completion != null ? String(rev.year_of_completion) : "",
          property_id:        String(rev.property_id ?? ""),
          category_id:        String(rev.category_id ?? ""),
        })
      })
      .catch(() => { if (mounted) setNotFound(true) })
      .finally(() => { if (mounted) setLoading(false) })
    return () => { mounted = false }
  }, [idParam, reset])

  // Reload suburbs when post code changes
  useEffect(() => {
    if (!watchedPostCode || watchedPostCode.length < 4) return
    reviewsAPI.getSuburbs(watchedPostCode)
      .then(res => setSuburbs(res.map(s => s.text)))
      .catch(() => {})
  }, [watchedPostCode])

  const toggleTag = (tag: ReviewTag) => {
    setSelectedTags(prev =>
      prev.find(t => t.id === tag.id)
        ? prev.filter(t => t.id !== tag.id)
        : [...prev, tag]
    )
  }

  const onSubmit = async (data: FormData) => {
    if (!review) return
    setSubmitting(true)
    try {
      await reviewsAPI.update(
        review.id,
        {
          title:              data.title,
          comment:            data.comment || null,
          buyer_type:         Number(data.buyer_type),
          recommendation:     Number(data.recommendation),
          status:             Number(data.status),
          state:              data.state,
          suburb:             data.suburb,
          post_code:          data.post_code,
          year_of_completion: data.year_of_completion ? Number(data.year_of_completion) : null,
          property_id:        Number(data.property_id),
          category_id:        Number(data.category_id),
          review_tags:        selectedTags,
        },
        { successMessage: "Review updated." }
      )
      navigate(returnPath)
    } catch {
      // Toast handled by interceptor
    } finally {
      setSubmitting(false)
    }
  }

  const handleDelete = async () => {
    if (!review) return
    try {
      await reviewsAPI.delete(review.id, { successMessage: "Review deleted." })
      navigate(returnPath)
    } catch {}
  }

  const handleStatusFlip = async () => {
    if (!review || confirmStatus === null) return
    try {
      await reviewsAPI.changeStatus(review.id, confirmStatus, {
        successMessage: confirmStatus === REVIEW_STATUS.APPROVED ? "Review approved." : "Review set to pending.",
      })
      setReview(prev => prev ? { ...prev, status: confirmStatus } : prev)
      setConfirmStatus(null)
    } catch {}
  }

  const handleCancel = useCallback(() => navigate(returnPath), [navigate, returnPath])

  if (loading) return (
    <div className="space-y-10">
      <Breadcrumb items={BREADCRUMB} />
      <Card className="p-10">
        <div className="space-y-4">
          {Array.from({ length: 6 }).map((_, i) => (
            <div key={i} className="h-10 animate-pulse rounded bg-gray-200" />
          ))}
        </div>
      </Card>
    </div>
  )

  if (notFound || !review) return (
    <div className="space-y-10">
      <Breadcrumb items={BREADCRUMB} />
      <Card className="p-10 text-center text-sm text-gray-500">Review not found.</Card>
    </div>
  )

  const reviewerName = review.users
    ? `${review.users.display_name ?? ""} (${review.users.firstname ?? ""} ${review.users.lastname ?? ""})`.trim()
    : "—"

  return (
    <div className="space-y-10">
      <Breadcrumb items={BREADCRUMB} />

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6" noValidate>
        <Card className="overflow-hidden p-0">
          <div className="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800">Edit Review</h2>
            <div className="flex gap-2">
              {watchedStatus === REVIEW_STATUS.APPROVED ? (
                <Button type="button" color="destructive" size="sm"
                  onClick={() => setConfirmStatus(REVIEW_STATUS.PENDING)}>
                  Set Pending
                </Button>
              ) : (
                <Button type="button" color="primary" size="sm"
                  onClick={() => setConfirmStatus(REVIEW_STATUS.APPROVED)}>
                  Approve
                </Button>
              )}
              <Button type="button" color="destructive" size="sm"
                onClick={() => setConfirmDelete(true)}>
                Delete
              </Button>
            </div>
          </div>

          <div className="grid grid-cols-1 gap-x-6 gap-y-5 p-6 md:grid-cols-2">
            {/* Reviewer — read only */}
            <FormControl label="Reviewer" value={reviewerName} readOnly disabled />

            {/* Property */}
            <FormControl
              variant="select"
              label="Property"
              error={errors.property_id?.message}
              {...register("property_id")}
            >
              <option value="" disabled>Select property</option>
              {(review.property_list ?? []).map(p => (
                <option key={p.id} value={String(p.id)}>{p.project_name}</option>
              ))}
            </FormControl>

            {/* Title */}
            <FormControl
              label="Title"
              placeholder="Review title"
              error={errors.title?.message}
              className="md:col-span-2"
              {...register("title")}
            />

            {/* Comment */}
            <FormControl
              variant="textarea"
              label="Comment"
              placeholder="Review comment"
              error={errors.comment?.message}
              className="md:col-span-2"
              {...register("comment")}
            />

            {/* Category */}
            <FormControl
              variant="select"
              label="Category"
              error={errors.category_id?.message}
              {...register("category_id")}
            >
              <option value="" disabled>Select category</option>
              {categories.map(c => (
                <option key={c.id} value={String(c.id)}>{c.name}</option>
              ))}
            </FormControl>

            {/* Buyer type */}
            <FormControl
              variant="select"
              label="Buyer Type"
              error={errors.buyer_type?.message}
              {...register("buyer_type")}
            >
              <option value="" disabled>Select buyer type</option>
              {buyerTypes.map(b => (
                <option key={b.id} value={String(b.id)}>{b.name}</option>
              ))}
            </FormControl>

            {/* Recommendation */}
            <FormControl
              variant="select"
              label="Recommendation"
              error={errors.recommendation?.message}
              {...register("recommendation")}
            >
              <option value="" disabled>Select</option>
              <option value="1">Recommend</option>
              <option value="0">Don't Recommend</option>
            </FormControl>

            {/* Status */}
            <FormControl
              variant="select"
              label="Status"
              error={errors.status?.message}
              {...register("status")}
            >
              <option value="1">Approved</option>
              <option value="0">Pending</option>
            </FormControl>

            {/* State */}
            <FormControl
              variant="select"
              label="State"
              error={errors.state?.message}
              {...register("state")}
            >
              <option value="" disabled>Select state</option>
              {states.map(s => (
                <option key={s.id} value={s.state_name}>{s.state_name}</option>
              ))}
            </FormControl>

            {/* Post code */}
            <FormControl
              label="Post Code"
              placeholder="Post code"
              error={errors.post_code?.message}
              {...register("post_code")}
            />

            {/* Suburb */}
            <FormControl
              variant="select"
              label="Suburb"
              error={errors.suburb?.message}
              {...register("suburb")}
            >
              <option value="" disabled>Select suburb</option>
              {suburbs.map(s => (
                <option key={s} value={s}>{s}</option>
              ))}
            </FormControl>

            {/* Year of completion */}
            <FormControl
              label="Year of Completion"
              placeholder="e.g. 2023"
              error={errors.year_of_completion?.message}
              {...register("year_of_completion")}
            />

            {/* Tags */}
            <div className="md:col-span-2">
              <p className="mb-2 text-sm font-medium text-gray-700">Tags</p>
              <div className="flex flex-wrap gap-2">
                {allTags.map(tag => {
                  const selected = !!selectedTags.find(t => t.id === tag.id)
                  return (
                    <button
                      key={tag.id}
                      type="button"
                      onClick={() => toggleTag(tag)}
                      className={`rounded-full px-3 py-1 text-xs font-medium transition-colors ${
                        selected
                          ? "bg-primary-600 text-white"
                          : "bg-gray-100 text-gray-600 hover:bg-gray-200"
                      }`}
                    >
                      {tag.name}
                    </button>
                  )
                })}
              </div>
            </div>
          </div>
        </Card>

        <div className="flex flex-wrap items-center gap-3">
          <Button type="submit" color="primary" disabled={submitting}
            className="px-6 py-2 disabled:cursor-not-allowed disabled:opacity-60">
            {submitting ? "Saving…" : "Save"}
          </Button>
          <Button type="button" color="destructive" onClick={handleCancel} className="px-6 py-2">
            Cancel
          </Button>
        </div>
      </form>

      <ConfirmDialog
        open={confirmDelete}
        title="Delete Review"
        description="This review will be permanently deleted. This cannot be undone."
        confirmLabel="Delete"
        onConfirm={handleDelete}
        onOpenChange={(open) => { if (!open) setConfirmDelete(false) }}
      />

      <ConfirmDialog
        open={confirmStatus !== null}
        title={confirmStatus === REVIEW_STATUS.APPROVED ? "Approve Review" : "Set to Pending"}
        description={confirmStatus === REVIEW_STATUS.APPROVED
          ? "This will approve the review and make it visible."
          : "This will move the review back to pending."}
        confirmLabel={confirmStatus === REVIEW_STATUS.APPROVED ? "Approve" : "Set Pending"}
        onConfirm={handleStatusFlip}
        onOpenChange={(open) => { if (!open) setConfirmStatus(null) }}
      />
    </div>
  )
}