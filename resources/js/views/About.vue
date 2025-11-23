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
        <v-col cols="12" md="8">
          <v-card variant="outlined" class="pa-8">
            <h2 class="text-h4 font-weight-bold mb-6">{{ locale === 'ar' ? 'رؤيتنا' : 'Our Vision' }}</h2>
            <p class="text-body-1 mb-6">
              {{ locale === 'ar'
                ? 'طلبنا هي منصة رائدة للإعلانات المبوبة تهدف إلى ربط البائعين والمشترين بطريقة سهلة وآمنة. نسعى لتوفير تجربة مستخدم استثنائية تجعل عملية البيع والشراء أسهل من أي وقت مضى.'
                : 'Talabna is a leading classified ads platform that aims to connect sellers and buyers in an easy and secure way. We strive to provide an exceptional user experience that makes buying and selling easier than ever.'
              }}
            </p>

            <h2 class="text-h4 font-weight-bold mb-6 mt-12">{{ locale === 'ar' ? 'قيمنا' : 'Our Values' }}</h2>
            <v-row>
              <v-col cols="12" sm="4" v-for="value in values" :key="value.icon">
                <div class="text-center pa-4">
                  <v-avatar color="primary" size="64" class="mb-4">
                    <v-icon size="32" color="white">{{ value.icon }}</v-icon>
                  </v-avatar>
                  <h3 class="text-h6 font-weight-bold mb-2">{{ locale === 'ar' ? value.titleAr : value.titleEn }}</h3>
                  <p class="text-body-2 text-medium-emphasis">{{ locale === 'ar' ? value.descAr : value.descEn }}</p>
                </div>
              </v-col>
            </v-row>
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

const values = [
  { icon: 'mdi-shield-check', titleAr: 'الأمان', titleEn: 'Security', descAr: 'نحمي بياناتك وخصوصيتك', descEn: 'We protect your data and privacy' },
  { icon: 'mdi-lightning-bolt', titleAr: 'السرعة', titleEn: 'Speed', descAr: 'منصة سريعة وسهلة الاستخدام', descEn: 'Fast and easy to use platform' },
  { icon: 'mdi-heart', titleAr: 'الثقة', titleEn: 'Trust', descAr: 'بناء مجتمع موثوق', descEn: 'Building a trusted community' },
]

onMounted(() => {
  updateMeta({ title: locale.value === 'ar' ? 'من نحن - طلبنا' : 'About Us - Talabna' })
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
</style>
