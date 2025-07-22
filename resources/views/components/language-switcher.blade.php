<form method="GET" action="{{ url()->current() }}" class="d-inline">
    <select name="lang" onchange="this.form.submit()" class="form-select" style="width:auto;display:inline;">
        <option value="en" {{ app()->getLocale() == 'en' ? 'selected' : '' }}>{{('components.language-switcher.english') }}</option>
        <option value="ar" {{ app()->getLocale() == 'ar' ? 'selected' : '' }}>{{('components.language-switcher.arabic') }}</option>
        <!-- Add more languages here -->
    </select>
    @foreach(request()->except('lang') as $key => $value)
        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
    @endforeach
</form> 






