<template>
  <div class="about-page">
    <section class="hero-gradient py-16">
      <div class="container text-center">
        <h1 class="text-h2 font-weight-bold text-white mb-4">{{ t('about.title') }}</h1>
        <p class="text-h6 text-white-darken-1">{{ t('about.subtitle') }}</p>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-10">
          <!-- Mission -->
          <div class="card pa-8 mb-8">
            <div class="row" style="align-items: center;">
              <div class="col-12 col-md-6">
                <h2 class="text-h4 font-weight-bold mb-4">{{ t('about.mission') }}</h2>
                <p class="text-body-1 text-muted mb-4">
                  {{ t('about.mission_p1') }}
                </p>
                <p class="text-body-1 text-muted">
                  {{ t('about.mission_p2') }}
                </p>
              </div>
              <div class="col-12 col-md-6 text-center">
                <i class="mdi mdi-handshake-outline" style="font-size: 160px; color: var(--color-primary); opacity: 0.15;"></i>
              </div>
            </div>
          </div>

          <!-- Values -->
          <h2 class="text-h4 font-weight-bold mb-6 text-center">{{ t('about.values') }}</h2>
          <div class="row mb-8">
            <div v-for="value in values" :key="value.icon" class="col-12 col-sm-6 col-md-3">
              <div class="card pa-6 text-center h-100">
                <div class="avatar avatar-64 mx-auto mb-4" :style="{ background: value.color }">
                  <i class="mdi" :class="value.icon" style="font-size: 32px; color: #fff;"></i>
                </div>
                <h3 class="text-h6 font-weight-bold mb-2">{{ t(value.titleKey) }}</h3>
                <p class="text-body-2 text-muted">{{ t(value.descKey) }}</p>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="card-flat pa-8 text-center" style="background: var(--color-primary); border-radius: var(--radius-xl);">
            <h2 class="text-h4 font-weight-bold mb-6 text-white">{{ t('about.by_numbers') }}</h2>
            <div class="row">
              <div v-for="stat in aboutStats" :key="stat.labelEn" class="col-6 col-md-3">
                <div class="text-h3 font-weight-bold text-white">{{ stat.value }}</div>
                <div class="text-body-2 text-white" style="opacity: 0.8">{{ t(stat.labelKey) }}</div>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <div class="text-center mt-12">
            <h2 class="text-h5 font-weight-bold mb-4">{{ t('about.ready') }}</h2>
            <router-link to="/browse" class="btn btn-primary btn-xl mr-4">
              <i class="mdi mdi-magnify"></i>
              {{ t('browse.title') }}
            </router-link>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn btn-outline btn-xl">
              <i class="mdi mdi-google-play"></i>
              {{ t('about.download_app') }}
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'
import { t } from '@/utils/translate'

const appStore = useAppStore()
const { updateMeta, setOrganizationSchema, setFaqSchema } = useSeo()
const locale = computed(() => appStore.locale)

const values = [
  { icon: 'mdi-shield-check', color: '#10B981', titleKey: 'about.value_security', descKey: 'about.value_security_desc' },
  { icon: 'mdi-lightning-bolt', color: '#F59E0B', titleKey: 'about.value_speed', descKey: 'about.value_speed_desc' },
  { icon: 'mdi-heart', color: '#EF4444', titleKey: 'about.value_trust', descKey: 'about.value_trust_desc' },
  { icon: 'mdi-earth', color: '#06B6D4', titleKey: 'about.value_coverage', descKey: 'about.value_coverage_desc' },
]

const aboutStats = [
  { value: '22+', labelKey: 'about.stat_countries' },
  { value: '500+', labelKey: 'about.stat_users' },
  { value: '1000+', labelKey: 'about.stat_listings' },
  { value: '8', labelKey: 'about.stat_categories' },
]

onMounted(() => {
  updateMeta({
    title: t('about.title') + ' - ' + t('app.name') + ' | ' + t('app.tagline'),
    description: t('about.mission_p1'),
  })
  setOrganizationSchema()
  setFaqSchema([
    { question: t('faq.what_is'), answer: t('faq.what_is_answer') },
    { question: t('faq.is_free'), answer: t('faq.is_free_answer') },
    { question: t('faq.contact_seller'), answer: t('faq.contact_seller_answer') },
    { question: t('faq.countries'), answer: t('faq.countries_answer') },
  ])
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%); }
</style>
