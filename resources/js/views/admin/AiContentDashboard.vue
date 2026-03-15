<template>
  <div>
    <v-card class="mb-6" elevation="0" border>
      <v-card-title class="d-flex align-center">
        <v-icon class="mr-2" color="purple">mdi-robot</v-icon>
        AI Content Generator
        <v-spacer />
        <v-chip v-if="isRunning" color="warning" variant="flat" size="small">
          <v-progress-circular indeterminate size="14" width="2" class="mr-2" />
          Running
        </v-chip>
      </v-card-title>
    </v-card>

    <v-tabs v-model="activeTab" color="purple" grow>
      <v-tab value="posts">
        <v-icon class="mr-2">mdi-post</v-icon>
        Generate Posts
      </v-tab>
      <v-tab value="seed">
        <v-icon class="mr-2">mdi-account-multiple-plus</v-icon>
        Seed Users & Posts
      </v-tab>
      <v-tab value="images">
        <v-icon class="mr-2">mdi-image-auto-adjust</v-icon>
        Generate Images
      </v-tab>
    </v-tabs>

    <v-window v-model="activeTab" class="mt-4">
      <!-- Tab 1: Generate Posts -->
      <v-window-item value="posts">
        <v-card elevation="0" border>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="postForm.category_id"
                  :items="categories"
                  item-title="name"
                  item-value="id"
                  label="Category *"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-shape"
                  @update:modelValue="loadSubcategories"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="postForm.subcategory_id"
                  :items="subcategories"
                  item-title="name"
                  item-value="id"
                  label="Subcategory (optional — leave empty for random)"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-sitemap"
                  clearable
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="postForm.count"
                  label="Number of posts"
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-numeric"
                  :min="1"
                  :max="50"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="postForm.photos_count"
                  label="Photos per post"
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-camera"
                  :min="1"
                  :max="3"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-select
                  v-model="postForm.bot_user_id"
                  :items="botUsers"
                  item-title="name"
                  item-value="id"
                  label="Bot User"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-robot"
                />
              </v-col>
              <v-col cols="12">
                <v-switch
                  v-model="postForm.random"
                  label="Random subcategories (distribute posts across random subcategories)"
                  color="purple"
                  hide-details
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-btn
              color="purple"
              variant="flat"
              size="large"
              :loading="postLoading"
              :disabled="!postForm.category_id"
              @click="generatePosts"
              prepend-icon="mdi-play"
            >
              Generate Posts
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Post Progress -->
        <v-card v-if="postProgress" class="mt-4" elevation="0" border>
          <v-card-title>
            <v-icon class="mr-2">mdi-progress-check</v-icon>
            Progress
          </v-card-title>
          <v-card-text>
            <v-progress-linear
              :model-value="postProgressPercent"
              color="purple"
              height="8"
              rounded
              class="mb-4"
            />
            <div class="d-flex justify-space-between mb-2">
              <span>Status: <v-chip :color="postProgress.status === 'running' ? 'warning' : 'success'" size="small">{{ postProgress.status }}</v-chip></span>
              <span>{{ postProgress.completed_posts || 0 }} / {{ postProgress.total_posts || 0 }} posts</span>
            </div>
            <div v-if="postProgress.current_item" class="text-body-2 text-medium-emphasis">
              {{ postProgress.current_item }}
            </div>
            <div v-if="postProgress.errors?.length" class="mt-3">
              <v-alert type="error" density="compact" v-for="(err, i) in postProgress.errors" :key="i" class="mb-1">
                {{ err.error || err }}
              </v-alert>
            </div>
          </v-card-text>
        </v-card>
      </v-window-item>

      <!-- Tab 2: Seed Users & Posts -->
      <v-window-item value="seed">
        <v-card elevation="0" border>
          <v-card-text>
            <v-alert type="info" variant="tonal" class="mb-4">
              Create bot users across countries with random posts in random categories. Each user gets their own posts.
            </v-alert>
            <v-row>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="seedForm.users"
                  label="Total users to create"
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-account-group"
                  :min="1"
                  :max="500"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="seedForm.posts_per_user"
                  label="Posts per user"
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-post"
                  :min="1"
                  :max="20"
                />
              </v-col>
              <v-col cols="12" md="4">
                <v-text-field
                  v-model.number="seedForm.photos"
                  label="Photos per post"
                  type="number"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-camera"
                  :min="1"
                  :max="3"
                />
              </v-col>
              <v-col cols="12" md="6">
                <v-select
                  v-model="seedForm.country_id"
                  :items="countries"
                  item-title="name"
                  item-value="id"
                  label="Country (optional — leave empty for all countries)"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-earth"
                  clearable
                />
              </v-col>
              <v-col cols="12" md="6">
                <div class="text-body-2 text-medium-emphasis pa-3 bg-grey-lighten-4 rounded">
                  <strong>Summary:</strong>
                  {{ seedForm.country_id ? seedForm.users : seedForm.users }} users
                  &times; {{ seedForm.posts_per_user }} posts
                  = {{ (seedForm.country_id ? seedForm.users : seedForm.users) * seedForm.posts_per_user }} total posts
                </div>
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-btn
              color="purple"
              variant="flat"
              size="large"
              :loading="seedLoading"
              @click="seedUsers"
              prepend-icon="mdi-play"
            >
              Start Seeding
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Seed Progress -->
        <v-card v-if="seedProgress" class="mt-4" elevation="0" border>
          <v-card-title>
            <v-icon class="mr-2">mdi-progress-check</v-icon>
            Seed Progress
          </v-card-title>
          <v-card-text>
            <v-progress-linear
              :model-value="seedProgressPercent"
              color="purple"
              height="8"
              rounded
              class="mb-4"
            />
            <div class="d-flex justify-space-between mb-2">
              <span>Status: <v-chip :color="seedProgress.status === 'running' ? 'warning' : 'success'" size="small">{{ seedProgress.status }}</v-chip></span>
              <span>Users: {{ seedProgress.created_users || 0 }}/{{ seedProgress.total_users || 0 }} | Posts: {{ seedProgress.created_posts || 0 }}/{{ seedProgress.total_posts || 0 }}</span>
            </div>
            <div v-if="seedProgress.current_item" class="text-body-2 text-medium-emphasis">
              {{ seedProgress.current_item }}
            </div>
          </v-card-text>
        </v-card>
      </v-window-item>

      <!-- Tab 3: Generate Images -->
      <v-window-item value="images">
        <v-card elevation="0" border>
          <v-card-text>
            <v-row>
              <v-col cols="12" md="6">
                <v-select
                  v-model="imageTarget"
                  :items="['Category', 'Subcategory', 'All Categories', 'All Subcategories']"
                  label="Generate images for"
                  variant="outlined"
                  density="comfortable"
                  prepend-inner-icon="mdi-image"
                />
              </v-col>
              <v-col cols="12" md="6" v-if="imageTarget === 'Category'">
                <v-select
                  v-model="imageTargetId"
                  :items="categories"
                  item-title="name"
                  item-value="id"
                  label="Select Category"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>
              <v-col cols="12" md="6" v-if="imageTarget === 'Subcategory'">
                <v-select
                  v-model="imageTargetId"
                  :items="allSubcategories"
                  item-title="name"
                  item-value="id"
                  label="Select Subcategory"
                  variant="outlined"
                  density="comfortable"
                />
              </v-col>
            </v-row>
          </v-card-text>
          <v-card-actions class="pa-4">
            <v-btn
              color="purple"
              variant="flat"
              size="large"
              :loading="imageLoading"
              @click="generateImages"
              prepend-icon="mdi-image-auto-adjust"
            >
              Generate Images
            </v-btn>
            <v-btn
              variant="outlined"
              size="large"
              @click="checkImageStatus"
              prepend-icon="mdi-refresh"
            >
              Check Status
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- Image Progress -->
        <v-card v-if="imageProgress" class="mt-4" elevation="0" border>
          <v-card-title>Image Generation Status</v-card-title>
          <v-card-text>
            <pre class="text-body-2 bg-grey-lighten-4 pa-3 rounded">{{ JSON.stringify(imageProgress, null, 2) }}</pre>
          </v-card-text>
        </v-card>
      </v-window-item>
    </v-window>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" :timeout="4000">
      {{ snackbar.text }}
    </v-snackbar>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const activeTab = ref('posts')
const snackbar = ref({ show: false, text: '', color: 'success' })

// Data
const categories = ref([])
const subcategories = ref([])
const allSubcategories = ref([])
const countries = ref([])
const botUsers = ref([])

// Post form
const postForm = ref({
  category_id: null,
  subcategory_id: null,
  count: 5,
  photos_count: 1,
  bot_user_id: null,
  random: true,
})
const postLoading = ref(false)
const postProgress = ref(null)

// Seed form
const seedForm = ref({
  users: 10,
  posts_per_user: 5,
  photos: 1,
  country_id: null,
})
const seedLoading = ref(false)
const seedProgress = ref(null)

// Image form
const imageTarget = ref('Category')
const imageTargetId = ref(null)
const imageLoading = ref(false)
const imageProgress = ref(null)

// Polling
let pollInterval = null

const isRunning = computed(() => {
  return postProgress.value?.status === 'running' || seedProgress.value?.status === 'running'
})

const postProgressPercent = computed(() => {
  if (!postProgress.value?.total_posts) return 0
  return Math.round((postProgress.value.completed_posts / postProgress.value.total_posts) * 100)
})

const seedProgressPercent = computed(() => {
  if (!seedProgress.value?.total_posts) return 0
  return Math.round((seedProgress.value.created_posts / seedProgress.value.total_posts) * 100)
})

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || ''

// Load initial data
onMounted(async () => {
  await Promise.all([
    loadCategories(),
    loadCountries(),
    loadBotUsers(),
    loadAllSubcategories(),
  ])
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})

async function loadCategories() {
  try {
    const res = await fetch('/api/admin/categories', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      categories.value = (data.data || data).map(c => ({
        id: c.id,
        name: c.name?.en || c.name?.ar || `Category ${c.id}`,
      }))
    }
  } catch (e) { console.error(e) }
}

async function loadSubcategories() {
  if (!postForm.value.category_id) return
  try {
    const res = await fetch(`/api/admin/subcategories?category_id=${postForm.value.category_id}`, { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      subcategories.value = (data.data || data).map(s => ({
        id: s.id,
        name: s.name?.en || s.name?.ar || `Subcategory ${s.id}`,
      }))
    }
  } catch (e) { console.error(e) }
}

async function loadAllSubcategories() {
  try {
    const res = await fetch('/api/admin/subcategories', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      allSubcategories.value = (data.data || data).map(s => ({
        id: s.id,
        name: s.name?.en || s.name?.ar || `Subcategory ${s.id}`,
      }))
    }
  } catch (e) { console.error(e) }
}

async function loadCountries() {
  try {
    const res = await fetch('/api/admin/countries', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      countries.value = (data.data || data).map(c => ({
        id: c.id,
        name: c.name?.en || c.name?.ar || `Country ${c.id}`,
      }))
    }
  } catch (e) { console.error(e) }
}

async function loadBotUsers() {
  try {
    const res = await fetch('/api/admin/users?search=bot&per_page=50', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      const data = await res.json()
      botUsers.value = (data.data || data).map(u => ({
        id: u.id,
        name: `${u.name || u.user_name} (ID: ${u.id})`,
      }))
    }
  } catch (e) { console.error(e) }
}

// Generate Posts
async function generatePosts() {
  postLoading.value = true
  try {
    const res = await fetch('/ai-posts/generate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify(postForm.value),
    })
    const data = await res.json()
    if (data.success) {
      showSnackbar('Post generation started!', 'success')
      startPollingPostProgress()
    } else {
      showSnackbar(data.message || 'Failed to start', 'error')
    }
  } catch (e) {
    showSnackbar('Error: ' + e.message, 'error')
  }
  postLoading.value = false
}

function startPollingPostProgress() {
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch(`/ai-posts/status?category_id=${postForm.value.category_id}`, { headers: { 'Accept': 'application/json' } })
      if (res.ok) {
        const data = await res.json()
        postProgress.value = data.progress
        if (data.progress?.status === 'finished') {
          clearInterval(pollInterval)
          showSnackbar('Post generation complete!', 'success')
        }
      }
    } catch (e) { console.error(e) }
  }, 5000)
}

// Seed Users
async function seedUsers() {
  seedLoading.value = true
  try {
    const res = await fetch('/api/admin/ai/seed', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify(seedForm.value),
    })
    const data = await res.json()
    if (data.success) {
      showSnackbar('User seeding started!', 'success')
      startPollingSeedProgress()
    } else {
      showSnackbar(data.message || 'Failed to start', 'error')
    }
  } catch (e) {
    showSnackbar('Error: ' + e.message, 'error')
  }
  seedLoading.value = false
}

function startPollingSeedProgress() {
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch('/api/admin/ai/seed-status', { headers: { 'Accept': 'application/json' } })
      if (res.ok) {
        const data = await res.json()
        seedProgress.value = data.progress
        if (data.progress?.status === 'finished') {
          clearInterval(pollInterval)
          showSnackbar('User seeding complete!', 'success')
        }
      }
    } catch (e) { console.error(e) }
  }, 5000)
}

// Generate Images
async function generateImages() {
  imageLoading.value = true
  try {
    let url = ''
    const headers = { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' }

    if (imageTarget.value === 'Category' && imageTargetId.value) {
      url = `/ai-image/generate-category/${imageTargetId.value}`
    } else if (imageTarget.value === 'Subcategory' && imageTargetId.value) {
      url = `/ai-image/generate-subcategory/${imageTargetId.value}`
    } else if (imageTarget.value === 'All Categories') {
      url = '/ai-image/generate-all-categories'
    } else if (imageTarget.value === 'All Subcategories') {
      url = '/ai-image/generate-all-subcategories'
    }

    if (url) {
      const res = await fetch(url, { method: 'POST', headers })
      const data = await res.json()
      showSnackbar(data.message || 'Image generation started', data.success ? 'success' : 'error')
    }
  } catch (e) {
    showSnackbar('Error: ' + e.message, 'error')
  }
  imageLoading.value = false
}

async function checkImageStatus() {
  try {
    const res = await fetch('/ai-image/status', { headers: { 'Accept': 'application/json' } })
    if (res.ok) {
      imageProgress.value = await res.json()
    }
  } catch (e) { console.error(e) }
}

function showSnackbar(text, color = 'success') {
  snackbar.value = { show: true, text, color }
}
</script>
