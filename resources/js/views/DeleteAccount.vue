<template>
  <div class="privacy-page">
    <section class="hero-gradient py-12">
      <div class="container text-center">
        <h1 class="text-h3 font-weight-bold text-white">{{ locale === 'ar' ? 'حذف الحساب' : 'Delete Your Account' }}</h1>
      </div>
    </section>

    <div class="container py-12">
      <div class="row" style="justify-content: center;">
        <div class="col-12 col-md-8">
          <div class="card pa-8">
            <div class="policy-content" v-if="locale === 'ar'">
              <h2>داخل التطبيق (فوري)</h2>
              <p>افتح تطبيق طلبنا ثم اذهب إلى <strong>الملف الشخصي ← الإعدادات ← حذف الحساب</strong>، وأكد كلمة المرور (إن وجدت). يتم حذف الحساب فوراً ولا يمكن التراجع عنه.</p>

              <h2>بدون التطبيق</h2>
              <p>إذا لم يعد التطبيق مثبتاً لديك، أرسل بريداً إلكترونياً من عنوان بريدك المسجل إلى <strong>support@talbna.cloud</strong> بعنوان "حذف الحساب"، وسنقوم بحذف حسابك خلال 30 يوماً كحد أقصى.</p>

              <h2>ماذا يتم حذفه</h2>
              <ul>
                <li>معلومات الملف الشخصي (الاسم، البريد الإلكتروني، رقم الهاتف، الصورة الشخصية)</li>
                <li>جميع الإعلانات والصور المرتبطة بها</li>
                <li>التعليقات والمفضلة والمتابعون/المتابَعون</li>
                <li>الإشعارات وسجل النقاط/المشتريات</li>
              </ul>
              <p>هذا الإجراء نهائي ولا يمكن التراجع عنه بعد تنفيذه.</p>
            </div>

            <div class="policy-content" v-else>
              <h2>In the app (immediate)</h2>
              <p>Open the Talabna app and go to <strong>Profile → Settings → Delete Account</strong>, then confirm your password (if your account has one). The account is deleted immediately and cannot be undone.</p>

              <h2>Without the app</h2>
              <p>If you no longer have the app installed, email <strong>support@talbna.cloud</strong> from your registered address with the subject "Delete my account," and we'll delete your account within 30 days.</p>

              <h2>What gets deleted</h2>
              <ul>
                <li>Profile info (name, email, phone number, avatar)</li>
                <li>All listings and their photos</li>
                <li>Comments, favorites, and followers/following</li>
                <li>Notifications and points/purchase history</li>
              </ul>
              <p>This action is permanent and cannot be reversed once completed.</p>
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
    title: t('delete_account.title') + ' - ' + t('app.name'),
    description: t('delete_account.title') + ' - ' + t('app.tagline'),
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
