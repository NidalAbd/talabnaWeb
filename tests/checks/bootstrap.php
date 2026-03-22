<?php
/**
 * Shared bootstrap and test helpers for health check suite.
 * Each check file includes this to get Laravel bootstrapped + test/warn functions.
 */
if (!defined('HEALTH_CHECK_BOOTSTRAPPED')) {
    define('HEALTH_CHECK_BOOTSTRAPPED', true);

    // Find project root (two levels up from tests/checks/)
    $root = dirname(__DIR__, 2);
    require "{$root}/vendor/autoload.php";
    $app = require "{$root}/bootstrap/app.php";
    $app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
}

// Global counters
if (!isset($GLOBALS['_hc_pass'])) {
    $GLOBALS['_hc_pass'] = 0;
    $GLOBALS['_hc_fail'] = 0;
    $GLOBALS['_hc_failures'] = [];
    $GLOBALS['_hc_warnings'] = [];
}

if (!function_exists('hc_test')) {
    function hc_test(string $name, bool $condition, string $detail = ''): void
    {
        if ($condition) {
            $GLOBALS['_hc_pass']++;
            echo "  PASS: {$name}\n";
        } else {
            $GLOBALS['_hc_fail']++;
            $msg = $name . ($detail ? " -- {$detail}" : '');
            $GLOBALS['_hc_failures'][] = $msg;
            echo "  FAIL: {$msg}\n";
        }
    }

    function hc_warn(string $msg): void
    {
        $GLOBALS['_hc_warnings'][] = $msg;
        echo "  WARN: {$msg}\n";
    }

    function hc_section(string $title): void
    {
        echo "\n=== {$title} ===\n";
    }

    function hc_results(): array
    {
        return [
            'pass' => $GLOBALS['_hc_pass'],
            'fail' => $GLOBALS['_hc_fail'],
            'failures' => $GLOBALS['_hc_failures'],
            'warnings' => $GLOBALS['_hc_warnings'],
        ];
    }

    function hc_reset(): void
    {
        $GLOBALS['_hc_pass'] = 0;
        $GLOBALS['_hc_fail'] = 0;
        $GLOBALS['_hc_failures'] = [];
        $GLOBALS['_hc_warnings'] = [];
    }

    /**
     * Call a controller method safely, returning [statusCode, decodedData] or [0, null] on error.
     */
    function hc_call(string $controllerClass, string $method, array $queryParams = []): array
    {
        try {
            $ctrl = app($controllerClass);
            $url = '/test?' . http_build_query($queryParams);
            $req = \Illuminate\Http\Request::create($url, 'GET', $queryParams);
            $resp = $ctrl->$method($req);

            $status = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
            $content = method_exists($resp, 'getContent') ? $resp->getContent() : json_encode($resp);
            $data = json_decode($content, true);
            return [$status, $data];
        } catch (\Exception $e) {
            hc_test("{$controllerClass}::{$method}()", false, $e->getMessage());
            return [0, null];
        }
    }

    /**
     * Call a controller method that takes an ID parameter.
     */
    function hc_call_with_id(string $controllerClass, string $method, $id): array
    {
        try {
            $ctrl = app($controllerClass);
            $resp = $ctrl->$method($id);

            $status = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
            $content = method_exists($resp, 'getContent') ? $resp->getContent() : json_encode($resp);
            $data = json_decode($content, true);
            return [$status, $data];
        } catch (\Exception $e) {
            hc_test("{$controllerClass}::{$method}({$id})", false, $e->getMessage());
            return [0, null];
        }
    }

    /**
     * Call a controller method with no parameters (stats, getRoles, etc.)
     */
    function hc_call_no_args(string $controllerClass, string $method): array
    {
        try {
            $ctrl = app($controllerClass);
            $resp = $ctrl->$method();

            $status = method_exists($resp, 'getStatusCode') ? $resp->getStatusCode() : 200;
            $content = method_exists($resp, 'getContent') ? $resp->getContent() : json_encode($resp);
            $data = json_decode($content, true);
            return [$status, $data];
        } catch (\Exception $e) {
            hc_test("{$controllerClass}::{$method}()", false, $e->getMessage());
            return [0, null];
        }
    }
}
