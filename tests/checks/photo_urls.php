<?php
/**
 * Photo & URL Health Checks
 * Tests photo paths, storage prefixes, file existence, and avatar URLs.
 *
 * Run standalone: php tests/checks/photo_urls.php
 */
require_once __DIR__ . '/bootstrap.php';

use App\Models\ServicePost;
use App\Models\User;
use App\Models\Photos;

hc_section('Photo URLs — Post Photos');

$postsWithPhotos = ServicePost::whereHas('photos')->with('photos')->limit(10)->get();
hc_test('Posts with photos found', $postsWithPhotos->count() > 0);

$doubleStorageCount = 0;
$missingFileCount = 0;
$checkedCount = 0;

foreach ($postsWithPhotos as $post) {
    foreach ($post->photos as $photo) {
        $checkedCount++;

        // Double storage prefix check
        if (strpos($photo->src, 'storage/storage') !== false) {
            $doubleStorageCount++;
            if ($doubleStorageCount <= 3) {
                hc_test("Photo #{$photo->id} no double storage prefix", false, "src: {$photo->src}");
            }
        }

        // File existence (skip external URLs)
        if (!$photo->is_external) {
            $cleanSrc = preg_replace('#^/?storage/#', '', $photo->src);
            $fullPath = storage_path("app/public/{$cleanSrc}");
            if (!file_exists($fullPath)) {
                $missingFileCount++;
                if ($missingFileCount <= 3) {
                    hc_test("Photo #{$photo->id} file exists on disk", false, "path: {$cleanSrc}");
                }
            }
        }
    }
}

hc_test("No double storage prefixes in post photos", $doubleStorageCount === 0,
    "found: {$doubleStorageCount} out of {$checkedCount} checked");
if ($missingFileCount > 0) {
    hc_warn("Missing photo files: {$missingFileCount} out of {$checkedCount} checked");
}

hc_section('Photo URLs — User Avatars');

$usersWithPhotos = User::whereHas('photos')->with('photos')->limit(10)->get();
hc_test('Users with photos found', $usersWithPhotos->count() > 0);

$avatarDoubleStorage = 0;
$avatarChecked = 0;

foreach ($usersWithPhotos as $user) {
    $uPhoto = $user->photos->first();
    if (!$uPhoto) continue;
    $avatarChecked++;

    $assetUrl = asset($uPhoto->src);
    if (strpos($assetUrl, 'storage/storage') !== false) {
        $avatarDoubleStorage++;
        if ($avatarDoubleStorage <= 3) {
            hc_test("User #{$user->id} avatar no double storage", false, "url: {$assetUrl}");
        }
    }

    // First avatar: test URL format
    if ($avatarChecked === 1) {
        hc_test('User avatar asset() returns valid URL', str_starts_with($assetUrl, 'http'), "url: {$assetUrl}");
    }
}

hc_test("No double storage prefixes in user avatars", $avatarDoubleStorage === 0,
    "found: {$avatarDoubleStorage} out of {$avatarChecked} checked");

hc_section('Photo URLs — External Photos');

$externalPhotos = Photos::where('is_external', 1)->limit(5)->get();
if ($externalPhotos->count() > 0) {
    foreach ($externalPhotos as $photo) {
        hc_test("External photo #{$photo->id} src starts with http",
            str_starts_with($photo->src, 'http'),
            "src: " . substr($photo->src, 0, 80));
    }
} else {
    hc_warn('No external photos found to test');
}

hc_section('Photo URLs — OG Image');

hc_test('og-image.jpg exists', file_exists(storage_path('app/public/photos/og-image.jpg')));

hc_section('Photo URLs — Bulk Storage Prefix Scan');

// Scan a larger sample for double storage issues
$badPrefixCount = Photos::where('src', 'LIKE', '%storage/storage%')->count();
hc_test("No double storage/storage in photos table", $badPrefixCount === 0, "found: {$badPrefixCount} rows");

$badPrefixCount2 = Photos::where('src', 'LIKE', 'storage/storage/%')->count();
hc_test("No leading storage/storage/ prefix", $badPrefixCount2 === 0, "found: {$badPrefixCount2} rows");

// ── Print standalone results if run directly ───────────────
if (realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === realpath(__FILE__)) {
    $r = hc_results();
    echo "\n========================================\n";
    echo "Photo URLs: {$r['pass']} passed, {$r['fail']} failed\n";
    if (!empty($r['failures'])) {
        echo "FAILURES:\n";
        foreach ($r['failures'] as $f) echo "  - {$f}\n";
    }
    if (!empty($r['warnings'])) {
        echo "WARNINGS:\n";
        foreach ($r['warnings'] as $w) echo "  - {$w}\n";
    }
    echo "========================================\n";
    exit($r['fail'] > 0 ? 1 : 0);
}
