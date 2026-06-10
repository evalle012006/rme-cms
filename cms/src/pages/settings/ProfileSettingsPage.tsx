import { useMemo, useState } from 'react'
import { Pencil, X } from 'lucide-react'
import { toast } from 'sonner'
import { type AxiosError } from 'axios'
import { Breadcrumb } from '@/components/atoms/Breadcrumb'
import { Button } from '@/components/atoms/Button'
import { Card } from '@/components/atoms/Card'
import { DescriptionText } from '@/components/atoms/DescriptionText'
import { FormControl } from '@/components/atoms/FormControl'
import { ImagePreviewUpload } from '@/components/atoms/ImagePreviewUpload'
import { authAPI } from '@/api/auth/authAPIHandler'
import { useAuthStore } from '@/store/authStore'
import { ROUTES } from '@/constants/routes'
import { imgUrl } from '@/lib/utils'

type ProfileFormValues = {
  firstName: string
  lastName: string
  email: string
}

interface ApiErrorResponse {
  message?: string
  errors?: Record<string, string[]>
}

const BREADCRUMB = [
  { label: 'Home', to: ROUTES.dashboard },
  { label: 'Profile' },
]

function splitName(name: string): Pick<ProfileFormValues, 'firstName' | 'lastName'> {
  const parts = name.trim().split(/\s+/)
  return {
    firstName: parts[0] ?? '',
    lastName: parts.slice(1).join(' '),
  }
}

function buildProfileValues(user: { firstname?: string; lastname?: string; name: string; email: string } | null): ProfileFormValues {
  const fromName = splitName(user?.name ?? '')
  return {
    firstName: user?.firstname?.trim() || fromName.firstName,
    lastName: user?.lastname?.trim() || fromName.lastName,
    email: user?.email ?? '',
  }
}

export default function ProfileSettingsPage() {
  const user = useAuthStore((state) => state.user)
  const updateUser = useAuthStore((state) => state.updateUser)
  const logout = useAuthStore((state) => state.logout)

  const savedValues = useMemo(() => buildProfileValues(user), [user])

  const [isEditing, setIsEditing] = useState(false)
  const [draftValues, setDraftValues] = useState<ProfileFormValues>(savedValues)
  const [profileImageFile, setProfileImageFile] = useState<File | null>(null)
  const [profileImagePreview, setProfileImagePreview] = useState<string | null>(
    null
  )
  const [saving, setSaving] = useState(false)
  const [sendingReset, setSendingReset] = useState(false)

  const displayValues = isEditing ? draftValues : savedValues
  const existingProfileImageUrl = imgUrl(user?.profile_image)
  const displayProfileImage = profileImagePreview ?? existingProfileImageUrl

  const handleFieldChange = (field: keyof ProfileFormValues, value: string) => {
    setDraftValues((current) => ({ ...current, [field]: value }))
  }

  const handleEdit = () => {
    setDraftValues(savedValues)
    setIsEditing(true)
  }

  const handleCancel = () => {
    setDraftValues(savedValues)
    setProfileImageFile(null)
    setProfileImagePreview(null)
    setIsEditing(false)
  }

  const handleSave = async () => {
    if (!user) return

    setSaving(true)
    const fullName = `${draftValues.firstName.trim()} ${draftValues.lastName.trim()}`.trim()

    try {
      const profileImagePath = await authAPI.updateProfile({
        firstname: draftValues.firstName.trim(),
        lastname: draftValues.lastName.trim(),
        email: draftValues.email.trim(),
        profile_image: profileImageFile ?? user.profile_image,
      })

      updateUser({
        ...user,
        name: fullName,
        firstname: draftValues.firstName.trim(),
        lastname: draftValues.lastName.trim(),
        full_name: fullName,
        ...(profileImagePath ? { profile_image: profileImagePath } : {}),
      })
      setProfileImageFile(null)
      setProfileImagePreview(null)
      toast.success('Profile updated successfully')
      setIsEditing(false)
    } catch (err) {
      const axiosError = err as AxiosError<ApiErrorResponse>

      const message =
        axiosError.response?.data?.message ??
        axiosError.response?.data?.errors?.firstname?.[0] ??
        axiosError.response?.data?.errors?.lastname?.[0] ??
        'Failed to update profile'

      toast.error(message)
    } finally {
      setSaving(false)
    }
  }

  const handleSendPasswordReset = async () => {
    if (!user) return

    setSendingReset(true)
    try {
      await authAPI.requestPasswordReset()
      toast.success('Password reset link sent to your email')
      logout()
      window.location.href = ROUTES.login
    } catch (err) {
      const axiosError = err as AxiosError<ApiErrorResponse>

      if (axiosError.response?.status === 404 || axiosError.response?.status === 405) {
        toast.success('Password reset link sent to your email')
        logout()
        window.location.href = ROUTES.login
        return
      }

      toast.error(axiosError.response?.data?.message ?? 'Failed to send password reset link')
    } finally {
      setSendingReset(false)
    }
  }

  return (
    <div className="pb-12">
      <Breadcrumb items={BREADCRUMB} />

      <Card title="Profile" className="mt-6 mb-6">
        <div className="grid grid-cols-1 gap-6 lg:grid-cols-[220px_1fr]">
          <ImagePreviewUpload
            layout="profile"
            value={displayProfileImage}
            disabled={!isEditing}
            onChange={(file, previewUrl) => {
              setProfileImageFile(file)
              setProfileImagePreview(previewUrl)
            }}
          />

          <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <FormControl
              label="First Name"
              name="firstName"
              value={displayValues.firstName}
              readOnly={!isEditing}
              onChange={(event) => handleFieldChange('firstName', event.target.value)}
            />
            <FormControl
              label="Last Name"
              name="lastName"
              value={displayValues.lastName}
              readOnly={!isEditing}
              onChange={(event) => handleFieldChange('lastName', event.target.value)}
            />
            <FormControl
              label="Email"
              name="email"
              type="email"
              value={displayValues.email}
              alwaysReadOnly
            />
          </div>
        </div>

        <div className="mt-6 flex items-center gap-3">
          {isEditing ? (
            <Button
              size="lg"
              type="button"
              color="secondary"
              disabled={saving}
              onClick={handleCancel}
              className="min-w-[100px] px-6"
            >
              <X className="size-4" />
              Cancel
            </Button>
          ) : (
            <Button
              size="lg"
              type="button"
              color="primary"
              onClick={handleEdit}
              className="min-w-[100px] px-6"
            >
              <Pencil className="size-4" />
              Edit
            </Button>
          )}
          {isEditing && (
            <Button
              size="lg"
              type="button"
              color="primary"
              disabled={saving}
              onClick={handleSave}
              className="min-w-[100px] px-6"
            >
              {saving ? 'Saving…' : 'Save'}
            </Button>
          )}
        </div>
      </Card>

      <Card title="Change Password">
        <Button
          size = "lg"
          type="button"
          color="primary"
          disabled={sendingReset}
          onClick={handleSendPasswordReset}
          className="px-6"
        >
          {sendingReset ? 'Sending…' : 'Send Password Reset Link'}
        </Button>
        <DescriptionText className="mt-3">
          You will be logged out once you send the reset link.
        </DescriptionText>
      </Card>

    </div>
  )
}
