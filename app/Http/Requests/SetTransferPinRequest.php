<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetTransferPinRequest extends FormRequest
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
            'pin' => [
                'required',
                'string',
                'size:4',
                'regex:/^[0-9]{4}$/',
            ],
            'confirm_pin' => [
                'required',
                'string',
                'same:pin',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $pin = $this->pin;

            // Check for common weak PINs
            $weakPins = ['0000', '1111', '2222', '3333', '4444', '5555', '6666', '7777', '8888', '9999', '1234', '4321', '0123', '9876'];

            if (in_array($pin, $weakPins)) {
                $validator->errors()->add('pin', 'Please choose a stronger PIN. Avoid common patterns like 1234 or repeated digits.');
            }

            // Check for sequential numbers
            if ($this->isSequential($pin)) {
                $validator->errors()->add('pin', 'Please avoid sequential numbers in your PIN.');
            }
        });
    }

    /**
     * Check if PIN contains sequential numbers
     */
    private function isSequential(string $pin): bool
    {
        $ascending = true;
        $descending = true;

        for ($i = 0; $i < 3; $i++) {
            if ((int)$pin[$i + 1] !== (int)$pin[$i] + 1) {
                $ascending = false;
            }
            if ((int)$pin[$i + 1] !== (int)$pin[$i] - 1) {
                $descending = false;
            }
        }

        return $ascending || $descending;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'pin.required' => 'PIN is required',
            'pin.size' => 'PIN must be exactly 4 digits',
            'pin.regex' => 'PIN must contain only numbers',
            'confirm_pin.required' => 'Please confirm your PIN',
            'confirm_pin.same' => 'PIN confirmation does not match',
        ];
    }
}
