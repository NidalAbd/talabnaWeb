<?php

namespace App\Http\Controllers;

use App\Models\PointPackage;
use App\Models\PremiumFeature;
use App\Models\User;
use App\Models\point_transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        
        return view('admin.point_packages.index', compact('packages', 'features'));
    }

    public function create()
    {
        return view('admin.point_packages.create');
    }

    public function store(Request $request)
    {
        Log::info('PointPackageController@store called', [
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'method' => $request->method()
        ]);

        $request->validate([
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'features.ar' => 'nullable|string',
            'features.en' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'display_order' => 'nullable|integer|min:1',
            'max_purchases' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        try {
            $data = $request->all();
            $data['name'] = [
                'ar' => $request->input('name.ar', ''),
                'en' => $request->input('name.en', '')
            ];
            $data['description'] = [
                'ar' => $request->input('description.ar', ''),
                'en' => $request->input('description.en', '')
            ];
            $data['features'] = [
                'ar' => $request->input('features.ar', ''),
                'en' => $request->input('features.en', '')
            ];

            // Map form fields to database columns
            $packageData = [
                'name' => $data['name'],
                'description' => $data['description'],
                'points_amount' => $data['points'],
                'price' => $data['price'],
                'currency_code' => $data['currency'],
                'validity_days' => $data['duration_days'],
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : true,
                'is_popular' => isset($data['is_popular']) ? (bool)$data['is_popular'] : false,
                'display_order' => $data['display_order'] ?? 0,
                'features' => $data['features'] ?? null,
            ];

            // Remove any fields that don't exist in the database
            $packageData = array_intersect_key($packageData, array_flip([
                'name', 'description', 'points_amount', 'price', 'currency_code', 
                'validity_days', 'is_active', 'is_popular', 'display_order', 'features'
            ]));

            Log::info('Final package data for creation', ['package_data' => $packageData]);
            
            $package = PointPackage::create($packageData);
            Log::info('Point package created successfully', ['package_id' => $package->id]);

            if ($request->ajax()) {
                Log::info('Returning AJAX response for store');
                return response()->json([
                    'success' => true, 
                    'message' => 'Point package created successfully.',
                    'package' => $package
                ]);
            }

            return redirect()->route('admin.point_packages.index')
                ->with('success', 'Point package created successfully.');
        } catch (\Exception $e) {
            Log::error("Error creating point package: " . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to create point package.'], 500);
            }
            
            return back()->with('error', 'Failed to create point package.');
        }
    }

    public function show(PointPackage $pointPackage)
    {
        return view('admin.point_packages.show', compact('pointPackage'));
    }

    public function edit(PointPackage $pointPackage)
    {
        return view('admin.point_packages.edit', compact('pointPackage'));
    }

    public function update(Request $request, PointPackage $pointPackage)
    {
        Log::info('PointPackageController@update called', [
            'package_id' => $pointPackage->id,
            'request_data' => $request->all(),
            'is_ajax' => $request->ajax(),
            'method' => $request->method()
        ]);

        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'points' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'currency' => 'required|string|max:10',
            'duration_days' => 'required|integer|min:1',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        try {
            // Map form fields to database columns
            $data = $request->all();
            $updateData = [
                'name' => [
                    'ar' => $data['name']['ar'] ?? '',
                    'en' => $data['name']['en'] ?? ''
                ],
                'description' => [
                    'ar' => $data['description']['ar'] ?? '',
                    'en' => $data['description']['en'] ?? ''
                ],
                'points_amount' => $data['points'],
                'price' => $data['price'],
                'currency_code' => $data['currency'],
                'validity_days' => $data['duration_days'],
                'discount_percentage' => $data['discount_percentage'] ?? 0,
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? (bool)$data['is_active'] : false,
                'is_popular' => isset($data['is_popular']) ? (bool)$data['is_popular'] : false,
                'icon' => $data['icon'] ?? null,
                'color' => $data['color'] ?? null,
                'features' => [
                    'ar' => $data['features']['ar'] ?? '',
                    'en' => $data['features']['en'] ?? ''
                ]
            ];

            $pointPackage->update($updateData);
            Log::info('Point package updated successfully', ['package_id' => $pointPackage->id]);

            if ($request->ajax()) {
                Log::info('Returning AJAX response for update');
                return response()->json([
                    'success' => true, 
                    'message' => 'Point package updated successfully.',
                    'package' => $pointPackage->fresh()
                ]);
            }

            return redirect()->route('admin.point_packages.index')
                ->with('success', 'Point package updated successfully.');
        } catch (\Exception $e) {
            Log::error("Error updating point package: " . $e->getMessage(), [
                'exception' => $e,
                'package_id' => $pointPackage->id,
                'request_data' => $request->all()
            ]);
            
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to update point package.'], 500);
            }
            
            return back()->with('error', 'Failed to update point package.');
        }
    }

    public function destroy(PointPackage $pointPackage)
    {
        Log::info('PointPackageController@destroy called', [
            'package_id' => $pointPackage->id,
            'is_ajax' => request()->ajax()
        ]);

        try {
            $pointPackage->delete();
            Log::info('Point package deleted successfully', ['package_id' => $pointPackage->id]);
            
            if (request()->ajax()) {
                Log::info('Returning AJAX response for destroy');
                return response()->json(['success' => true, 'message' => 'Point package deleted successfully.']);
            }
            
            return redirect()->route('admin.point_packages.index')
                ->with('success', 'Point package deleted successfully.');
        } catch (\Exception $e) {
            Log::error("Error deleting point package: " . $e->getMessage(), [
                'exception' => $e,
                'package_id' => $pointPackage->id
            ]);
            
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Failed to delete point package.'], 500);
            }
            
            return back()->with('error', 'Failed to delete point package.');
        }
    }

    // Toggle package status
    public function toggleStatus(PointPackage $pointPackage)
    {
        Log::info('PointPackageController@toggleStatus called', [
            'package_id' => $pointPackage->id,
            'current_status' => $pointPackage->is_active
        ]);

        try {
            $pointPackage->update(['is_active' => !$pointPackage->is_active]);
            Log::info('Package status toggled successfully', [
                'package_id' => $pointPackage->id,
                'new_status' => $pointPackage->is_active
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Package status updated successfully.',
                'is_active' => $pointPackage->is_active
            ]);
        } catch (\Exception $e) {
            Log::error("Error toggling package status: " . $e->getMessage(), [
                'exception' => $e,
                'package_id' => $pointPackage->id
            ]);
            return response()->json(['success' => false, 'message' => 'Failed to update package status.'], 500);
        }
    }







    // Export functionality
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $packages = PointPackage::all();
        
        if ($format === 'pdf') {
            // PDF export logic
            return response()->json(['success' => true, 'message' => 'PDF export functionality will be implemented.']);
        } else {
            // Excel export logic
            return response()->json(['success' => true, 'message' => 'Excel export functionality will be implemented.']);
        }
    }

    // Premium Features Management
    public function features()
    {
        $features = PremiumFeature::all();
        return view('admin.premium_features.index', compact('features'));
    }

    public function createFeature()
    {
        return view('admin.premium_features.create');
    }

    public function storeFeature(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1',
            'category' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        PremiumFeature::create($request->all());

        return redirect()->route('admin.premium-features.index')
            ->with('success', 'Premium feature created successfully.');
    }

    public function editFeature(PremiumFeature $feature)
    {
        return view('admin.premium_features.edit', compact('feature'));
    }

    public function updateFeature(Request $request, PremiumFeature $feature)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string',
            'description.en' => 'nullable|string',
            'points_cost' => 'required|integer|min:1',
            'duration_days' => 'required|integer|min:1',
            'category' => 'required|string|max:100',
            'icon' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
            'is_popular' => 'boolean'
        ]);

        try {
            $feature->update($request->all());
            return response()->json(['success' => true, 'message' => 'Premium feature updated successfully.']);
        } catch (\Exception $e) {
            Log::error("Error updating premium feature: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update premium feature.'], 500);
        }
    }

    public function destroyFeature(PremiumFeature $feature)
    {
        try {
            $feature->delete();
            return response()->json(['success' => true, 'message' => 'Premium feature deleted successfully.']);
        } catch (\Exception $e) {
            Log::error("Error deleting premium feature: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete premium feature.'], 500);
        }
    }

    public function showFeature(PremiumFeature $feature)
    {
        return view('admin.premium_features.show', compact('feature'));
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
        $packageSales = PointPackage::withCount('transactions')->get();
        $monthlySales = point_transactions::selectRaw('MONTH(created_at) as month, SUM(points) as total_points')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->get();

        return view('admin.point_packages.analytics', compact('packageSales', 'monthlySales'));
    }

    // AJAX Methods for enhanced functionality
    public function list()
    {
        try {
            $packages = PointPackage::select('id', 'name', 'points_amount as points', 'price', 'currency_code as currency', 'is_active', 'is_popular')
                ->get()
                ->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'name' => $package->name,
                        'points' => $package->points,
                        'price' => $package->price,
                        'currency' => $package->currency,
                        'is_active' => $package->is_active,
                        'is_popular' => $package->is_popular
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching packages list: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch packages list.'], 500);
        }
    }

    public function stats()
    {
        try {
            $total = PointPackage::count();
            $active = PointPackage::where('is_active', true)->count();
            $popular = PointPackage::where('is_popular', true)->count();
            $inactive = PointPackage::where('is_active', false)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'active' => $active,
                    'popular' => $popular,
                    'inactive' => $inactive
                ]
            ]);
        } catch (\Exception $e) {
            Log::error("Error fetching packages stats: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to fetch packages stats.'], 500);
        }
    }

    public function setPopular(Request $request)
    {
        try {
            $packageIds = $request->input('package_ids', []);
            
            if (empty($packageIds)) {
                return response()->json(['success' => false, 'message' => 'No packages selected.'], 400);
            }

            // First, remove popular status from all packages
            PointPackage::query()->update(['is_popular' => false]);
            
            // Then set popular status for selected packages
            PointPackage::whereIn('id', $packageIds)->update(['is_popular' => true]);

            // Get updated packages for table refresh
            $packages = PointPackage::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points' => $package->points_amount,
                    'price' => $package->price,
                    'currency' => $package->currency_code,
                    'duration_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'icon' => $package->icon,
                    'color' => $package->color
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Popular packages updated successfully.',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error setting popular packages: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update popular packages.'], 500);
        }
    }

    // Enhanced bulk operations with data return
    public function bulkActivate()
    {
        try {
            PointPackage::where('is_active', false)->update(['is_active' => true]);
            
            // Get updated packages for table refresh
            $packages = PointPackage::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points' => $package->points_amount,
                    'price' => $package->price,
                    'currency' => $package->currency_code,
                    'duration_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'icon' => $package->icon,
                    'color' => $package->color
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All packages activated successfully.',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error bulk activating packages: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to activate packages.'], 500);
        }
    }

    public function bulkDeactivate()
    {
        try {
            PointPackage::where('is_active', true)->update(['is_active' => false]);
            
            // Get updated packages for table refresh
            $packages = PointPackage::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points' => $package->points_amount,
                    'price' => $package->price,
                    'currency' => $package->currency_code,
                    'duration_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'icon' => $package->icon,
                    'color' => $package->color
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'All packages deactivated successfully.',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error bulk deactivating packages: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to deactivate packages.'], 500);
        }
    }

    // Enhanced duplicate with data return
    public function duplicate(PointPackage $pointPackage)
    {
        try {
            $newPackage = $pointPackage->replicate();
            $newPackage->name = [
                'ar' => $pointPackage->name['ar'] . ' (Copy)',
                'en' => $pointPackage->name['en'] . ' (Copy)'
            ];
            $newPackage->is_popular = false;
            $newPackage->save();
            
            // Get updated packages for table refresh
            $packages = PointPackage::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points' => $package->points_amount,
                    'price' => $package->price,
                    'currency' => $package->currency_code,
                    'duration_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'icon' => $package->icon,
                    'color' => $package->color
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Package duplicated successfully.',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error duplicating package: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to duplicate package.'], 500);
        }
    }

    // Enhanced toggle popular with data return
    public function togglePopular(PointPackage $pointPackage)
    {
        try {
            $pointPackage->update(['is_popular' => !$pointPackage->is_popular]);
            
            // Get updated packages for table refresh
            $packages = PointPackage::all()->map(function ($package) {
                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'points' => $package->points_amount,
                    'price' => $package->price,
                    'currency' => $package->currency_code,
                    'duration_days' => $package->validity_days,
                    'discount_percentage' => $package->discount_percentage ?? 0,
                    'is_active' => $package->is_active,
                    'is_popular' => $package->is_popular,
                    'icon' => $package->icon,
                    'color' => $package->color
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Package popular status updated successfully.',
                'data' => $packages
            ]);
        } catch (\Exception $e) {
            Log::error("Error toggling package popular status: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update package popular status.'], 500);
        }
    }
} 