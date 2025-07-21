@extends('layouts.app')
@section('content')
<div class="container">
    <h1>{{ __('admin\translations\create.add_translation') }}</h1>
    <form method="POST" action="{{ route('translations.store') }}">
        @csrf
        <div class="mb-3">
            <label for="group" class="form-label">{{ __('admin\translations\create.group') }}</label>
            <input type="text" class="form-control" id="group" name="group" required>
        </div>
        <div class="mb-3">
            <label for="key" class="form-label">{{ __('admin\translations\create.key') }}</label>
            <input type="text" class="form-control" id="key" name="key" required>
        </div>
        <div class="mb-3">
            <label for="text_en" class="form-label">{{ __('admin\translations\create.english') }}</label>
            <input type="text" class="form-control" id="text_en" name="text_en" required>
        </div>
        <div class="mb-3">
            <label for="text_ar" class="form-label">{{ __('admin\translations\create.arabic') }}</label>
            <input type="text" class="form-control" id="text_ar" name="text_ar">
        </div>
        <button type="submit" class="btn btn-success">{{ __('admin\translations\create.add') }}</button>
        <a href="{{ route('translations.index') }}" class="btn btn-secondary">{{ __('admin\translations\create.cancel') }}</a>
    </form>
</div>
@endsection 