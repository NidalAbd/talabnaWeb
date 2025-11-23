<template>
  <div class="terms-page">
    <section class="hero-gradient py-12">
      <v-container class="text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ locale === 'ar' ? 'شروط الاستخدام' : 'Terms of Service' }}</h1>
      </v-container>
    </section>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card variant="outlined" class="pa-8">
            <div class="terms-content">
              <h2>{{ locale === 'ar' ? 'القبول' : 'Acceptance' }}</h2>
              <p>{{ locale === 'ar'
                ? 'باستخدام منصة طلبنا، فإنك توافق على الالتزام بهذه الشروط والأحكام.'
                : 'By using Talabna platform, you agree to be bound by these terms and conditions.'
              }}</p>

              <h2>{{ locale === 'ar' ? 'استخدام المنصة' : 'Platform Usage' }}</h2>
              <p>{{ locale === 'ar'
                ? 'يجب استخدام المنصة للأغراض المشروعة فقط. يحظر نشر محتوى غير قانوني أو مسيء.'
                : 'The platform must be used for legitimate purposes only. Posting illegal or offensive content is prohibited.'
              }}</p>

              <h2>{{ locale === 'ar' ? 'المسؤولية' : 'Liability' }}</h2>
              <p>{{ locale === 'ar'
                ? 'طلبنا ليست مسؤولة عن المعاملات بين المستخدمين. يجب التحقق من المعلومات قبل إتمام أي صفقة.'
                : 'Talabna is not responsible for transactions between users. Information should be verified before completing any deal.'
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

onMounted(() => { updateMeta({ title: locale.value === 'ar' ? 'شروط الاستخدام - طلبنا' : 'Terms of Service - Talabna' }) })
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
.terms-content h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem; }
.terms-content p { line-height: 1.8; margin-bottom: 1rem; color: rgba(0,0,0,0.7); }
</style>
