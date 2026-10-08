<template>
  <div class="shop-page" :dir="dir">
    <div v-if="loading && !shop" class="shop-wrap shop-loading">
      <div class="spinner spinner-lg"></div>
    </div>

    <div v-else-if="notFound" class="shop-wrap shop-missing">
      <i class="mdi mdi-storefront-outline"></i>
      <h1>{{ tr('Shop not available', 'المتجر غير متاح') }}</h1>
      <p>{{ tr('This shop is closed or the link is wrong.', 'هذا المتجر مغلق حالياً أو الرابط غير صحيح.') }}</p>
      <router-link to="/" class="shop-btn shop-btn-primary">{{ tr('Browse listings', 'تصفح الإعلانات') }}</router-link>
    </div>

    <div v-else-if="shop" class="shop-wrap">
      <div class="shop-cover" :class="{ empty: !shop.cover }">
        <img v-if="shop.cover" :src="img(shop.cover)" :alt="shop.name" fetchpriority="high" decoding="async" />
      </div>

      <header class="shop-head">
        <div class="shop-logo">
          <img v-if="shop.logo" :src="img(shop.logo)" :alt="shop.name" width="112" height="112" decoding="async" />
          <span v-else>{{ initial }}</span>
        </div>
        <div class="shop-title">
          <div class="shop-name-row">
            <h1>{{ shop.name }}</h1>
            <span class="shop-badge" :title="tr('Shop on Talabna', 'متجر على طلبنا')">
              <i class="mdi mdi-check-decagram"></i>
            </span>
          </div>
          <p class="shop-sub">
            <span v-if="shop.category">{{ shop.category.name }}</span>
            <span v-if="place"> · {{ place }}</span>
          </p>
          <div class="shop-chips">
            <span v-if="openState" class="shop-chip" :class="openState.open ? 'is-open' : 'is-closed'">
              <span class="dot"></span>{{ openState.label }}
            </span>
            <span class="shop-chip">{{ stats.products }} {{ tr('products', 'منتج') }}</span>
            <span class="shop-chip">{{ stats.followers }} {{ tr('followers', 'متابع') }}</span>
            <span v-if="stats.sold" class="shop-chip">{{ stats.sold }} {{ tr('sold', 'تم بيعه') }}</span>
          </div>
        </div>
        <div class="shop-actions">
          <a :href="appLink" class="shop-btn shop-btn-primary" @click="contact('chat')">
            <i class="mdi mdi-message-text-outline"></i>{{ tr('Message the shop', 'راسل المتجر') }}
          </a>
          <a v-if="whatsappLink" :href="whatsappLink" target="_blank" rel="noopener" class="shop-btn" @click="contact('whatsapp')">
            <i class="mdi mdi-whatsapp"></i>{{ tr('WhatsApp', 'واتساب') }}
          </a>
          <a v-if="phoneLink" :href="phoneLink" class="shop-btn" @click="contact('call')">
            <i class="mdi mdi-phone-outline"></i>{{ tr('Call', 'اتصال') }}
          </a>
          <button type="button" class="shop-btn shop-btn-icon" :aria-label="tr('Share', 'مشاركة')" @click="share">
            <i class="mdi mdi-share-variant-outline"></i>
          </button>
        </div>
      </header>

      <div v-if="shop.offer" class="shop-offer">
        <i class="mdi mdi-bullhorn-outline"></i>
        <div>
          <strong>{{ tr('Offer', 'عرض المتجر') }}</strong>
          <span>{{ shop.offer }}</span>
        </div>
      </div>

      <div class="shop-body">
        <section class="shop-main">
          <div v-if="featured.length" class="shop-featured">
            <h2>{{ tr('Featured', 'منتجات مميزة') }}</h2>
            <div class="shop-grid">
              <listing-card v-for="l in featured" :key="'f' + l.id" :listing="l" :locale="locale" />
            </div>
          </div>

          <div class="shop-tools">
            <label class="shop-search">
              <i class="mdi mdi-magnify"></i>
              <span class="sr-only">{{ tr('Search this shop', 'ابحث في المتجر') }}</span>
              <input v-model="q" type="search" :placeholder="tr('Search this shop', 'ابحث في منتجات المتجر')" @input="onSearch" />
            </label>
            <div class="shop-shelves" role="tablist">
              <button type="button" role="tab" :aria-selected="shelf === 0" :class="{ on: shelf === 0 }" @click="pickShelf(0)">
                {{ tr('All', 'الكل') }} <span>{{ stats.products }}</span>
              </button>
              <button v-for="s in shelves" :key="s.id" type="button" role="tab" :aria-selected="shelf === s.id" :class="{ on: shelf === s.id }" @click="pickShelf(s.id)">
                {{ s.name }} <span>{{ s.count }}</span>
              </button>
            </div>
          </div>

          <div v-if="listings.length" class="shop-grid">
            <listing-card v-for="l in listings" :key="l.id" :listing="l" :locale="locale" />
          </div>
          <div v-else-if="postsLoading" class="shop-empty"><div class="spinner"></div></div>
          <div v-else class="shop-empty">{{ tr('Nothing here yet.', 'لا توجد منتجات هنا بعد.') }}</div>

          <div v-if="page < lastPage" class="shop-more">
            <button type="button" class="shop-btn" :disabled="postsLoading" @click="loadPosts(page + 1)">{{ tr('Show more', 'عرض المزيد') }}</button>
          </div>
        </section>

        <aside class="shop-side">
          <div v-if="shop.about" class="shop-card">
            <h2>{{ tr('About the shop', 'عن المتجر') }}</h2>
            <p class="shop-about">{{ shop.about }}</p>
          </div>

          <div v-if="weekRows.length || shop.hours" class="shop-card">
            <div class="shop-card-head">
              <h2>{{ tr('Opening hours', 'ساعات العمل') }}</h2>
              <span v-if="openState" :class="openState.open ? 'txt-open' : 'txt-closed'">{{ openState.short }}</span>
            </div>
            <dl v-if="weekRows.length" class="shop-hours">
              <div v-for="r in weekRows" :key="r.d" :class="{ today: r.today }">
                <dt>{{ r.day }}</dt>
                <dd>{{ r.text }}</dd>
              </div>
            </dl>
            <p v-else class="shop-about">{{ shop.hours }}</p>
          </div>

          <div v-if="mapLink || shop.address" class="shop-card shop-map">
            <a v-if="mapLink" :href="mapLink" target="_blank" rel="noopener" class="shop-map-box" @click="contact('map')">
              <i class="mdi mdi-map-marker-radius-outline"></i>
              <span>{{ tr('Open in Google Maps', 'افتح في خرائط جوجل') }}</span>
            </a>
            <p v-if="shop.address || place">{{ shop.address || place }}</p>
          </div>

          <div class="shop-card shop-app">
            <h2>{{ tr('Follow this shop in the Talabna app', 'تابع المتجر من تطبيق طلبنا') }}</h2>
            <p>{{ tr('Get a notification for every new product and offer.', 'يصلك إشعار عند كل منتج أو عرض جديد.') }}</p>
            <div class="shop-app-btns">
              <a :href="PLAY_URL" target="_blank" rel="noopener" class="shop-btn shop-btn-light"><i class="mdi mdi-google-play"></i>Google Play</a>
              <a v-if="APP_STORE_URL" :href="APP_STORE_URL" target="_blank" rel="noopener" class="shop-btn shop-btn-ghost"><i class="mdi mdi-apple"></i>App Store</a>
              <span v-else class="shop-btn shop-btn-ghost is-soon"><i class="mdi mdi-apple"></i>App Store · {{ comingSoon(locale) }}</span>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { apiFetch } from '@/utils/api'
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'
import { PLAY_URL, APP_STORE_URL, comingSoon } from '@/utils/stores'
import { ensureAbsoluteUrl } from '@/utils/helpers'

const route = useRoute()
const appStore = useAppStore()
const { updateMeta } = useSeo()

const locale = computed(() => appStore.locale || 'ar')
const dir = computed(() => (['ar', 'he', 'fa', 'ur', 'ps', 'ku', 'sd'].includes(locale.value) ? 'rtl' : 'ltr'))
const tr = (en, ar) => (locale.value === 'ar' ? ar : en)

const shop = ref(null)
const stats = ref({ products: 0, followers: 0, sold: 0 })
const shelves = ref([])
const featured = ref([])
const loading = ref(true)
const notFound = ref(false)

const listings = ref([])
const page = ref(1)
const lastPage = ref(1)
const postsLoading = ref(false)
const shelf = ref(0)
const q = ref('')

const key = computed(() => String(route.params.slug || ''))
const img = (src) => ensureAbsoluteUrl(src)
const initial = computed(() => (shop.value?.name || '?').trim().charAt(0))
const place = computed(() => [shop.value?.city?.name, shop.value?.country?.name].filter(Boolean).join(locale.value === 'ar' ? '، ' : ', '))

const digits = (s) => String(s || '').replace(/[^\d+]/g, '')
const whatsappLink = computed(() => {
  const n = digits(shop.value?.whatsapp).replace(/^\+/, '')
  return n.length >= 7 ? `https://wa.me/${n}` : null
})
const phoneLink = computed(() => {
  const n = digits(shop.value?.phone)
  return n.length >= 6 ? `tel:${n}` : null
})
const mapLink = computed(() => (shop.value?.lat != null && shop.value?.lng != null
  ? `https://www.google.com/maps/search/?api=1&query=${shop.value.lat},${shop.value.lng}` : null))
// The app opens talbna.cloud/shop/… links itself; on a computer this leads to the app's store page.
const appLink = computed(() => (/android/i.test(navigator.userAgent) ? window.location.href.split('?')[0] : PLAY_URL))


const dayName = (d) => {
  // 2023-01-01 was a Sunday (d = 0).
  try { return new Intl.DateTimeFormat(locale.value, { weekday: 'long' }).format(new Date(2023, 0, 1 + d)) } catch { return String(d) }
}
const toMin = (hhmm) => { const [h, m] = String(hhmm).split(':').map(Number); return h * 60 + m }

const weekRows = computed(() => {
  const week = shop.value?.week_hours || []
  if (!week.length) return []
  const today = new Date().getDay()
  return [0, 1, 2, 3, 4, 5, 6].map((d) => {
    const slots = week.filter((h) => h.d === d)
    return {
      d,
      day: dayName(d),
      today: d === today,
      text: slots.length ? slots.map((h) => `${h.open} – ${h.close}`).join(', ') : tr('Closed', 'مغلق'),
    }
  })
})

// Open now, in the visitor's local time (a close before the open time runs past midnight).
const openState = computed(() => {
  const week = shop.value?.week_hours || []
  if (!week.length) return null
  const now = new Date()
  const d = now.getDay()
  const mins = now.getHours() * 60 + now.getMinutes()
  const yesterday = (d + 6) % 7
  for (const h of week) {
    const o = toMin(h.open); const c = toMin(h.close)
    const overnight = c <= o
    if ((h.d === d && (overnight ? mins >= o : mins >= o && mins < c)) || (overnight && h.d === yesterday && mins < c)) {
      return { open: true, short: tr('Open now', 'مفتوح الآن'), label: `${tr('Open now', 'مفتوح الآن')} · ${tr('closes', 'يغلق')} ${h.close}` }
    }
  }
  const next = week.find((h) => h.d === d && toMin(h.open) > mins)
  return { open: false, short: tr('Closed now', 'مغلق الآن'), label: next ? `${tr('Closed', 'مغلق')} · ${tr('opens', 'يفتح')} ${next.open}` : tr('Closed now', 'مغلق الآن') }
})

async function loadShop() {
  loading.value = true
  notFound.value = false
  try {
    const res = await apiFetch(`/api/public/shops/${encodeURIComponent(key.value)}?web=1`)
    if (res.status === 404) { notFound.value = true; shop.value = null; return }
    if (!res.ok) return
    const data = await res.json()
    shop.value = data.shop
    stats.value = data.stats || stats.value
    shelves.value = data.shelves || []
    featured.value = data.featured || []
    updateMeta({
      title: `${data.shop.name} | ${tr('Talabna', 'طلبنا')}`,
      description: data.shop.about || data.shop.name,
      image: data.shop.cover || data.shop.logo || undefined,
      url: data.shop.url,
    })
    await loadPosts(1)
  } catch (e) {
    console.error('shop', e)
  } finally {
    loading.value = false
  }
}

async function loadPosts(p = 1) {
  postsLoading.value = true
  try {
    const params = new URLSearchParams({ web: '1', page: String(p) })
    if (shelf.value) params.set('shelf', String(shelf.value))
    if (q.value.trim()) params.set('q', q.value.trim())
    const res = await apiFetch(`/api/public/shops/${encodeURIComponent(key.value)}/posts?${params}`)
    if (!res.ok) return
    const data = await res.json()
    listings.value = p === 1 ? (data.listings || []) : [...listings.value, ...(data.listings || [])]
    page.value = data.pagination?.current_page || p
    lastPage.value = data.pagination?.last_page || 1
  } finally {
    postsLoading.value = false
  }
}

function pickShelf(id) {
  shelf.value = id
  loadPosts(1)
}

let searchTimer = null
function onSearch() {
  clearTimeout(searchTimer)
  searchTimer = setTimeout(() => loadPosts(1), 350)
}

function contact(channel) {
  apiFetch(`/api/public/shops/${encodeURIComponent(key.value)}/contact`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ channel }),
  }).catch(() => {})
}

async function share() {
  const url = shop.value?.url || window.location.href
  try {
    if (navigator.share) await navigator.share({ title: shop.value?.name, url })
    else await navigator.clipboard.writeText(url)
  } catch { /* cancelled */ }
}

onMounted(loadShop)
watch(key, loadShop)
</script>

<style scoped>
.shop-page {
  --s-ink: #16201D;
  --s-ink-2: #2E3833;
  --s-muted: #5C6560;
  --s-line: #E2E0D6;
  --s-soft: #EBEAE3;
  --s-card: #FFFFFF;
  --s-bg: #F3F2ED;
  --s-gold: #9C6B00;
  --s-gold-ink: #7A5300;
  --s-gold-soft: #EDE4D1;
  --s-green: #0B6B3A;
  --s-green-soft: #E3F1EA;
  background: var(--s-bg);
  color: var(--s-ink);
  min-height: 70vh;
  padding-bottom: 48px;
}
:global([data-theme="dark"]) .shop-page {
  --s-ink: #F1EFE6;
  --s-ink-2: #D6D4CA;
  --s-muted: #A8A99C;
  --s-line: #2E2F24;
  --s-soft: #242519;
  --s-card: #1B1C17;
  --s-bg: #12130F;
  --s-gold: #E0B24A;
  --s-gold-ink: #E0B24A;
  --s-gold-soft: #3A2F13;
  --s-green: #4FCB89;
  --s-green-soft: #123322;
}
.shop-wrap { max-width: 1180px; margin: 0 auto; padding: 24px 16px 0; }
.shop-loading, .shop-missing { min-height: 50vh; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 12px; text-align: center; }
.shop-missing i { font-size: 64px; color: var(--s-muted); }
.shop-missing h1 { font-size: 1.6rem; margin: 0; }
.shop-missing p { color: var(--s-muted); margin: 0 0 8px; }

.shop-cover { height: 240px; border-radius: 24px; overflow: hidden; background: var(--s-ink); }
.shop-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
.shop-cover.empty { background: linear-gradient(0deg, rgba(0,0,0,0.06), rgba(0,0,0,0.06)), var(--s-ink); }

.shop-head { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px 20px; padding: 0 20px; margin-top: -52px; position: relative; }
.shop-logo { width: 112px; height: 112px; border-radius: 28px; background: var(--s-card); border: 5px solid var(--s-bg); box-shadow: 0 8px 24px rgba(22,32,29,0.14); overflow: hidden; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.shop-logo img { width: 100%; height: 100%; object-fit: cover; }
.shop-logo span { font-size: 40px; font-weight: 700; color: var(--s-gold); }
.shop-title { flex: 1 1 320px; min-width: 0; padding-bottom: 4px; }
.shop-name-row { display: flex; align-items: center; gap: 8px; }
.shop-name-row h1 { margin: 0; font-size: clamp(1.5rem, 3vw, 2rem); font-weight: 700; line-height: 1.25; overflow-wrap: anywhere; }
.shop-badge { color: var(--s-gold); font-size: 24px; display: inline-flex; }
.shop-sub { margin: 4px 0 0; color: var(--s-muted); font-size: 15px; }
.shop-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.shop-chip { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: var(--s-card); border: 1px solid var(--s-line); font-size: 13px; font-weight: 600; color: var(--s-ink-2); }
.shop-chip .dot { width: 8px; height: 8px; border-radius: 4px; background: currentColor; }
.shop-chip.is-open { background: var(--s-green-soft); color: var(--s-green); border-color: transparent; }
.shop-chip.is-closed { color: var(--s-muted); }
.shop-actions { display: flex; flex-wrap: wrap; gap: 10px; padding-bottom: 4px; }

.shop-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 18px; border-radius: 12px; border: 1px solid var(--s-line); background: var(--s-card); color: var(--s-ink); font-size: 15px; font-weight: 600; text-decoration: none; cursor: pointer; font-family: inherit; }
.shop-btn i { font-size: 20px; }
.shop-btn:hover { border-color: var(--s-gold); }
.shop-btn-primary { background: var(--s-gold); border-color: var(--s-gold); color: #FFFFFF; }
:global([data-theme="dark"]) .shop-btn-primary { color: #191305; }
.shop-btn-primary:hover { filter: brightness(1.05); }
.shop-btn-icon { width: 46px; padding: 0; }
.shop-btn-light { background: #FFFFFF; color: #16201D; border-color: #FFFFFF; }
.shop-btn-ghost { background: transparent; color: #D6D4CA; border-color: #3C3D2E; }
.shop-btn.is-soon { cursor: default; font-size: 13px; }

.shop-offer { margin: 24px 0 0; display: flex; align-items: center; gap: 14px; padding: 14px 18px; border-radius: 18px; background: var(--s-gold-soft); color: var(--s-gold-ink); }
.shop-offer i { font-size: 26px; }
.shop-offer div { display: flex; flex-direction: column; }
.shop-offer strong { font-size: 15px; }
.shop-offer span { font-size: 14px; color: var(--s-ink-2); }

.shop-body { display: flex; flex-wrap: wrap; gap: 24px; margin-top: 28px; align-items: flex-start; }
.shop-main { flex: 999 1 560px; min-width: 0; }
.shop-side { flex: 1 1 300px; display: flex; flex-direction: column; gap: 16px; }

.shop-featured { margin-bottom: 28px; }
.shop-featured h2, .shop-card h2 { font-size: 1.1rem; font-weight: 700; margin: 0 0 12px; }
.shop-tools { display: flex; flex-direction: column; gap: 12px; margin-bottom: 16px; }
.shop-search { display: flex; align-items: center; gap: 8px; height: 46px; padding: 0 14px; border-radius: 12px; background: var(--s-card); border: 1px solid var(--s-line); }
.shop-search i { font-size: 20px; color: var(--s-muted); }
.shop-search input { flex: 1; border: 0; outline: 0; background: transparent; color: var(--s-ink); font-size: 15px; font-family: inherit; min-width: 0; }
.shop-shelves { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 2px; scrollbar-width: none; }
.shop-shelves button { flex-shrink: 0; height: 38px; padding: 0 14px; border-radius: 999px; border: 1px solid var(--s-line); background: var(--s-card); color: var(--s-ink-2); font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; white-space: nowrap; }
.shop-shelves button span { color: var(--s-muted); font-weight: 500; margin-inline-start: 4px; }
.shop-shelves button.on { background: var(--s-ink); border-color: var(--s-ink); color: var(--s-bg); }
.shop-shelves button.on span { color: inherit; opacity: 0.75; }
.shop-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px; }
.shop-empty { padding: 48px 0; text-align: center; color: var(--s-muted); }
.shop-more { display: flex; justify-content: center; margin-top: 24px; }

.shop-card { background: var(--s-card); border-radius: 20px; padding: 20px; border: 1px solid var(--s-line); }
.shop-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px; }
.shop-card-head h2 { margin: 0; }
.shop-about { margin: 0; font-size: 14px; line-height: 1.8; color: var(--s-ink-2); white-space: pre-line; }
.txt-open { color: var(--s-green); font-weight: 700; font-size: 13px; }
.txt-closed { color: var(--s-muted); font-weight: 700; font-size: 13px; }
.shop-hours { margin: 0; display: flex; flex-direction: column; gap: 6px; font-size: 14px; }
.shop-hours div { display: flex; justify-content: space-between; gap: 12px; padding: 2px 0; }
.shop-hours dt { margin: 0; }
.shop-hours dd { margin: 0; color: var(--s-muted); }
.shop-hours .today { font-weight: 700; }
.shop-hours .today dd { color: var(--s-ink); }
.shop-map { padding: 0; overflow: hidden; }
.shop-map-box { height: 140px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; background: var(--s-soft); color: var(--s-ink); text-decoration: none; font-weight: 600; }
.shop-map-box i { font-size: 36px; color: var(--s-gold); }
.shop-map p { margin: 0; padding: 14px 20px; font-size: 14px; }
.shop-app { background: #16201D; border-color: #16201D; color: #F1EFE6; }
.shop-app p { margin: 0 0 12px; color: #A8A99C; font-size: 14px; }
.shop-app-btns { display: flex; flex-wrap: wrap; gap: 8px; }
.sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

@media (max-width: 640px) {
  .shop-wrap { padding: 12px 12px 0; }
  .shop-cover { height: 160px; border-radius: 18px; }
  .shop-head { padding: 0 8px; margin-top: -44px; }
  .shop-logo { width: 88px; height: 88px; border-radius: 22px; }
  .shop-actions { width: 100%; }
  .shop-actions .shop-btn:not(.shop-btn-icon) { flex: 1 1 auto; }
  .shop-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
}
</style>
