<template>
  <div class="contact-page">
    <section class="hero-gradient py-16">
      <v-container class="text-center">
        <h1 class="text-h2 font-weight-bold text-white mb-4">{{ locale === 'ar' ? 'اتصل بنا' : 'Contact Us' }}</h1>
        <p class="text-h6 text-white-darken-1">{{ locale === 'ar' ? 'نحن هنا لمساعدتك' : 'We are here to help' }}</p>
      </v-container>
    </section>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="6">
          <v-card variant="outlined" class="pa-8">
            <h2 class="text-h5 font-weight-bold mb-6">{{ locale === 'ar' ? 'أرسل لنا رسالة' : 'Send us a message' }}</h2>

            <v-alert v-if="submitSuccess" type="success" class="mb-4" closable @click:close="submitSuccess = false">
              {{ locale === 'ar' ? 'تم إرسال رسالتك بنجاح! سنرد عليك في أقرب وقت.' : 'Message sent successfully! We will reply soon.' }}
            </v-alert>
            <v-alert v-if="submitError" type="error" class="mb-4" closable @click:close="submitError = ''">
              {{ submitError }}
            </v-alert>

            <v-form ref="formRef" @submit.prevent="submitForm" :disabled="submitting">
              <v-text-field
                v-model="form.name"
                :label="locale === 'ar' ? 'الاسم' : 'Name'"
                :rules="[v => !!v || (locale === 'ar' ? 'الاسم مطلوب' : 'Name is required')]"
                required
                class="mb-4"
              />
              <v-text-field
                v-model="form.email"
                :label="locale === 'ar' ? 'البريد الإلكتروني' : 'Email'"
                type="email"
                :rules="[
                  v => !!v || (locale === 'ar' ? 'البريد الإلكتروني مطلوب' : 'Email is required'),
                  v => /.+@.+\..+/.test(v) || (locale === 'ar' ? 'بريد إلكتروني غير صالح' : 'Invalid email')
                ]"
                required
                class="mb-4"
              />
              <v-textarea
                v-model="form.message"
                :label="locale === 'ar' ? 'الرسالة' : 'Message'"
                rows="5"
                :rules="[v => !!v || (locale === 'ar' ? 'الرسالة مطلوبة' : 'Message is required')]"
                required
                class="mb-4"
              />
              <v-btn color="primary" type="submit" size="large" block :loading="submitting">
                <v-icon start>mdi-send</v-icon>
                {{ locale === 'ar' ? 'إرسال' : 'Send' }}
              </v-btn>
            </v-form>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card variant="outlined" class="pa-6 mb-4">
            <v-icon color="primary" size="32" class="mb-3">mdi-email</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</h3>
            <a href="mailto:support@talbna.cloud" class="text-body-2 text-decoration-none">support@talbna.cloud</a>
          </v-card>
          <v-card variant="outlined" class="pa-6 mb-4">
            <v-icon color="primary" size="32" class="mb-3">mdi-web</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'الموقع' : 'Website' }}</h3>
            <a href="https://talbna.cloud" class="text-body-2 text-decoration-none">talbna.cloud</a>
          </v-card>
          <v-card variant="outlined" class="pa-6">
            <v-icon color="primary" size="32" class="mb-3">mdi-google-play</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'التطبيق' : 'App' }}</h3>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="text-body-2 text-decoration-none">Google Play</a>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
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
const formRef = ref(null)
const submitting = ref(false)
const submitSuccess = ref(false)
const submitError = ref('')

const submitForm = async () => {
  const { valid } = await formRef.value.validate()
  if (!valid) return

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
      formRef.value.reset()
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
