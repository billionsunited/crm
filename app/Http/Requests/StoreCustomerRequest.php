<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'client_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'registered_address' => 'nullable|string',
            'mobile_no' => 'nullable|string|max:20',
            'email_id' => 'nullable|email|max:255',
        ];
    }
}
