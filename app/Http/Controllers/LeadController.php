<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use App\Models\LeadDocumentTracking;
use App\Models\Customer;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class LeadController extends Controller
{
    /**
     * Upload a file and send it to the customer via email
     */
    public function updateBlacklistFlag(Request $request, Lead $lead)
    {
        $request->validate([
            'blacklist_flag' => 'required|integer|min:0|max:3',
        ]);

        $lead->update([
            'blacklist_flag' => $request->blacklist_flag
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blacklist flag updated successfully',
            'blacklist_flag' => $lead->blacklist_flag
        ]);
    }

    public function sendFile(Request $request, Lead $lead)
    {
        $request->validate([
            'document' => 'required|file|max:15360', // 15MB limit
        ]);

        if (!$lead->email_id) {
            return back()->with('error', 'Lead does not have a primary email address.');
        }

        try {
            $file = $request->file('document');
            $originalName = time() . '_' . $file->getClientOriginalName(); // Prevent collisions

            // Ensure directory exists
            if (!Storage::disk('local')->exists('temp_uploads')) {
                Storage::disk('local')->makeDirectory('temp_uploads');
            }

            $path = $file->storeAs('temp_uploads', $originalName, 'local');
            $fullPath = Storage::disk('local')->path($path);

            if (!file_exists($fullPath)) {
                throw new \Exception("File was not stored correctly at: " . $fullPath);
            }

            $mail = new PHPMailer(true);

            // SMTP Settings
            $mail->isSMTP();
            $mail->Host = 'Smtp.rediffmailpro.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sales@billionsunited.com';
            $mail->Password = 'Gautam123#@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('sales@billionsunited.com', 'Billions United');
            $mail->addAddress($lead->email_id, $lead->customer_name);

            if ($lead->alternate_email_id) {
                $mail->addCC($lead->alternate_email_id);
            }
            if ($lead->alternate_email_id_2) {
                $mail->addCC($lead->alternate_email_id_2);
            }

            $mail->addBCC('salesteam@billionsunited.com');
            $mail->addBCC('sales@billionsunited.com');

            // Attachments
            $extension = pathinfo($fullPath, PATHINFO_EXTENSION);
            $currentDate = date('d-m-Y');
            $customerName = trim($lead->customer_name);
            $organizationName = trim($lead->company_name ?? '');
            $leadId = trim($lead->record_id ?? '');

            $attachmentName = $customerName;

            // Append organization name if it exists and is not 'None'
            if ($organizationName !== '' && strtoupper($organizationName) !== 'NONE') {
                $attachmentName .= " - " . $organizationName;
            }

            // Append lead ID if it exists
            if ($leadId !== '') {
                $attachmentName .= " - " . $leadId;
            }

            $attachmentName .= " - " . $currentDate . "." . $extension;

            $mail->addAttachment($fullPath, $attachmentName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Marketing Services";

            $disclaimerNote = "Note: Client shall NOT Resell, sublicense, lease, distribute, publish our services. Client shall not use our services for phishing, impersonation, fraud, financial scams or cybercrime. Client shall irrevocably and unconditionally defend, indemnify and hold harmless Billions United. Billions United, in its sole discretion, reserves the right to disconnect the services without any notice";

            $body = "
                <div style='font-family: sans-serif; color: #333; line-height: 1.6;'>
                    <p>Dear {$lead->customer_name},</p>
                    <p>Thank you for choosing our marketing services. We truly appreciate the opportunity to work with you and support your business goals.</p>
                    <p>We are excited to embark on this journey together and are committed to delivering strategies and solutions that drive meaningful results for your brand.</p>
                    <p>Should you require any further information or assistance, please do not hesitate to contact us.</p>
                    <p>Thank you once again for your association and trust in us.</p>
                    <p>Best regards,</p>
                    <p><strong>Billions United Team</strong><br>
                    <a href='https://billionsunited.com' style='color: #4f46e5; text-decoration: none;'>www.billionsunited.com</a></p>
                    <br>
                    <p style='font-size: 12px; color: #666;'>$disclaimerNote</p>
                </div>
            ";

            $mail->Body = $body;

            $mail->send();

            // Track document sent
            LeadDocumentTracking::create([
                'lead_id' => $lead->id,
                'document_name' => $originalName,
            ]);

            // Delete temp file
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return back()->with('status', 'Document has been successfully sent to the customer.');

        } catch (Exception $e) {
            Log::error("Failed to send lead document: " . $e->getMessage());
            return back()->with('error', "Failed to send email: " . ($mail->ErrorInfo ?? $e->getMessage()));
        } finally {
            // Always delete temp file if it exists
            if (isset($fullPath) && file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    /**
     * Send Client Agreement (MSA) to the customer via email
     */
    public function sendClientAgreement(Request $request, Lead $lead)
    {
        if ($lead->creation_source !== 'CLIENT REGISTRATION') {
            return back()->with('error', 'This feature is only available for Client Registration leads.');
        }

        if ($lead->is_agreement_sent) {
            return back()->with('error', 'Agreement has already been sent to this client.');
        }

        if (!$lead->msa_document) {
            return back()->with('error', 'No MSA document found for this client.');
        }

        if (!$lead->email_id) {
            return back()->with('error', 'Lead does not have a primary email address.');
        }

        try {
            $mail = new PHPMailer(true);

            // SMTP Settings (Using same as sendFile for consistency)
            $mail->isSMTP();
            $mail->Host = 'Smtp.rediffmailpro.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'sales@billionsunited.com';
            $mail->Password = 'Gautam123#@';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('hello@billionsunited.com', 'Billions United');
            $mail->addAddress($lead->email_id, $lead->customer_name);

            if ($lead->alternate_email_id) {
                $mail->addCC($lead->alternate_email_id);
            }
            if ($lead->alternate_email_id_2) {
                $mail->addCC($lead->alternate_email_id_2);
            }

            $mail->addBCC('salesteam@billionsunited.com');
            $mail->addBCC('sales@billionsunited.com');

            // Attachments
            $filePath = storage_path('app/public/' . $lead->msa_document);
            if (!file_exists($filePath)) {
                return back()->with('error', 'Agreement document file not found on server.');
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $attachmentName = "MSA-".trim($lead->customer_name);
            if (!empty($lead->company_name) && strtoupper($lead->company_name) !== 'NONE') {
                $attachmentName .= "-" . $lead->company_name;
            }

            $leadId = trim($lead->record_id ?? '');
            // if ($leadId !== '') {
            //     $attachmentName .= " - " . $leadId;
            // }

            $attachmentName .= '.'.$extension;
             
            $mail->addAttachment($filePath, $attachmentName);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Client MSA";

            $body = "
                <div style='font-family: sans-serif; color: #333; line-height: 1.6;'>
                    <p>Dear {$lead->customer_name},</p>
                    <p>Please find attached the Client (MSA) document.</p>
                    <p>We are excited to work with you and look forward to a successful association.</p>
                    <p>Should you have any questions or require further clarification, please feel free to reach out to us.</p>
                    <p>Best regards,</p>
                    <p><strong>Billions United Team</strong><br>
                    <a href='https://billionsunited.com' style='color: #4f46e5; text-decoration: none;'>www.billionsunited.com</a></p>
                </div>
            ";

            $mail->Body = $body;

            $mail->send();

            // Update status
            $lead->update(['is_agreement_sent' => true]);

            return back()->with('status', 'Client Agreement has been successfully sent.');

        } catch (Exception $e) {
            Log::error("Failed to send client agreement: " . $e->getMessage());
            return back()->with('error', "Failed to send email: " . ($mail->ErrorInfo ?? $e->getMessage()));
        }
    }


    /**
     * Single source of truth for CSV headers (Export Style)
     */
    private function csvColumns(): array
    {
        return [
            'Record ID',
            'Lead Status',
            'KYC',
            'MSA Signed',
            'Customer Type',
            'Create Date',
            'Customer Name',
            'Mobile',
            'Email ID',
            'City',
            'Company Name',
            'Company Address',
            'Nature of Industry',
            'Initial Product Interest',
            'Product Demand',
            'Quantity',
            'Rate',
            'Previous Deals & Date',
            'Follow Up Date',
            'Records Owner',
            'Reference',
            'Alternate Mobile',
            'Alternate Email Id',
            'Designation',
            'GST No',
            'PAN Number',
            'Aadhar No',
            'Udyam Registration',
            'Website',
            'Comment',

            // Documents
            'Doc PAN',
            'Doc Aadhar',
            'Doc GST',
            'Doc Udyam',
            'Doc TRAI DLT',
            'Doc DSA License',
            'Doc Company ID',
            'Doc MSA',
        ];
    }

    public function index(Request $request)
    {
        $query = Lead::with('customer')
            ->select('leads.*')
            ->addSelect([
                'original_record_id' => DB::table('leads as l2')->select('record_id')
                    ->whereColumn('l2.mobile', 'leads.mobile')
                    ->whereIn('l2.creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC', 'CLIENT REGISTRATION'])
                    ->whereNotNull('l2.mobile')
                    ->where('l2.mobile', '!=', '')
                    ->orderBy('id', 'asc')
                    ->limit(1)
            ])
            ->addSelect([
                'original_id' => DB::table('leads as l2')->select('id')
                    ->whereColumn('l2.mobile', 'leads.mobile')
                    ->whereIn('l2.creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC', 'CLIENT REGISTRATION'])
                    ->whereNotNull('l2.mobile')
                    ->where('l2.mobile', '!=', '')
                    ->orderBy('id', 'asc')
                    ->limit(1)
            ])
            ->whereIn('creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC', 'CLIENT REGISTRATION'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('alternate_mobile', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('aadhar_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('creation_source')) {
            $source = $request->creation_source;
            if (is_array($source)) {
                $query->whereIn('creation_source', $source);
            } else {
                $query->where('creation_source', $source);
            }
        }

        if ($request->filled('lead_status')) {
            $query->where('lead_status', $request->lead_status);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->filled('product')) {
            $query->where('initial_product_interest', 'like', "%{$request->product}%");
        }

        if ($request->filled('industry')) {
            $query->where('nature_of_industry', 'like', "%{$request->industry}%");
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
        }

        $leads = $query->paginate(50)->withQueryString();

        // Handle empty page due to deletion or filtering
        if ($leads->isEmpty() && $leads->currentPage() > 1) {
            return redirect($leads->previousPageUrl());
        }

        return view('leads.index', compact('leads'));
    }

    public function export(Request $request)
    {
        $type = $request->input('type');

        $query = Lead::with('customer')
            ->whereIn('creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC', 'CLIENT REGISTRATION'])
            ->latest();

        if ($type === 'selected') {
            $ids = explode(',', $request->input('ids', ''));
            if (empty($ids) || $ids[0] === '') {
                return back()->with('error', 'No leads selected for export.');
            }
            $query->whereIn('id', $ids);
        } elseif ($type === 'filtered') {
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('alternate_mobile', 'like', "%{$search}%")
                        ->orWhere('email_id', 'like', "%{$search}%")
                        ->orWhere('alternate_email_id', 'like', "%{$search}%")
                        ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                        ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                        ->orWhere('record_id', 'like', "%{$search}%")
                        ->orWhere('reference', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('lead_status')) {
                $query->where('lead_status', $request->lead_status);
            }

            if ($request->filled('customer_type')) {
                $query->where('customer_type', $request->customer_type);
            }

            if ($request->filled('city')) {
                $query->where('city', 'like', "%{$request->city}%");
            }

            if ($request->filled('product')) {
                $query->where('initial_product_interest', 'like', "%{$request->product}%");
            }

            if ($request->filled('industry')) {
                $query->where('nature_of_industry', 'like', "%{$request->industry}%");
            }

            if ($request->filled('date_from') && $request->filled('date_to')) {
                $query->whereBetween('created_at', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
            }
        }

        $fileName = 'leads-export-' . $type . '-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = $this->csvColumns();

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $query->chunk(100, function ($leads) use ($file) {
                foreach ($leads as $lead) {
                    $row = [
                        'Record ID' => $lead->record_id,
                        'Lead Status' => $lead->lead_status,
                        'KYC' => $lead->kyc,
                        'MSA Signed' => $lead->master_service_agreement_signed ? 'Yes' : 'No',
                        'Customer Type' => $lead->customer_type,
                        'Create Date' => $lead->created_at ? $lead->created_at->format('Y-m-d H:i:s') : '',
                        'Customer Name' => $lead->customer_name,
                        'Mobile' => $lead->mobile,
                        'Email ID' => $lead->email_id,
                        'City' => $lead->city,
                        'Company Name' => $lead->company_name,
                        'Company Address' => $lead->company_address,
                        'Nature of Industry' => $lead->nature_of_industry,
                        'Initial Product Interest' => $lead->initial_product_interest,
                        'Product Demand' => $lead->product_demand,
                        'Quantity' => $lead->quantity,
                        'Rate' => $lead->rate,
                        'Previous Deals & Date' => $lead->previous_deals_and_date ? \Carbon\Carbon::parse($lead->previous_deals_and_date)->format('Y-m-d') : '',
                        'Follow Up Date' => $lead->follow_up_date ? \Carbon\Carbon::parse($lead->follow_up_date)->format('Y-m-d') : '',
                        'Records Owner' => $lead->records_owner,
                        'Reference' => $lead->reference,
                        'Alternate Mobile' => $lead->alternate_mobile,
                        'Alternate Mobile 2' => $lead->alternate_mobile_2,
                        'Alternate Email Id' => $lead->alternate_email_id,
                        'Alternate Email Id 2' => $lead->alternate_email_id_2,
                        'Designation' => $lead->designation,
                        'GST No' => $lead->gst_no,
                        'PAN Number' => $lead->pan_number,
                        'Aadhar No' => $lead->aadhar_no,
                        'Udyam Registration' => $lead->udyam_registration_certificate,
                        'Website' => $lead->website,
                        'Comment' => $lead->comment,

                        // Documents
                        'Doc PAN' => $lead->doc_pan ? asset('storage/' . $lead->doc_pan) : '',
                        'Doc Aadhar' => $lead->doc_aadhar ? asset('storage/' . $lead->doc_aadhar) : '',
                        'Doc GST' => $lead->doc_gst ? asset('storage/' . $lead->doc_gst) : '',
                        'Doc Udyam' => $lead->doc_certificate_incorporation_udyam ? asset('storage/' . $lead->doc_certificate_incorporation_udyam) : '',
                        'Doc TRAI DLT' => $lead->doc_trai_dlt ? asset('storage/' . $lead->doc_trai_dlt) : '',
                        'Doc DSA License' => $lead->doc_dsa_license ? asset('storage/' . $lead->doc_dsa_license) : '',
                        'Doc Company ID' => $lead->doc_company_id_card ? asset('storage/' . $lead->doc_company_id_card) : '',
                        'Doc MSA' => $lead->msa_document ? asset('storage/' . $lead->msa_document) : '',
                    ];

                    fputcsv($file, array_values($row));
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function sampleCsv()
    {
        $fileName = 'leads-sample-import.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Explicitly define columns for sample CSV (excluding Record ID and Create Date)
        $columns = [
            'Lead Status',
            'KYC',
            'MSA Signed',
            'Customer Type',
            'Customer Name',
            'Mobile',
            'Email ID',
            'City',
            'Company Name',
            'Company Address',
            'Nature of Industry',
            'Initial Product Interest',
            'Product Demand',
            'Quantity',
            'Rate',
            'Previous Deals & Date',
            'Follow Up Date',
            'Records Owner',
            'Reference',
            'Alternate Mobile',
            'Alternate Email Id',
            'Designation',
            'GST No',
            'PAN Number',
            'Aadhar No',
            'Udyam Registration',
            'Website',
            'Comment',
            'Doc PAN',
            'Doc Aadhar',
            'Doc GST',
            'Doc Udyam',
            'Doc TRAI DLT',
            'Doc DSA License',
            'Doc Company ID',
            'Doc MSA',
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $sampleRow = [
                'Lead Status' => 'Active',
                'KYC' => 'Not Done',
                'MSA Signed' => 'Yes',
                'Customer Type' => '1st Time',
                'Customer Name' => 'John Doe',
                'Mobile' => '9876543210',
                'Email ID' => 'john@example.com',
                'City' => 'New York',
                'Company Name' => 'Acme Corp',
                'Company Address' => '123 Main St',
                'Nature of Industry' => 'Technology',
                'Initial Product Interest' => 'Data, SMS',
                'Product Demand' => 'High',
                'Quantity' => '10',
                'Rate' => '500',
                'Previous Deals & Date' => '',
                'Follow Up Date' => '',
                'Records Owner' => 'John Smith',
                'Reference' => 'Referral',
                'Alternate Mobile' => '',
                'Alternate Email Id' => '',
                'Designation' => 'CEO',
                'GST No' => 'GST123',
                'PAN Number' => 'PAN123',
                'Aadhar No' => '1234 5678 9012',
                'Udyam Registration' => '',
                'Website' => 'www.acme.com',
                'Comment' => 'Sample import record',

                // Documents
                'Doc PAN' => 'https://example.com/sample-pan.pdf',
                'Doc Aadhar' => 'https://example.com/sample-aadhar.pdf',
                'Doc GST' => 'https://example.com/sample-gst.pdf',
                'Doc Udyam' => 'https://example.com/sample-udyam.pdf',
                'Doc TRAI DLT' => '',
                'Doc DSA License' => '',
                'Doc Company ID' => '',
                'Doc MSA' => '',
            ];

            // Ensure sample row data matches $columns order perfectly
            $rowData = [];
            foreach ($columns as $col) {
                $rowData[] = $sampleRow[$col] ?? '';
            }
            fputcsv($file, $rowData);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            if (!$headers) {
                fclose($handle);
                return back()->with('error', 'The CSV file is completely empty.');
            }

            // Convert headers to UTF-8 to handle any Windows-1252 / ISO-8859-1 header chars
            $headers = array_map(function($header) {
                return mb_convert_encoding($header, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }, $headers);

            // Normalize headers for mapping
            $headers = array_map(function ($val) {
                $val = trim((string) preg_replace('/[\x00-\x1F\x80-\xFF]/', '', (string) $val));
                $val = strtolower($val);
                $val = str_replace([' ', '-'], '_', $val);
                $val = str_replace('&', 'and', $val);
                return $val;
            }, $headers);

            // "Customer Name" column MUST exist in the file
            if (!in_array('customer_name', $headers)) {
                fclose($handle);
                return back()->with('error', 'Missing required CSV headers. "Customer Name" column must exist.');
            }

            $importedCount = 0;
            $failedCount = 0;

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Ensure row has data
                if (empty($data) || (count($data) === 1 && $data[0] === null)) {
                    continue;
                }

                // Convert values to UTF-8 to avoid SQLSTATE 22007 encoding errors
                $data = array_map(function($val) {
                    if ($val === null) return null;
                    return mb_convert_encoding($val, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }, $data);

                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($data[$index]) ? trim((string) $data[$index]) : null;
                }

                // Check required field
                $customerName = $row['customer_name'] ?? '';
                if (empty($customerName)) {
                    $failedCount++;
                    continue;
                }

                try {
                    $email = trim((string) ($row['email_id'] ?? ''));
                    $mobile = trim((string) ($row['mobile'] ?? ''));
                    $companyName = trim((string) ($row['company_name'] ?? ''));

                    $customer = Customer::findByMobileAndContext($mobile, 'CRM');

                    if (!$customer) {
                        $customer = Customer::create([
                            'client_name' => $customerName,
                            'company_name' => $companyName !== '' ? $companyName : null,
                            'place' => trim((string) ($row['city'] ?? '')) ?: null,
                            'registered_address' => trim((string) ($row['company_address'] ?? '')) ?: null,
                            'mobile_no' => $mobile !== '' ? $mobile : null,
                            'email_id' => $email !== '' ? $email : null,
                        ]);
                    }

                    $previousDealsDate = trim((string) ($row['previous_deals_and_date'] ?? ''));
                    $followUpDate = trim((string) ($row['follow_up_date'] ?? ''));

                    Lead::create([
                        'customer_id' => $customer->id,
                        'customer_name' => $customerName,
                        'service_type' => null,
                        'kyc' => trim((string) ($row['kyc'] ?? '')) ?: null,
                        'master_service_agreement_signed' => strtolower(trim((string) ($row['msa_signed'] ?? ''))) === 'yes' ? 1 : 0,
                        'customer_type' => trim((string) ($row['customer_type'] ?? '')) ?: '1st Time',
                        'lead_status' => trim((string) ($row['lead_status'] ?? '')) ?: 'Active',
                        'reference' => trim((string) ($row['reference'] ?? '')) ?: null,
                        'mobile' => $mobile !== '' ? $mobile : null,
                        'alternate_mobile' => trim((string) ($row['alternate_mobile'] ?? '')) ?: null,
                        'alternate_mobile_2' => trim((string) ($row['alternate_mobile_2'] ?? '')) ?: null,
                        'email_id' => $email !== '' ? $email : null,
                        'alternate_email_id' => trim((string) ($row['alternate_email_id'] ?? '')) ?: null,
                        'alternate_email_id_2' => trim((string) ($row['alternate_email_id_2'] ?? '')) ?: null,
                        'designation' => trim((string) ($row['designation'] ?? '')) ?: null,
                        'city' => trim((string) ($row['city'] ?? '')) ?: null,
                        'nature_of_industry' => trim((string) ($row['nature_of_industry'] ?? '')) ?: null,
                        'company_name' => $companyName !== '' ? $companyName : null,
                        'company_address' => trim((string) ($row['company_address'] ?? '')) ?: null,
                        'gst_no' => trim((string) ($row['gst_no'] ?? '')) ?: null,
                        'pan_number' => trim((string) ($row['pan_number'] ?? '')) ?: null,
                        'aadhar_no' => trim((string) ($row['aadhar_no'] ?? '')) ?: null,
                        'udyam_registration_certificate' => trim((string) ($row['udyam_registration'] ?? '')) ?: null,
                        'website' => trim((string) ($row['website'] ?? '')) ?: null,
                        'initial_product_interest' => trim((string) ($row['initial_product_interest'] ?? '')) ?: null,
                        'product_demand' => trim((string) ($row['product_demand'] ?? '')) ?: null,
                        'quantity' => trim((string) ($row['quantity'] ?? '')) !== '' ? trim((string) ($row['quantity'] ?? '')) : null,
                        'rate' => trim((string) ($row['rate'] ?? '')) !== '' ? trim((string) ($row['rate'] ?? '')) : null,
                        'previous_deals_and_date' => $previousDealsDate !== '' ? \Carbon\Carbon::parse($previousDealsDate) : null,
                        'follow_up_date' => $followUpDate !== '' ? \Carbon\Carbon::parse($followUpDate) : null,
                        'records_owner' => trim((string) ($row['records_owner'] ?? '')) ?: null,
                        'comment' => trim((string) ($row['comment'] ?? '')) ?: null,

                        // Added mapping for document fields if provided via URLs in CSV
                        'doc_pan' => trim((string) ($row['doc_pan'] ?? '')) ?: null,
                        'doc_aadhar' => trim((string) ($row['doc_aadhar'] ?? '')) ?: null,
                        'doc_gst' => trim((string) ($row['doc_gst'] ?? '')) ?: null,
                        'doc_certificate_incorporation_udyam' => trim((string) ($row['doc_udyam'] ?? '')) ?: null,
                        'doc_trai_dlt' => trim((string) ($row['doc_trai_dlt'] ?? '')) ?: null,
                        'doc_dsa_license' => trim((string) ($row['doc_dsa_license'] ?? '')) ?: null,
                        'doc_company_id_card' => trim((string) ($row['doc_company_id_card'] ?? '')) ?: null,
                        'msa_document' => trim((string) ($row['doc_msa'] ?? '')) ?: null,
                    ]);

                    $importedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                }
            }

            fclose($handle);

            if ($importedCount === 0) {
                return back()->with('error', 'No valid records found to import. Verify format and required "Customer Name".');
            }

            $msg = "Successfully imported {$importedCount} leads.";
            if ($failedCount > 0) {
                $msg .= " Skipped {$failedCount} invalid or failed rows.";
            }

            return redirect()->route('leads.index')->with('success', $msg);
        }

        return back()->with('error', 'Failed to read the file.');
    }

    public function create()
    {
        return view('leads.create');
    }

    public function store(StoreLeadRequest $request)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            $mobile = $data['mobile'] ?? null;
            $email = $data['email_id'] ?? null;
            $customer = Customer::findByMobileAndContext($mobile, 'CRM');

            if ($customer) {
                // Update existing customer details if they've changed
                $customer->update([
                    'client_name' => $data['customer_name'] ?? $customer->client_name,
                    'company_name' => $data['company_name'] ?? $customer->company_name,
                    'place' => $data['city'] ?? $customer->place,
                    'registered_address' => $data['company_address'] ?? $customer->registered_address,
                    'mobile_no' => $data['mobile'] ?? $customer->mobile_no,
                    'email_id' => $email ?? $customer->email_id,
                    'state_name' => $data['state_name'] ?? $customer->state_name,
                    'state_code' => $data['state_code'] ?? $customer->state_code,
                ]);
            } else {
                $customer = Customer::create([
                    'client_name' => $data['customer_name'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'place' => $data['city'] ?? null,
                    'registered_address' => $data['company_address'] ?? null,
                    'mobile_no' => $data['mobile'] ?? null,
                    'email_id' => $email,
                    'state_name' => $data['state_name'] ?? null,
                    'state_code' => $data['state_code'] ?? null,
                ]);
            }

            $leadData = collect($data)->toArray();
            $leadData['customer_id'] = $customer->id;

            $docFields = ['doc_pan', 'doc_aadhar', 'doc_gst', 'doc_certificate_incorporation_udyam', 'doc_trai_dlt', 'doc_dsa_license', 'doc_company_id_card', 'msa_document'];

            $extractionService = app(\App\Services\DocumentNumberExtractionService::class);
            $ocrMap = [
                'doc_pan' => ['type' => 'PAN', 'field' => 'pan_number'],
                'doc_aadhar' => ['type' => 'Aadhaar', 'field' => 'aadhar_no'],
                'doc_gst' => ['type' => 'GST', 'field' => 'gst_no'],
                'doc_certificate_incorporation_udyam' => ['type' => 'Udyam', 'field' => 'udyam_registration_certificate'],
            ];

            foreach ($docFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    // Compress in place BEFORE OCR so large images are reduced in size
                    \App\Services\ImageCompressionService::compressInPlace($file);
                    
                    // Run OCR Extraction when a document is uploaded
                    if (isset($ocrMap[$field])) {
                        try {
                            $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$field]['type']);
                            if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                $leadData[$ocrMap[$field]['field']] = $ocrResult['document_number'];
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Backend OCR Extraction failed for {$field}: " . $e->getMessage());
                        }
                    }

                    $leadData[$field] = \App\Services\ImageCompressionService::compressAndStore($file, 'documents/leads', 'public');
                }
            }

            Lead::create($leadData);

            DB::commit();
            return redirect()->route('leads.index')->with('success', 'Lead and Customer created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Lead $lead)
    {
        $lead->load('customer');
        return view('leads.show', compact('lead'));
    }

    public function edit(Lead $lead)
    {
        return view('leads.edit', compact('lead'));
    }

    public function update(UpdateLeadRequest $request, Lead $lead)
    {
        $data = $request->validated();

        try {
            DB::beginTransaction();

            if ($lead->customer) {
                $lead->customer->update([
                    'client_name' => $data['customer_name'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'place' => $data['city'] ?? null,
                    'registered_address' => $data['company_address'] ?? null,
                    'mobile_no' => $data['mobile'] ?? null,
                    'email_id' => $data['email_id'] ?? null,
                    'state_name' => $data['state_name'] ?? null,
                    'state_code' => $data['state_code'] ?? null,
                ]);
            }

            $leadData = collect($data)->toArray();

            $docFields = ['doc_pan', 'doc_aadhar', 'doc_gst', 'doc_certificate_incorporation_udyam', 'doc_trai_dlt', 'doc_dsa_license', 'doc_company_id_card', 'msa_document'];

            $extractionService = app(\App\Services\DocumentNumberExtractionService::class);
            $ocrMap = [
                'doc_pan' => ['type' => 'PAN', 'field' => 'pan_number'],
                'doc_aadhar' => ['type' => 'Aadhaar', 'field' => 'aadhar_no'],
                'doc_gst' => ['type' => 'GST', 'field' => 'gst_no'],
                'doc_certificate_incorporation_udyam' => ['type' => 'Udyam', 'field' => 'udyam_registration_certificate'],
            ];

            foreach ($docFields as $field) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);

                    // Compress in place BEFORE OCR so large images are reduced in size
                    \App\Services\ImageCompressionService::compressInPlace($file);

                    // Run OCR Extraction when a document is uploaded
                    if (isset($ocrMap[$field])) {
                        try {
                            $ocrResult = $extractionService->extractDocumentNumber($file, $ocrMap[$field]['type']);
                            if (isset($ocrResult['success']) && $ocrResult['success'] && !empty($ocrResult['document_number'])) {
                                $leadData[$ocrMap[$field]['field']] = $ocrResult['document_number'];
                            }
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error("Backend OCR Extraction failed for {$field}: " . $e->getMessage());
                        }
                    }

                    if ($lead->{$field}) {
                        \Illuminate\Support\Facades\Storage::disk('public')->delete($lead->{$field});
                    }
                    $leadData[$field] = \App\Services\ImageCompressionService::compressAndStore($file, 'documents/leads', 'public');
                }
            }

            $lead->update($leadData);

            DB::commit();

            return redirect()->route('leads.show', $lead->id)->with('success', 'Lead updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred: ' . $e->getMessage())->withInput();
        }
    }

    public function bulkDestroy(Request $request)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->can('lead-delete')) {
            abort(403, 'Unauthorized action.');
        }

        $ids = $request->input('ids');
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'No leads selected for deletion.');
        }

        try {
            DB::beginTransaction();
            $leads = Lead::whereIn('id', $ids)->get();
            $count = $leads->count();

            foreach ($leads as $lead) {
                $docFields = ['doc_pan', 'doc_aadhar', 'doc_gst', 'doc_certificate_incorporation_udyam', 'doc_trai_dlt', 'doc_dsa_license', 'doc_company_id_card', 'msa_document'];
                foreach ($docFields as $field) {
                    if ($lead->{$field}) {
                        Storage::disk('public')->delete($lead->{$field});
                    }
                }
                $lead->delete();
            }

            DB::commit();
            return back()->with('success', "{$count} leads deleted successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error occurred during bulk deletion: ' . $e->getMessage());
        }
    }

    public function destroy(Lead $lead)
    {
        if (!auth()->user()->isAdmin() && !auth()->user()->can('lead-delete')) {
            abort(403, 'Unauthorized action.');
        }

        $source = $lead->creation_source;

        $docFields = ['doc_pan', 'doc_aadhar', 'doc_gst', 'doc_certificate_incorporation_udyam', 'doc_trai_dlt', 'doc_dsa_license', 'doc_company_id_card', 'msa_document'];
        foreach ($docFields as $field) {
            if ($lead->{$field}) {
                Storage::disk('public')->delete($lead->{$field});
            }
        }

        $lead->delete();

        // If the request came from the show page, redirect to the appropriate index
        $referer = request()->header('referer');
        if ($referer && str_contains($referer, "/leads/{$lead->id}")) {
            $redirectRoute = 'leads.index';
            if ($source && str_contains($source, 'VENDOR PO')) {
                $redirectRoute = 'vendor_leads.po';
            } elseif ($source && str_contains($source, 'VENDOR KYC')) {
                $redirectRoute = 'vendor_leads.kyc';
            }
            return redirect()->route($redirectRoute, ['page' => request('page')])->with('success', 'Lead deleted successfully.');
        }

        return back()->with('success', 'Lead deleted successfully.');
    }

    public function sendEmailCampaign(Request $request)
    {
        abort_if(!auth()->user()->can('email-template-send'), 403);

        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'theme' => 'nullable|string|in:default,emerald,purple,charcoal',
            'ids' => 'nullable|string',
            'is_filtered_campaign' => 'nullable|string'
        ]);

        $template = \App\Models\EmailTemplate::findOrFail($request->template_id);

        try {
            $query = Lead::select('id', 'customer_name', 'email_id', 'alternate_email_id', 'alternate_email_id_2')
                ->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotNull('email_id')->where('email_id', '!=', '');
                    })->orWhere(function($q2) {
                        $q2->whereNotNull('alternate_email_id')->where('alternate_email_id', '!=', '');
                    })->orWhere(function($q3) {
                        $q3->whereNotNull('alternate_email_id_2')->where('alternate_email_id_2', '!=', '');
                    });
                });

            if ($request->is_filtered_campaign === 'true') {
                // Apply same filters as index
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('alternate_mobile', 'like', "%{$search}%")
                            ->orWhere('email_id', 'like', "%{$search}%")
                            ->orWhere('alternate_email_id', 'like', "%{$search}%")
                            ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                            ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                            ->orWhere('record_id', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('lead_status'))
                    $query->where('lead_status', $request->lead_status);
                if ($request->filled('customer_type'))
                    $query->where('customer_type', $request->customer_type);
                if ($request->filled('city'))
                    $query->where('city', 'like', "%{$request->city}%");
                if ($request->filled('product'))
                    $query->where('initial_product_interest', 'like', "%{$request->product}%");
                if ($request->filled('industry'))
                    $query->where('nature_of_industry', 'like', "%{$request->industry}%");
                if ($request->filled('date_from') && $request->filled('date_to')) {
                    $query->whereBetween('created_at', [$request->date_from . ' 00:00:00', $request->date_to . ' 23:59:59']);
                }
            } else {
                $ids = explode(',', $request->ids);
                $query->whereIn('id', $ids);
            }

            $leads = $query->get();
            $successCount = 0;

            if ($leads->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No leads with valid email addresses found for selection.'
                ]);
            }

            $emailList = [];
            foreach ($leads as $lead) {
                if (!empty($lead->email_id)) {
                    $emailList[] = trim($lead->email_id);
                }
                if (!empty($lead->alternate_email_id)) {
                    $emailList[] = trim($lead->alternate_email_id);
                }
                if (!empty($lead->alternate_email_id_2)) {
                    $emailList[] = trim($lead->alternate_email_id_2);
                }
            }
            // Remove duplicates across all gathered emails
            $emailList = array_unique($emailList);

            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'Smtp.rediffmailpro.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'hello@billionsunited.com';
                $mail->Password = 'Gautam123#@';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                $mail->SMTPKeepAlive = true; // Keep SMTP connection open to send multiple emails

                // Recipients
                $mail->setFrom('admin@billionsunited.com', 'Billions United');
                $mail->addReplyTo('salesteam@billionsunited.com', 'Sales Team');

                $mail->isHTML(true);
                $mail->Subject = $template->subject;
                $mail->Body = view('emails.campaign', [
                    'subject' => $template->subject,
                    'body' => $template->body,
                    'theme' => $request->input('theme', 'default')
                ])->render();

                if ($template->attachment && \Illuminate\Support\Facades\Storage::disk('public')->exists($template->attachment)) {
                    $diskPath = \Illuminate\Support\Facades\Storage::disk('public')->path($template->attachment);
                    $fileName = basename($template->attachment);
                    $parts = explode('---', $fileName, 2);
                    $niceName = count($parts) === 2 ? $parts[1] : $fileName;
                    $mail->addAttachment($diskPath, $niceName);
                }

                // Clear any existing stop flag
                \Illuminate\Support\Facades\Cache::forget('stop_campaign_' . auth()->id());

                $successCount = 0;
                $stopped = false;
                foreach ($emailList as $email) {
                    if (\Illuminate\Support\Facades\Cache::pull('stop_campaign_' . auth()->id())) {
                        $stopped = true;
                        break;
                    }
                    try {
                        $mail->addAddress($email);
                        $mail->send();
                        $successCount++;
                    } catch (\Exception $e) {
                        \Log::error("Failed to send campaign email to {$email}: " . $mail->ErrorInfo);
                    }
                    $mail->clearAddresses();
                }

                $mail->smtpClose(); // Close the persistent SMTP connection

                if ($successCount === 0 && !$stopped) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send campaign emails to any of the selected leads.'
                    ], 500);
                }

                $msg = $stopped ? "Campaign stopped. Sent successfully to {$successCount} of " . count($emailList) . " leads." 
                                : "Campaign sent successfully to {$successCount} of " . count($emailList) . " leads.";

                return response()->json([
                    'success' => true,
                    'message' => $msg
                ]);
            } catch (Exception $e) {
                \Log::error("PHPMailer Setup/Error: " . $mail->ErrorInfo);
                throw new \Exception("Mailer Error: " . $mail->ErrorInfo);
            }
        } catch (\Exception $e) {
            \Log::error("Email Campaign Critical Error: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Critical error: ' . $e->getMessage()
            ], 500);
        }
    }
}