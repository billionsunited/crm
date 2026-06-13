<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VendorKycRequest extends FormRequest
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
            'agreement_date' => 'nullable|date',
            'vendor_name' => 'required|string|max:255',
            'vendor_address' => 'required|string',
            'place' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20',
            'email_id' => 'required|email|max:255',
            'pan_card_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_pan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'aadhar_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_aadhar' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'gst_certificate' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_gst' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'certificate_of_incorporation' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'doc_certificate_incorporation_udyam' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
            'vendor_agreement' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'msa_document' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'contact_person' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'state_name' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:255',
        ];
    }
}
