import { useEffect, useMemo, useState } from "react"
import { useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"

import { Button } from "@/components/atoms/Button"
import { FormControl } from "@/components/atoms/FormControl"
import { Card } from "@/components/ui/card"

import {
  BRAND_TYPE_OPTIONS,
  USER_STATUS_OPTIONS,
} from "@/api/users/usersConstants"
import { usersAPI } from "@/api/users/usersAPIHandler"
import { isValidationError } from "@/api/apiService"
import { companiesAPI, type Company } from "@/api/companies/companiesAPIHandler"

const builderSchema = z.object({
  firstname: z.string().min(1, "Name is required"),
  email: z.string().min(1, "Email is required").email("Enter a valid email"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  status: z.string().min(1, "Status is required"),
  brand_type: z.string().min(1, "Brand type is required"),
  company_id: z.string().optional(),
})

type BuilderFormData = z.infer<typeof builderSchema>

function FormSectionHeader({ title }: { title: string }) {
  return (
    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4">
      <h2 className="text-base font-semibold text-gray-800">{title}</h2>
    </div>
  )
}

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

export function BuilderDeveloperForm({ onCancel }: { onCancel: () => void }) {
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
          company_id: data.company_id ? Number(data.company_id) : null,
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
            <option value="">
              {companiesLoading ? "Loading companies…" : "No company"}
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
