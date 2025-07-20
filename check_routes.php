<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Route;

echo "Checking for duplicate routes...\n";

$routes = Route::getRoutes();
$routeNames = [];

foreach ($routes as $route) {
    $name = $route->getName();
    if ($name) {
        if (!isset($routeNames[$name])) {
            $routeNames[$name] = [];
        }
        $routeNames[$name][] = [
            'uri' => $route->uri(),
            'methods' => $route->methods(),
            'action' => $route->getActionName()
        ];
    }
}

$duplicates = [];
foreach ($routeNames as $name => $routes) {
    if (count($routes) > 1) {
        $duplicates[$name] = $routes;
    }
}

if (empty($duplicates)) {
    echo "No duplicate routes found.\n";
} else {
    echo "Found duplicate routes:\n";
    foreach ($duplicates as $name => $routes) {
        echo "\nRoute name: {$name}\n";
        foreach ($routes as $route) {
            echo "  - URI: {$route['uri']}\n";
            echo "    Methods: " . implode(', ', $route['methods']) . "\n";
            echo "    Action: {$route['action']}\n";
        }
    }
}

echo "\nChecking specifically for service_posts routes:\n";
foreach ($routeNames as $name => $routes) {
    if (strpos($name, 'service_posts') !== false) {
        echo "\nRoute name: {$name}\n";
        foreach ($routes as $route) {
            echo "  - URI: {$route['uri']}\n";
            echo "    Methods: " . implode(', ', $route['methods']) . "\n";
            echo "    Action: {$route['action']}\n";
        }
    }
} 