<template>
  <div class="not-found-page">
    <div class="container py-16 text-center">
      <i class="mdi mdi-alert-circle-outline" style="font-size: 150px; color: var(--color-text-muted); opacity: 0.4;"></i>
      <h1 class="text-h1 font-weight-bold mt-8 mb-4">404</h1>
      <h2 class="text-h4 text-muted mb-6">
        {{ t('notfound.title') }}
      </h2>
      <p class="text-body-1 text-muted mb-8">
        {{ t('notfound.desc') }}
      </p>
      <router-link to="/" class="btn btn-primary btn-lg">
        <i class="mdi mdi-home"></i>
        {{ t('notfound.back') }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import { t } from '@/utils/translate'

const appStore = useAppStore()
const { updateMeta } = useSeo()
const locale = computed(() => appStore.locale)

onMounted(() => {
  updateMeta({
    title: '404 - ' + t('notfound.title') + ' | ' + t('app.name'),
    description: t('notfound.desc'),
  })
})
</script>
