<template>
  <router-link
    :to="`/listing/${listing.id}/${slugify(listing.title)}`"
    class="listing-card card card-hover h-100"
  >
    <!-- Image/Video Thumbnail -->
    <div class="listing-image-container">
      <!-- Video thumbnail -->
      <video
        v-if="isFirstMediaVideo"
        :src="mainPhoto"
        class="listing-image"
        style="width: 100%; height: 200px; object-fit: cover;"
        preload="metadata"
        muted
      />
      <!-- No Image Placeholder -->
      <div
        v-else-if="!mainPhoto"
        class="no-media-placeholder d-flex flex-column align-center justify-center"
        style="height: 200px;"
      >
        <i class="mdi mdi-image-multiple-outline" style="font-size: 56px; color: var(--color-text-muted); opacity: 0.4;"></i>
        <span class="text-caption text-muted mt-2">{{ locale === 'ar' ? 'لا توجد صور' : 'No media' }}</span>
      </div>
      <!-- Image -->
      <img
        v-else
        :src="mainPhoto"
        :alt="listing.title"
        loading="lazy"
        class="listing-image img-cover"
        style="height: 200px;"
        @error="$event.target.style.display='none'"
      />

      <!-- Badge -->
      <span v-if="showBadge" class="listing-badge chip chip-sm" :style="{ background: badgeColor, color: '#fff' }">
        <i class="mdi" :class="badgeIcon" style="font-size: 14px;"></i>
        {{ badgeName }}
      </span>

      <!-- Video Play Icon -->
      <div v-if="isFirstMediaVideo" class="video-indicator">
        <i class="mdi mdi-play-circle" style="font-size: 48px; color: #fff;"></i>
      </div>

      <!-- Favorite Button -->
      <button
        class="favorite-btn"
        @click.prevent="toggleFavorite"
      >
        <i class="mdi" :class="isFavorite ? 'mdi-heart' : 'mdi-heart-outline'" :style="{ color: isFavorite ? '#EF4444' : '#9CA3AF' }"></i>
      </button>
    </div>

    <!-- Content -->
    <div class="card-body pa-4">
      <!-- Category -->
      <div class="d-flex align-center gap-2 mb-2">
        <span class="chip chip-xs chip-primary">
          {{ getCategoryName(listing.category) }}
        </span>
        <span v-if="listing.sub_category" class="chip chip-xs">
          {{ getCategoryName(listing.sub_category) }}
        </span>
      </div>

      <!-- Title -->
      <h3 class="listing-title text-subtitle-1 font-weight-bold mb-2">
        {{ listing.title }}
      </h3>

      <!-- Location -->
      <div v-if="listing.city || listing.country" class="d-flex align-center text-caption text-muted mb-2">
        <i class="mdi mdi-map-marker mr-1" style="font-size: 14px;"></i>
        {{ locationText }}
      </div>

      <!-- Price -->
      <div class="d-flex justify-between align-center">
        <span v-if="listing.price" class="text-h6 font-weight-bold text-primary">
          {{ formatPrice(listing.price) }}
        </span>
        <span v-else class="text-body-2 text-muted">
          {{ locale === 'ar' ? 'السعر عند الاتصال' : 'Contact for price' }}
        </span>

        <!-- Stats -->
        <div class="d-flex align-center gap-2 text-caption text-muted">
          <span class="d-flex align-center">
            <i class="mdi mdi-eye mr-1" style="font-size: 14px;"></i>
            {{ formatNumber(listing.view_count || 0) }}
          </span>
          <span class="d-flex align-center">
            <i class="mdi mdi-heart mr-1" style="font-size: 14px;"></i>
            {{ listing.favorites_count || 0 }}
          </span>
        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="card-footer pa-4 pt-3 pb-3">
      <div class="avatar avatar-28 mr-2">
        <img v-if="userPhoto" :src="userPhoto" />
        <i v-else class="mdi mdi-account" style="font-size: 16px;"></i>
      </div>
      <span class="text-caption text-muted">{{ listing.user?.name || 'User' }}</span>
      <div class="flex-1"></div>
      <span class="text-caption text-muted">{{ timeAgo }}</span>
    </div>
  </router-link>
</template>

<script setup>
import { computed, ref } from 'vue'
import { getLocalizedName, ensureAbsoluteUrl, isVideo, formatPrice as fmtPrice, formatNumber, slugify, timeAgo as calcTimeAgo, getCurrencyFromListing } from '@/utils/helpers'

const props = defineProps({
  listing: { type: Object, required: true },
  locale: { type: String, default: 'ar' },
})

const isFavorite = ref(false)

const getCategoryName = (item) => {
  if (!item) return ''
  if (item.name && typeof item.name === 'object') {
    return getLocalizedName(item.name, props.locale)
  }
  return props.locale === 'ar' ? item.name : (item.name_en || item.name || '')
}

const getLocationName = (item) => getCategoryName(item)

const isVideoFile = (src) => isVideo(src)

const mainPhoto = computed(() => {
  if (props.listing.photos && props.listing.photos.length > 0) {
    const src = props.listing.photos[0].src || props.listing.photos[0].url
    return ensureAbsoluteUrl(src)
  }
  return ''
})

const isFirstMediaVideo = computed(() => {
  if (props.listing.photos && props.listing.photos.length > 0) {
    const photo = props.listing.photos[0]
    return photo.isVideo || isVideoFile(photo.src)
  }
  return false
})

const userPhoto = computed(() => {
  if (props.listing.user?.photos?.[0]?.src) {
    return ensureAbsoluteUrl(props.listing.user.photos[0].src)
  }
  return null
})

// Badge computed properties - supports both new badge object and legacy have_badge
const showBadge = computed(() => {
  if (props.listing.badge) {
    return !props.listing.badge.is_default
  }
  return props.listing.have_badge && props.listing.have_badge !== 'عادي'
})

const badgeName = computed(() => {
  if (props.listing.badge) {
    return props.locale === 'ar' ? props.listing.badge.name_ar : props.listing.badge.name_en
  }
  return props.listing.have_badge
})

const badgeColor = computed(() => {
  if (props.listing.badge && props.listing.badge.color) {
    return props.listing.badge.color
  }
  switch (props.listing.have_badge) {
    case 'ماسي': return '#9333ea'
    case 'ذهبي': return '#f59e0b'
    case 'فضي': return '#9ca3af'
    default: return '#6b7280'
  }
})

const badgeIcon = computed(() => {
  if (props.listing.badge && props.listing.badge.icon) {
    return props.listing.badge.icon
  }
  switch (props.listing.have_badge) {
    case 'ماسي': return 'mdi-diamond-stone'
    case 'ذهبي': return 'mdi-star'
    case 'فضي': return 'mdi-medal'
    default: return 'mdi-tag'
  }
})

const locationText = computed(() => {
  const parts = []
  if (props.listing.city) parts.push(getLocationName(props.listing.city))
  if (props.listing.country) parts.push(getLocationName(props.listing.country))
  return parts.filter(p => p).join(', ')
})

const timeAgo = computed(() => calcTimeAgo(props.listing.created_at, props.locale))

const formatPrice = (price) => fmtPrice(price, props.locale, getCurrencyFromListing(props.listing))

const toggleFavorite = async () => {
  isFavorite.value = !isFavorite.value
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    await fetch(`/api/doFavourite/${props.listing.id}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
    })
  } catch (e) {
    isFavorite.value = !isFavorite.value
  }
}
</script>

<style scoped>
.listing-card {
  cursor: pointer;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  border-radius: 20px !important;
  overflow: hidden;
  background: rgb(var(--v-theme-surface));
  border: 1px solid rgba(var(--v-theme-on-surface), 0.08);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.listing-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.listing-image-container {
  position: relative;
  overflow: hidden;
}

.listing-image {
  transition: transform 0.3s ease;
}

.listing-card:hover .listing-image {
  transform: scale(1.05);
}

.listing-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.favorite-btn {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 2;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  border: none;
  display: flex;
  align-items: center;
  justify-content: center;
  backdrop-filter: blur(10px);
  background: rgba(255, 255, 255, 0.9) !important;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
  cursor: pointer;
  font-size: 18px;
}

.video-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(0, 0, 0, 0.6);
  border-radius: 50%;
  padding: 12px;
  backdrop-filter: blur(5px);
}

.listing-title {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.5;
  min-height: 3em;
}

.no-media-placeholder {
  background: linear-gradient(135deg, rgb(var(--v-theme-surface-variant)) 0%, rgb(var(--v-theme-surface-bright)) 100%);
  border-bottom: 1px solid rgba(var(--v-theme-on-surface), 0.08);
}
</style>
