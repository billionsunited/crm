<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ClientMsaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_name' => ['required', 'string', 'max:255'],
            'agreement_date' => ['nullable', 'date'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'place' => ['nullable', 'string', 'max:255'],
            'registered_address' => ['nullable', 'string'],
            'mobile_no' => ['nullable', 'string', 'max:255'],
            'firm_type' => ['nullable', 'string', 'max:255'],
            'email_id' => ['nullable', 'email', 'max:255'],

            'pan_card_copy' => ['nullable'],
            'doc_pan' => ['nullable'],
            'aadhar_card' => ['nullable'],
            'doc_aadhar' => ['nullable'],
            'gst_registration_certificate' => ['nullable'],
            'doc_gst' => ['nullable'],
            'certificate_of_incorporation' => ['nullable'],
            'doc_certificate_incorporation_udyam' => ['nullable'],
            'trai_dlt_certificate' => ['nullable'],
            'doc_trai_dlt' => ['nullable'],
            'dsa_license_certificate' => ['nullable'],
            'doc_dsa_license' => ['nullable'],
            'company_id_card' => ['nullable'],
            'doc_company_id_card' => ['nullable'],
            'msa_document' => ['nullable'],
            'signed_msa' => ['nullable'],
            'msa_file' => ['nullable'],
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}