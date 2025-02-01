@extends('dashboard')
@section('title', "show User")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Show Users
                        </h3>
                        <div class="card-tools">

                            @if ($user->photos->count() > 0)
                                <img src="{{ asset('storage/' . $user->photos->first()->src) }}" class="img-circle elevation-2" alt="Profile Picture" width="70">
                            @else
                                <img src="{{ asset('images/default-profile-picture.png') }}" alt="Profile Picture">

                            @endif
                        </div>
                    </div>

                    <div class="card-body table-responsive p-2">
                        <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <div class="form-inline">
                                <div class="form-group col-md-4">
                                    <label for="user_name">Username:</label>
                                    <input type="text" name="user_name" class="form-control col-md-12" value="{{ $user->user_name }}" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="name">Name:</label>
                                    <input type="text" name="name" class="form-control col-md-12" value="{{ $user->name }}" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="gender">Gender:</label>
                                    <select name="gender" class="form-control col-md-12" disabled>
                                        <option value="male" {{ $user->gender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $user->gender == 'female' ? 'selected' : '' }}>Female</option>
                                        <option value="other" {{ $user->gender == 'other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>

                            </div>
                            <div class="form-inline">
                                <div class="form-group col-md-4">
                                    <label for="date_of_birth">Date of Birth:</label>
                                    <input type="datetime-local" name="date_of_birth" class="form-control col-md-12" value="{{ date('Y-m-d H:i:s', strtotime($user->date_of_birth)) }}" disabled>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="location_latitudes">Latitude:</label>
                                    <input type="text" name="location_latitudes" class="form-control col-md-12" value="{{ $user->location_latitudes }}" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="location_longitudes">Longitude:</label>
                                    <input type="text" name="location_longitudes" class="form-control col-md-12" value="{{ $user->location_longitudes }}" disabled>
                                </div>
                            </div>
                            <div class="form-inline">
                                <div class="form-group col-md-4">
                                    <label for="phones">Phone Number:</label>
                                    <input type="text" name="phones" class="form-control col-md-12" value="{{ $user->phones }}" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="WatsNumber">Wats Number:</label>
                                    <input type="text" name="WatsNumber" class="form-control col-md-12" value="{{ $user->WatsNumber }}" disabled>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="city">City:</label>
                                    <select name="city" id="city" class="form-control @error('city') is-invalid @enderror col-md-12" disabled>
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
                                        <option value="{{ old('city', $user->city) === 'صفد' ? 'selected' : '' }}صفد">صفد</option>
                                        <option value="{{ old('city', $user->city) === 'بيسان' ? 'selected' : '' }}بيسان">بيسان</option>
                                        <option value="{{ old('city', $user->city) === 'اللد' ? 'selected' : '' }}اللد">اللد</option>
                                        <option value="{{ old('city', $user->city) === 'الرملة' ? 'selected' : '' }}الرملة">الرملة</option>
                                        <option value="طولكرم{{ old('city', $user->city) === 'طولكرم' ? 'selected' : '' }}">طولكرم</option>
                                        <option value="{{ old('city', $user->city) === 'قلقيلية' ? 'selected' : '' }}قلقيلية">قلقيلية</option>
                                        <option value="عكا{{ old('city', $user->city) === 'عكا' ? 'selected' : '' }}">عكا</option>
                                        <option value="حيفا{{ old('city', $user->city) === 'حيفا' ? 'selected' : '' }}">حيفا</option>
                                        <option value="يافا{{ old('city', $user->city) === 'يافا' ? 'selected' : '' }}">يافا</option>
                                        <option value="{{ old('city', $user->city) === 'أريحا' ? 'selected' : '' }}أريحا">أريحا</option>
                                    </select>
                                    @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-inline">
                                <div class="form-group col-md-4">
                                    <label for="email">Email:</label>
                                    <input type="email" name="email" id="email" class="form-control col-md-12"  value="{{ $user->email }}"  disabled>
                                    @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="password">Password:</label>
                                    <input type="password" name="password" id="password"  class="form-control col-md-12" value="{{ $user->password }}" disabled>
                                    @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="password_confirmation">Confirm Password:</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control col-md-12" value="{{ $user->password }}" disabled>
                                    @error('password_confirmation')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>


                        </form>

                        <div class="panel-heading" style="display:flex; justify-content:center;align-items:center;">

                        </div>
                    </div>

                    <div class="card-footer ">
                        <a href="{{ url()->previous() }}" class="btn btn-primary ">Back</a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
