<?php
/**
 * Admin Panel Health Check - Tests all API endpoints and data integrity
 * Run: php tests/admin_health_check.php
 */
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();

use App\Models\ServicePost;
use App\Models\User;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\countries;
use App\Models\cities;
use App\Models\Language;
use App\Models\BadgeType;

$pass = 0;
$fail = 0;
$warnings = [];

function test($name, $condition, $detail = '') {
    global $pass, $fail;
    if ($condition) {
        $pass++;
        echo "  PASS: {$name}\n";
    } else {
        $fail++;
        echo "  FAIL: {$name}" . ($detail ? " -- {$detail}" : "") . "\n";
    }
}

function warn($msg) {
    global $warnings;
    $warnings[] = $msg;
    echo "  WARN: {$msg}\n";
}

// ============================================================
echo "=== 1. MODEL DATA INTEGRITY ===\n";
// ============================================================

$post = ServicePost::first();
test("ServicePost exists", $post !== null);
if ($post) {
    $rawTitle = $post->getRawOriginal('title');
    $decoded = json_decode($rawTitle, true);
    test("ServicePost title is valid JSON in DB", is_array($decoded) && !empty($decoded));

    $titleValue = $post->title;
    test("ServicePost->title returns string", is_string($titleValue), "got: " . gettype($titleValue));
    test("ServicePost->title not empty", !empty($titleValue));
    test("ServicePost->title not raw JSON", !str_starts_with(trim($titleValue), '{'));

    $descValue = $post->description;
    test("ServicePost->description returns string", is_string($descValue), "got: " . gettype($descValue));

    $arr = $post->toArray();
    test("toArray title is string", is_string($arr['title']), "got: " . gettype($arr['title']));
    test("toArray has translations key", isset($arr['translations']['title']));
    test("toArray translations is array", is_array($arr['translations']['title'] ?? null));

    $rawT = $post->getRawTranslations('title');
    test("getRawTranslations returns array", is_array($rawT));

    $arTitle = $post->translate('title', 'ar');
    test("translate(ar) returns string", is_string($arTitle), "got: " . gettype($arTitle));
}

$cat = Categories::first();
test("Category exists", $cat !== null);
if ($cat) {
    test("Category->name is array", is_array($cat->name), "got: " . gettype($cat->name));
    if (is_array($cat->name)) {
        test("Category has ar key", isset($cat->name['ar']));
        test("Category has en key", isset($cat->name['en']));
    }
}

$sub = Sub_categories::first();
test("SubCategory exists", $sub !== null);
if ($sub) test("SubCategory->name is array", is_array($sub->name), "got: " . gettype($sub->name));

$country = countries::first();
test("Country exists", $country !== null);
if ($country) test("Country->name is array", is_array($country->name), "got: " . gettype($country->name));

$city = cities::first();
test("City exists", $city !== null);
if ($city) test("City->name is array", is_array($city->name), "got: " . gettype($city->name));

test("Languages exist", Language::count() > 0, "count: " . Language::count());

$badge = BadgeType::first();
test("BadgeType exists", $badge !== null);
if ($badge) {
    test("BadgeType has name_ar", !empty($badge->name_ar));
    $validBadges = ['عادي', 'فضي', 'ذهبي', 'ماسي', 'فلسطين'];
    test("BadgeType name_ar valid ENUM", in_array($badge->name_ar, $validBadges), "name_ar: {$badge->name_ar}");
}

// ============================================================
echo "\n=== 2. PHOTO/MEDIA INTEGRITY ===\n";
// ============================================================

$postWithPhotos = ServicePost::whereHas('photos')->with('photos')->first();
if ($postWithPhotos) {
    $photo = $postWithPhotos->photos->first();
    test("Post photo src exists", !empty($photo->src));
    test("Photo no double storage prefix", strpos($photo->src, 'storage/storage') === false, "src: {$photo->src}");

    $cleanSrc = preg_replace('#^/?storage/#', '', $photo->src);
    $exists = file_exists(storage_path("app/public/{$cleanSrc}"));
    test("Photo file exists on disk", $exists || $photo->is_external, "path: {$cleanSrc}");
} else {
    warn("No posts with photos");
}

$userWithPhoto = User::whereHas('photos')->with('photos')->first();
if ($userWithPhoto) {
    $uPhoto = $userWithPhoto->photos->first();
    test("User photo src exists", !empty($uPhoto->src));
    $assetUrl = asset($uPhoto->src);
    test("User photo asset() valid URL", str_starts_with($assetUrl, 'http'), "url: {$assetUrl}");
    test("User photo no double storage", strpos($assetUrl, 'storage/storage') === false);
} else {
    warn("No users with photos");
}

test("og-image.jpg exists", file_exists(storage_path('app/public/photos/og-image.jpg')));

// ============================================================
echo "\n=== 3. ADMIN API RESPONSES ===\n";
// ============================================================

// Posts index
$ctrl = app(\App\Http\Controllers\Admin\ServicePostsApiController::class);
$req = \Illuminate\Http\Request::create('/api/admin/service-posts?per_page=3', 'GET');
try {
    $resp = $ctrl->index($req);
    $data = json_decode($resp->getContent(), true);
    test("Posts index returns 200", $resp->getStatusCode() === 200);
    if (!empty($data['service_posts']['data'])) {
        $fp = $data['service_posts']['data'][0];
        test("API title is string", is_string($fp['title']), "got: " . gettype($fp['title']));
        test("API title not JSON obj", !str_starts_with(trim($fp['title'] ?? ''), '{'));
        test("API description is string", is_string($fp['description'] ?? ''));
        if ($fp['category']) test("API category.name is string", is_string($fp['category']['name']), "got: " . gettype($fp['category']['name']));
        if ($fp['country']) test("API country.name is string", is_string($fp['country']['name']), "got: " . gettype($fp['country']['name']));
        if ($fp['media']) test("API media.src no double storage", strpos($fp['media']['src'] ?? '', 'storage/storage') === false, "src: " . ($fp['media']['src'] ?? ''));
    }
} catch (\Exception $e) {
    test("Posts index API", false, $e->getMessage());
}

// Posts show
if ($post) {
    try {
        $showResp = $ctrl->show($post->id);
        $showData = json_decode($showResp->getContent(), true);
        test("Post show returns 200", $showResp->getStatusCode() === 200);
        test("Show title is string", is_string($showData['post']['title'] ?? null));
        test("Show has title_translations", isset($showData['post']['title_translations']));
        test("Show title_translations is array", is_array($showData['post']['title_translations'] ?? null));
        if (!empty($showData['post']['photos'])) {
            test("Show photo src no double storage", strpos($showData['post']['photos'][0]['src'], 'storage/storage') === false);
        }
    } catch (\Exception $e) {
        test("Post show API", false, $e->getMessage());
    }
}

// Users API
try {
    $usersCtrl = app(\App\Http\Controllers\Admin\UsersApiController::class);
    $usersReq = \Illuminate\Http\Request::create('/api/admin/users?per_page=3', 'GET');
    $usersResp = $usersCtrl->index($usersReq);
    $usersData = json_decode($usersResp->getContent(), true);
    test("Users index returns 200", $usersResp->getStatusCode() === 200);
    if (!empty($usersData['users']['data'])) {
        $fu = $usersData['users']['data'][0];
        test("User has avatar_url", isset($fu['avatar_url']));
        if ($fu['avatar_url']) {
            test("User avatar_url is valid URL", str_starts_with($fu['avatar_url'], 'http'), "url: " . substr($fu['avatar_url'], 0, 80));
            test("User avatar no double storage", strpos($fu['avatar_url'], 'storage/storage') === false);
        }
    }
} catch (\Exception $e) {
    test("Users index API", false, $e->getMessage());
}

// Public countries
try {
    $pubCtrl = app(\App\Http\Controllers\Api\PublicController::class);
    $pubReq = \Illuminate\Http\Request::create('/api/public/countries', 'GET');
    $pubResp = $pubCtrl->countries($pubReq);
    $pubData = json_decode($pubResp->getContent(), true);
    test("Public countries returns 200", $pubResp->getStatusCode() === 200);
    $cc = count($pubData['countries'] ?? []);
    test("Countries count > 0", $cc > 0, "count: {$cc}");
} catch (\Exception $e) {
    test("Public countries API", false, $e->getMessage());
}

// Languages API
try {
    $langCtrl = app(\App\Http\Controllers\Admin\LanguageManagementController::class);
    $langReq = \Illuminate\Http\Request::create('/api/admin/languages', 'GET');
    $langResp = $langCtrl->index($langReq);
    $langData = json_decode($langResp->getContent(), true);
    test("Languages index returns 200", $langResp->getStatusCode() === 200);
    $lc = count($langData['data'] ?? $langData['languages'] ?? []);
    test("Languages count > 0", $lc > 0, "count: {$lc}");
} catch (\Exception $e) {
    test("Languages API", false, $e->getMessage());
}

// ============================================================
echo "\n=== 4. BADGE SYSTEM ===\n";
// ============================================================

$allBadges = BadgeType::all();
test("Badge types exist", $allBadges->count() > 0);
$enumValues = ['عادي', 'فضي', 'ذهبي', 'ماسي', 'فلسطين'];
foreach ($allBadges as $bt) {
    test("Badge '{$bt->name_en}' ENUM compatible", in_array($bt->name_ar, $enumValues), "name_ar: '{$bt->name_ar}'");
}


echo "
=== 5. FLUTTER API (skipped - needs HTTP auth) ===
";

// ============================================================
echo "\n=== 6. POINTS SYSTEM ===\n";
// ============================================================

test("palservice_points table has data", \DB::table('palservice_points')->count() > 0);
$userWithPoints = User::first();
if ($userWithPoints) {
    $balance = $userWithPoints->pointsBalance;
    test("User pointsBalance is numeric", is_numeric($balance), "balance: {$balance}");
}

// ============================================================
echo "\n========================================\n";
echo "RESULTS: {$pass} passed, {$fail} failed\n";
if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $w) echo "  - {$w}\n";
}
echo "========================================\n";

exit($fail > 0 ? 1 : 0);
