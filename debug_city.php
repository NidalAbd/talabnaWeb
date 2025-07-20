<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ServicePost;

echo "Debugging city data:\n";

$post = ServicePost::with('city')->find(200);

if ($post && $post->city) {
    echo "Post ID: " . $post->id . "\n";
    echo "City ID: " . $post->city->id . "\n";
    echo "City Name Raw: " . $post->city->name . "\n";
    echo "City Name Type: " . gettype($post->city->name) . "\n";
    
    // Get raw database value
    $rawCity = \DB::table('cities')->where('id', $post->city->id)->first();
    echo "Raw DB City Name: " . $rawCity->name . "\n";
    
    if (is_array($post->city->name)) {
        echo "City Name Array: " . json_encode($post->city->name) . "\n";
        echo "City Arabic: " . ($post->city->name['ar'] ?? 'Not found') . "\n";
        echo "City English: " . ($post->city->name['en'] ?? 'Not found') . "\n";
    } else {
        echo "City Name is not an array\n";
        // Try to decode manually
        $decoded = json_decode($post->city->name, true);
        if ($decoded) {
            echo "Manually decoded: " . json_encode($decoded) . "\n";
            echo "Arabic: " . ($decoded['ar'] ?? 'Not found') . "\n";
            echo "English: " . ($decoded['en'] ?? 'Not found') . "\n";
        }
    }
} else {
    echo "Post or city not found\n";
} 