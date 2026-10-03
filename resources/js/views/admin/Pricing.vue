<template>
  <div class="pr">
    <div class="pr-head">
      <h2>Point prices</h2>
      <p class="muted">What actions cost in points. Changes apply immediately in the app. Plan and badge prices have their own pages.</p>
    </div>

    <div v-if="loading" class="muted">Loading…</div>
    <template v-else>
      <div class="panel">
        <h3>AI actions</h3>
        <table>
          <thead><tr><th>Action</th><th>Points</th><th>Enabled</th></tr></thead>
          <tbody>
            <tr v-for="f in form.ai_features" :key="f.key">
              <td>{{ labels[f.key] || f.key }}<div class="muted small">{{ f.key }}</div></td>
              <td><input type="number" min="0" v-model.number="f.points_cost" class="num" /></td>
              <td><input type="checkbox" v-model="f.enabled" /></td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="panel">
        <h3>Photos &amp; videos per post</h3>
        <div class="grid3">
          <label>Free per post<input type="number" min="0" v-model.number="form.media.free" class="num" /></label>
          <label>Maximum per post<input type="number" min="1" v-model.number="form.media.max" class="num" /></label>
          <label>Points per extra photo<input type="number" min="0" v-model.number="form.media.extra_points" class="num" /></label>
        </div>
        <p class="muted small">Subscribers get at least their plan's “photos per post” for free.</p>
      </div>

      <div class="panel">
        <h3>Limits</h3>
        <label class="lim">AI images + videos per user per day
          <input type="number" min="1" v-model.number="form.limits.daily_media_per_user" class="num" />
        </label>
        <p class="muted small">Stops anyone from generating without end, even with points. Max 2 images and 1 video at the same time.</p>
        <label class="lim" style="margin-top:12px">Ask the user to confirm when an AI action costs at least (points)
          <input type="number" min="1" v-model.number="form.limits.confirm_from" class="num" />
        </label>
        <p class="muted small">1 = always ask before taking points. Actions included in the user's plan are free and never ask.</p>
      </div>

      <div class="actions">
        <span v-if="message" :class="ok ? 'okmsg' : 'errmsg'">{{ message }}</span>
        <button class="save" :disabled="saving" @click="save">{{ saving ? 'Saving…' : 'Save prices' }}</button>
      </div>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const labels = {
  enhance_post: 'Improve post text', generate_image: 'Generate image', generate_video: 'Generate video',
  suggest_category: 'Suggest category', suggest_price: 'Suggest price', translate_post: 'Translate post',
}
const loading = ref(true)
const saving = ref(false)
const message = ref('')
const ok = ref(true)
const form = ref({ ai_features: [], media: { free: 4, max: 10, extra_points: 1 }, limits: { daily_media_per_user: 30, confirm_from: 1 } })

async function load() {
  loading.value = true
  try {
    const r = await fetch('/api/admin/pricing', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
    form.value = await r.json()
  } finally {
    loading.value = false
  }
}

async function save() {
  saving.value = true
  message.value = ''
  try {
    const r = await fetch('/api/admin/pricing', {
      method: 'PUT',
      credentials: 'same-origin',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify(form.value),
    })
    const d = await r.json()
    ok.value = r.ok
    if (r.ok) { form.value = d; message.value = 'Saved ✓' } else { message.value = d.message || 'Could not save' }
  } catch (e) {
    ok.value = false
    message.value = 'Could not save'
  } finally {
    saving.value = false
  }
}

onMounted(load)
</script>

<style scoped>
.pr { display: flex; flex-direction: column; gap: 16px; max-width: 820px; }
.pr-head h2 { margin: 0; font-weight: 700; }
.muted { color: #6b7280; } .small { font-size: 12px; }
.panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 14px; }
.panel h3 { font-size: 15px; margin: 0 0 10px; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th, td { text-align: left; padding: 8px; border-bottom: 1px solid #f3f4f6; }
.num { width: 90px; border: 1px solid #d1d5db; border-radius: 8px; padding: 5px 8px; }
.grid3 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
.grid3 label { display: flex; flex-direction: column; gap: 6px; font-size: 13px; }
.lim { display: flex; flex-direction: column; gap: 6px; font-size: 13px; max-width: 280px; }
.actions { display: flex; justify-content: flex-end; align-items: center; gap: 12px; }
.save { background: #111827; color: #fff; border: 0; border-radius: 10px; padding: 8px 18px; cursor: pointer; }
.okmsg { color: #059669; } .errmsg { color: #dc2626; }
@media (prefers-color-scheme: dark) { .panel { background: #111827; border-color: #1f2937; } th, td { border-color: #1f2937; } }
</style>
