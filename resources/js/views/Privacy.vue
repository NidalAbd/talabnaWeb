<template>
  <div class="privacy-page">
    <section class="hero-gradient py-12">
      <v-container class="text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ locale === 'ar' ? 'سياسة الخصوصية' : 'Privacy Policy' }}</h1>
        <p class="text-body-1 text-white mt-2" style="opacity:0.8">{{ locale === 'ar' ? 'آخر تحديث: مارس 2026' : 'Last updated: March 2026' }}</p>
      </v-container>
    </section>

    <v-container class="py-12">
      <v-row justify="center">
        <v-col cols="12" md="8">
          <v-card variant="outlined" class="pa-8">
            <div class="policy-content" v-if="locale === 'ar'">
              <h2>مقدمة</h2>
              <p>نحن في طلبنا نلتزم بحماية خصوصيتك وبياناتك الشخصية. توضح سياسة الخصوصية هذه كيفية جمع واستخدام وحماية معلوماتك عند استخدام منصتنا وتطبيقنا.</p>

              <h2>المعلومات التي نجمعها</h2>
              <p>نجمع الأنواع التالية من المعلومات:</p>
              <ul>
                <li><strong>معلومات الحساب:</strong> الاسم، البريد الإلكتروني، رقم الهاتف، تاريخ الميلاد، والجنس عند إنشاء حسابك.</li>
                <li><strong>معلومات الإعلانات:</strong> عناوين وأوصاف وصور الإعلانات التي تنشرها.</li>
                <li><strong>معلومات الموقع:</strong> الدولة والمدينة لعرض المحتوى المناسب لمنطقتك.</li>
                <li><strong>معلومات الجهاز:</strong> نوع الجهاز، نظام التشغيل، ومعرف الجهاز لإرسال الإشعارات.</li>
                <li><strong>بيانات الاستخدام:</strong> كيفية تفاعلك مع المنصة لتحسين تجربتك.</li>
              </ul>

              <h2>كيف نستخدم معلوماتك</h2>
              <ul>
                <li>تقديم خدماتنا وتحسينها وتخصيصها</li>
                <li>التواصل معك بشأن حسابك وإعلاناتك</li>
                <li>إرسال إشعارات مهمة حول المنصة</li>
                <li>ضمان أمان المنصة ومنع الاحتيال</li>
                <li>الامتثال للمتطلبات القانونية</li>
              </ul>

              <h2>حماية البيانات</h2>
              <p>نستخدم تدابير أمنية متقدمة لحماية بياناتك بما في ذلك التشفير والحماية بكلمة مرور ورمز PIN للتحويلات المالية.</p>

              <h2>مشاركة البيانات</h2>
              <p>لا نبيع أو نؤجر بياناتك الشخصية لأطراف ثالثة. قد نشارك بيانات محدودة مع:</p>
              <ul>
                <li>مزودي الخدمات الذين يساعدوننا في تشغيل المنصة (مثل خدمات الدفع والاستضافة)</li>
                <li>الجهات القانونية عند الطلب بموجب القانون</li>
              </ul>

              <h2>حقوقك</h2>
              <p>يمكنك في أي وقت تعديل بياناتك الشخصية أو حذف حسابك أو طلب نسخة من بياناتك عن طريق التواصل معنا على support@talbna.cloud.</p>

              <h2>التواصل معنا</h2>
              <p>لأي استفسارات حول سياسة الخصوصية، تواصل معنا على: support@talbna.cloud</p>
            </div>

            <div class="policy-content" v-else>
              <h2>Introduction</h2>
              <p>At Talabna, we are committed to protecting your privacy and personal data. This privacy policy explains how we collect, use, and protect your information when you use our platform and application.</p>

              <h2>Information We Collect</h2>
              <p>We collect the following types of information:</p>
              <ul>
                <li><strong>Account Information:</strong> Name, email, phone number, date of birth, and gender when you create your account.</li>
                <li><strong>Listing Information:</strong> Titles, descriptions, and photos of listings you publish.</li>
                <li><strong>Location Information:</strong> Country and city to display relevant content for your area.</li>
                <li><strong>Device Information:</strong> Device type, OS, and device ID for sending notifications.</li>
                <li><strong>Usage Data:</strong> How you interact with the platform to improve your experience.</li>
              </ul>

              <h2>How We Use Your Information</h2>
              <ul>
                <li>Provide, improve, and personalize our services</li>
                <li>Communicate with you about your account and listings</li>
                <li>Send important notifications about the platform</li>
                <li>Ensure platform security and prevent fraud</li>
                <li>Comply with legal requirements</li>
              </ul>

              <h2>Data Protection</h2>
              <p>We use advanced security measures to protect your data including encryption, password protection, and PIN codes for financial transfers.</p>

              <h2>Data Sharing</h2>
              <p>We do not sell or rent your personal data to third parties. We may share limited data with:</p>
              <ul>
                <li>Service providers who help us operate the platform (e.g., payment and hosting services)</li>
                <li>Legal authorities when required by law</li>
              </ul>

              <h2>Your Rights</h2>
              <p>You can at any time edit your personal data, delete your account, or request a copy of your data by contacting us at support@talbna.cloud.</p>

              <h2>Contact Us</h2>
              <p>For any questions about this privacy policy, contact us at: support@talbna.cloud</p>
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

onMounted(() => {
  updateMeta({
    title: locale.value === 'ar' ? 'سياسة الخصوصية - طلبنا' : 'Privacy Policy - Talabna',
    description: locale.value === 'ar' ? 'سياسة الخصوصية لمنصة طلبنا للإعلانات المبوبة' : 'Privacy policy for Talabna classified ads platform',
  })
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(135deg, #5035FF 0%, #7C6AFF 100%); }
.policy-content h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem; }
.policy-content p { line-height: 1.8; margin-bottom: 1rem; color: rgba(var(--v-theme-on-surface), 0.7); }
.policy-content ul { margin-bottom: 1rem; padding-left: 1.5rem; }
.policy-content li { line-height: 1.8; margin-bottom: 0.5rem; color: rgba(var(--v-theme-on-surface), 0.7); }
</style>
