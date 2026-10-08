// Every non-default locale the site serves under /{code}/ (same list as App\Models\Language::WEB_LOCALES). The server
// also prints it as window.__WEB_LOCALES__, which wins, so a locale added there needs no new build. The router used
// a 16-code list, so /ja/…, /it/… and 53 other locales showed "page not found" once Vue mounted (2026-10-08).
const FALLBACK = [
  'en', 'tr', 'fr', 'es', 'hi', 'ur', 'bn', 'pt', 'ru', 'id', 'de', 'zh', 'ku', 'fa', 'sw', 'ms',
  'af', 'am', 'az', 'be', 'bg', 'ca', 'cs', 'da', 'el', 'et', 'eu', 'fi', 'fil', 'gl', 'gu', 'he',
  'hr', 'hu', 'hy', 'is', 'it', 'ja', 'ka', 'kk', 'km', 'kn', 'ko', 'ky', 'lo', 'lt', 'lv', 'mk',
  'ml', 'mn', 'mr', 'my', 'ne', 'nl', 'no', 'pa', 'pl', 'rm', 'ro', 'si', 'sk', 'sl', 'sq', 'sr',
  'sv', 'ta', 'te', 'th', 'uk', 'vi', 'zu',
]

export const WEB_LOCALES = Array.isArray(window.__WEB_LOCALES__) && window.__WEB_LOCALES__.length ? window.__WEB_LOCALES__ : FALLBACK
