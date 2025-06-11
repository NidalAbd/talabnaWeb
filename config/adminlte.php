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
    'dashboard_url' => 'home',
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
        // Dashboard Section
        [
            'text' => 'Dashboard',
            'url'  => 'home',
            'icon' => 'fas fa-tachometer-alt',
            'permission' => ['view_statistics']
        ],

        // Administration Section
        [
            'text'    => 'Admin Management',
            'icon'    => 'fas fa-cogs',
            'permission' => ['user_index', 'user_view'],
            'submenu' => [
                [
                    'text' => 'Users',
                    'url'  => 'users',
                    'icon' => 'fas fa-users',
                    'permission' => ['user_index']
                ],
                [
                    'text' => 'Roles',
                    'url'  => 'roles',  // This should match your roles route
                    'icon' => 'fas fa-user-tag',
                    'permission' => ['view_role']
                ],
                [
                    'text' => 'Permissions',
                    'url'  => 'permissions',  // This should match your permissions route
                    'icon' => 'fas fa-key',
                    'permission' => ['view_permission']
                ],
                [
                    'text' => 'Role Assignments',  // You might want to add this new menu item
                    'url'  => 'role-assignments',  // This should match your role-assignments route
                    'icon' => 'fas fa-user-cog',
                    'permission' => ['edit_role']
                ],
                [
                    'text' => 'Orders',
                    'url'  => 'purchase_points',
                    'icon' => 'fas fa-shopping-cart',
                    'permission' => ['purchase_index']
                ],
                [
                    'text' => 'Transactions',
                    'url'  => 'point_transactions',
                    'icon' => 'fas fa-exchange-alt',
                    'permission' => ['point_transactions.index']
                ]
            ]
        ],
        [
            'text'    => 'Revenue Management',
            'icon'    => 'fas fa-dollar-sign',
            'permission' => ['revenue_view'],
            'submenu' => [
                [
                    'text' => 'Point Sales',
                    'url'  => 'point-sales',
                    'icon' => 'fas fa-coins',
                    'permission' => ['point_sales.index']
                ],
                [
                    'text' => 'Golden Post Revenue',
                    'url'  => 'golden-post-revenue',
                    'icon' => 'fas fa-star',
                    'permission' => ['golden_revenue.view']
                ],
                [
                    'text' => 'Diamond Post Revenue',
                    'url'  => 'diamond-post-revenue',
                    'icon' => 'fas fa-gem',
                    'permission' => ['diamond_revenue.view']
                ],
                [
                    'text' => 'Revenue Analytics',
                    'url'  => 'revenue-analytics',
                    'icon' => 'fas fa-chart-line',
                    'permission' => ['revenue_analytics.view']
                ],
                [
                    'text' => 'Payment Gateway Reports',
                    'url'  => 'payment-reports',
                    'icon' => 'fas fa-credit-card',
                    'permission' => ['payment_reports.view']
                ]
            ]
        ],

        // Investor Management Section
        [
            'text'    => 'Investor Management',
            'icon'    => 'fas fa-handshake',
            'permission' => ['investor_view'],
            'submenu' => [
                [
                    'text' => 'Investor Profile',
                    'url'  => 'investor-profile',
                    'icon' => 'fas fa-user-tie',
                    'permission' => ['investor_profile.view']
                ],
                [
                    'text' => 'Investment Agreements',
                    'url'  => 'investment-agreements',
                    'icon' => 'fas fa-file-contract',
                    'permission' => ['investment_agreements.index']
                ],
                [
                    'text' => 'Investment Funding',
                    'url'  => 'investment-funding',
                    'icon' => 'fas fa-piggy-bank',
                    'permission' => ['investment_funding.index']
                ],
                [
                    'text' => 'Investor Reports',
                    'url'  => 'investor-reports',
                    'icon' => 'fas fa-chart-bar',
                    'permission' => ['investor_reports.view']
                ],
                [
                    'text' => 'ROI Tracking',
                    'url'  => 'roi-tracking',
                    'icon' => 'fas fa-percentage',
                    'permission' => ['roi_tracking.view']
                ]
            ]
        ],

        // Expense Management Section
        [
            'text'    => 'Expense Management',
            'icon'    => 'fas fa-receipt',
            'permission' => ['expense_view'],
            'submenu' => [
                [
                    'text' => 'Expense Categories',
                    'url'  => 'expense-categories',
                    'icon' => 'fas fa-tags',
                    'permission' => ['expense_categories.index']
                ],
                [
                    'text' => 'Advertisement Costs',
                    'url'  => 'advertisement-costs',
                    'icon' => 'fas fa-bullhorn',
                    'permission' => ['advertisement_costs.index']
                ],
                [
                    'text' => 'Equipment & Furniture',
                    'url'  => 'equipment-furniture',
                    'icon' => 'fas fa-couch',
                    'permission' => ['equipment_furniture.index']
                ],
                [
                    'text' => 'Development Devices',
                    'url'  => 'development-devices',
                    'icon' => 'fas fa-laptop',
                    'permission' => ['development_devices.index']
                ],
                [
                    'text' => 'Technical Support',
                    'url'  => 'technical-support-costs',
                    'icon' => 'fas fa-tools',
                    'permission' => ['technical_support.index']
                ],
                [
                    'text' => 'Server & Hosting',
                    'url'  => 'server-hosting-costs',
                    'icon' => 'fas fa-server',
                    'permission' => ['server_costs.index']
                ],
                [
                    'text' => 'Office Rent & Utilities',
                    'url'  => 'office-expenses',
                    'icon' => 'fas fa-building',
                    'permission' => ['office_expenses.index']
                ],
                [
                    'text' => 'Legal & Compliance',
                    'url'  => 'legal-compliance',
                    'icon' => 'fas fa-gavel',
                    'permission' => ['legal_costs.index']
                ],
                [
                    'text' => 'Marketing & Promotion',
                    'url'  => 'marketing-costs',
                    'icon' => 'fas fa-megaphone',
                    'permission' => ['marketing_costs.index']
                ]
            ]
        ],

        // Approval & Budget Control Section
        [
            'text'    => 'Approval & Budget Control',
            'icon'    => 'fas fa-clipboard-check',
            'permission' => ['approval_view'],
            'submenu' => [
                [
                    'text' => 'Expense Approvals',
                    'url'  => 'expense-approvals',
                    'icon' => 'fas fa-check-circle',
                    'permission' => ['expense_approvals.index']
                ],
                [
                    'text' => 'Investment Approvals',
                    'url'  => 'investment-approvals',
                    'icon' => 'fas fa-stamp',
                    'permission' => ['investment_approvals.index']
                ],
                [
                    'text' => 'Budget Limits',
                    'url'  => 'budget-limits',
                    'icon' => 'fas fa-exclamation-triangle',
                    'permission' => ['budget_limits.index']
                ],
                [
                    'text' => 'Approval Workflow',
                    'url'  => 'approval-workflow',
                    'icon' => 'fas fa-project-diagram',
                    'permission' => ['approval_workflow.view']
                ],
                [
                    'text' => 'Payment Status',
                    'url'  => 'payment-status',
                    'icon' => 'fas fa-money-check',
                    'permission' => ['payment_status.view']
                ]
            ]
        ],

        // Financial Planning Section
        [
            'text'    => 'Financial Planning',
            'icon'    => 'fas fa-calendar-alt',
            'permission' => ['planning_view'],
            'submenu' => [
                [
                    'text' => '2-Year Strategic Plan',
                    'url'  => 'two-year-plan',
                    'icon' => 'fas fa-road',
                    'permission' => ['strategic_plan.view']
                ],
                [
                    'text' => 'Monthly Budget Planning',
                    'url'  => 'monthly-budget-planning',
                    'icon' => 'fas fa-calendar-week',
                    'permission' => ['monthly_budget.index']
                ],
                [
                    'text' => 'Quarterly Reviews',
                    'url'  => 'quarterly-reviews',
                    'icon' => 'fas fa-calendar-check',
                    'permission' => ['quarterly_reviews.view']
                ],
                [
                    'text' => 'Cash Flow Projections',
                    'url'  => 'cash-flow-projections',
                    'icon' => 'fas fa-chart-area',
                    'permission' => ['cash_flow_projections.view']
                ],
                [
                    'text' => 'Break-even Analysis',
                    'url'  => 'break-even-analysis',
                    'icon' => 'fas fa-balance-scale-right',
                    'permission' => ['break_even.view']
                ]
            ]
        ],

        // Profit & Loss Analysis Section
        [
            'text'    => 'Profit & Loss Analysis',
            'icon'    => 'fas fa-chart-pie',
            'permission' => ['profit_analysis_view'],
            'submenu' => [
                [
                    'text' => 'Monthly P&L',
                    'url'  => 'monthly-profit-loss',
                    'icon' => 'fas fa-calendar-day',
                    'permission' => ['monthly_pl.view']
                ],
                [
                    'text' => 'Current Month Profit',
                    'url'  => 'current-month-profit',
                    'icon' => 'fas fa-coins',
                    'permission' => ['current_profit.view']
                ],
                [
                    'text' => 'Profit Retention Analysis',
                    'url'  => 'profit-retention-analysis',
                    'icon' => 'fas fa-piggy-bank',
                    'permission' => ['profit_retention.view']
                ],
                [
                    'text' => '10-Month Profit Trend',
                    'url'  => 'ten-month-profit-trend',
                    'icon' => 'fas fa-chart-line',
                    'permission' => ['profit_trend.view']
                ],
                [
                    'text' => 'Profitability Forecasts',
                    'url'  => 'profitability-forecasts',
                    'icon' => 'fas fa-crystal-ball',
                    'permission' => ['profitability_forecasts.view']
                ],
                [
                    'text' => 'Unit Economics',
                    'url'  => 'unit-economics',
                    'icon' => 'fas fa-calculator',
                    'permission' => ['unit_economics.view']
                ]
            ]
        ],

        // Financial Reports Section
        [
            'text'    => 'Financial Reports',
            'icon'    => 'fas fa-file-invoice-dollar',
            'permission' => ['financial_reports_view'],
            'submenu' => [
                [
                    'text' => 'Executive Dashboard',
                    'url'  => 'executive-dashboard',
                    'icon' => 'fas fa-tachometer-alt',
                    'permission' => ['executive_dashboard.view']
                ],
                [
                    'text' => 'Income Statement',
                    'url'  => 'income-statement',
                    'icon' => 'fas fa-chart-bar',
                    'permission' => ['income_statement.view']
                ],
                [
                    'text' => 'Balance Sheet',
                    'url'  => 'balance-sheet',
                    'icon' => 'fas fa-file-invoice',
                    'permission' => ['balance_sheet.view']
                ],
                [
                    'text' => 'Cash Flow Statement',
                    'url'  => 'cash-flow-statement',
                    'icon' => 'fas fa-water',
                    'permission' => ['cash_flow.view']
                ],
                [
                    'text' => 'Investor Quarterly Report',
                    'url'  => 'investor-quarterly-report',
                    'icon' => 'fas fa-file-pdf',
                    'permission' => ['investor_quarterly.view']
                ],
                [
                    'text' => 'Tax Reports',
                    'url'  => 'tax-reports',
                    'icon' => 'fas fa-file-alt',
                    'permission' => ['tax_reports.view']
                ]
            ]
        ],
        [
            'text' => 'Marketing',
            'icon' => 'fas fa-bullhorn',
            'permission' => ['view_statistics'],
            'submenu' => [
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
            ]
        ],

        // Location Management Section
        [
            'text'    => 'Location Management',
            'icon'    => 'fas fa-map-marker-alt',
            'permission' => ['view_service'],
            'submenu' => [
                [
                    'text' => 'Countries',
                    'url'  => 'countries',
                    'icon' => 'fas fa-globe',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Add Country',
                    'url'  => 'countries/create',
                    'icon' => 'fas fa-plus-circle',
                    'permission' => ['create_service']
                ],
                [
                    'text' => 'Cities',
                    'url'  => 'cities',
                    'icon' => 'fas fa-city',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Add City',
                    'url'  => 'cities/create',
                    'icon' => 'fas fa-plus-circle',
                    'permission' => ['create_service']
                ],
            ]
        ],

        // Categories Section
        [
            'text'    => 'Categories',
            'icon'    => 'fas fa-list',
            'permission' => ['view_service'],
            'submenu' => [
                [
                    'text' => 'Main Categories',
                    'url'  => 'categories',
                    'icon' => 'fas fa-folder',
                    'permission' => ['view_service']
                ],
                [
                    'text' => 'Add Category',
                    'url'  => 'categories/create',
                    'icon' => 'fas fa-plus-circle',
                    'permission' => ['create_service']
                ],
                [
                    'text' => 'Sub Categories',
                    'url'  => 'indexSubCategory',
                    'icon' => 'fas fa-folder-open',
                    'permission' => ['view_service']
                ]
            ]
        ],

        // Service Posts Section
        [
            'text'    => 'Service Posts',
            'icon'    => 'fas fa-clipboard-list',
            'permission' => ['view_service'],
            'submenu' => [
                [
                    'text' => 'All Services',
                    'url'  => 'service_posts',
                    'icon' => 'fas fa-list-alt',
                    'permission' => ['view_all_service']
                ],
                [
                    'text' => 'Add New Post',
                    'url'  => 'service_posts/create',
                    'icon' => 'fas fa-plus',
                    'permission' => ['create_service']
                ],
                [
                    'text' => 'User Services',
                    'url'  => 'userAllServiceIndex',
                    'icon' => 'fas fa-user-tag',
                    'permission' => ['view_service']
                ]
            ]
        ],

        [
            'text' => 'Statistics',
            'url'  => 'statistics',
            'icon' => 'fas fa-chart-pie',
            'permission' => ['view_statistics']
        ],
        [
            'text' => 'Reports',
            'url'  => 'reports',
            'icon' => 'fas fa-file-alt',
            'permission' => ['report_index']
        ],
        // Pal Service Points Section
        [
            'text' => 'Points Overview',
            'url'  => 'palservice_points',
            'icon' => 'fas fa-piggy-bank'
        ],

    ],    /*

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
