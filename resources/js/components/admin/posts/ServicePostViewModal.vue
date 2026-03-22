<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container modal-lg">
      <div class="modal-header-gradient">
        <h2><i class="fas fa-eye"></i> Post #{{ post?.id }}</h2>
        <button class="modal-close-btn" @click="$emit('close')"><i class="fas fa-times"></i></button>
      </div>

      <div v-if="loading" class="modal-body text-center py-5">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-3">Loading post...</p>
      </div>

      <div v-else-if="error" class="modal-body text-center py-5">
        <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
        <p class="mt-3 text-danger">{{ error }}</p>
      </div>

      <div v-else-if="post" class="modal-body">
        <!-- Photos Gallery -->
        <div v-if="post.photos && post.photos.length" class="photos-gallery mb-4">
          <div class="photos-grid">
            <div v-for="photo in post.photos" :key="photo.id" class="photo-item">
              <img :src="photoUrl(photo)" :alt="post.title" @error="$event.target.src='/storage/photos/og-image.jpg'">
            </div>
          </div>
        </div>

        <!-- Title & Status -->
        <div class="d-flex justify-content-between align-items-start mb-3">
          <h3 class="post-title mb-0">{{ post.title }}</h3>
          <span class="badge-status" :class="post.state === 'published' ? 'bg-success' : 'bg-warning'">
            {{ post.state }}
          </span>
        </div>

        <!-- Info Cards -->
        <div class="info-grid">
          <div class="info-card"><label>Price</label><span>{{ post.price || 0 }} {{ post.price_currency_code || '' }}</span></div>
          <div class="info-card"><label>Type</label><span>{{ post.type }}</span></div>
          <div class="info-card"><label>Views</label><span>{{ post.view_count }}</span></div>
          <div class="info-card"><label>Favorites</label><span>{{ post.favorites_count }}</span></div>
          <div class="info-card"><label>Category</label><span>{{ post.category?.name || '-' }}</span></div>
          <div class="info-card"><label>Subcategory</label><span>{{ post.sub_category?.name || '-' }}</span></div>
          <div class="info-card"><label>Country</label><span>{{ post.country?.name || '-' }}</span></div>
          <div class="info-card"><label>City</label><span>{{ post.city?.name || '-' }}</span></div>
          <div class="info-card"><label>User</label><span>{{ post.user?.user_name || '-' }}</span></div>
          <div class="info-card"><label>Badge</label><span>{{ post.badge ? post.badge.name_en : post.have_badge }}</span></div>
          <div class="info-card"><label>Created</label><span>{{ formatDate(post.created_at) }}</span></div>
          <div class="info-card"><label>Updated</label><span>{{ formatDate(post.updated_at) }}</span></div>
        </div>

        <!-- Description -->
        <div class="mt-4">
          <label class="form-label-bold">Description</label>
          <div class="description-box">{{ post.description || 'No description' }}</div>
        </div>

        <!-- Translations -->
        <div v-if="post.title_translations && Object.keys(post.title_translations).length > 1" class="mt-4">
          <label class="form-label-bold">Title Translations</label>
          <div class="translations-list">
            <div v-for="(val, locale) in post.title_translations" :key="locale" class="translation-item">
              <span class="locale-badge">{{ locale }}</span>
              <span>{{ val }}</span>
            </div>
          </div>
        </div>

        <div v-if="post.description_translations && Object.keys(post.description_translations).length > 1" class="mt-3">
          <label class="form-label-bold">Description Translations</label>
          <div class="translations-list">
            <div v-for="(val, locale) in post.description_translations" :key="locale" class="translation-item">
              <span class="locale-badge">{{ locale }}</span>
              <span>{{ truncate(val, 100) }}</span>
            </div>
          </div>
        </div>

        <!-- Viewers Section -->
        <div class="mt-4">
          <label class="form-label-bold">
            <i class="fas fa-eye"></i> Viewers
            <span v-if="post.unique_viewers_count != null" class="viewers-count-badge">{{ post.unique_viewers_count }} unique</span>
          </label>
          <div v-if="post.recent_viewers && post.recent_viewers.length" class="viewers-list">
            <div v-for="viewer in post.recent_viewers" :key="viewer.id" class="viewer-item">
              <img :src="viewer.avatar || '/vendor/adminlte/dist/img/user-default.jpg'" :alt="viewer.user_name" class="viewer-avatar">
              <div class="viewer-info">
                <span class="viewer-name">{{ viewer.user_name }}</span>
                <span class="viewer-time">{{ formatDate(viewer.viewed_at) }}</span>
              </div>
            </div>
          </div>
          <div v-else class="no-viewers">
            <i class="fas fa-eye-slash"></i> No viewers tracked yet
          </div>
          <button v-if="post.unique_viewers_count > 10" class="btn-view-all" @click="showAllViewers = true">
            View all {{ post.unique_viewers_count }} viewers
          </button>
        </div>

        <!-- All Viewers Modal -->
        <div v-if="showAllViewers" class="viewers-panel">
          <div class="viewers-panel-header">
            <h4><i class="fas fa-users"></i> All Viewers ({{ allViewersTotal }})</h4>
            <button class="modal-close-btn" @click="showAllViewers = false"><i class="fas fa-times"></i></button>
          </div>
          <div v-if="loadingViewers" class="text-center py-3"><i class="fas fa-spinner fa-spin"></i></div>
          <div v-else class="viewers-list">
            <div v-for="viewer in allViewers" :key="viewer.id" class="viewer-item">
              <img :src="viewer.avatar || '/vendor/adminlte/dist/img/user-default.jpg'" :alt="viewer.user_name" class="viewer-avatar">
              <div class="viewer-info">
                <span class="viewer-name">{{ viewer.user_name }}</span>
                <span class="viewer-email">{{ viewer.email }}</span>
                <span class="viewer-time">{{ formatDate(viewer.viewed_at) }}</span>
              </div>
            </div>
          </div>
          <div v-if="allViewersLastPage > 1" class="viewers-pagination">
            <button :disabled="allViewersPage <= 1" @click="loadAllViewers(allViewersPage - 1)">Previous</button>
            <span>Page {{ allViewersPage }} / {{ allViewersLastPage }}</span>
            <button :disabled="allViewersPage >= allViewersLastPage" @click="loadAllViewers(allViewersPage + 1)">Next</button>
          </div>
        </div>
      </div>

      <div class="modal-footer-actions">
        <button class="btn-action cancel" @click="$emit('close')">Close</button>
        <button class="btn-action primary" @click="$emit('edit', post.id)">
          <i class="fas fa-edit"></i> Edit Post
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'

const props = defineProps({ postId: { type: Number, required: true } })
defineEmits(['close', 'edit'])

const post = ref(null)
const loading = ref(true)
const error = ref('')

// All viewers panel state
const showAllViewers = ref(false)
const loadingViewers = ref(false)
const allViewers = ref([])
const allViewersPage = ref(1)
const allViewersLastPage = ref(1)
const allViewersTotal = ref(0)

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
const truncate = (s, n) => s && s.length > n ? s.substring(0, n) + '...' : s
const photoUrl = (photo) => {
  if (!photo || !photo.src) return '/storage/photos/og-image.jpg'
  if (photo.is_external || photo.src.startsWith('http')) return photo.src
  return photo.src.startsWith('/') ? photo.src : '/' + photo.src
}

const loadAllViewers = async (page = 1) => {
  loadingViewers.value = true
  try {
    const res = await fetch(`/api/admin/service-posts/${props.postId}/viewers?page=${page}&per_page=20`)
    const data = await res.json()
    allViewers.value = data.viewers?.data || []
    allViewersPage.value = data.viewers?.current_page || 1
    allViewersLastPage.value = data.viewers?.last_page || 1
    allViewersTotal.value = data.unique_viewers || 0
  } catch (e) {
    console.error('Failed to load viewers:', e)
  } finally {
    loadingViewers.value = false
  }
}

watch(showAllViewers, (val) => {
  if (val) loadAllViewers(1)
})

onMounted(async () => {
  try {
    const res = await fetch(`/api/admin/service-posts/${props.postId}`)
    if (!res.ok) throw new Error('Failed to load post')
    const data = await res.json()
    post.value = data.post
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-container { background: white; border-radius: 20px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); animation: slideIn 0.3s ease; }
.modal-lg { max-width: 800px; }
@keyframes slideIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
.modal-header-gradient { padding: 1.25rem 2rem; background: linear-gradient(135deg, #667eea, #764ba2); color: white; display: flex; justify-content: space-between; align-items: center; }
.modal-header-gradient h2 { margin: 0; font-size: 1.3rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
.modal-close-btn { background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.modal-close-btn:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); transition: all 0.3s; }
.modal-body { padding: 1.5rem 2rem; max-height: 65vh; overflow-y: auto; }
.photos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 8px; }
.photo-item img { width: 100%; height: 110px; object-fit: cover; border-radius: 10px; border: 1px solid #eee; }
.post-title { font-size: 1.2rem; font-weight: 700; color: #1a1a2e; }
.badge-status { padding: 0.3rem 0.9rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; color: white; }
.bg-success { background: #28a745; }
.bg-warning { background: #ffc107; color: #333 !important; }
.info-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; }
.info-card { background: #f8f9fa; border-radius: 10px; padding: 0.6rem 0.9rem; }
.info-card label { display: block; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; color: #6c757d; margin-bottom: 2px; }
.info-card span { font-size: 0.85rem; font-weight: 500; color: #2c3e50; }
.form-label-bold { font-weight: 700; font-size: 0.85rem; color: #2c3e50; }
.description-box { background: #f8f9fa; border-radius: 10px; padding: 1rem; white-space: pre-wrap; font-size: 0.85rem; line-height: 1.6; max-height: 180px; overflow-y: auto; }
.translations-list { display: flex; flex-direction: column; gap: 4px; }
.translation-item { display: flex; align-items: center; gap: 8px; font-size: 0.8rem; padding: 0.35rem 0.7rem; background: #f0f2ff; border-radius: 8px; }
.locale-badge { background: #667eea; color: white; padding: 1px 7px; border-radius: 5px; font-size: 0.65rem; font-weight: 700; min-width: 24px; text-align: center; }
.modal-footer-actions { padding: 1rem 2rem; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #e9ecef; }
.btn-action { padding: 0.65rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; gap: 0.4rem; }
.btn-action.cancel { background: white; border: 2px solid #e9ecef; color: #6c757d; }
.btn-action.primary { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
.btn-action.primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102,126,234,0.4); }
.viewers-count-badge { background: #667eea; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 6px; font-weight: 600; }
.viewers-list { display: flex; flex-direction: column; gap: 6px; max-height: 300px; overflow-y: auto; }
.viewer-item { display: flex; align-items: center; gap: 10px; padding: 6px 10px; background: #f8f9fa; border-radius: 10px; }
.viewer-avatar { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid #e9ecef; }
.viewer-info { display: flex; flex-direction: column; gap: 1px; }
.viewer-name { font-size: 0.82rem; font-weight: 600; color: #2c3e50; }
.viewer-email { font-size: 0.72rem; color: #888; }
.viewer-time { font-size: 0.7rem; color: #999; }
.no-viewers { text-align: center; padding: 1rem; color: #999; font-size: 0.85rem; background: #f8f9fa; border-radius: 10px; }
.no-viewers i { margin-right: 6px; }
.btn-view-all { display: block; width: 100%; margin-top: 8px; padding: 8px; background: #f0f2ff; border: 1px solid #d0d5ff; color: #667eea; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.82rem; }
.btn-view-all:hover { background: #e0e4ff; }
.viewers-panel { margin-top: 12px; background: white; border: 2px solid #667eea; border-radius: 14px; padding: 1rem; }
.viewers-panel-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.viewers-panel-header h4 { margin: 0; font-size: 1rem; color: #2c3e50; display: flex; align-items: center; gap: 8px; }
.viewers-pagination { display: flex; justify-content: center; align-items: center; gap: 12px; margin-top: 10px; padding-top: 10px; border-top: 1px solid #eee; }
.viewers-pagination button { padding: 4px 12px; border: 1px solid #ddd; border-radius: 8px; background: white; cursor: pointer; font-size: 0.8rem; }
.viewers-pagination button:disabled { opacity: 0.4; cursor: not-allowed; }
.viewers-pagination span { font-size: 0.8rem; color: #666; }
</style>
