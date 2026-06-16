<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClientMsaRequest;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientMsaController extends Controller
{
    public function handle(ClientMsaRequest $request)
    {
        try {
            DB::beginTransaction();

            $email = $request->input('email_id');
            $mobile = $request->input('mobile_no');
            $clientName = $request->input('client_name');
            $companyName = $request->input('company_name');

            $customer = Customer::where('mobile_no', $mobile)->first();

            $customerData = [
                'client_name' => $clientName,
                'company_name' => $companyName,
                'place' => $request->input('place'),
                'registered_address' => $request->input('registered_address'),
                'mobile_no' => $mobile,
                'email_id' => $email,
                'ip_address' => $request->input('ip_address'),
                'state_name' => $request->input('state_name'),
                'state_code' => $request->input('state_code'),
            ];

            if ($request->hasFile('signature_path')) {
                $file = $request->file('signature_path');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('signatures', $filename, 'public');
                $customerData['signature_path'] = 'storage/' . $path;
                Log::info('Signature saved for customer', ['path' => $customerData['signature_path']]);
            }

            if ($customer) {
                if (isset($customerData['signature_path'])) {
                    $customer->update($customerData);
                } else {
                    $customer->update(collect($customerData)->except('signature_path')->toArray());
                }
                Log::info('Client MSA API updated customer', ['customer_id' => $customer->id]);
            } else {
                $customer = Customer::create($customerData);
                Log::info('Client MSA API created fresh customer', ['customer_id' => $customer->id]);
            }

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
                'creation_source' => 'CLIENT KYC',
                'master_service_agreement_signed' => 1,
                'kyc' => 'Done',
            ];

            if ($request->filled('agreement_date')) {
                try {
                    $dateObj = \Carbon\Carbon::parse($request->input('agreement_date'));
                    $leadData['created_at'] = $dateObj;
                    $leadData['updated_at'] = $dateObj;
                } catch (\Exception $e) {
                    // fallback to normal timestamps
                }
            }

            $docMap = [
                'pan_card_copy' => 'doc_pan',
                'aadhar_card' => 'doc_aadhar',
                'gst_registration_certificate' => 'doc_gst',
                'certificate_of_incorporation' => 'doc_certificate_incorporation_udyam',
                'trai_dlt_certificate' => 'doc_trai_dlt',
                'dsa_license_certificate' => 'doc_dsa_license',
                'company_id_card' => 'doc_company_id_card',
                'msa_document' => 'msa_document',
                'signed_msa' => 'msa_document',
                'msa_file' => 'msa_document',
            ];

            $extractionService = app(\App\Services\DocumentNumberExtractionService::class);
            $ocrMap = [
                'doc_pan' => ['type' => 'PAN', 'field' => 'pan_number'],
                'doc_aadhar' => ['type' => 'Aadhaar', 'field' => 'aadhar_no'],
                'doc_gst' => ['type' => 'GST', 'field' => 'gst_no'],
                'doc_certificate_incorporation_udyam' => ['type' => 'Udyam', 'field' => 'udyam_registration_certificate'],
            ];

            $uploadedDocs = [];

            foreach ($docMap as $inputKey => $dbColumn) {
                if ($request->hasFile($inputKey)) {
                    $file = $request->file($inputKey);

                    // API OCR Extraction
                    if (isset($ocrMap[$dbColumn])) {
                        try {
                            $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$dbColumn]['type']);
                            if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                $leadData[$ocrMap[$dbColumn]['field']] = $ocrResult['document_number'];
                                Log::info("OCR Extracted {$ocrMap[$dbColumn]['type']} number via Client MSA API");
                            }
                        } catch (\Exception $e) {
                            Log::error("API OCR Extraction failed for {$dbColumn}: " . $e->getMessage());
                        }
                    }

                    $path = $file->store('documents/leads', 'public');
                    $leadData[$dbColumn] = $path;
                    $uploadedDocs[] = $inputKey;
                    Log::info("Document uploaded via Client MSA API: $inputKey -> $path");
                } elseif ($request->filled($inputKey) && is_string($request->input($inputKey))) {
                    $leadData[$dbColumn] = $request->input($inputKey);
                    $uploadedDocs[] = $inputKey . ' (as string)';
                } else {
                    // Check if maybe it's coming via the DB column name key directly
                    if ($request->hasFile($dbColumn)) {
                        $file = $request->file($dbColumn);

                        // API OCR Extraction
                        if (isset($ocrMap[$dbColumn])) {
                            try {
                                $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$dbColumn]['type']);
                                if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                    $leadData[$ocrMap[$dbColumn]['field']] = $ocrResult['document_number'];
                                    Log::info("OCR Extracted {$ocrMap[$dbColumn]['type']} number via Client MSA API (fallback)");
                                }
                            } catch (\Exception $e) {
                                Log::error("API OCR Extraction failed for {$dbColumn}: " . $e->getMessage());
                            }
                        }

                        $path = $file->store('documents/leads', 'public');
                        $leadData[$dbColumn] = $path;
                        $uploadedDocs[] = $dbColumn;
                        Log::info("Document uploaded via Client MSA API (fallback key): $dbColumn -> $path");
                    } elseif ($request->filled($dbColumn) && is_string($request->input($dbColumn))) {
                        $leadData[$dbColumn] = $request->input($dbColumn);
                        $uploadedDocs[] = $dbColumn . ' (as string)';
                    }
                }
            }

            $lead = Lead::create($leadData);

            DB::commit();

            Log::info('Client MSA API created lead successfully', [
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'docs' => $uploadedDocs,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Data securely received and CRM lead created.',
                'lead_id' => $lead->id,
                'customer_id' => $customer->id,
                'documents_uploaded' => $uploadedDocs,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Client MSA API Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'A server error occurred while processing the request.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}