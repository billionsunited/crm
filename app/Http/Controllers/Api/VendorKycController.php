<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorKycRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VendorKycController extends Controller
{
    /**
     * Handle incoming Vendor KYC data from external website
     */
    public function handle(VendorKycRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $vendorName = $request->input('vendor_name');
            $contactPerson = $request->input('contact_person');

            Log::info('Vendor KYC API received request', [
                'email' => $email, 
                'vendor_name' => $vendorName,
                'contact_person' => $contactPerson
            ]);

            // 1. Find or create Customer
            $mobile = $request->input('mobile_no');
            $customer = Customer::findByMobileAndContext($mobile, 'VENDOR KYC API');
            if (!$customer) {
                $customer = Customer::create([
                    'email_id' => $email,
                    'client_name' => $vendorName,
                    'company_name' => $vendorName,
                    'mobile_no' => $mobile,
                    'place' => $request->input('place'),
                    'registered_address' => $request->input('vendor_address'),
                    'ip_address' => $request->input('ip_address'),
                    'state_name' => $request->input('state_name'),
                    'state_code' => $request->input('state_code'),
                ]);
            } else {
                $updateData = [];
                if ($request->filled('state_name')) $updateData['state_name'] = $request->input('state_name');
                if ($request->filled('state_code')) $updateData['state_code'] = $request->input('state_code');
                
                if (!empty($updateData)) {
                    $customer->update($updateData);
                }
            }

            // 2. Handle File Uploads
            $documentPaths = [];
            $fileFields = [
                'pan_card_copy' => 'doc_pan',
                'aadhar_card' => 'doc_aadhar',
                'gst_certificate' => 'doc_gst',
                'certificate_of_incorporation' => 'doc_certificate_incorporation_udyam',
                'vendor_agreement' => 'msa_document',
            ];

            foreach ($fileFields as $requestKey => $dbColumn) {
                if ($request->hasFile($requestKey)) {
                    $file = $request->file($requestKey);
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('vendor_kyc_docs', $filename, 'public');
                    $documentPaths[$dbColumn] = $path;
                    Log::info("Document uploaded via Vendor KYC API: $requestKey -> $path");
                } else {
                    // Check if maybe it's coming via the DB column name key directly
                    if ($request->hasFile($dbColumn)) {
                        $file = $request->file($dbColumn);
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('vendor_kyc_docs', $filename, 'public');
                        $documentPaths[$dbColumn] = $path;
                        Log::info("Document uploaded via Vendor KYC API (fallback key): $dbColumn -> $path");
                    }
                }
            }

            // 3. Prepare Lead Data
            $comment = "\"Vendor KYC Details (Submitted via Website): - Agreement Date: " . $request->input('agreement_date') .
                " - Place: " . $request->input('place') .
                " - Mobile: " . $request->input('mobile_no') . " \"";

            $leadData = array_merge([
                'customer_id' => $customer->id,
                'email_id' => $email,
                'customer_name' => $vendorName,
                'contact_person' => $contactPerson,
                'company_name' => $vendorName,
                'company_address' => $request->input('vendor_address'),
                'mobile' => $request->input('mobile_no'),
                'city' => $request->input('place'),
                'lead_status' => 'Active',
                'customer_type' => 'Enquiry',
                'creation_source' => 'VENDOR KYC API',
                'comment' => '',
                'kyc' => 'Done',
                'master_service_agreement_signed' => 1,
            ], $documentPaths);

            // Handle date if provided
            if ($request->filled('agreement_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('agreement_date'));
                    $leadData['created_at'] = $dateObj;
                } catch (\Exception $e) {
                    // Fallback to now
                }
            }

            $lead = Lead::create($leadData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Vendor KYC data and documents successfully received.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor KYC API Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'A server error occurred while processing the KYC request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
