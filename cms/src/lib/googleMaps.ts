// Minimal Google Maps JS API loader.
//
// We don't pull in `@googlemaps/js-api-loader` to keep the bundle lean — there's
// only one consumer (the property add/edit pages) and the script is tiny. The
// loader is idempotent: concurrent callers all wait on the same promise, and a
// second call after a successful load resolves immediately.
//
// The API key comes from `VITE_GOOGLE_MAPS_API_KEY`. When the env var is
// missing the loader rejects with a typed error so callers can degrade
// gracefully (e.g. fall back to a plain text input).
//
// We deliberately type `google` loosely (`GoogleNamespace`) so this file
// doesn't require the `@types/google.maps` package — callers cast to the
// specific shape they need.

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY as
  | string
  | undefined

export interface PlaceAddressComponent {
  long_name: string
  short_name: string
  types: string[]
}

export interface PlaceGeometryLocation {
  lat(): number
  lng(): number
}

export interface PlaceResult {
  address_components?: PlaceAddressComponent[]
  formatted_address?: string
  geometry?: { location?: PlaceGeometryLocation }
  name?: string
}

export interface PlacesAutocomplete {
  addListener(event: 'place_changed', handler: () => void): { remove(): void }
  getPlace(): PlaceResult
}

interface PlacesAutocompleteOptions {
  types?: string[]
  componentRestrictions?: { country: string | string[] }
  fields?: string[]
}

export interface LatLngLiteral {
  lat: number
  lng: number
}

export interface MapOptions {
  center?: LatLngLiteral
  zoom?: number
  mapTypeControl?: boolean
  streetViewControl?: boolean
  fullscreenControl?: boolean
}

export interface GoogleMap {
  setCenter(latLng: LatLngLiteral): void
  panTo(latLng: LatLngLiteral): void
  addListener(event: 'click', handler: (e: { latLng: PlaceGeometryLocation | null }) => void): { remove(): void }
}

export interface GoogleMarker {
  setPosition(latLng: LatLngLiteral): void
  getPosition(): PlaceGeometryLocation | null | undefined
  addListener(event: 'dragend', handler: () => void): { remove(): void }
}

export interface GoogleNamespace {
  maps: {
    Map: new (el: HTMLElement, opts?: MapOptions) => GoogleMap
    Marker: new (opts: {
      map: GoogleMap
      position: LatLngLiteral
      draggable?: boolean
    }) => GoogleMarker
    places: {
      Autocomplete: new (
        input: HTMLInputElement,
        opts?: PlacesAutocompleteOptions
      ) => PlacesAutocomplete
    }
    event?: {
      clearInstanceListeners(instance: unknown): void
    }
  }
}

let loadPromise: Promise<GoogleNamespace> | null = null

export class GoogleMapsLoadError extends Error {
  constructor(message: string) {
    super(message)
    this.name = 'GoogleMapsLoadError'
  }
}

export function isGoogleMapsConfigured(): boolean {
  return !!GOOGLE_MAPS_API_KEY
}

export function loadGoogleMaps(): Promise<GoogleNamespace> {
  if (typeof window === 'undefined') {
    return Promise.reject(
      new GoogleMapsLoadError('Google Maps can only be loaded in the browser')
    )
  }

  const existing = (window as unknown as { google?: GoogleNamespace }).google
  if (existing?.maps?.Map && existing?.maps?.places) {
    return Promise.resolve(existing)
  }

  if (loadPromise) return loadPromise

  if (!GOOGLE_MAPS_API_KEY) {
    return Promise.reject(
      new GoogleMapsLoadError(
        'VITE_GOOGLE_MAPS_API_KEY is not configured. Set it in .env to enable location autocomplete.'
      )
    )
  }

  loadPromise = new Promise((resolve, reject) => {
    const script = document.createElement('script')
    script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(
      GOOGLE_MAPS_API_KEY
    )}&libraries=places&v=weekly`
    script.async = true
    script.defer = true
    script.onload = () => {
      const win = window as unknown as { google?: GoogleNamespace }
      if (win.google?.maps?.Map && win.google?.maps?.places) {
        resolve(win.google)
      } else {
        loadPromise = null
        reject(new GoogleMapsLoadError('Google Maps loaded without required libraries'))
      }
    }
    script.onerror = () => {
      loadPromise = null
      reject(new GoogleMapsLoadError('Failed to load Google Maps script'))
    }
    document.head.appendChild(script)
  })

  return loadPromise
}

export function parseCoordinate(value: string | null | undefined): number | null {
  if (value == null || value === '') return null
  const parsed = Number.parseFloat(value)
  return Number.isFinite(parsed) ? parsed : null
}

export function requestCurrentPosition(): Promise<LatLngLiteral> {
  return new Promise((resolve, reject) => {
    if (!navigator.geolocation) {
      reject(new Error('Geolocation is not supported by this browser'))
      return
    }

    navigator.geolocation.getCurrentPosition(
      (position) => {
        resolve({
          lat: position.coords.latitude,
          lng: position.coords.longitude,
        })
      },
      (error) => reject(error),
      { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
    )
  })
}
