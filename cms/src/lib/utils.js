import { clsx } from 'clsx'
import { twMerge } from 'tailwind-merge'

export function cn(...inputs) {
  return twMerge(clsx(inputs))
}

export function imgUrl(path) {
  if (!path) return null
  if (path.startsWith('http')) return path
  return `${import.meta.env.VITE_IMG_URL}/${path}`
}

export function formatDate(date) {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-AU', {
    day: '2-digit', month: 'short', year: 'numeric',
  })
}
