<template>
  <div class="contact-page">
    <section class="hero-gradient py-16">
      <div class="container text-center">
        <h1 class="text-h2 font-weight-bold text-white mb-4">{{ locale === 'ar' ? 'اتصل بنا' : 'Contact Us' }}</h1>
        <p class="text-h6 text-white-darken-1">{{ locale === 'ar' ? 'نحن هنا لمساعدتك' : 'We are here to help' }}</p>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-6">
          <div class="card pa-8">
            <h2 class="text-h5 font-weight-bold mb-6">{{ locale === 'ar' ? 'أرسل لنا رسالة' : 'Send us a message' }}</h2>

            <div v-if="submitSuccess" class="alert alert-success mb-4">
              {{ locale === 'ar' ? 'تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت.' : 'Message sent successfully! We will reply soon.' }}
              <button class="alert-close" @click="submitSuccess = false">&times;</button>
            </div>
            <div v-if="submitError" class="alert alert-error mb-4">
              {{ submitError }}
              <button class="alert-close" @click="submitError = ''">&times;</button>
            </div>

            <form @submit.prevent="submitForm">
              <div class="form-group">
                <label class="form-label">{{ locale === 'ar' ? 'الاسم' : 'Name' }} *</label>
                <input v-model="form.name" class="form-input" required :disabled="submitting" />
              </div>
              <div class="form-group">
                <label class="form-label">{{ locale === 'ar' ? 'البريد الإلكتروني' : 'Email' }} *</label>
                <input v-model="form.email" type="email" class="form-input" required :disabled="submitting" />
              </div>
              <div class="form-group">
                <label class="form-label">{{ locale === 'ar' ? 'الرسالة' : 'Message' }} *</label>
                <textarea v-model="form.message" class="form-textarea" rows="5" required :disabled="submitting"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-lg btn-block" :class="{ 'btn-loading': submitting }" :disabled="submitting">
                <i class="mdi mdi-send"></i>
                {{ locale === 'ar' ? 'إرسال' : 'Send' }}
              </button>
            </form>
          </div>
        </div>

        <div class="col-12 col-md-4">
          <div class="card pa-6 mb-4">
            <i class="mdi mdi-email" style="font-size: 32px; color: var(--color-primary); margin-bottom: 0.75rem;"></i>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</h3>
            <a href="mailto:support@talbna.cloud" class="text-body-2">support@talbna.cloud</a>
          </div>
          <div class="card pa-6 mb-4">
            <i class="mdi mdi-web" style="font-size: 32px; color: var(--color-primary); margin-bottom: 0.75rem;"></i>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'الموقع' : 'Website' }}</h3>
            <a href="https://talbna.cloud" class="text-body-2">talbna.cloud</a>
          </div>
          <div class="card pa-6">
            <i class="mdi mdi-google-play" style="font-size: 32px; color: var(--color-primary); margin-bottom: 0.75rem;"></i>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'التطبيق' : 'App' }}</h3>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="text-body-2">Google Play</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'

const appStore = useAppStore()
const { updateMeta } = useSeo()
const locale = computed(() => appStore.locale)

const form = ref({ name: '', email: '', message: '' })
const submitting = ref(false)
const submitSuccess = ref(false)
const submitError = ref('')

const submitForm = async () => {
  if (!form.value.name || !form.value.email || !form.value.message) return

  submitting.value = true
  submitError.value = ''
  submitSuccess.value = false

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
    const response = await fetch('/api/contact', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      body: JSON.stringify(form.value),
    })

    if (response.ok) {
      submitSuccess.value = true
      form.value = { name: '', email: '', message: '' }
    } else {
      const data = await response.json()
      submitError.value = data.message || (locale.value === 'ar' ? 'حدث خطأ. حاول مرة أخرى.' : 'An error occurred. Please try again.')
    }
  } catch (e) {
    submitError.value = locale.value === 'ar' ? 'فشل الاتصال بالخادم.' : 'Failed to connect to server.'
  } finally {
    submitting.value = false
  }
}

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? 'اتصل بنا - طلبنا' : 'Contact Us - Talabna',
    description: locale.value === 'ar' ? 'تواصل مع فريق طلبنا للدعم والاستفسارات' : 'Contact Talabna team for support and inquiries',
  })
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%); }
</style>
