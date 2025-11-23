<template>
  <div class="privacy-page">
    <section class="hero-gradient py-12">
      <v-container class="text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</h1>
      </v-container>
    </section>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card variant="outlined" class="pa-8">
            <div class="policy-content">
              <h2>{{ locale === 'ar' ? 'مقدمة' : 'Introduction' }}</h2>
              <p>{{ locale === 'ar'
                ? 'نحن في طلبنا نلتزم بحماية خصوصيتك وبياناتك الشخصية. توضح سياسة الخصوصية هذه كيفية جمع واستخدام وحماية معلوماتك.'
                : 'At Talabna, we are committed to protecting your privacy and personal data. This privacy policy explains how we collect, use, and protect your information.'
              }}</p>

              <h2>{{ locale === 'ar' ? 'المعلومات التي نجمعها' : 'Information We Collect' }}</h2>
              <p>{{ locale === 'ar'
                ? 'نجمع المعلومات التي تقدمها مباشرة لنا، مثل الاسم والبريد الإلكتروني ورقم الهاتف عند إنشاء حساب أو نشر إعلان.'
                : 'We collect information you provide directly to us, such as name, email, and phone number when you create an account or post an ad.'
              }}</p>

              <h2>{{ locale === 'ar' ? 'كيف نستخدم معلوماتك' : 'How We Use Your Information' }}</h2>
              <p>{{ locale === 'ar'
                ? 'نستخدم المعلومات لتوفير وتحسين خدماتنا، والتواصل معك، وضمان أمان المنصة.'
                : 'We use the information to provide and improve our services, communicate with you, and ensure platform security.'
              }}</p>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useAppStore } from '@/stores/app'
import { useSeo } from '@/composables/useSeo'

const appStore = useAppStore()
const { updateMeta } = useSeo()
const locale = computed(() => appStore.locale)

onMounted(() => { updateMeta({ title: locale.value === 'ar' ? 'سياسة الخصوصية - طلبنا' : 'Privacy Policy - Talabna' }) })
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
.policy-content h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem; }
.policy-content p { line-height: 1.8; margin-bottom: 1rem; color: rgba(0,0,0,0.7); }
</style>
