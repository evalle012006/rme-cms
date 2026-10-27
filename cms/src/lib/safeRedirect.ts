// In-app path only. Used to send an admin back to the page they opened from
// an email after they sign in. Rejects external and protocol-relative URLs.
export function safeRedirectPath(value: string | null | undefined): string | null {
  if (!value) return null

  let decoded: string
  try {
    decoded = decodeURIComponent(value)
  } catch {
    return null
  }

  if (!decoded.startsWith('/') || decoded.startsWith('//')) return null
  if (decoded.includes('\\') || decoded.includes('://')) return null
  if (/[\u0000-\u001F\u007F]/.test(decoded)) return null

  const pathOnly = decoded.split(/[?#]/)[0] ?? ''
  if (pathOnly === '/login' || pathOnly === '/login/') return null

  return decoded
}
