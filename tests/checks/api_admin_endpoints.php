<?php
/**
 * Admin API Endpoint Health Checks
 * Tests all admin controller methods return expected response structures.
 *
 * Run standalone: php tests/checks/api_admin_endpoints.php
 */
require_once __DIR__ . '/bootstrap.php';

use App\Http\Controllers\Admin\ServicePostsApiController;
use App\Http\Controllers\Admin\UsersApiController;
use App\Http\Controllers\Admin\CategoriesApiController;
use App\Http\Controllers\Admin\SubCategoriesApiController;
use App\Http\Controllers\Admin\CountriesApiController;
use App\Http\Controllers\Admin\CitiesApiController;
use App\Http\Controllers\Admin\BadgeTypesApiController;
use App\Http\Controllers\Admin\LanguageManagementController;
use App\Http\Controllers\Admin\TranslationManagementController;
use App\Http\Controllers\Admin\ReportsApiController;
use App\Http\Controllers\Admin\PalServicePointsApiController;
use App\Http\Controllers\Admin\PointTransactionsApiController;
use App\Http\Controllers\Admin\PointPackagesApiController;
use App\Http\Controllers\Admin\DashboardApiController;
use App\Http\Controllers\Admin\StatisticsApiController;
use App\Http\Controllers\Admin\AnalyticsApiController;
use App\Http\Controllers\Admin\SeoApiController;
use App\Http\Controllers\Admin\CommandMonitorController;
use App\Http\Controllers\Admin\ContentTranslationController;

hc_section('Admin API Endpoints');

// ── Service Posts ──────────────────────────────────────────
hc_section('Service Posts');

[$status, $data] = hc_call(ServicePostsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('service-posts index returns 200', $status === 200);
    hc_test('service-posts has service_posts key', isset($data['service_posts']));
    hc_test('service-posts has data array', isset($data['service_posts']['data']) && is_array($data['service_posts']['data']));
    hc_test('service-posts has total', isset($data['service_posts']['total']));
    if (!empty($data['service_posts']['data'])) {
        $fp = $data['service_posts']['data'][0];
        hc_test('service-posts item has id', isset($fp['id']));
        hc_test('service-posts title is string', is_string($fp['title'] ?? null), 'got: ' . gettype($fp['title'] ?? null));
        hc_test('service-posts title not raw JSON', !str_starts_with(trim($fp['title'] ?? ''), '{'));
        hc_test('service-posts description is string', is_string($fp['description'] ?? ''));
    }
}

// Stats
try {
    $ctrl = app(ServicePostsApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('service-posts stats returns 200', $resp->getStatusCode() === 200);
    hc_test('service-posts stats has stats array', isset($statsData['stats']) && is_array($statsData['stats']));
    if (!empty($statsData['stats'])) {
        $first = $statsData['stats'][0];
        hc_test('service-posts stats item has label', isset($first['label']));
        hc_test('service-posts stats item has value', isset($first['value']));
    }
} catch (\Exception $e) {
    hc_test('service-posts stats', false, $e->getMessage());
}

// Show
$postId = \App\Models\ServicePost::value('id');
if ($postId) {
    [$status, $data] = hc_call_with_id(ServicePostsApiController::class, 'show', $postId);
    if ($data !== null) {
        hc_test('service-posts show returns 200', $status === 200);
        hc_test('service-posts show has post key', isset($data['post']));
        hc_test('service-posts show title is string', is_string($data['post']['title'] ?? null));
        hc_test('service-posts show has title_translations', isset($data['post']['title_translations']));
    }
}

// ── Users ──────────────────────────────────────────────────
hc_section('Users');

[$status, $data] = hc_call(UsersApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('users index returns 200', $status === 200);
    hc_test('users has users key', isset($data['users']));
    hc_test('users has data array', isset($data['users']['data']) && is_array($data['users']['data']));
    hc_test('users has total', isset($data['users']['total']));
    if (!empty($data['users']['data'])) {
        $fu = $data['users']['data'][0];
        hc_test('users item has id', isset($fu['id']));
        hc_test('users item has user_name', isset($fu['user_name']));
        hc_test('users item has avatar_url', isset($fu['avatar_url']));
    }
}

// Stats
try {
    $ctrl = app(UsersApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('users stats returns 200', $resp->getStatusCode() === 200);
    hc_test('users stats has stats array', isset($statsData['stats']) && is_array($statsData['stats']));
} catch (\Exception $e) {
    hc_test('users stats', false, $e->getMessage());
}

// Show
$userId = \App\Models\User::value('id');
if ($userId) {
    [$status, $data] = hc_call_with_id(UsersApiController::class, 'show', $userId);
    if ($data !== null) {
        hc_test('users show returns 200', $status === 200, isset($data['error']) ? "error: {$data['message']}" : '');
        if ($status === 200 && isset($data['user'])) {
            hc_test('users show has user key', true);
            hc_test('users show has points_balance', isset($data['user']['points_balance']));
            hc_test('users show has service_posts_count', isset($data['user']['service_posts_count']));
        } elseif ($status !== 200) {
            hc_test('users show has user key', false, "status {$status}: " . ($data['message'] ?? 'unknown'));
        }
    }
}

// Roles
try {
    $ctrl = app(UsersApiController::class);
    $resp = $ctrl->getRoles();
    $rolesData = json_decode($resp->getContent(), true);
    hc_test('users getRoles returns 200', $resp->getStatusCode() === 200);
    hc_test('users getRoles has roles key', isset($rolesData['roles']));
} catch (\Exception $e) {
    hc_test('users getRoles', false, $e->getMessage());
}

// ── Categories ─────────────────────────────────────────────
hc_section('Categories');

[$status, $data] = hc_call(CategoriesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('categories index returns 200', $status === 200);
    hc_test('categories has data array', isset($data['data']) && is_array($data['data']));
    hc_test('categories has total', isset($data['total']));
    if (!empty($data['data'])) {
        $fc = $data['data'][0];
        hc_test('categories item has id', isset($fc['id']));
        hc_test('categories item has name', isset($fc['name']));
        hc_test('categories item has posts_count', isset($fc['posts_count']), 'got keys: ' . implode(', ', array_keys($fc)));
        hc_test('categories item has subcategories_count', isset($fc['subcategories_count']), 'got keys: ' . implode(', ', array_keys($fc)));
    }
}

// Stats
try {
    $ctrl = app(CategoriesApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('categories stats returns 200', $resp->getStatusCode() === 200);
    hc_test('categories stats has stats array', isset($statsData['stats']) && is_array($statsData['stats']));
} catch (\Exception $e) {
    hc_test('categories stats', false, $e->getMessage());
}

// Show
$catId = \App\Models\Categories::value('id');
if ($catId) {
    [$status, $data] = hc_call_with_id(CategoriesApiController::class, 'show', $catId);
    if ($data !== null) {
        hc_test('categories show returns 200', $status === 200);
        hc_test('categories show has category key', isset($data['category']));
    }
}

// ── SubCategories ──────────────────────────────────────────
hc_section('SubCategories');

[$status, $data] = hc_call(SubCategoriesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('subcategories index returns 200', $status === 200);
    $hasData = isset($data['data']) && is_array($data['data']);
    hc_test('subcategories has data array', $hasData);
}

// ── Countries ──────────────────────────────────────────────
hc_section('Countries');

[$status, $data] = hc_call(CountriesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('countries index returns 200', $status === 200);
    hc_test('countries has data array', isset($data['data']) && is_array($data['data']));
    if (!empty($data['data'])) {
        $fc = $data['data'][0];
        hc_test('countries item has name', isset($fc['name']));
        hc_test('countries item has country_code', isset($fc['country_code']));
        hc_test('countries item has cities_count', isset($fc['cities_count']));
    }
}

// Show
$countryId = \App\Models\countries::value('id');
if ($countryId) {
    [$status, $data] = hc_call_with_id(CountriesApiController::class, 'show', $countryId);
    if ($data !== null) {
        hc_test('countries show returns 200', $status === 200);
        hc_test('countries show has cities', isset($data['cities']));
    }
}

// Stats
try {
    $ctrl = app(CountriesApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('countries stats returns 200', $resp->getStatusCode() === 200);
    hc_test('countries stats has stats array', isset($statsData['stats']));
} catch (\Exception $e) {
    hc_test('countries stats', false, $e->getMessage());
}

// getCities
if ($countryId) {
    try {
        $ctrl = app(CountriesApiController::class);
        $resp = $ctrl->getCities($countryId);
        $citiesData = json_decode($resp->getContent(), true);
        hc_test('countries getCities returns 200', $resp->getStatusCode() === 200);
        hc_test('countries getCities has cities key', isset($citiesData['cities']));
    } catch (\Exception $e) {
        hc_test('countries getCities', false, $e->getMessage());
    }
}

// ── Cities ─────────────────────────────────────────────────
hc_section('Cities');

[$status, $data] = hc_call(CitiesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('cities index returns 200', $status === 200);
    hc_test('cities has data array', isset($data['data']) && is_array($data['data']));
}

// ── Badge Types (Admin) ────────────────────────────────────
hc_section('Badge Types (Admin)');

[$status, $data] = hc_call(BadgeTypesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('badge-types index returns 200', $status === 200);
    $hasData = isset($data['data']) && is_array($data['data']);
    hc_test('badge-types has data', $hasData);
}

try {
    $ctrl = app(BadgeTypesApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('badge-types stats returns 200', $resp->getStatusCode() === 200);
    hc_test('badge-types stats has stats', isset($statsData['stats']));
    hc_test('badge-types stats has badge_stats', isset($statsData['badge_stats']));
} catch (\Exception $e) {
    hc_test('badge-types stats', false, $e->getMessage());
}

// ── Languages ──────────────────────────────────────────────
hc_section('Languages');

[$status, $data] = hc_call(LanguageManagementController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('languages index returns 200', $status === 200);
    hc_test('languages has data array', isset($data['data']) && is_array($data['data']));
}

try {
    $ctrl = app(LanguageManagementController::class);
    $resp = $ctrl->stats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('languages stats returns 200', $resp->getStatusCode() === 200);
    hc_test('languages stats has success', isset($statsData['success']));
    hc_test('languages stats has total', isset($statsData['total']));
    hc_test('languages stats has languages array', isset($statsData['languages']) && is_array($statsData['languages']));
    if (!empty($statsData['languages'])) {
        $lang = $statsData['languages'][0];
        hc_test('languages stats item has models', isset($lang['models']));
        hc_test('languages stats item has overall', isset($lang['overall']));
    }
} catch (\Exception $e) {
    hc_test('languages stats', false, $e->getMessage());
}

// ── Translations ───────────────────────────────────────────
hc_section('Translations');

[$status, $data] = hc_call(TranslationManagementController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('translations index returns 200', $status === 200);
    hc_test('translations has data array', isset($data['data']) && is_array($data['data']));
}

try {
    $ctrl = app(TranslationManagementController::class);
    $resp = $ctrl->stats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('translations stats returns 200', $resp->getStatusCode() === 200);
    hc_test('translations stats has total_translations', isset($statsData['total_translations']));
} catch (\Exception $e) {
    hc_test('translations stats', false, $e->getMessage());
}

// Content Translations (requires language code parameter)
try {
    $ctrl = app(ContentTranslationController::class);
    $langCode = \App\Models\Language::where('is_default', true)->value('code') ?? 'ar';
    $req = \Illuminate\Http\Request::create("/api/admin/languages/{$langCode}/content-translations?model=categories&per_page=3", 'GET', [
        'model' => 'categories', 'per_page' => 3,
    ]);
    $resp = $ctrl->index($req, $langCode);
    $ctData = json_decode($resp->getContent(), true);
    hc_test('content-translations index returns 200', $resp->getStatusCode() === 200);
    hc_test('content-translations has data', isset($ctData['data']));
} catch (\Exception $e) {
    hc_test('content-translations index', false, $e->getMessage());
}

// ── Reports ────────────────────────────────────────────────
hc_section('Reports');

[$status, $data] = hc_call(ReportsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('reports index returns 200', $status === 200);
    hc_test('reports has reports key', isset($data['reports']));
    hc_test('reports has data array', isset($data['reports']['data']) && is_array($data['reports']['data']));
}

try {
    $ctrl = app(ReportsApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('reports stats returns 200', $resp->getStatusCode() === 200);
    hc_test('reports stats has stats', isset($statsData['stats']));
    hc_test('reports stats has reason_chart', isset($statsData['reason_chart']));
} catch (\Exception $e) {
    hc_test('reports stats', false, $e->getMessage());
}

// ── Points ─────────────────────────────────────────────────
hc_section('Points');

[$status, $data] = hc_call(PalServicePointsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('points index returns 200', $status === 200);
    hc_test('points has data', isset($data['data']));
}

try {
    $ctrl = app(PalServicePointsApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('points stats returns 200', $resp->getStatusCode() === 200);
    hc_test('points stats has total_points', isset($statsData['total_points']));
    hc_test('points stats has total_users', isset($statsData['total_users']));
} catch (\Exception $e) {
    hc_test('points stats', false, $e->getMessage());
}

// Point Transactions
[$status, $data] = hc_call(PointTransactionsApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('transactions index returns 200', $status === 200);
    hc_test('transactions has transactions key', isset($data['transactions']));
}

try {
    $ctrl = app(PointTransactionsApiController::class);
    $resp = $ctrl->getStats();
    $statsData = json_decode($resp->getContent(), true);
    hc_test('transactions stats returns 200', $resp->getStatusCode() === 200);
    hc_test('transactions stats has stats', isset($statsData['stats']));
} catch (\Exception $e) {
    hc_test('transactions stats', false, $e->getMessage());
}

// Point Packages
[$status, $data] = hc_call(PointPackagesApiController::class, 'index', ['per_page' => 3]);
if ($data !== null) {
    hc_test('packages index returns 200', $status === 200);
    hc_test('packages has packages key', isset($data['packages']));
}

// ── Dashboard ──────────────────────────────────────────────
hc_section('Dashboard');

try {
    $ctrl = app(DashboardApiController::class);
    $req = \Illuminate\Http\Request::create('/test', 'GET');
    $resp = $ctrl->index($req);
    $data = json_decode($resp->getContent(), true);
    hc_test('dashboard returns 200', $resp->getStatusCode() === 200);
    hc_test('dashboard has stats', isset($data['stats']));
    hc_test('dashboard has recentPosts', isset($data['recentPosts']));
    hc_test('dashboard has recentUsers', isset($data['recentUsers']));
    hc_test('dashboard has postsByMonth', isset($data['postsByMonth']));
    hc_test('dashboard has topUsers', isset($data['topUsers']));
} catch (\Exception $e) {
    hc_test('dashboard index', false, $e->getMessage());
}

// ── Statistics ─────────────────────────────────────────────
hc_section('Statistics');

try {
    $ctrl = app(StatisticsApiController::class);
    $req = \Illuminate\Http\Request::create('/test', 'GET');
    $resp = $ctrl->index($req);
    $data = json_decode($resp->getContent(), true);
    hc_test('statistics returns 200', $resp->getStatusCode() === 200);
    hc_test('statistics has todays_pulse', isset($data['todays_pulse']));
    hc_test('statistics has charts', isset($data['charts']));
    hc_test('statistics has system_rates', isset($data['system_rates']));
} catch (\Exception $e) {
    hc_test('statistics index', false, $e->getMessage());
}

// ── Analytics ──────────────────────────────────────────────
hc_section('Analytics');

try {
    $ctrl = app(AnalyticsApiController::class);
    $req = \Illuminate\Http\Request::create('/test?tab=overview&days=7', 'GET', ['tab' => 'overview', 'days' => 7]);
    $resp = $ctrl->getAnalytics($req);
    $data = json_decode($resp->getContent(), true);
    hc_test('analytics returns 200', $resp->getStatusCode() === 200);
    hc_test('analytics has overview', isset($data['overview']));
    hc_test('analytics has data', isset($data['data']));
} catch (\Exception $e) {
    hc_test('analytics', false, $e->getMessage());
}

// ── SEO ────────────────────────────────────────────────────
hc_section('SEO');

try {
    $ctrl = app(SeoApiController::class);
    $resp = $ctrl->dashboard();
    $data = json_decode($resp->getContent(), true);
    hc_test('seo dashboard returns 200', $resp->getStatusCode() === 200);
    hc_test('seo has overview', isset($data['overview']));
} catch (\Exception $e) {
    hc_test('seo dashboard', false, $e->getMessage());
}

// ── Command Monitor ────────────────────────────────────────
hc_section('Command Monitor');

[$status, $data] = hc_call(CommandMonitorController::class, 'index');
if ($data !== null) {
    hc_test('command-monitor returns 200', $status === 200);
    hc_test('command-monitor has commands', isset($data['commands']));
}

// ── Print standalone results if run directly ───────────────
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $r = hc_results();
    echo "\n========================================\n";
    echo "Admin Endpoints: {$r['pass']} passed, {$r['fail']} failed\n";
    if (!empty($r['failures'])) {
        echo "FAILURES:\n";
        foreach ($r['failures'] as $f) echo "  - {$f}\n";
    }
    echo "========================================\n";
    exit($r['fail'] > 0 ? 1 : 0);
}
