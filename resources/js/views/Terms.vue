<template>
  <div class="terms-page">
    <section class="hero-gradient py-12">
      <div class="container text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ t('terms.title') }}</h1>
        <p class="text-body-1 text-white mt-2" style="opacity:0.8">{{ t('terms.updated') }}</p>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-8">
          <div class="card pa-8">
            <div class="terms-content" v-if="locale === 'ar'">
              <h2>قبول الشروط</h2>
              <p>باستخدام منصة طلبنا أو تطبيقها، فإنك توافق على الالتزام بهذه الشروط والأحكام. إذا كنت لا توافق على أي جزء من هذه الشروط، يرجى عدم استخدام المنصة.</p>

              <h2>وصف الخدمة</h2>
              <p>طلبنا هي منصة إعلانات مبوبة تتيح للمستخدمين نشر إعلانات في فئات متنوعة تشمل الوظائف، العقارات، السيارات، الأجهزة الإلكترونية، والخدمات المتنوعة. المنصة تعمل كوسيط بين البائعين والمشترين.</p>

              <h2>حساب المستخدم</h2>
              <ul>
                <li>يجب أن تكون بعمر 18 سنة أو أكثر لإنشاء حساب</li>
                <li>أنت مسؤول عن الحفاظ على سرية بيانات حسابك</li>
                <li>يجب تقديم معلومات دقيقة وصحيحة عند التسجيل</li>
                <li>يحق لنا تعليق أو حذف الحسابات المخالفة</li>
              </ul>

              <h2>المحتوى والإعلانات</h2>
              <ul>
                <li>يحظر نشر محتوى غير قانوني أو مسيء أو مضلل</li>
                <li>يحظر نشر إعلانات وهمية أو احتيالية</li>
                <li>أنت المسؤول الوحيد عن محتوى إعلاناتك</li>
                <li>يحق لنا حذف أي إعلان يخالف هذه الشروط</li>
              </ul>

              <h2>نظام النقاط والشارات</h2>
              <ul>
                <li>يمكنك شراء النقاط لترقية إعلاناتك بشارات مميزة</li>
                <li>النقاط غير قابلة للاسترداد بعد الاستخدام</li>
                <li>أسعار النقاط تختلف حسب الدولة</li>
                <li>تحويل النقاط بين المستخدمين يخضع لحدود يومية</li>
              </ul>

              <h2>المسؤولية</h2>
              <p>طلبنا ليست طرفاً في المعاملات بين المستخدمين ولا تتحمل مسؤولية جودة المنتجات أو الخدمات المعروضة. يجب التحقق من المعلومات قبل إتمام أي صفقة.</p>

              <h2>إنهاء الخدمة</h2>
              <p>يحق لنا إنهاء أو تعليق وصولك إلى المنصة في أي وقت وبدون إنذار مسبق في حالة مخالفة هذه الشروط.</p>

              <h2>التواصل</h2>
              <p>للأسئلة حول هذه الشروط، تواصل معنا على: support@talbna.cloud</p>
            </div>

            <div class="terms-content" v-else>
              <h2>Acceptance of Terms</h2>
              <p>By using Talabna platform or its application, you agree to be bound by these terms and conditions. If you do not agree to any part of these terms, please do not use the platform.</p>

              <h2>Service Description</h2>
              <p>Talabna is a classified ads platform that allows users to post listings in various categories including jobs, real estate, cars, electronics, and various services. The platform acts as an intermediary between sellers and buyers.</p>

              <h2>User Account</h2>
              <ul>
                <li>You must be 18 years or older to create an account</li>
                <li>You are responsible for maintaining the confidentiality of your account</li>
                <li>You must provide accurate and truthful information when registering</li>
                <li>We reserve the right to suspend or delete violating accounts</li>
              </ul>

              <h2>Content and Listings</h2>
              <ul>
                <li>Posting illegal, offensive, or misleading content is prohibited</li>
                <li>Posting fake or fraudulent listings is prohibited</li>
                <li>You are solely responsible for the content of your listings</li>
                <li>We reserve the right to remove any listing that violates these terms</li>
              </ul>

              <h2>Points and Badges System</h2>
              <ul>
                <li>You can purchase points to upgrade your listings with premium badges</li>
                <li>Points are non-refundable after use</li>
                <li>Point prices vary by country</li>
                <li>Point transfers between users are subject to daily limits</li>
              </ul>

              <h2>Liability</h2>
              <p>Talabna is not a party to transactions between users and is not responsible for the quality of products or services listed. Information should be verified before completing any deal.</p>

              <h2>Termination</h2>
              <p>We reserve the right to terminate or suspend your access to the platform at any time without prior notice in case of violation of these terms.</p>

              <h2>Contact</h2>
              <p>For questions about these terms, contact us at: support@talbna.cloud</p>
            </div>
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
const { updateMeta } = useSeo()
const locale = computed(() => appStore.locale)

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? 'شروط الاستخدام - طلبنا' : 'Terms of Service - Talabna',
    description: locale.value === 'ar' ? 'شروط استخدام منصة طلبنا للإعلانات المبوبة' : 'Terms of service for Talabna classified ads platform',
  })
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%); }
.terms-content h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem; }
.terms-content p { line-height: 1.8; margin-bottom: 1rem; color: var(--color-text-muted); }
.terms-content ul { margin-bottom: 1rem; padding-left: 1.5rem; list-style: disc; }
.terms-content li { line-height: 1.8; margin-bottom: 0.5rem; color: var(--color-text-muted); }
</style>
