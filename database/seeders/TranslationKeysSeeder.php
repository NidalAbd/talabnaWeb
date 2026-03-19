<?php

namespace Database\Seeders;

use App\Models\Translation;
use Illuminate\Database\Seeder;

class TranslationKeysSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            // ==================== COMMON ====================
            'common' => [
                'retry' => ['ar' => 'إعادة المحاولة', 'en' => 'Retry'],
                'cancel' => ['ar' => 'إلغاء', 'en' => 'Cancel'],
                'ok' => ['ar' => 'حسناً', 'en' => 'OK'],
                'close' => ['ar' => 'إغلاق', 'en' => 'Close'],
                'save' => ['ar' => 'حفظ', 'en' => 'Save'],
                'delete' => ['ar' => 'حذف', 'en' => 'Delete'],
                'error' => ['ar' => 'خطأ', 'en' => 'Error'],
                'success' => ['ar' => 'نجاح', 'en' => 'Success'],
                'loading' => ['ar' => 'جاري التحميل...', 'en' => 'Loading...'],
                'refresh' => ['ar' => 'تحديث', 'en' => 'Refresh'],
                'search' => ['ar' => 'بحث', 'en' => 'Search'],
                'points' => ['ar' => 'نقاط', 'en' => 'points'],
                'days' => ['ar' => 'أيام', 'en' => 'days'],
                'month' => ['ar' => 'شهر', 'en' => 'month'],
                'primary' => ['ar' => 'رئيسي', 'en' => 'Primary'],
                'go_back' => ['ar' => 'رجوع', 'en' => 'Go Back'],
                'try_again' => ['ar' => 'حاول مرة أخرى', 'en' => 'Try Again'],
                'not_found' => ['ar' => 'غير موجود', 'en' => 'Not Found'],
                'unauthorized' => ['ar' => 'غير مصرح', 'en' => 'Unauthorized'],
            ],

            // ==================== AUTH ====================
            'auth' => [
                'login' => ['ar' => 'تسجيل الدخول', 'en' => 'Login'],
                'register' => ['ar' => 'تسجيل', 'en' => 'Register'],
                'logout' => ['ar' => 'تسجيل الخروج', 'en' => 'Logout'],
                'email' => ['ar' => 'البريد الإلكتروني', 'en' => 'Email'],
                'password' => ['ar' => 'كلمة المرور', 'en' => 'Password'],
                'forgot_password' => ['ar' => 'نسيت كلمة المرور؟', 'en' => 'Forgot password?'],
                'google' => ['ar' => 'جوجل', 'en' => 'Google'],
                'facebook' => ['ar' => 'فيسبوك', 'en' => 'Facebook'],
                'logged_out' => ['ar' => 'تم تسجيل الخروج بنجاح', 'en' => 'Logged out successfully'],
            ],

            // ==================== VALIDATION ====================
            'validation' => [
                'required' => ['ar' => 'هذا الحقل مطلوب', 'en' => 'This field is required'],
                'email_required' => ['ar' => 'البريد الإلكتروني مطلوب', 'en' => 'Email is required'],
                'invalid_email' => ['ar' => 'بريد إلكتروني غير صالح', 'en' => 'Invalid email'],
                'password_required' => ['ar' => 'كلمة المرور مطلوبة', 'en' => 'Password is required'],
            ],

            // ==================== POST ====================
            'post' => [
                'title' => ['ar' => 'العنوان', 'en' => 'Title'],
                'description' => ['ar' => 'الوصف', 'en' => 'Description'],
                'add_language' => ['ar' => 'إضافة لغة', 'en' => 'Add Language'],
                'all_languages_added' => ['ar' => 'تمت إضافة جميع اللغات المتاحة', 'en' => 'All available languages have been added'],
                'create_success' => ['ar' => 'تم إنشاء المنشور بنجاح', 'en' => 'Post created successfully'],
                'update_success' => ['ar' => 'تم تحديث المنشور بنجاح', 'en' => 'Post updated successfully'],
                'delete_success' => ['ar' => 'تم حذف المنشور بنجاح', 'en' => 'Post deleted successfully'],
                'delete_confirm' => ['ar' => 'حذف هذا المنشور؟', 'en' => 'Delete this post?'],
                'detecting_location' => ['ar' => 'جاري تحديد الموقع...', 'en' => 'Detecting location...'],
                'select_category' => ['ar' => 'الرجاء اختيار التصنيف والتصنيف الفرعي', 'en' => 'Please select a category and subcategory'],
                'post_text' => ['ar' => 'نص المنشور', 'en' => 'Post Text'],
                'media_type' => ['ar' => 'نوع الوسائط', 'en' => 'Media Type'],
                'photo' => ['ar' => 'صورة', 'en' => 'Photo'],
                'video' => ['ar' => 'فيديو', 'en' => 'Video'],
                'sound' => ['ar' => 'صوت', 'en' => 'Sound'],
                'price' => ['ar' => 'السعر', 'en' => 'Price'],
                'city' => ['ar' => 'المدينة', 'en' => 'City'],
                'verified' => ['ar' => 'تم التحقق من المنشور', 'en' => 'Post has been verified'],
                'error_load' => ['ar' => 'حدث خطأ، اضغط زر التحديث', 'en' => 'Some error happened, press refresh button'],
                'loading_post' => ['ar' => 'جاري تحميل المنشور', 'en' => 'Loading Post'],
            ],

            // ==================== CONTACT ====================
            'contact' => [
                'call' => ['ar' => 'اتصال', 'en' => 'Call'],
                'whatsapp' => ['ar' => 'واتساب', 'en' => 'WhatsApp'],
                'email' => ['ar' => 'بريد إلكتروني', 'en' => 'Email'],
                'chat' => ['ar' => 'محادثة', 'en' => 'Chat'],
                'report' => ['ar' => 'إبلاغ', 'en' => 'Report'],
                'location' => ['ar' => 'الموقع', 'en' => 'Location'],
                'view_on_map' => ['ar' => 'عرض على الخريطة', 'en' => 'View on map'],
                'add_to_contacts' => ['ar' => 'إضافة لجهات الاتصال', 'en' => 'Add to contacts'],
                'no_whatsapp' => ['ar' => 'لا يوجد رقم واتساب', 'en' => 'No WhatsApp number available'],
                'no_phone' => ['ar' => 'لا يوجد رقم هاتف', 'en' => 'No phone number available'],
                'no_email' => ['ar' => 'لا يوجد بريد إلكتروني', 'en' => 'No email available'],
                'launch_failed' => ['ar' => 'تعذر فتح التطبيق', 'en' => 'Could not launch app'],
                'copy_link' => ['ar' => 'نسخ الرابط', 'en' => 'Copy link'],
                'link_copied' => ['ar' => 'تم نسخ الرابط', 'en' => 'Link copied to clipboard'],
                'not_interested' => ['ar' => 'غير مهتم', 'en' => 'Not interested'],
            ],

            // ==================== SEARCH ====================
            'search' => [
                'hint' => ['ar' => 'ابحث عن مستخدمين، منشورات، والمزيد...', 'en' => 'Search users, posts, and more...'],
                'users_count' => ['ar' => 'المستخدمون', 'en' => 'Users'],
                'posts_count' => ['ar' => 'المنشورات', 'en' => 'Posts'],
            ],

            // ==================== PROFILE ====================
            'profile' => [
                'update_profile' => ['ar' => 'تحديث الملف الشخصي', 'en' => 'Update Profile'],
                'id_copied' => ['ar' => 'تم نسخ المعرف', 'en' => 'ID copied to clipboard'],
                'no_profile_data' => ['ar' => 'لا توجد بيانات ملف شخصي', 'en' => 'No user profile data found'],
                'no_seller' => ['ar' => 'لا يوجد بائعين', 'en' => 'No Seller found'],
                'no_followers' => ['ar' => 'لا يوجد متابعين', 'en' => 'No followers found'],
            ],

            // ==================== ADMIN ====================
            'admin' => [
                'title' => ['ar' => 'مشرف', 'en' => 'Admin'],
                'request_monitor' => ['ar' => 'مراقب الطلبات', 'en' => 'Request Monitor'],
                'admin_action' => ['ar' => 'إجراء المشرف', 'en' => 'Admin Action'],
                'delete_only' => ['ar' => 'حذف فقط', 'en' => 'Delete Only'],
                'verify_post' => ['ar' => 'التحقق من المنشور', 'en' => 'Verify Post'],
                'toggle_ban' => ['ar' => 'تبديل الحظر', 'en' => 'Toggle User Ban'],
                'ban_success' => ['ar' => 'تم حظر المستخدم بنجاح', 'en' => 'User has been banned successfully'],
                'unban_success' => ['ar' => 'تم إلغاء حظر المستخدم بنجاح', 'en' => 'User has been unbanned successfully'],
                'ban_failed' => ['ar' => 'فشل في حظر المستخدم', 'en' => 'Failed to ban user. Please try again.'],
                'unban_failed' => ['ar' => 'فشل في إلغاء حظر المستخدم', 'en' => 'Failed to unban user. Please try again.'],
                'controls' => ['ar' => 'أدوات التحكم', 'en' => 'Admin Service Post Controls'],
                'processing' => ['ar' => 'جاري المعالجة...', 'en' => 'Processing...'],
            ],

            // ==================== SUBSCRIPTION ====================
            'subscription' => [
                'title' => ['ar' => 'خطط الاشتراك', 'en' => 'Subscription Plans'],
                'choose_plan' => ['ar' => 'اختر خطة', 'en' => 'Choose a Plan'],
                'other_plans' => ['ar' => 'خطط أخرى', 'en' => 'Other Plans'],
                'current' => ['ar' => 'الخطة الحالية', 'en' => 'Current Plan'],
                'current_plan' => ['ar' => 'الخطة الحالية', 'en' => 'Current Plan'],
                'subscribe' => ['ar' => 'اشتراك', 'en' => 'Subscribe'],
                'confirm' => ['ar' => 'تأكيد الاشتراك', 'en' => 'Confirm Subscription'],
                'confirm_message' => ['ar' => 'اشتراك في', 'en' => 'Subscribe to'],
                'for' => ['ar' => 'مقابل', 'en' => 'for'],
                'expires_in' => ['ar' => 'ينتهي خلال', 'en' => 'Expires in'],
                'popular' => ['ar' => 'الأكثر شعبية', 'en' => 'Popular'],
                'already_subscribed' => ['ar' => 'مشترك بالفعل', 'en' => 'Already Subscribed'],
                'ai_images' => ['ar' => 'صور الذكاء الاصطناعي', 'en' => 'AI Images'],
                'ai_images_month' => ['ar' => 'صور ذكاء اصطناعي/شهر', 'en' => 'AI images/month'],
                'featured_posts' => ['ar' => 'منشورات مميزة', 'en' => 'featured posts'],
                'auto_translate' => ['ar' => 'ترجمة تلقائية للمنشورات', 'en' => 'Auto-translate posts'],
                'badge_discount' => ['ar' => 'خصم على الشارات', 'en' => 'badge discount'],
                'bonus_points' => ['ar' => 'نقاط إضافية', 'en' => 'bonus points'],
                'priority_support' => ['ar' => 'دعم ذو أولوية', 'en' => 'Priority support'],
                'max_photos' => ['ar' => 'صور لكل منشور', 'en' => 'photos per post'],
            ],

            // ==================== ERROR ====================
            'error' => [
                'video_load_failed' => ['ar' => 'فشل في تحميل الفيديو', 'en' => 'Failed to load video'],
                'video_compression' => ['ar' => 'خطأ في ضغط الفيديو', 'en' => 'Video Compression Error'],
                'video_compress_failed' => ['ar' => 'فشل في ضغط وتحويل الفيديو', 'en' => 'Failed to compress and convert the video'],
                'invalid_video' => ['ar' => 'فيديو غير صالح', 'en' => 'Invalid video'],
                'invalid_image' => ['ar' => 'صورة غير صالحة', 'en' => 'Invalid image'],
                'invalid_location' => ['ar' => 'إحداثيات الموقع غير صالحة', 'en' => 'Invalid location coordinates'],
                'image_processing' => ['ar' => 'خطأ في معالجة الصور', 'en' => 'Error processing images'],
                'video_processing' => ['ar' => 'خطأ في معالجة الفيديو', 'en' => 'Error processing video'],
                'no_media' => ['ar' => 'لا توجد وسائط متاحة', 'en' => 'No media available'],
                'unable_init' => ['ar' => 'تعذر تشغيل التطبيق', 'en' => 'Unable to initialize the app'],
                'notification_update' => ['ar' => 'خطأ في تحديث الإشعارات', 'en' => 'Error updating notifications'],
                'data_saver_update' => ['ar' => 'خطأ في تحديث وضع توفير البيانات', 'en' => 'Error updating data saver mode'],
                'failed_load' => ['ar' => 'فشل في تحميل البيانات', 'en' => 'Failed to load data'],
            ],

            // ==================== HELP CENTER ====================
            'help' => [
                'account_issues' => ['ar' => 'مشاكل الحساب', 'en' => 'Account Issues'],
                'reset_password' => ['ar' => 'كيفية إعادة تعيين كلمة المرور', 'en' => 'How to reset password'],
                'update_profile' => ['ar' => 'كيفية تحديث معلومات الملف الشخصي', 'en' => 'How to update profile'],
                'app_features' => ['ar' => 'ميزات التطبيق', 'en' => 'App Features'],
                'change_language' => ['ar' => 'كيفية تغيير اللغة', 'en' => 'How to change language'],
                'dark_light_mode' => ['ar' => 'كيفية التبديل بين الوضع المظلم/الفاتح', 'en' => 'How to toggle dark/light mode'],
                'troubleshooting' => ['ar' => 'حل المشكلات', 'en' => 'Troubleshooting'],
                'app_crash' => ['ar' => 'انهيار التطبيق', 'en' => 'App crashing'],
                'connectivity' => ['ar' => 'مشاكل الاتصال', 'en' => 'Connectivity issues'],
            ],

            // ==================== API RESPONSES (Laravel) ====================
            'api' => [
                'user_not_found' => ['ar' => 'المستخدم غير موجود', 'en' => 'User not found'],
                'post_not_found' => ['ar' => 'المنشور غير موجود', 'en' => 'Post not found'],
                'category_not_found' => ['ar' => 'التصنيف غير موجود', 'en' => 'Category not found'],
                'invalid_badge_type' => ['ar' => 'نوع الشارة غير صالح', 'en' => 'Invalid badge type'],
                'badge_not_active' => ['ar' => 'نوع الشارة غير نشط', 'en' => 'Badge type is not active'],
                'insufficient_points' => ['ar' => 'رصيد النقاط غير كافي', 'en' => 'Insufficient points'],
                'invalid_report' => ['ar' => 'نوع البلاغ غير صالح', 'en' => 'Invalid report type'],
                'login_required' => ['ar' => 'يجب تسجيل الدخول', 'en' => 'You need to log in'],
                'created_success' => ['ar' => 'تم الإنشاء بنجاح', 'en' => 'Created successfully'],
                'updated_success' => ['ar' => 'تم التحديث بنجاح', 'en' => 'Updated successfully'],
                'deleted_success' => ['ar' => 'تم الحذف بنجاح', 'en' => 'Deleted successfully'],
                'comment_deleted' => ['ar' => 'تم حذف التعليق', 'en' => 'Comment deleted'],
                'message_sent' => ['ar' => 'تم إرسال الرسالة بنجاح', 'en' => 'Message sent successfully'],
                'message_failed' => ['ar' => 'فشل في إرسال الرسالة', 'en' => 'Failed to send message'],
                'invalid_payload' => ['ar' => 'بيانات غير صالحة', 'en' => 'Invalid payload'],
                'invalid_signature' => ['ar' => 'توقيع غير صالح', 'en' => 'Invalid signature'],
                'report_submitted' => ['ar' => 'تم تقديم البلاغ بنجاح. سيقوم فريقنا بمراجعته قريباً', 'en' => 'Report submitted successfully. Our team will review it shortly.'],
            ],

            // ==================== LANGUAGE ====================
            'language' => [
                'not_available' => ['ar' => 'هذه اللغة غير متوفرة حالياً', 'en' => 'This language is not available'],
                'changed' => ['ar' => 'تم تغيير اللغة بنجاح', 'en' => 'Language changed successfully'],
                'changing' => ['ar' => 'جاري تغيير اللغة...', 'en' => 'Changing language...'],
            ],
        ];

        foreach ($translations as $group => $keys) {
            foreach ($keys as $key => $locales) {
                foreach ($locales as $locale => $value) {
                    Translation::setTranslation($locale, $group, $key, $value);
                }
            }
        }
    }
}
