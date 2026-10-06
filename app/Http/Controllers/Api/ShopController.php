<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FeatureUnlocks;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Release C (2026-10-07): shop page. Business includes it; others unlock it with points for 30 days. The shop shows on
 * the owner's profile only while it is active (plan or unlock), so it disappears cleanly when that ends.
 */
class ShopController extends Controller
{
    /** GET /api/users/{id}/shop: the shop if active, else null. */
    public function show(int $id): JsonResponse
    {
        $shop = DB::table('shops')->where('user_id', $id)->first();
        if (! $shop || ! FeatureUnlocks::has($id, 'shop_page')) {
            return response()->json(['shop' => null]);
        }

        return response()->json(['shop' => $this->present($shop)]);
    }

    /** GET /api/me/shop: mine, whether it is active, and the price to activate it. */
    public function mine(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $shop = DB::table('shops')->where('user_id', $uid)->first();

        return response()->json([
            'shop' => $shop ? $this->present($shop) : null,
            'active' => FeatureUnlocks::has($uid, 'shop_page'),
            'price' => FeatureUnlocks::price('shop_page'),
        ]);
    }

    /** POST /api/me/shop (multipart: name, about, hours, logo?) */
    public function save(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        if (! FeatureUnlocks::has($uid, 'shop_page')) {
            return response()->json(['message' => 'Shop pages come with Business or can be unlocked with points.', 'code' => 'locked',
                'price' => FeatureUnlocks::price('shop_page')], 403);
        }
        $d = $request->validate([
            'name' => 'required|string|min:2|max:80',
            'about' => 'nullable|string|max:1000',
            'hours' => 'nullable|string|max:200',
            'logo' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:4096',
        ]);
        $row = ['name' => $d['name'], 'about' => $d['about'] ?? null, 'hours' => $d['hours'] ?? null, 'updated_at' => now()];
        if ($request->hasFile('logo')) {
            $old = DB::table('shops')->where('user_id', $uid)->value('logo');
            $row['logo'] = 'storage/'.$request->file('logo')->store('shops', 'public');
            if ($old && str_starts_with($old, 'storage/shops/')) {
                Storage::disk('public')->delete(substr($old, strlen('storage/')));
            }
        }
        DB::table('shops')->updateOrInsert(['user_id' => $uid], $row + ['created_at' => now()]);

        return response()->json(['shop' => $this->present(DB::table('shops')->where('user_id', $uid)->first())]);
    }

    private function present(object $shop): array
    {
        return ['user_id' => $shop->user_id, 'name' => $shop->name, 'logo' => $shop->logo, 'about' => $shop->about, 'hours' => $shop->hours];
    }
}
