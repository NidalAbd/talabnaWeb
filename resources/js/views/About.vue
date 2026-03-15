<template>
  <div class="about-page">
    <section class="hero-gradient py-16">
      <v-container class="text-center">
        <h1 class="text-h2 font-weight-bold text-white mb-4">{{ locale === 'ar' ? 'من نحن' : 'About Us' }}</h1>
        <p class="text-h6 text-white-darken-1">{{ locale === 'ar' ? 'تعرف على قصة طلبنا' : 'Learn about Talabna story' }}</p>
      </v-container>
    </section>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="10">
          <!-- Mission -->
          <v-card variant="outlined" class="pa-8 mb-8">
            <v-row align="center">
              <v-col cols="12" md="6">
                <h2 class="text-h4 font-weight-bold mb-4">{{ locale === 'ar' ? 'مهمتنا' : 'Our Mission' }}</h2>
                <p class="text-body-1 text-medium-emphasis mb-4">
                  {{ locale === 'ar'
                    ? 'طلبنا هي منصة رائدة للإعلانات المبوبة تخدم المنطقة العربية. نربط بين البائعين والمشترين بطريقة سهلة وآمنة وسريعة. من الوظائف إلى العقارات، ومن السيارات إلى الأجهزة الإلكترونية — نوفر لك كل ما تحتاجه في مكان واحد.'
                    : 'Talabna is a leading classified ads platform serving the Arab region. We connect sellers and buyers in an easy, secure, and fast way. From jobs to real estate, cars to electronics — we provide everything you need in one place.'
                  }}
                </p>
                <p class="text-body-1 text-medium-emphasis">
                  {{ locale === 'ar'
                    ? 'نسعى لبناء مجتمع رقمي موثوق يُمكّن الجميع من البيع والشراء والتواصل بثقة وأمان.'
                    : 'We strive to build a trusted digital community that empowers everyone to buy, sell, and connect with confidence and security.'
                  }}
                </p>
              </v-col>
              <v-col cols="12" md="6" class="text-center">
                <v-icon size="160" color="primary" style="opacity: 0.15">mdi-handshake-outline</v-icon>
              </v-col>
            </v-row>
          </v-card>

          <!-- Values -->
          <h2 class="text-h4 font-weight-bold mb-6 text-center">{{ locale === 'ar' ? 'قيمنا' : 'Our Values' }}</h2>
          <v-row class="mb-8">
            <v-col cols="12" sm="6" md="3" v-for="value in values" :key="value.icon">
              <v-card variant="outlined" class="pa-6 text-center h-100">
                <v-avatar :color="value.color" size="64" class="mb-4">
                  <v-icon size="32" color="white">{{ value.icon }}</v-icon>
                </v-avatar>
                <h3 class="text-h6 font-weight-bold mb-2">{{ locale === 'ar' ? value.titleAr : value.titleEn }}</h3>
                <p class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? value.descAr : value.descEn }}</p>
              </v-card>
            </v-col>
          </v-row>

          <!-- Stats -->
          <v-card variant="flat" class="pa-8 text-center" color="primary" dark>
            <h2 class="text-h4 font-weight-bold mb-6 text-white">{{ locale === 'ar' ? 'بالأرقام' : 'By the Numbers' }}</h2>
            <v-row>
              <v-col cols="6" md="3" v-for="stat in aboutStats" :key="stat.label">
                <div class="text-h3 font-weight-bold text-white">{{ stat.value }}</div>
                <div class="text-body-2 text-white" style="opacity: 0.8">{{ locale === 'ar' ? stat.labelAr : stat.labelEn }}</div>
              </v-col>
            </v-row>
          </v-card>

          <!-- CTA -->
          <div class="text-center mt-12">
            <h2 class="text-h5 font-weight-bold mb-4">{{ locale === 'ar' ? 'جاهز للبدء؟' : 'Ready to get started?' }}</h2>
            <v-btn color="primary" size="x-large" to="/browse" class="mr-4">
              <v-icon start>mdi-magnify</v-icon>
              {{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}
            </v-btn>
            <v-btn variant="outlined" size="x-large" href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank">
              <v-icon start>mdi-google-play</v-icon>
              {{ locale === 'ar' ? 'حمل التطبيق' : 'Download App' }}
            </v-btn>
          </div>
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
const { updateMeta, setOrganizationSchema } = useSeo()
const locale = computed(() => appStore.locale)

const values = [
  { icon: 'mdi-shield-check', color: 'success', titleAr: 'الأمان', titleEn: 'Security', descAr: 'نحمي بياناتك وخصوصيتك بأحدث تقنيات الحماية', descEn: 'We protect your data and privacy with the latest security technologies' },
  { icon: 'mdi-lightning-bolt', color: 'warning', titleAr: 'السرعة', titleEn: 'Speed', descAr: 'منصة سريعة وسهلة الاستخدام مع تجربة سلسة', descEn: 'Fast and easy to use platform with a smooth experience' },
  { icon: 'mdi-heart', color: 'error', titleAr: 'الثقة', titleEn: 'Trust', descAr: 'بناء مجتمع موثوق من المستخدمين المُوثّقين', descEn: 'Building a trusted community of verified users' },
  { icon: 'mdi-earth', color: 'info', titleAr: 'التغطية', titleEn: 'Coverage', descAr: 'نخدم أكثر من 22 دولة في المنطقة العربية', descEn: 'Serving over 22 countries across the Arab region' },
]

const aboutStats = [
  { value: '22+', labelAr: 'دولة', labelEn: 'Countries' },
  { value: '500+', labelAr: 'مستخدم', labelEn: 'Users' },
  { value: '1000+', labelAr: 'إعلان', labelEn: 'Listings' },
  { value: '8', labelAr: 'تصنيف', labelEn: 'Categories' },
]

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? 'من نحن - طلبنا | منصة الإعلانات المبوبة' : 'About Us - Talabna | Classified Ads Platform',
    description: locale.value === 'ar'
      ? 'تعرف على طلبنا، منصة الإعلانات المبوبة الرائدة في المنطقة العربية. نربط بين البائعين والمشترين بأمان وسهولة.'
      : 'Learn about Talabna, the leading classified ads platform in the Arab region. We connect sellers and buyers safely and easily.',
  })
  setOrganizationSchema()
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
</style>
