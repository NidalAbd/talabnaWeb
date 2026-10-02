<template>
  <div class="privacy-page">
    <section class="hero-gradient py-12">
      <div class="container text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ t('privacy.title') }}</h1>
        <p class="text-body-1 text-white mt-2" style="opacity:0.8">{{ locale === 'ar' ? 'آخر تحديث: 2 أكتوبر 2026' : 'Last updated: October 2, 2026' }}</p>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-8">
          <div class="card pa-8">
            <div class="policy-content" v-if="locale === 'ar'">
              <h2>مقدمة</h2>
              <p>طلبنا سوق إلكتروني (تطبيق وموقع) يمكنك من خلاله نشر وتصفح إعلانات الوظائف والعقارات والسيارات والأجهزة والخدمات، ومشاركة مقاطع فيديو قصيرة (ريلز)، والدردشة مع المستخدمين الآخرين. توضح هذه السياسة المعلومات التي نجمعها، ولماذا نجمعها، ومع من نشاركها، ومدة احتفاظنا بها، والخيارات المتاحة لك.</p>

              <h2>المعلومات التي نجمعها</h2>
              <p>نجمع فقط المعلومات اللازمة لتشغيل السوق:</p>
              <ul>
                <li><strong>بيانات الحساب:</strong> الاسم والبريد الإلكتروني ورقم الهاتف وتاريخ الميلاد والجنس وصورة الملف الشخصي، ومعرّف تسجيل الدخول عبر Google أو Apple إذا استخدمته.</li>
                <li><strong>الإعلانات والريلز:</strong> العناوين والأوصاف والأسعار والفئات والصور ومقاطع الفيديو التي تنشرها، وهي مرئية للمستخدمين الآخرين.</li>
                <li><strong>الموقع:</strong> الدولة والمدينة والموقع الذي ترفقه بالإعلان، وموقع جهازك فقط عندما تسمح بذلك، لعرض الإعلانات القريبة منك.</li>
                <li><strong>الرسائل:</strong> المحادثات التي تتبادلها مع المستخدمين الآخرين، ونحفظها ليتمكن الطرفان من قراءتها.</li>
                <li><strong>النقاط والمشتريات:</strong> رصيد نقاطك وسجلها وتحويلات النقاط بين المستخدمين، وسجلات الشراء من App Store أو Google Play. لا نتلقى بيانات بطاقتك أبدًا.</li>
                <li><strong>بيانات الجهاز:</strong> طراز الجهاز ونظام التشغيل وإصدار التطبيق وعنوان IP ورمز الإشعارات.</li>
              </ul>

              <h2>كيف نستخدم معلوماتك</h2>
              <ul>
                <li>إنشاء حسابك وإدارته وتسجيل دخولك</li>
                <li>نشر إعلاناتك ومقاطع الريلز وعرضها لمن يتصفح حسب الفئة أو الموقع</li>
                <li>توصيل رسائل الدردشة وإشعارك بالنشاط على حسابك وإعلاناتك</li>
                <li>إضافة النقاط والاشتراكات المشتراة وتنفيذ تحويلات النقاط والاحتفاظ بسجلها</li>
                <li>تشغيل أدوات الذكاء الاصطناعي الاختيارية التي تختار استخدامها، مثل تحسين الوصف أو ترجمة الإعلان أو اقتراح الفئة أو السعر أو إنشاء صورة</li>
                <li>منع الرسائل المزعجة والاحتيال والمحتوى المخالف لقواعدنا، ومراجعة المحتوى والمستخدمين الذين تبلّغ عنهم، ومنع المستخدمين الذين تحظرهم من التواصل معك</li>
                <li>الرد على طلبات الدعم وحل المشكلات</li>
              </ul>

              <h2>مشاركة البيانات</h2>
              <p>لا نبيع بياناتك الشخصية ولا نستخدمها لإعلانات جهات خارجية. نشاركها فقط في الحالات التالية:</p>
              <ul>
                <li><strong>المستخدمون الآخرون:</strong> إعلاناتك ومقاطع الريلز وملفك العام وأي بيانات تواصل تضيفها إلى الإعلان تكون مرئية للمستخدمين الآخرين.</li>
                <li><strong>مزودو الخدمات:</strong> Google Firebase (الإشعارات)، وخرائط Google (الخرائط والموقع)، وGoogle وApple (تسجيل الدخول)، وApp Store وGoogle Play (المدفوعات)، وHostinger (الاستضافة).</li>
                <li><strong>مزود الذكاء الاصطناعي:</strong> عند استخدامك لأداة ذكاء اصطناعي، يُرسل النص أو الصورة التي تقدمها إلى OpenAI فقط لإنتاج تلك النتيجة.</li>
                <li><strong>أسباب قانونية:</strong> عندما يتطلب القانون ذلك، أو لحماية المستخدمين من الاحتيال أو الضرر.</li>
              </ul>

              <h2>الاحتفاظ بالبيانات وحذف الحساب</h2>
              <p>نحتفظ بمعلوماتك طالما كان حسابك نشطًا. يمكنك حذف حسابك في أي وقت من التطبيق عبر <strong>الإعدادات ← حذف الحساب</strong>، أو بمراسلتنا. يؤدي حذف الحساب إلى إزالة ملفك الشخصي وإعلاناتك ومقاطع الريلز وسجل النقاط، باستثناء السجلات التي يلزمنا القانون بالاحتفاظ بها (مثل سجلات الشراء).</p>

              <h2>حماية البيانات</h2>
              <p>نحمي بياناتك باستخدام اتصالات مشفرة (HTTPS) وضوابط وصول على خوادمنا، ورمز PIN لتأكيد تحويلات النقاط. لا يوجد نظام آمن بالكامل، لذا يرجى عدم مشاركة كلمات المرور أو بيانات الدفع في الدردشات.</p>

              <h2>الأطفال</h2>
              <p>يجب أن يكون عمر مستخدمي طلبنا 18 عامًا أو أكثر، ولا نجمع معلومات الأطفال عن قصد. إذا اعتقدت أن قاصرًا قدم لنا معلومات شخصية، تواصل معنا وسنحذفها.</p>

              <h2>خياراتك</h2>
              <ul>
                <li>تعديل أو حذف إعلاناتك ومقاطع الريلز وملفك الشخصي في أي وقت من التطبيق.</li>
                <li>الإبلاغ عن إعلان أو ريلز أو مستخدم، وحظر المستخدمين؛ ويمكنك إدارة المستخدمين المحظورين من الإعدادات.</li>
                <li>تشغيل أو إيقاف الوصول إلى الموقع والإشعارات من إعدادات جهازك.</li>
                <li>طلب نسخة من بياناتك أو حذفها بالتواصل معنا.</li>
              </ul>

              <h2>التواصل معنا</h2>
              <p>لأي استفسار حول سياسة الخصوصية أو لطلب الاطلاع على بياناتك أو حذفها، تواصل معنا على: <a href="mailto:support@talbna.cloud">support@talbna.cloud</a></p>
            </div>

            <div class="policy-content" v-else>
              <h2>Introduction</h2>
              <p>Talabna is a marketplace app and website where you can post and browse listings for jobs, real estate, cars, devices and services, share short video reels, and chat with other users. This policy explains what information we collect, why we collect it, who we share it with, how long we keep it, and the choices you have.</p>

              <h2>Information We Collect</h2>
              <p>We collect only the information needed to run the marketplace:</p>
              <ul>
                <li><strong>Account data:</strong> name, email address, phone number, date of birth, gender, profile photo, and your Google or Apple sign-in identifier if you use one.</li>
                <li><strong>Listings and reels:</strong> titles, descriptions, prices, categories, photos and videos you publish. These are visible to other users.</li>
                <li><strong>Location:</strong> your country and city, the location you attach to a listing, and your device location only when you allow it, to show listings near you.</li>
                <li><strong>Messages:</strong> chats you exchange with other users, stored so both sides can read them.</li>
                <li><strong>Points and purchases:</strong> your points balance and history, points transfers between users, and App Store or Google Play purchase records. We never receive your card details.</li>
                <li><strong>Device data:</strong> device model, operating system, app version, IP address and a push-notification token.</li>
              </ul>

              <h2>How We Use Your Information</h2>
              <ul>
                <li>Create and manage your account and sign you in</li>
                <li>Publish your listings and reels and show them to people browsing by category or location</li>
                <li>Deliver chat messages and notify you about activity on your account and listings</li>
                <li>Credit purchased points and subscriptions, process points transfers, and keep your points history</li>
                <li>Run the optional AI tools you choose to use, such as improving a description, translating a listing, suggesting a category or price, or generating an image</li>
                <li>Prevent spam, fraud and content that breaks our rules, review content and users you report, and stop users you block from contacting you</li>
                <li>Answer your support requests and fix problems</li>
              </ul>

              <h2>Data Sharing</h2>
              <p>We do not sell your personal information and we do not use it for third-party advertising. We share it only in these cases:</p>
              <ul>
                <li><strong>Other users:</strong> your listings, reels, public profile and any contact details you add to a listing are visible to other users.</li>
                <li><strong>Service providers:</strong> Google Firebase (notifications), Google Maps (maps and location), Google and Apple (sign-in), Apple App Store and Google Play (payments), and Hostinger (hosting).</li>
                <li><strong>AI provider:</strong> when you use an AI tool, the text or image you submit is sent to OpenAI only to produce that result.</li>
                <li><strong>Legal reasons:</strong> when the law requires it, or to protect users from fraud or harm.</li>
              </ul>

              <h2>Data Retention and Account Deletion</h2>
              <p>We keep your information while your account is active. You can delete your account at any time in the app from <strong>Settings → Delete Account</strong>, or by emailing us. Deleting your account removes your profile, listings, reels and points history, except records we must keep by law (for example purchase records).</p>

              <h2>Data Protection</h2>
              <p>We protect your data with encrypted connections (HTTPS), access controls on our servers, and a PIN to confirm points transfers. No system is perfectly secure, so please do not share passwords or payment details in chats.</p>

              <h2>Children</h2>
              <p>You must be 18 or older to use Talabna, and we do not knowingly collect information from children. If you believe a minor has given us personal information, contact us and we will delete it.</p>

              <h2>Your Choices</h2>
              <ul>
                <li>Edit or delete your listings, reels and profile at any time in the app.</li>
                <li>Report a listing, reel or user, and block users you don't want to hear from; you can manage blocked users in Settings.</li>
                <li>Turn location access and notifications on or off in your device settings.</li>
                <li>Request a copy of your data or its deletion by contacting us.</li>
              </ul>

              <h2>Contact Us</h2>
              <p>For questions about this privacy policy, or to request access to or deletion of your data, contact us at: <a href="mailto:support@talbna.cloud">support@talbna.cloud</a></p>
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
    title: t('privacy.title') + ' - ' + t('app.name'),
    description: t('privacy.title') + ' - ' + t('app.tagline'),
  })
})
</script>

<style scoped>
.hero-gradient { background: linear-gradient(160deg, #0a1628 0%, #1a3a5c 40%, #1565c0 100%); }
.policy-content h2 { font-size: 1.5rem; font-weight: 600; margin: 2rem 0 1rem; }
.policy-content p { line-height: 1.8; margin-bottom: 1rem; color: var(--color-text-muted); }
.policy-content ul { margin-bottom: 1rem; padding-left: 1.5rem; list-style: disc; }
.policy-content li { line-height: 1.8; margin-bottom: 0.5rem; color: var(--color-text-muted); }
</style>
