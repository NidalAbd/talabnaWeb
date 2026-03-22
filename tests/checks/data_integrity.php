<?php
/**
 * Data Integrity Health Checks
 * Tests model data is well-formed: translations, JSON fields, relationships.
 *
 * Run standalone: php tests/checks/data_integrity.php
 */
require_once __DIR__ . '/bootstrap.php';

use App\Models\ServicePost;
use App\Models\User;
use App\Models\Categories;
use App\Models\Sub_categories;
use App\Models\countries;
use App\Models\cities;
use App\Models\Language;
use App\Models\BadgeType;

hc_section('Data Integrity — ServicePost');

$post = ServicePost::first();
hc_test('ServicePost exists', $post !== null);
if ($post) {
    $rawTitle = $post->getRawOriginal('title');
    $decoded = json_decode($rawTitle, true);
    hc_test('ServicePost title is valid JSON in DB', is_array($decoded) && !empty($decoded));

    $titleValue = $post->title;
    hc_test('ServicePost->title returns string', is_string($titleValue), 'got: ' . gettype($titleValue));
    hc_test('ServicePost->title not empty', !empty($titleValue));
    hc_test('ServicePost->title not raw JSON object', !str_starts_with(trim($titleValue), '{'));

    $descValue = $post->description;
    hc_test('ServicePost->description returns string', is_string($descValue), 'got: ' . gettype($descValue));

    $arr = $post->toArray();
    hc_test('toArray title is string', is_string($arr['title']), 'got: ' . gettype($arr['title']));
    hc_test('toArray has translations key', isset($arr['translations']['title']));
    hc_test('toArray translations.title is array', is_array($arr['translations']['title'] ?? null));

    $rawT = $post->getRawTranslations('title');
    hc_test('getRawTranslations returns array', is_array($rawT));

    $arTitle = $post->translate('title', 'ar');
    hc_test('translate(title, ar) returns string', is_string($arTitle), 'got: ' . gettype($arTitle));
}

// Sample a few posts to check for bad data
$samplePosts = ServicePost::inRandomOrder()->limit(5)->get();
foreach ($samplePosts as $i => $sp) {
    $t = $sp->title;
    hc_test("Sample post #{$sp->id} title is string", is_string($t), 'got: ' . gettype($t));
    hc_test("Sample post #{$sp->id} title not raw JSON", !str_starts_with(trim($t), '{'), 'title: ' . substr($t, 0, 50));
}

hc_section('Data Integrity — Categories');

$cat = Categories::first();
hc_test('Category exists', $cat !== null);
if ($cat) {
    hc_test('Category->name is array', is_array($cat->name), 'got: ' . gettype($cat->name));
    if (is_array($cat->name)) {
        hc_test('Category name has ar key', isset($cat->name['ar']));
        hc_test('Category name has en key', isset($cat->name['en']));
    }
}

hc_section('Data Integrity — SubCategories');

$sub = Sub_categories::first();
hc_test('SubCategory exists', $sub !== null);
if ($sub) {
    hc_test('SubCategory->name is array', is_array($sub->name), 'got: ' . gettype($sub->name));
    if (is_array($sub->name)) {
        hc_test('SubCategory name has ar key', isset($sub->name['ar']));
        hc_test('SubCategory name has en key', isset($sub->name['en']));
    }
}

hc_section('Data Integrity — Countries & Cities');

$country = countries::first();
hc_test('Country exists', $country !== null);
if ($country) {
    hc_test('Country->name is array', is_array($country->name), 'got: ' . gettype($country->name));
    if (is_array($country->name)) {
        hc_test('Country name has en key', isset($country->name['en']));
    }
}

$city = cities::first();
hc_test('City exists', $city !== null);
if ($city) {
    hc_test('City->name is array', is_array($city->name), 'got: ' . gettype($city->name));
}

// Check for orphan cities (no valid country)
$orphanCities = cities::whereNotIn('country_id', countries::pluck('id'))->count();
hc_test('No orphan cities', $orphanCities === 0, "orphan count: {$orphanCities}");

hc_section('Data Integrity — Languages');

$langCount = Language::count();
hc_test('Languages exist', $langCount > 0, "count: {$langCount}");

$activeLangs = Language::where('is_active', true)->count();
hc_test('Active languages exist', $activeLangs > 0, "count: {$activeLangs}");

$defaultLang = Language::where('is_default', true)->first();
hc_test('Default language exists', $defaultLang !== null);

hc_section('Data Integrity — BadgeType');

$allBadges = BadgeType::all();
hc_test('Badge types exist', $allBadges->count() > 0);
$enumValues = ['عادي', 'فضي', 'ذهبي', 'ماسي', 'فلسطين'];
foreach ($allBadges as $bt) {
    hc_test("Badge '{$bt->name_en}' has valid name_ar ENUM", in_array($bt->name_ar, $enumValues), "name_ar: '{$bt->name_ar}'");
}

$defaultBadge = BadgeType::where('is_default', true)->first();
hc_test('Default badge type exists', $defaultBadge !== null);

hc_section('Data Integrity — Users');

$totalUsers = User::count();
hc_test('Users exist', $totalUsers > 0, "count: {$totalUsers}");

// Check users have country/city
$usersNoCountry = User::whereNull('country_id')->count();
if ($usersNoCountry > 0) {
    hc_warn("Users without country: {$usersNoCountry}");
}

// Points
$userWithPoints = User::first();
if ($userWithPoints) {
    $balance = $userWithPoints->pointsBalance;
    hc_test('User pointsBalance is numeric', is_numeric($balance), "balance: {$balance}");
}

hc_test('palservice_points table has data', \DB::table('palservice_points')->count() > 0);

// ── Print standalone results if run directly ───────────────
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $r = hc_results();
    echo "\n========================================\n";
    echo "Data Integrity: {$r['pass']} passed, {$r['fail']} failed\n";
    if (!empty($r['failures'])) {
        echo "FAILURES:\n";
        foreach ($r['failures'] as $f) echo "  - {$f}\n";
    }
    echo "========================================\n";
    exit($r['fail'] > 0 ? 1 : 0);
}
