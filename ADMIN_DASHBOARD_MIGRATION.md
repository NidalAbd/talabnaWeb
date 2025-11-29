# Admin Dashboard Migration to Vue.js

This guide explains how to migrate existing AdminLTE Blade views to Vue.js components while keeping the mobile API unchanged.

## Table of Contents
1. [Overview](#overview)
2. [Current Setup](#current-setup)
3. [Migration Steps](#migration-steps)
4. [AdminLTE Configuration](#adminlte-configuration)
5. [Creating New Dashboard Pages](#creating-new-dashboard-pages)
6. [API Endpoints](#api-endpoints)
7. [Troubleshooting](#troubleshooting)

---

## Overview

The admin dashboard has been migrated to use Vue.js 3 for a modern, reactive interface while maintaining:
- ✅ AdminLTE 3 layout and styling
- ✅ Separate API routes for admin dashboard (doesn't affect mobile app)
- ✅ Laravel Blade wrapper for authentication and layout
- ✅ Vue.js components for dynamic content

## Current Setup

### Directory Structure
```
talabnaWeb/
├── resources/
│   ├── js/
│   │   ├── admin.js                    # Admin Vue app entry point
│   │   ├── components/
│   │   │   └── admin/
│   │   │       └── charts/             # Chart components
│   │   │           ├── PieChart.vue
│   │   │           ├── LineChart.vue
│   │   │           ├── BarChart.vue
│   │   │           └── MixedChart.vue
│   │   └── views/
│   │       └── admin/
│   │           ├── ModernDashboard.vue # Main dashboard component
│   │           └── ...                 # Other admin views
│   └── views/
│       └── admin/
│           └── dashboard.blade.php     # Blade wrapper for Vue
├── app/
│   └── Http/
│       └── Controllers/
│           ├── Admin/
│           │   └── DashboardApiController.php  # Dashboard API
│           └── HomeController.php               # Blade view controller
└── routes/
    ├── web.php                         # Web routes (admin dashboard)
    └── api.php                         # Mobile API routes
```

### Key Files

**1. Entry Point:** `resources/js/admin.js`
```javascript
import { createApp } from 'vue'
import ModernDashboard from './views/admin/ModernDashboard.vue'

const app = createApp({})
app.component('AdminDashboard', ModernDashboard)
app.mount('#admin-app')
```

**2. Blade Wrapper:** `resources/views/admin/dashboard.blade.php`
```blade
@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <h1>Dashboard</h1>
@stop

@section('content')
    <div id="admin-app">
        <admin-dashboard></admin-dashboard>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
```

**3. Dashboard API:** `app/Http/Controllers/Admin/DashboardApiController.php`
- Endpoint: `/api/dashboard`
- Protected by: `auth` and `admin` middleware
- Returns: JSON data for dashboard

---

## Migration Steps

### Step 1: Create API Endpoint for Your Page

Create a new API controller in `app/Http/Controllers/Admin/`:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class YourFeatureApiController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $data = [
                // Your data here
                'items' => YourModel::all(),
                'stats' => $this->getStats(),
            ];

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getStats(): array
    {
        return [
            'total' => YourModel::count(),
            // Add more stats
        ];
    }
}
```

### Step 2: Add Route to `routes/web.php`

Add your route inside the admin middleware group:

```php
Route::middleware(['auth', 'admin'])->group(function () {
    // Existing routes...

    // API routes for admin dashboard
    Route::prefix('api')->group(function () {
        Route::get('/dashboard', [DashboardApiController::class, 'index'])->name('dashboard');

        // Add your new route here
        Route::get('/your-feature', [YourFeatureApiController::class, 'index'])->name('your-feature.api');
    });
});
```

### Step 3: Create Vue Component

Create a new Vue component in `resources/js/views/admin/`:

```vue
<template>
  <div>
    <!-- Loading State -->
    <div v-if="loading" class="text-center py-5">
      <div class="spinner-border text-primary" role="status">
        <span class="sr-only">Loading...</span>
      </div>
    </div>

    <!-- Content -->
    <div v-else>
      <div class="row">
        <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Your Feature Title</h3>
            </div>
            <div class="card-body">
              <!-- Your content here -->
              <p>Total Items: {{ stats.total }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const loading = ref(true)
const items = ref([])
const stats = ref({})

onMounted(async () => {
  await loadData()
})

const loadData = async () => {
  try {
    loading.value = true
    const response = await fetch('/api/your-feature')
    const data = await response.json()

    items.value = data.items
    stats.value = data.stats
  } catch (error) {
    console.error('Error loading data:', error)
  } finally {
    loading.value = false
  }
}
</script>
```

### Step 4: Create Blade View

Create a new Blade file in `resources/views/admin/`:

```blade
@extends('adminlte::page')

@section('title', 'Your Feature Title')

@section('content_header')
    <h1>Your Feature Title</h1>
@stop

@section('content')
    <div id="admin-app">
        <your-feature-component></your-feature-component>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
```

### Step 5: Register Component in `admin.js`

```javascript
import YourFeatureComponent from './views/admin/YourFeature.vue'

app.component('YourFeatureComponent', YourFeatureComponent)
```

### Step 6: Add Route to Display Blade View

In `routes/web.php`:

```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    // Add your new page route
    Route::get('/your-feature', function () {
        return view('admin.your-feature');
    })->name('your-feature');
});
```

### Step 7: Build Assets

```bash
npm run build
```

---

## AdminLTE Configuration

### Update Menu in `config/adminlte.php`

Add menu items for your new pages:

```php
'menu' => [
    [
        'text' => 'Dashboard',
        'url'  => 'dashboard',
        'icon' => 'fas fa-fw fa-tachometer-alt',
    ],
    [
        'text' => 'Your Feature',
        'url'  => 'your-feature',
        'icon' => 'fas fa-fw fa-list',
    ],
    // Add more menu items...
],
```

### Important Configuration Keys

```php
// In config/adminlte.php

// Dashboard URL (redirect after login)
'dashboard_url' => 'dashboard',

// Login URL
'login_url' => 'login',

// Logout URL
'logout_url' => 'logout',

// Use route URLs instead of direct URLs
'use_route_url' => true,
```

---

## Creating New Dashboard Pages

### Pattern to Follow

1. **API First**: Create API controller that returns JSON
2. **Route It**: Add route in `web.php` under admin middleware
3. **Build Vue**: Create Vue component that fetches from API
4. **Wrap in Blade**: Create Blade view that extends AdminLTE
5. **Register**: Add component to `admin.js`
6. **Build**: Run `npm run build`

### Example: User Management Page

**1. API Controller:**
```php
// app/Http/Controllers/Admin/UserManagementApiController.php
public function index(): JsonResponse
{
    return response()->json([
        'users' => User::with('roles')->paginate(20),
        'stats' => [
            'total' => User::count(),
            'active' => User::where('is_active', 'active')->count(),
        ]
    ]);
}
```

**2. Web Route (API):**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::prefix('api')->group(function () {
        Route::get('/users', [UserManagementApiController::class, 'index']);
    });
});
```

**3. Web Route (View):**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/users', fn() => view('admin.users'))->name('users');
});
```

**4. Vue Component:**
```vue
<!-- resources/js/views/admin/Users.vue -->
<template>
  <div class="card">
    <div class="card-body">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.id }}</td>
            <td>{{ user.name }}</td>
            <td>{{ user.email }}</td>
            <td>{{ user.is_active }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const users = ref([])

onMounted(async () => {
  const response = await fetch('/api/users')
  const data = await response.json()
  users.value = data.users.data
})
</script>
```

**5. Blade View:**
```blade
<!-- resources/views/admin/users.blade.php -->
@extends('adminlte::page')

@section('title', 'User Management')

@section('content_header')
    <h1>Users</h1>
@stop

@section('content')
    <div id="admin-app">
        <users-component></users-component>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
```

**6. Register in admin.js:**
```javascript
import Users from './views/admin/Users.vue'
app.component('UsersComponent', Users)
```

---

## API Endpoints

### Admin Dashboard API Routes
All admin dashboard API routes should be:
- Protected by `['auth', 'admin']` middleware
- Prefixed with `/api/` in the URL
- Return JSON responses
- Placed in `app/Http/Controllers/Admin/` namespace

### Mobile API Routes
Mobile API routes remain unchanged:
- Located in `routes/api.php`
- Prefixed with `/api/` automatically by Laravel
- Use different controllers (not in Admin namespace)

### Example Route Structure

```php
// routes/web.php - Admin Dashboard APIs
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::get('/dashboard', [Admin\DashboardApiController::class, 'index']);
    Route::get('/users', [Admin\UserApiController::class, 'index']);
    Route::get('/reports', [Admin\ReportApiController::class, 'index']);
});

// routes/api.php - Mobile App APIs (unchanged)
Route::post('/login', [Api\UserController::class, 'login']);
Route::get('/posts', [Api\PostController::class, 'index']);
```

---

## Chart Components

### Using Existing Chart Components

The project includes reusable chart components:

**PieChart.vue:**
```vue
<pie-chart :data="chartData" :height="250" :key="'unique-key'" />
```

**LineChart.vue:**
```vue
<line-chart :data="chartData" :height="300" />
```

**BarChart.vue:**
```vue
<bar-chart :data="chartData" :height="350" />
```

### Chart Data Format

```javascript
const chartData = {
  labels: ['Label 1', 'Label 2', 'Label 3'],
  datasets: [{
    label: 'Dataset Name',
    data: [10, 20, 30],
    backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
  }]
}
```

### Important: Chart Container Sizing

Always wrap charts in containers with fixed heights:

```vue
<div class="card-body" style="height: 350px; max-height: 350px; overflow: hidden;">
  <line-chart :data="chartData" :height="300" />
</div>
```

This prevents infinite resize loops.

---

## Troubleshooting

### Issue: Charts Growing Infinitely

**Solution:** Ensure chart containers have fixed heights:
```html
<div style="height: 350px; max-height: 350px; overflow: hidden;">
  <your-chart :data="data" />
</div>
```

### Issue: 404 on API Endpoint

**Check:**
1. Route is defined in `routes/web.php`
2. Route is inside `['auth', 'admin']` middleware group
3. Route prefix is `/api/`
4. Run `php artisan route:clear`

### Issue: Vue Component Not Rendering

**Check:**
1. Component is registered in `admin.js`
2. Component name matches in Blade (kebab-case vs PascalCase)
3. Assets are built: `npm run build`
4. Browser cache is cleared

### Issue: Authentication Error

**Check:**
1. User is logged in
2. User has 'admin' role: `$user->hasRole('admin')`
3. AdminAccessMiddleware is applied to route

### Issue: Data Not Loading

**Check:**
1. API endpoint returns JSON
2. Network tab shows successful request
3. CORS is not blocking request
4. Error handling in Vue component catches errors

---

## Best Practices

### 1. API Controllers
- Always return JSON
- Use try-catch for error handling
- Add proper HTTP status codes
- Keep logic in private methods

### 2. Vue Components
- Use composition API (`<script setup>`)
- Show loading states
- Handle errors gracefully
- Use reactive refs for data

### 3. Blade Views
- Extend `adminlte::page`
- Mount Vue app in specific div
- Include `@vite(['resources/js/admin.js'])`
- Keep Blade minimal (just wrapper)

### 4. Routing
- Admin APIs in `web.php` with auth middleware
- Mobile APIs in `api.php` (unchanged)
- Use named routes
- Group related routes

### 5. Security
- Protect all admin routes with `['auth', 'admin']`
- Validate all inputs
- Use Laratrust for role checking
- Never expose sensitive data in API responses

---

## Quick Reference

### Build Commands
```bash
# Development build
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch
```

### Clear Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Check Routes
```bash
php artisan route:list --path=api
php artisan route:list --path=dashboard
```

---

## Migration Checklist

When converting an existing Blade page to Vue:

- [ ] Create API controller in `app/Http/Controllers/Admin/`
- [ ] Add API route in `routes/web.php` with admin middleware
- [ ] Create Vue component in `resources/js/views/admin/`
- [ ] Create Blade wrapper in `resources/views/admin/`
- [ ] Register component in `resources/js/admin.js`
- [ ] Add menu item in `config/adminlte.php`
- [ ] Run `npm run build`
- [ ] Test authentication and authorization
- [ ] Test data loading
- [ ] Clear browser cache and test

---

## Example: Complete Feature Implementation

See `ModernDashboard.vue` and `DashboardApiController.php` as reference implementations.

Key files to review:
- `resources/js/views/admin/ModernDashboard.vue`
- `app/Http/Controllers/Admin/DashboardApiController.php`
- `resources/views/admin/dashboard.blade.php`
- `routes/web.php` (lines 440-451)
- `config/adminlte.php` (menu configuration)

---

## Support

For issues or questions:
1. Check this documentation
2. Review console errors in browser
3. Check Laravel logs in `storage/logs/`
4. Verify route list with `php artisan route:list`

---

## Section Analysis & Migration Plans

This section provides detailed analysis of each admin dashboard section, documenting current functionality and migration plans.

---

### 1. Admin Management → Users

#### Current Implementation Analysis

**Blade Views:**
- `resources/views/users/index.blade.php` - Main users list with filtering
- `resources/views/users/show.blade.php` - Detailed user profile view
- `resources/views/users/create.blade.php` - Create new user form
- `resources/views/users/edit.blade.php` - Edit user form
- `resources/views/users/login_history.blade.php` - User login history
- `resources/views/admin/users/banned.blade.php` - Banned users list
- `resources/views/admin/users/ban_form.blade.php` - Ban user form

**Controllers:**
- `app/Http/Controllers/UserController.php` - Main user CRUD operations
- `app/Http/Controllers/UserRoleAssignmentController.php` - Role management

**Key Features:**

1. **Users List (index.blade.php)**
   - **Stats Widgets:** 4 info boxes showing:
     - Total Users count
     - Active Users count
     - Banned Users count
     - Total Posts count
   - **Filters:**
     - Status filter (All/Active/Inactive/Banned)
     - Role filter (dropdown with all available roles)
     - Search input (by name, email, user ID)
     - Auto-submit on filter change
     - Reset button
   - **Data Table:**
     - Columns: ID, Username (with avatar), Status badge, Role badges, Email, Reports count, Posts count, Actions
     - User avatar display with fallback to default image
     - Color-coded status badges
     - Role badges (multiple per user)
   - **Action Buttons per user:**
     - View (eye icon) - Links to user details
     - Edit (pen icon) - Links to edit form
     - Profile (ID card icon) - Links to public profile
     - Manage Roles (user-tag icon) - Links to role assignment
     - Add Points (coins icon) - Links to points management
     - Toggle Ban (ban/check icon) - AJAX toggle ban/unban
     - Report (flag icon) - Report button component
     - Delete (trash icon) - Delete user with confirmation
   - **Toggle Ban Feature:**
     - AJAX-based ban/unban without page reload
     - Updates button appearance dynamically
     - Updates status badge in table
     - Uses CSRF token
     - Shows loading spinner during request
     - Success/error messages with toastr
   - **Pagination:** Laravel pagination with query parameters preserved

2. **User Details (show.blade.php)**
   - **Left Sidebar:**
     - Profile Card:
       - Large circular avatar (150x150px)
       - Full name and username
       - Stats: Service Posts, Followers, Following, Points Balance
       - "View Public Profile" button
       - "Add Points" button
       - "Delete User" button
     - Account Status Card:
       - Color-coded status badge (active/banned/inactive)
       - Status update form with dropdown
     - Roles Card:
       - List of assigned roles with badges
       - "Manage Roles & Permissions" button
   - **Right Content:**
     - User Information Card (12 info boxes):
       - Full Name, Username, Email, Auth Type
       - Gender, Date of Birth, Phone, WhatsApp
       - Location (City, Country - multilingual support)
       - Data Saver status, Member Since, Last Updated
       - Latitude/Longitude with copy buttons
     - Google Maps integration showing user location
     - Activity Tabs:
       - **Service Posts Tab:** Table of user's posts with ID, Title, Category, Created date, Status, Actions
       - **Points History Tab:** Table showing Date, Points (+/-), Type, Description
       - **Reports Tab:** Table of reports against user (Reporter, Reason, Date, Actions)

3. **Create User (create.blade.php)**
   - **Left Column:**
     - Profile image preview (150x150 circular)
     - Image upload input
     - Auth Type dropdown (Email/Google)
     - Data Saver toggle switch
   - **Right Column (2-column grid):**
     - Full Name* (required)
     - Username* (required)
     - Email* (required)
     - Password* (required with confirmation)
     - Gender dropdown (ذكر/انثى)
     - Country dropdown (select2)
     - City dropdown (select2, dependent on country via AJAX)
     - Date of Birth date picker
     - Phone Number
     - WhatsApp Number
     - Account Status dropdown
   - **Location Section:**
     - Google Maps with draggable marker
     - Hidden inputs for latitude/longitude
     - Default location: Riyadh (24.774265, 46.738586)
   - **Roles Section:**
     - Checkbox grid for all available roles
   - **JavaScript Features:**
     - Image preview on file select
     - Country-City dependent dropdown with AJAX
     - Handles multilingual city names (Arabic/English)
     - Google Maps initialization with error handling
     - Marker drag to update coordinates
     - Select2 initialization for dropdowns

4. **Banned Users (banned.blade.php)**
   - **Stats Cards:** 4 widgets showing:
     - Banned Users count
     - Banned Devices count (with link)
     - Ban Actions count (with link to history)
     - Unban Actions count (with link to history)
   - **Search Card:** Collapsible search with reset button
   - **Data Table:**
     - Columns: ID, User (avatar + name/username), Contact Info (email/phone), Banned Devices (count + device info), Ban Reason, Banned At, Actions
     - Device info shows brand/model with modal link
     - Shows last ban reason from BanHistory
   - **Action Buttons:**
     - View user details
     - Unban button (POST with confirmation)
     - Ban History modal button
   - **Ban History Modal:**
     - Timeline view of all ban/unban actions
     - Shows: Action type (ban/unban), Timestamp, Performer, Reason, Related device
     - Color-coded timeline icons (red for ban, green for unban)
   - **Device Info Modals:** Separate modal for each device showing full details

**Database Relations:**
- User hasMany ServicePosts
- User hasMany Reports (as reporter and reportable)
- User hasMany Photos (morphMany)
- User belongsToMany Roles
- User hasMany BannedDevices
- User hasMany BanHistory
- User hasMany PointTransactions
- User belongsTo Country
- User belongsTo City
- User belongsToMany Users (followers/following)

**Routes:**
```php
// Web Routes (admin dashboard)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::get('users/data', [UserController::class, 'data'])->name('users.data');
    Route::post('users/{id}/update-status', [UserController::class, 'updateStatus']);
    Route::post('users/{id}/toggle-ban', [UserController::class, 'toggleBan']);
    Route::get('users/{id}/banned', [UserController::class, 'banned'])->name('users.banned');
    Route::post('users/{id}/unban', [UserController::class, 'unban'])->name('users.unban');
});

// API Routes (mobile app - unchanged)
Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', Api\UserController::class);
    Route::post('users/{user}/follow', [Api\UserController::class, 'follow']);
    Route::delete('users/{user}/unfollow', [Api\UserController::class, 'unfollow']);
    // ... more API routes
});
```

**Third-Party Integrations:**
- Select2 for enhanced dropdowns
- Google Maps JavaScript API for location
- Toastr for notifications
- Chart.js (not in this section but available)

---

#### Migration Plan: Users Section to Vue.js

**Phase 1: API Development**

1. **Create API Controller:** `app/Http/Controllers/Admin/UsersApiController.php`
   ```php
   class UsersApiController extends Controller
   {
       // GET /api/admin/users - List with filters, search, pagination
       public function index(Request $request): JsonResponse

       // GET /api/admin/users/{id} - User details with relations
       public function show($id): JsonResponse

       // POST /api/admin/users - Create new user
       public function store(Request $request): JsonResponse

       // PUT /api/admin/users/{id} - Update user
       public function update(Request $request, $id): JsonResponse

       // DELETE /api/admin/users/{id} - Delete user
       public function destroy($id): JsonResponse

       // POST /api/admin/users/{id}/toggle-ban - Toggle ban status
       public function toggleBan($id): JsonResponse

       // GET /api/admin/users/stats - Dashboard stats
       public function getStats(): JsonResponse

       // GET /api/admin/users/banned - Banned users list
       public function getBannedUsers(Request $request): JsonResponse

       // POST /api/admin/users/{id}/unban - Unban user
       public function unban(Request $request, $id): JsonResponse

       // GET /api/admin/users/{id}/ban-history - User's ban history
       public function getBanHistory($id): JsonResponse
   }
   ```

2. **Add Routes in `routes/web.php`:**
   ```php
   Route::middleware(['auth', 'admin'])->prefix('api/admin')->group(function () {
       Route::get('/users/stats', [Admin\UsersApiController::class, 'getStats']);
       Route::get('/users/banned', [Admin\UsersApiController::class, 'getBannedUsers']);
       Route::post('/users/{id}/toggle-ban', [Admin\UsersApiController::class, 'toggleBan']);
       Route::post('/users/{id}/unban', [Admin\UsersApiController::class, 'unban']);
       Route::get('/users/{id}/ban-history', [Admin\UsersApiController::class, 'getBanHistory']);
       Route::apiResource('users', Admin\UsersApiController::class);
   });
   ```

**Phase 2: Vue Components Development**

1. **Main Components:**
   - `resources/js/views/admin/users/UsersList.vue` - Main users list with filters
   - `resources/js/views/admin/users/UserDetails.vue` - User profile details
   - `resources/js/views/admin/users/UserForm.vue` - Create/Edit user form
   - `resources/js/views/admin/users/BannedUsers.vue` - Banned users list

2. **Reusable Sub-Components:**
   - `resources/js/components/admin/users/UserCard.vue` - User profile card
   - `resources/js/components/admin/users/UserStatsWidget.vue` - Stats box component
   - `resources/js/components/admin/users/UserFilters.vue` - Filter panel
   - `resources/js/components/admin/users/UserActionsMenu.vue` - Action buttons
   - `resources/js/components/admin/users/BanHistoryTimeline.vue` - Ban history timeline
   - `resources/js/components/admin/users/LocationPicker.vue` - Google Maps location picker
   - `resources/js/components/admin/users/ImageUploadPreview.vue` - Image upload with preview

3. **Composables (Reusable Logic):**
   - `resources/js/composables/useUsers.js` - User CRUD operations
   - `resources/js/composables/useUserFilters.js` - Filter/search logic
   - `resources/js/composables/useUserBan.js` - Ban/unban operations
   - `resources/js/composables/useGoogleMaps.js` - Maps integration
   - `resources/js/composables/usePagination.js` - Pagination logic

**Phase 3: Component Implementation Details**

**UsersList.vue Structure:**
```vue
<template>
  <div class="users-management">
    <!-- Stats Widgets Row -->
    <div class="row">
      <div class="col-lg-3 col-6" v-for="stat in stats" :key="stat.label">
        <UserStatsWidget :stat="stat" />
      </div>
    </div>

    <!-- Filters Section -->
    <div class="card card-outline card-light mb-3">
      <div class="card-body">
        <UserFilters
          v-model:status="filters.status"
          v-model:role="filters.role"
          v-model:search="filters.search"
          :roles="roles"
          @reset="resetFilters"
        />
      </div>
    </div>

    <!-- Users Table -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Users List</h3>
        <div class="card-tools">
          <button class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-file-export mr-1"></i> Export
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <!-- Loading State -->
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <!-- Table -->
        <div v-else class="table-responsive">
          <table class="table table-hover text-nowrap">
            <thead>
              <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Status</th>
                <th>Role</th>
                <th>Email</th>
                <th>Reports</th>
                <th>Posts</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="user in users.data" :key="user.id">
                <td>{{ user.id }}</td>
                <td>
                  <div class="user-block">
                    <img :src="user.avatar" :alt="user.user_name" class="img-circle">
                    <span class="username">{{ user.user_name }}</span>
                  </div>
                </td>
                <td>
                  <span :class="`badge badge-${getStatusClass(user.is_active)}`">
                    {{ user.is_active }}
                  </span>
                </td>
                <td>
                  <span v-for="role in user.roles" :key="role.id" class="badge badge-info mr-1">
                    {{ role.name }}
                  </span>
                </td>
                <td>{{ user.email }}</td>
                <td><span class="badge badge-danger">{{ user.reports_count }}</span></td>
                <td><span class="badge badge-primary">{{ user.posts_count }}</span></td>
                <td>
                  <UserActionsMenu
                    :user="user"
                    @view="viewUser"
                    @edit="editUser"
                    @toggle-ban="toggleBan"
                    @delete="deleteUser"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div class="card-footer">
        <Pagination
          :current-page="users.current_page"
          :last-page="users.last_page"
          @page-change="loadUsers"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useUsers } from '@/composables/useUsers'
import { useUserFilters } from '@/composables/useUserFilters'
import UserStatsWidget from '@/components/admin/users/UserStatsWidget.vue'
import UserFilters from '@/components/admin/users/UserFilters.vue'
import UserActionsMenu from '@/components/admin/users/UserActionsMenu.vue'
import Pagination from '@/components/admin/common/Pagination.vue'

const { users, loading, fetchUsers, deleteUser, toggleBan } = useUsers()
const { filters, resetFilters } = useUserFilters()

const stats = ref([])
const roles = ref([])

onMounted(async () => {
  await loadData()
})

// Watch filters and reload data
watch(filters, () => {
  loadUsers(1)
}, { deep: true })

const loadData = async () => {
  await Promise.all([
    loadUsers(),
    loadStats(),
    loadRoles()
  ])
}

const loadUsers = async (page = 1) => {
  await fetchUsers({ ...filters.value, page })
}

const loadStats = async () => {
  const response = await fetch('/api/admin/users/stats')
  const data = await response.json()
  stats.value = data.stats
}

const loadRoles = async () => {
  const response = await fetch('/api/admin/roles')
  const data = await response.json()
  roles.value = data.roles
}

const viewUser = (user) => {
  window.location.href = `/users/${user.id}`
}

const editUser = (user) => {
  window.location.href = `/users/${user.id}/edit`
}

const getStatusClass = (status) => {
  return {
    'active': 'success',
    'banned': 'danger',
    'inactive': 'warning'
  }[status] || 'secondary'
}
</script>
```

**Phase 4: Enhanced Features (Improvements over Blade)**

1. **Real-time Updates:**
   - Use Laravel Echo for real-time user updates
   - Live status changes without refresh
   - Live user count updates

2. **Improved UX:**
   - Instant search with debouncing (300ms)
   - Optimistic UI updates for ban/unban
   - Smooth transitions and animations
   - Loading states for all async operations
   - Inline editing for quick updates

3. **Better Performance:**
   - Virtual scrolling for large user lists
   - Lazy loading of user details
   - Image lazy loading
   - Cached filter state in localStorage
   - Debounced search input

4. **Advanced Filtering:**
   - Multi-select filters (role, status)
   - Date range filters (created, last active)
   - Advanced search (by multiple fields)
   - Save filter presets
   - Export filtered results

5. **Bulk Operations:**
   - Checkbox selection for bulk actions
   - Bulk ban/unban
   - Bulk role assignment
   - Bulk delete with confirmation

6. **Enhanced Analytics:**
   - User activity charts
   - Growth trends
   - Geographic distribution map
   - Role distribution pie chart

**Phase 5: Blade Wrappers**

Create minimal Blade wrappers that mount Vue components:

1. `resources/views/admin/users.blade.php`:
```blade
@extends('adminlte::page')

@section('title', 'Users Management')

@section('content_header')
    <h1>Users Management</h1>
@stop

@section('content')
    <div id="admin-app">
        <users-list></users-list>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
```

2. `resources/views/admin/users/show.blade.php`:
```blade
@extends('adminlte::page')

@section('title', 'User Details')

@section('content_header')
    <h1>User Details</h1>
@stop

@section('content')
    <div id="admin-app">
        <user-details :user-id="{{ $userId }}"></user-details>
    </div>
@stop

@section('js')
    @vite(['resources/js/admin.js'])
@stop
```

**Phase 6: Testing & Deployment**

1. **Testing Checklist:**
   - [ ] All CRUD operations work
   - [ ] Filters apply correctly
   - [ ] Search works with debounce
   - [ ] Ban/unban toggle works
   - [ ] Pagination preserves filters
   - [ ] Image upload works
   - [ ] Google Maps loads correctly
   - [ ] Role assignment works
   - [ ] Delete confirmation works
   - [ ] Mobile responsiveness
   - [ ] Browser compatibility

2. **Deployment Steps:**
   - Run `npm run build`
   - Clear Laravel caches
   - Test all routes
   - Monitor for errors
   - Gather user feedback

---

### 2. Admin Management → Roles

#### Current Implementation Analysis

**Blade Views:**
- `resources/views/admin/roles/index.blade.php` - Main roles list with search/sorting
- `resources/views/admin/roles/show.blade.php` - Role details with permissions
- `resources/views/admin/roles/create.blade.php` - Create new role form
- `resources/views/admin/roles/edit.blade.php` - Edit role form

**Controllers:**
- `app/Http/Controllers/RolesController.php` - Role CRUD using Laratrust

**Key Features:**

1. **Roles List (index.blade.php)**
   - **Stats Widgets:** 4 info boxes showing:
     - Total Roles count
     - Total Permission Assignments count
     - Administrative Roles count (excluding 'user' role)
     - Admin Users count (users with admin role)
   - **Search & Filter Card (collapsed by default):**
     - Search input (by name or description)
     - Sort By dropdown (Name, Creation Date, Permissions Count)
     - Sort Direction (Ascending/Descending)
   - **Data Table:**
     - Columns: ID, Name, Display Name, Description, Permissions count, Actions
     - Color-coded role badges:
       - superadmin: Red (danger)
       - admin: Orange (warning)
       - others: Blue (info)
     - Truncated description (50 chars max)
   - **Action Buttons per role:**
     - View (eye icon) - Links to role details
     - Users (users icon) - Links to users with this role
     - Edit (pen icon) - Only for non-protected roles (not superadmin/admin)
     - Delete (trash icon) - Shown inline below table, protected roles cannot be deleted
   - **Permission Checks:**
     - `@can('create_role')` - Show create button
     - `@can('view_permission')` - Show manage permissions button
     - `@can('edit_role')` - Show edit button
     - `@can('delete_role')` - Show delete button
   - **Protected Roles:**
     - 'superadmin', 'admin', 'user' - Cannot be edited or deleted
   - **Pagination:** Laravel pagination

2. **Role Details (show.blade.php)**
   - **Left Sidebar:**
     - Role Info Card:
       - Color-coded role name badge
       - Display name
       - Info list: ID, Name, Display Name, Created date, Last Updated, Permissions count, Users Assigned count
       - Delete button (only if not protected and no users assigned)
     - Options Card:
       - "View Users with this Role" button
       - "Clone Role" button (modal trigger, not for superadmin/admin)
   - **Right Content:**
     - Description Card:
       - Full role description or "No description available"
     - Permissions Card:
       - Permission count badge
       - Grouped permissions by category (first part of permission name before underscore)
       - Each group shows heading and grid of permission info boxes
       - Info box shows: Icon, Display Name, Permission Name
       - 3 columns layout for permission boxes
   - **Clone Role Modal:**
     - Form with new role name and display name inputs
     - POST to `/roles/{id}/clone`
     - Validation: New name must be unique, lowercase, no spaces
   - **Protected Logic:**
     - Cannot delete if: Role is superadmin/admin/user OR has users assigned
     - Cannot clone: superadmin or admin roles

3. **Create Role (create.blade.php)**
   - **Role Info Section:**
     - Role Name* (required, unique, lowercase)
       - Input with icon prepend
       - Helper text about format
     - Display Name (optional)
       - Human-readable name for UI
     - Description (textarea)
       - Purpose and capabilities description
   - **Permissions Section:**
     - Grouped by category cards
     - Each category card has:
       - Card header with category name (capitalized)
       - "Select All" checkbox in header
       - Grid of permission checkboxes (4 columns)
       - Each checkbox shows: Display name and actual permission name
   - **JavaScript Features:**
     - "Select All" checkbox per group
     - Auto-update "Select All" when individual permissions change
     - Initialize "Select All" based on current state
     - Handles old input on validation errors
   - **Validation:**
     - name: required, string, unique in roles table
     - display_name: nullable, string
     - description: nullable, string
     - permissions: array of permission IDs

4. **Edit Role (edit.blade.php)**
   - Similar to create form
   - Cannot edit 'name' field (only display_name and description)
   - Pre-checked permissions based on current assignments
   - Protected roles check via `Helper::roleIsEditable()`
   - Redirects with error if role is not editable

**Database Relations:**
- Role belongsToMany Permissions
- Role belongsToMany Users

**Routes:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('roles', CustomRolesController::class);
    Route::post('roles/{role}/clone', [CustomRolesController::class, 'clone'])
        ->name('roles.clone');
});

// API endpoint for roles list
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::get('/roles/list', [ApiController::class, 'getRoles'])->name('roles.list');
});
```

**Laratrust Integration:**
- Uses Laratrust package for roles and permissions
- Helper methods: `roleIsEditable()`, `roleIsDeletable()`
- Config: `laratrust.models.role`, `laratrust.models.permission`
- Session flash messages: 'laratrust-success', 'laratrust-error', 'laratrust-warning'

**Business Logic:**
- Role name cannot be changed after creation (only display_name and description)
- Protected roles (superadmin, admin, user) cannot be edited or deleted
- Roles with assigned users cannot be deleted
- Clone feature creates exact copy with different name

---

#### Migration Plan: Roles Section to Vue.js

**Phase 1: API Development**

1. **Create API Controller:** `app/Http/Controllers/Admin/RolesApiController.php`
   ```php
   class RolesApiController extends Controller
   {
       // GET /api/admin/roles - List with search, sorting, pagination
       public function index(Request $request): JsonResponse

       // GET /api/admin/roles/stats - Role statistics
       public function getStats(): JsonResponse

       // GET /api/admin/roles/{id} - Role details with permissions
       public function show($id): JsonResponse

       // POST /api/admin/roles - Create new role
       public function store(Request $request): JsonResponse

       // PUT /api/admin/roles/{id} - Update role
       public function update(Request $request, $id): JsonResponse

       // DELETE /api/admin/roles/{id} - Delete role
       public function destroy($id): JsonResponse

       // POST /api/admin/roles/{id}/clone - Clone role
       public function clone(Request $request, $id): JsonResponse

       // GET /api/admin/roles/{id}/users - Users with this role
       public function getUsersWithRole($id): JsonResponse

       // GET /api/admin/permissions/grouped - Permissions grouped by category
       public function getGroupedPermissions(): JsonResponse
   }
   ```

**Phase 2: Vue Components Development**

1. **Main Components:**
   - `resources/js/views/admin/roles/RolesList.vue` - Main roles list
   - `resources/js/views/admin/roles/RoleDetails.vue` - Role details view
   - `resources/js/views/admin/roles/RoleForm.vue` - Create/Edit role form

2. **Reusable Sub-Components:**
   - `resources/js/components/admin/roles/RoleStatsWidget.vue` - Stats box
   - `resources/js/components/admin/roles/RoleCard.vue` - Role info card
   - `resources/js/components/admin/roles/PermissionGroup.vue` - Permission category card
   - `resources/js/components/admin/roles/PermissionCheckbox.vue` - Single permission checkbox
   - `resources/js/components/admin/roles/CloneRoleModal.vue` - Clone modal
   - `resources/js/components/admin/roles/RoleSearchFilters.vue` - Search and filters

3. **Composables:**
   - `resources/js/composables/useRoles.js` - Role CRUD operations
   - `resources/js/composables/usePermissions.js` - Permission operations
   - `resources/js/composables/useRoleFilters.js` - Filter/search/sort logic

**Phase 3: Enhanced Features**

1. **Improved Permission Management:**
   - Visual grouping with expand/collapse
   - Permission search within groups
   - Quick templates (e.g., "Content Editor", "Moderator")
   - Comparison view (compare permissions between roles)
   - Dependency checking (warn if permissions require others)

2. **Better UX:**
   - Drag-and-drop permission assignment
   - Bulk permission selection
   - Real-time validation
   - Permission impact preview ("X users will be affected")
   - Role hierarchy visualization

3. **Advanced Features:**
   - Role usage analytics
   - Permission usage heatmap
   - Audit trail for role changes
   - Role templates library
   - Export/import roles configuration

**Implementation Example - RolesList.vue:**
```vue
<template>
  <div class="roles-management">
    <!-- Stats Widgets -->
    <div class="row">
      <div class="col-lg-3 col-6" v-for="stat in stats" :key="stat.label">
        <RoleStatsWidget :stat="stat" />
      </div>
    </div>

    <!-- Search & Filters -->
    <RoleSearchFilters
      v-model:search="filters.search"
      v-model:sortBy="filters.sortBy"
      v-model:sortDir="filters.sortDir"
      @submit="loadRoles"
    />

    <!-- Roles Table -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Roles List</h3>
        <div class="card-tools">
          <button v-if="can('create_role')" @click="createRole" class="btn btn-success btn-sm">
            <i class="fas fa-plus-circle mr-1"></i> Create New Role
          </button>
        </div>
      </div>

      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Display Name</th>
              <th>Description</th>
              <th>Permissions</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="role in roles.data" :key="role.id">
              <td>{{ role.id }}</td>
              <td>
                <span :class="`badge badge-${getRoleBadgeClass(role.name)}`">
                  {{ role.name }}
                </span>
              </td>
              <td>{{ role.display_name || role.name }}</td>
              <td>{{ truncate(role.description, 50) }}</td>
              <td>
                <span class="badge badge-primary">{{ role.permissions_count }}</span>
              </td>
              <td>
                <div class="btn-group">
                  <button @click="viewRole(role)" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button @click="viewUsers(role)" class="btn btn-sm btn-primary">
                    <i class="fas fa-users"></i> Users
                  </button>
                  <button
                    v-if="can('edit_role') && isEditable(role)"
                    @click="editRole(role)"
                    class="btn btn-sm btn-warning"
                  >
                    <i class="fas fa-edit"></i> Edit
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        <Pagination
          :current-page="roles.current_page"
          :last-page="roles.last_page"
          @page-change="loadRoles"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoles } from '@/composables/useRoles'
import { useRoleFilters } from '@/composables/useRoleFilters'
import { usePermissions } from '@/composables/usePermissions'

const { roles, loading, fetchRoles, deleteRole } = useRoles()
const { filters, resetFilters } = useRoleFilters()
const { can } = usePermissions()

const stats = ref([])

onMounted(async () => {
  await loadData()
})

watch(filters, () => {
  loadRoles(1)
}, { deep: true })

const loadData = async () => {
  await Promise.all([
    loadRoles(),
    loadStats()
  ])
}

const loadRoles = async (page = 1) => {
  await fetchRoles({ ...filters.value, page })
}

const loadStats = async () => {
  const response = await fetch('/api/admin/roles/stats')
  const data = await response.json()
  stats.value = data.stats
}

const getRoleBadgeClass = (name) => {
  return {
    'superadmin': 'danger',
    'admin': 'warning'
  }[name] || 'info'
}

const isEditable = (role) => {
  return !['superadmin', 'admin'].includes(role.name)
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const viewRole = (role) => {
  window.location.href = `/roles/${role.id}`
}

const editRole = (role) => {
  window.location.href = `/roles/${role.id}/edit`
}

const viewUsers = (role) => {
  window.location.href = `/role-assignments/users-with-role/${role.id}`
}

const createRole = () => {
  window.location.href = '/roles/create'
}
</script>
```

---

### 3. Admin Management → Permissions

#### Current Implementation Analysis

**Blade Views:**
- `resources/views/admin/permissions/index.blade.php` - Main permissions list
- `resources/views/admin/permissions/show.blade.php` - Permission details
- `resources/views/admin/permissions/create.blade.php` - Create permission form
- `resources/views/admin/permissions/edit.blade.php` - Edit permission form

**Controllers:**
- `app/Http/Controllers/PermissionsController.php` - Permission CRUD using Laratrust

**Key Features:**

1. **Permissions List (index.blade.php)**
   - **Stats Widgets:** 4 info boxes showing:
     - Total Permissions count
     - Permission Categories count (grouped by prefix)
     - Role Assignments count (permission-role pivot table)
     - Direct User Assignments count (permission-user pivot table)
   - **Search & Filter Card (collapsed by default):**
     - Search input (by name or description)
     - Category filter dropdown (auto-populated from grouped permissions)
     - Results per page selector (15/25/50/100)
   - **Data Table:**
     - Columns: ID, Name, Display Name, Description, Roles count, Actions
     - Permission name shown as blue badge
     - Truncated description (50 chars max)
     - Role count from pivot table query
   - **Action Buttons per permission:**
     - View (eye icon) - Links to permission details
     - Edit (pen icon) - Edit permission (with `@can('edit_permission')`)
     - Delete (trash icon) - Only shown if:
       - Not a system permission (view_role, create_role, edit_role, delete_role, view_permission, create_permission, edit_permission, delete_permission)
       - Not assigned to any roles (roleCount == 0)
       - User has `delete_permission` permission
   - **Generate CRUD Permissions Modal:**
     - "Generate CRUD Permissions" button in header
     - Modal for generating standard permissions (view, create, edit, delete)
     - Module name input (lowercase, underscores)
     - Real-time preview of permissions to be generated
     - Auto-updates preview as user types module name
   - **Protected Permissions:**
     - System permissions for role/permission management cannot be deleted
   - **Pagination:** Laravel pagination with debug info

2. **Permission Details (show.blade.php)**
   - **Left Sidebar:**
     - Permission Info Card:
       - Permission name badge (blue)
       - Display name below
       - Info list: ID, Name, Display Name, Created date, Last Updated, Assigned to Roles count
       - Delete button (only if not system permission and not assigned to roles)
       - Warning text explaining why deletion is disabled
   - **Right Content:**
     - Description Card:
       - Full permission description or "No description available"
     - Roles Using Permission Card:
       - Role count badge in header
       - Table of roles with this permission:
         - Columns: ID, Role Name (colored badge), Display Name, Actions
         - "View Role" button for each role
       - Empty state if no roles use the permission
     - Permission Usage Guide Card:
       - Code examples for using permission in:
         - Blade templates (`@can` directive)
         - Controllers (`auth()->user()->can()`, middleware)
         - Routes (middleware groups)
       - Actual permission name interpolated in code examples
   - **System Permission Protection:**
     - Cannot delete: view_role, create_role, edit_role, delete_role, view_permission, create_permission, edit_permission, delete_permission
     - Shows different disabled message for system vs. in-use permissions

3. **Create Permission (create.blade.php)**
   - **Permission Info Fields:**
     - Permission Name* (required, unique)
       - Input with key icon
       - Helper text about format (lowercase, underscores)
       - Validation feedback
     - Display Name (optional)
       - Input with eye icon
       - Auto-generated from permission name
       - Helper text about UI display
     - Description (textarea)
       - 3 rows
   - **Naming Convention Guide:**
     - Alert box with best practices
     - Pattern examples:
       - view_[resource] - viewing/listing
       - create_[resource] - creating new
       - edit_[resource] - updating existing
       - delete_[resource] - deleting
       - manage_[resource] - full control
     - Concrete examples provided
   - **JavaScript Features:**
     - Auto-generate display name from permission name
     - Capitalizes each word (view_users → View Users)
     - Only auto-fills if display name is empty
   - **Validation:**
     - name: required, string, unique
     - display_name: nullable, string
     - description: nullable, string

4. **Generate CRUD Permissions (Modal)**
   - **Form Fields:**
     - Module Name input (required)
     - Placeholder and helper text about format
   - **Preview Section:**
     - Alert box showing permissions to be created
     - Dynamic list updating as user types:
       - view_[module]
       - create_[module]
       - edit_[module]
       - delete_[module]
   - **JavaScript:**
     - Real-time preview update on input
     - Resets to placeholder if input cleared
   - **Route:** POST to `/permissions/generate`

**Database Relations:**
- Permission belongsToMany Roles (permission_role pivot)
- Permission belongsToMany Users (permission_user pivot - for direct assignments)

**Routes:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::resource('permissions', CustomPermissionsController::class);
    Route::post('permissions/generate', [CustomPermissionsController::class, 'generateForModule'])
        ->name('permissions.generate');
});
```

**Laratrust Integration:**
- Config: `laratrust.models.permission`
- Pivot tables: `permission_role`, `permission_user`
- Session flash: 'laratrust-success'

**Business Logic:**
- Permission name cannot be changed after creation (only display_name and description)
- System permissions (role/permission CRUD) cannot be deleted
- Permissions assigned to roles cannot be deleted
- CRUD permission generator creates 4 permissions at once
- Permissions grouped by category (prefix before underscore)

---

#### Migration Plan: Permissions Section to Vue.js

**Phase 1: API Development**

1. **Create API Controller:** `app/Http/Controllers/Admin/PermissionsApiController.php`
   ```php
   class PermissionsApiController extends Controller
   {
       // GET /api/admin/permissions - List with search, category filter, pagination
       public function index(Request $request): JsonResponse

       // GET /api/admin/permissions/stats - Permission statistics
       public function getStats(): JsonResponse

       // GET /api/admin/permissions/{id} - Permission details with roles
       public function show($id): JsonResponse

       // POST /api/admin/permissions - Create new permission
       public function store(Request $request): JsonResponse

       // PUT /api/admin/permissions/{id} - Update permission
       public function update(Request $request, $id): JsonResponse

       // DELETE /api/admin/permissions/{id} - Delete permission
       public function destroy($id): JsonResponse

       // POST /api/admin/permissions/generate - Generate CRUD permissions
       public function generateForModule(Request $request): JsonResponse

       // GET /api/admin/permissions/grouped - Grouped by category
       public function getGrouped(): JsonResponse

       // GET /api/admin/permissions/{id}/roles - Roles using permission
       public function getRolesUsingPermission($id): JsonResponse
   }
   ```

**Phase 2: Vue Components Development**

1. **Main Components:**
   - `resources/js/views/admin/permissions/PermissionsList.vue` - Main list
   - `resources/js/views/admin/permissions/PermissionDetails.vue` - Details view
   - `resources/js/views/admin/permissions/PermissionForm.vue` - Create/Edit form

2. **Reusable Sub-Components:**
   - `resources/js/components/admin/permissions/PermissionStatsWidget.vue` - Stats box
   - `resources/js/components/admin/permissions/PermissionCard.vue` - Permission info card
   - `resources/js/components/admin/permissions/GenerateCrudModal.vue` - Generate CRUD modal
   - `resources/js/components/admin/permissions/PermissionSearchFilters.vue` - Search/filter panel
   - `resources/js/components/admin/permissions/UsageGuide.vue` - Code examples component
   - `resources/js/components/admin/permissions/CategoryBadge.vue` - Category badge

3. **Composables:**
   - `resources/js/composables/usePermissions.js` - Permission CRUD
   - `resources/js/composables/usePermissionFilters.js` - Filter/search logic
   - `resources/js/composables/usePermissionGenerator.js` - CRUD generation logic

**Phase 3: Enhanced Features**

1. **Improved Permission Management:**
   - Batch permission creation (multiple modules at once)
   - Custom permission templates (beyond CRUD)
   - Permission dependency graph (visualize relationships)
   - Bulk delete for unused permissions
   - Import/export permissions as JSON/CSV

2. **Better UX:**
   - Permission search with highlighting
   - Category-based filtering with chips
   - Quick actions (duplicate, assign to role)
   - Real-time usage statistics
   - Permission impact analysis before deletion

3. **Advanced Features:**
   - Permission usage heatmap (which permissions are most used)
   - Unused permission detection
   - Permission audit log (who created/modified/deleted)
   - Smart suggestions based on existing patterns
   - Bulk operations with undo functionality

**Implementation Example - PermissionsList.vue:**
```vue
<template>
  <div class="permissions-management">
    <!-- Stats Widgets -->
    <div class="row">
      <div class="col-lg-3 col-6" v-for="stat in stats" :key="stat.label">
        <PermissionStatsWidget :stat="stat" />
      </div>
    </div>

    <!-- Action Bar -->
    <div class="mb-3">
      <button v-if="can('create_permission')" @click="createPermission" class="btn btn-success">
        <i class="fas fa-plus-circle mr-1"></i> Create Permission
      </button>
      <button @click="showGenerateModal" class="btn btn-primary ml-2">
        <i class="fas fa-magic mr-1"></i> Generate CRUD Permissions
      </button>
    </div>

    <!-- Search & Filters -->
    <PermissionSearchFilters
      v-model:search="filters.search"
      v-model:category="filters.category"
      v-model:perPage="filters.perPage"
      :categories="categories"
      @submit="loadPermissions"
    />

    <!-- Permissions Table -->
    <div class="card card-outline card-primary">
      <div class="card-header">
        <h3 class="card-title">Permissions List</h3>
      </div>

      <div class="card-body p-0">
        <div v-if="loading" class="text-center py-5">
          <div class="spinner-border text-primary"></div>
        </div>

        <table v-else class="table table-hover">
          <thead>
            <tr>
              <th>ID</th>
              <th>Name</th>
              <th>Display Name</th>
              <th>Description</th>
              <th>Roles</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="permission in permissions.data" :key="permission.id">
              <td>{{ permission.id }}</td>
              <td>
                <span class="badge badge-info">{{ permission.name }}</span>
              </td>
              <td>{{ permission.display_name || permission.name }}</td>
              <td>{{ truncate(permission.description, 50) }}</td>
              <td>
                <span class="badge badge-primary">{{ permission.roles_count }}</span>
              </td>
              <td>
                <div class="btn-group">
                  <button @click="viewPermission(permission)" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> View
                  </button>
                  <button
                    v-if="can('edit_permission')"
                    @click="editPermission(permission)"
                    class="btn btn-sm btn-warning"
                  >
                    <i class="fas fa-edit"></i> Edit
                  </button>
                  <button
                    v-if="canDelete(permission)"
                    @click="deletePermission(permission)"
                    class="btn btn-sm btn-danger"
                  >
                    <i class="fas fa-trash"></i> Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="card-footer">
        <Pagination
          :current-page="permissions.current_page"
          :last-page="permissions.last_page"
          @page-change="loadPermissions"
        />
      </div>
    </div>

    <!-- Generate CRUD Modal -->
    <GenerateCrudModal
      v-if="showGenerateCrudModal"
      @close="showGenerateCrudModal = false"
      @generated="onPermissionsGenerated"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { usePermissions } from '@/composables/usePermissions'
import { usePermissionFilters } from '@/composables/usePermissionFilters'

const { permissions, loading, fetchPermissions, deletePermission: deletePermissionApi, can } = usePermissions()
const { filters, resetFilters } = usePermissionFilters()

const stats = ref([])
const categories = ref([])
const showGenerateCrudModal = ref(false)

const systemPermissions = [
  'view_role', 'create_role', 'edit_role', 'delete_role',
  'view_permission', 'create_permission', 'edit_permission', 'delete_permission'
]

onMounted(async () => {
  await loadData()
})

watch(filters, () => {
  loadPermissions(1)
}, { deep: true })

const loadData = async () => {
  await Promise.all([
    loadPermissions(),
    loadStats(),
    loadCategories()
  ])
}

const loadPermissions = async (page = 1) => {
  await fetchPermissions({ ...filters.value, page })
}

const loadStats = async () => {
  const response = await fetch('/api/admin/permissions/stats')
  const data = await response.json()
  stats.value = data.stats
}

const loadCategories = async () => {
  const response = await fetch('/api/admin/permissions/grouped')
  const data = await response.json()
  categories.value = Object.keys(data.grouped)
}

const canDelete = (permission) => {
  return can('delete_permission') &&
         !systemPermissions.includes(permission.name) &&
         permission.roles_count === 0
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

const viewPermission = (permission) => {
  window.location.href = `/permissions/${permission.id}`
}

const editPermission = (permission) => {
  window.location.href = `/permissions/${permission.id}/edit`
}

const createPermission = () => {
  window.location.href = '/permissions/create'
}

const showGenerateModal = () => {
  showGenerateCrudModal.value = true
}

const onPermissionsGenerated = () => {
  showGenerateCrudModal.value = false
  loadPermissions()
}
</script>
```

---

### 4. Admin Management → Role Assignments

#### Current Implementation Analysis

**Blade Views:**
- `resources/views/admin/role-assignments/index.blade.php` - User list with role assignments
- `resources/views/admin/role-assignments/edit.blade.php` - Assign roles/permissions to user
- `resources/views/admin/role-assignments/users-with-role.blade.php` - List users with specific role

**Controllers:**
- `app/Http/Controllers/UserRoleAssignmentController.php` - Role assignment operations

**Key Features:**

1. **User Role Assignments List (index.blade.php)**
   - **Compact Filter Section:**
     - Search input (name or email) with submit button
     - Role filter dropdown (all roles from database)
     - Sort by (ID, Name, Email, Roles Count)
     - Sort direction (Asc/Desc)
     - Reset button
   - **Auto-submit Filters:** Dropdowns auto-submit form on change
   - **Data Table:**
     - Columns: ID, User (avatar + name), Email, Roles (badges), Direct Permissions count, Actions
     - User avatars with fallback to default
     - Color-coded role badges (superadmin=red, admin=orange, others=blue)
     - Direct permissions count badge
   - **Actions:**
     - "Assign Roles" button (edit icon)
     - "View User" button (eye icon)

2. **Edit User Assignments (edit.blade.php)**
   - **Left Sidebar - User Profile Card:**
     - Large circular avatar
     - Name and username
     - Info list: ID, Email, Status (colored badge), Joined date, Roles count, Permissions count
     - "Edit User" button
   - **Right Content - Tabbed Interface:**
     - **Roles Tab:**
       - Info alert explaining role assignments
       - Grid of role cards (4 columns on XL, 3 on LG, 2 on MD)
       - Each role card: Switch checkbox, Display name, Description
       - Selected roles highlighted with blue border
       - Switch style customized
     - **Permissions Tab:**
       - Warning alert about direct permissions overriding role permissions
       - Accordion groups by permission category
       - Each accordion card:
         - Header: Category name, count badge, "Select All" checkbox
         - Collapsible body: Grid of permission switches
         - First accordion expanded by default
     - **"Reset to Role Permissions" Button:**
       - AJAX call to get default permissions for selected roles
       - Unchecks all current permissions, checks role-based ones
       - Updates "Select All" checkboxes
       - Fallback to browser alert (no SweetAlert dependency)
   - **JavaScript Features:**
     - Role card highlighting on selection
     - Permission "Select All" per category
     - Auto-update "Select All" when individual permissions change
     - AJAX reset to role permissions
     - Error handling with fallbacks

3. **Users with Role (users-with-role.blade.php)**
   - **Role Info Card:**
     - Role name, display name, description
     - Permissions count badge
   - **Users Table:**
     - Columns: ID, User (avatar + name), Email, Status, All Roles, Actions
     - Status badge (active=green, banned=red, inactive=orange)
     - Shows all roles user has (not just the filtered role)
     - Color-coded role badges
   - **Actions:**
     - View User
     - Edit Roles (with permission check)
     - Edit User (with permission check)
   - **Empty State:** Alert when no users have the role

**Database Relations:**
- User belongsToMany Roles
- User belongsToMany Permissions (direct assignments)

**Routes:**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/role-assignments', [UserRoleAssignmentController::class, 'index'])
        ->name('role-assignments.index');
    Route::get('/role-assignments/{user}/edit', [UserRoleAssignmentController::class, 'edit'])
        ->name('role-assignments.edit');
    Route::put('/role-assignments/{user}', [UserRoleAssignmentController::class, 'update'])
        ->name('role-assignments.update');
    Route::get('/role-assignments/users-with-role/{role}', [UserRoleAssignmentController::class, 'usersWithRole'])
        ->name('role-assignments.users-with-role');
    Route::post('/role-assignments/default-permissions', [UserRoleAssignmentController::class, 'getDefaultPermissions'])
        ->name('role-assignments.default-permissions');
});
```

**Business Logic:**
- Direct permissions override role-based permissions
- "Reset to Role Permissions" clears direct permissions and loads role defaults
- Multiple roles can be assigned to one user
- Permission assignment is optional (can use only roles)

---

#### Migration Plan: Role Assignments Section to Vue.js

**Phase 1: API Development**
```php
class RoleAssignmentsApiController extends Controller
{
    // GET /api/admin/role-assignments - User list with role filters
    public function index(Request $request): JsonResponse

    // GET /api/admin/role-assignments/{userId} - User with roles/permissions
    public function show($userId): JsonResponse

    // PUT /api/admin/role-assignments/{userId} - Update user roles/permissions
    public function update(Request $request, $userId): JsonResponse

    // GET /api/admin/role-assignments/role/{roleId}/users - Users with role
    public function getUsersWithRole($roleId): JsonResponse

    // POST /api/admin/role-assignments/default-permissions - Get role permissions
    public function getDefaultPermissions(Request $request): JsonResponse
}
```

**Phase 2: Vue Components**
- `RoleAssignmentsList.vue` - Main user list with filters
- `RoleAssignmentsEdit.vue` - Assign roles/permissions
- `UsersWithRole.vue` - Users by role view
- Sub-components: `RoleCard.vue`, `PermissionAccordion.vue`, `UserProfileCard.vue`

**Phase 3: Enhanced Features**
- Bulk role assignment (select multiple users)
- Role comparison (visual diff between roles)
- Permission inheritance viewer
- Audit trail for role changes
- Smart suggestions based on user activity

---

### 5. Categories Management

#### Current Implementation
**Views:** index, create, edit, show (4 Blade files)
**Key Features:**
- Table with: ID, Image thumbnail, Multilingual name (AR/EN), Status badge, Subcategories count, Posts count, Actions
- Image upload with preview
- Multilingual fields (name in multiple languages)
- Parent-child relationship (Categories → Subcategories)
- Status toggle (Active/Suspended)
- Photo management (morphMany relationship)

**Migration Notes:**
- Image upload component with drag-drop
- Multilingual input fields
- Category tree visualization
- Inline editing for quick updates

---

### 6. Service Posts Management

#### Current Implementation
**Views:** index, create, edit, show, + specialized views (car_index, job_index, real_state_index, phone_index, etc.) - 15+ Blade files
**Key Features:**
- **Advanced Filters:**
  - Category dropdown (with AJAX subcategory loading)
  - Subcategory dropdown (dependent on category)
  - Status filter (Published, Archive, Draft, Rejected)
  - Type filter (Offer/Request - عرض/طلب)
  - Search input
  - Auto-submit on filter change
- **Bulk Operations:**
  - Select All checkbox
  - Delete Selected button (enabled when items selected)
  - Bulk status updates
- **Table Columns:** ID, Title, Category/Subcategory, User, Status badge, Type, Created date, Views count, Actions
- **Specialized Views by Category:**
  - Cars: Additional fields (brand, model, year)
  - Jobs: Job-specific fields
  - Real Estate: Property-specific fields
  - Phones: Device specifications
- **Rich Content:**
  - Image galleries (multiple photos)
  - Video support
  - Location with Google Maps
  - Custom fields per category
  - Tags system

**Migration Notes:**
- Dynamic form fields based on category
- Image gallery with sortable drag-drop
- Location picker component
- Rich text editor for descriptions
- Category-specific validations

---

### 7. Location Management

#### Current Implementation - Countries
**Views:** index, create, edit, show (4 Blade files)
**Key Features:**
- Table: ID, Flag image, Multilingual name, Country code, Currency, Status, Cities count, Actions
- Flag upload
- Currency settings
- Cities relationship (one-to-many)

#### Current Implementation - Cities
**Views:** index, create, edit, show (4 Blade files)
**Key Features:**
- Table: ID, City name, Country (with flag), Status, Users count, Posts count, Actions
- Country dropdown (select2)
- Dependent on country
- User and post counts

**Migration Notes:**
- Country-city cascading selects
- Flag/image upload
- Location hierarchy visualization
- Bulk import from CSV

---

### 8. Marketing

#### Current Implementation - Send Notifications
**View:** `resources/views/admin/notifications/marketing/send.blade.php`
**Key Features:**
- **Target Audience Selection:**
  - All Users
  - Specific roles
  - Users by city/country
  - Custom user selection
- **Notification Types:**
  - Push notification
  - Email
  - SMS (if configured)
- **Content:**
  - Title (multilingual)
  - Message (multilingual)
  - Image upload
  - Action URL/deep link
  - Schedule send time
- **Preview:** Live preview of notification

#### Current Implementation - Notification History
**View:** `resources/views/admin/notifications/marketing/history.blade.php`
**Key Features:**
- Table: ID, Title, Target audience, Type, Status, Sent count, Opened count, Click rate, Sent at
- Filter by status, type, date range
- Analytics: Delivery rate, open rate, click-through rate
- Resend option for failed notifications

**Migration Notes:**
- Rich notification composer
- Audience builder with visual feedback
- Real-time delivery tracking
- A/B testing capabilities
- Template library

---

### 9. Additional Sections Summary

**Orders Management:**
- Order list with filters (status, payment, user, date range)
- Order details with timeline
- Status updates
- Invoice generation
- Payment tracking

**Transactions Management:**
- Transaction list with filters
- Payment method breakdown
- Revenue analytics
- Refund management
- Export to Excel/PDF

**Statistics Dashboard:**
- Overview widgets
- Revenue charts
- User growth trends
- Category performance
- Geographic distribution

**Reports Management:**
- User reports list
- Content moderation
- Report categories
- Action taken tracking
- Reporter and reported details

**Points Overview:**
- Points transactions
- User points balance
- Points packages
- Analytics and trends
- Point adjustment tools

**Badge System:**
- Badge types CRUD
- User badges assignment
- Achievement tracking
- Badge requirements
- Progress visualization

**Financial Sections:**
- Budget planning
- Expense tracking
- Revenue reports
- Profit/loss statements
- Payment approvals

**System Management:**
- API management
- System health monitoring
- Database management
- Backup/restore
- Logs viewer

---

## Complete Migration Priority

### Phase 1: Core Admin Functions (High Priority)
1. ✅ Users Management
2. ✅ Roles & Permissions
3. ✅ Role Assignments
4. Categories & Service Posts
5. Location Management (Countries/Cities)

### Phase 2: Content & Operations (Medium Priority)
6. Orders Management
7. Transactions Management
8. Reports Management
9. Marketing (Notifications)

### Phase 3: Analytics & Advanced (Lower Priority)
10. Statistics Dashboard
11. Points System
12. Badge System
13. Financial Sections
14. System Management

---

## Global Components Needed

**Reusable Across All Sections:**
1. **DataTable Component** - Sortable, filterable, paginated tables
2. **FilterPanel Component** - Search, filters, date ranges
3. **ImageUpload Component** - Drag-drop, preview, crop
4. **MultilingualInput Component** - Tabs for AR/EN inputs
5. **StatusBadge Component** - Colored status indicators
6. **ActionButtons Component** - View/Edit/Delete grouped buttons
7. **StatsWidget Component** - Info boxes with icons
8. **ConfirmDialog Component** - Delete confirmations
9. **ExportButton Component** - Export to Excel/PDF/CSV
10. **BulkActions Component** - Select all, bulk operations
11. **DateRangePicker Component** - Date filters
12. **LocationPicker Component** - Google Maps integration
13. **RichTextEditor Component** - Content editing
14. **ChartComponents** - Pie, Line, Bar, Mixed charts (already created)

---

## API Structure Template

For each section, follow this pattern:

```php
// API Controller
class {Section}ApiController extends Controller
{
    // GET /api/admin/{section} - List with filters
    public function index(Request $request): JsonResponse

    // GET /api/admin/{section}/stats - Statistics
    public function getStats(): JsonResponse

    // GET /api/admin/{section}/{id} - Show details
    public function show($id): JsonResponse

    // POST /api/admin/{section} - Create
    public function store(Request $request): JsonResponse

    // PUT /api/admin/{section}/{id} - Update
    public function update(Request $request, $id): JsonResponse

    // DELETE /api/admin/{section}/{id} - Delete
    public function destroy($id): JsonResponse

    // POST /api/admin/{section}/bulk - Bulk operations
    public function bulkAction(Request $request): JsonResponse
}
```

---

## Testing Checklist Template

For each migrated section:
- [ ] All CRUD operations work
- [ ] Filters apply correctly
- [ ] Search works with debounce
- [ ] Sorting works on all columns
- [ ] Pagination preserves filters
- [ ] Image upload works (if applicable)
- [ ] Multilingual inputs save correctly (if applicable)
- [ ] Bulk operations work (if applicable)
- [ ] Export functions work (if applicable)
- [ ] Delete confirmation works
- [ ] Mobile responsiveness
- [ ] Browser compatibility
- [ ] Loading states display
- [ ] Error messages show correctly
- [ ] Success messages show correctly

---

---

## Advanced Card-Based Design Patterns

### Modern Design Philosophy

All migrated sections now use an **advanced card-based design** instead of traditional tables. This provides:
- Better visual hierarchy
- Improved user experience
- Modern, professional appearance
- Flexible layout options (Grid/List views)
- Enhanced mobile responsiveness

### Key Design Features

1. **Dual View Modes:**
   - **Grid View:** Card-based layout with visual emphasis
   - **List View:** Horizontal card layout for dense information

2. **Uniform Card Sizing:**
   - All cards have consistent heights using flexbox
   - `min-height` ensures uniformity across varying content
   - Spacer divs push footer content to bottom

3. **Interactive Elements:**
   - Dropdown context menus per card
   - Hover animations and transitions
   - Click-outside-to-close functionality
   - Pulsing status indicators

4. **Advanced Filtering:**
   - Search with clear button and debouncing (300ms)
   - Category/role filters with clean dropdowns
   - Sort direction toggle buttons
   - Filter pill buttons for quick selection

5. **Professional Animations:**
   - Card hover effects (translateY on hover)
   - Smooth transitions (cubic-bezier easing)
   - Loading spinners for async operations
   - Modal fade-in animations

### Design Pattern Implementation

#### 1. Users Management (Advanced Card Design)

**File:** `resources/js/views/admin/UsersList.vue`

**Features:**
- Grid/List view toggle
- Advanced card layout with user avatars
- Dropdown context menus
- Pulsing status indicators (active/banned)
- Filter pill buttons (All/Active/Banned)
- Uniform card heights (420px minimum)
- Modern search with clear button

**CSS Pattern:**
```css
.user-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 420px;
}

.user-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.card-body-custom {
  padding: 1.5rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.spacer {
  flex: 1;
}
```

**API Response Format:**
```json
{
  "users": {
    "data": [
      {
        "id": 1,
        "user_name": "john_doe",
        "email": "john@example.com",
        "phones": "+1234567890",
        "is_active": "active",
        "avatar_url": "https://...",
        "roles": ["Admin", "User"],
        "reports_count": 0,
        "service_posts_count": 5
      }
    ],
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

#### 2. Roles Management (Advanced Card Design)

**File:** `resources/js/views/admin/RolesList.vue`

**Features:**
- Large role icons (crown for superadmin, shield for admin, user for others)
- Color-coded card borders by role type
- Protected role badges (golden gradient)
- Grid/List view toggle
- Sort direction toggle buttons
- Uniform card heights (400px minimum)
- System protection indicators

**CSS Pattern:**
```css
.role-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  min-height: 400px;
}

.role-card.superadmin {
  border: 2px solid #dc3545;
}

.role-card.admin {
  border: 2px solid #ffc107;
}

.role-icon {
  width: 80px;
  height: 80px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2rem;
  background: linear-gradient(135deg, var(--role-color-start) 0%, var(--role-color-end) 100%);
  color: white;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}
```

#### 3. Permissions Management (Advanced Card Design)

**File:** `resources/js/views/admin/PermissionsList.vue`

**Features:**
- Section header with title and subtitle
- Grid/List view toggle
- Large permission icons color-coded by category:
  - View = Info (blue)
  - Create = Success (green)
  - Edit = Warning (yellow)
  - Delete = Danger (red)
- System permission badges (golden gradient)
- Dropdown context menus
- Sort direction toggle button
- Category filtering
- Generate CRUD modal
- Uniform card heights (400px minimum)

**CSS Custom Properties:**
```css
.stat-card-advanced.stat-info {
  --stat-color-start: #17a2b8;
  --stat-color-end: #138496;
}

.stat-card-advanced.stat-success {
  --stat-color-start: #28a745;
  --stat-color-end: #1e7e34;
}

.stat-icon {
  background: linear-gradient(135deg, var(--stat-color-start) 0%, var(--stat-color-end) 100%);
}
```

### Reusable Component Patterns

#### Advanced Pagination

**Usage:**
```vue
<div class="pagination-advanced">
  <button class="page-btn" :disabled="currentPage === 1" @click="prevPage">
    <i class="fas fa-chevron-left"></i>
    Previous
  </button>

  <div class="page-numbers">
    <button
      v-for="page in visiblePages"
      :key="page"
      class="page-number"
      :class="{ active: page === currentPage }"
      @click="goToPage(page)"
    >
      {{ page }}
    </button>
  </div>

  <button class="page-btn" :disabled="currentPage === lastPage" @click="nextPage">
    Next
    <i class="fas fa-chevron-right"></i>
  </button>
</div>
```

**Visible Pages Logic:**
```javascript
const visiblePages = computed(() => {
  const pages = []
  const current = currentPage.value
  const last = lastPage.value

  let start = Math.max(1, current - 2)
  let end = Math.min(last, current + 2)

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }

  return pages
})
```

#### Dropdown Context Menu

**Template:**
```vue
<div class="card-menu">
  <button class="menu-btn" @click="toggleMenu(item.id)">
    <i class="fas fa-ellipsis-v"></i>
  </button>
  <div v-if="activeMenu === item.id" class="dropdown-menu">
    <a :href="`/items/${item.id}`" class="menu-item">
      <i class="fas fa-eye"></i>
      View Details
    </a>
    <a :href="`/items/${item.id}/edit`" class="menu-item">
      <i class="fas fa-edit"></i>
      Edit
    </a>
    <button @click="handleDelete(item)" class="menu-item danger">
      <i class="fas fa-trash"></i>
      Delete
    </button>
  </div>
</div>
```

**JavaScript:**
```javascript
const activeMenu = ref(null)

const toggleMenu = (itemId) => {
  activeMenu.value = activeMenu.value === itemId ? null : itemId
}

const closeMenus = () => {
  activeMenu.value = null
}

onMounted(() => {
  document.addEventListener('click', closeMenus)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeMenus)
})
```

#### Search with Debouncing

**Template:**
```vue
<div class="search-box">
  <i class="fas fa-search search-icon"></i>
  <input
    type="text"
    v-model="filters.search"
    class="search-input"
    placeholder="Search..."
  >
  <span v-if="filters.search" class="clear-search" @click="filters.search = ''">
    <i class="fas fa-times"></i>
  </span>
</div>
```

**JavaScript:**
```javascript
let searchTimeout = null
watch(() => filters.search, () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadData(1), 300)
})
```

### Color Coding Standards

**Status Indicators:**
- Active: Green (#28a745)
- Banned/Inactive: Red (#dc3545)
- Pending: Yellow (#ffc107)
- Default: Blue (#007bff)

**Role Types:**
- Superadmin: Red (#dc3545)
- Admin: Orange/Yellow (#ffc107)
- User: Blue (#007bff)
- Others: Info (#17a2b8)

**Permission Categories:**
- View: Info (#17a2b8)
- Create: Success (#28a745)
- Edit: Warning (#ffc107)
- Delete: Danger (#dc3545)

### Gradient Backgrounds

**Standard Gradients:**
```css
/* Primary Gradient */
background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);

/* Success Gradient */
background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);

/* Danger Gradient */
background: linear-gradient(135deg, #dc3545 0%, #bd2130 100%);

/* Warning Gradient */
background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);

/* Info Gradient */
background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
```

### Responsive Design Breakpoints

```css
/* Mobile */
@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .permissions-grid,
  .users-grid,
  .roles-grid {
    grid-template-columns: 1fr;
  }

  .list-item-info,
  .list-item-display {
    flex: 1 1 100%;
  }
}
```

### Animation Standards

**Card Hover:**
```css
transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);

.card:hover {
  transform: translateY(-8px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}
```

**Button Hover:**
```css
transition: all 0.2s ease;

.action-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}
```

**Pulsing Indicator:**
```css
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.status-indicator.active {
  background: #28a745;
  animation: pulse 2s infinite;
}
```

**Modal Fade-in:**
```css
@keyframes modalFadeIn {
  from {
    opacity: 0;
    transform: scale(0.9);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

.modal-dialog-advanced {
  animation: modalFadeIn 0.3s ease;
}
```

### API Response Standards

All admin APIs should return data in this format:

```json
{
  "data": [
    {
      "id": 1,
      "field1": "value1",
      "field2": "value2"
    }
  ],
  "current_page": 1,
  "last_page": 10,
  "per_page": 15,
  "total": 150
}
```

**Stats API:**
```json
{
  "stats": [
    {
      "label": "Total Users",
      "value": 1234,
      "icon": "fas fa-users",
      "color": "info"
    }
  ]
}
```

### Migration Checklist for New Sections

When migrating a new section to the advanced design:

- [ ] Create API controller with proper response format
- [ ] Implement Grid/List view toggle
- [ ] Add uniform card heights with flexbox
- [ ] Implement dropdown context menus
- [ ] Add search with debouncing and clear button
- [ ] Implement filter pill buttons or dropdown filters
- [ ] Add sort direction toggle
- [ ] Use CSS custom properties for theming
- [ ] Implement hover animations
- [ ] Add loading states with spinners
- [ ] Use advanced pagination with visible page numbers
- [ ] Implement click-outside-to-close for menus
- [ ] Add empty state messages
- [ ] Ensure mobile responsiveness
- [ ] Test all interactive elements

---

## Centralized Dashboard with Tabs

### Overview

As of Version 4.0, all statistics and widgets have been **centralized** into a single Dashboard component with tabbed interface. Individual section components (Users, Roles, Permissions) now focus **only** on CRUD operations and data management.

### Architecture Changes

**Before:**
- Each section (Users, Roles, Permissions) had its own stats widgets
- Stats were duplicated across components
- Each component made separate API calls for statistics

**After:**
- Single centralized Dashboard with tabs (Overview, Users, Roles, Permissions)
- All statistics loaded once in the Dashboard
- Section components only handle CRUD operations
- No duplication of stats code

### Dashboard Structure

**File:** `resources/js/views/admin/ModernDashboard.vue`

**Features:**
1. **Tab Navigation:**
   - Overview tab: High-level system metrics
   - Users tab: User-related statistics
   - Roles tab: Role-related statistics
   - Permissions tab: Permission-related statistics

2. **Quick Actions:**
   - Manage Users (router-link to /users)
   - Manage Roles (router-link to /roles)
   - Manage Permissions (router-link to /permissions)

3. **API Endpoints:**
   - `/api/admin/users/stats` - User statistics
   - `/api/admin/roles/stats` - Role statistics
   - `/api/admin/permissions/stats` - Permission statistics

4. **Unified Widget Design:**
   - All stat widgets use the same design via `admin-compact.css`
   - Consistent sizing across all tabs
   - Color-coded by category (info, success, warning, danger, primary)

### Component Responsibilities

#### ModernDashboard.vue
- Display all statistics in tabbed interface
- Load stats from all sections
- Provide quick action links to management sections
- Handle tab switching

#### UsersList.vue
- User CRUD operations (create, edit, delete)
- User data table with filters
- User search and sorting
- **NO stats widgets**

#### RolesList.vue
- Role CRUD operations (create, edit, delete)
- Role data display
- Role search and sorting
- **NO stats widgets**

#### PermissionsList.vue
- Permission CRUD operations (create, edit, delete)
- Permission data display with categories
- CRUD permission generator
- **NO stats widgets**

### Implementation Example

**Dashboard Tab Component:**
```vue
<template>
  <div class="dashboard-container">
    <!-- Tab Navigation -->
    <div class="stats-tabs-container mb-4">
      <div class="stats-tabs">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          class="stats-tab-btn"
          :class="{ active: activeTab === tab.id }"
          @click="activeTab = tab.id"
        >
          <i :class="tab.icon"></i>
          {{ tab.label }}
        </button>
      </div>
    </div>

    <!-- Tab Content -->
    <div v-if="activeTab === 'users'" class="tab-content-panel">
      <div class="stats-grid">
        <div
          v-for="stat in usersStats"
          :key="stat.label"
          class="stat-card-advanced"
          :class="`stat-${stat.color}`"
        >
          <div class="stat-icon-wrapper">
            <div class="stat-icon">
              <i :class="stat.icon"></i>
            </div>
          </div>
          <div class="stat-details">
            <h2 class="stat-number">{{ stat.value }}</h2>
            <p class="stat-label">{{ stat.label }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions mt-4">
      <router-link to="/users" class="action-card">
        <div class="action-icon bg-info">
          <i class="fas fa-users"></i>
        </div>
        <div class="action-content">
          <h4>Manage Users</h4>
          <p>View and manage all users</p>
        </div>
        <i class="fas fa-arrow-right action-arrow"></i>
      </router-link>
    </div>
  </div>
</template>
```

**Loading All Stats:**
```javascript
const loadAllStats = async () => {
  loading.value = true
  try {
    await Promise.all([
      loadOverviewStats(),
      loadUsersStats(),
      loadRolesStats(),
      loadPermissionsStats()
    ])
  } catch (error) {
    console.error('Error loading dashboard stats:', error)
  } finally {
    loading.value = false
  }
}

const loadUsersStats = async () => {
  try {
    const response = await fetch('/api/admin/users/stats')
    const data = await response.json()
    usersStats.value = data.stats || []
  } catch (error) {
    console.error('Error loading users stats:', error)
  }
}
```

### Migration Steps for New Sections

When migrating a new section:

1. **Remove Stats from Section Component:**
   - Delete stats grid from template
   - Remove `const stats = ref([])` from script
   - Remove `loadStats()` function
   - Remove stats API calls from data loading

2. **Add Stats to Dashboard:**
   - Add new tab to tabs array
   - Create new stats ref (e.g., `ordersStats`)
   - Add load function (e.g., `loadOrdersStats()`)
   - Add API call to `Promise.all` in `loadAllStats()`
   - Add tab content panel to template

3. **Update API Controller:**
   - Ensure stats endpoint returns correct format:
   ```php
   return response()->json([
       'stats' => [
           [
               'label' => 'Total Orders',
               'value' => Order::count(),
               'icon' => 'fas fa-shopping-cart',
               'color' => 'info'
           ]
       ]
   ]);
   ```

### Benefits of Centralized Approach

1. **Performance:**
   - Single API call per stats type instead of multiple
   - Reduced component bundle size
   - Faster initial page load

2. **Maintainability:**
   - Single source of truth for statistics
   - Easier to update stats design globally
   - Reduced code duplication

3. **User Experience:**
   - All statistics in one place
   - Easy navigation between stat categories
   - Consistent design across all stats

4. **Code Organization:**
   - Clear separation of concerns
   - Components focused on their primary purpose
   - Easier to test and debug

### Files Modified

- `resources/js/views/admin/ModernDashboard.vue` - Complete rewrite with tabs
- `resources/js/views/admin/UsersList.vue` - Removed stats
- `resources/js/views/admin/RolesList.vue` - Removed stats
- `resources/js/views/admin/PermissionsList.vue` - Removed stats
- `resources/css/admin-compact.css` - Unified stat widget styling

---

**Last Updated:** 2025-11-29
**Version:** 4.0 (Centralized Dashboard Architecture)
