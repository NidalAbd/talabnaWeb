<template>
  <div class="search-page">
    <div class="container py-8">
      <!-- Search Header -->
      <div class="mb-8">
        <h1 class="text-h4 font-weight-bold mb-4">
          {{ t('search.title') }}
        </h1>
        <div class="search-wrapper" style="max-width: 600px;">
          <i class="mdi mdi-magnify search-input-icon"></i>
          <input
            v-model="searchQuery"
            :placeholder="t('search.placeholder')"
            class="form-input form-input-search"
            @keyup.enter="doSearch"
          />
        </div>
        <p v-if="query" class="text-body-1 text-muted mt-4">
          {{ pagination.total }} {{ t('search.results_for') }} "{{ query }}"
        </p>
      </div>

      <!-- Results -->
      <div v-if="!loading && listings.length > 0" class="row">
        <div v-for="listing in listings" :key="listing.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
          <listing-card :listing="listing" :locale="locale" />
        </div>
      </div>

      <!-- Loading -->
      <div v-else-if="loading" class="text-center py-16">
        <div class="spinner spinner-lg"></div>
      </div>

      <!-- No Results -->
      <div v-else class="card-flat text-center py-16">
        <i class="mdi mdi-magnify" style="font-size: 80px; color: var(--color-text-muted);"></i>
        <h3 class="text-h5 mt-4">{{ t('search.no_results') }}</h3>
        <p class="text-muted">{{ t('search.try_different') }}</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <div class="pagination">
          <button class="pagination-btn" :disabled="currentPage <= 1" @click="currentPage--; fetchResults()">
            <i class="mdi mdi-chevron-left"></i>
          </button>
          <button v-for="p in paginationPages" :key="p" class="pagination-btn" :class="{ active: currentPage === p }" @click="currentPage = p; fetchResults()">
            {{ p }}
          </button>
          <button class="pagination-btn" :disabled="currentPage >= pagination.last_page" @click="currentPage++; fetchResults()">
            <i class="mdi mdi-chevron-right"></i>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { apiFetch } from "@/utils/api"
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import ListingCard from '@/components/ListingCard.vue'
import { t } from '@/utils/translate'

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

const paginationPages = computed(() => {
  const pages = []
  const total = pagination.value.last_page
  const current = currentPage.value
  const start = Math.max(1, current - 2)
  const end = Math.min(total, current + 2)
  for (let i = start; i <= end; i++) pages.push(i)
  return pages
})

const doSearch = () => {
  if (searchQuery.value.trim()) {
    router.push({ name: 'search', query: { q: searchQuery.value } })
  }
}

const fetchResults = async () => {
  if (!query.value) return
  loading.value = true
  try {
    const response = await apiFetch(`/api/public/search?q=${encodeURIComponent(query.value)}&page=${currentPage.value}`)
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
  updateMeta({
    title: query.value
      ? (locale.value === 'ar' ? `نتائج البحث: ${query.value} - طلبنا` : `Search: ${query.value} - Talabna`)
      : (locale.value === 'ar' ? 'بحث - طلبنا' : 'Search - Talabna'),
    description: locale.value === 'ar'
      ? `نتائج البحث عن "${query.value}" في طلبنا - منصة الإعلانات المبوبة`
      : `Search results for "${query.value}" on Talabna - Classified Ads Platform`,
  })
  fetchResults()
})

watch(() => route.query.q, (newQ) => {
  query.value = newQ || ''
  searchQuery.value = query.value
  currentPage.value = 1
  fetchResults()
})
</script>
