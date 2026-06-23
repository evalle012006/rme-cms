import { useCallback, useEffect, useMemo, useRef, useState } from "react"
import { useNavigate } from "react-router-dom"
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
  BRAND_TYPE_OPTIONS,
  USER_STATUS_OPTIONS,
  USER_TAB,
  type UserTab,
} from "@/api/users/usersConstants"
import { usersAPI } from "@/api/users/usersAPIHandler"
import { isValidationError } from "@/api/apiService"
import { companiesAPI, type Company } from "@/api/companies/companiesAPIHandler"
import { ROUTES } from "@/constants/routes"

// ── Breadcrumb ───────────────────────────────────────────────────────────────
const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "Users", to: ROUTES.users.list },
  { label: "Add" },
]

// ── Page ─────────────────────────────────────────────────────────────────────
export default function UserAddPage() {
  const navigate = useNavigate()
  const [activeTab, setActiveTab] = useState<UserTab | null>(null)

  const handleCancel = useCallback(() => navigate(ROUTES.users.list), [navigate])

  return (
    <div className="space-y-6">
      <Breadcrumb items={BREADCRUMB} />

      <UserTabs active={activeTab} onChange={setActiveTab} />

      {activeTab === USER_TAB.REVIEWER && (
        <ReviewerForm onCancel={handleCancel} />
      )}

      {activeTab === USER_TAB.BUILDER_DEVELOPER && (
        <BuilderDeveloperForm onCancel={handleCancel} />
      )}
    </div>
  )
}

// ── Tab switcher ─────────────────────────────────────────────────────────────
function UserTabs({
  active,
  onChange,
}: {
  active: UserTab | null
  onChange: (tab: UserTab) => void
}) {
  const tabs: { value: UserTab; label: string }[] = [
    { value: USER_TAB.REVIEWER, label: "Reviewer" },
    { value: USER_TAB.BUILDER_DEVELOPER, label: "Builder/Developer" },
  ]

  return (
    <div className="flex flex-wrap gap-4">
      {tabs.map((tab) => {
        const isActive = active === tab.value
        return (
          <button
            key={tab.value}
            type="button"
            onClick={() => onChange(tab.value)}
            className={cn(
              "min-w-48 rounded-card border px-5 py-3.5 text-left text-sm font-medium transition-colors",
              isActive
                ? "border-primary-500 bg-primary-500 text-white shadow-sm"
                : "border-gray-200 bg-white text-gray-800 hover:border-primary-300"
            )}
          >
            {tab.label}
          </button>
        )
      })}
    </div>
  )
}

// ── Form section header ──────────────────────────────────────────────────────
function FormSectionHeader({ title }: { title: string }) {
  return (
    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
      <h2 className="text-base font-semibold text-gray-800">{title}</h2>
    </div>
  )
}

// ─────────────────────────────────────────────────────────────────────────────
// Reviewer form
// ─────────────────────────────────────────────────────────────────────────────

const reviewerSchema = z.object({
  firstname: z.string().min(1, "First name is required"),
  lastname: z.string().min(1, "Last name is required"),
  display_name: z.string().min(1, "Display name is required"),
  status: z.string().min(1, "Status is required"),
  email: z.string().min(1, "Email is required").email("Enter a valid email"),
  mobile: z
    .string()
    .min(1, "Mobile is required")
    .regex(/^[0-9+\-\s()]+$/, "Enter a valid mobile number"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  location: z.string().min(1, "Location is required"),
})

type ReviewerFormData = z.infer<typeof reviewerSchema>

function ReviewerForm({ onCancel }: { onCancel: () => void }) {
  const [profileImage, setProfileImage] = useState<File | null>(null)
  const [profileImageError, setProfileImageError] = useState<string | null>(null)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors, isSubmitted },
  } = useForm<ReviewerFormData>({
    resolver: zodResolver(reviewerSchema),
    defaultValues: {
      firstname: "",
      lastname: "",
      display_name: "",
      status: "",
      email: "",
      mobile: "",
      password: "",
      location: "",
    },
  })

  // Show the image error only after first submit attempt — matches the rest
  // of the form fields (errors appear on Create, not on focus).
  const visibleImageError = isSubmitted ? profileImageError : null

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof ReviewerFormData)[] = [
      "firstname",
      "lastname",
      "display_name",
      "status",
      "email",
      "mobile",
      "password",
      "location",
    ]
    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: "server", message })
      }
    })

    const imageMessage =
      apiErrors.profile_image?.[0] ?? apiErrors.profileImage?.[0]
    if (imageMessage) {
      setProfileImageError(imageMessage)
    }
  }

  const validateImage = useCallback(() => {
    if (!profileImage) {
      setProfileImageError("Profile image is required")
      return false
    }
    setProfileImageError(null)
    return true
  }, [profileImage])

  const onSubmit = async (data: ReviewerFormData) => {
    clearErrors()
    if (!validateImage() || !profileImage) return
    setSubmitting(true)
    try {
      await usersAPI.createReviewer(
        {
          firstname: data.firstname,
          lastname: data.lastname,
          display_name: data.display_name,
          email: data.email,
          password: data.password,
          mobile: data.mobile,
          location: data.location,
          status: data.status as Parameters<
            typeof usersAPI.createReviewer
          >[0]["status"],
          profile_image: profileImage,
        },
        {
          successMessage: "User created successfully",
          customErrorMessage: "Failed to create user",
        }
      )
      onCancel()
    } catch (err) {
      if (isValidationError(err)) {
        applyServerErrors(err.response.data.errors)
      }
    } finally {
      setSubmitting(false)
    }
  }

  const onInvalid = () => {
    // Triggers image validation alongside RHF field validation on submit.
    validateImage()
  }

  return (
    <form
      onSubmit={handleSubmit(onSubmit, onInvalid)}
      className="space-y-6"
      noValidate
    >
      <Card className="overflow-hidden p-0">
        <FormSectionHeader title="Add User Detail" />

        <div className="grid grid-cols-1 gap-x-6 gap-y-5 p-6 md:grid-cols-[200px_1fr_1fr]">
          <div className="md:row-span-2">
            <ProfileImagePicker
              value={profileImage}
              onChange={(file) => {
                setProfileImage(file)
                if (file) setProfileImageError(null)
              }}
              error={visibleImageError ?? undefined}
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
            label="Display Name"
            placeholder="Display name"
            error={errors.display_name?.message}
            {...register("display_name")}
          />

          <FormControl
            variant="select"
            label="Status"
            error={errors.status?.message}
            defaultValue=""
            {...register("status")}
          >
            <option value="" disabled>
              Select status
            </option>
            {USER_STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </FormControl>

          <div className="md:col-span-3">
            <div className="grid grid-cols-1 gap-x-6 gap-y-5 md:grid-cols-2">
              <FormControl
                label="Email"
                type="email"
                placeholder="name@example.com"
                error={errors.email?.message}
                {...register("email")}
              />

              <FormControl
                label="Mobile"
                inputMode="tel"
                placeholder="Mobile number"
                error={errors.mobile?.message}
                {...register("mobile")}
              />

              <FormControl
                variant="password"
                label="Password"
                placeholder="••••••••"
                error={errors.password?.message}
                {...register("password")}
              />

              <FormControl
                label="Location"
                placeholder="Location"
                error={errors.location?.message}
                {...register("location")}
              />
            </div>
          </div>
        </div>
      </Card>

      <FormActions submitting={submitting} onCancel={onCancel} />
    </form>
  )
}

// ── Profile image picker ─────────────────────────────────────────────────────
function ProfileImagePicker({
  value,
  onChange,
  error,
}: {
  value: File | null
  onChange: (file: File | null) => void
  error?: string
}) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [preview, setPreview] = useState<string | null>(null)
  const [dragOver, setDragOver] = useState(false)

  useEffect(() => {
    if (!value) {
      setPreview(null)
      return
    }
    const url = URL.createObjectURL(value)
    setPreview(url)
    return () => URL.revokeObjectURL(url)
  }, [value])

  const handleFile = (file: File | null) => {
    if (!file) return onChange(null)
    if (!file.type.startsWith("image/")) {
      toast.error("Only image files are allowed")
      return
    }
    onChange(file)
  }

  return (
    <div>
      <label className="mb-1.5 block text-sm font-medium text-gray-700">
        Profile Image
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
            : "border-primary-300 bg-primary-100/60 hover:border-primary-400",
          error && "border-red-400 bg-red-50"
        )}
      >
        {preview ? (
          <>
            <img
              src={preview}
              alt="Profile preview"
              className="h-full w-full rounded-card object-cover"
            />
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation()
                handleFile(null)
              }}
              className="absolute top-1.5 right-1.5 rounded-full bg-white/90 p-1 text-gray-700 shadow hover:bg-white"
              aria-label="Remove image"
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

      {error && <p className="mt-1 text-xs text-red-500">{error}</p>}
    </div>
  )
}

// ─────────────────────────────────────────────────────────────────────────────
// Builder/Developer form
// ─────────────────────────────────────────────────────────────────────────────

const builderSchema = z.object({
  firstname: z.string().min(1, "Name is required"),
  email: z.string().min(1, "Email is required").email("Enter a valid email"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  status: z.string().min(1, "Status is required"),
  brand_type: z.string().min(1, "Brand type is required"),
  company_id: z.string().min(1, "Company is required"),
})

type BuilderFormData = z.infer<typeof builderSchema>

function BuilderDeveloperForm({ onCancel }: { onCancel: () => void }) {
  const [companies, setCompanies] = useState<Company[]>([])
  const [companiesLoading, setCompaniesLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)

  const {
    register,
    handleSubmit,
    setError,
    clearErrors,
    formState: { errors },
  } = useForm<BuilderFormData>({
    resolver: zodResolver(builderSchema),
    defaultValues: {
      firstname: "",
      email: "",
      password: "",
      status: "",
      brand_type: "",
      company_id: "",
    },
  })

  useEffect(() => {
    let mounted = true
    companiesAPI
      .getAll()
      .then((list) => {
        if (!mounted) return
        setCompanies(list ?? [])
      })
      .catch(() => {})
      .finally(() => mounted && setCompaniesLoading(false))
    return () => {
      mounted = false
    }
  }, [])

  const companyOptions = useMemo(
    () =>
      companies.map((c) => ({
        value: String(c.id),
        label: c.company_name,
      })),
    [companies]
  )

  const applyServerErrors = (apiErrors: Record<string, string[]>) => {
    const fieldKeys: (keyof BuilderFormData)[] = [
      "firstname",
      "email",
      "password",
      "status",
      "brand_type",
      "company_id",
    ]
    fieldKeys.forEach((key) => {
      const message = apiErrors[key]?.[0]
      if (message) {
        setError(key, { type: "server", message })
      }
    })
  }

  const onSubmit = async (data: BuilderFormData) => {
    clearErrors()
    setSubmitting(true)
    try {
      await usersAPI.createBuilder(
        {
          firstname: data.firstname,
          email: data.email,
          password: data.password,
          status: data.status as Parameters<
            typeof usersAPI.createBuilder
          >[0]["status"],
          brand_type: data.brand_type as Parameters<
            typeof usersAPI.createBuilder
          >[0]["brand_type"],
          company_id: Number(data.company_id),
        },
        {
          successMessage: "User created successfully",
          customErrorMessage: "Failed to create user",
        }
      )
      onCancel()
    } catch (err) {
      if (isValidationError(err)) {
        applyServerErrors(err.response.data.errors)
      }
    } finally {
      setSubmitting(false)
    }
  }

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-6" noValidate>
      <Card className="overflow-hidden p-0">
        <FormSectionHeader title="Add User Detail" />

        <div className="grid grid-cols-1 gap-x-6 gap-y-5 p-6 md:grid-cols-2">
          <FormControl
            label="Name"
            placeholder="Name"
            error={errors.firstname?.message}
            {...register("firstname")}
          />

          <FormControl
            label="Email"
            type="email"
            placeholder="name@example.com"
            error={errors.email?.message}
            {...register("email")}
          />

          <FormControl
            variant="password"
            label="Password"
            placeholder="••••••••"
            error={errors.password?.message}
            {...register("password")}
          />

          <FormControl
            variant="select"
            label="Status"
            error={errors.status?.message}
            defaultValue=""
            {...register("status")}
          >
            <option value="" disabled>
              Select status
            </option>
            {USER_STATUS_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </FormControl>

          <FormControl
            variant="select"
            label="Brand Type"
            error={errors.brand_type?.message}
            defaultValue=""
            {...register("brand_type")}
          >
            <option value="" disabled>
              Select brand type
            </option>
            {BRAND_TYPE_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </FormControl>
        </div>
      </Card>

      <Card className="overflow-hidden p-0">
        <div className="p-6">
          <FormControl
            variant="select"
            label="Company"
            error={errors.company_id?.message}
            defaultValue=""
            disabled={companiesLoading}
            {...register("company_id")}
          >
            <option value="" disabled>
              {companiesLoading ? "Loading companies…" : "Select company"}
            </option>
            {companyOptions.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </FormControl>
        </div>
      </Card>

      <FormActions submitting={submitting} onCancel={onCancel} />
    </form>
  )
}

// ── Shared form actions ──────────────────────────────────────────────────────
function FormActions({
  submitting,
  onCancel,
}: {
  submitting: boolean
  onCancel: () => void
}) {
  return (
    <div className="flex flex-wrap items-center gap-3">
      <Button
        type="submit"
        color="primary"
        disabled={submitting}
        className="px-6 py-3 disabled:cursor-not-allowed disabled:opacity-60"
      >
        {submitting ? "Creating…" : "Create"}
      </Button>
      <Button
        type="button"
        color="destructive"
        onClick={onCancel}
        className="px-6 py-3"
      >
        Cancel
      </Button>
    </div>
  )
}
