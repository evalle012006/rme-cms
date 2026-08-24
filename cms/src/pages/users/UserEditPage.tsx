import { useCallback, useEffect, useRef, useState } from "react"
import { useNavigate, useParams } from "react-router-dom"
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

import { usersAPI, type User } from "@/api/users/usersAPIHandler"
import {
  USER_STATUS,
  USER_STATUS_OPTIONS,
  type UserStatus,
} from "@/api/users/usersConstants"
import { ROUTES } from "@/constants/routes"

// ── Form schema ──────────────────────────────────────────────────────────────
// Email is read-only on the page (matching the legacy UI) but is still part
// of the submit payload, so it lives in the form state. Mobile/suburb are
// nullable on the backend, so empty values are allowed here.

const editUserSchema = z.object({
  firstname: z.string().min(1, "First name is required"),
  lastname: z.string().min(1, "Last name is required"),
  display_name: z.string(),
  status: z.string().min(1, "Status is required"),
  email: z.string().min(1, "Email is required").email("Enter a valid email"),
  mobile: z.string(),
  location: z.string(),
})

type EditUserFormData = z.infer<typeof editUserSchema>

const BREADCRUMB = [
  { label: "Home", to: ROUTES.root },
  { label: "User", to: ROUTES.users.list },
  { label: "Edit" },
]

// ── Status normalisation ─────────────────────────────────────────────────────
// The backend stores status as "1"/"2"/"3"/"4". Guard against unexpected
// values by falling back to Pending.
const STATUS_MAP: Record<string, UserStatus> = {
  "1": USER_STATUS.APPROVED,
  "2": USER_STATUS.PENDING,
  "3": USER_STATUS.REJECTED,
  "4": USER_STATUS.SUSPENDED,
}

// ── Page ─────────────────────────────────────────────────────────────────────
export default function UserEditPage() {
  const { id: idParam } = useParams<{ id: string }>()
  const navigate = useNavigate()
  const id = Number(idParam)

  const [user, setUser] = useState<User | null>(null)
  const [loading, setLoading] = useState(true)
  const [notFound, setNotFound] = useState(false)
  const [submitting, setSubmitting] = useState(false)

  const [profileImage, setProfileImage] = useState<File | null>(null)
  const [existingProfileImageUrl, setExistingProfileImageUrl] = useState<string | null>(
    null
  )
  const [profileImageCleared, setProfileImageCleared] = useState(false)

  const {
    register,
    handleSubmit,
    reset,
    formState: { errors },
  } = useForm<EditUserFormData>({
    resolver: zodResolver(editUserSchema),
    defaultValues: {
      firstname: "",
      lastname: "",
      display_name: "",
      status: USER_STATUS.PENDING,
      email: "",
      mobile: "",
      location: "",
    },
  })

  // ── Load user ────────────────────────────────────────────────────────────
  useEffect(() => {
    if (!idParam || Number.isNaN(id)) {
      setNotFound(true)
      setLoading(false)
      return
    }
    let mounted = true
    setLoading(true)
    usersAPI
      .getById(id)
      .then((res) => {
        if (!mounted) return
        const data = res.data
        setUser(data)
        setExistingProfileImageUrl(data.full_profile_image ?? data.profile_image)
        setProfileImage(null)
        setProfileImageCleared(false)
        const normalisedStatus = STATUS_MAP[data.status] ?? USER_STATUS.PENDING
        reset({
          firstname: data.firstname ?? "",
          lastname: data.lastname ?? "",
          display_name: data.display_name ?? "",
          status: normalisedStatus,
          email: data.email ?? "",
          mobile: data.mobile ?? "",
          location: data.location ?? "",
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
  }, [id, idParam, reset])

  // ── Cancel ───────────────────────────────────────────────────────────────
  const handleCancel = useCallback(() => {
    navigate(-1)
  }, [navigate])

  // ── Submit ───────────────────────────────────────────────────────────────
  const onSubmit = async (data: EditUserFormData) => {
    if (!user) return

    const profileImagePayload =
      profileImage ?? (profileImageCleared ? null : user.profile_image)

    setSubmitting(true)
    try {
      await usersAPI.updateReviewer(
        {
          id: user.id,
          firstname: data.firstname,
          lastname: data.lastname,
          display_name: data.display_name,
          email: data.email,
          mobile: data.mobile.trim() === "" ? null : data.mobile,
          location: data.location.trim() === "" ? null : data.location,
          status: data.status,
          profile_image: profileImagePayload,
        },
        {
          successMessage: "User updated.",
          customErrorMessage: "Failed to update user",
        }
      )
      navigate(-1)
    } catch {
      // Toast already shown by the API wrapper / global interceptor.
    } finally {
      setSubmitting(false)
    }
  }

  if (loading) {
    return (
      <div className="space-y-10">
        <Breadcrumb items={BREADCRUMB} />
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

  if (notFound || !user) {
    return (
      <div className="space-y-10">
        <Breadcrumb items={BREADCRUMB} />
        <Card className="p-10 text-center text-sm text-gray-500">
          User not found.
        </Card>
      </div>
    )
  }

  return (
    <div className="space-y-10">
      <Breadcrumb items={BREADCRUMB} />

      <form
        onSubmit={handleSubmit(onSubmit)}
        className="space-y-6"
        noValidate
      >
        <Card className="overflow-hidden p-0">
          {/* Header bar with "Email User" action on the right */}
          <div className="flex items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-4">
            <h2 className="text-base font-semibold text-gray-800">
              Edit User Detail
            </h2>
            <a href={`mailto:${user.email}`}>
              <Button color="primary" size="sm" className="h-auto px-4 py-1.5">
                Email User
              </Button>
            </a>
          </div>

          {/* Top block — profile image (spans two rows) + 2 fields per row */}
          <div className="grid grid-cols-1 gap-x-6 gap-y-5 p-6 md:grid-cols-[200px_1fr_1fr]">
            <div className="md:row-span-2">
              <ProfileImagePicker
                file={profileImage}
                existingUrl={
                  profileImageCleared ? null : existingProfileImageUrl
                }
                onChange={(file) => {
                  setProfileImage(file)
                  if (file) {
                    setProfileImageCleared(false)
                  }
                }}
                onClearExisting={() => {
                  setExistingProfileImageUrl(null)
                  setProfileImageCleared(true)
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
              label="Display Name"
              placeholder="Display name"
              error={errors.display_name?.message}
              {...register("display_name")}
            />

            <FormControl
              variant="select"
              label="Status"
              error={errors.status?.message}
              {...register("status")}
            >
              {USER_STATUS_OPTIONS.map((opt) => (
                <option key={opt.value} value={opt.value}>
                  {opt.label}
                </option>
              ))}
            </FormControl>
          </div>

          {/* Email + Mobile + Suburb row — full width grid (no logo column) */}
          <div className="grid grid-cols-1 gap-x-6 gap-y-5 px-6 pb-6 md:grid-cols-2">
            <FormControl
              label="Email"
              type="email"
              placeholder="name@example.com"
              readOnly
              disabled
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
              label="Suburb"
              placeholder="Suburb"
              error={errors.location?.message}
              {...register("location")}
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

// ── Profile image picker ─────────────────────────────────────────────────────

function ProfileImagePicker({
  file,
  existingUrl,
  onChange,
  onClearExisting,
}: {
  file: File | null
  existingUrl: string | null
  onChange: (file: File | null) => void
  onClearExisting?: () => void
}) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [filePreview, setFilePreview] = useState<string | null>(null)
  const [dragOver, setDragOver] = useState(false)

  useEffect(() => {
    if (!file) {
      setFilePreview(null)
      return
    }
    const url = URL.createObjectURL(file)
    setFilePreview(url)
    return () => URL.revokeObjectURL(url)
  }, [file])

  const preview = filePreview ?? existingUrl

  const handleFile = (next: File | null) => {
    if (next && !next.type.startsWith("image/")) {
      toast.error("Only image files are allowed")
      return
    }
    onChange(next)
  }

  const handleRemove = () => {
    if (file) {
      onChange(null)
      return
    }
    onClearExisting?.()
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
            : "border-primary-300 bg-primary-100/60 hover:border-primary-400"
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
                handleRemove()
              }}
              className="absolute top-1.5 right-1.5 rounded-full bg-gray-900/80 p-1 text-white shadow hover:bg-gray-900"
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
    </div>
  )
}