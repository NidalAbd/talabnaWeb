<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SubscriptionPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = SubscriptionPlan::query();

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->status === 'active') $query->where('is_active', true);
        if ($request->status === 'inactive') $query->where('is_active', false);

        $query->orderBy('sort_order')->orderBy('price_points');

        $plans = $query->paginate($request->per_page ?? 15);

        // Add subscriber counts
        $plans->getCollection()->transform(function ($plan) {
            $plan->active_subscribers = UserSubscription::where('subscription_plan_id', $plan->id)
                ->active()->count();
            $plan->total_subscribers = UserSubscription::where('subscription_plan_id', $plan->id)->count();
            return $plan;
        });

        return response()->json($plans);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'required|string',
            'description' => 'nullable|array',
            'slug' => 'nullable|string|unique:subscription_plans,slug',
            'price_points' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        if (empty($data['slug'])) {
            $data['slug'] = Str::slug($data['name']['en']);
        }

        $plan = SubscriptionPlan::create($data);

        return response()->json(['success' => true, 'plan' => $plan], 201);
    }

    public function show(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->active_subscribers = UserSubscription::where('subscription_plan_id', $id)->active()->count();
        $plan->total_subscribers = UserSubscription::where('subscription_plan_id', $id)->count();
        $plan->total_revenue = UserSubscription::where('subscription_plan_id', $id)->sum('points_paid');

        return response()->json(['plan' => $plan]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|array',
            'name.ar' => 'required|string',
            'name.en' => 'required|string',
            'description' => 'nullable|array',
            'slug' => 'nullable|string|unique:subscription_plans,slug,' . $id,
            'price_points' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'features' => 'required|array',
            'color' => 'nullable|string|max:20',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $plan->update($validator->validated());

        return response()->json(['success' => true, 'plan' => $plan]);
    }

    public function destroy(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);

        $activeCount = UserSubscription::where('subscription_plan_id', $id)->active()->count();
        if ($activeCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete plan with {$activeCount} active subscribers"
            ], 400);
        }

        $plan->delete();
        return response()->json(['success' => true]);
    }

    public function toggle(int $id): JsonResponse
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);
        return response()->json(['success' => true, 'is_active' => $plan->is_active]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'total_plans' => SubscriptionPlan::count(),
            'active_plans' => SubscriptionPlan::where('is_active', true)->count(),
            'total_subscribers' => UserSubscription::active()->count(),
            'total_revenue' => UserSubscription::sum('points_paid'),
            'plans_breakdown' => SubscriptionPlan::withCount(['subscriptions as active_count' => function ($q) {
                $q->where('status', 'active')->where('expires_at', '>', now());
            }])->get(['id', 'name', 'slug', 'price_points']),
        ]);
    }
}
