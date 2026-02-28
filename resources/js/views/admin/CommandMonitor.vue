<template>
    <div class="command-monitor">

        <!-- Time Frame Bar -->
        <div class="date-range-bar">
            <span class="range-label">Time Frame:</span>
            <div class="range-buttons">
                <button
                    v-for="tf in timeframes"
                    :key="tf.hours"
                    class="range-btn"
                    :class="{ active: activeTimeframe === tf.hours }"
                    @click="changeTimeframe(tf.hours)"
                >
                    {{ tf.label }}
                </button>
            </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading && !commandData" class="loading-state">
            <div class="spinner"></div>
            <p>Loading command monitor...</p>
        </div>

        <template v-if="commandData">

            <!-- Command Cards Grid -->
            <div class="stats-grid">
                <div
                    v-for="cmd in commandData.commands"
                    :key="cmd.name"
                    class="command-card"
                    :class="cardStatusClass(cmd)"
                >
                    <div class="command-card-header">
                        <div class="command-icon">
                            <v-icon size="24">{{ cmd.icon }}</v-icon>
                        </div>
                        <div class="command-name">{{ cmd.label }}</div>
                    </div>

                    <div class="command-data-change">
                        <span class="data-count">{{ formatNumber(cmd.data_changes.count) }}</span>
                        <span class="data-label">{{ cmd.data_changes.label }}</span>
                    </div>

                    <div class="command-last-run">
                        <template v-if="cmd.last_run">
                            <span class="status-badge" :class="'status-' + cmd.last_run.status">
                                {{ statusIcon(cmd.last_run.status) }} {{ cmd.last_run.status }}
                            </span>
                            <span class="last-time">{{ timeAgo(cmd.last_run.started_at) }}</span>
                            <span v-if="cmd.last_run.duration !== null" class="duration">{{ formatDuration(cmd.last_run.duration) }}</span>
                        </template>
                        <span v-else class="no-runs">No runs yet</span>
                    </div>

                    <div class="command-stats-row">
                        <span class="stat-item" title="Total runs">{{ cmd.stats.total_runs }} runs</span>
                        <span class="stat-item success" title="Successful">{{ cmd.stats.successful }} ok</span>
                        <span v-if="cmd.stats.failed > 0" class="stat-item failed" title="Failed">{{ cmd.stats.failed }} failed</span>
                    </div>

                    <div class="command-schedule">
                        <v-icon size="12">mdi-clock-outline</v-icon> {{ cmd.schedule }}
                    </div>
                </div>
            </div>

            <!-- AI Progress Section -->
            <div v-if="hasActiveAiProgress" class="section-card ai-progress-section">
                <h3 class="section-title">
                    <v-icon size="20" class="mr-1">mdi-robot-outline</v-icon>
                    AI Progress
                </h3>
                <div
                    v-for="(progress, key) in commandData.ai_progress"
                    :key="key"
                    class="progress-item"
                >
                    <div class="progress-header">
                        <span class="progress-name">{{ formatProgressKey(key) }}</span>
                        <span class="progress-status" :class="'status-' + (progress.status || 'unknown')">
                            {{ progress.status || 'unknown' }}
                        </span>
                    </div>
                    <template v-if="progress.total_posts">
                        <div class="progress-bar-wrapper">
                            <div class="progress-bar">
                                <div
                                    class="progress-fill"
                                    :style="{ width: progressPercent(progress) + '%' }"
                                ></div>
                            </div>
                            <span class="progress-pct">{{ progressPercent(progress) }}%</span>
                        </div>
                        <div v-if="progress.current_item" class="progress-detail">
                            {{ progress.current_item }}
                        </div>
                    </template>
                    <template v-else-if="progress.completed">
                        <div class="progress-detail">
                            {{ progress.completed.length }} completed, {{ (progress.errors || []).length }} errors
                        </div>
                    </template>
                </div>
            </div>

            <!-- Recent Runs Table -->
            <div class="section-card">
                <h3 class="section-title">
                    <v-icon size="20" class="mr-1">mdi-history</v-icon>
                    Recent Runs
                </h3>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Command</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th>Duration</th>
                                <th>Records</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="log in commandData.recent_logs" :key="log.id">
                                <td class="cmd-name">{{ log.command }}</td>
                                <td>
                                    <span class="status-badge" :class="'status-' + log.status">
                                        {{ statusIcon(log.status) }} {{ log.status }}
                                    </span>
                                </td>
                                <td class="time-cell">{{ timeAgo(log.started_at) }}</td>
                                <td>{{ log.duration !== null ? formatDuration(log.duration) : '—' }}</td>
                                <td>{{ log.records_affected || '—' }}</td>
                            </tr>
                            <tr v-if="commandData.recent_logs.length === 0">
                                <td colspan="5" class="empty-state">No command runs recorded yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Errors Section -->
            <div v-if="commandData.recent_errors.length > 0" class="section-card errors-section">
                <h3 class="section-title">
                    <v-icon size="20" class="mr-1" color="error">mdi-alert-circle-outline</v-icon>
                    Recent Errors
                </h3>
                <div
                    v-for="err in commandData.recent_errors"
                    :key="err.id"
                    class="error-item"
                    @click="toggleError(err.id)"
                >
                    <div class="error-header">
                        <span class="error-toggle">{{ expandedErrors.has(err.id) ? '▾' : '▸' }}</span>
                        <span class="error-cmd">{{ err.command }}</span>
                        <span class="error-time">{{ timeAgo(err.started_at) }}</span>
                    </div>
                    <div v-if="expandedErrors.has(err.id)" class="error-body">
                        {{ err.error_message }}
                    </div>
                </div>
            </div>

        </template>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useCommandMonitor } from '../../composables/useCommandMonitor'

const {
    commandData,
    activeTimeframe,
    loading,
    error,
    changeTimeframe,
} = useCommandMonitor()

const expandedErrors = ref(new Set())

const timeframes = [
    { hours: 1, label: '1h' },
    { hours: 4, label: '4h' },
    { hours: 6, label: '6h' },
    { hours: 12, label: '12h' },
    { hours: 24, label: '24h' },
    { hours: 720, label: '1 month' },
]

const hasActiveAiProgress = computed(() => {
    if (!commandData.value?.ai_progress) return false
    const progress = commandData.value.ai_progress
    return Object.keys(progress).length > 0 &&
        Object.values(progress).some(p => p.status === 'running' || (p.completed && p.completed.length > 0))
})

function toggleError(id) {
    const s = new Set(expandedErrors.value)
    if (s.has(id)) {
        s.delete(id)
    } else {
        s.add(id)
    }
    expandedErrors.value = s
}

function cardStatusClass(cmd) {
    if (!cmd.last_run) return ''
    if (cmd.last_run.status === 'running') return 'card-running'
    if (cmd.last_run.status === 'failed') return 'card-failed'
    return 'card-ok'
}

function statusIcon(status) {
    switch (status) {
        case 'finished': return '✓'
        case 'running': return '⏳'
        case 'failed': return '✗'
        default: return '?'
    }
}

function timeAgo(dateStr) {
    if (!dateStr) return '—'
    const now = new Date()
    const date = new Date(dateStr)
    const diffMs = now - date
    const diffSec = Math.floor(diffMs / 1000)
    const diffMin = Math.floor(diffSec / 60)
    const diffHr = Math.floor(diffMin / 60)
    const diffDay = Math.floor(diffHr / 24)

    if (diffSec < 60) return `${diffSec}s ago`
    if (diffMin < 60) return `${diffMin}m ago`
    if (diffHr < 24) return `${diffHr}h ago`
    return `${diffDay}d ago`
}

function formatDuration(seconds) {
    if (seconds === null || seconds === undefined) return '—'
    if (seconds < 60) return `${seconds}s`
    const mins = Math.floor(seconds / 60)
    const secs = seconds % 60
    return `${mins}m ${secs}s`
}

function formatNumber(n) {
    if (n === null || n === undefined) return '0'
    return n.toLocaleString()
}

function progressPercent(progress) {
    if (!progress.total_posts || progress.total_posts === 0) return 0
    return Math.round((progress.completed_posts / progress.total_posts) * 100)
}

function formatProgressKey(key) {
    return key.replace(/_/g, ' ').replace(/^ai /, 'AI ').replace(/cat(\d+)/, 'Category $1')
}
</script>

<style scoped>
.command-monitor {
    padding: 0;
}

/* Date Range Bar */
.date-range-bar {
    background: white;
    border-radius: 12px;
    padding: 0.75rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.range-label {
    font-weight: 600;
    color: #374151;
    white-space: nowrap;
}

.range-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.range-btn {
    padding: 0.5rem 1rem;
    border: 1px solid #e5e7eb;
    background: white;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.2s ease;
}

.range-btn:hover {
    background: #f3f4f6;
    color: #374151;
}

.range-btn.active {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: white;
    border-color: #3b82f6;
}

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-bottom: 1.25rem;
}

/* Command Card */
.command-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #e5e7eb;
    transition: all 0.2s ease;
}

.command-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
}

.command-card.card-ok {
    border-left-color: #22c55e;
}

.command-card.card-running {
    border-left-color: #f59e0b;
    animation: pulse-border 2s infinite;
}

.command-card.card-failed {
    border-left-color: #ef4444;
}

@keyframes pulse-border {
    0%, 100% { border-left-color: #f59e0b; }
    50% { border-left-color: #fbbf24; }
}

.command-card-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.command-icon {
    color: #6b7280;
}

.command-name {
    font-weight: 700;
    color: #1a1a2e;
    font-size: 0.95rem;
}

.command-data-change {
    margin-bottom: 0.75rem;
}

.data-count {
    font-size: 1.5rem;
    font-weight: 800;
    color: #1a1a2e;
    margin-right: 0.5rem;
}

.data-label {
    font-size: 0.8rem;
    color: #6b7280;
}

.command-last-run {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
    margin-bottom: 0.5rem;
    font-size: 0.82rem;
}

.last-time {
    color: #6b7280;
}

.duration {
    color: #9ca3af;
    font-size: 0.78rem;
}

.no-runs {
    color: #9ca3af;
    font-style: italic;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
    font-size: 0.78rem;
    font-weight: 600;
}

.status-finished {
    background: #dcfce7;
    color: #166534;
}

.status-running {
    background: #fef3c7;
    color: #92400e;
}

.status-failed {
    background: #fef2f2;
    color: #991b1b;
}

.command-stats-row {
    display: flex;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.78rem;
}

.stat-item {
    color: #6b7280;
}

.stat-item.success {
    color: #22c55e;
}

.stat-item.failed {
    color: #ef4444;
}

.command-schedule {
    font-size: 0.75rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Section Cards */
.section-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    margin-bottom: 1.25rem;
    overflow: hidden;
}

.section-title {
    display: flex;
    align-items: center;
    padding: 1rem 1.25rem;
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    border-bottom: 1px solid #f0f0f0;
}

/* AI Progress */
.ai-progress-section {
    border-left: 4px solid #f59e0b;
}

.progress-item {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #f0f0f0;
}

.progress-item:last-child {
    border-bottom: none;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.progress-name {
    font-weight: 600;
    color: #374151;
    text-transform: capitalize;
}

.progress-status {
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 6px;
}

.progress-bar-wrapper {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.25rem;
}

.progress-bar {
    flex: 1;
    height: 8px;
    background: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border-radius: 4px;
    transition: width 0.5s ease;
}

.progress-pct {
    font-weight: 700;
    font-size: 0.85rem;
    color: #374151;
    min-width: 3rem;
    text-align: right;
}

.progress-detail {
    font-size: 0.8rem;
    color: #6b7280;
    font-style: italic;
}

/* Data Table */
.table-wrapper {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
}

.data-table th {
    padding: 0.75rem 1rem;
    text-align: left;
    font-size: 0.78rem;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: #f8f9fa;
    border-bottom: 2px solid #e5e7eb;
}

.data-table td {
    padding: 0.6rem 1rem;
    font-size: 0.85rem;
    color: #374151;
    border-bottom: 1px solid #f0f0f0;
}

.data-table tr:hover td {
    background: #f8f9fa;
}

.cmd-name {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
    font-size: 0.82rem;
    color: #1a1a2e;
    font-weight: 600;
}

.time-cell {
    white-space: nowrap;
}

.empty-state {
    text-align: center;
    color: #9ca3af;
    padding: 2rem !important;
    font-style: italic;
}

/* Errors Section */
.errors-section {
    border-left: 4px solid #ef4444;
}

.error-item {
    padding: 0.75rem 1.25rem;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: background 0.15s ease;
}

.error-item:last-child {
    border-bottom: none;
}

.error-item:hover {
    background: #fef2f2;
}

.error-header {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.error-toggle {
    color: #9ca3af;
    font-size: 0.85rem;
    width: 1rem;
}

.error-cmd {
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
    font-weight: 600;
    color: #991b1b;
    font-size: 0.85rem;
}

.error-time {
    color: #6b7280;
    font-size: 0.8rem;
    margin-left: auto;
}

.error-body {
    margin-top: 0.5rem;
    padding: 0.75rem;
    background: #fef2f2;
    border-radius: 8px;
    font-size: 0.82rem;
    color: #991b1b;
    font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
    white-space: pre-wrap;
    word-break: break-word;
}

/* Loading */
.loading-state {
    text-align: center;
    padding: 4rem 2rem;
}

.spinner {
    width: 50px;
    height: 50px;
    margin: 0 auto 1rem;
    border: 4px solid #f3f4f6;
    border-top: 4px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 1024px) {
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }

    .date-range-bar {
        flex-direction: column;
        align-items: flex-start;
    }

    .range-buttons {
        justify-content: flex-start;
    }
}
</style>
