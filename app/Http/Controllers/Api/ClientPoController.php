<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientPoRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientPoController extends Controller
{
    public function handle(ClientPoRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('authorised_recipient_email');
            $contactPerson = $request->input('contact_person');
            $orgName = $request->input('organization_name');

            Log::info('Client PO API received request', [
                'email' => $email,
                'contact_person' => $contactPerson,
                'organization' => $orgName
            ]);

            $customerData = [
                'client_name' => $contactPerson,
                'company_name' => $orgName,
                'place' => $request->input('client_place'),
                'registered_address' => $request->input('registered_address'),
                'email_id' => $email,
                'ip_address' => $request->input('ip_address'),
            ];

            if ($request->hasFile('signature_path')) {
                $file = $request->file('signature_path');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('signatures', $filename, 'public');
                $customerData['signature_path'] = 'storage/' . $path;
                Log::info('Signature saved for customer', ['path' => $customerData['signature_path']]);
            }

            $mobile = $request->input('mobile_no');
            $customer = Customer::findByMobileAndContext($mobile, 'CLIENT P.O');

            if ($customer) {
                // Update signature path only if provided in request
                if (isset($customerData['signature_path'])) {
                    $customer->update($customerData);
                } else {
                    // Update other fields but preserve existing signature
                    $customer->update(collect($customerData)->except('signature_path')->toArray());
                }
                Log::info('Client PO API linked to existing customer', ['customer_id' => $customer->id]);
            } else {
                $customer = Customer::create($customerData);
                Log::info('Client PO API created fresh customer', ['customer_id' => $customer->id]);
            }

            // Lead creation
            $leadData = [
                'customer_id' => $customer->id,
                'customer_name' => $contactPerson,
                'company_name' => $orgName,
                'city' => $request->input('client_place'),
                'company_address' => $request->input('registered_address'),
                'email_id' => $email,
                'lead_status' => 'Active',
                'customer_type' => 'Enquiry',
                'creation_source' => 'CLIENT P.O',
                'master_service_agreement_signed' => 1,
                'kyc' => 'Done',
            ];

            if ($request->filled('sow_effective_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('sow_effective_date'));
                    $leadData['created_at'] = $dateObj;
                    $leadData['updated_at'] = $dateObj;
                } catch (\Exception $e) {
                    // Fallback to current timestamps if date is invalid
                }
            }

            $lead = Lead::create($leadData);

            DB::commit();

            Log::info('Client PO API created lead successfully', [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'PO data securely received and CRM lead created.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Client PO API Error: ' . $e->getMessage(), [
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
