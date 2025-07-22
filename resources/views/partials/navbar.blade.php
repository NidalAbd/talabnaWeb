<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Language Switcher -->
        <li class="nav-item">
            <form method="GET" action="{{ url()->current() }}" class="d-inline">
                <select name="lang" onchange="this.form.submit()" class="form-control form-control-sm" style="width:auto;display:inline;">
                    <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>{{ __('partials/navbar.english') }}</option>
                    <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>{{ __('partials/navbar.arabic') }}</option>
                </select>
                @foreach(request()->except('lang') as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
            </form>
        </li>
        <!-- Navbar Search -->
        <li class="nav-item">
            <a class="nav-link" data-widget="navbar-search" href="#" role="button">
                <i class="fas fa-search"></i>
            </a>
        </li>
    </ul>
</nav>







