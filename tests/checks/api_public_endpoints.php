<?php
/**
 * Public API Endpoint Health Checks
 * Tests all public (no-auth) controller methods return expected response structures.
 *
 * Run standalone: php tests/checks/api_public_endpoints.php
 */
require_once __DIR__ . '/bootstrap.php';

use App\Http\Controllers\Api\PublicController;
use App\Http\Controllers\Api\BadgeTypeController;

hc_section('Public API Endpoints');

// ── Categories ─────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'categories');
if ($data !== null) {
    hc_test('public categories returns 200', $status === 200);
    hc_test('public categories has categories key', isset($data['categories']));
    hc_test('public categories is array', is_array($data['categories'] ?? null));
    $cc = count($data['categories'] ?? []);
    hc_test('public categories count > 0', $cc > 0, "count: {$cc}");
    if ($cc > 0) {
        $fc = $data['categories'][0];
        hc_test('public category has id', isset($fc['id']));
        hc_test('public category has name', isset($fc['name']));
    }
}

// ── Subcategories ──────────────────────────────────────────
$catId = \App\Models\Categories::value('id');
if ($catId) {
    try {
        $ctrl = app(PublicController::class);
        $req = \Illuminate\Http\Request::create("/api/public/categories/{$catId}/subcategories", 'GET');
        $resp = $ctrl->subcategories($req, $catId);
        $status = $resp->getStatusCode();
        $data = json_decode($resp->getContent(), true);
        hc_test('public subcategories returns 200', $status === 200);
        hc_test('public subcategories has subcategories key', isset($data['subcategories']));
    } catch (\Exception $e) {
        hc_test('public subcategories', false, $e->getMessage());
    }
}

// ── Listings ───────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'listings', ['per_page' => 3]);
if ($data !== null) {
    hc_test('public listings returns 200', $status === 200);
    hc_test('public listings has listings key', isset($data['listings']));
    hc_test('public listings has pagination', isset($data['pagination']));
    if (!empty($data['listings'])) {
        $fl = $data['listings'][0];
        hc_test('public listing has id', isset($fl['id']));
        hc_test('public listing has title', isset($fl['title']));
        hc_test('public listing title is string', is_string($fl['title'] ?? null), 'got: ' . gettype($fl['title'] ?? null));
    }
}

// ── Featured ───────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'featured', ['per_page' => 3]);
if ($data !== null) {
    hc_test('public featured returns 200', $status === 200);
    hc_test('public featured has featured key', isset($data['featured']));
}

// ── Latest ─────────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'latest', ['per_page' => 3]);
if ($data !== null) {
    hc_test('public latest returns 200', $status === 200);
    hc_test('public latest has latest key', isset($data['latest']));
}

// ── Popular ────────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'popular', ['per_page' => 3]);
if ($data !== null) {
    hc_test('public popular returns 200', $status === 200);
    hc_test('public popular has popular key', isset($data['popular']));
}

// ── Stats ──────────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'stats');
if ($data !== null) {
    hc_test('public stats returns 200', $status === 200);
    hc_test('public stats has total_listings', isset($data['total_listings']));
    hc_test('public stats has total_users', isset($data['total_users']));
    hc_test('public stats has total_categories', isset($data['total_categories']));
}

// ── Countries ──────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'countries');
if ($data !== null) {
    hc_test('public countries returns 200', $status === 200);
    hc_test('public countries has countries key', isset($data['countries']));
    $cc = count($data['countries'] ?? []);
    hc_test('public countries count > 0', $cc > 0, "count: {$cc}");
}

// ── Country Cities ─────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'countryCities', ['per_page' => 5]);
if ($data !== null) {
    hc_test('public country-cities returns 200', $status === 200);
    hc_test('public country-cities has cities key', isset($data['cities']));
}

// ── Cities by Country ──────────────────────────────────────
$countryId = \App\Models\countries::value('id');
if ($countryId) {
    try {
        $ctrl = app(PublicController::class);
        $req = \Illuminate\Http\Request::create("/api/public/countries/{$countryId}/cities", 'GET');
        $resp = $ctrl->cities($req, $countryId);
        $status = $resp->getStatusCode();
        $data = json_decode($resp->getContent(), true);
        hc_test('public cities-by-country returns 200', $status === 200);
        hc_test('public cities-by-country has cities key', isset($data['cities']));
    } catch (\Exception $e) {
        hc_test('public cities-by-country', false, $e->getMessage());
    }
}

// ── Search ─────────────────────────────────────────────────
[$status, $data] = hc_call(PublicController::class, 'search', ['q' => 'test', 'per_page' => 3]);
if ($data !== null) {
    hc_test('public search returns 200', $status === 200);
    hc_test('public search has listings key', isset($data['listings']));
}

// ── Badge Types (Public) ───────────────────────────────────
try {
    $ctrl = app(PublicController::class);
    $req = \Illuminate\Http\Request::create('/api/public/badge-types', 'GET');
    $resp = $ctrl->badgeTypes($req);
    $status = $resp->getStatusCode();
    $data = json_decode($resp->getContent(), true);
    hc_test('public badge-types returns 200', $status === 200);
    hc_test('public badge-types has badges key', isset($data['badges']));
    if (!empty($data['badges'])) {
        $fb = $data['badges'][0];
        hc_test('public badge has name_ar', isset($fb['name_ar']));
        hc_test('public badge has name_en', isset($fb['name_en']));
    }
} catch (\Exception $e) {
    hc_test('public badge-types', false, $e->getMessage());
}

// ── User Profile ───────────────────────────────────────────
$userId = \App\Models\User::value('id');
if ($userId) {
    try {
        $ctrl = app(PublicController::class);
        $req = \Illuminate\Http\Request::create("/api/public/users/{$userId}", 'GET');
        $resp = $ctrl->userProfile($req, $userId);
        $status = $resp->getStatusCode();
        $data = json_decode($resp->getContent(), true);
        hc_test('public user-profile returns 200', $status === 200);
        hc_test('public user-profile has user key', isset($data['user']));
    } catch (\Exception $e) {
        hc_test('public user-profile', false, $e->getMessage());
    }
}

// ── Print standalone results if run directly ───────────────
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $r = hc_results();
    echo "\n========================================\n";
    echo "Public Endpoints: {$r['pass']} passed, {$r['fail']} failed\n";
    if (!empty($r['failures'])) {
        echo "FAILURES:\n";
        foreach ($r['failures'] as $f) echo "  - {$f}\n";
    }
    echo "========================================\n";
    exit($r['fail'] > 0 ? 1 : 0);
}
