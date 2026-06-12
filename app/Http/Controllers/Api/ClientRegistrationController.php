<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientRegistrationRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientRegistrationController extends Controller
{
    public function handle(ClientRegistrationRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $mobile = $request->input('mobile_no');
            $clientName = $request->input('client_name');
            $companyName = $request->input('company_name');

            // Find or Create Customer
            $customer = Customer::findByMobileAndContext($mobile, 'CLIENT KYC');

            $customerData = [
                'client_name' => $clientName,
                'company_name' => $companyName,
                'place' => $request->input('place'),
                'registered_address' => $request->input('registered_address'),
                'mobile_no' => $mobile,
                'email_id' => $email,
                'ip_address' => $request->input('ip_address'),
            ];

            // Handle Signature
            if ($request->hasFile('signature_path')) {
                $file = $request->file('signature_path');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('signatures', $filename, 'public');
                $customerData['signature_path'] = 'storage/' . $path;
            }

            if ($customer) {
                if (isset($customerData['signature_path'])) {
                    $customer->update($customerData);
                } else {
                    $customer->update(collect($customerData)->except('signature_path')->toArray());
                }
            } else {
                $customer = Customer::create($customerData);
            }

            // Create Lead in Client KYC context
            $leadData = [
                'customer_id' => $customer->id,
                'customer_name' => $clientName,
                'company_name' => $companyName,
                'company_type' => $request->input('firm_type'),
                'city' => $request->input('place'),
                'company_address' => $request->input('registered_address'),
                'mobile' => $mobile,
                'email_id' => $email,
                'lead_status' => 'Active',
                'customer_type' => 'Enquiry',
                'creation_source' => 'CLIENT REGISTRATION', // Specific source as requested
                'master_service_agreement_signed' => 1,
                'kyc' => 'Not Done',
                'comment' => 'Source: Client Registration API',
            ];

            if ($request->filled('agreement_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('agreement_date'));
                    $leadData['created_at'] = $dateObj;
                    $leadData['updated_at'] = $dateObj;
                } catch (\Exception $e) {
                    // Fallback to current time
                }
            }

            // Handle Registration Document
            if ($request->hasFile('registration_document')) {
                $path = $request->file('registration_document')->store('documents/leads', 'public');
                $leadData['msa_document'] = $path; // Store as MSA document equivalent
            }

            $lead = Lead::create($leadData);

            DB::commit();

            Log::info('Client Registration API processed successfully', [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Client Registration received and lead created successfully.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Client Registration API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'A server error occurred while processing the client registration.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
