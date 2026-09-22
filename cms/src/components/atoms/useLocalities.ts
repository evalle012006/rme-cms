import { useEffect, useState } from 'react'
import {
  localitiesAPI,
  type Locality,
  type LocalityState,
} from '@/api/localities/localitiesAPIHandler'

/**
 * The reference data is static between quarterly imports, so both lookups are
 * memoised per module. Several LocalitySelects on one page (reviews list,
 * dashboard filters) then share a single request.
 */
let statesPromise: Promise<LocalityState[]> | null = null
const localitiesByState = new Map<string, Promise<Locality[]>>()

function loadStates() {
  if (!statesPromise) {
    statesPromise = localitiesAPI
      .states({ showErrorToast: false })
      .catch((error) => {
        statesPromise = null
        throw error
      })
  }
  return statesPromise
}

function loadLocalities(stateCode: string) {
  const cached = localitiesByState.get(stateCode)
  if (cached) return cached

  const request = localitiesAPI
    .byState(stateCode, { showErrorToast: false })
    .catch((error) => {
      localitiesByState.delete(stateCode)
      throw error
    })

  localitiesByState.set(stateCode, request)
  return request
}

export function useLocalityStates() {
  const [states, setStates] = useState<LocalityState[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    let cancelled = false

    loadStates()
      .then((rows) => {
        if (!cancelled) setStates(rows ?? [])
      })
      .catch(() => {
        if (!cancelled) setStates([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [])

  return { states, loading }
}

/** Every active locality in one state. Empty until a state is chosen. */
export function useLocalitiesByState(stateCode: string) {
  const [localities, setLocalities] = useState<Locality[]>([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    const code = stateCode.trim()
    if (!code) {
      setLocalities([])
      setLoading(false)
      return
    }

    let cancelled = false
    setLocalities([])
    setLoading(true)

    loadLocalities(code)
      .then((rows) => {
        if (!cancelled) setLocalities(rows ?? [])
      })
      .catch(() => {
        if (!cancelled) setLocalities([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [stateCode])

  return { localities, loading }
}
