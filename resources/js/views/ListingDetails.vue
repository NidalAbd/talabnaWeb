<template>
  <div class="listing-details-page">
    <!-- Breadcrumbs -->
    <v-container class="py-4">
      <v-breadcrumbs :items="breadcrumbs" class="px-0">
        <template v-slot:divider>
          <v-icon>mdi-chevron-left</v-icon>
        </template>
      </v-breadcrumbs>
    </v-container>

    <v-container v-if="listing && !loading" class="pb-12">
      <v-row>
        <!-- Main Content -->
        <v-col cols="12" lg="8">
          <!-- Image Gallery -->
          <v-card class="mb-6" variant="outlined">
            <v-carousel
              v-if="listing.photos && listing.photos.length > 0"
              height="450"
              show-arrows="hover"
              hide-delimiter-background
            >
              <v-carousel-item
                v-for="(photo, i) in listing.photos"
                :key="i"
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
                <v-img
                  v-else
                  :src="getPhotoUrl(photo)"
                  height="450"
                  cover
                />
              </v-carousel-item>
            </v-carousel>
            <div v-else class="d-flex align-center justify-center bg-grey-lighten-3" style="height: 450px;">
              <v-icon size="100" color="grey">mdi-image-off</v-icon>
            </div>
          </v-card>

          <!-- Listing Info -->
          <v-card class="mb-6" variant="outlined">
            <v-card-text class="pa-6">
              <!-- Badges -->
              <div class="d-flex flex-wrap gap-2 mb-4">
                <v-chip v-if="showBadge" :color="badgeColor" variant="flat">
                  <v-icon start>{{ badgeIcon }}</v-icon>
                  {{ badgeName }}
                </v-chip>
                <v-chip color="primary" variant="tonal">
                  {{ getLocalizedName(listing.category) }}
                </v-chip>
                <v-chip v-if="listing.sub_category" variant="tonal">
                  {{ getLocalizedName(listing.sub_category) }}
                </v-chip>
              </div>

              <!-- Title -->
              <h1 class="text-h4 font-weight-bold mb-4">{{ listing.title }}</h1>

              <!-- Meta -->
              <div class="d-flex flex-wrap gap-4 text-body-2 text-medium-emphasis mb-6">
                <span class="d-flex align-center">
                  <v-icon start size="18">mdi-map-marker</v-icon>
                  {{ locationText }}
                </span>
                <span class="d-flex align-center">
                  <v-icon start size="18">mdi-clock-outline</v-icon>
                  {{ formatDate(listing.created_at) }}
                </span>
                <span class="d-flex align-center">
                  <v-icon start size="18">mdi-eye</v-icon>
                  {{ formatNumber(listing.view_count) }} {{ locale === 'ar' ? 'مشاهدة' : 'views' }}
                </span>
              </div>

              <v-divider class="my-6" />

              <!-- Description -->
              <h2 class="text-h6 font-weight-bold mb-4">
                {{ locale === 'ar' ? 'الوصف' : 'Description' }}
              </h2>
              <p class="text-body-1 listing-description" style="white-space: pre-line">
                {{ listing.description }}
              </p>
            </v-card-text>
          </v-card>

          <!-- Related Listings -->
          <div v-if="related.length > 0">
            <h2 class="text-h5 font-weight-bold mb-4">
              {{ locale === 'ar' ? 'إعلانات مشابهة' : 'Related Listings' }}
            </h2>
            <v-row>
              <v-col v-for="item in related" :key="item.id" cols="12" sm="6">
                <listing-card :listing="item" :locale="locale" />
              </v-col>
            </v-row>
          </div>
        </v-col>

        <!-- Sidebar -->
        <v-col cols="12" lg="4">
          <!-- Price Card -->
          <v-card class="mb-6 sticky-sidebar" variant="outlined">
            <v-card-text class="pa-6">
              <div class="text-center mb-6">
                <div v-if="listing.price" class="text-h3 font-weight-bold text-primary">
                  ${{ formatNumber(listing.price) }}
                </div>
                <div v-else class="text-h5 text-medium-emphasis">
                  {{ locale === 'ar' ? 'السعر عند الاتصال' : 'Contact for price' }}
                </div>
              </div>

              <v-divider class="mb-6" />

              <!-- Seller Info -->
              <div class="d-flex align-center mb-6">
                <v-avatar size="56" class="mr-4">
                  <v-img v-if="userPhotoUrl" :src="userPhotoUrl" />
                  <v-icon v-else size="32">mdi-account</v-icon>
                </v-avatar>
                <div>
                  <h3 class="text-subtitle-1 font-weight-bold">{{ listing.user?.name }}</h3>
                  <p class="text-caption text-medium-emphasis">
                    {{ locale === 'ar' ? 'عضو منذ' : 'Member since' }}
                    {{ formatDate(listing.user?.created_at, true) }}
                  </p>
                </div>
              </div>

              <!-- Actions -->
              <v-btn color="success" size="large" block class="mb-3" :href="`tel:${listing.phone}`">
                <v-icon start>mdi-phone</v-icon>
                {{ locale === 'ar' ? 'اتصل الآن' : 'Call Now' }}
              </v-btn>

              <v-btn color="primary" size="large" block variant="outlined" class="mb-3" :href="`https://wa.me/${listing.whatsapp || listing.phone}`" target="_blank">
                <v-icon start>mdi-whatsapp</v-icon>
                {{ locale === 'ar' ? 'واتساب' : 'WhatsApp' }}
              </v-btn>

              <v-btn variant="text" size="large" block @click="toggleFavorite">
                <v-icon start :color="isFavorite ? 'red' : ''">
                  {{ isFavorite ? 'mdi-heart' : 'mdi-heart-outline' }}
                </v-icon>
                {{ locale === 'ar' ? 'حفظ الإعلان' : 'Save Listing' }}
              </v-btn>
            </v-card-text>
          </v-card>

          <!-- Share -->
          <v-card variant="outlined">
            <v-card-text class="pa-4">
              <h3 class="text-subtitle-1 font-weight-bold mb-3">
                {{ locale === 'ar' ? 'مشاركة الإعلان' : 'Share Listing' }}
              </h3>
              <div class="d-flex gap-2">
                <v-btn icon variant="tonal" color="blue" :href="`https://facebook.com/sharer/sharer.php?u=${shareUrl}`" target="_blank">
                  <v-icon>mdi-facebook</v-icon>
                </v-btn>
                <v-btn icon variant="tonal" color="info" :href="`https://twitter.com/intent/tweet?url=${shareUrl}&text=${listing.title}`" target="_blank">
                  <v-icon>mdi-twitter</v-icon>
                </v-btn>
                <v-btn icon variant="tonal" color="success" :href="`https://wa.me/?text=${listing.title} ${shareUrl}`" target="_blank">
                  <v-icon>mdi-whatsapp</v-icon>
                </v-btn>
                <v-btn icon variant="tonal" @click="copyLink">
                  <v-icon>mdi-link</v-icon>
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Loading -->
    <v-container v-else-if="loading" class="py-12">
      <v-row>
        <v-col cols="12" lg="8">
          <v-skeleton-loader type="image" height="450" class="mb-6" />
          <v-skeleton-loader type="article" />
        </v-col>
        <v-col cols="12" lg="4">
          <v-skeleton-loader type="card" height="400" />
        </v-col>
      </v-row>
    </v-container>

    <!-- Not Found -->
    <v-container v-else class="py-16 text-center">
      <v-icon size="100" color="error">mdi-alert-circle</v-icon>
      <h2 class="text-h4 mt-6 mb-4">{{ locale === 'ar' ? 'الإعلان غير موجود' : 'Listing Not Found' }}</h2>
      <v-btn color="primary" to="/browse">{{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}</v-btn>
    </v-container>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar" :timeout="2000">
      {{ snackbarText }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useAdvancedSeo } from '@/composables/useAdvancedSeo'
import ListingCard from '@/components/ListingCard.vue'

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

const getPhotoUrl = (photo) => {
  return ensureAbsoluteUrl(photo?.src || photo?.url)
}

// Check if file is a video by extension
const isVideoFile = (src) => {
  if (!src) return false
  const videoExtensions = ['.mp4', '.webm', '.ogg', '.mov', '.avi', '.mkv']
  const lowerSrc = src.toLowerCase()
  return videoExtensions.some(ext => lowerSrc.endsWith(ext))
}

const userPhotoUrl = computed(() => {
  if (listing.value?.user?.photos?.[0]) {
    return ensureAbsoluteUrl(listing.value.user.photos[0].src)
  }
  return null
})

// Get localized name from category/subcategory/city/country object
const getLocalizedName = (item) => {
  if (!item) return ''
  // If name is an object with ar/en keys
  if (item.name && typeof item.name === 'object') {
    return locale.value === 'ar' ? (item.name.ar || item.name.en || '') : (item.name.en || item.name.ar || '')
  }
  // If name is a string directly
  if (typeof item.name === 'string') {
    return locale.value === 'ar' ? item.name : (item.name_en || item.name)
  }
  return ''
}

const shareUrl = computed(() => window.location.href)

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
    return locale.value === 'ar' ? listing.value.badge.name_ar : listing.value.badge.name_en
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
    { title: locale.value === 'ar' ? 'الرئيسية' : 'Home', to: '/' },
    { title: getLocalizedName(listing.value.category), to: `/category/${listing.value.category?.id}` },
    { title: listing.value.title, disabled: true },
  ]
})

const formatNumber = (num) => new Intl.NumberFormat().format(num || 0)

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
  snackbarText.value = isFavorite.value
    ? (locale.value === 'ar' ? 'تم حفظ الإعلان' : 'Listing saved')
    : (locale.value === 'ar' ? 'تم إزالة الإعلان' : 'Listing removed')
  snackbar.value = true
}

const copyLink = () => {
  navigator.clipboard.writeText(window.location.href)
  snackbarText.value = locale.value === 'ar' ? 'تم نسخ الرابط' : 'Link copied'
  snackbar.value = true
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
</style>
