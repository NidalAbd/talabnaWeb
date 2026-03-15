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
            <v-form @submit.prevent="submitForm">
              <v-text-field v-model="form.name" :label="locale === 'ar' ? 'الاسم' : 'Name'" required class="mb-4" />
              <v-text-field v-model="form.email" :label="locale === 'ar' ? 'البريد الإلكتروني' : 'Email'" type="email" required class="mb-4" />
              <v-textarea v-model="form.message" :label="locale === 'ar' ? 'الرسالة' : 'Message'" rows="5" required class="mb-4" />
              <v-btn color="primary" type="submit" size="large" block>{{ locale === 'ar' ? 'إرسال' : 'Send' }}</v-btn>
            </v-form>
          </v-card>
        </v-col>

        <v-col cols="12" md="4">
          <v-card variant="outlined" class="pa-6 mb-4">
            <v-icon color="primary" size="32" class="mb-3">mdi-email</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'البريد الإلكتروني' : 'Email' }}</h3>
            <p class="text-body-2">support@talbna.cloud</p>
          </v-card>
          <v-card variant="outlined" class="pa-6">
            <v-icon color="primary" size="32" class="mb-3">mdi-phone</v-icon>
            <h3 class="text-subtitle-1 font-weight-bold">{{ locale === 'ar' ? 'الهاتف' : 'Phone' }}</h3>
            <p class="text-body-2">+970 123 456 789</p>
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
const submitForm = () => { alert(locale.value === 'ar' ? 'تم إرسال رسالتك' : 'Message sent') }

onMounted(() => { updateMeta({ title: locale.value === 'ar' ? 'اتصل بنا - طلبنا' : 'Contact Us - Talabna' }) })
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
</style>
