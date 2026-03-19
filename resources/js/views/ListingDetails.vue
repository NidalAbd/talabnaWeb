<template>
  <div class="listing-details-page">
    <!-- Breadcrumbs -->
    <div class="container py-4">
      <nav class="breadcrumbs px-0">
        <template v-for="(item, index) in breadcrumbs" :key="index">
          <router-link v-if="!item.disabled" :to="item.to" class="breadcrumb-link">{{ item.title }}</router-link>
          <span v-else class="breadcrumb-current">{{ item.title }}</span>
          <i v-if="index < breadcrumbs.length - 1" class="mdi mdi-chevron-left breadcrumb-divider"></i>
        </template>
      </nav>
    </div>

    <div v-if="listing && !loading" class="container pb-12">
      <div class="row">
        <!-- Main Content -->
        <div class="col-12 col-lg-8">
          <!-- Image Gallery -->
          <div class="card mb-6">
            <div
              v-if="listing.photos && listing.photos.length > 0"
              class="carousel"
              style="height: 450px; overflow: hidden; position: relative;"
            >
              <div
                class="carousel-track"
                :style="{ transform: `translateX(-${currentSlide * 100}%)`, display: 'flex', transition: 'transform 0.3s ease', height: '100%' }"
              >
                <div
                  v-for="(photo, i) in listing.photos"
                  :key="i"
                  class="carousel-slide"
                  style="min-width: 100%; height: 100%;"
                >
                  <!-- Video -->
                  <video
                    v-if="photo.isVideo || isVideoFile(photo.src)"
                    :src="getPhotoUrl(photo)"
                    controls
                    class="w-100 h-100"
                    style="object-fit: cover;"
                  />
                  <!-- Image -->
                  <img
                    v-else
                    :src="getPhotoUrl(photo)"
                    :loading="i === 0 ? 'eager' : 'lazy'"
                    :fetchpriority="i === 0 ? 'high' : 'auto'"
                    decoding="async"
                    width="800"
                    height="450"
                    class="img-cover"
                    style="width: 100%; height: 450px; object-fit: cover;"
                  />
                </div>
              </div>
              <!-- Prev/Next buttons -->
              <button v-if="currentSlide > 0" class="carousel-btn carousel-btn-prev" @click="prevSlide">
                <i class="mdi mdi-chevron-left"></i>
              </button>
              <button v-if="currentSlide < totalSlides - 1" class="carousel-btn carousel-btn-next" @click="nextSlide">
                <i class="mdi mdi-chevron-right"></i>
              </button>
              <!-- Dot indicators -->
              <div class="carousel-dots">
                <span
                  v-for="(_, i) in listing.photos"
                  :key="i"
                  class="carousel-dot"
                  :class="{ active: i === currentSlide }"
                  @click="currentSlide = i"
                ></span>
              </div>
            </div>
            <div v-else class="d-flex align-center justify-center bg-grey-lighten-3" style="height: 450px;">
              <i class="mdi mdi-image-off" style="font-size: 100px; color: grey;"></i>
            </div>
          </div>

          <!-- Listing Info -->
          <div class="card mb-6">
            <div class="card-body pa-6">
              <!-- Badges -->
              <div class="d-flex flex-wrap gap-2 mb-4">
                <span v-if="showBadge" class="chip" :style="{ backgroundColor: badgeColor, color: '#fff' }">
                  <i :class="badgeIcon" style="margin-inline-end: 4px;"></i>
                  {{ badgeName }}
                </span>
                <span class="chip chip-primary-tonal">
                  {{ getLocalizedName(listing.category) }}
                </span>
                <span v-if="listing.sub_category" class="chip chip-tonal">
                  {{ getLocalizedName(listing.sub_category) }}
                </span>
              </div>

              <!-- Title -->
              <h1 class="text-h4 font-weight-bold mb-4">{{ listing.title }}</h1>

              <!-- Meta -->
              <div class="d-flex flex-wrap gap-4 text-body-2 text-medium-emphasis mb-6">
                <span class="d-flex align-center">
                  <i class="mdi mdi-map-marker" style="font-size: 18px; margin-inline-end: 4px;"></i>
                  {{ locationText }}
                </span>
                <span class="d-flex align-center">
                  <i class="mdi mdi-clock-outline" style="font-size: 18px; margin-inline-end: 4px;"></i>
                  {{ formatDate(listing.created_at) }}
                </span>
                <span class="d-flex align-center">
                  <i class="mdi mdi-eye" style="font-size: 18px; margin-inline-end: 4px;"></i>
                  {{ formatNumber(listing.view_count) }} {{ t('listing.views') }}
                </span>
              </div>

              <hr class="my-6" />

              <!-- Description -->
              <h2 class="text-h6 font-weight-bold mb-4">
                {{ t('listing.description') }}
              </h2>
              <p class="text-body-1 listing-description" style="white-space: pre-line">
                {{ listing.description }}
              </p>
            </div>
          </div>

          <!-- Related Listings -->
          <div v-if="related.length > 0">
            <h2 class="text-h5 font-weight-bold mb-4">
              {{ t('listing.related') }}
            </h2>
            <div class="row">
              <div v-for="item in related" :key="item.id" class="col-12 col-sm-6">
                <listing-card :listing="item" :locale="locale" />
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="col-12 col-lg-4">
          <!-- Price Card -->
          <div class="card mb-6 sticky-sidebar">
            <div class="card-body pa-6">
              <div class="text-center mb-6">
                <div v-if="listing.price" class="text-h3 font-weight-bold text-primary">
                  {{ formatPrice(listing.price, locale, getCurrencyFromListing(listing)) }}
                </div>
                <div v-else class="text-h5 text-medium-emphasis">
                  {{ t('listing.price_contact') }}
                </div>
              </div>

              <hr class="mb-6" />

              <!-- Seller Info -->
              <div class="d-flex align-center mb-6">
                <div class="avatar mr-4" style="width: 56px; height: 56px;">
                  <img v-if="userPhotoUrl" :src="userPhotoUrl" loading="lazy" decoding="async" width="56" height="56" class="img-cover" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;" />
                  <i v-else class="mdi mdi-account" style="font-size: 32px;"></i>
                </div>
                <div>
                  <h3 class="text-subtitle-1 font-weight-bold">{{ listing.user?.name }}</h3>
                  <p class="text-caption text-medium-emphasis">
                    {{ t('listing.member_since') }}
                    {{ formatDate(listing.user?.created_at, true) }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <a class="btn btn-success btn-lg btn-block mb-3" :href="`tel:${listing.phone}`">
                <i class="mdi mdi-phone" style="margin-inline-end: 4px;"></i>
                {{ t('listing.call_now') }}
              </a>

              <a class="btn btn-outline-primary btn-lg btn-block mb-3" :href="`https://wa.me/${listing.whatsapp || listing.phone}`" target="_blank">
                <i class="mdi mdi-whatsapp" style="margin-inline-end: 4px;"></i>
                {{ t('listing.whatsapp') }}
              </a>

              <button class="btn btn-text btn-lg btn-block" @click="toggleFavorite">
                <i class="mdi" :class="isFavorite ? 'mdi-heart' : 'mdi-heart-outline'" :style="{ color: isFavorite ? 'red' : '', marginInlineEnd: '4px' }"></i>
                {{ t('listing.save') }}
              </button>
            </div>
          </div>

          <!-- Share -->
          <div class="card">
            <div class="card-body pa-4">
              <h3 class="text-subtitle-1 font-weight-bold mb-3">
                {{ t('listing.share_listing') }}
              </h3>
              <div class="d-flex gap-2">
                <a class="btn btn-icon" style="background: rgba(59,130,246,0.1); color: #3b82f6;" :href="`https://facebook.com/sharer/sharer.php?u=${shareUrl}`" target="_blank">
                  <i class="mdi mdi-facebook"></i>
                </a>
                <a class="btn btn-icon" style="background: rgba(14,165,233,0.1); color: #0ea5e9;" :href="`https://twitter.com/intent/tweet?url=${shareUrl}&text=${listing.title}`" target="_blank">
                  <i class="mdi mdi-twitter"></i>
                </a>
                <a class="btn btn-icon" style="background: rgba(34,197,94,0.1); color: #22c55e;" :href="`https://wa.me/?text=${listing.title} ${shareUrl}`" target="_blank">
                  <i class="mdi mdi-whatsapp"></i>
                </a>
                <button class="btn btn-icon" style="background: rgba(107,114,128,0.1); color: #6b7280;" @click="copyLink">
                  <i class="mdi mdi-link"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-else-if="loading" class="container py-12">
      <div class="row">
        <div class="col-12 col-lg-8">
          <div class="skeleton mb-6" style="height: 450px;"></div>
          <div class="skeleton" style="height: 200px;"></div>
        </div>
        <div class="col-12 col-lg-4">
          <div class="skeleton" style="height: 400px;"></div>
        </div>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else class="container py-16 text-center">
      <i class="mdi mdi-alert-circle" style="font-size: 100px; color: var(--color-error, #ef4444);"></i>
      <h2 class="text-h4 mt-6 mb-4">{{ t('listing.not_found') }}</h2>
      <router-link to="/browse" class="btn btn-primary">{{ t('browse.title') }}</router-link>
    </div>

    <!-- Snackbar -->
    <div class="snackbar" :class="{ show: snackbar }">{{ snackbarText }}</div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAdvancedSeo } from '@/composables/useAdvancedSeo'
import ListingCard from '@/components/ListingCard.vue'
import { t } from '@/utils/translate'

const route = useRoute()
const appStore = useAppStore()
const { setListingSeo } = useAdvancedSeo()

const listing = ref(null)
const related = ref([])
const loading = ref(true)
const isFavorite = ref(false)
const snackbar = ref(false)
const snackbarText = ref('')

const locale = computed(() => appStore.locale)

import { ensureAbsoluteUrl, isVideo as isVideoFile, getLocalizedName as _gln, formatNumber as _fn, formatPrice, getCurrencyFromListing } from '@/utils/helpers'

const getPhotoUrl = (photo) => ensureAbsoluteUrl(photo?.src || photo?.url)

const userPhotoUrl = computed(() => {
  if (listing.value?.user?.photos?.[0]) {
    return ensureAbsoluteUrl(listing.value.user.photos[0].src)
  }
  return null
})

const getLocalizedName = (item) => {
  if (!item) return ''
  if (item.name_localized) return item.name_localized
  if (item.name && typeof item.name === 'object') return _gln(item.name, locale.value)
  return item.name_en || item.name || ''
}

const shareUrl = computed(() => window.location.href)

// Carousel controls
const currentSlide = ref(0)
const totalSlides = computed(() => listing.value?.photos?.length || 0)
const nextSlide = () => { if (currentSlide.value < totalSlides.value - 1) currentSlide.value++ }
const prevSlide = () => { if (currentSlide.value > 0) currentSlide.value-- }

// Snackbar helper
const showSnackbar = (text) => {
  snackbarText.value = text
  snackbar.value = true
  setTimeout(() => { snackbar.value = false }, 2000)
}

// Badge computed properties - supports both new badge object and legacy have_badge
const showBadge = computed(() => {
  if (!listing.value) return false
  // Check new badge object first
  if (listing.value.badge) {
    return !listing.value.badge.is_default
  }
  // Fallback to legacy have_badge
  return listing.value.have_badge && listing.value.have_badge !== 'عادي'
})

const badgeName = computed(() => {
  if (!listing.value) return ''
  // Use new badge object if available
  if (listing.value.badge) {
    return listing.value.badge.name_localized || listing.value.badge.name_en || listing.value.badge.name_ar
  }
  // Fallback to legacy
  return listing.value.have_badge
})

const badgeColor = computed(() => {
  if (!listing.value) return 'grey'
  // Use new badge object color if available
  if (listing.value.badge && listing.value.badge.color) {
    return listing.value.badge.color
  }
  // Fallback to legacy color mapping
  return listing.value.have_badge === 'ماسي' ? 'purple' : 'amber'
})

const badgeIcon = computed(() => {
  if (!listing.value) return 'mdi-tag'
  // Use new badge object icon if available
  if (listing.value.badge && listing.value.badge.icon) {
    return listing.value.badge.icon
  }
  // Fallback to legacy icon mapping
  return listing.value.have_badge === 'ماسي' ? 'mdi-diamond-stone' : 'mdi-star'
})

const locationText = computed(() => {
  if (!listing.value) return ''
  const parts = []
  if (listing.value.city) parts.push(getLocalizedName(listing.value.city))
  if (listing.value.country) parts.push(getLocalizedName(listing.value.country))
  return parts.join(', ')
})

const breadcrumbs = computed(() => {
  if (!listing.value) return []
  return [
    { title: t('nav.home'), to: '/' },
    { title: getLocalizedName(listing.value.category), to: `/category/${listing.value.category?.id}` },
    { title: listing.value.title, disabled: true },
  ]
})

const formatNumber = (num) => _fn(num || 0)

const formatDate = (date, yearOnly = false) => {
  if (!date) return ''
  const d = new Date(date)
  if (yearOnly) return d.getFullYear()
  return d.toLocaleDateString(locale.value === 'ar' ? 'ar-SA' : 'en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value
  showSnackbar(isFavorite.value ? t('listing.saved') : t('listing.removed'))
}

const copyLink = () => {
  navigator.clipboard.writeText(window.location.href)
  showSnackbar(t('listing.link_copied'))
}

const fetchListing = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/public/listings/${route.params.id}`)
    if (!response.ok) {
      listing.value = null
      return
    }
    const data = await response.json()
    listing.value = data.listing
    related.value = Array.isArray(data.related) ? data.related : []

    // Reset carousel when listing changes
    currentSlide.value = 0

    // Update SEO using advanced SEO composable
    if (listing.value) {
      setListingSeo(listing.value, locale.value)
    }
  } catch (error) {
    console.error('Error:', error)
    listing.value = null
  } finally {
    loading.value = false
  }
}

onMounted(fetchListing)
watch(() => route.params.id, fetchListing)
</script>

<style scoped>
.sticky-sidebar {
  position: sticky;
  top: 80px;
}

.listing-description {
  line-height: 1.8;
}

/* Carousel */
.carousel-btn {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: #fff;
  border: none;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  z-index: 2;
  transition: background 0.2s;
}
.carousel-btn:hover {
  background: rgba(0, 0, 0, 0.7);
}
.carousel-btn-prev {
  left: 12px;
}
.carousel-btn-next {
  right: 12px;
}
.carousel-dots {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 6px;
  z-index: 2;
}
.carousel-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.5);
  cursor: pointer;
  transition: background 0.2s;
}
.carousel-dot.active {
  background: #fff;
}

/* Breadcrumbs */
.breadcrumbs {
  display: flex;
  align-items: center;
  gap: 4px;
  flex-wrap: wrap;
}
.breadcrumb-link {
  color: var(--color-primary, #1976d2);
  text-decoration: none;
}
.breadcrumb-link:hover {
  text-decoration: underline;
}
.breadcrumb-current {
  color: var(--text-medium-emphasis, rgba(0, 0, 0, 0.6));
}
.breadcrumb-divider {
  font-size: 18px;
  color: var(--text-medium-emphasis, rgba(0, 0, 0, 0.6));
}

/* Snackbar */
.snackbar {
  position: fixed;
  bottom: 24px;
  left: 50%;
  transform: translateX(-50%) translateY(100px);
  background: #323232;
  color: #fff;
  padding: 12px 24px;
  border-radius: 8px;
  z-index: 9999;
  opacity: 0;
  transition: transform 0.3s ease, opacity 0.3s ease;
  pointer-events: none;
}
.snackbar.show {
  transform: translateX(-50%) translateY(0);
  opacity: 1;
  pointer-events: auto;
}

/* Skeleton */
.skeleton {
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: skeleton-loading 1.5s infinite;
  border-radius: 8px;
}
@keyframes skeleton-loading {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* Avatar */
.avatar {
  border-radius: 50%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e0e0e0;
  flex-shrink: 0;
}
</style>
