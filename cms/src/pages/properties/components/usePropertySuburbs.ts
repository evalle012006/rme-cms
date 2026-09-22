import { useEffect, useState } from 'react'
import {
  suburbsAPI,
  type SuburbByStateWithPostcodeOption,
} from '@/api/suburbs/suburbsAPIHandler'

export function usePropertySuburbs(stateId: string) {
  const [suburbs, setSuburbs] = useState<SuburbByStateWithPostcodeOption[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const trimmed = stateId.trim()
    if (!trimmed) {
      setSuburbs([])
      setLoading(false)
      return
    }

    let cancelled = false
    setSuburbs([])
    setLoading(true)

    suburbsAPI
      .byStateWithPostcode({ state_id: trimmed }, { showErrorToast: false })
      .then((rows) => {
        if (!cancelled) setSuburbs(rows ?? [])
      })
      .catch(() => {
        if (!cancelled) setSuburbs([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [stateId])

  return { suburbs, loading }
}
