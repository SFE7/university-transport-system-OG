export function getCarPhotoUrl(category: string, model: string): string | null {
  if (!category || !model) return null

  const safeCategory = encodeURIComponent(category.trim())
  const safeModel = encodeURIComponent(model.trim())

  const apiBase = (import.meta.env.VITE_API_URL || '').replace(/\/$/, '')
  return `${apiBase}/photos/${safeCategory}/${safeModel}`
}
