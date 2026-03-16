<template>
  <div class="not-found-page">
    <div class="container py-16 text-center">
      <i class="mdi mdi-alert-circle-outline" style="font-size: 150px; color: var(--color-text-muted); opacity: 0.4;"></i>
      <h1 class="text-h1 font-weight-bold mt-8 mb-4">404</h1>
      <h2 class="text-h4 text-muted mb-6">
        {{ locale === 'ar' ? 'الصفحة غير موجودة' : 'Page Not Found' }}
      </h2>
      <p class="text-body-1 text-muted mb-8">
        {{ locale === 'ar'
          ? 'عذراً، الصفحة التي تبحث عنها غير موجودة أو تم نقلها.'
          : 'Sorry, the page you are looking for does not exist or has been moved.'
        }}
      </p>
      <router-link to="/" class="btn btn-primary btn-lg">
        <i class="mdi mdi-home"></i>
        {{ locale === 'ar' ? 'العودة للرئيسية' : 'Back to Home' }}
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'

const appStore = useAppStore()
const { updateMeta } = useSeo()
const locale = computed(() => appStore.locale)

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? '404 - الصفحة غير موجودة | طلبنا' : '404 - Page Not Found | Talabna',
    description: locale.value === 'ar' ? 'الصفحة المطلوبة غير موجودة' : 'The requested page was not found',
  })
})
</script>
