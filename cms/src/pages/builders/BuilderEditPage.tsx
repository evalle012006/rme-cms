import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { useNavigate, useParams, useSearchParams } from "react-router-dom"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { toast } from "sonner"
import { ImagePlus, X } from "lucide-react"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { Button } from "@/components/atoms/Button"
import { FormControl } from "@/components/atoms/FormControl"
import { Card } from "@/components/ui/card"
import { cn } from "@/lib/utils"

import {
  buildersAPI,
  BUILDER_STATUS,
  BUILDER_STATUS_OPTIONS,
  type Builder,
  type BuilderStatusFilter,
} from "@/api/builders/buildersAPIHandler"
import { ROUTES } from "@/constants/routes"

// ── Form schema ──────────────────────────────────────────────────────────────
// Email is shown as read-only on the page (matching the legacy UI) but is
// still included in the submit payload, so it lives in the form state.

const editBuilderSchema = z.object({
  firstname: z.string().min(1, "First name is required"),
  lastname: z.string().min(1, "Last name is required"),
  status: z.string().min(1, "Status is required"),
  email: z.string().min(1, "Email is required").email("Enter a valid email"),
  company_name: z.string().min(1, "Company name is required"),
  website: z.string().min(1, "Website is required"),
  company_bio: z.string().min(1, "Company bio is required"),
})

type EditBuilderFormData = z.infer<typeof editBuilderSchema>

function resolveReturnPath(from: string | null) {
  return from === "pending" ? ROUTES.builder.pending : ROUTES.builder.approved
}

// ── Page ─────────────────────────────────────────────────────────────────────
export default function BuilderEditPage() {
  const { id: idParam } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const id = Number(idParam)
  const returnPath = resolveReturnPath(searchParams.get("from"))
  const isFromPending = searchParams.get("from") === "pending"

  const breadcrumb = useMemo(
    () => [
      { label: "Home", to: ROUTES.root },
      {
        label: isFromPending
          ? "Pending Company Users"
          : "Approved Company Users",
        to: returnPath,
      },
      { label: "Edit Company User" },
    ],
    [isFromPending, returnPath]
  )

  const [builder, setBuilder] = useState<Builder | null>(null)
  const [loading, setLoading] = useState(true)
  const [notFound, setNotFound] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  // Company logo state — kept outside the form because it's a File and the
  // existing path is preserved across re-renders.
  // - `logoFile`  → newly picked File (overrides path on submit)
  // - `logoPath`  → existing path returned by the backend (sent unchanged
  //                  when the user doesn't replace the image)
  const [logoFile, setLogoFile] = useState<File | null>(null)
  const [logoPath, setLogoPath] = useState<string | null>(null)

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<EditBuilderFormData>({
    resolver: zodResolver(editBuilderSchema),
    defaultValues: {
      firstname: "",
      lastname: "",
      status: "",
      email: "",
      company_name: "",
      website: "",
      company_bio: "",
    },
  })

  // ── Load builder ──────────────────────────────────────────────────────────
  useEffect(() => {
    if (!idParam || Number.isNaN(id)) {
      setNotFound(true)
      setLoading(false)
      return
    }
    let mounted = true
    setLoading(true)
    buildersAPI
      .getById(id)
      .then((data) => {
        if (!mounted) return
        setBuilder(data)
        setLogoPath(data.company?.logo ?? null)
        reset({
          firstname: data.firstname ?? "",
          lastname: data.lastname ?? "",
          // getById omits status — infer it from which list page opened this form.
          status: String(
            isFromPending ? BUILDER_STATUS.PENDING : BUILDER_STATUS.APPROVED
          ),
          email: data.email ?? "",
          company_name: data.company?.company_name ?? "",
          website: data.company?.website ?? "",
          company_bio: data.company?.bio ?? "",
        })
      })
      .catch(() => {
        if (mounted) setNotFound(true)
      })
      .finally(() => {
        if (mounted) setLoading(false)
      })
    return () => {
      mounted = false
    }
  }, [id, idParam, isFromPending, reset])

  // ── Cancel ───────────────────────────────────────────────────────────────
  const handleCancel = useCallback(() => {
    navigate(returnPath)
  }, [navigate, returnPath])

  // ── Submit ───────────────────────────────────────────────────────────────
  const onSubmit = async (data: EditBuilderFormData) => {
    if (!builder) return
    setSubmitting(true)
    try {
      await buildersAPI.update(
        {
          id: builder.id,
          company_id: builder.company_id ?? builder.company?.id ?? 0,
          firstname: data.firstname,
          lastname: data.lastname,
          email: data.email,
          status: Number(data.status) as BuilderStatusFilter,
          company_name: data.company_name,
          company_bio: data.company_bio,
          website: data.website,
          company_logo: logoFile ?? logoPath,
        },
        {
          successMessage: "Company user updated.",
          customErrorMessage: "Failed to update company user",
        }
      )
      navigate(returnPath)
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) {
    return (
      <div className="space-y-10">
        <Breadcrumb items={breadcrumb} />
        <Card className="p-10">
          <div className="h-6 w-48 animate-pulse rounded bg-gray-200" />
          <div className="mt-6 grid grid-cols-1 gap-4 md:grid-cols-3">
            {Array.from({ length: 6 }).map((_, i) => (
              <div key={i} className="h-12 animate-pulse rounded bg-gray-200" />
            ))}
          </div>
        </Card>
      </div>
    )
  }

  if (notFound || !builder) {
    return (
      <div className="space-y-10">
        <Breadcrumb items={breadcrumb} />
        <Card className="p-10 text-center text-sm text-gray-500">
          Company user not found.
        </Card>
      </div>
    )
  }

  return (
    <div className="space-y-10">
      <Breadcrumb items={breadcrumb} />

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6" noValidate>
        <Card className="overflow-hidden p-0">
          {/* Header bar with "Email User" action on the right */}
          <div className="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800">
              Edit Company User
            </h2>
            <a href={`mailto:${builder.email}`}>
              <Button color="primary" size="sm" className="h-auto px-4 py-1.5">
                Email User
              </Button>
            </a>
          </div>

          {/* Top row: logo + name fields + status/email */}
          <div className="grid grid-cols-1 gap-x-6 gap-y-5 p-6 md:grid-cols-[200px_1fr_1fr]">
            <div className="md:row-span-2">
              <CompanyLogoPicker
                file={logoFile}
                existingUrl={builder.company?.full_company_logo ?? null}
                onChange={(file) => {
                  setLogoFile(file)
                  if (file === null) {
                    // Mark the existing logo as removed so the update call
                    // doesn't accidentally preserve it.
                    setLogoPath(null)
                  }
                }}
              />
            </div>

            <FormControl
              label="First Name"
              placeholder="First name"
              error={errors.firstname?.message}
              {...register("firstname")}
            />

            <FormControl
              label="Last Name"
              placeholder="Last name"
              error={errors.lastname?.message}
              {...register("lastname")}
            />

            <FormControl
              variant="select"
              label="Status"
              error={errors.status?.message}
              {...register("status")}
            >
              {BUILDER_STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </FormControl>

            <FormControl
              label="Email"
              type="email"
              placeholder="name@example.com"
              readOnly
              disabled
              error={errors.email?.message}
              {...register("email")}
            />
          </div>

          {/* Company name + website */}
          <div className="grid grid-cols-1 gap-x-6 gap-y-5 px-6 pb-6 md:grid-cols-2">
            <FormControl
              label="Company Name"
              placeholder="Company name"
              error={errors.company_name?.message}
              {...register("company_name")}
            />

            <FormControl
              label="Website URL"
              placeholder="https://example.com"
              error={errors.website?.message}
              {...register("website")}
            />
          </div>

          {/* Company bio (full-width textarea) */}
          <div className="px-6 pb-6">
            <FormControl
              variant="textarea"
              label="Company Bio"
              rows={3}
              placeholder="Company bio"
              error={errors.company_bio?.message}
              {...register("company_bio")}
            />
          </div>
        </Card>

        <div className="flex flex-wrap items-center gap-3">
          <Button
            type="submit"
            color="primary"
            disabled={submitting}
            className="px-6 py-2 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {submitting ? "Updating…" : "Update"}
          </Button>
          <Button
            type="button"
            color="destructive"
            onClick={handleCancel}
            className="px-6 py-2"
          >
            Cancel
          </Button>
        </div>
      </form>
    </div>
  )
}

// ── Company logo picker ──────────────────────────────────────────────────────
// Shows the existing logo (from the backend URL) until the user picks a new
// file. Clicking the X clears both — the parent decides what to send.

function CompanyLogoPicker({
  file,
  existingUrl,
  onChange,
}: {
  file: File | null
  existingUrl: string | null
  onChange: (file: File | null) => void
}) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [filePreview, setFilePreview] = useState<string | null>(null)
  const [dragOver, setDragOver] = useState(false)
  const [removed, setRemoved] = useState(false)

  useEffect(() => {
    if (!file) {
      setFilePreview(null)
      return
    }
    const url = URL.createObjectURL(file)
    setFilePreview(url)
    return () => URL.revokeObjectURL(url)
  }, [file])

  const preview = filePreview ?? (removed ? null : existingUrl)

  const handleFile = (next: File | null) => {
    if (next && !next.type.startsWith("image/")) {
      toast.error("Only image files are allowed")
      return
    }
    if (next) setRemoved(false)
    onChange(next)
  }

  const handleRemove = () => {
    setRemoved(true)
    onChange(null)
  }

  return (
    <div>
      <label className="mb-1.5 block text-sm font-medium text-gray-700">
        Company Logo
      </label>

      <div
        role="button"
        tabIndex={0}
        onClick={() => inputRef.current?.click()}
        onKeyDown={(e) => {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault()
            inputRef.current?.click()
          }
        }}
        onDragOver={(e) => {
          e.preventDefault()
          setDragOver(true)
        }}
        onDragLeave={() => setDragOver(false)}
        onDrop={(e) => {
          e.preventDefault()
          setDragOver(false)
          handleFile(e.dataTransfer.files?.[0] ?? null)
        }}
        className={cn(
          "relative flex aspect-square w-full cursor-pointer items-center justify-center rounded-card border-2 border-dashed transition-colors",
          dragOver
            ? "border-primary-500 bg-primary-100"
            : "border-primary-300 bg-primary-100/60 hover:border-primary-400"
        )}
      >
        {preview ? (
          <>
            <img
              src={preview}
              alt="Company logo preview"
              className="h-full w-full rounded-card object-contain p-2"
            />
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation()
                handleRemove()
              }}
              className="absolute top-1.5 right-1.5 rounded-full bg-gray-900/80 p-1 text-white shadow hover:bg-gray-900"
              aria-label="Remove logo"
            >
              <X size={14} />
            </button>
          </>
        ) : (
          <div className="flex flex-col items-center gap-2 px-3 text-center">
            <ImagePlus size={22} className="text-primary-600" />
            <p className="text-xs leading-snug text-gray-700">
              Click Or Drag Image to upload!
            </p>
          </div>
        )}

        <input
          ref={inputRef}
          type="file"
          accept="image/*"
          className="hidden"
          onChange={(e) => handleFile(e.target.files?.[0] ?? null)}
        />
      </div>
    </div>
  )
}

