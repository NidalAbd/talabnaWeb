<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can ss the default title of your admin panel.
    |s
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'Talabna',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => true,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b>Talabna</b>',
    'logo_img' => 'img/logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Talabna Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => true,
        'img' => [
            'path' => 'img/logo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [
            'path' => 'img/logo.png',
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-primary elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => true,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'dark',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'menu' => [
        // Main Dashboard
        [
            'text' => 'Dashboard',
            'url'  => 'dashboard',
            'icon' => 'fas fa-tachometer-alt',
            'permission' => ['view_statistics']
        ],

        // Quick Actions
        [
            'text' => 'Quick Actions',
            'icon' => 'fas fa-bolt',
            'permission' => ['view_statistics'],
            'submenu' => [
                [
                    'text' => 'Add Service Post',
                    'url'  => 'service_posts/create',
                    'icon' => 'fas fa-plus-circle',
                    'permission' => ['create_service']
                ],
                [
                    'text' => 'Send Notification',
                    'url'  => 'admin/notifications/marketing',
                    'icon' => 'fas fa-bell',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'View Reports',
                    'url'  => 'reports',
                    'icon' => 'fas fa-chart-bar',
                    'permission' => ['report_index']
                ],
                [
                    'text' => 'System Health',
                    'url'  => 'system-health',
                    'icon' => 'fas fa-heartbeat',
                    'permission' => ['view_statistics']
                ]
            ]
        ],

        // User Management
        [
            'text'    => 'User Management',
            'icon'    => 'fas fa-users',
            'permission' => ['user_index', 'user_view'],
            'submenu' => [
                [
                    'text' => 'All Users',
                    'url'  => 'users',
                    'icon' => 'fas fa-users',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'Active Users',
                    'url'  => 'users?status=active',
                    'icon' => 'fas fa-user-check',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'Premium Users',
                    'url'  => 'users?type=premium',
                    'icon' => 'fas fa-crown',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'Banned Users',
                    'url'  => 'users?status=banned',
                    'icon' => 'fas fa-user-slash',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'User Analytics',
                    'url'  => 'user-analytics',
                    'icon' => 'fas fa-chart-line',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'Roles & Permissions',
                    'url'  => 'roles',
                    'icon' => 'fas fa-user-shield',
                    'permission' => ['view_role']
                ],
                [
                    'text' => 'Role Assignments',
                    'url'  => 'role-assignments',
                    'icon' => 'fas fa-user-cog',
                    'permission' => ['edit_role']
                ]
            ]
        ],

        // Financial Management
        [
            'text'    => 'Financial Management',
            'icon'    => 'fas fa-chart-line',
            'permission' => ['revenue_view', 'expense_view', 'profit_analysis_view'],
            'submenu' => [
                // Revenue Tracking
                [
                    'text' => 'Revenue Overview',
                    'url'  => 'financial/revenue',
                    'icon' => 'fas fa-dollar-sign',
                    'permission' => ['revenue_view']
                ],
                [
                    'text' => 'Point Sales',
                    'url'  => 'point-sales',
                    'icon' => 'fas fa-coins',
                    'permission' => ['point_sales.index']
                ],
                [
                    'text' => 'Premium Revenue',
                    'url'  => 'golden-post-revenue',
                    'icon' => 'fas fa-star',
                    'permission' => ['golden_revenue.view']
                ],
                [
                    'text' => 'Payment Reports',
                    'url'  => 'payment-reports',
                    'icon' => 'fas fa-credit-card',
                    'permission' => ['payment_reports.view']
                ],

                // Expense Management
                [
                    'text' => 'Expense Tracking',
                    'url'  => 'financial/expenses',
                    'icon' => 'fas fa-receipt',
                    'permission' => ['expense_view']
                ],
                [
                    'text' => 'Operational Costs',
                    'url'  => 'advertisement-costs',
                    'icon' => 'fas fa-cogs',
                    'permission' => ['advertisement_costs.index']
                ],
                [
                    'text' => 'Infrastructure Costs',
                    'url'  => 'server-hosting-costs',
                    'icon' => 'fas fa-server',
                    'permission' => ['server_costs.index']
                ],

                // Financial Analysis
                [
                    'text' => 'Profit & Loss',
                    'url'  => 'monthly-profit-loss',
                    'icon' => 'fas fa-chart-pie',
                    'permission' => ['monthly_pl.view']
                ],
                [
                    'text' => 'Cash Flow',
                    'url'  => 'cash-flow-projections',
                    'icon' => 'fas fa-chart-area',
                    'permission' => ['cash_flow_projections.view']
                ],
                [
                    'text' => 'Financial Reports',
                    'url'  => 'income-statement',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'permission' => ['income_statement.view']
                ]
            ]
        ],

        // Point System Management
        [
            'text'    => 'Point System',
            'icon'    => 'fas fa-coins',
            'permission' => ['view_statistics'],
            'submenu' => [
                [
                    'text' => 'Point Overview',
                    'url'  => 'palservice_points',
                    'icon' => 'fas fa-chart-pie',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Point Transactions',
                    'url'  => 'point_transactions',
                    'icon' => 'fas fa-exchange-alt',
                    'permission' => ['point_transactions.index']
                ],
                [
                    'text' => 'Purchase Requests',
                    'url'  => 'purchase_points',
                    'icon' => 'fas fa-shopping-cart',
                    'permission' => ['purchase_index']
                ],
                [
                    'text' => 'Point Packages',
                    'url'  => 'point-packages',
                    'icon' => 'fas fa-box',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Point Usage Analytics',
                    'url'  => 'point-analytics',
                    'icon' => 'fas fa-chart-bar',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Premium Features',
                    'url'  => 'premium-features',
                    'icon' => 'fas fa-gem',
                    'permission' => ['view_statistics']
                ]
            ]
        ],

        // Business Operations
        [
            'text'    => 'Business Operations',
            'icon'    => 'fas fa-briefcase',
            'permission' => ['investor_view', 'approval_view', 'planning_view'],
            'submenu' => [
                // Investor Relations
                [
                    'text' => 'Investor Dashboard',
                    'url'  => 'investor-dashboard',
                    'icon' => 'fas fa-chart-line',
                    'permission' => ['investor_view']
                ],
                [
                    'text' => 'Investor Relations',
                    'url'  => 'investor-profile',
                    'icon' => 'fas fa-handshake',
                    'permission' => ['investor_profile.view']
                ],
                [
                    'text' => 'Investment Tracking',
                    'url'  => 'investment-funding',
                    'icon' => 'fas fa-piggy-bank',
                    'permission' => ['investment_funding.index']
                ],

                // Business Planning
                [
                    'text' => 'Strategic Planning',
                    'url'  => 'two-year-plan',
                    'icon' => 'fas fa-road',
                    'permission' => ['strategic_plan.view']
                ],
                [
                    'text' => 'Budget Planning',
                    'url'  => 'monthly-budget-planning',
                    'icon' => 'fas fa-calendar-week',
                    'permission' => ['monthly_budget.index']
                ],
                [
                    'text' => 'Expense Approvals',
                    'url'  => 'expense-approvals',
                    'icon' => 'fas fa-check-circle',
                    'permission' => ['expense_approvals.index']
                ],
                [
                    'text' => 'Budget Controls',
                    'url'  => 'budget-limits',
                    'icon' => 'fas fa-exclamation-triangle',
                    'permission' => ['budget_limits.index']
                ]
            ]
        ],

        // Content Management
        [
            'text'    => 'Content Management',
            'icon'    => 'fas fa-clipboard-list',
            'permission' => ['view_service'],
            'submenu' => [
                // Service Posts
                [
                    'text' => 'All Service Posts',
                    'url'  => 'service_posts',
                    'icon' => 'fas fa-list-alt',
                    'permission' => ['view_all_service']
                ],
                [
                    'text' => 'Add New Service',
                    'url'  => 'service_posts/create',
                    'icon' => 'fas fa-plus',
                    'permission' => ['create_service']
                ],
                [
                    'text' => 'User Services',
                    'url'  => 'userAllServiceIndex',
                    'icon' => 'fas fa-user-tag',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Premium Posts',
                    'url'  => 'service_posts?badge=premium',
                    'icon' => 'fas fa-star',
                    'permission' => ['view_service']
                ],

                // Categories & Locations
                [
                    'text' => 'Categories',
                    'url'  => 'categories',
                    'icon' => 'fas fa-folder',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Sub Categories',
                    'url'  => 'indexSubCategory',
                    'icon' => 'fas fa-folder-open',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Location Management',
                    'url'  => 'countries',
                    'icon' => 'fas fa-map-marker-alt',
                    'permission' => ['view_service']
                ]
            ]
        ],

        // Marketing & Analytics
        [
            'text'    => 'Marketing & Analytics',
            'icon'    => 'fas fa-bullhorn',
            'permission' => ['view_statistics'],
            'submenu' => [
                [
                    'text' => 'Marketing Dashboard',
                    'url'  => 'marketing-dashboard',
                    'icon' => 'fas fa-chart-line',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Send Notifications',
                    'url'  => 'admin/notifications/marketing',
                    'icon' => 'fas fa-bell',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Notification History',
                    'url'  => 'admin/notifications/marketing/history',
                    'icon' => 'fas fa-history',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Statistics Overview',
                    'url'  => 'statistics',
                    'icon' => 'fas fa-chart-pie',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'System Reports',
                    'url'  => 'reports',
                    'icon' => 'fas fa-file-alt',
                    'permission' => ['report_index']
                ],
                [
                    'text' => 'User Analytics',
                    'url'  => 'user-analytics',
                    'icon' => 'fas fa-users',
                    'permission' => ['view_statistics']
                ]
            ]
        ],

        // System Management
        [
            'text'    => 'System Management',
            'icon'    => 'fas fa-cogs',
            'permission' => ['view_statistics'],
            'submenu' => [
                [
                    'text' => 'System Health',
                    'url'  => 'system-health',
                    'icon' => 'fas fa-heartbeat',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Database Management',
                    'url'  => 'database-management',
                    'icon' => 'fas fa-database',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'Backup & Restore',
                    'url'  => 'backup-restore',
                    'icon' => 'fas fa-save',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'System Logs',
                    'url'  => 'system-logs',
                    'icon' => 'fas fa-file-alt',
                    'permission' => ['view_statistics']
                ],
                [
                    'text' => 'API Management',
                    'url'  => 'api-management',
                    'icon' => 'fas fa-code',
                    'permission' => ['view_statistics']
                ]
            ]
        ]
    ],

    /*

        |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
        App\Providers\LaratrustMenuFilter::class, // Add this line

    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@8',
                ],
            ],
        ],
        'Pace' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
