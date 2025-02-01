@extends('dashboard')
@section('title', "Transactions Points")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header ">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-1"></i>
                            <h3 class="float-left"> Point Transactions</h3>
                        </h3>

                    </div>

                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped table-dark table-sm text-center" >
                            <thead>
                            <tr class="btn-dark " >
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('from user') }}</th>
                                <th>{{ __('To user') }}</th>
                                <th>{{ __('Points') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if(is_countable($pointTransactions) && count($pointTransactions) > 0)
                                @foreach ($pointTransactions as $pointTransaction)
                                    <tr>
                                        <td>{{ $pointTransaction->id }}</td>
                                        <td>{{ $pointTransaction->fromUser?->name ?? 'Passed User' }}</td>
                                        <td>{{ $pointTransaction->toUser?->name ?? 'Passed User' }}</td>
                                        <td>{{ $pointTransaction->point }}</td>
                                        <td>{{ $pointTransaction->type }}</td>
                                        <td>{{ $pointTransaction->created_at }}</td>
                                        <td>
                                            <form action="{{ route('point_transactions.destroy', $pointTransaction->id) }}" method="POST" style="display: inline-block;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">{{ __('Delete') }}</button>
                                            </form>
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
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('from user') }}</th>
                                <th>{{ __('To user') }}</th>
                                <th>{{ __('Points') }}</th>
                                <th>{{ __('Type') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </table>
                    </div>
                                <div class="card-footer" style="height: 50px;"> <!-- Adjust height as needed -->
                            <div class="m-0" style="display: flex; justify-content: center;">
                                {{ $pointTransactions->links() }}
                            </div>
                     </div>
                </div>
            </div>
        </div>
    </div>
@endsection

