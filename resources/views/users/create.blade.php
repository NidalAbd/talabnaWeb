@extends('dashboard')
@section('title', "create User")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            Create Users
                        </h3>
                        <div class="card-tools">
                               <img src="{{ asset('photos/avatar6.png') }}" class="brand img-circle elevation-2" alt="User photo" width="75">

                        </div>
                    </div>

                    <div class="card-body table-responsive p-0">
                        <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="user_name">User Name:</label>
                                    <input type="text" name="user_name" id="user_name" class="form-control col-md-12" required>
                                    @error('user_name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="name">Name:</label>
                                    <input type="text" name="name" id="name" class="form-control col-md-12" required>
                                    @error('name')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="gender">Gender:</label>
                                    <select name="gender" id="gender" class="form-control col-md-12" required>
                                        <option value="">-- Select Gender --</option>
                                        <option value="ذكر">ذكر</option>
                                        <option value="انثى">انثى</option>
                                    </select>
                                    @error('gender')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="date_of_birth">Date of Birth:</label>
                                    <input type="date" name="date_of_birth" id="date_of_birth" class="form-control col-md-12" {{ date('Y-m-d H:i:s') }}>
                                    @error('date_of_birth')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="location_latitudes">Location Latitudes:</label>
                                    <input type="text" name="location_latitudes" id="location_latitudes" class="form-control col-md-12">
                                    @error('location_latitudes')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="location_longitudes">Location Longitudes:</label>
                                    <input type="text" name="location_longitudes" id="location_longitudes" class="form-control col-md-12">
                                    @error('location_longitudes')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="phones">Phones:</label>
                                    <input type="number" name="phones" id="phones" class="form-control col-md-12" pattern="[0-9]+" minlength="10" maxlength="12">
                                     @error('phones')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="phones">Wats Number:</label>
                                    <input type="number" name="WatsNumber" id="WatsNumber" class="form-control col-md-12" pattern="[0-9]+" minlength="10" maxlength="12">
                                    @error('WatsNumber')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="city">city : </label>
                                    <select name="city" id="city" class="form-control col-md-12">
                                        <option value="">-- Select a city --</option>
                                        <option value="القدس">القدس</option>
                                        <option value="شمال غزة">شمال غزة</option>
                                        <option value="غزة">غزة</option>
                                        <option value="دير البلح">دير البلح</option>
                                        <option value="خان يونس">خان يونس</option>
                                        <option value="رفح">رفح</option>
                                        <option value="رام الله">رام الله</option>
                                        <option value="الخليل">الخليل</option>
                                        <option value="بيت لحم">بيت لحم</option>
                                        <option value="نابلس">نابلس</option>
                                        <option value="جنين">جنين</option>
                                        <option value="سلفيت">سلفيت</option>
                                        <option value="عسقلان">عسقلان</option>
                                        <option value="بئر السبع">بئر السبع</option>
                                        <option value="طبريا">طبريا</option>
                                        <option value="الناصرة">الناصرة</option>
                                        <option value="صفد">صفد</option>
                                        <option value="بيسان">بيسان</option>
                                        <option value="اللد">اللد</option>
                                        <option value="الرملة">الرملة</option>
                                        <option value="طولكرم">طولكرم</option>
                                        <option value="قلقيلية">قلقيلية</option>
                                        <option value="عكا">عكا</option>
                                        <option value="حيفا">حيفا</option>
                                        <option value="يافا">يافا</option>
                                        <option value="أريحا">أريحا</option>
                                    </select>
                                    @error('city')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                            <div class="form-inline m-2">
                                <div class="form-group col-md-4">
                                    <label for="email">Email:</label>
                                    <input type="email" name="email" id="email" class="form-control col-md-12" required>
                                    @error('email')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="password">Password:</label>
                                    <input type="password" name="password" id="password" class="form-control col-md-12" required>
                                    @error('password')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="password_confirmation">Confirm Password:</label>
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control col-md-12" required>
                                    @error('password_confirmation')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group items-center col-md-6">
                                    <label for="photo">Photo:</label>
                                    <input type="file" name="photo" id="photo" accept="image/*" class="form-control-file col-md-12" required>
                                    <small class="form-text text-muted">Maximum file size: 2.5 MB</small>
                                    @error('photo')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group col-md-6 items-center">
                                    <button type="submit" class="btn btn-primary col-md-6">Create</button>
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

