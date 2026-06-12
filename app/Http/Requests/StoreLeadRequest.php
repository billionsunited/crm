<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeadRequest extends FormRequest
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
        $isAdmin = auth()->check() && auth()->user()->isAdmin();

        $rules = [
            // Customer Details
            'customer_name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'email_id' => 'nullable|email|max:255',

            // Leads Details
            'master_service_agreement_signed' => 'nullable|boolean',
            'lead_status' => 'nullable|in:Active,Non Active',
            'kyc' => 'nullable|in:Done,Not Done',

            // Contact Info
            'alternate_mobile' => 'nullable|string|max:20',
            'alternate_email_id' => 'nullable|email|max:255',
            'designation' => 'nullable|string|max:255',

            // Company Info
            'company_name' => 'nullable|string|max:255',
            'company_address' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'gst_no' => 'nullable|string|max:255',
            'pan_number' => 'nullable|string|max:255',
            'aadhar_no' => 'nullable|string|max:255',
            'udyam_registration_certificate' => 'nullable|string|max:255',

            'nature_of_industry' => 'nullable|string|max:255',
            'company_type' => 'nullable|string|max:255',
            'customer_type' => 'nullable|in:Enquiry,1st Time,Loyal,Premium,Discount/Bargain Hunter,Need Base,Unqualified',
            'initial_product_interest' => 'nullable|array',
            'initial_product_interest.*' => 'in:Data,SMS,RCS,Whatsapp',
            'product_demand' => 'nullable|string',
            'quantity' => 'nullable|string|max:255',
            'rate' => 'nullable|string|max:255',

            // Tracking Info
            'follow_up_date' => 'nullable|date',
            'previous_deals_and_date' => 'nullable|date',
            'records_owner' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string',

            // Documents
            'doc_pan' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_aadhar' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_gst' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_certificate_incorporation_udyam' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_trai_dlt' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_dsa_license' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'doc_company_id_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'msa_document' => 'nullable|file',
        ];

        if ($isAdmin) {
            $rules['admin_comment'] = 'nullable|string';
            $rules['admin_rate'] = 'nullable|string|max:255';
        }

        return $rules;
    }
}
