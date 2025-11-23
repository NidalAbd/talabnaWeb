<template>
  <v-card
    class="listing-card h-100"
    variant="outlined"
    :to="`/listing/${listing.id}/${slugify(listing.title)}`"
  >
    <!-- Image -->
    <div class="listing-image-container">
      <v-img
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
          {{ locale === 'ar' ? listing.category?.name : listing.category?.name_en }}
        </v-chip>
        <v-chip v-if="listing.sub_category" size="x-small" variant="tonal">
          {{ locale === 'ar' ? listing.sub_category?.name : listing.sub_category?.name_en }}
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
        <v-img v-if="listing.user?.photos?.[0]" :src="listing.user.photos[0].src" />
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

const mainPhoto = computed(() => {
  if (props.listing.photos && props.listing.photos.length > 0) {
    return props.listing.photos[0].src || props.listing.photos[0].url
  }
  return '/storage/photos/placeholder.jpg'
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
  if (props.listing.city?.name) parts.push(props.listing.city.name)
  if (props.listing.country?.name) parts.push(props.listing.country.name)
  return parts.join(', ')
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

.listing-title {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}
</style>
