<template>
  <div class="category-page">
    <!-- Hero -->
    <section class="hero-gradient py-12">
      <v-container>
        <v-avatar :color="getCategoryColor(category?.id)" size="80" class="mb-4">
          <v-icon size="40" color="white">{{ getCategoryIcon(category?.id) }}</v-icon>
        </v-avatar>
        <h1 class="text-h3 font-weight-bold text-white mb-2">
          {{ locale === 'ar' ? category?.name : category?.name_en }}
        </h1>
        <p class="text-h6 text-white-darken-1">
          {{ pagination.total }} {{ locale === 'ar' ? 'إعلان' : 'listings' }}
        </p>
      </v-container>
    </section>

    <v-container class="py-8">
      <!-- Subcategories -->
      <div v-if="subcategories.length > 0" class="mb-8">
        <h2 class="text-h6 font-weight-bold mb-4">{{ locale === 'ar' ? 'التصنيفات الفرعية' : 'Subcategories' }}</h2>
        <v-chip-group v-model="selectedSubcategory" column @update:model-value="onSubcategoryChange">
          <v-chip filter :value="null">{{ locale === 'ar' ? 'الكل' : 'All' }}</v-chip>
          <v-chip v-for="sub in subcategories" :key="sub.id" filter :value="sub.id">
            {{ locale === 'ar' ? sub.name : sub.name_en }} ({{ sub.posts_count }})
          </v-chip>
        </v-chip-group>
      </div>

      <!-- Listings -->
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
        <v-icon size="80" color="grey">mdi-folder-open</v-icon>
        <h3 class="text-h5 mt-4">{{ locale === 'ar' ? 'لا توجد إعلانات' : 'No listings found' }}</h3>
      </v-card>

      <!-- Pagination -->
      <div v-if="pagination.last_page > 1" class="d-flex justify-center mt-8">
        <v-pagination v-model="currentPage" :length="pagination.last_page" rounded="circle" @update:model-value="fetchListings" />
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
const { updateMeta, setBreadcrumbSchema } = useSeo()

const category = ref(null)
const subcategories = ref([])
const listings = ref([])
const loading = ref(true)
const selectedSubcategory = ref(null)
const currentPage = ref(1)
const pagination = ref({ total: 0, last_page: 1 })

const locale = computed(() => appStore.locale)

const categoryIcons = { 1: 'mdi-cellphone', 2: 'mdi-car', 3: 'mdi-briefcase', 4: 'mdi-home-city', 5: 'mdi-shape' }
const categoryColors = { 1: 'blue', 2: 'red', 3: 'green', 4: 'purple', 5: 'orange' }
const getCategoryIcon = (id) => categoryIcons[id] || 'mdi-folder'
const getCategoryColor = (id) => categoryColors[id] || 'grey'

const fetchCategory = async () => {
  try {
    const response = await fetch('/api/public/categories')
    if (!response.ok) return
    const data = await response.json()
    const cats = Array.isArray(data.categories) ? data.categories : []
    category.value = cats.find(c => c.id === parseInt(route.params.id))

    if (category.value) {
      updateMeta({
        title: `${locale.value === 'ar' ? category.value.name : category.value.name_en} - طلبنا`,
        description: `تصفح إعلانات ${category.value.name} على طلبنا`,
      })
    }
  } catch (error) {
    console.error('Error:', error)
  }
}

const fetchSubcategories = async () => {
  try {
    const response = await fetch(`/api/public/categories/${route.params.id}/subcategories`)
    if (!response.ok) return
    const data = await response.json()
    subcategories.value = Array.isArray(data.subcategories) ? data.subcategories : []
  } catch (error) {
    console.error('Error:', error)
  }
}

const fetchListings = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams({
      category_id: route.params.id,
      page: currentPage.value,
    })
    if (selectedSubcategory.value) params.append('subcategory_id', selectedSubcategory.value)

    const response = await fetch(`/api/public/listings?${params}`)
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

const onSubcategoryChange = () => {
  currentPage.value = 1
  fetchListings()
}

onMounted(() => {
  fetchCategory()
  fetchSubcategories()
  fetchListings()
})

watch(() => route.params.id, () => {
  selectedSubcategory.value = null
  currentPage.value = 1
  fetchCategory()
  fetchSubcategories()
  fetchListings()
})
</script>

<style scoped>
.hero-gradient {
  background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%);
}
</style>
