<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VendorRegistrationRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VendorRegistrationController extends Controller
{
    /**
     * Handle incoming Vendor Registration data from external website
     */
    public function handle(VendorRegistrationRequest $request)
    {
        // Increase memory limit for processing large uploads
        ini_set('memory_limit', '1024M');

        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $vendorName = $request->input('vendor_name');
            $contactPerson = $request->input('contact_person');

            Log::info('Vendor Registration API received request', [
                'email' => $email,
                'vendor_name' => $vendorName,
                'contact_person' => $contactPerson,
                'has_contact_person' => $request->has('contact_person'),
                'received_keys' => array_keys($request->all())
            ]);

            // 1. Find or create Customer
            $mobile = $request->input('mobile_no');
            $customer = Customer::findByMobileAndContext($mobile, 'VENDOR REGISTRATION');

            if (!$customer) {
                $customer = Customer::create([
                    'email_id' => $email,
                    'client_name' => $vendorName,
                    'company_name' => $vendorName,
                    'mobile_no' => $mobile,
                    'place' => $request->input('place'),
                    'registered_address' => $request->input('vendor_address'),
                    'ip_address' => $request->input('ip_address'),
                ]);
            }

            // 2. Handle File Uploads
            $documentPaths = [];
            $fileFields = [
                'vendor_agreement' => 'msa_document',
                'msa_document' => 'msa_document',
                'vendor_registration' => 'msa_document', // Kept for backward compatibility with previous prompt
            ];

            foreach ($fileFields as $requestKey => $dbColumn) {
                if ($request->hasFile($requestKey)) {
                    $file = $request->file($requestKey);
                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = $file->storeAs('vendor_kyc_docs', $filename, 'public');
                    $documentPaths[$dbColumn] = $path;
                    Log::info("Document uploaded via Vendor Registration API: $requestKey -> $path");
                }
            }

            // 3. Prepare Lead Data
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
                'creation_source' => 'VENDOR REGISTRATION',
                'comment' => 'Vendor Registration (Submitted via Website)',
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
                'message' => 'Vendor Registration successfully received.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Vendor Registration API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'A server error occurred while processing the request.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
