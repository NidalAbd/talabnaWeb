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
import { ref, onMounted } from 'vue'

const props = defineProps({ postId: { type: Number, required: true } })
defineEmits(['close', 'edit'])

const post = ref(null)
const loading = ref(true)
const error = ref('')

const formatDate = (d) => d ? new Date(d).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-'
const truncate = (s, n) => s && s.length > n ? s.substring(0, n) + '...' : s
const photoUrl = (photo) => {
  if (!photo || !photo.src) return '/storage/photos/og-image.jpg'
  if (photo.is_external || photo.src.startsWith('http')) return photo.src
  return photo.src.startsWith('/') ? photo.src : '/' + photo.src
}

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
</style>
