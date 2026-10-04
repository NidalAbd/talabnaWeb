<template>
    <div class="addons-panel">
        <div class="addons-head">
            <div>
                <h3>Top-up packs</h3>
                <p>Extra uses of a plan allowance, bought with points. They last until the subscriber's current period ends.</p>
            </div>
            <button class="addons-btn primary" @click="addRow"><i class="fas fa-plus"></i> Add pack</button>
        </div>
        <div v-if="error" class="addons-error">{{ error }}</div>
        <table class="addons-table">
            <thead>
                <tr>
                    <th>Name (EN)</th><th>Name (AR)</th><th>Slug</th><th>Adds to</th>
                    <th>Amount</th><th>Price (points)</th><th>Order</th><th>Active</th><th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="!loading && rows.length === 0"><td colspan="9" class="empty">No packs yet.</td></tr>
                <tr v-for="row in rows" :key="row._key">
                    <td><input v-model="row.name.en" /></td>
                    <td><input v-model="row.name.ar" dir="rtl" /></td>
                    <td><input v-model="row.slug" /></td>
                    <td>
                        <select v-model="row.feature_key">
                            <option v-for="f in features" :key="f" :value="f">{{ featureLabel(f) }}</option>
                        </select>
                    </td>
                    <td><input type="number" min="1" v-model.number="row.amount" class="num" /></td>
                    <td><input type="number" min="0" v-model.number="row.price_points" class="num" /></td>
                    <td><input type="number" v-model.number="row.sort_order" class="num" /></td>
                    <td><input type="checkbox" v-model="row.is_active" /></td>
                    <td class="actions">
                        <button class="addons-btn" :disabled="row._saving" @click="save(row)">
                            <i :class="row._saving ? 'fas fa-spinner fa-spin' : 'fas fa-save'"></i>
                        </button>
                        <button class="addons-btn danger" @click="remove(row)"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const rows = ref([])
const features = ref(['ai_images_per_month', 'featured_posts'])
const loading = ref(false)
const error = ref(null)
let seq = 0

const headers = () => ({
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
    'Content-Type': 'application/json',
    'Accept': 'application/json'
})

const featureLabel = (f) => ({ ai_images_per_month: 'AI images', featured_posts: 'Featured posts' }[f] || f)

const toRow = (a) => ({ ...a, name: { en: a.name?.en || '', ar: a.name?.ar || '' }, _key: ++seq, _saving: false })

const load = async () => {
    loading.value = true
    error.value = null
    try {
        const r = await fetch('/api/admin/subscription-addons', { credentials: 'same-origin', headers: headers() })
        if (!r.ok) throw new Error('Could not load the packs')
        const data = await r.json()
        rows.value = data.addons.map(toRow)
        if (data.features?.length) features.value = data.features
    } catch (e) {
        error.value = e.message
    } finally {
        loading.value = false
    }
}

const addRow = () => {
    rows.value.push(toRow({ id: null, slug: '', name: {}, feature_key: features.value[0], amount: 5, price_points: 10, sort_order: rows.value.length + 1, is_active: true }))
}

const save = async (row) => {
    row._saving = true
    error.value = null
    try {
        const body = JSON.stringify({ slug: row.slug, name: row.name, feature_key: row.feature_key, amount: row.amount,
            price_points: row.price_points, sort_order: row.sort_order, is_active: !!row.is_active })
        const r = await fetch(row.id ? `/api/admin/subscription-addons/${row.id}` : '/api/admin/subscription-addons',
            { method: row.id ? 'PUT' : 'POST', credentials: 'same-origin', headers: headers(), body })
        const data = await r.json().catch(() => ({}))
        if (!r.ok) throw new Error(data.message || 'Could not save the pack')
        Object.assign(row, { id: data.addon.id })
    } catch (e) {
        error.value = e.message
    } finally {
        row._saving = false
    }
}

const remove = async (row) => {
    if (row.id) {
        const r = await fetch(`/api/admin/subscription-addons/${row.id}`, { method: 'DELETE', credentials: 'same-origin', headers: headers() })
        if (!r.ok) { error.value = 'Could not delete the pack'; return }
    }
    rows.value = rows.value.filter((x) => x !== row)
}

onMounted(load)
</script>

<style scoped>
.addons-panel { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); padding: 1.25rem; margin-top: 1.5rem; overflow-x: auto; }
.addons-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; margin-bottom: 1rem; }
.addons-head h3 { margin: 0 0 .25rem; font-size: 1.1rem; color: #2c3e50; }
.addons-head p { margin: 0; color: #6c757d; font-size: .85rem; }
.addons-error { background: #fdecea; color: #b71c1c; padding: .5rem .75rem; border-radius: 8px; margin-bottom: .75rem; }
.addons-table { width: 100%; border-collapse: collapse; min-width: 820px; }
.addons-table th { text-align: left; font-size: .8rem; color: #6c757d; padding: .5rem; border-bottom: 1px solid #eee; }
.addons-table td { padding: .4rem .5rem; border-bottom: 1px solid #f3f3f3; }
.addons-table input:not([type=checkbox]), .addons-table select { width: 100%; padding: .35rem .5rem; border: 1px solid #dde1e6; border-radius: 6px; }
.addons-table input.num { width: 80px; }
.addons-table .empty { text-align: center; color: #999; padding: 1rem; }
.actions { white-space: nowrap; }
.addons-btn { border: 1px solid #dde1e6; background: #f8f9fa; border-radius: 6px; padding: .35rem .6rem; cursor: pointer; margin-right: .25rem; }
.addons-btn.primary { background: #2c3e50; color: white; border-color: #2c3e50; }
.addons-btn.danger { color: #c0392b; }
</style>
