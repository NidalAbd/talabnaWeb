<template>
  <div class="user-profile-page">
    <v-container class="py-8">
      <!-- User Header -->
      <v-card v-if="user" class="mb-8" variant="outlined">
        <v-card-text class="pa-6">
          <div class="d-flex flex-wrap align-center gap-4">
            <v-avatar size="100">
              <v-img v-if="user.photo" :src="user.photo.src" />
              <v-icon v-else size="60">mdi-account</v-icon>
            </v-avatar>
            <div>
              <h1 class="text-h4 font-weight-bold mb-2">{{ user.name }}</h1>
              <p class="text-body-2 text-medium-emphasis">
                <v-icon size="16" class="mr-1">mdi-calendar</v-icon>
                {{ locale === 'ar' ? 'عضو منذ' : 'Member since' }} {{ formatYear(user.created_at) }}
              </p>
              <p class="text-body-2 text-medium-emphasis">
                <v-icon size="16" class="mr-1">mdi-bullhorn</v-icon>
                {{ user.listings_count }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}
              </p>
            </div>
          </div>
        </v-card-text>
      </v-card>

      <!-- User Listings -->
      <h2 class="text-h5 font-weight-bold mb-4">{{ locale === 'ar' ? 'إعلانات المستخدم' : 'User Listings' }}</h2>

      <v-row v-if="!loading && listings.length > 0">
        <v-col v-for="listing in listings" :key="listing.id" cols="12" sm="6" md="4" lg="3">
          <listing-card :listing="listing" :locale="locale" />
        </v-col>
      </v-row>

      <div v-else-if="loading" class="text-center py-16">
        <v-progress-circular indeterminate color="primary" size="64" />
      </div>

      <v-card v-else class="text-center py-16" variant="flat">
        <v-icon size="80" color="grey">mdi-folder-open</v-icon>
        <h3 class="text-h5 mt-4">{{ locale === 'ar' ? 'لا توجد إعلانات' : 'No listings' }}</h3>
      </v-card>

      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <v-pagination v-model="currentPage" :length="pagination.last_page" rounded="circle" @update:model-value="fetchUser" />
      </div>
    </v-container>
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
