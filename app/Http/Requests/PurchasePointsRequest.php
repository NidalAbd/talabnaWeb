<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PurchasePointsRequest extends FormRequest
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
            'points_amount' => [
                'required',
                'integer',
                'min:1',
                'max:10000', // Reasonable maximum for single purchase
            ],
            'package_id' => [
                'nullable',
                'integer',
                'exists:point_packages,id',
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
            // If package_id is provided, validate it's active
            if ($this->package_id) {
                $package = \App\Models\PointPackage::find($this->package_id);
                if ($package && !$package->is_active) {
                    $validator->errors()->add('package_id', 'Selected package is no longer available');
                }
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'points_amount.required' => 'Points amount is required',
            'points_amount.min' => 'Minimum purchase is 1 point',
            'points_amount.max' => 'Maximum purchase is 10,000 points per transaction',
            'package_id.exists' => 'Selected package does not exist',
            'idempotency_key.required' => 'Idempotency key is required for payment safety',
        ];
    }
}
