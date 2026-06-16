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
                'state_name' => $request->input('state_name'),
                'state_code' => $request->input('state_code'),
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

            // Handle Registration Documents
            $fileFields = [
                'registration_document' => 'msa_document', // Store as MSA document equivalent
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

                    // API OCR Extraction
                    if (isset($ocrMap[$dbColumn])) {
                        try {
                            $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$dbColumn]['type']);
                            if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                $leadData[$ocrMap[$dbColumn]['field']] = $ocrResult['document_number'];
                                Log::info("OCR Extracted {$ocrMap[$dbColumn]['type']} number via Client Registration API");
                            }
                        } catch (\Exception $e) {
                            Log::error("API OCR Extraction failed for {$dbColumn}: " . $e->getMessage());
                        }
                    }

                    $path = $file->store('documents/leads', 'public');
                    $leadData[$dbColumn] = $path;
                }
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
