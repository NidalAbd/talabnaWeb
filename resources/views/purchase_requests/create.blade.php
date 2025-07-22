@extends('adminlte::page')
@section('title', "Purchase Point")
@section('content_header')
    @include('partials.breadcrumbs')
    <div class="d-flex justify-content-between align-items-center">
        <h1><i class="fas fa-coins text-primary mr-2"></i> Points Management</h1>
        <div>
            <a href="{{ route('palservice_points.create', ['user_id' => 0]) }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add Points
            </a>
        </div>
    </div>
@stop
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <form action="{{ route('purchase_points.store') }}" method="POST">
                        @csrf
                        <div class="card-header">{{('Purchase Point') }}</div>
                        <div class="card-body table-responsive p-0">
                            <div class="form-inline  m-2">
                                <div class="form-group col-md-6">
                                    <label for="points_requested">Points Needed : </label>
                                    <input type="hidden" name="user_id" value="{{ $userId }}">
                                    <input type="number" class="form-control col-md-6" id="points_requested"
                                           name="points_requested" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <span class="form-group col-md-4"><strong>{{('purchase_requests\create.total_price_') }}</strong></span>
                                    <span class="text-black" id="total_price">0</span>
                                </div>

                            </div>
                            <div class="form-inline m-2">
                                    <div class="form-group col-md-2">
                                        <label for="payment_method">{{('Payment Method  :') }}</label>
                                    </div>
                                    <div class="row justify-content-evenly">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method"
                                                   id="payment_method_request" value="request" checked>
                                            <label class="form-check-label"
                                                   for="payment_method_request">{{('Request') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method"
                                                   id="payment_method_card" value="card" disabled >
                                            <label class="form-check-label" for="payment_method_card">{{('Credit Card') }}</label>
                                        </div>
                                    </div>
                            </div>

                            <div class="container card-form p-0" style="display: none;">
                                <div class="card px-4" style="background-color: #0c525d">
                                    <p class="h8 py-3">{{('purchase_requests\create.payment_details') }}</p>
                                    <div class="row gx-3">
                                        <div class="col-12">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">{{('purchase_requests\create.person_name') }}</p>
                                                <input class="form-control mb-3" type="text" placeholder="Name" >
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">{{('purchase_requests\create.card_number') }}</p>
                                                <input class="form-control mb-3" type="text" placeholder="1234 5678 435678">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">{{('purchase_requests\create.expiry_date') }}</p>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" placeholder="MM" aria-label="Expiry Month">
                                                    <span class="input-group-text">{{('purchase_requests\create._') }}</span>
                                                    <input type="text" class="form-control" placeholder="YYYY" aria-label="Expiry Year">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">{{('purchase_requests\create.cvv_cvc') }}</p>
                                                <input class="form-control mb-3 pt-2" type="password" placeholder="***">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer ">
                            <div class="row justify-content-between m-2">
                                    <a href="{{ url()-> __('purchase_requests\create.previous_class_btn_btn_primary_fo') }}</a>
                                <span></span>
                                    <button id="payButton" class="btn btn-primary form-group col-md-4 mb-3">
                                        <span class="ps-3">{{('purchase_requests\create.make_request') }}</span>
                                    </button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(document).ready(function () {
            const payButton = document.getElementById('payButton');
            // Listen for changes to the points_requested input field
            $('#points_requested').on('input', function () {
                var points = $(this).val();
                var price = points * 3.8;
                $('#total_price').text(price);
                payButton.innerText = `${price} Pay ILS`;
            });

            $('input[name="payment_method"]').change(function () {
                if ($(this).val() === 'card') {
                    $('.card-form').show();
                } else {
                    $('.card-form').hide();
                }
            });
        });
    </script>
@endsection







