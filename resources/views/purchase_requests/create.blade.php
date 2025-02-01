@extends('dashboard')
@section('title', "Purchase Point")
@section('content')
    <div class="p-0">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card">
                    <form action="{{ route('purchase_points.store') }}" method="POST">
                        @csrf
                        <div class="card-header">{{ __('Purchase Point') }}</div>
                        <div class="card-body table-responsive p-0">
                            <div class="form-inline  m-2">
                                <div class="form-group col-md-6">
                                    <label for="points_requested">Points Needed : </label>
                                    <input type="hidden" name="user_id" value="{{ $userId }}">
                                    <input type="number" class="form-control col-md-6" id="points_requested"
                                           name="points_requested" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <span class="form-group col-md-4"><strong>Total Price:</strong></span>
                                    <span class="text-black" id="total_price">0</span>
                                </div>

                            </div>
                            <div class="form-inline m-2">
                                    <div class="form-group col-md-2">
                                        <label for="payment_method">{{ __('Payment Method  :') }}</label>
                                    </div>
                                    <div class="row justify-content-evenly">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method"
                                                   id="payment_method_request" value="request" checked>
                                            <label class="form-check-label"
                                                   for="payment_method_request">{{ __('Request') }}</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="payment_method"
                                                   id="payment_method_card" value="card" disabled >
                                            <label class="form-check-label" for="payment_method_card">{{ __('Credit Card') }}</label>
                                        </div>
                                    </div>
                            </div>

                            <div class="container card-form p-0" style="display: none;">
                                <div class="card px-4" style="background-color: #0c525d">
                                    <p class="h8 py-3">Payment Details</p>
                                    <div class="row gx-3">
                                        <div class="col-12">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">Person Name</p>
                                                <input class="form-control mb-3" type="text" placeholder="Name" >
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">Card Number</p>
                                                <input class="form-control mb-3" type="text" placeholder="1234 5678 435678">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">Expiry Date</p>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" placeholder="MM" aria-label="Expiry Month">
                                                    <span class="input-group-text">/</span>
                                                    <input type="text" class="form-control" placeholder="YYYY" aria-label="Expiry Year">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex flex-column">
                                                <p class="text mb-1">CVV/CVC</p>
                                                <input class="form-control mb-3 pt-2" type="password" placeholder="***">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer ">
                            <div class="row justify-content-between m-2">
                                    <a href="{{ url()->previous() }}" class="btn btn-primary form-group col-md-4">Back</a>
                                <span></span>
                                    <button id="payButton" class="btn btn-primary form-group col-md-4 mb-3">
                                        <span class="ps-3">Make Request</span>
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
