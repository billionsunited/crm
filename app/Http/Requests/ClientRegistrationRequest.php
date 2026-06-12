<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClientRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name'           => ['required', 'string', 'max:255'],
            'agreement_date'        => ['nullable', 'date'],
            'company_name'          => ['nullable', 'string', 'max:255'],
            'place'                 => ['nullable', 'string', 'max:255'],
            'registered_address'    => ['nullable', 'string'],
            'mobile_no'             => ['nullable', 'string', 'max:255'],
            'firm_type'             => ['nullable', 'string', 'max:255'],
            'email_id'              => ['nullable', 'email', 'max:255'],
            'ip_address'            => ['nullable', 'string'],
            'registration_document' => ['nullable', 'file', 'max:10240'], // 10MB limit
            'signature_path'        => ['nullable', 'file', 'max:5120'],  // 5MB limit
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors(),
            ], 422)
        );
    }
}
