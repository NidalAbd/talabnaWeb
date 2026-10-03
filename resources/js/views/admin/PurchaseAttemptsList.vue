<template>
  <div class="pa">
    <div class="pa-head">
      <h2>Purchase attempts</h2>
      <p class="muted">Every “Buy” tap in the app — logged before the store opens — and how it ended.</p>
      <div class="range">
        <button v-for="d in [1, 7, 30, 90]" :key="d" :class="{ on: days === d }" @click="days = d; loadStats()">
          {{ d === 1 ? '24h' : d + ' days' }}
        </button>
      </div>
    </div>

    <div v-if="stats" class="cards">
      <div class="card"><div class="v">{{ stats.total }}</div><div class="l">Attempts</div></div>
      <div class="card ok"><div class="v">{{ s('completed') }}</div><div class="l">Completed</div></div>
      <div class="card"><div class="v">{{ stats.conversion_rate }}%</div><div class="l">Conversion</div></div>
      <div class="card warn"><div class="v">{{ s('cancelled') }}</div><div class="l">Cancelled</div></div>
      <div class="card bad"><div class="v">{{ s('failed') + s('abandoned') }}</div><div class="l">Failed / no response</div></div>
      <div class="card"><div class="v">{{ s('pending') + s('attempted') }}</div><div class="l">In progress</div></div>
      <div class="card warn"><div class="v">{{ stats.stuck_now }}</div><div class="l">Stuck &gt; 10 min</div></div>
      <div class="card"><div class="v">{{ stats.tried_never_bought_users }}</div><div class="l">Tried, never bought</div></div>
    </div>

    <div v-if="stats" class="grid2">
      <div class="panel">
        <h3>By product</h3>
        <table>
          <thead><tr><th>Product</th><th>Attempts</th><th>Completed</th><th>Cancelled</th><th>Failed</th><th>Conv.</th></tr></thead>
          <tbody>
            <tr v-for="p in stats.by_product" :key="p.product_id">
              <td>{{ p.product_id }}</td><td>{{ p.attempts }}</td><td>{{ p.completed }}</td><td>{{ p.cancelled }}</td><td>{{ p.failed }}</td>
              <td>{{ p.attempts ? Math.round((p.completed * 100) / p.attempts) : 0 }}%</td>
            </tr>
            <tr v-if="!stats.by_product.length"><td colspan="6" class="muted">No attempts in this period</td></tr>
          </tbody>
        </table>
      </div>
      <div class="panel">
        <h3>Why purchases fail</h3>
        <table>
          <tbody>
            <tr v-for="e in stats.top_errors" :key="e.reason"><td>{{ e.reason }}</td><td class="num">{{ e.n }}</td></tr>
            <tr v-if="!stats.top_errors.length"><td class="muted">No failures 🎉</td></tr>
          </tbody>
        </table>
        <h3 style="margin-top:16px">By platform</h3>
        <table>
          <tbody>
            <tr v-for="p in stats.by_platform" :key="p.platform"><td>{{ p.platform }}</td><td class="num">{{ p.completed }} / {{ p.attempts }}</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="filters">
      <input v-model="filters.search" @input="debounced" placeholder="Search user name, email or id…" />
      <select v-model="filters.status" @change="load(1)">
        <option value="">All statuses</option>
        <option v-for="st in statuses" :key="st" :value="st">{{ st }}</option>
      </select>
      <select v-model="filters.platform" @change="load(1)">
        <option value="">All platforms</option><option value="android">Android</option><option value="ios">iOS</option>
      </select>
      <input type="date" v-model="filters.from" @change="load(1)" />
      <input type="date" v-model="filters.to" @change="load(1)" />
    </div>

    <div class="panel">
      <div v-if="loading" class="muted">Loading…</div>
      <table v-else>
        <thead><tr><th>#</th><th>User</th><th>Product</th><th>Platform</th><th>Price</th><th>Status</th><th>Reason</th><th>App</th><th>Tapped</th><th>Resolved</th></tr></thead>
        <tbody>
          <tr v-for="a in rows.data" :key="a.id">
            <td>{{ a.id }}</td>
            <td>{{ a.user ? (a.user.name || a.user.user_name) : '#' + a.user_id }}<div class="muted small">{{ a.user?.email }}</div></td>
            <td>{{ a.product_id }}</td>
            <td>{{ a.platform }}</td>
            <td>{{ a.price ? a.price + ' ' + (a.currency || '') : '—' }}</td>
            <td><span class="badge" :class="a.status">{{ a.status }}</span></td>
            <td class="small">{{ a.error_code || a.error_message || '' }}</td>
            <td class="small">{{ a.app_version || '' }}</td>
            <td class="small">{{ fmt(a.created_at) }}</td>
            <td class="small">{{ a.resolved_at ? fmt(a.resolved_at) : '' }}</td>
          </tr>
          <tr v-if="!rows.data.length"><td colspan="10" class="muted">No attempts</td></tr>
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

const statuses = ['attempted', 'pending', 'completed', 'cancelled', 'failed', 'abandoned']
const days = ref(7)
const stats = ref(null)
const rows = ref({ data: [], current_page: 1, last_page: 1 })
const loading = ref(false)
const filters = ref({ search: '', status: '', platform: '', from: '', to: '' })
let t = null

const s = (k) => Number(stats.value?.by_status?.[k] || 0)
const fmt = (d) => new Date(d).toLocaleString()

async function getJson(url) {
  const r = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
  if (!r.ok) throw new Error('HTTP ' + r.status)
  return r.json()
}

async function loadStats() {
  try { stats.value = await getJson(`/api/admin/purchase-attempts/stats?days=${days.value}`) } catch (e) { console.error(e) }
}

async function load(page = 1) {
  loading.value = true
  const p = new URLSearchParams({ page, per_page: 25 })
  for (const [k, v] of Object.entries(filters.value)) if (v) p.append(k, v)
  try { rows.value = await getJson(`/api/admin/purchase-attempts?${p}`) } catch (e) { console.error(e) }
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
.badge.completed { background: #d1fae5; color: #065f46; }
.badge.cancelled { background: #fef3c7; color: #92400e; }
.badge.failed, .badge.abandoned { background: #fee2e2; color: #991b1b; }
.badge.pending, .badge.attempted { background: #e0e7ff; color: #3730a3; }
.pager { display: flex; gap: 8px; align-items: center; justify-content: flex-end; margin-top: 8px; }
@media (prefers-color-scheme: dark) {
  .card, .panel { background: #111827; border-color: #1f2937; }
  th, td { border-color: #1f2937; }
}
</style>
