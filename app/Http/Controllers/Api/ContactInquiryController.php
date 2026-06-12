<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CampaignLead;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ContactInquiryController extends Controller
{
    public function handle(Request $request)
    {
        Log::info('Received Contact Inquiry API Request', $request->all());

        try {
            // Map incoming request to CampaignLead model fields
            $campaignLead = CampaignLead::create([
                'customer_name' => $request->full_name ?? 'N/A',
                'email_id' => $request->email,
                'mobile' => $request->phone,
                'company_name' => $request->company_name,
                'type_of_firm' => $request->firm_type,
                'product_interested' => $request->product,
                'comment' => "Help Option: " . ($request->help_option ?? 'N/A') . "\n" . 
                                        "IP: " . ($request->ip_address ?? $request->ip()),
                'rate' => 'Enquiry', // Default lead type for website inquiries
                'source' => $request->source ?? 'Website',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Contact inquiry saved successfully as a campaign lead.',
                'lead_id' => $campaignLead->id
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error processing Contact Inquiry API: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to process contact inquiry.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
