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
            $customer = Customer::where('mobile_no', $mobile)->first();

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
            $extractedNumbers = [];
            $fileFields = [
                'vendor_agreement' => 'msa_document',
                'msa_document' => 'msa_document',
                'vendor_registration' => 'msa_document', // Kept for backward compatibility with previous prompt
                'pan_card_copy' => 'doc_pan',
                'aadhar_card' => 'doc_aadhar',
                'gst_certificate' => 'doc_gst',
                'certificate_of_incorporation' => 'doc_certificate_incorporation_udyam',
                'doc_pan' => 'doc_pan',
                'doc_aadhar' => 'doc_aadhar',
                'doc_gst' => 'doc_gst',
                'doc_certificate_incorporation_udyam' => 'doc_certificate_incorporation_udyam',
            ];

            $extractionService = app(\App\Services\DocumentNumberExtractionService::class);
            $ocrMap = [
                'doc_pan' => ['type' => 'PAN', 'field' => 'pan_number'],
                'doc_aadhar' => ['type' => 'Aadhaar', 'field' => 'aadhar_no'],
                'doc_gst' => ['type' => 'GST', 'field' => 'gst_no'],
                'doc_certificate_incorporation_udyam' => ['type' => 'Udyam', 'field' => 'udyam_registration_certificate'],
            ];

            foreach ($fileFields as $requestKey => $dbColumn) {
                if ($request->hasFile($requestKey)) {
                    $file = $request->file($requestKey);

                    // Compress in place BEFORE OCR
                    \App\Services\ImageCompressionService::compressInPlace($file);

                    // API OCR Extraction
                    if (isset($ocrMap[$dbColumn])) {
                        try {
                            $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$dbColumn]['type']);
                            if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                $extractedNumbers[$ocrMap[$dbColumn]['field']] = $ocrResult['document_number'];
                                Log::info("OCR Extracted {$ocrMap[$dbColumn]['type']} number via Vendor Registration API");
                            }
                        } catch (\Exception $e) {
                            Log::error("API OCR Extraction failed for {$dbColumn}: " . $e->getMessage());
                        }
                    }

                    $filename = time() . '_' . $file->getClientOriginalName();
                    $path = \App\Services\ImageCompressionService::compressAndStore($file, 'vendor_kyc_docs', 'public', $filename);
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
            ], $documentPaths, $extractedNumbers);

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
