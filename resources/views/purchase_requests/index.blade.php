@extends('dashboard')
@section('title', "Order")
@section('content')
<div class="p-0">
    <div class="row justify-content-center">
    <div class="col-md-12">
    <div class="card">
    <div class="card-header ">
        <h3 class="card-title">
            <i class="fas fa-users mr-1"></i>
            <h3 class="float-left"> Points Order Request</h3>
        </h3>
        <div class="card-tools">
            <ul class="nav nav-pills ml-auto">
                <li class="nav-item">
                    <form action="{{ route('purchase_points.search') }}" method="GET" class="input-group mt-0 input-group-sm">
                        <input type="text" class="form-control" name="search" placeholder="Search users...">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
    </div>

            <div class="card-body table-responsive p-0">
                <table class="table table-bordered table-striped table-dark table-sm text-center" >
                    <thead>
                    <tr class="btn-dark " >
                        <th>id</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Points</th>
                        <th>price</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @if(is_countable($purchaseRequests) && count($purchaseRequests) > 0)
                        @foreach ($purchaseRequests as $purchaseRequest)
                            <tr>
                                @if($purchaseRequest->user)
                                    <td>{{ $purchaseRequest->user->id }}</td>
                                    <td>{{ $purchaseRequest->user->user_name }}</td>
                                    <td>
                                        @if(count($purchaseRequest->user->roles) > 0)
                                            @foreach($purchaseRequest->user->roles as $role)
                                                {{$role->name}}
                                            @endforeach
                                        @endif
                                    </td>
                                @else
                                    <td colspan="3">User Not Found</td>
                                @endif
                                <td>{{ $purchaseRequest->points_requested }}</td>
                                <td>{{ $purchaseRequest->price_per_point }}</td>
                                <td>{{ $purchaseRequest->total_price }}</td>
                                <td>{{ $purchaseRequest->status }}</td>
                                <td>
                                    @if ($purchaseRequest->status == 'pending')
                                        <form action="{{ route('purchase_points.approved', $purchaseRequest) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="user_id" value="{{ $purchaseRequest->user_id }}">
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form action="{{ route('purchase_points.cancel', $purchaseRequest) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="7">{{ __('No Records') }}</td>
                        </tr>
                    </tbody>

                @endif
                    <tr class="btn-dark " >
                        <th>id</th>
                        <th>User</th>
                        <th>Role</th>
                        <th>Points</th>
                        <th>price</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </table>
            </div>
             <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $purchaseRequests->links() }}
                            </div>
                     </div>
        
           </div>
         </div>
    </div>
</div>
@endsection

