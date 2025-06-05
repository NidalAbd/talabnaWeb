<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BanDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorize admin users only (implement based on your roles/permissions)
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'device_id' => 'required|string|max:255',
            'reason' => 'required|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'device_name' => 'nullable|string|max:255',
            'device_brand' => 'nullable|string|max:255',
            'device_model' => 'nullable|string|max:255',
            'os_version' => 'nullable|string|max:255',
        ];
    }
}
