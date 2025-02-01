@extends('dashboard')
@section('title', "Edit User")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">

 <div class="d-flex justify-content-end mb-3">
                    <ul class="nav nav-pills ml-auto">
                        <li class="nav-item">
                             <div class="image text-center">
        @if ($user->photos->count() > 0)
            <img src="{{ asset('storage/' . $user->photos->first()->src) }}"
                 class="img-thumbnail rounded-circle" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
        @else
            <img src="{{ asset('storage/photos/avatar1.png') }}" class="img-thumbnail rounded-circle" alt="Profile Picture" style="width: 100px; height: 100px; object-fit: cover;">
        @endif
    </div>
                        </li>
                    </ul>
                </div>
  
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title mb-0">
                            <i class="fas fa-users mr-1"></i>
                            Edit User
                        </h3>


                    </div>
                    <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="user_name" class="form-label">Username:</label>
                                    <input type="text" name="user_name" class="form-control" value="{{ $user->user_name }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="name" class="form-label">Name:</label>
                                    <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="gender" class="form-label">Gender:</label>
                                    <select name="gender" class="form-control">
                                        <option value="">اختر</option>
                                        <option value="ذكر" {{ $user->gender == 'ذكر' ? 'selected' : '' }}>ذكر</option>
                                        <option value="انثى" {{ $user->gender == 'انثى' ? 'selected' : '' }}>انثى</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="date_of_birth" class="form-label">Date of Birth:</label>
                                    <input type="datetime-local" name="date_of_birth" class="form-control" value="{{ date('Y-m-d\TH:i', strtotime($user->date_of_birth)) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="location_latitudes" class="form-label">Latitude:</label>
                                    <input type="text" name="location_latitudes" class="form-control" value="{{ $user->location_latitudes }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="location_longitudes" class="form-label">Longitude:</label>
                                    <input type="text" name="location_longitudes" class="form-control" value="{{ $user->location_longitudes }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="phones" class="form-label">Phone Number:</label>
                                    <input type="text" name="phones" class="form-control" value="{{ $user->phones }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="WatsNumber" class="form-label">WhatsApp Number:</label>
                                    <input type="text" name="WatsNumber" class="form-control" value="{{ $user->WatsNumber }}">
                                    @error('WatsNumber')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="city" class="form-label">City:</label>
                                    <select name="city" id="city" class="form-control @error('city') is-invalid @enderror">
                                        <option value="">Select city</option>
                                        <option value="القدس" {{ old('city', $user->city) === 'القدس' ? 'selected' : '' }}>القدس</option>
                                        <option value="شمال غزة" {{ old('city', $user->city) === 'شمال غزة' ? 'selected' : '' }}>شمال غزة</option>
                                        <option value="غزة" {{ old('city', $user->city) === 'غزة' ? 'selected' : '' }}>غزة</option>
                                        <option value="دير البلح" {{ old('city', $user->city) === 'دير البلح' ? 'selected' : '' }}>دير البلح</option>
                                        <option value="خان يونس" {{ old('city', $user->city) === 'خان يونس' ? 'selected' : '' }}>خان يونس</option>
                                        <option value="رفح" {{ old('city', $user->city) === 'رفح' ? 'selected' : '' }}>رفح</option>
                                        <option value="رام الله" {{ old('city', $user->city) === 'رام الله' ? 'selected' : '' }}>رام الله</option>
                                        <option value="الخليل" {{ old('city', $user->city) === 'الخليل' ? 'selected' : '' }}>الخليل</option>
                                        <option value="بيت لحم" {{ old('city', $user->city) === 'بيت لحم' ? 'selected' : '' }}>بيت لحم</option>
                                        <option value="نابلس" {{ old('city', $user->city) === 'نابلس' ? 'selected' : '' }}>نابلس</option>
                                        <option value="جنين" {{ old('city', $user->city) === 'جنين' ? 'selected' : '' }}>جنين</option>
                                        <option value="سلفيت" {{ old('city', $user->city) === 'سلفيت' ? 'selected' : '' }}>سلفيت</option>
                                        <option value="عسقلان" {{ old('city', $user->city) === 'عسقلان' ? 'selected' : '' }}>عسقلان</option>
                                        <option value="بئر السبع" {{ old('city', $user->city) === 'بئر السبع' ? 'selected' : '' }}>بئر السبع</option>
                                        <option value="طبريا" {{ old('city', $user->city) === 'طبريا' ? 'selected' : '' }}>طبريا</option>
                                        <option value="الناصرة" {{ old('city', $user->city) === 'الناصرة' ? 'selected' : '' }}>الناصرة</option>
                                        <option value="صفد" {{ old('city', $user->city) === 'صفد' ? 'selected' : '' }}>صفد</option>
                                        <option value="بيسان" {{ old('city', $user->city) === 'بيسان' ? 'selected' : '' }}>بيسان</option>
                                        <option value="اللد" {{ old('city', $user->city) === 'اللد' ? 'selected' : '' }}>اللد</option>
                                        <option value="الرملة" {{ old('city', $user->city) === 'الرملة' ? 'selected' : '' }}>الرملة</option>
                                        <option value="طولكرم" {{ old('city', $user->city) === 'طولكرم' ? 'selected' : '' }}>طولكرم</option>
                                        <option value="قلقيلية" {{ old('city', $user->city) === 'قلقيلية' ? 'selected' : '' }}>قلقيلية</option>
                                        <option value="عكا" {{ old('city', $user->city) === 'عكا' ? 'selected' : '' }}>عكا</option>
                                        <option value="حيفا" {{ old('city', $user->city) === 'حيفا' ? 'selected' : '' }}>حيفا</option>
                                        <option value="يافا" {{ old('city', $user->city) === 'يافا' ? 'selected' : '' }}>يافا</option>
                                        <option value="أريحا" {{ old('city', $user->city) === 'أريحا' ? 'selected' : '' }}>أريحا</option>
                                    </select>
                                    @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="is_active" class="form-label">الحالة:</label>
                                    <select name="is_active" class="form-control">
                                        <option value="">اختر</option>
                                        <option value="active" {{ $user->is_active == 'active' ? 'selected' : '' }}>active</option>
                                        <option value="banned" {{ $user->is_active == 'banned' ? 'selected' : '' }}>banned</option>
                                        <option value="inactive" {{ $user->is_active == 'inactive' ? 'selected' : '' }}>inactive</option>
                                    </select>
                                    @error('is_active')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label for="photo" class="form-label">Photo:</label>
                                    <input type="file" name="photo" class="form-control" accept="image/*">
                                    @error('photo')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
