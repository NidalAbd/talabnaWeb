<template>
  <div class="about-page">
    <section class="hero-gradient py-16">
      <div class="container text-center">
        <h1 class="text-h2 font-weight-bold text-white mb-4">{{ locale === 'ar' ? 'من نحن' : 'About Us' }}</h1>
        <p class="text-h6 text-white-darken-1">{{ locale === 'ar' ? 'تعرف على قصة طلبنا' : 'Learn about Talabna story' }}</p>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-10">
          <!-- Mission -->
          <div class="card pa-8 mb-8">
            <div class="row" style="align-items: center;">
              <div class="col-12 col-md-6">
                <h2 class="text-h4 font-weight-bold mb-4">{{ locale === 'ar' ? 'مهمتنا' : 'Our Mission' }}</h2>
                <p class="text-body-1 text-muted mb-4">
                  {{ locale === 'ar'
                    ? 'طلبنا هي منصة رائدة للإعلانات المبوبة تخدم المنطقة العربية. نربط بين البائعين والمشترين بطريقة سهلة وآمنة وسريعة. من الوظائف إلى العقارات، ومن السيارات إلى الأجهزة الإلكترونية — نوفر لك كل ما تحتاجه في مكان واحد.'
                    : 'Talabna is a leading classified ads platform serving the Arab region. We connect sellers and buyers in an easy, secure, and fast way. From jobs to real estate, cars to electronics — we provide everything you need in one place.'
                  }}
                </p>
                <p class="text-body-1 text-muted">
                  {{ locale === 'ar'
                    ? 'نسعى لبناء مجتمع رقمي موثوق يُمكّن الجميع من البيع والشراء والتواصل بثقة وأمان.'
                    : 'We strive to build a trusted digital community that empowers everyone to buy, sell, and connect with confidence and security.'
                  }}
                </p>
              </div>
              <div class="col-12 col-md-6 text-center">
                <i class="mdi mdi-handshake-outline" style="font-size: 160px; color: var(--color-primary); opacity: 0.15;"></i>
              </div>
            </div>
          </div>

          <!-- Values -->
          <h2 class="text-h4 font-weight-bold mb-6 text-center">{{ locale === 'ar' ? 'قيمنا' : 'Our Values' }}</h2>
          <div class="row mb-8">
            <div v-for="value in values" :key="value.icon" class="col-12 col-sm-6 col-md-3">
              <div class="card pa-6 text-center h-100">
                <div class="avatar avatar-64 mx-auto mb-4" :style="{ background: value.color }">
                  <i class="mdi" :class="value.icon" style="font-size: 32px; color: #fff;"></i>
                </div>
                <h3 class="text-h6 font-weight-bold mb-2">{{ locale === 'ar' ? value.titleAr : value.titleEn }}</h3>
                <p class="text-body-2 text-muted">{{ locale === 'ar' ? value.descAr : value.descEn }}</p>
              </div>
            </div>
          </div>

          <!-- Stats -->
          <div class="card-flat pa-8 text-center" style="background: var(--color-primary); border-radius: var(--radius-xl);">
            <h2 class="text-h4 font-weight-bold mb-6 text-white">{{ locale === 'ar' ? 'بالأرقام' : 'By the Numbers' }}</h2>
            <div class="row">
              <div v-for="stat in aboutStats" :key="stat.label" class="col-6 col-md-3">
                <div class="text-h3 font-weight-bold text-white">{{ stat.value }}</div>
                <div class="text-body-2 text-white" style="opacity: 0.8">{{ locale === 'ar' ? stat.labelAr : stat.labelEn }}</div>
              </div>
            </div>
          </div>

          <!-- CTA -->
          <div class="text-center mt-12">
            <h2 class="text-h5 font-weight-bold mb-4">{{ locale === 'ar' ? 'جاهز للبدء؟' : 'Ready to get started?' }}</h2>
            <router-link to="/browse" class="btn btn-primary btn-xl mr-4">
              <i class="mdi mdi-magnify"></i>
              {{ locale === 'ar' ? 'تصفح الإعلانات' : 'Browse Listings' }}
            </router-link>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank" class="btn btn-outline btn-xl">
              <i class="mdi mdi-google-play"></i>
              {{ locale === 'ar' ? 'حمل التطبيق' : 'Download App' }}
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

const appStore = useAppStore()
const { updateMeta, setOrganizationSchema, setFaqSchema } = useSeo()
const locale = computed(() => appStore.locale)

const values = [
  { icon: 'mdi-shield-check', color: '#10B981', titleAr: 'الأمان', titleEn: 'Security', descAr: 'نحمي بياناتك وخصوصيتك بأحدث تقنيات الحماية', descEn: 'We protect your data and privacy with the latest security technologies' },
  { icon: 'mdi-lightning-bolt', color: '#F59E0B', titleAr: 'السرعة', titleEn: 'Speed', descAr: 'منصة سريعة وسهلة الاستخدام مع تجربة سلسة', descEn: 'Fast and easy to use platform with a smooth experience' },
  { icon: 'mdi-heart', color: '#EF4444', titleAr: 'الثقة', titleEn: 'Trust', descAr: 'بناء مجتمع موثوق من المستخدمين المُوثّقين', descEn: 'Building a trusted community of verified users' },
  { icon: 'mdi-earth', color: '#06B6D4', titleAr: 'التغطية', titleEn: 'Coverage', descAr: 'نخدم أكثر من 22 دولة في المنطقة العربية', descEn: 'Serving over 22 countries across the Arab region' },
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
  setFaqSchema([
    { question: locale.value === 'ar' ? 'ما هي طلبنا؟' : 'What is Talabna?', answer: locale.value === 'ar' ? 'طلبنا هي منصة إعلانات مبوبة رائدة تخدم المنطقة العربية، تتيح نشر إعلانات في فئات الوظائف والعقارات والسيارات والأجهزة والخدمات.' : 'Talabna is a leading classified ads platform serving the Arab region, allowing posting in jobs, real estate, cars, electronics, and services categories.' },
    { question: locale.value === 'ar' ? 'هل طلبنا مجانية؟' : 'Is Talabna free?', answer: locale.value === 'ar' ? 'نعم، إنشاء الحساب ونشر الإعلانات مجاني. يمكنك شراء شارات مميزة لتعزيز ظهور إعلاناتك.' : 'Yes, creating an account and posting ads is free. You can purchase premium badges to boost your listing visibility.' },
    { question: locale.value === 'ar' ? 'كيف أتواصل مع البائع؟' : 'How do I contact a seller?', answer: locale.value === 'ar' ? 'يمكنك التواصل مع البائع عبر الاتصال المباشر أو واتساب من صفحة الإعلان.' : 'You can contact the seller via direct call or WhatsApp from the listing page.' },
    { question: locale.value === 'ar' ? 'ما هي الدول المدعومة؟' : 'What countries are supported?', answer: locale.value === 'ar' ? 'نخدم أكثر من 22 دولة عربية تشمل فلسطين، مصر، السعودية، الأردن، العراق، الإمارات وغيرها.' : 'We serve over 22 Arab countries including Palestine, Egypt, Saudi Arabia, Jordan, Iraq, UAE and more.' },
  ])
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%); }
</style>
