<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorRegistrationRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'agreement_date' => 'required|date',
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'required|string',
            'place' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email_id' => 'required|email|max:255',
            'vendor_agreement' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'msa_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'ip_address' => 'nullable|string|max:45',
            'contact_person' => 'nullable|string|max:255',
            'state_name' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'agreement_date.required' => 'Date is required.',
            'agreement_date.date' => 'Invalid date.',
            'vendor_name.required' => 'Company Name is required.',
            'vendor_address.required' => 'Registered Address is required.',
            'place.required' => 'Place is required.',
            'mobile_no.required' => 'Mobile No is required.',
            'email_id.required' => 'Email ID is required.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        \Illuminate\Support\Facades\Log::warning('Vendor Registration Validation Failed', [
            'errors' => $validator->errors()->toArray(),
        ]);

        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors occurred.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}
