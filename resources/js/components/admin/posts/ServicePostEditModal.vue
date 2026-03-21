<template>
  <div class="modal-overlay" @click.self="$emit('close')">
    <div class="modal-container modal-lg">
      <div class="modal-header-gradient edit">
        <h2><i class="fas fa-edit"></i> Edit Post #{{ postId }}</h2>
        <button class="modal-close-btn" @click="$emit('close')"><i class="fas fa-times"></i></button>
      </div>

      <div v-if="loading" class="modal-body text-center py-5">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p class="mt-3">Loading post...</p>
      </div>

      <div v-else-if="post" class="modal-body">
        <form @submit.prevent="savePost">
          <!-- Status & Type -->
          <div class="form-row">
            <div class="form-group">
              <label>Status</label>
              <select v-model="form.state" class="form-input">
                <option value="published">Published</option>
                <option value="not published">Not Published</option>
              </select>
            </div>
            <div class="form-group">
              <label>Type</label>
              <select v-model="form.type" class="form-input">
                <option value="عرض">Offer (عرض)</option>
                <option value="طلب">Request (طلب)</option>
              </select>
            </div>
            <div class="form-group">
              <label>Price</label>
              <input v-model.number="form.price" type="number" class="form-input" min="0" step="0.01">
            </div>
          </div>

          <!-- Title Translations -->
          <div class="section-header">
            <label class="form-label-bold">Title Translations</label>
          </div>
          <div class="tabs-container">
            <button v-for="locale in availableLocales" :key="'t-'+locale" type="button"
              class="tab-btn" :class="{ active: activeTab === locale }" @click="activeTab = locale">
              {{ locale }}
              <span v-if="form.title[locale]" class="check">&#10003;</span>
            </button>
          </div>
          <div v-for="locale in availableLocales" :key="'ti-'+locale" v-show="activeTab === locale">
            <input v-model="form.title[locale]" type="text" class="form-input"
              :placeholder="`Title in ${locale}`"
              :dir="['ar','he','fa','ur','ps','ku'].includes(locale) ? 'rtl' : 'ltr'">
          </div>

          <!-- Description Translations -->
          <div class="section-header mt-4">
            <label class="form-label-bold">Description Translations</label>
          </div>
          <div class="tabs-container">
            <button v-for="locale in availableLocales" :key="'d-'+locale" type="button"
              class="tab-btn" :class="{ active: descTab === locale }" @click="descTab = locale">
              {{ locale }}
              <span v-if="form.description[locale]" class="check">&#10003;</span>
            </button>
          </div>
          <div v-for="locale in availableLocales" :key="'di-'+locale" v-show="descTab === locale">
            <textarea v-model="form.description[locale]" class="form-input form-textarea" rows="4"
              :placeholder="`Description in ${locale}`"
              :dir="['ar','he','fa','ur','ps','ku'].includes(locale) ? 'rtl' : 'ltr'"></textarea>
          </div>

          <!-- Category -->
          <div class="form-row mt-4">
            <div class="form-group">
              <label>Category</label>
              <select v-model="form.categories_id" class="form-input" @change="loadSubcategories">
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ getLocalName(cat.name) }}</option>
              </select>
            </div>
            <div class="form-group">
              <label>Subcategory</label>
              <select v-model="form.sub_categories_id" class="form-input">
                <option v-for="sub in subcategories" :key="sub.id" :value="sub.id">{{ getLocalName(sub.name) }}</option>
              </select>
            </div>
          </div>

          <!-- Photos Preview -->
          <div v-if="post.photos && post.photos.length" class="mt-4">
            <label class="form-label-bold">Photos ({{ post.photos.length }})</label>
            <div class="photos-preview">
              <div v-for="photo in post.photos" :key="photo.id" class="photo-thumb">
                <img :src="photo.src" @error="$event.target.src='/storage/photos/og-image.jpg'">
              </div>
            </div>
          </div>

          <!-- Error -->
          <div v-if="saveError" class="error-msg mt-3">
            <i class="fas fa-exclamation-circle"></i> {{ saveError }}
          </div>

          <!-- Success -->
          <div v-if="saveSuccess" class="success-msg mt-3">
            <i class="fas fa-check-circle"></i> Post updated successfully!
          </div>
        </form>
      </div>

      <div class="modal-footer-actions">
        <button class="btn-action cancel" @click="$emit('close')">Cancel</button>
        <button class="btn-action save" @click="savePost" :disabled="saving">
          <i :class="saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'

const props = defineProps({ postId: { type: Number, required: true } })
const emit = defineEmits(['close', 'saved'])

const post = ref(null)
const loading = ref(true)
const saving = ref(false)
const saveError = ref('')
const saveSuccess = ref(false)
const activeTab = ref('ar')
const descTab = ref('ar')
const categories = ref([])
const subcategories = ref([])

const form = reactive({
  title: {},
  description: {},
  price: 0,
  type: '',
  state: '',
  categories_id: null,
  sub_categories_id: null,
})

const availableLocales = ['ar', 'en', 'hi', 'tr', 'fr', 'es', 'ur', 'bn', 'pt', 'ru', 'id', 'de', 'zh', 'ku', 'fa', 'sw', 'ms']

const getLocalName = (name) => {
  if (typeof name === 'string') return name
  if (typeof name === 'object') return name.ar || name.en || Object.values(name)[0] || ''
  return ''
}

const loadSubcategories = async () => {
  if (!form.categories_id) return
  try {
    const res = await fetch(`/api/admin/service-posts/subcategories?category_id=${form.categories_id}`)
    const data = await res.json()
    subcategories.value = data.subcategories || []
  } catch (e) { console.error(e) }
}

const savePost = async () => {
  saving.value = true
  saveError.value = ''
  saveSuccess.value = false
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    const res = await fetch(`/api/admin/service-posts/${props.postId}`, {
      method: 'PUT',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      credentials: 'same-origin',
      body: JSON.stringify({
        title: form.title,
        description: form.description,
        price: form.price,
        type: form.type,
        state: form.state,
        categories_id: form.categories_id,
        sub_categories_id: form.sub_categories_id,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.error || data.message || 'Update failed')
    saveSuccess.value = true
    emit('saved')
    setTimeout(() => { saveSuccess.value = false }, 3000)
  } catch (e) {
    saveError.value = e.message
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    // Load post + categories in parallel
    const [postRes, catRes] = await Promise.all([
      fetch(`/api/admin/service-posts/${props.postId}`),
      fetch('/api/admin/service-posts/categories'),
    ])
    const postData = await postRes.json()
    const catData = await catRes.json()

    post.value = postData.post
    categories.value = catData.categories || []

    // Populate form
    const p = postData.post
    form.title = p.title_translations || {}
    form.description = p.description_translations || {}
    form.price = p.price || 0
    form.type = p.type || ''
    form.state = p.state || ''
    form.categories_id = p.categories_id
    form.sub_categories_id = p.sub_categories_id

    // Ensure all locales have keys
    for (const loc of availableLocales) {
      if (!form.title[loc]) form.title[loc] = ''
      if (!form.description[loc]) form.description[loc] = ''
    }

    // Load subcategories
    if (form.categories_id) await loadSubcategories()
  } catch (e) {
    saveError.value = e.message
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 1rem; }
.modal-container { background: white; border-radius: 20px; width: 100%; max-height: 90vh; overflow: hidden; box-shadow: 0 25px 50px rgba(0,0,0,0.25); }
.modal-lg { max-width: 800px; }
.modal-header-gradient.edit { padding: 1.25rem 2rem; background: linear-gradient(135deg, #f093fb, #f5576c); color: white; display: flex; justify-content: space-between; align-items: center; }
.modal-header-gradient h2 { margin: 0; font-size: 1.3rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem; }
.modal-close-btn { background: rgba(255,255,255,0.2); border: none; width: 36px; height: 36px; border-radius: 50%; color: white; cursor: pointer; display: flex; align-items: center; justify-content: center; }
.modal-body { padding: 1.5rem 2rem; max-height: 65vh; overflow-y: auto; }
.form-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; }
.form-group { display: flex; flex-direction: column; }
.form-group label { font-size: 0.75rem; font-weight: 700; color: #6c757d; text-transform: uppercase; margin-bottom: 4px; }
.form-input { padding: 0.65rem 0.9rem; border: 2px solid #e9ecef; border-radius: 10px; font-size: 0.9rem; width: 100%; box-sizing: border-box; }
.form-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.form-textarea { resize: vertical; min-height: 80px; }
.section-header { margin-bottom: 0.5rem; }
.form-label-bold { font-weight: 700; font-size: 0.85rem; color: #2c3e50; }
.tabs-container { display: flex; gap: 4px; flex-wrap: wrap; margin-bottom: 8px; background: #f8f9fa; padding: 4px; border-radius: 10px; }
.tab-btn { padding: 0.4rem 0.7rem; border: none; background: transparent; border-radius: 8px; font-weight: 600; cursor: pointer; font-size: 0.75rem; color: #6c757d; }
.tab-btn.active { background: white; color: #667eea; box-shadow: 0 1px 4px rgba(0,0,0,0.1); }
.tab-btn .check { color: #10b981; margin-left: 2px; }
.photos-preview { display: flex; gap: 6px; flex-wrap: wrap; }
.photo-thumb img { width: 80px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; }
.error-msg { background: #fee; color: #dc3545; padding: 0.75rem; border-radius: 10px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }
.success-msg { background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 10px; font-size: 0.85rem; display: flex; align-items: center; gap: 0.5rem; }
.modal-footer-actions { padding: 1rem 2rem; background: #f8f9fa; display: flex; justify-content: flex-end; gap: 0.75rem; border-top: 1px solid #e9ecef; }
.btn-action { padding: 0.65rem 1.5rem; border-radius: 12px; font-weight: 600; cursor: pointer; border: none; display: flex; align-items: center; gap: 0.4rem; }
.btn-action.cancel { background: white; border: 2px solid #e9ecef; color: #6c757d; }
.btn-action.save { background: linear-gradient(135deg, #f093fb, #f5576c); color: white; }
.btn-action.save:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245,87,108,0.4); }
.btn-action:disabled { opacity: 0.6; cursor: not-allowed; }
</style>
