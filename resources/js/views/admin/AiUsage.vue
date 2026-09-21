<template>
    <div class="ai-usage">
        <div class="bar">
            <span class="label">Period:</span>
            <button v-for="p in periods" :key="p.hours" class="chip" :class="{ active: hours === p.hours }" @click="setHours(p.hours)">{{ p.label }}</button>
            <span class="spacer"></span>
            <button class="chip" @click="load">Refresh</button>
        </div>

        <p v-if="error" class="error">{{ error }}</p>

        <template v-if="summary">
            <div v-if="attentionCount || integrityCount" class="alert">
                <strong>Needs attention</strong>
                <ul>
                    <li v-if="summary.attention.refund_failed">{{ summary.attention.refund_failed }} request(s) could not be refunded automatically. Use "Refund" below.</li>
                    <li v-if="summary.attention.overdue_processing">{{ summary.attention.overdue_processing }} request(s) have been processing for over 30 minutes.</li>
                    <li v-if="summary.integrity.charge_without_request">{{ summary.integrity.charge_without_request }} AI charge(s) have no request record.</li>
                    <li v-if="summary.integrity.failed_without_refund">{{ summary.integrity.failed_without_refund }} failed request(s) have no refund record.</li>
                    <li v-if="summary.integrity.refunded_twice">{{ summary.integrity.refunded_twice }} request(s) were refunded more than once.</li>
                </ul>
            </div>
            <div v-else class="ok">All charges and refunds reconcile.</div>

            <div class="cards">
                <div class="card"><div class="num">{{ summary.totals.requests }}</div><div class="cap">Requests</div></div>
                <div class="card"><div class="num">{{ summary.totals.succeeded }}</div><div class="cap">Succeeded</div></div>
                <div class="card"><div class="num">{{ summary.totals.failed }}</div><div class="cap">Failed (refunded)</div></div>
                <div class="card"><div class="num">{{ summary.totals.points_earned }}</div><div class="cap">Points earned</div></div>
                <div class="card"><div class="num">{{ summary.totals.points_refunded }}</div><div class="cap">Points refunded</div></div>
            </div>

            <div class="scroll">
                <table>
                    <thead><tr><th>Feature</th><th>Requests</th><th>Succeeded</th><th>Failed</th><th>Processing</th><th>Refund failed</th><th>Points earned</th><th>Points refunded</th></tr></thead>
                    <tbody>
                        <tr v-for="f in summary.by_feature" :key="f.feature">
                            <td>{{ f.feature }}</td><td>{{ f.requests }}</td><td>{{ f.succeeded }}</td><td>{{ f.failed }}</td>
                            <td>{{ f.processing }}</td><td>{{ f.refund_failed }}</td><td>{{ f.points_earned }}</td><td>{{ f.points_refunded }}</td>
                        </tr>
                        <tr v-if="!summary.by_feature.length"><td colspan="8" class="empty">No AI activity in this period.</td></tr>
                    </tbody>
                </table>
            </div>
        </template>

        <h3>Requests</h3>
        <div class="bar">
            <select v-model="filters.status" @change="loadRequests(1)">
                <option value="">Any status</option>
                <option v-for="s in statuses" :key="s" :value="s">{{ s }}</option>
            </select>
            <select v-model="filters.feature" @change="loadRequests(1)">
                <option value="">Any feature</option>
                <option v-for="f in features" :key="f" :value="f">{{ f }}</option>
            </select>
            <input v-model="filters.user_id" placeholder="User id" @keyup.enter="loadRequests(1)" />
        </div>

        <div class="scroll">
            <table>
                <thead><tr><th>When</th><th>User</th><th>Feature</th><th>Points</th><th>Status</th><th>Error</th><th>Time</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="r in requests.data" :key="r.id">
                        <td>{{ formatDate(r.created_at) }}</td>
                        <td>{{ r.user?.user_name || r.user_id }}</td>
                        <td>{{ r.feature }}</td>
                        <td>{{ r.points }}</td>
                        <td><span class="badge" :class="'s-' + r.status">{{ r.status }}</span><span v-if="r.refunded_at" class="badge s-refunded">refunded</span></td>
                        <td :title="r.error_message">{{ r.error_code }}</td>
                        <td>{{ r.duration_ms ? (r.duration_ms / 1000).toFixed(1) + 's' : '' }}</td>
                        <td><button v-if="!r.refunded_at && r.status !== 'processing'" class="chip" @click="refund(r)">Refund</button></td>
                    </tr>
                    <tr v-if="!requests.data.length"><td colspan="8" class="empty">Nothing here.</td></tr>
                </tbody>
            </table>
        </div>
        <div class="bar">
            <button class="chip" :disabled="requests.current_page <= 1" @click="loadRequests(requests.current_page - 1)">Previous</button>
            <span>Page {{ requests.current_page }} / {{ requests.last_page }}</span>
            <button class="chip" :disabled="requests.current_page >= requests.last_page" @click="loadRequests(requests.current_page + 1)">Next</button>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'

const periods = [
    { hours: 24, label: '24h' },
    { hours: 168, label: '7 days' },
    { hours: 720, label: '30 days' },
]
const statuses = ['processing', 'succeeded', 'failed', 'refund_failed']
const features = ['enhance_post', 'translate_post', 'suggest_category', 'suggest_price', 'generate_image', 'generate_video']

const hours = ref(24)
const summary = ref(null)
const error = ref(null)
const requests = ref({ data: [], current_page: 1, last_page: 1 })
const filters = reactive({ status: '', feature: '', user_id: '' })

const attentionCount = computed(() => summary.value ? summary.value.attention.refund_failed + summary.value.attention.overdue_processing : 0)
const integrityCount = computed(() => summary.value ? Object.values(summary.value.integrity).reduce((a, b) => a + b, 0) : 0)

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content

async function getJson(url) {
    const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
    if (!res.ok) throw new Error(`Request failed (${res.status})`)
    return res.json()
}

async function load() {
    error.value = null
    try {
        summary.value = await getJson(`/api/admin/ai-usage/summary?hours=${hours.value}`)
        await loadRequests(1)
    } catch (e) {
        error.value = e.message
    }
}

async function loadRequests(page) {
    const q = new URLSearchParams({ page })
    Object.entries(filters).forEach(([k, v]) => v && q.set(k, v))
    try {
        requests.value = await getJson(`/api/admin/ai-usage/requests?${q}`)
    } catch (e) {
        error.value = e.message
    }
}

async function refund(r) {
    if (!confirm(`Give ${r.points} point(s) back to user ${r.user?.user_name || r.user_id}?`)) return
    const res = await fetch(`/api/admin/ai-usage/requests/${r.id}/refund`, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() || '' },
        credentials: 'same-origin',
    })
    if (!res.ok) error.value = `Refund failed (${res.status})`
    await load()
}

function setHours(h) { hours.value = h; load() }
function formatDate(v) { return v ? new Date(v).toLocaleString() : '' }

onMounted(load)
</script>

<style scoped>
.ai-usage { padding: 16px; display: flex; flex-direction: column; gap: 14px; }
.bar { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
.spacer { flex: 1; }
.label { font-weight: 600; }
.chip { border: 1px solid #c9c9d3; background: #fff; border-radius: 999px; padding: 4px 12px; cursor: pointer; }
.chip.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
.chip:disabled { opacity: .5; cursor: default; }
select, input { border: 1px solid #c9c9d3; border-radius: 8px; padding: 5px 8px; }
.cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 10px; }
.card { border: 1px solid #e3e3ea; border-radius: 12px; padding: 12px; background: #fff; }
.num { font-size: 24px; font-weight: 800; }
.cap { color: #666; font-size: 12px; }
.alert { border: 1px solid #f59e0b; background: #fffbeb; border-radius: 10px; padding: 10px 14px; }
.alert ul { margin: 6px 0 0 18px; }
.ok { border: 1px solid #86efac; background: #f0fdf4; border-radius: 10px; padding: 8px 14px; }
.error { color: #b91c1c; }
.scroll { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; background: #fff; }
th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #eee; white-space: nowrap; }
.empty { text-align: center; color: #888; }
.badge { border-radius: 999px; padding: 2px 8px; font-size: 12px; background: #eee; margin-right: 4px; }
.s-succeeded { background: #dcfce7; }
.s-failed, .s-refund_failed { background: #fee2e2; }
.s-processing { background: #fef3c7; }
.s-refunded { background: #dbeafe; }
</style>
