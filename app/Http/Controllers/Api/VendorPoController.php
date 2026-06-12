<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorPoRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VendorPoController extends Controller
{
    public function handle(VendorPoRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $poNumber = $request->input('po_number');

            Log::info('Vendor PO API received request', [
                'email' => $email,
                'po_number' => $poNumber
            ]);

            // Try to find existing customer by email or create new one
            $mobile = $request->input('mobile_no');
            $customer = Customer::findByMobileAndContext($mobile, 'VENDOR PO API');

            if (!$customer) {
                $customer = Customer::create([
                    'email_id' => $email,
                    'mobile_no' => $mobile,
                    'client_name' => 'Vendor ' . $poNumber, // Placeholder name
                ]);
                Log::info('Vendor PO API created fresh customer', ['customer_id' => $customer->id]);
            }

            // Prepare details for comment
            $dataCategory = $request->input('data_category', []);
            $geographyFilter = $request->input('geography_filter', []);

            $details = [
                'PO Number' => $poNumber,
                'PO Date' => $request->input('po_date'),
                'Data Category' => implode(', ', $dataCategory),
                'Data Category (Other)' => $request->input('data_category_other_text'),
                'Geography' => implode(', ', $geographyFilter),
                'Geography (Other)' => $request->input('geography_filter_other_text'),
                'Volume' => $request->input('volume_records'),
                'Amount (excl. GST)' => $request->input('excluded_amount'),
            ];

            $comment = "Vendor PO Details:\n";
            foreach ($details as $key => $value) {
                if ($value) {
                    $comment .= "- $key: $value\n";
                }
            }

            // Lead creation
            $leadData = [
                'customer_id' => $customer->id,
                'email_id' => $email,
                'customer_name' => $customer->client_name,
                'lead_status' => 'Active',
                'customer_type' => 'Enquiry',
                'creation_source' => 'VENDOR PO API',
                'comment' => $comment,
                'kyc' => 'Not Done',
            ];

            if ($request->filled('po_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('po_date'));
                    $leadData['created_at'] = $dateObj;
                    $leadData['updated_at'] = $dateObj;
                } catch (\Exception $e) {
                    // Fallback to current timestamps
                }
            }

            $lead = Lead::create($leadData);

            DB::commit();

            Log::info('Vendor PO API created lead successfully', [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vendor PO data securely received and CRM lead created.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor PO API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'A server error occurred while processing the request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
