<template>
  <div class="search-page">
    <v-container class="py-8">
      <!-- Search Header -->
      <div class="mb-8">
        <h1 class="text-h4 font-weight-bold mb-4">
          {{ locale === 'ar' ? 'نتائج البحث' : 'Search Results' }}
        </h1>
        <v-text-field
          v-model="searchQuery"
          :placeholder="locale === 'ar' ? 'ابحث...' : 'Search...'"
          prepend-inner-icon="mdi-magnify"
          variant="outlined"
          density="comfortable"
          clearable
          @keyup.enter="doSearch"
          style="max-width: 600px"
        />
        <p v-if="query" class="text-body-1 text-medium-emphasis mt-4">
          {{ pagination.total }} {{ locale === 'ar' ? 'نتيجة لـ' : 'results for' }} "{{ query }}"
        </p>
      </div>

      <!-- Results -->
      <v-row v-if="!loading && listings.length > 0">
        <v-col v-for="listing in listings" :key="listing.id" cols="12" sm="6" md="4" lg="3">
          <listing-card :listing="listing" :locale="locale" />
        </v-col>
      </v-row>

      <!-- Loading -->
      <div v-else-if="loading" class="text-center py-16">
        <v-progress-circular indeterminate color="primary" size="64" />
      </div>

      <!-- No Results -->
      <v-card v-else class="text-center py-16" variant="flat">
        <v-icon size="80" color="grey">mdi-magnify</v-icon>
        <h3 class="text-h5 mt-4">{{ locale === 'ar' ? 'لا توجد نتائج' : 'No results found' }}</h3>
        <p class="text-medium-emphasis">{{ locale === 'ar' ? 'جرب كلمات بحث مختلفة' : 'Try different search terms' }}</p>
      </v-card>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <v-pagination v-model="currentPage" :length="pagination.last_page" rounded="circle" @update:model-value="fetchResults" />
      </div>
    </v-container>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'

const route = useRoute()
const router = useRouter()
const appStore = useAppStore()
const { updateMeta } = useSeo()

const searchQuery = ref('')
const query = ref('')
const listings = ref([])
const loading = ref(false)
const currentPage = ref(1)
const pagination = ref({ total: 0, last_page: 1 })

const locale = computed(() => appStore.locale)

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

const fetchResults = async () => {
  if (!query.value) return
  loading.value = true
  try {
    const response = await fetch(`/api/public/search?q=${encodeURIComponent(query.value)}&page=${currentPage.value}`)
    if (!response.ok) return
    const data = await response.json()
    listings.value = Array.isArray(data.listings) ? data.listings : []
    pagination.value = data.pagination || pagination.value
  } catch (error) {
    console.error('Error:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  query.value = route.query.q || ''
  searchQuery.value = query.value
  updateMeta({ title: `البحث: ${query.value} - طلبنا` })
  fetchResults()
})

watch(() => route.query.q, (newQ) => {
  query.value = newQ || ''
  searchQuery.value = query.value
  currentPage.value = 1
  fetchResults()
})
</script>
