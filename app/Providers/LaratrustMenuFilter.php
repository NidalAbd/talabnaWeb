<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class LaratrustMenuFilter implements FilterInterface
{
    public function transform($item)
    {
        // Handle route with dynamic user ID
        if (isset($item['route']) && isset($item['params'])) {
            $transformedParams = [];
            foreach ($item['params'] as $key => $value) {
                if ($value === 'auth_user_id' && auth()->check()) {
                    $transformedParams[$key] = auth()->id();
                } else {
                    $transformedParams[$key] = $value;
                }
            }

            $item['url'] = route($item['route'], $transformedParams);
        }

        // Check Laratrust permissions
        if (isset($item['permission'])) {
            $hasPermission = false;

            foreach ($item['permission'] as $permission) {
                if (auth()->user()->isAbleTo($permission)) {
                    $hasPermission = true;
                    break;
                }
            }

            if (!$hasPermission) {
                return false;
            }
        }

        return $item;
    }
}
