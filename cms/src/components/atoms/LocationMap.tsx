import { useEffect, useRef, useState } from 'react'
import { cn } from '@/lib/utils'
import {
  isGoogleMapsConfigured,
  loadGoogleMaps,
  parseCoordinate,
  requestCurrentPosition,
  type GoogleMap,
  type GoogleMarker,
  type LatLngLiteral,
} from '@/lib/googleMaps'

export type LocationMapProps = {
  lat: string
  lng: string
  onLocationChange?: (lat: number, lng: number) => void
  /** Request browser geolocation on mount and center the map on the user's position. */
  requestCurrentLocation?: boolean
  disabled?: boolean
  className?: string
}

type MapStatus = 'loading' | 'ready' | 'unavailable' | 'error'

const DEFAULT_CENTER: LatLngLiteral = { lat: -33.8688, lng: 151.2093 }
const DEFAULT_ZOOM = 14

export function LocationMap({
  lat,
  lng,
  onLocationChange,
  requestCurrentLocation = false,
  disabled = false,
  className,
}: LocationMapProps) {
  const containerRef = useRef<HTMLDivElement>(null)
  const mapRef = useRef<GoogleMap | null>(null)
  const markerRef = useRef<GoogleMarker | null>(null)
  const onLocationChangeRef = useRef(onLocationChange)
  const skipNextPropSync = useRef(false)

  const [status, setStatus] = useState<MapStatus>(() =>
    isGoogleMapsConfigured() ? 'loading' : 'unavailable'
  )
  const [locationHint, setLocationHint] = useState<string | null>(null)

  useEffect(() => {
    onLocationChangeRef.current = onLocationChange
  }, [onLocationChange])

  const applyPosition = (position: LatLngLiteral, notifyParent: boolean) => {
    mapRef.current?.panTo(position)
    markerRef.current?.setPosition(position)
    if (notifyParent) {
      skipNextPropSync.current = true
      onLocationChangeRef.current?.(position.lat, position.lng)
    }
  }

  useEffect(() => {
    if (!isGoogleMapsConfigured() || !containerRef.current) return

    let cancelled = false
    let mapClickListener: { remove(): void } | null = null
    let markerDragListener: { remove(): void } | null = null

    const init = async () => {
      try {
        let initialCenter: LatLngLiteral = DEFAULT_CENTER

        const parsedLat = parseCoordinate(lat)
        const parsedLng = parseCoordinate(lng)
        if (parsedLat != null && parsedLng != null) {
          initialCenter = { lat: parsedLat, lng: parsedLng }
        }

        if (requestCurrentLocation) {
          setLocationHint('Requesting your location…')
          try {
            initialCenter = await requestCurrentPosition()
            if (cancelled) return
            skipNextPropSync.current = true
            onLocationChangeRef.current?.(initialCenter.lat, initialCenter.lng)
            setLocationHint(null)
          } catch {
            if (cancelled) return
            setLocationHint(
              'Location access denied or unavailable. Search or click the map to set a position.'
            )
          }
        }

        const google = await loadGoogleMaps()
        if (cancelled || !containerRef.current) return

        const map = new google.maps.Map(containerRef.current, {
          center: initialCenter,
          zoom: DEFAULT_ZOOM,
          mapTypeControl: false,
          streetViewControl: false,
          fullscreenControl: false,
        })
        mapRef.current = map

        const marker = new google.maps.Marker({
          map,
          position: initialCenter,
          draggable: !disabled,
        })
        markerRef.current = marker

        mapClickListener = map.addListener('click', (event) => {
          if (disabled || !event.latLng) return
          applyPosition(
            { lat: event.latLng.lat(), lng: event.latLng.lng() },
            true
          )
        })

        markerDragListener = marker.addListener('dragend', () => {
          if (disabled) return
          const position = marker.getPosition()
          if (!position) return
          applyPosition(
            { lat: position.lat(), lng: position.lng() },
            true
          )
        })

        setStatus('ready')
      } catch {
        if (!cancelled) setStatus('error')
      }
    }

    void init()

    return () => {
      cancelled = true
      mapClickListener?.remove()
      markerDragListener?.remove()
      mapRef.current = null
      markerRef.current = null
    }
    // eslint-disable-next-line react-hooks/exhaustive-deps -- map is initialized once per mount
  }, [requestCurrentLocation])

  useEffect(() => {
    if (skipNextPropSync.current) {
      skipNextPropSync.current = false
      return
    }

    const parsedLat = parseCoordinate(lat)
    const parsedLng = parseCoordinate(lng)
    if (parsedLat == null || parsedLng == null || !mapRef.current) return

    applyPosition({ lat: parsedLat, lng: parsedLng }, false)
  }, [lat, lng])

  if (status === 'unavailable') {
    return (
      <div
        className={cn(
          'flex h-64 items-center justify-center rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 text-center text-sm text-gray-500',
          className
        )}
      >
        Map preview unavailable — set <code className="mx-1">VITE_GOOGLE_MAPS_API_KEY</code>{' '}
        in your environment file.
      </div>
    )
  }

  if (status === 'error') {
    return (
      <div
        className={cn(
          'flex h-64 items-center justify-center rounded-md border border-red-200 bg-red-50 px-4 text-center text-sm text-red-600',
          className
        )}
      >
        Google Maps failed to load. Check your API key, enabled APIs, and domain
        restrictions in Google Cloud Console.
      </div>
    )
  }

  return (
    <div className={cn('space-y-2', className)}>
      {locationHint ? (
        <p className="text-xs text-gray-500">{locationHint}</p>
      ) : null}
      <div className="relative overflow-hidden rounded-md border border-gray-200">
        <div ref={containerRef} className="h-64 w-full" aria-hidden={status === 'loading'} />
        {status === 'loading' ? (
          <div className="absolute inset-0 flex items-center justify-center bg-gray-100 text-sm text-gray-500">
            Loading map…
          </div>
        ) : null}
      </div>
    </div>
  )
}
