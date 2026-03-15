/**
 * Shared utility functions for the Talabna frontend
 * Single source of truth - used across all components
 */

// Category icons and colors mapping
export const categoryConfig = {
  1: { icon: 'mdi-briefcase', color: '#4CAF50', name: { ar: 'وظائف', en: 'Jobs' } },
  2: { icon: 'mdi-cellphone', color: '#2196F3', name: { ar: 'أجهزة', en: 'Devices' } },
  3: { icon: 'mdi-home-city', color: '#9C27B0', name: { ar: 'عقارات', en: 'Real Estate' } },
  4: { icon: 'mdi-car', color: '#F44336', name: { ar: 'سيارات', en: 'Cars' } },
  5: { icon: 'mdi-room-service', color: '#FF9800', name: { ar: 'خدمات', en: 'Services' } },
  6: { icon: 'mdi-map-marker', color: '#00BCD4', name: { ar: 'قريب منك', en: 'Near You' } },
  7: { icon: 'mdi-play-circle', color: '#E91E63', name: { ar: 'ريلز', en: 'Reels' } },
  8: { icon: 'mdi-alert-circle', color: '#FF5722', name: { ar: 'عاجل', en: 'Urgent' } },
}

export const getCategoryIcon = (id) => categoryConfig[id]?.icon || 'mdi-folder'
export const getCategoryColor = (id) => categoryConfig[id]?.color || '#9E9E9E'

/**
 * Get localized name from a multilingual object
 */
export function getLocalizedName(nameObj, locale = 'ar') {
  if (!nameObj) return ''
  if (typeof nameObj === 'string') {
    try {
      const parsed = JSON.parse(nameObj)
      return parsed[locale] || parsed.ar || parsed.en || nameObj
    } catch {
      return nameObj
    }
  }
  return nameObj[locale] || nameObj.ar || nameObj.en || ''
}

/**
 * Format price with currency
 */
export function formatPrice(price, locale = 'ar', currency = 'ILS') {
  if (!price && price !== 0) return locale === 'ar' ? 'اتصل للسعر' : 'Contact for price'
  const num = Number(price)
  if (isNaN(num)) return price

  const symbols = { ILS: '₪', USD: '$', EUR: '€', SAR: 'ر.س', EGP: 'ج.م', JOD: 'د.أ' }
  const symbol = symbols[currency] || currency

  const formatted = num.toLocaleString(locale === 'ar' ? 'ar-SA' : 'en-US')
  return locale === 'ar' ? `${formatted} ${symbol}` : `${symbol}${formatted}`
}

/**
 * Format large numbers (1000 -> 1K, 1000000 -> 1M)
 */
export function formatNumber(num) {
  if (!num) return '0'
  if (num >= 1000000) return (num / 1000000).toFixed(1).replace(/\.0$/, '') + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1).replace(/\.0$/, '') + 'K'
  return num.toString()
}

/**
 * Generate URL-safe slug
 */
export function slugify(text) {
  if (!text) return ''
  return text.toString().toLowerCase()
    .replace(/\s+/g, '-')
    .replace(/[^\w\u0600-\u06FF-]+/g, '')
    .replace(/--+/g, '-')
    .replace(/^-+/, '')
    .replace(/-+$/, '')
}

/**
 * Ensure URL is absolute
 */
export function ensureAbsoluteUrl(url) {
  if (!url) return '/storage/photos/placeholder.jpg'
  if (url.startsWith('http://') || url.startsWith('https://')) return url
  return url.startsWith('/') ? url : `/${url}`
}

/**
 * Get media URL from a listing photo/video
 */
export function getMediaUrl(src) {
  if (!src) return null
  if (src.startsWith('http')) return src
  return `/storage/${src}`
}

/**
 * Time ago with bilingual support
 */
export function timeAgo(dateString, locale = 'ar') {
  if (!dateString) return ''
  const date = new Date(dateString)
  const now = new Date()
  const seconds = Math.floor((now - date) / 1000)

  const intervals = [
    { seconds: 31536000, ar: 'سنة', en: 'year' },
    { seconds: 2592000, ar: 'شهر', en: 'month' },
    { seconds: 604800, ar: 'أسبوع', en: 'week' },
    { seconds: 86400, ar: 'يوم', en: 'day' },
    { seconds: 3600, ar: 'ساعة', en: 'hour' },
    { seconds: 60, ar: 'دقيقة', en: 'minute' },
  ]

  for (const interval of intervals) {
    const count = Math.floor(seconds / interval.seconds)
    if (count >= 1) {
      const unit = locale === 'ar' ? interval.ar : interval.en
      const prefix = locale === 'ar' ? 'منذ' : ''
      const suffix = locale === 'ar' ? '' : ' ago'
      const plural = locale !== 'ar' && count > 1 ? 's' : ''
      return locale === 'ar'
        ? `${prefix} ${count} ${unit}`
        : `${count} ${unit}${plural}${suffix}`
    }
  }
  return locale === 'ar' ? 'الآن' : 'just now'
}

/**
 * Check if media is video
 */
export function isVideo(src) {
  if (!src) return false
  const videoExts = ['.mp4', '.mov', '.avi', '.webm', '.mkv']
  return videoExts.some(ext => src.toLowerCase().endsWith(ext))
}
