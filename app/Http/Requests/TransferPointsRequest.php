<?php

namespace App\Http\Requests;

use App\Services\PointsService;
use Illuminate\Foundation\Http\FormRequest;

class TransferPointsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->is_active === 'active';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'to_user_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
            'amount' => [
                'required',
                'integer',
                'min:1',
                'max:' . PointsService::MAX_POINTS_PER_TRANSFER,
            ],
            'pin' => [
                'required',
                'string',
                'size:4',
                'regex:/^[0-9]{4}$/',
            ],
            'idempotency_key' => [
                'required',
                'string',
                'min:32',
                'max:64',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Prevent self-transfer
            if ($this->to_user_id == $this->user()->id) {
                $validator->errors()->add('to_user_id', 'Cannot transfer points to yourself');
            }

            // Check if recipient is active
            $recipient = \App\Models\User::find($this->to_user_id);
            if ($recipient && $recipient->is_active !== 'active') {
                $validator->errors()->add('to_user_id', 'Recipient account is not active');
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'to_user_id.required' => 'Recipient user ID is required',
            'to_user_id.exists' => 'Recipient user does not exist',
            'amount.required' => 'Transfer amount is required',
            'amount.min' => 'Transfer amount must be at least 1 point',
            'amount.max' => 'Transfer amount cannot exceed ' . PointsService::MAX_POINTS_PER_TRANSFER . ' points',
            'pin.required' => 'Transfer PIN is required',
            'pin.size' => 'PIN must be exactly 4 digits',
            'pin.regex' => 'PIN must contain only numbers',
            'idempotency_key.required' => 'Idempotency key is required for transaction safety',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'to_user_id' => 'recipient',
            'amount' => 'transfer amount',
            'pin' => 'transfer PIN',
            'idempotency_key' => 'transaction key',
        ];
    }
}
