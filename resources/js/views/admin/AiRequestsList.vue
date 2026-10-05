<template>
  <div class="pa">
    <div class="pa-head">
      <h2>AI requests</h2>
      <p class="muted">Every paid AI action (images, videos, text tools): what was charged, whether it worked, and whether the points went back when it failed.</p>
      <div class="range">
        <button v-for="d in [1, 7, 30, 90]" :key="d" :class="{ on: days === d }" @click="days = d; loadStats()">
          {{ d === 1 ? '24h' : d + ' days' }}
        </button>
      </div>
    </div>

    <div v-if="stats" class="cards">
      <div class="card"><div class="v">{{ stats.total }}</div><div class="l">Requests</div></div>
      <div class="card ok"><div class="v">{{ s('succeeded') }}</div><div class="l">Succeeded</div></div>
      <div class="card warn"><div class="v">{{ s('failed') }}</div><div class="l">Failed (points returned)</div></div>
      <div class="card"><div class="v">{{ s('processing') }}</div><div class="l">Running</div></div>
      <div class="card warn"><div class="v">{{ stats.stuck_now }}</div><div class="l">Stuck &gt; 10 min</div></div>
      <div class="card bad"><div class="v">{{ stats.needs_admin }}</div><div class="l">Refund failed - fix by hand</div></div>
    </div>

    <div v-if="stats" class="grid2">
      <div class="panel">
        <h3>By feature</h3>
        <table>
          <thead><tr><th>Feature</th><th>Total</th><th>OK</th><th>Failed</th><th>Points kept</th><th>Points returned</th></tr></thead>
          <tbody>
            <tr v-for="f in stats.by_feature" :key="f.feature">
              <td>{{ f.feature }}</td><td>{{ f.total }}</td><td>{{ f.succeeded }}</td>
              <td>{{ Number(f.failed) + Number(f.refund_failed) }}</td><td>{{ f.points_earned }}</td><td>{{ f.points_refunded }}</td>
            </tr>
            <tr v-if="!stats.by_feature.length"><td colspan="6" class="muted">No requests in this period</td></tr>
          </tbody>
        </table>
      </div>
      <div class="panel">
        <h3>Why requests fail</h3>
        <table>
          <tbody>
            <tr v-for="e in stats.top_errors" :key="e.reason"><td>{{ e.reason }}</td><td class="num">{{ e.n }}</td></tr>
            <tr v-if="!stats.top_errors.length"><td class="muted">No failures</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="filters">
      <input v-model="filters.search" @input="debounced" placeholder="Search user name, email, id or request id…" />
      <select v-model="filters.status" @change="load(1)">
        <option value="">All statuses</option>
        <option v-for="st in statuses" :key="st" :value="st">{{ st }}</option>
      </select>
      <select v-model="filters.feature" @change="load(1)">
        <option value="">All features</option>
        <option v-for="f in features" :key="f" :value="f">{{ f }}</option>
      </select>
      <input type="date" v-model="filters.from" @change="load(1)" />
      <input type="date" v-model="filters.to" @change="load(1)" />
    </div>

    <div class="panel">
      <div v-if="loading" class="muted">Loading…</div>
      <table v-else>
        <thead><tr><th>#</th><th>User</th><th>Feature</th><th>Points</th><th>Status</th><th>Refund</th><th>Reason</th><th>Took</th><th>Started</th><th>Finished</th></tr></thead>
        <tbody>
          <tr v-for="r in rows.data" :key="r.id">
            <td>{{ r.id }}<div class="muted small mono">{{ r.uuid?.slice(0, 8) }}</div></td>
            <td>{{ r.user ? (r.user.name || r.user.user_name) : '#' + r.user_id }}<div class="muted small">{{ r.user?.email }}</div></td>
            <td>{{ r.feature }}</td>
            <td>{{ r.points > 0 ? r.points : 'included' }}<div class="muted small" v-if="r.charge_transaction_id">charge #{{ r.charge_transaction_id }}</div></td>
            <td><span class="badge" :class="r.status">{{ r.status }}</span></td>
            <td class="small">
              <template v-if="r.refunded_at">returned{{ r.refund_transaction_id ? ' #' + r.refund_transaction_id : ' (allowance)' }}<div class="muted">{{ fmt(r.refunded_at) }}</div></template>
              <template v-else-if="r.status === 'refund_failed'">NOT returned ({{ r.refund_attempts }} tries)</template>
              <template v-else>—</template>
            </td>
            <td class="small">{{ r.error_code || '' }}<div class="muted">{{ r.error_message || '' }}</div></td>
            <td class="small">{{ r.duration_ms ? (r.duration_ms / 1000).toFixed(1) + ' s' : '' }}</td>
            <td class="small">{{ fmt(r.created_at) }}</td>
            <td class="small">{{ r.completed_at ? fmt(r.completed_at) : '' }}</td>
          </tr>
          <tr v-if="!rows.data.length"><td colspan="10" class="muted">No requests</td></tr>
        </tbody>
      </table>
      <div class="pager" v-if="rows.last_page > 1">
        <button :disabled="rows.current_page <= 1" @click="load(rows.current_page - 1)">‹</button>
        <span>{{ rows.current_page }} / {{ rows.last_page }}</span>
        <button :disabled="rows.current_page >= rows.last_page" @click="load(rows.current_page + 1)">›</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const statuses = ['processing', 'succeeded', 'failed', 'refund_failed']
const features = ['generate_image', 'generate_video', 'enhance_post', 'translate_post', 'suggest_category']
const days = ref(7)
const stats = ref(null)
const rows = ref({ data: [], current_page: 1, last_page: 1 })
const loading = ref(false)
const filters = ref({ search: '', status: '', feature: '', from: '', to: '' })
let t = null

const s = (k) => Number(stats.value?.by_status?.[k] || 0)
const fmt = (d) => new Date(d).toLocaleString()

async function getJson(url) {
  const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
  if (!r.ok) throw new Error('HTTP ' + r.status)
  return r.json()
}

async function loadStats() {
  try { stats.value = await getJson(`/api/admin/ai-requests/stats?days=${days.value}`) } catch (e) { console.error(e) }
}

async function load(page = 1) {
  loading.value = true
  const p = new URLSearchParams({ page, per_page: 25 })
  for (const [k, v] of Object.entries(filters.value)) if (v) p.append(k, v)
  try { rows.value = await getJson(`/api/admin/ai-requests?${p}`) } catch (e) { console.error(e) }
  loading.value = false
}

const debounced = () => { clearTimeout(t); t = setTimeout(() => load(1), 400) }

onMounted(() => { loadStats(); load(1) })
</script>

<style scoped>
.pa { display: flex; flex-direction: column; gap: 16px; }
.pa-head h2 { margin: 0; font-weight: 700; }
.muted { color: #6b7280; }
.small { font-size: 12px; }
.mono { font-family: ui-monospace, monospace; }
.range { display: flex; gap: 6px; margin-top: 8px; }
.range button, .pager button { border: 1px solid #d1d5db; background: #fff; border-radius: 8px; padding: 4px 10px; cursor: pointer; }
.range button.on { background: #111827; color: #fff; border-color: #111827; }
.cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
.card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; }
.card .v { font-size: 24px; font-weight: 800; }
.card .l { font-size: 12px; color: #6b7280; }
.card.ok .v { color: #059669; } .card.warn .v { color: #d97706; } .card.bad .v { color: #dc2626; }
.grid2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 12px; }
.panel { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 12px; overflow-x: auto; }
.panel h3 { font-size: 15px; margin: 0 0 8px; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th, td { text-align: left; padding: 6px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
td.num { text-align: right; font-weight: 600; }
.filters { display: flex; flex-wrap: wrap; gap: 8px; }
.filters input, .filters select { border: 1px solid #d1d5db; border-radius: 8px; padding: 6px 10px; }
.badge { padding: 2px 8px; border-radius: 999px; font-size: 12px; background: #f3f4f6; }
.badge.succeeded { background: #d1fae5; color: #065f46; }
.badge.failed { background: #fef3c7; color: #92400e; }
.badge.refund_failed { background: #fee2e2; color: #991b1b; }
.badge.processing { background: #e0e7ff; color: #3730a3; }
.pager { display: flex; gap: 8px; align-items: center; justify-content: flex-end; margin-top: 8px; }
@media (prefers-color-scheme: dark) {
  .card, .panel { background: #111827; border-color: #1f2937; }
  th, td { border-color: #1f2937; }
}
</style>
