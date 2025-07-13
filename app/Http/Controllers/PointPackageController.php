<?php

namespace App\Http\Controllers;

use App\Models\PointPackage;
use App\Models\PremiumFeature;
use App\Models\User;
use App\Models\point_transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PointPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index()
    {
        $packages = PointPackage::all();
        $features = PremiumFeature::all();
        
        return view('admin.point-packages.index', compact('packages', 'features'));
    }

    public function create()
    {
        return view('admin.point-packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'features' => 'array'
        ]);

        $package = PointPackage::create($request->all());

        if ($request->has('features')) {
            $package->features()->attach($request->features);
        }

        return redirect()->route('point-packages.index')
            ->with('success', 'Point package created successfully.');
    }

    public function edit(PointPackage $pointPackage)
    {
        return view('admin.point-packages.edit', compact('pointPackage'));
    }

    public function update(Request $request, PointPackage $pointPackage)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'features' => 'array'
        ]);

        $pointPackage->update($request->all());

        if ($request->has('features')) {
            $pointPackage->features()->sync($request->features);
        }

        return redirect()->route('point-packages.index')
            ->with('success', 'Point package updated successfully.');
    }

    public function destroy(PointPackage $pointPackage)
    {
        $pointPackage->delete();
        return redirect()->route('point-packages.index')
            ->with('success', 'Point package deleted successfully.');
    }

    // Premium Features Management
    public function features()
    {
        $features = PremiumFeature::all();
        return view('admin.premium-features.index', compact('features'));
    }

    public function createFeature()
    {
        return view('admin.premium-features.create');
    }

    public function storeFeature(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'point_cost' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'feature_type' => 'required|in:post_enhancement,user_benefit,system_feature'
        ]);

        PremiumFeature::create($request->all());

        return redirect()->route('premium-features.index')
            ->with('success', 'Premium feature created successfully.');
    }

    // User Point Purchase
    public function purchasePackage(Request $request, PointPackage $package)
    {
        $user = Auth::user();
        
        // Check if user has enough points
        $userPoints = $user->palservice_points->sum('points') ?? 0;
        
        if ($userPoints < $package->points) {
            return back()->with('error', 'Insufficient points to purchase this package.');
        }

        DB::transaction(function () use ($user, $package) {
            // Deduct points from user
            $user->palservice_points()->create([
                'points' => -$package->points,
                'type' => 'used',
                'description' => "Purchased package: {$package->name}"
            ]);

            // Grant package features to user
            foreach ($package->features as $feature) {
                $this->grantFeatureToUser($user, $feature);
            }

            // Record transaction
            point_transactions::create([
                'user_id' => $user->id,
                'points' => $package->points,
                'type' => 'used',
                'description' => "Package purchase: {$package->name}",
                'balance_after' => $userPoints - $package->points
            ]);
        });

        return back()->with('success', "Successfully purchased {$package->name} package!");
    }

    private function grantFeatureToUser(User $user, PremiumFeature $feature)
    {
        // Implement feature granting logic based on feature type
        switch ($feature->feature_type) {
            case 'post_enhancement':
                // Grant ability to create premium posts
                $user->premium_features()->updateOrCreate(
                    ['feature_id' => $feature->id],
                    ['expires_at' => now()->addDays(30)]
                );
                break;
                
            case 'user_benefit':
                // Grant user benefits (profile highlights, etc.)
                $user->user_benefits()->updateOrCreate(
                    ['feature_id' => $feature->id],
                    ['expires_at' => now()->addDays(30)]
                );
                break;
                
            case 'system_feature':
                // Grant system-wide features
                $user->system_features()->updateOrCreate(
                    ['feature_id' => $feature->id],
                    ['expires_at' => now()->addDays(30)]
                );
                break;
        }
    }

    // Analytics
    public function analytics()
    {
        $packageSales = PointPackage::withCount('sales')->get();
        $featureUsage = PremiumFeature::withCount('userFeatures')->get();
        
        $monthlySales = DB::table('point_transactions')
            ->where('type', 'used')
            ->where('description', 'like', 'Package purchase:%')
            ->selectRaw('MONTH(created_at) as month, SUM(points) as total_points')
            ->groupBy('month')
            ->get();

        return view('admin.point-packages.analytics', compact('packageSales', 'featureUsage', 'monthlySales'));
    }
} 