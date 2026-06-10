import { useEffect, useRef, useState } from 'react'
import { Input } from '@/components/ui/input'
import {
  loadGoogleMaps,
  isGoogleMapsConfigured,
  type PlaceResult,
  type PlacesAutocomplete,
} from '@/lib/googleMaps'

// ── Address payload ──────────────────────────────────────────────────────────
// What the parent form actually cares about — already normalized from the
// raw `address_components[]` array. Country/state/etc. are best-effort: when
// Google doesn't return a component, the field stays empty and the parent
// keeps whatever the user typed earlier.

export interface ResolvedPlace {
  formatted_address: string
  address: string
  country: string
  state: string
  city: string
  post_code: string
  suburb: string
  lat: number | null
  lng: number | null
}

// ── Component ────────────────────────────────────────────────────────────────
// Thin wrapper around Google's Places Autocomplete input. Falls back to a
// plain text input if the API key isn't configured so the form is still
// usable in dev without VITE_GOOGLE_MAPS_API_KEY.

export type LocationAutocompleteProps = {
  value: string
  onChange: (value: string) => void
  onPlaceSelected?: (place: ResolvedPlace) => void
  placeholder?: string
  disabled?: boolean
  /** Two-letter country code(s) to bias results (defaults to Australia). */
  country?: string | string[]
}

export function LocationAutocomplete({
  value,
  onChange,
  onPlaceSelected,
  placeholder = 'Search Nearest Location',
  disabled = false,
  country = 'au',
}: LocationAutocompleteProps) {
  const inputRef = useRef<HTMLInputElement>(null)
  const [ready, setReady] = useState(false)
  const onPlaceSelectedRef = useRef(onPlaceSelected)

  useEffect(() => {
    onPlaceSelectedRef.current = onPlaceSelected
  }, [onPlaceSelected])

  useEffect(() => {
    if (!isGoogleMapsConfigured()) return
    let cancelled = false
    let autocomplete: PlacesAutocomplete | null = null
    let listener: { remove(): void } | null = null

    loadGoogleMaps()
      .then((google) => {
        if (cancelled || !inputRef.current) return
        autocomplete = new google.maps.places.Autocomplete(inputRef.current, {
          types: ['geocode'],
          componentRestrictions: { country },
          fields: ['address_components', 'formatted_address', 'geometry', 'name'],
        })
        listener = autocomplete.addListener('place_changed', () => {
          const place = autocomplete!.getPlace()
          const resolved = normalizePlace(place)
          if (resolved.formatted_address) {
            onChange(resolved.formatted_address)
          }
          onPlaceSelectedRef.current?.(resolved)
        })
        setReady(true)
      })
      .catch(() => {
        // API key missing or script failed to load — graceful fallback to a
        // plain input. The user can still type freely.
      })

    return () => {
      cancelled = true
      listener?.remove()
      // Avoid memory leaks: detach Google's instance listeners from the input.
      if (autocomplete) {
        const google = (window as unknown as { google?: { maps: { event?: { clearInstanceListeners(i: unknown): void } } } }).google
        google?.maps.event?.clearInstanceListeners(autocomplete)
      }
    }
  }, [country, onChange])

  return (
    <Input
      ref={inputRef}
      type="text"
      value={value}
      onChange={(e) => onChange(e.target.value)}
      placeholder={placeholder}
      disabled={disabled}
      autoComplete="off"
      data-google-places-ready={ready ? 'true' : 'false'}
    />
  )
}

// ── Helpers ──────────────────────────────────────────────────────────────────

const COMPONENT_TYPE_MAP: Record<keyof Omit<ResolvedPlace, 'formatted_address' | 'address' | 'lat' | 'lng'>, string[]> = {
  country: ['country'],
  state: ['administrative_area_level_1'],
  city: ['locality', 'postal_town', 'administrative_area_level_2'],
  post_code: ['postal_code'],
  suburb: ['sublocality', 'sublocality_level_1', 'neighborhood'],
}

function pickComponent(
  components: PlaceResult['address_components'] | undefined,
  preferredTypes: string[],
  useShortName = false
): string {
  if (!components) return ''
  for (const type of preferredTypes) {
    const match = components.find((c) => c.types.includes(type))
    if (match) return useShortName ? match.short_name : match.long_name
  }
  return ''
}

function buildStreetAddress(components: PlaceResult['address_components'] | undefined): string {
  if (!components) return ''
  const streetNumber = pickComponent(components, ['street_number'])
  const route = pickComponent(components, ['route'])
  return [streetNumber, route].filter(Boolean).join(' ')
}

function normalizePlace(place: PlaceResult): ResolvedPlace {
  const components = place.address_components
  const loc = place.geometry?.location
  return {
    formatted_address: place.formatted_address ?? '',
    address:
      buildStreetAddress(components) ||
      place.formatted_address ||
      place.name ||
      '',
    country: pickComponent(components, COMPONENT_TYPE_MAP.country),
    state: pickComponent(components, COMPONENT_TYPE_MAP.state),
    city: pickComponent(components, COMPONENT_TYPE_MAP.city),
    post_code: pickComponent(components, COMPONENT_TYPE_MAP.post_code),
    suburb: pickComponent(components, COMPONENT_TYPE_MAP.suburb),
    lat: loc ? loc.lat() : null,
    lng: loc ? loc.lng() : null,
  }
}
