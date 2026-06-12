<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientTermsRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientTermsController extends Controller
{
    public function handle(ClientTermsRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $mobile = $request->input('mobile_no');
            $clientName = $request->input('client_name');
            $orgName = $request->input('organization_name');

            Log::info('Client Terms API received request', [
                'email' => $email,
                'client_name' => $clientName,
                'organization' => $orgName
            ]);

            $customer = Customer::findByMobileAndContext($mobile, 'CLIENT TERMS');

            $customerData = [
                'client_name' => $clientName,
                'company_name' => $orgName,
                'place' => $request->input('place'),
                'mobile_no' => $mobile,
                'email_id' => $email,
                'ip_address' => $request->input('ip_address'),
            ];

            if ($customer) {
                $customer->update($customerData);
                Log::info('Client Terms API linked to existing customer', ['customer_id' => $customer->id]);
            } else {
                $customer = Customer::create($customerData);
                Log::info('Client Terms API created fresh customer', ['customer_id' => $customer->id]);
            }

            // Lead creation
            $leadData = [
                'customer_id' => $customer->id,
                'customer_name' => $clientName,
                'company_name' => $orgName,
                'city' => $request->input('place'),
                'mobile' => $mobile,
                'email_id' => $email,
                'lead_status' => 'Active',
                'customer_type' => 'Enquiry',
                'creation_source' => 'CLIENT TERMS',
                'master_service_agreement_signed' => 0,
                'kyc' => 'Not Done',
            ];

            if ($request->filled('agreement_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('agreement_date'));
                    $leadData['created_at'] = $dateObj;
                    $leadData['updated_at'] = $dateObj;
                } catch (\Exception $e) {
                    // Fallback to current timestamps if date is invalid
                }
            }

            $lead = Lead::create($leadData);

            DB::commit();

            Log::info('Client Terms API created lead successfully', [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client Terms data stored successfully.',
                'customer_id' => $customer->id,
                'lead_id' => $lead->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Client Terms API Error: ' . $e->getMessage(), [
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
