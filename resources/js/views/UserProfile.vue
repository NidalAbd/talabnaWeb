<template>
  <div class="user-profile-page">
    <div class="container py-8">
      <!-- User Header -->
      <div v-if="user" class="card mb-8">
        <div class="card-body pa-6">
          <div class="d-flex flex-wrap align-center gap-4">
            <div class="avatar avatar-100">
              <img v-if="user.photo" :src="user.photo.src" width="100" height="100" loading="lazy" decoding="async" />
              <i v-else class="mdi mdi-account" style="font-size: 60px;"></i>
            </div>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ user.name }}</h1>
              <p class="text-body-2 text-muted">
                <i class="mdi mdi-calendar mr-1" style="font-size: 16px;"></i>
                {{ locale === 'ar' ? 'عضو منذ' : 'Member since' }} {{ formatYear(user.created_at) }}
              </p>
              <p class="text-body-2 text-muted">
                <i class="mdi mdi-bullhorn mr-1" style="font-size: 16px;"></i>
                {{ user.listings_count }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- User Listings -->
      <h2 class="text-h5 font-weight-bold mb-4">{{ locale === 'ar' ? 'إعلانات المستخدم' : 'User Listings' }}</h2>

      <div v-if="!loading && listings.length > 0" class="row">
        <div v-for="listing in listings" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
          <listing-card :listing="listing" :locale="locale" />
        </div>
      </div>

      <div v-else-if="loading" class="text-center py-16">
        <div class="spinner spinner-lg"></div>
      </div>

      <div v-else class="card-flat text-center py-16">
        <i class="mdi mdi-folder-open" style="font-size: 80px; color: var(--color-text-muted);"></i>
        <h3 class="text-h5 mt-4">{{ locale === 'ar' ? 'لا توجد إعلانات' : 'No listings' }}</h3>
      </div>

      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <div class="pagination">
          <button class="pagination-btn" :disabled="currentPage <= 1" @click="currentPage--; fetchUser()">
            <i class="mdi mdi-chevron-left"></i>
          </button>
          <button v-for="p in paginationPages" :key="p" class="pagination-btn" :class="{ active: currentPage === p }" @click="currentPage = p; fetchUser()">
            {{ p }}
          </button>
          <button class="pagination-btn" :disabled="currentPage >= pagination.last_page" @click="currentPage++; fetchUser()">
            <i class="mdi mdi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'

const route = useRoute()
const appStore = useAppStore()
const { updateMeta, setProfileSchema, setItemListSchema } = useSeo()

const user = ref(null)
const listings = ref([])
const loading = ref(true)
const currentPage = ref(1)
const pagination = ref({ total: 0, last_page: 1 })

const locale = computed(() => appStore.locale)

const paginationPages = computed(() => {
  const pages = []
  const total = pagination.value.last_page
  const current = currentPage.value
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const formatYear = (date) => date ? new Date(date).getFullYear() : ''

const fetchUser = async () => {
  loading.value = true
  try {
    const response = await fetch(`/api/public/users/${route.params.id}?page=${currentPage.value}`)
    if (!response.ok) return
    const data = await response.json()
    user.value = data.user
    listings.value = Array.isArray(data.listings) ? data.listings : []
    pagination.value = data.pagination || pagination.value

    if (user.value) {
      updateMeta({
        title: locale.value === 'ar'
          ? `${user.value.name} - طلبنا`
          : `${user.value.name} - Talabna`,
        description: locale.value === 'ar'
          ? `عرض ملف ${user.value.name} و${pagination.value.total} إعلان على طلبنا`
          : `View ${user.value.name}'s profile and ${pagination.value.total} listings on Talabna`,
      })
      setProfileSchema(user.value)
      if (listings.value.length > 0) {
        setItemListSchema(listings.value, `${user.value.name}'s Listings`)
      }
    }
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchUser)
watch(() => route.params.id, fetchUser)
</script>
