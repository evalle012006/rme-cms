import { useCallback, useMemo } from "react"
import { useNavigate, useSearchParams } from "react-router-dom"

import { Breadcrumb } from "@/components/atoms/Breadcrumb"
import { BuilderDeveloperForm } from "@/pages/users/components/BuilderDeveloperForm"
import { ROUTES } from "@/constants/routes"

function resolveReturnPath(from: string | null) {
  return from === "pending" ? ROUTES.builder.pending : ROUTES.builder.approved
}

export default function BuilderAddPage() {
  const navigate = useNavigate()
  const [searchParams] = useSearchParams()
  const returnPath = resolveReturnPath(searchParams.get("from"))
  const isFromPending = searchParams.get("from") === "pending"

  const breadcrumb = useMemo(
    () => [
      { label: "Home", to: ROUTES.dashboard },
      {
        label: isFromPending
          ? "Pending Company Users"
          : "Approved Company Users",
        to: returnPath,
      },
      { label: "Add Company User" },
    ],
    [isFromPending, returnPath]
  )

  const handleCancel = useCallback(
    () => navigate(returnPath),
    [navigate, returnPath]
  )

  return (
    <div className="space-y-6">
      <Breadcrumb items={breadcrumb} />
      <BuilderDeveloperForm onCancel={handleCancel} />
    </div>
  )
}
