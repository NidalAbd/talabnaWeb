<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" sizes="64x64" href="{{ asset('storage/favicon-32x32.png') }}">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel111') }}</title>
    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    <link rel="icon" href="{{ asset('img/logo.ico') }}">


    <!-- Scripts -->
    <!-- jQuery and Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Other body tags -->
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

    <!-- App scripts and styles -->
    <script src="{{ asset('js/app.js') }}" defer></script>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css"/>
    <script src="{{ mix('js/app.js') }}"></script>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <!-- Scripts -->
    <style>
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
        .bg-gradient {
            background: linear-gradient(135deg, #4caf50, #81c784);
            color: #ffffff;
        }
        .btn-light {
            background-color: #ffffff;
            color: #4caf50;
            border: none;
        }
        .btn-light:hover {
            background-color: #f1f1f1;
            color: #388e3c;
        }

        .info-box {
            border-radius: 8px;
            display: flex;
            align-items: center;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .info-box-icon {
            width: 60px;
            height: 60px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 1.5rem;
            border-radius: 50%;
            margin-right: 1rem;
        }
        .info-box-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .small-box {
            border-radius: 8px;
            padding: 1rem;
            position: relative;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .small-box .icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 2.5rem;
            opacity: 0.15;
        }
        .small-box-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0.5rem;
            font-weight: bold;
            text-decoration: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }
        .info-box .info-box-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            border-radius: 50%;
            width: 60px;
            height: 60px;
        }
        .info-box .info-box-content {
            margin-left: 70px;
        }
        .info-box-number {
            font-size: 1.75rem;
            font-weight: bold;
        }

    </style>
</head>
<body class="antialiased">
<!-- Navigation Bar -->
<nav class="navbar navbar-expand-md navbar-dark bg-primary shadow-sm">
    <div class="container justify-content-between">
        <!-- Brand -->
        <a class="navbar-brand fw-bold text-white" href="{{ url('userAllServiceIndex') }}">
            {{ config('app.name', 'Laravel') }}
        </a>

        <!-- Toggler -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Collapsible Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Centered Links -->
            <ul class="navbar-nav mx-auto d-flex flex-wrap align-items-center">
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">All</a>
                </li>
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">Jobs</a>
                </li>
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">Devices</a>
                </li>
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">Real Estate</a>
                </li>
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">Cars</a>
                </li>
                <li class="nav-item">
                    <a href="/" class="nav-link text-white px-3 fw-semibold">Service</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white fw-semibold" href="{{ route('policy.index') }}">{{ __('policy') }}</a>
                </li>
            </ul>

            <!-- Authentication Links -->
            <ul class="navbar-nav ms-auto">
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>
                    @endif
                    @if (Route::has('register'))
                        <li class="nav-item">
                            <a class="nav-link text-white fw-semibold" href="{{ route('register') }}">{{ __('Register') }}</a>
                        </li>
                    @endif
                @else
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="{{ route('home') }}">{{ __('Dashboard') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white fw-semibold" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
<div id="subcategory-container" class="d-flex flex-wrap mt-2" style="display: none;">
    <!-- Subcategories/tags will be added here dynamically -->
</div>
<!-- Main Content Container -->
<div class="container my-5">
    <div class="d-flex align-items-center justify-content-between bg-gradient shadow-lg p-5 rounded-4">
        <!-- Left Section -->
        <div class="text-white">
            <h1 class="fw-bold display-5 mb-3">🚀 Talabnaa App is Now Live!</h1>
            <p class="fs-5">
                Experience convenience like never before. Download the Talabna app on Google Play
                and take your services to the next level!
            </p>
            <a href="https://play.google.com/store/apps/details?id=com.talabna.talabna" target="_blank"
               class="btn btn-light btn-lg fw-bold">
                <i class="fab fa-google-play"></i> Get it on Google Play
            </a>
        </div>

    </div>
</div>


<div class="">
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="d-flex justify-content-center">
                <h2><strong>Statistics only Displayed and the Data Viewed Only by Flutter end Point</strong></h2>
            </div>
            <div class="col-md-12">
                <div class="container">
                    <div class="row">
                        <!-- Active Box -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-success text-white shadow-sm">
                                <div class="info-box-icon bg-light text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Active') }}</span>
                                    <span class="info-box-number h4">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Banned Box -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-warning text-dark shadow-sm">
                                <div class="info-box-icon bg-light text-warning">
                                    <i class="fas fa-ghost"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Banned') }}</span>
                                    <span class="info-box-number h4">{{$allService}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Not Active Box -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-secondary text-white shadow-sm">
                                <div class="info-box-icon bg-light text-secondary">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Not Active') }}</span>
                                    <span class="info-box-number h4">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Rejected Box -->
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="info-box bg-danger text-white shadow-sm">
                                <div class="info-box-icon bg-light text-danger">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Rejected') }}</span>
                                    <span class="info-box-number h4">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Purchase Requests -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-primary text-white shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">{{$purchaseRequests}}</h3>
                                    <p class="fw-bold">Purchase Requests</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="#" class="small-box-footer text-light">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <!-- Users -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-success text-white shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">{{$users}}</h3>
                                    <p class="fw-bold">{{ __('User') }}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="#" class="small-box-footer text-light">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <!-- Unique Visitors -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-warning text-dark shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">65</h3>
                                    <p class="fw-bold">Unique Visitors</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer text-dark">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- Diamond Box -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-info text-white shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">{{$allDiamond}}</h3>
                                    <p class="fw-bold">Diamond</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-bag"></i>
                                </div>
                                <a href="#" class="small-box-footer text-light">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <!-- Golden Box -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-warning text-dark shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">{{$allGolden}}</h3>
                                    <p class="fw-bold">{{ __('Golden') }}</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-person-add"></i>
                                </div>
                                <a href="#" class="small-box-footer text-dark">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                        <!-- Normal Box -->
                        <div class="col-lg-4 col-6">
                            <div class="small-box bg-secondary text-white shadow-sm">
                                <div class="inner">
                                    <h3 class="display-5">{{$allNormal}}</h3>
                                    <p class="fw-bold">Normal</p>
                                </div>
                                <div class="icon">
                                    <i class="ion ion-pie-graph"></i>
                                </div>
                                <a href="#" class="small-box-footer text-light">
                                    More info <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <!-- All Services -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-primary text-white shadow-sm">
                                <span class="info-box-icon bg-light text-primary elevation-1"><i class="fas fa-tools"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('All') }}</span>
                                    <span class="info-box-number display-6">{{$allService}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- General Service -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-info text-white shadow-sm">
                                <span class="info-box-icon bg-light text-info elevation-1"><i class="fas fa-ghost"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('General Service') }}</span>
                                    <span class="info-box-number display-6">{{$allGeneral}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Real Estate -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-success text-white shadow-sm">
                                <span class="info-box-icon bg-light text-success elevation-1"><i class="fas fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Real Estate') }}</span>
                                    <span class="info-box-number display-6">{{$allRealState}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Jobs -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-warning text-dark shadow-sm">
                                <span class="info-box-icon bg-light text-warning elevation-1"><i class="fas fa-joint"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Jobs') }}</span>
                                    <span class="info-box-number display-6">{{$allJobs}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Cars -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-secondary text-white shadow-sm">
                                <span class="info-box-icon bg-light text-secondary elevation-1"><i class="fas fa-car"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Cars') }}</span>
                                    <span class="info-box-number display-6">{{$allCar}}</span>
                                </div>
                            </div>
                        </div>
                        <!-- Devices -->
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="info-box bg-danger text-white shadow-sm">
                                <span class="info-box-icon bg-light text-danger elevation-1"><i class="fas fa-phone"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text fw-bold">{{ __('Devices') }}</span>
                                    <span class="info-box-number display-6">{{$allPhone}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>

{{--@section('script')--}}
{{--    <script>--}}
{{--        document.addEventListener('DOMContentLoaded', function () {--}}
{{--            // Get references to elements--}}
{{--            const subcategoryContainer = document.getElementById('subcategory-container');--}}
{{--            const categoryLinks = document.querySelectorAll('.category-link');--}}

{{--            // Function to display subcategories/tags--}}
{{--            function displaySubcategories(subcategoryList) {--}}
{{--                subcategoryContainer.innerHTML = ''; // Clear previous content--}}
{{--                subcategoryList.split(',').forEach((subcategory) => {--}}
{{--                    const subcategoryTag = document.createElement('span');--}}
{{--                    subcategoryTag.className = 'badge bg-info mx-2';--}}
{{--                    subcategoryTag.textContent = subcategory;--}}
{{--                    subcategoryContainer.appendChild(subcategoryTag);--}}
{{--                });--}}
{{--                subcategoryContainer.style.display = 'flex'; // Show the container--}}
{{--            }--}}

{{--            // Event listener for category link clicks--}}
{{--            categoryLinks.forEach((link) => {--}}
{{--                link.addEventListener('click', function (event) {--}}
{{--                    event.preventDefault();--}}
{{--                    const subcategories = this.getAttribute('data-subcategories');--}}
{{--                    displaySubcategories(subcategories);--}}
{{--                });--}}
{{--            });--}}
{{--        });--}}
{{--    </script>--}}
{{--@endsection--}}

{{--<!-- ./wrapper -->--}}
{{--@yield('script')--}}
{{--<script>--}}
{{--    $(document).ready(function() {--}}
{{--        const filterForm = document.getElementById('filterForm');--}}
{{--        const categorySelect = document.getElementById('filterCategory');--}}
{{--        const subcategorySelect = document.getElementById('filterSubCategory');--}}

{{--        // Function to fetch subcategories based on the selected category--}}
{{--        function fetchSubcategories() {--}}
{{--            const category = categorySelect.value;--}}
{{--            const subcategoryDropdown = subcategorySelect;--}}

{{--            // Clear the existing options--}}
{{--            while (subcategoryDropdown.options.length > 0) {--}}
{{--                subcategoryDropdown.remove(0);--}}
{{--            }--}}

{{--            // Make an AJAX request to fetch subcategories based on the selected category--}}
{{--            $.ajax({--}}
{{--                url: `{{ route('fetchSubcategories') }}?category=${category}`,--}}
{{--                type: 'GET',--}}
{{--                success: function(data) {--}}
{{--                    // Add the retrieved subcategories to the dropdown--}}
{{--                    data.forEach(function(subcategory) {--}}
{{--                        const option = new Option(subcategory.name, subcategory.name);--}}
{{--                        subcategoryDropdown.add(option);--}}
{{--                    });--}}
{{--                }--}}
{{--            });--}}
{{--        }--}}

{{--        // Event listener for category selection change--}}
{{--        categorySelect.addEventListener('change', function() {--}}
{{--            fetchSubcategories();--}}
{{--        });--}}

{{--        // Event listener for form submission--}}
{{--        filterForm.addEventListener('submit', function(event) {--}}
{{--            // Prevent the default form submission behavior--}}
{{--            event.preventDefault();--}}

{{--            // Get the selected values from the dropdowns--}}
{{--            const selectedCategory = categorySelect.value;--}}
{{--            const selectedSubCategory = subcategorySelect.value;--}}

{{--            // Construct the URL with the selected values--}}
{{--            const url = '{{ route("servicePostCategorySubCategory", ["categories" => "1" ,"sub_categories" => "2"]) }}'--}}
{{--                .replace('"1"', selectedCategory)--}}
{{--                .replace('"2"', selectedSubCategory);--}}

{{--            // Set the form action to the constructed URL--}}
{{--            filterForm.action = url;--}}

{{--            // Submit the form--}}
{{--            filterForm.submit();--}}
{{--        });--}}

{{--        // Initial fetch of subcategories based on the selected category--}}
{{--        fetchSubcategories();--}}
{{--    });--}}
{{--</script>--}}




