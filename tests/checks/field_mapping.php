<?php
/**
 * Field Mapping Health Checks — Vue ↔ API consistency
 * The most important test: catches bugs like posts_count vs service_posts_count.
 *
 * Run standalone: php tests/checks/field_mapping.php
 */
require_once __DIR__ . '/bootstrap.php';

use App\Http\Controllers\Admin\ServicePostsApiController;
use App\Http\Controllers\Admin\UsersApiController;
use App\Http\Controllers\Admin\CategoriesApiController;
use App\Http\Controllers\Admin\CountriesApiController;
use App\Http\Controllers\Admin\CitiesApiController;
use App\Http\Controllers\Admin\LanguageManagementController;
use App\Http\Controllers\Admin\PointTransactionsApiController;
use App\Http\Controllers\Admin\BadgeTypesApiController;
use App\Http\Controllers\Api\PublicController;

hc_section('Field Mapping — Categories API');

[$status, $data] = hc_call(CategoriesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && !empty($data['data'])) {
    $cat = $data['data'][0];
    $keys = array_keys($cat);

    // Vue admin expects these exact field names
    hc_test('categories: has posts_count (not service_posts_count)', isset($cat['posts_count']),
        'got keys: ' . implode(', ', $keys));
    hc_test('categories: has subcategories_count (not sub_categories_count)', isset($cat['subcategories_count']),
        'got keys: ' . implode(', ', $keys));
    hc_test('categories: has id', isset($cat['id']));
    hc_test('categories: has name (object)', isset($cat['name']) && (is_array($cat['name']) || is_object($cat['name'])));
    hc_test('categories: has image_url', array_key_exists('image_url', $cat));
    hc_test('categories: has is_featured', array_key_exists('is_featured', $cat));
}

hc_section('Field Mapping — Service Posts API');

[$status, $data] = hc_call(ServicePostsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && !empty($data['service_posts']['data'])) {
    $post = $data['service_posts']['data'][0];
    $keys = array_keys($post);

    hc_test('posts: title is string (not JSON obj)', is_string($post['title'] ?? null));
    hc_test('posts: description is string', is_string($post['description'] ?? null));
    hc_test('posts: has id', isset($post['id']));
    hc_test('posts: has user_id', isset($post['user_id']));
    hc_test('posts: has categories_id', isset($post['categories_id']));

    // Category name should be resolved to string in the nested object
    if (isset($post['category'])) {
        hc_test('posts: category.name is string', is_string($post['category']['name'] ?? null),
            'got: ' . gettype($post['category']['name'] ?? null));
    }

    // Country name should be resolved
    if (isset($post['country']) && $post['country']) {
        hc_test('posts: country.name is string', is_string($post['country']['name'] ?? null),
            'got: ' . gettype($post['country']['name'] ?? null));
    }

    // Media src should not have double storage prefix
    if (isset($post['media']) && $post['media']) {
        hc_test('posts: media.src no double storage', strpos($post['media']['src'] ?? '', 'storage/storage') === false,
            'src: ' . ($post['media']['src'] ?? ''));
    }
}

// Show endpoint — translations structure
$postId = \App\Models\ServicePost::value('id');
if ($postId) {
    [$status, $data] = hc_call_with_id(ServicePostsApiController::class, 'show', $postId);
    if ($data !== null && isset($data['post'])) {
        $p = $data['post'];
        hc_test('post show: title is string', is_string($p['title'] ?? null));
        hc_test('post show: has title_translations array', isset($p['title_translations']) && is_array($p['title_translations']));
        hc_test('post show: has description_translations', isset($p['description_translations']));

        if (!empty($p['photos'])) {
            $photo = $p['photos'][0];
            hc_test('post show: photo.src no double storage', strpos($photo['src'] ?? '', 'storage/storage') === false);
        }
    }
}

hc_section('Field Mapping — Users API');

[$status, $data] = hc_call(UsersApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && !empty($data['users']['data'])) {
    $user = $data['users']['data'][0];
    $keys = array_keys($user);

    hc_test('users: has avatar_url', isset($user['avatar_url']), 'got keys: ' . implode(', ', $keys));

    if (!empty($user['avatar_url'])) {
        hc_test('users: avatar_url is valid URL', str_starts_with($user['avatar_url'], 'http'),
            'url: ' . substr($user['avatar_url'], 0, 80));
        hc_test('users: avatar_url no double storage', strpos($user['avatar_url'], 'storage/storage') === false);
    }

    hc_test('users: has user_name', isset($user['user_name']));
    hc_test('users: has email', isset($user['email']));
}

// User show — must have points_balance and service_posts_count
$userId = \App\Models\User::value('id');
if ($userId) {
    [$status, $data] = hc_call_with_id(UsersApiController::class, 'show', $userId);
    if ($data !== null && isset($data['user'])) {
        $u = $data['user'];
        hc_test('user show: has points_balance', isset($u['points_balance']),
            'got keys: ' . implode(', ', array_keys($u)));
        hc_test('user show: has service_posts_count', isset($u['service_posts_count']));
        hc_test('user show: has roles', isset($u['roles']));
    }
}

hc_section('Field Mapping — Countries API');

[$status, $data] = hc_call(CountriesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && !empty($data['data'])) {
    $country = $data['data'][0];
    hc_test('countries: has name (object)', isset($country['name']) && (is_array($country['name']) || is_object($country['name'])));
    hc_test('countries: has country_code', isset($country['country_code']));
    hc_test('countries: has cities_count', isset($country['cities_count']));
    hc_test('countries: has currency_code', array_key_exists('currency_code', $country));
}

hc_section('Field Mapping — Cities API');

[$status, $data] = hc_call(CitiesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && !empty($data['data'])) {
    $city = $data['data'][0];
    hc_test('cities: has name (object)', isset($city['name']) && (is_array($city['name']) || is_object($city['name'])));
    hc_test('cities: has country_id', isset($city['country_id']));
    hc_test('cities: has country.name', isset($city['country']['name']));
}

hc_section('Field Mapping — Languages Stats');

try {
    $ctrl = app(LanguageManagementController::class);
    $resp = $ctrl->stats();
    $data = json_decode($resp->getContent(), true);
    if ($data && !empty($data['languages'])) {
        $lang = $data['languages'][0];
        hc_test('lang stats: has models key', isset($lang['models']));
        hc_test('lang stats: has overall key', isset($lang['overall']));

        if (isset($lang['overall'])) {
            hc_test('lang stats: overall has translated', isset($lang['overall']['translated']));
            hc_test('lang stats: overall has total', isset($lang['overall']['total']));
            hc_test('lang stats: overall has percentage', isset($lang['overall']['percentage']));
        }

        if (isset($lang['models'])) {
            hc_test('lang stats: models has ui_strings', isset($lang['models']['ui_strings']));
            hc_test('lang stats: models has categories', isset($lang['models']['categories']));
        }
    }
} catch (\Exception $e) {
    hc_test('lang stats field mapping', false, $e->getMessage());
}

hc_section('Field Mapping — Transactions');

[$status, $data] = hc_call(PointTransactionsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null && isset($data['transactions']['data']) && !empty($data['transactions']['data'])) {
    $tx = $data['transactions']['data'][0];
    $keys = array_keys($tx);
    hc_test('transactions: has id', isset($tx['id']));
    hc_test('transactions: has type or transaction_type', isset($tx['type']) || isset($tx['transaction_type']),
        'got keys: ' . implode(', ', $keys));
} else {
    hc_warn('No transactions found to test field mapping');
}

hc_section('Field Mapping — Public Listings');

[$status, $data] = hc_call(PublicController::class, 'listings', ['per_page' => 3]);
if ($data !== null && !empty($data['listings'])) {
    $listing = $data['listings'][0];
    hc_test('public listing: title is string', is_string($listing['title'] ?? null));
    hc_test('public listing: title not raw JSON', !str_starts_with(trim($listing['title'] ?? ''), '{'));
    hc_test('public listing: has id', isset($listing['id']));
}

// ── Print standalone results if run directly ───────────────
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $r = hc_results();
    echo "\n========================================\n";
    echo "Field Mapping: {$r['pass']} passed, {$r['fail']} failed\n";
    if (!empty($r['failures'])) {
        echo "FAILURES:\n";
        foreach ($r['failures'] as $f) echo "  - {$f}\n";
    }
    echo "========================================\n";
    exit($r['fail'] > 0 ? 1 : 0);
}
