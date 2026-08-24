import { clsx, type ClassValue } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs))
}

// Returns a fully-qualified image URL. Handles absolute URLs (S3/CDN)
// and relative paths stored in the DB (prefixed with VITE_IMG_URL).
export function imgUrl(path: string | null | undefined): string | null {
  if (!path) return null
  if (path.startsWith('http')) return path
  const base = import.meta.env.VITE_IMG_URL.replace(/\/$/, '')
  const filePath = path.replace(/^\/+/, '')
  return `${base}/${filePath}`
}

// Formats an ISO date string or Date object to Australian locale (01 Jan 2024).
export function formatDate(date: string | Date | null | undefined): string {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-AU', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}