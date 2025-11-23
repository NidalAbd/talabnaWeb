<template>
  <v-card
    class="listing-card h-100"
    variant="outlined"
    :to="`/listing/${listing.id}/${slugify(listing.title)}`"
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
        <v-icon size="56" color="grey-lighten-1">mdi-image-multiple-outline</v-icon>
        <span class="text-caption text-grey mt-2">{{ locale === 'ar' ? 'لا توجد صور' : 'No media' }}</span>
      </div>
      <!-- Image -->
      <v-img
        v-else
        :src="mainPhoto"
        :alt="listing.title"
        height="200"
        cover
        class="listing-image"
      >
        <template v-slot:placeholder>
          <v-row class="fill-height ma-0" align="center" justify="center">
            <v-progress-circular indeterminate color="grey-lighten-3" />
          </v-row>
        </template>
        <template v-slot:error>
          <v-row class="fill-height ma-0 bg-grey-lighten-3" align="center" justify="center">
            <v-icon size="48" color="grey">mdi-image-off</v-icon>
          </v-row>
        </template>
      </v-img>

      <!-- Badge -->
      <v-chip
        v-if="listing.have_badge && listing.have_badge !== 'عادي'"
        :color="badgeColor"
        size="small"
        class="listing-badge"
      >
        <v-icon start size="14">{{ badgeIcon }}</v-icon>
        {{ listing.have_badge }}
      </v-chip>

      <!-- Video Play Icon -->
      <div v-if="isFirstMediaVideo" class="video-indicator">
        <v-icon size="48" color="white">mdi-play-circle</v-icon>
      </div>

      <!-- Favorite Button -->
      <v-btn
        icon
        size="small"
        variant="flat"
        color="white"
        class="favorite-btn"
        @click.prevent="toggleFavorite"
      >
        <v-icon :color="isFavorite ? 'red' : 'grey'">
          {{ isFavorite ? 'mdi-heart' : 'mdi-heart-outline' }}
        </v-icon>
      </v-btn>
    </div>

    <!-- Content -->
    <v-card-text class="pa-4">
      <!-- Category -->
      <div class="d-flex align-center gap-2 mb-2">
        <v-chip size="x-small" variant="tonal" color="primary">
          {{ getCategoryName(listing.category) }}
        </v-chip>
        <v-chip v-if="listing.sub_category" size="x-small" variant="tonal">
          {{ getCategoryName(listing.sub_category) }}
        </v-chip>
      </div>

      <!-- Title -->
      <h3 class="listing-title text-subtitle-1 font-weight-bold mb-2">
        {{ listing.title }}
      </h3>

      <!-- Location -->
      <div v-if="listing.city || listing.country" class="d-flex align-center text-caption text-medium-emphasis mb-2">
        <v-icon size="14" class="mr-1">mdi-map-marker</v-icon>
        {{ locationText }}
      </div>

      <!-- Price -->
      <div class="d-flex justify-space-between align-center">
        <span v-if="listing.price" class="text-h6 font-weight-bold text-primary">
          {{ formatPrice(listing.price) }}
        </span>
        <span v-else class="text-body-2 text-medium-emphasis">
          {{ locale === 'ar' ? 'السعر عند الاتصال' : 'Contact for price' }}
        </span>

        <!-- Stats -->
        <div class="d-flex align-center gap-2 text-caption text-medium-emphasis">
          <span class="d-flex align-center">
            <v-icon size="14" class="mr-1">mdi-eye</v-icon>
            {{ formatNumber(listing.view_count || 0) }}
          </span>
          <span class="d-flex align-center">
            <v-icon size="14" class="mr-1">mdi-heart</v-icon>
            {{ listing.favorites_count || 0 }}
          </span>
        </div>
      </div>
    </v-card-text>

    <!-- Footer -->
    <v-divider />
    <v-card-actions class="pa-4 pt-3 pb-3">
      <v-avatar size="28" class="mr-2">
        <v-img v-if="userPhoto" :src="userPhoto" />
        <v-icon v-else>mdi-account</v-icon>
      </v-avatar>
      <span class="text-caption text-medium-emphasis">{{ listing.user?.name || 'User' }}</span>
      <v-spacer />
      <span class="text-caption text-medium-emphasis">{{ timeAgo }}</span>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  listing: {
    type: Object,
    required: true,
  },
  locale: {
    type: String,
    default: 'ar',
  },
})

const isFavorite = ref(false)

// Get localized name from category/subcategory object
const getCategoryName = (item) => {
  if (!item) return ''
  // If name is an object with ar/en keys
  if (item.name && typeof item.name === 'object') {
    return props.locale === 'ar' ? (item.name.ar || item.name.en || '') : (item.name.en || item.name.ar || '')
  }
  // If name is a string directly
  if (typeof item.name === 'string') {
    return props.locale === 'ar' ? item.name : (item.name_en || item.name)
  }
  return ''
}

// Get localized location name
const getLocationName = (item) => {
  if (!item) return ''
  if (item.name && typeof item.name === 'object') {
    return props.locale === 'ar' ? (item.name.ar || item.name.en || '') : (item.name.en || item.name.ar || '')
  }
  if (typeof item.name === 'string') {
    return props.locale === 'ar' ? item.name : (item.name_en || item.name)
  }
  return ''
}

// Ensure URL starts with / for absolute path
const ensureAbsoluteUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
    return url
  }
  // Handle paths stored as 'photos/posts/...' (from public disk) - need /storage prefix
  if (url.startsWith('photos/')) {
    return '/storage/' + url
  }
  // Handle paths stored as 'storage/photos/...' - just add leading slash
  return '/' + url
}

// Check if file is a video by extension
const isVideoFile = (src) => {
  if (!src) return false
  const videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.mkv']
  const lowerSrc = src.toLowerCase()
  return videoExtensions.some(ext => lowerSrc.endsWith(ext))
}

const mainPhoto = computed(() => {
  if (props.listing.photos && props.listing.photos.length > 0) {
    const src = props.listing.photos[0].src || props.listing.photos[0].url
    return ensureAbsoluteUrl(src)
  }
  // Return empty - v-img will show error slot
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

const badgeColor = computed(() => {
  switch (props.listing.have_badge) {
    case 'ماسي': return 'purple'
    case 'ذهبي': return 'amber'
    default: return 'grey'
  }
})

const badgeIcon = computed(() => {
  switch (props.listing.have_badge) {
    case 'ماسي': return 'mdi-diamond-stone'
    case 'ذهبي': return 'mdi-star'
    default: return 'mdi-tag'
  }
})

const locationText = computed(() => {
  const parts = []
  if (props.listing.city) parts.push(getLocationName(props.listing.city))
  if (props.listing.country) parts.push(getLocationName(props.listing.country))
  return parts.filter(p => p).join(', ')
})

const timeAgo = computed(() => {
  if (!props.listing.created_at) return ''

  const now = new Date()
  const created = new Date(props.listing.created_at)
  const diffMs = now - created
  const diffMins = Math.floor(diffMs / 60000)
  const diffHours = Math.floor(diffMs / 3600000)
  const diffDays = Math.floor(diffMs / 86400000)

  if (props.locale === 'ar') {
    if (diffMins < 60) return `منذ ${diffMins} دقيقة`
    if (diffHours < 24) return `منذ ${diffHours} ساعة`
    if (diffDays < 30) return `منذ ${diffDays} يوم`
    return created.toLocaleDateString('ar-SA')
  } else {
    if (diffMins < 60) return `${diffMins}m ago`
    if (diffHours < 24) return `${diffHours}h ago`
    if (diffDays < 30) return `${diffDays}d ago`
    return created.toLocaleDateString('en-US')
  }
})

const slugify = (text) => {
  if (!text) return ''
  return text
    .toString()
    .toLowerCase()
    .trim()
    .replace(/\s+/g, '-')
    .replace(/[^\w\-]+/g, '')
    .replace(/\-\-+/g, '-')
    .substring(0, 50)
}

const formatPrice = (price) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
  }).format(price)
}

const formatNumber = (num) => {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M'
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K'
  return num.toString()
}

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value
  // TODO: Call API to toggle favorite
}
</script>

<style scoped>
.listing-card {
  cursor: pointer;
  transition: all 0.3s ease;
  border-radius: 16px !important;
  overflow: hidden;
}

.listing-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
  border-color: rgb(var(--v-theme-primary)) !important;
}

.listing-image-container {
  position: relative;
}

.listing-badge {
  position: absolute;
  top: 12px;
  left: 12px;
}

.favorite-btn {
  position: absolute;
  top: 12px;
  right: 12px;
}

.video-indicator {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(0, 0, 0, 0.5);
  border-radius: 50%;
  padding: 8px;
}

.listing-title {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.no-media-placeholder {
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%);
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
}
</style>
