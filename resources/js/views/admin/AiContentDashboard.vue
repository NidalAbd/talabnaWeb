<template>
  <div class="ai-content-dashboard">
    <!-- Header -->
    <div class="stats-dashboard">
      <div class="stats-grid">
        <div class="stat-card-compact stat-purple" @click="activeTab = 'posts'" :class="{ active: activeTab === 'posts' }" style="cursor:pointer">
          <div class="stat-icon"><i class="fas fa-pen-fancy"></i></div>
          <div class="stat-info">
            <div class="stat-label-compact">Generate Posts</div>
          </div>
        </div>
        <div class="stat-card-compact stat-blue" @click="activeTab = 'seed'" :class="{ active: activeTab === 'seed' }" style="cursor:pointer">
          <div class="stat-icon"><i class="fas fa-users-cog"></i></div>
          <div class="stat-info">
            <div class="stat-label-compact">Seed Users & Posts</div>
          </div>
        </div>
        <div class="stat-card-compact stat-green" @click="activeTab = 'images'" :class="{ active: activeTab === 'images' }" style="cursor:pointer">
          <div class="stat-icon"><i class="fas fa-image"></i></div>
          <div class="stat-info">
            <div class="stat-label-compact">Generate Images</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Tab: Generate Posts -->
    <div v-if="activeTab === 'posts'" class="card card-purple card-outline mt-3">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-pen-fancy mr-2"></i>Generate AI Posts</h3></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Category *</label>
            <select v-model="postForm.category_id" class="form-control" @change="loadSubcategories">
              <option :value="null">-- Select Category --</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <label>Subcategory <small class="text-muted">(optional - leave empty for random)</small></label>
            <select v-model="postForm.subcategory_id" class="form-control">
              <option :value="null">-- Random Subcategories --</option>
              <option v-for="s in subcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="col-md-4 mb-3">
            <label>Number of Posts</label>
            <input v-model.number="postForm.count" type="number" class="form-control" min="1" max="50">
          </div>
          <div class="col-md-4 mb-3">
            <label>Photos per Post</label>
            <input v-model.number="postForm.photos_count" type="number" class="form-control" min="1" max="3">
          </div>
          <div class="col-md-4 mb-3">
            <label>Bot User</label>
            <select v-model="postForm.bot_user_id" class="form-control">
              <option :value="null">-- Auto (Talabna Bot) --</option>
              <option v-for="u in botUsers" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="col-12 mb-3">
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="randomSwitch" v-model="postForm.random">
              <label class="custom-control-label" for="randomSwitch">Distribute posts across random subcategories</label>
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-purple" :disabled="!postForm.category_id || postLoading" @click="generatePosts">
          <i class="fas fa-play mr-1"></i> {{ postLoading ? 'Starting...' : 'Generate Posts' }}
        </button>
      </div>

      <!-- Progress -->
      <div v-if="postProgress" class="card-body border-top">
        <h6><i class="fas fa-tasks mr-1"></i> Progress</h6>
        <div class="progress mb-2" style="height: 20px;">
          <div class="progress-bar bg-purple" :style="{ width: postProgressPercent + '%' }">{{ postProgressPercent }}%</div>
        </div>
        <p class="mb-1">
          <span class="badge" :class="postProgress.status === 'running' ? 'badge-warning' : 'badge-success'">{{ postProgress.status }}</span>
          &nbsp; {{ postProgress.completed_posts || 0 }} / {{ postProgress.total_posts || 0 }} posts
        </p>
        <p v-if="postProgress.current_item" class="text-muted small mb-0">{{ postProgress.current_item }}</p>
      </div>
    </div>

    <!-- Tab: Seed Users -->
    <div v-if="activeTab === 'seed'" class="card card-primary card-outline mt-3">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-users-cog mr-2"></i>Seed Bot Users & Posts</h3></div>
      <div class="card-body">
        <div class="alert alert-info">
          <i class="fas fa-info-circle mr-1"></i>
          Create bot users across countries with random posts in random categories. Each user gets their own posts.
        </div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Total Users</label>
            <input v-model.number="seedForm.users" type="number" class="form-control" min="1" max="500">
          </div>
          <div class="col-md-4 mb-3">
            <label>Posts per User</label>
            <input v-model.number="seedForm.posts_per_user" type="number" class="form-control" min="1" max="20">
          </div>
          <div class="col-md-4 mb-3">
            <label>Photos per Post</label>
            <input v-model.number="seedForm.photos" type="number" class="form-control" min="1" max="3">
          </div>
          <div class="col-md-6 mb-3">
            <label>Country <small class="text-muted">(optional - leave empty for all)</small></label>
            <select v-model="seedForm.country_id" class="form-control">
              <option :value="null">-- All Countries --</option>
              <option v-for="c in countries" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="col-md-6 mb-3">
            <div class="callout callout-info py-2 mb-0">
              <strong>Summary:</strong> {{ seedForm.users }} users &times; {{ seedForm.posts_per_user }} posts = {{ seedForm.users * seedForm.posts_per_user }} total posts
            </div>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-primary" :disabled="seedLoading" @click="seedUsers">
          <i class="fas fa-play mr-1"></i> {{ seedLoading ? 'Starting...' : 'Start Seeding' }}
        </button>
      </div>

      <!-- Progress -->
      <div v-if="seedProgress" class="card-body border-top">
        <h6><i class="fas fa-tasks mr-1"></i> Seed Progress</h6>
        <div class="progress mb-2" style="height: 20px;">
          <div class="progress-bar bg-primary" :style="{ width: seedProgressPercent + '%' }">{{ seedProgressPercent }}%</div>
        </div>
        <p class="mb-1">
          <span class="badge" :class="seedProgress.status === 'running' ? 'badge-warning' : 'badge-success'">{{ seedProgress.status }}</span>
          &nbsp; Users: {{ seedProgress.created_users || 0 }}/{{ seedProgress.total_users || 0 }}
          | Posts: {{ seedProgress.created_posts || 0 }}/{{ seedProgress.total_posts || 0 }}
        </p>
        <p v-if="seedProgress.current_item" class="text-muted small mb-0">{{ seedProgress.current_item }}</p>
      </div>
    </div>

    <!-- Tab: Generate Images -->
    <div v-if="activeTab === 'images'" class="card card-success card-outline mt-3">
      <div class="card-header"><h3 class="card-title"><i class="fas fa-image mr-2"></i>Generate AI Images</h3></div>
      <div class="card-body">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Generate images for</label>
            <select v-model="imageTarget" class="form-control">
              <option value="Category">Specific Category</option>
              <option value="Subcategory">Specific Subcategory</option>
              <option value="All Categories">All Categories</option>
              <option value="All Subcategories">All Subcategories</option>
            </select>
          </div>
          <div v-if="imageTarget === 'Category'" class="col-md-6 mb-3">
            <label>Select Category</label>
            <select v-model="imageTargetId" class="form-control">
              <option :value="null">-- Select --</option>
              <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div v-if="imageTarget === 'Subcategory'" class="col-md-6 mb-3">
            <label>Select Subcategory</label>
            <select v-model="imageTargetId" class="form-control">
              <option :value="null">-- Select --</option>
              <option v-for="s in allSubcategories" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
        </div>
      </div>
      <div class="card-footer">
        <button class="btn btn-success mr-2" :disabled="imageLoading" @click="generateImages">
          <i class="fas fa-magic mr-1"></i> {{ imageLoading ? 'Starting...' : 'Generate Images' }}
        </button>
        <button class="btn btn-outline-secondary" @click="checkImageStatus">
          <i class="fas fa-sync-alt mr-1"></i> Check Status
        </button>
      </div>

      <div v-if="imageProgress" class="card-body border-top">
        <h6>Image Generation Status</h6>
        <pre class="bg-light p-3 rounded small">{{ JSON.stringify(imageProgress, null, 2) }}</pre>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const activeTab = ref('posts')

const categories = ref([])
const subcategories = ref([])
const allSubcategories = ref([])
const countries = ref([])
const botUsers = ref([])

const postForm = ref({ category_id: null, subcategory_id: null, count: 5, photos_count: 1, bot_user_id: null, random: true })
const postLoading = ref(false)
const postProgress = ref(null)

const seedForm = ref({ users: 10, posts_per_user: 5, photos: 1, country_id: null })
const seedLoading = ref(false)
const seedProgress = ref(null)

const imageTarget = ref('Category')
const imageTargetId = ref(null)
const imageLoading = ref(false)
const imageProgress = ref(null)

let pollInterval = null

const postProgressPercent = computed(() => {
  if (!postProgress.value?.total_posts) return 0
  return Math.round((postProgress.value.completed_posts / postProgress.value.total_posts) * 100)
})

const seedProgressPercent = computed(() => {
  if (!seedProgress.value?.total_posts) return 0
  return Math.round((seedProgress.value.created_posts / seedProgress.value.total_posts) * 100)
})

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || ''

function toArray(data) {
  if (Array.isArray(data)) return data
  if (data?.data && Array.isArray(data.data)) return data.data
  if (typeof data === 'object' && data !== null) {
    for (const val of Object.values(data)) {
      if (Array.isArray(val)) return val
    }
  }
  return []
}

function parseName(name) {
  if (!name) return ''
  if (typeof name === 'string') {
    try { const p = JSON.parse(name); return p.en || p.ar || name } catch { return name }
  }
  return name.en || name.ar || ''
}

onMounted(async () => {
  await Promise.allSettled([loadCategories(), loadCountries(), loadBotUsers(), loadAllSubcategories()])
})

onUnmounted(() => { if (pollInterval) clearInterval(pollInterval) })

async function loadCategories() {
  try {
    const res = await fetch('/api/admin/categories', { headers: { 'Accept': 'application/json' } })
    if (res.ok) { categories.value = toArray(await res.json()).map(c => ({ id: c.id, name: parseName(c.name) || `Category ${c.id}` })) }
  } catch (e) { console.error('loadCategories:', e) }
}

async function loadSubcategories() {
  if (!postForm.value.category_id) return
  try {
    const res = await fetch(`/api/admin/subcategories?category_id=${postForm.value.category_id}`, { headers: { 'Accept': 'application/json' } })
    if (res.ok) { subcategories.value = toArray(await res.json()).map(s => ({ id: s.id, name: parseName(s.name) || `Sub ${s.id}` })) }
  } catch (e) { console.error(e) }
}

async function loadAllSubcategories() {
  try {
    const res = await fetch('/api/admin/subcategories', { headers: { 'Accept': 'application/json' } })
    if (res.ok) { allSubcategories.value = toArray(await res.json()).map(s => ({ id: s.id, name: parseName(s.name) || `Sub ${s.id}` })) }
  } catch (e) { console.error(e) }
}

async function loadCountries() {
  try {
    const res = await fetch('/api/admin/countries', { headers: { 'Accept': 'application/json' } })
    if (res.ok) { countries.value = toArray(await res.json()).map(c => ({ id: c.id, name: parseName(c.name) || `Country ${c.id}` })) }
  } catch (e) { console.error(e) }
}

async function loadBotUsers() {
  try {
    const res = await fetch('/api/admin/users?search=bot&per_page=50', { headers: { 'Accept': 'application/json' } })
    if (res.ok) { botUsers.value = toArray(await res.json()).map(u => ({ id: u.id, name: `${u.name || u.user_name} (ID: ${u.id})` })) }
  } catch (e) { console.error(e) }
}

async function generatePosts() {
  postLoading.value = true
  try {
    const res = await fetch('/ai-posts/generate', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify(postForm.value),
    })
    const data = await res.json()
    if (data.success) { startPollingPostProgress() } else { alert(data.message || 'Failed') }
  } catch (e) { alert('Error: ' + e.message) }
  postLoading.value = false
}

function startPollingPostProgress() {
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch(`/ai-posts/status?category_id=${postForm.value.category_id}`, { headers: { 'Accept': 'application/json' } })
      if (res.ok) { const d = await res.json(); postProgress.value = d.progress; if (d.progress?.status === 'finished') clearInterval(pollInterval) }
    } catch (e) { console.error(e) }
  }, 5000)
}

async function seedUsers() {
  seedLoading.value = true
  try {
    const res = await fetch('/api/admin/ai/seed', {
      method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' },
      body: JSON.stringify(seedForm.value),
    })
    const data = await res.json()
    if (data.success) { startPollingSeedProgress() } else { alert(data.message || 'Failed') }
  } catch (e) { alert('Error: ' + e.message) }
  seedLoading.value = false
}

function startPollingSeedProgress() {
  if (pollInterval) clearInterval(pollInterval)
  pollInterval = setInterval(async () => {
    try {
      const res = await fetch('/api/admin/ai/seed-status', { headers: { 'Accept': 'application/json' } })
      if (res.ok) { const d = await res.json(); seedProgress.value = d.progress; if (d.progress?.status === 'finished') clearInterval(pollInterval) }
    } catch (e) { console.error(e) }
  }, 5000)
}

async function generateImages() {
  imageLoading.value = true
  try {
    let url = ''
    if (imageTarget.value === 'Category' && imageTargetId.value) url = `/ai-image/generate-category/${imageTargetId.value}`
    else if (imageTarget.value === 'Subcategory' && imageTargetId.value) url = `/ai-image/generate-subcategory/${imageTargetId.value}`
    else if (imageTarget.value === 'All Categories') url = '/ai-image/generate-all-categories'
    else if (imageTarget.value === 'All Subcategories') url = '/ai-image/generate-all-subcategories'
    if (url) {
      const res = await fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf(), 'Accept': 'application/json' } })
      const data = await res.json()
      alert(data.message || (data.success ? 'Started' : 'Failed'))
    }
  } catch (e) { alert('Error: ' + e.message) }
  imageLoading.value = false
}

async function checkImageStatus() {
  try {
    const res = await fetch('/ai-image/status', { headers: { 'Accept': 'application/json' } })
    if (res.ok) imageProgress.value = await res.json()
  } catch (e) { console.error(e) }
}
</script>

<style scoped>
.stat-card-compact.active {
  border: 2px solid currentColor;
  transform: scale(1.02);
}
.btn-purple {
  background: #7c3aed;
  border-color: #7c3aed;
  color: #fff;
}
.btn-purple:hover {
  background: #6d28d9;
  border-color: #6d28d9;
  color: #fff;
}
.card-purple.card-outline {
  border-top: 3px solid #7c3aed;
}
.bg-purple {
  background-color: #7c3aed !important;
}
</style>
