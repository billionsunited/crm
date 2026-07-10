<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ManagePoController extends Controller
{
  /**
   * Display the Client P.O form
   */
  public function createClientPo()
  {
    abort_if(!auth()->user()->can('client-po-access'), 403);
    $targetSources = array_merge(
      Lead::SOURCE_GROUPS['CLIENT_KYC'],
      Lead::SOURCE_GROUPS['CLIENT_TERMS'],
      Lead::SOURCE_GROUPS['CRM']
    );

    $leads = Lead::whereIn('creation_source', $targetSources)
      ->whereNotNull('customer_id')
      ->leftJoin('customers', 'leads.customer_id', '=', 'customers.id')
      ->select('leads.*')
      ->orderByRaw('COALESCE(customers.company_name, leads.company_name) ASC')
      ->with('customer')
      ->get()
      ->map(function ($lead) {
        $customer = $lead->customer;
        return [
          'id' => $lead->id,
          'record_id' => $lead->record_id,
          'client_name' => $customer?->client_name ?? $lead->customer_name ?? 'None',
          'organisation_name' => $customer?->company_name ?? $lead->company_name ?? 'None',
          'address' => $customer?->registered_address ?? $lead->company_address ?? 'None',
          'city' => $customer?->place ?? $lead->city ?? 'None',
          'email_id' => $customer?->email_id ?? $lead->email_id ?? 'None',
          'signature_path' => $customer?->signature_path ? 'https://billionsunited.com/generated_sow/signatures/' . basename($customer->signature_path) : null,
          'customer_id' => $customer?->id,
        ];
      });

    return view('manage-po.client-po', compact('leads'));
  }

  /**
   * Display the Vendor P.O form
   */
  public function vendorPo(Request $request)
  {
    // Fetch latest leads from vendor sources
    $leadsQuery = Lead::with('customer')
      ->whereIn('creation_source', ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR PO API', 'VENDOR P.O (ADMIN)'])
      ->orderBy('created_at', 'desc');

    $leads = $leadsQuery->get();

    // If a specific lead is requested, make sure it's in the collection before unique-ifying
    // or just ensure we don't lose it if it's a valid vendor lead.
    if ($request->has('lead_id')) {
      $requestedLead = Lead::with('customer')->find($request->lead_id);
      if ($requestedLead && !$leads->contains('id', $requestedLead->id)) {
        $leads->push($requestedLead);
      }
    }

    $leads = $leads->unique('email_id')->values();

    // Re-check: If the requested lead was unique-ified away because another lead with same email was newer,
    // we should actually prefer the requested lead if it's specifically passed.
    if ($request->has('lead_id')) {
      $requestedLead = Lead::with('customer')->find($request->lead_id);
      if ($requestedLead) {
        // If the unique list doesn't have THIS specific ID, but has the same email,
        // we might want to swap it or just add it.
        // For simplicity and UX consistency, if lead_id is passed, it MUST be in the list.
        if (!$leads->contains('id', $requestedLead->id)) {
          $leads->push($requestedLead);
        }
      }
    }

    // Get current counter for preview (without incrementing)
    $file = storage_path('app/sow_counter.txt');
    $currentCount = file_exists($file) ? (int) trim(file_get_contents($file)) : 0;
    $nextPoNumber = "BU/" . str_pad($currentCount + 1, 3, '0', STR_PAD_LEFT) . "/" . date('Y');

    return view('manage-po.vendor-po', compact('leads', 'nextPoNumber'));
  }

  /**
   * Robust PO Number Generation with File Locking
   */
  private function generatePONumber()
  {
    $filePath = storage_path('app/sow_counter.txt');
    if (!file_exists($filePath)) {
      file_put_contents($filePath, "0");
    }

    $fp = fopen($filePath, "c+");

    if (!$fp) {
      throw new \Exception("Unable to open counter file.");
    }

    // 🔒 Lock file (very important)
    if (!flock($fp, LOCK_EX)) {
      fclose($fp);
      throw new \Exception("Unable to lock counter file.");
    }

    $fileSize = filesize($filePath);
    $count = (int) trim(fread($fp, $fileSize ?: 1));
    $count++;

    // Reset pointer and overwrite
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, $count);

    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    // Format like 001, 002
    return str_pad($count, 3, '0', STR_PAD_LEFT);
  }

  /**
   * Fetch Customer Details for AJAX auto-fill
   */
  public function fetchCustomer(Request $request)
  {
    $customerId = $request->input('customer_id');
    $customer = Customer::find($customerId);

    if (!$customer) {
      return response()->json(['success' => false, 'message' => 'Customer not found.']);
    }

    return response()->json([
      'success' => true,
      'customer' => [
        'organization_name' => $customer->company_name,
        'contact_person' => $customer->client_name,
        'client_place' => $customer->place,
        'registered_address' => $customer->registered_address,
        'authorised_recipient_email' => $customer->email_id,
        'signature_path' => $customer->signature_path ? asset($customer->signature_path) : null,
      ]
    ]);
  }

  /**
   * Process the Client P.O submission
   */
  public function storeClientPo(Request $request)
  {
    abort_if(!auth()->user()->can('client-po-access'), 403);
    // Validation
    $request->validate([
      'customer_id' => 'required|exists:customers,id',
      'organization_name' => 'required|string',
      'contact_person' => 'required|string',
      'sow_effective_date' => 'required|date',
      'client_place' => 'required|string',
      'registered_address' => 'required|string',
      'authorised_recipient_email' => 'required|email',
      'declaration_confirm' => 'required|in:1',
    ]);

    try {
      DB::beginTransaction();

      $customer = Customer::findOrFail($request->customer_id);

      // Security Check: Ensure the customer has allowed lead sources
      $targetSources = array_merge(
        Lead::SOURCE_GROUPS['CLIENT_KYC'],
        Lead::SOURCE_GROUPS['CLIENT_TERMS'],
        Lead::SOURCE_GROUPS['CRM']
      );

      $hasValidLead = $customer->leads()->whereIn('creation_source', $targetSources)->exists();
      if (!$hasValidLead) {
        return back()->with('error', 'Client P.O can only be generated for customers with Client KYC or Client Terms leads.')->withInput();
      }

      // Create New Lead
      // $lead = Lead::create([
      //   'customer_id' => $customer->id,
      //   'customer_name' => $request->contact_person,
      //   'company_name' => $request->organization_name,
      //   'city' => $request->client_place,
      //   'company_address' => $request->registered_address,
      //   'email_id' => $request->authorised_recipient_email,
      //   'lead_status' => 'Active',
      //   'customer_type' => 'Enquiry',
      //   'creation_source' => 'CLIENT P.O',
      //   'master_service_agreement_signed' => 1,
      //   'kyc' => 'Done',
      //   // other fields as needed
      // ]);

      //Log::info("Manage PO: Created new lead ID {$lead->id} for customer ID {$customer->id}");

      // Generate SOW
      $sowNumber = $this->generateSowNumber();

      $docPath = $this->generateSowDocument($request, $sowNumber, $customer);

      // Send Emails
      $this->sendEmails($request, $docPath, $sowNumber);

      DB::commit();

      return redirect()->route('manage_po.client_po.create')->with('success', 'Client P.O successfully generated and emails sent.');

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Client PO Generation failed: " . $e->getMessage());
      return back()->with('error', 'Failed to generate P.O: ' . $e->getMessage())->withInput();
    }
  }


  private function generateSowNumber()
  {
    $number = $this->generatePONumber();
    return 'BU-SOW-' . $number;
  }

  /**
   * Generate Document using PHPWord
   */
  private function generateSowDocument(Request $request, $sowNumber, $customer)
  {
    $templatePath = storage_path('app/templates/STATEMENT-OF-WORK-TEMPLATE.docx');

    if (!file_exists($templatePath)) {
      throw new \Exception('Template file not found at: ' . $templatePath);
    }

    $templateProcessor = new TemplateProcessor($templatePath);

    $sowDate = date('d F Y', strtotime($request->sow_effective_date));

    $databaseQuantity = (int) $request->database_quantity;
    $smsQuantity = (int) $request->sms_quantity;
    $whatsappQuantity = (int) $request->whatsapp_quantity;
    $rcsQuantity = (int) $request->rcs_quantity;

    $services = $request->input('services', []);

    if (!in_array('Database Supply', $services))
      $databaseQuantity = 0;
    if (!in_array('SMS Campaign', $services))
      $smsQuantity = 0;
    if (!in_array('WhatsApp Campaign', $services))
      $whatsappQuantity = 0;
    if (!in_array('RCS Campaign', $services))
      $rcsQuantity = 0;

    $dbCharge = (float) $request->database_supply_charge;
    $smsCharge = (float) $request->sms_campaign_charge;
    $waCharge = (float) $request->whatsapp_campaign_charge;
    $rcsCharge = (float) $request->rcs_campaign_charge;

    $totalValue = $dbCharge + $smsCharge + $waCharge + $rcsCharge;
    $gstAmount = $totalValue * 0.18;
    $grandTotal = $totalValue + $gstAmount;

    // Process arrays
    $targetSegment = $request->input('target_segment', []);
    if (in_array('Other', $targetSegment)) {
      $targetSegment = array_diff($targetSegment, ['Other']);
      $targetSegment[] = $request->target_segment_other_text ?: 'Other';
    }

    $targetGeography = $request->input('target_geography', []);

    $productMarketed = $request->input('product_service_marketed', []);
    if (in_array('Other', $productMarketed)) {
      $productMarketed = array_diff($productMarketed, ['Other']);
      $productMarketed[] = $request->product_service_other_text ?: 'Other';
    }

    $marketingChannel = $request->input('marketing_channel', []);

    // Logic for data_fields_included
    $includedFieldsMap = [
      'Salaried' => ['Name', 'Email', 'Mobile', 'Organisation', 'Location', 'Salary Range'],
      'SME' => ['Name', 'Designation', 'Mobile', 'Email', 'Company Name', 'Address', 'Location'],
      'Car' => ['Name', 'Purchase Date', 'Mobile', 'Address', 'Make & Model']
    ];

    $dataFieldsIncludedLines = [];
    $databaseTypes = $request->input('target_segment', []);
    foreach ($databaseTypes as $type) {
      if ($type == 'Other' && $request->target_segment_other_text) {
        $dataFieldsIncludedLines[] = $request->target_segment_other_text;
      } elseif (isset($includedFieldsMap[$type])) {
        $dataFieldsIncludedLines[] = $type . ' (' . implode(', ', $includedFieldsMap[$type]) . ')';
      } else {
        $dataFieldsIncludedLines[] = $type;
      }
    }
    $dataFieldsIncluded = !empty($dataFieldsIncludedLines) && in_array('Database Supply', $services) ? implode("\n", $dataFieldsIncludedLines) : 'NA';

    $ip_address = $customer->ip_address;
    $timestamp = date('Y-m-d H:i:s');
    // Set Values
    $templateProcessor->setValue('sow_number', htmlspecialchars($sowNumber));
    $templateProcessor->setValue('sow_date', htmlspecialchars($sowDate));
    $templateProcessor->setValue('msa_reference', htmlspecialchars($request->organization_name));
    $templateProcessor->setValue('organization_name', htmlspecialchars($request->organization_name));
    $templateProcessor->setValue('contact_person', htmlspecialchars($request->contact_person));
    $templateProcessor->setValue('registered_address', htmlspecialchars($request->registered_address));
    $templateProcessor->setValue('effective_date', htmlspecialchars($sowDate));
    $templateProcessor->setValue('expiry_delivery_date', htmlspecialchars($sowDate));

    $templateProcessor->setValue('database_quantity', $databaseQuantity > 0 ? $databaseQuantity : 'NA');
    $templateProcessor->setValue('sms_quantity', $smsQuantity > 0 ? $smsQuantity : 'NA');
    $templateProcessor->setValue('whatsapp_quantity', $whatsappQuantity > 0 ? $whatsappQuantity : 'NA');
    $templateProcessor->setValue('rcs_quantity', $rcsQuantity > 0 ? $rcsQuantity : 'NA');

    $templateProcessor->setValue('data_fields_included', htmlspecialchars($dataFieldsIncluded));
    $templateProcessor->setValue('target_segment', htmlspecialchars(!empty($targetSegment) ? implode(', ', $targetSegment) : 'NA'));
    $templateProcessor->setValue('target_geography', htmlspecialchars(!empty($targetGeography) ? implode(', ', $targetGeography) : 'NA'));
    $templateProcessor->setValue('salary_band_filter', htmlspecialchars($request->salary_band_filter ?: 'NA'));
    $templateProcessor->setValue('authorised_recipient_email', htmlspecialchars($request->authorised_recipient_email));

    $templateProcessor->setValue('database_supply_charge', number_format($dbCharge, 2, '.', ''));
    $templateProcessor->setValue('sms_campaign_charge', number_format($smsCharge, 2, '.', ''));
    $templateProcessor->setValue('whatsapp_campaign_charge', number_format($waCharge, 2, '.', ''));
    $templateProcessor->setValue('rcs_campaign_charge', number_format($rcsCharge, 2, '.', ''));
    $templateProcessor->setValue('total_sow_value', number_format($totalValue, 2, '.', ''));
    $templateProcessor->setValue('gst_amount', number_format($gstAmount, 2, '.', ''));
    $templateProcessor->setValue('grand_total', number_format($grandTotal, 2, '.', ''));

    $templateProcessor->setValue('product_service_marketed', htmlspecialchars(!empty($productMarketed) ? implode(', ', $productMarketed) : 'NA'));
    $templateProcessor->setValue('marketing_channel', htmlspecialchars(!empty($marketingChannel) ? implode(', ', $marketingChannel) : 'NA'));
    $templateProcessor->setValue('permitted_geography_outreach', 'NA');

    $templateProcessor->setValue('client_place', htmlspecialchars($request->client_place));
    $templateProcessor->setValue('client_date', htmlspecialchars($sowDate));

    $templateProcessor->setValue('timestamp', e($timestamp));

    Log::info('Client PO Generation: use_digital_signature = ' . ($request->has('use_digital_signature') ? 'YES' : 'NO'));
    Log::info('Client PO Generation: include_ip = ' . ($request->has('include_ip') ? 'YES' : 'NO'));

    // Handle IP Address Inclusion
    if ($request->has('include_ip')) {
      $templateProcessor->setValue('ip_address', e($ip_address));
    } else {
      $templateProcessor->setValue('ip_address', '');
    }

    // Handle Digital Signature Inclusion
    if ($request->has('use_digital_signature')) {
      // Signature image from customer
      $sigPath = null;
      $dbPath = $customer->signature_path;

      if ($dbPath) {
        if (file_exists($dbPath)) {
          $sigPath = $dbPath;
        } else {
          // Try to handle absolute path misconfigurations (e.g. from live to local or vice-versa)
          $cleanPath = str_replace('/home/billions/public_html/', '', $dbPath);
          $cleanPath = ltrim($cleanPath, '/');

          if (file_exists(public_path($cleanPath))) {
            $sigPath = public_path($cleanPath);
          } elseif (file_exists(storage_path('app/public/' . str_replace('storage/', '', $dbPath)))) {
            $sigPath = storage_path('app/public/' . str_replace('storage/', '', $dbPath));
          }
        }
      }

      Log::info('Client PO Generation: sigPath found = ' . ($sigPath ?: 'NULL'));

      if ($sigPath) {
        // Replicate the white background conversion exactly as old script to ensure compatibility with PHPWord image loading
        $signatureBinary = @file_get_contents($sigPath);
        if ($signatureBinary) {
          $image = @imagecreatefromstring($signatureBinary);

          if ($image !== false) {
            $bg = imagecreatetruecolor(imagesx($image), imagesy($image));
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

            $tempSigPath = storage_path('app/temp_sig_' . uniqid() . '.png');
            imagepng($bg, $tempSigPath);
            imagedestroy($image);
            imagedestroy($bg);

            $templateProcessor->setImageValue('client_signature', [
              'path' => $tempSigPath,
              'width' => 180,
              'height' => 70,
              'ratio' => true
            ]);

            // We'll clean up the temp file after saving
            app()->terminating(function () use ($tempSigPath) {
              if (file_exists($tempSigPath))
                unlink($tempSigPath);
            });
          } else {
            Log::warning('Client PO Generation: Signature image unreadable at ' . $sigPath);
            $templateProcessor->setValue('client_signature', 'Signature Unreadable');
          }
        } else {
          Log::warning('Client PO Generation: Could not read signature file at ' . $sigPath);
          $templateProcessor->setValue('client_signature', 'Signature File Unreadable');
        }
      } else {
        $templateProcessor->setValue('client_signature', '');
      }
    } else {
      $templateProcessor->setValue('client_signature', '');
    }

    // $safeCompany = preg_replace('/[^A-Za-z0-9_\-]/', '_', $request->organization_name);
    // $outputDocxName = 'SOW_' . $safeCompany . '_' . date('Ymd_His') . '.docx';
    $outputDocxName = 'SOW_BU_' . date('dmY') . '.docx';

    $outputDir = storage_path('app/public/generated_sow');
    if (!is_dir($outputDir)) {
      mkdir($outputDir, 0777, true);
    }

    $outputDocxPath = $outputDir . '/' . $outputDocxName;
    $templateProcessor->saveAs($outputDocxPath);

    return $outputDocxPath;
  }

  /**
   * Send Emails using PHPMailer
   */
  private function sendEmails(Request $request, $outputDocxPath, $sowNumber)
  {
    $sowDate = date('d F Y', strtotime($request->sow_effective_date));
    $services = $request->input('services', []);

    $targetSegment = $request->input('target_segment', []);
    if (in_array('Other', $targetSegment)) {
      $targetSegment = array_diff($targetSegment, ['Other']);
      $targetSegment[] = $request->target_segment_other_text ?: 'Other';
    }

    $targetGeography = $request->input('target_geography', []);
    $productMarketed = $request->input('product_service_marketed', []);
    if (in_array('Other', $productMarketed)) {
      $productMarketed = array_diff($productMarketed, ['Other']);
      $productMarketed[] = $request->product_service_other_text ?: 'Other';
    }

    $marketingChannel = $request->input('marketing_channel', []);

    $dbCharge = (float) $request->database_supply_charge;
    $smsCharge = (float) $request->sms_campaign_charge;
    $waCharge = (float) $request->whatsapp_campaign_charge;
    $rcsCharge = (float) $request->rcs_campaign_charge;

    $totalValue = $dbCharge + $smsCharge + $waCharge + $rcsCharge;
    $gstAmount = $totalValue * 0.18;
    $grandTotal = $totalValue + $gstAmount;

    $emailBody = '
        <html>
        <head>
          <meta charset="UTF-8">
          <title>Statement of Work Submission</title>
        </head>
        <body style="margin:0;padding:40px 20px;background:#f4f6f8;font-family:Arial,sans-serif;">
          <div style="max-width:760px;margin:0 auto;padding:20px 0;">
            <div style="max-width:700px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.08);">
              <div style="background:#0d6efd;padding:28px 30px;color:#ffffff;">
                <h2 style="margin:0;font-size:24px;line-height:1.4;">Statement of Work Submission</h2>
              </div>
              <div style="padding:36px 30px;">
                <p style="margin-top:0;font-size:15px;color:#333;line-height:1.8;">Hello Admin,</p>
                <p style="font-size:15px;color:#333;line-height:1.8;margin-bottom:24px;">
                  A new Statement of Work has been submitted successfully. The completed SOW document is attached with this email.
                </p>
                <table style="width:100%;border-collapse:collapse;margin-top:20px;margin-bottom:24px;">
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>SOW Number</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($sowNumber) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Organization Name</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($request->organization_name) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Contact Person</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($request->contact_person) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Effective Date</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($sowDate) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Services</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars(!empty($services) ? implode(', ', $services) : 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Target Segment</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars(!empty($targetSegment) ? implode(', ', $targetSegment) : 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Target Geography</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars(!empty($targetGeography) ? implode(', ', $targetGeography) : 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Salary Band Filter</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($request->salary_band_filter ?: 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Product / Service Marketed</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars(!empty($productMarketed) ? implode(', ', $productMarketed) : 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Marketing Channel</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars(!empty($marketingChannel) ? implode(', ', $marketingChannel) : 'NA') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Total SOW Value</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . number_format($totalValue, 2, '.', '') . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Grand Total</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . number_format($grandTotal, 2, '.', '') . '</td>
                  </tr>
                </table>
                <p style="margin:0;font-size:15px;color:#333;line-height:1.8;">Regards,<br>Billions United</p>
              </div>
            </div>
          </div>
        </body>
        </html>';

    $clientEmailBody = '
        <html>
        <head>
          <meta charset="UTF-8">
          <title>Statement of Work Confirmation</title>
        </head>
        <body style="margin:0;padding:40px 20px;background:#f4f6f8;font-family:Arial,sans-serif;">
          <div style="max-width:760px;margin:0 auto;padding:20px 0;">
            <div style="max-width:700px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.08);">
              <div style="background:#0d6efd;padding:28px 30px;color:#ffffff;">
                <h2 style="margin:0;font-size:24px;line-height:1.4;">Statement of Work Confirmation</h2>
              </div>
              <div style="padding:36px 30px;">
                <p style="margin-top:0;font-size:15px;color:#333;line-height:1.8;">Dear ' . htmlspecialchars($request->contact_person) . ',</p>
                <p style="font-size:15px;color:#333;line-height:1.8;margin-bottom:20px;">
                  Thank you for submitting your Statement of Work details. We have successfully received your submission.
                </p>
                <table style="width:100%;border-collapse:collapse;margin-top:20px;margin-bottom:24px;">
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>SOW Number</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($sowNumber) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Organization Name</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($request->organization_name) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Effective Date</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($sowDate) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Grand Total</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . number_format($grandTotal, 2, '.', '') . '</td>
                  </tr>
                </table>
                <p style="font-size:15px;color:#333;line-height:1.8;margin-bottom:24px;">
                  Please review the attached Statement of Work document. Our team will review the submitted details and contact you if any further clarification is required.
                </p>
                <p style="margin:0;font-size:15px;color:#333;line-height:1.8;">Regards,<br>Billions United</p>
              </div>
            </div>
          </div>
        </body>
        </html>';

    try {
      // Admin Email
      $adminMail = new PHPMailer(true);
      $adminMail->isSMTP();
      $adminMail->Host = 'Smtp.rediffmailpro.com';
      $adminMail->SMTPAuth = true;
      $adminMail->Username = 'hello@billionsunited.com';
      $adminMail->Password = 'Gautam123#@';
      $adminMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $adminMail->Port = 587;

      $adminMail->setFrom('hello@billionsunited.com', 'Billions United');
      $adminMail->addAddress('hello@billionsunited.com');
      $adminMail->isHTML(true);
      $adminMail->Subject = 'New Statement of Work Submission - ' . $request->organization_name;
      $adminMail->Body = $emailBody;
      $adminMail->addAttachment($outputDocxPath, basename($outputDocxPath));

      // Allow failure to just be logged instead of stopping the whole process, 
      // since the user's legacy script commented out $adminMail->send()
      try {
        $adminMail->send();
      } catch (\Exception $e) {
        Log::warning("Admin email failed to send: " . $e->getMessage());
      }

      // Client Email
      $clientMail = new PHPMailer(true);
      $clientMail->isSMTP();
      $clientMail->Host = 'Smtp.rediffmailpro.com';
      $clientMail->SMTPAuth = true;
      $clientMail->Username = 'hello@billionsunited.com';
      $clientMail->Password = 'Gautam123#@';
      $clientMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $clientMail->Port = 587;

      $clientMail->setFrom('hello@billionsunited.com', 'Billions United');
      $clientMail->addAddress($request->authorised_recipient_email);
      $clientMail->isHTML(true);
      $clientMail->Subject = 'Confirmation - Statement of Work Submission';
      $clientMail->Body = $clientEmailBody;
      $clientMail->addAttachment($outputDocxPath, basename($outputDocxPath));

      try {
        $clientMail->send();
      } catch (\Exception $e) {
        Log::warning("Client email failed to send: " . $e->getMessage());
      }

    } catch (Exception $e) {
      throw new \Exception("Mailer Error: " . $e->getMessage());
    }
  }

  /**
   * Process the Vendor P.O submission
   */
  public function storeVendorPo(Request $request)
  {
    $request->validate([
      'lead_id' => 'required',
      'po_date' => 'required|date',
      'email_id' => 'required|email',
      'data_category' => 'required|array|min:1',
      'geography_filter' => 'required|array|min:1',
      'volume_records' => 'required|integer|min:1',
      'excluded_amount' => 'required|numeric|min:1',
    ]);

    try {
      DB::beginTransaction();

      // Automatically Generate PO Number
      $po_sequence = $this->generatePONumber();
      $po_number = $po_sequence;

      $po_date = $request->input('po_date');
      $email_id = $request->input('email_id');
      
      $data_category = $request->input('data_category', []);
      if (in_array('Other', $data_category)) {
          $data_category = array_diff($data_category, ['Other']);
          $data_category[] = $request->input('data_category_other_text') ?: 'Other';
      }

      $geography_filter = $request->input('geography_filter', []);
      if (in_array('Other', $geography_filter)) {
          $geography_filter = array_diff($geography_filter, ['Other']);
          $geography_filter[] = $request->input('geography_filter_other_text') ?: 'Other';
      }
      $volume_records = $request->input('volume_records');
      $excluded_amount = $request->input('excluded_amount');

      // Find or create customer
      $lead = Lead::find($request->lead_id);
      $mobile = $lead ? $lead->mobile : null;
      $customer = Customer::findByMobileAndContext($mobile, 'VENDOR P.O (ADMIN)');

      if (!$customer) {
        $customer = Customer::create([
          'email_id' => $email_id,
          'mobile_no' => $mobile,
          'client_name' => 'Vendor ' . $po_number,
        ]);
      }

      // Prepare details for comment
      $data_category_text = implode(' / ', $data_category);
      $geography_filter_text = implode(' / ', $geography_filter);

      $comment = "Vendor PO Details:\n";
      $comment .= "- PO Number: $po_number\n";
      $comment .= "- PO Date: $po_date\n";
      $comment .= "- Data Category: $data_category_text\n";
      $comment .= "- Geography: $geography_filter_text\n";
      $comment .= "- Volume: $volume_records\n";
      $comment .= "- Amount: $excluded_amount\n";

      // Create New Lead
      $lead = Lead::create([
        'customer_id' => $customer->id,
        'email_id' => $email_id,
        'customer_name' => $customer->client_name,
        'lead_status' => 'Active',
        'customer_type' => 'Enquiry',
        'creation_source' => 'VENDOR P.O (ADMIN)',
        'comment' => $comment,
        'kyc' => 'Not Done',
      ]);

      // Calculate totals for document
      $gst_amount = (float) $excluded_amount * 0.18;
      $total_payable = (float) $excluded_amount + $gst_amount;
      $total_payable_formatted = number_format($total_payable, 2, '.', '');
      $formatted_po_date = date('d F Y', strtotime($po_date));

      // Document Generation
      $templatePath = storage_path('app/templates/PO-VOUCHER-TEMPLATE.docx');
      if (!file_exists($templatePath)) {
        throw new \Exception('Template file not found at: ' . $templatePath);
      }

      $templateProcessor = new TemplateProcessor($templatePath);
      $templateProcessor->setValue('po_number', htmlspecialchars($po_number));
      $templateProcessor->setValue('po_date', htmlspecialchars($formatted_po_date));
      $templateProcessor->setValue('data_category', htmlspecialchars($data_category_text));
      $templateProcessor->setValue('geography_filter', htmlspecialchars($geography_filter_text));
      $templateProcessor->setValue('volume_records', htmlspecialchars($volume_records));
      $templateProcessor->setValue('excluded_amount', number_format((float) $excluded_amount, 2, '.', ''));
      $templateProcessor->setValue('total_payable', $total_payable_formatted);
      $templateProcessor->setValue('timestamp', date('Y-m-d H:i:s'));
      $templateProcessor->setValue('ip_address', $request->ip() ?? 'UNKNOWN');
      $templateProcessor->setValue('year', date('Y'));

      $outputDir = storage_path('app/public/generated_po_voucher');
      if (!is_dir($outputDir)) {
        mkdir($outputDir, 0777, true);
      }
      $outputDocxName = 'PO_Voucher_' . $po_number . '_' . date('dmY') . '.docx';
      $outputDocxPath = $outputDir . '/' . $outputDocxName;
      $templateProcessor->saveAs($outputDocxPath);

      // Send Emails
      $this->sendVendorEmails($po_number, $formatted_po_date, $email_id, $data_category_text, $geography_filter_text, $volume_records, $excluded_amount, $total_payable_formatted, $outputDocxPath);

      DB::commit();

      return redirect()->route('manage_po.vendor_po')->with('success', 'Vendor P.O successfully generated and emails sent.');

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Vendor PO Generation failed: " . $e->getMessage());
      return back()->with('error', 'Failed to generate Vendor P.O: ' . $e->getMessage())->withInput();
    }
  }

  /**
   * Send Vendor Emails
   */
  private function sendVendorEmails($po_number, $formatted_po_date, $email_id, $data_category_text, $geography_filter_text, $volume_records, $excluded_amount, $total_payable_formatted, $outputDocxPath)
  {
    $adminEmailBody = '
        <html>
        <head><meta charset="UTF-8"><title>Vendor P.O Notification</title></head>
        <body style="margin:0;padding:40px 20px;background:#f4f6f8;font-family:Arial,sans-serif;">
          <div style="max-width:760px;margin:0 auto;padding:20px 0;">
            <div style="max-width:700px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.08);">
              <div style="background:#0d6efd;padding:28px 30px;color:#ffffff;">
                <h2 style="margin:0;font-size:24px;line-height:1.4;">New Vendor P.O Generated</h2>
              </div>
              <div style="padding:36px 30px;">
                <p style="margin-top:0;font-size:15px;color:#333;line-height:1.8;">Hello Admin,</p>
                <p style="font-size:15px;color:#333;line-height:1.8;margin-bottom:24px;">
                  A new Vendor P.O has been generated successfully. The completed PO Voucher is attached.
                </p>
                <table style="width:100%;border-collapse:collapse;margin-top:20px;margin-bottom:24px;">
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>PO Number</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($po_number) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>PO Date</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($formatted_po_date) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Vendor Email</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($email_id) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Total Payable</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">RS. ' . htmlspecialchars($total_payable_formatted) . '</td>
                  </tr>
                </table>
                <p style="margin:0;font-size:15px;color:#333;line-height:1.8;">Regards,<br>Billions United</p>
              </div>
            </div>
          </div>
        </body>
        </html>';

    $vendorEmailBody = '
        <html>
        <head><meta charset="UTF-8"><title>Purchase Order Voucher</title></head>
        <body style="margin:0;padding:40px 20px;background:#f4f6f8;font-family:Arial,sans-serif;">
          <div style="max-width:760px;margin:0 auto;padding:20px 0;">
            <div style="max-width:700px;margin:0 auto;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,0.08);">
              <div style="background:#0d6efd;padding:28px 30px;color:#ffffff;">
                <h2 style="margin:0;font-size:24px;line-height:1.4;">Purchase Order Voucher</h2>
              </div>
              <div style="padding:36px 30px;">
                <p style="margin-top:0;font-size:15px;color:#333;line-height:1.8;">Hello,</p>
                <p style="font-size:15px;color:#333;line-height:1.8;margin-bottom:24px;">
                  Please find attached the Purchase Order Voucher generated for your reference.
                </p>
                <table style="width:100%;border-collapse:collapse;margin-top:20px;margin-bottom:24px;">
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>PO Number</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($po_number) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>PO Date</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">' . htmlspecialchars($formatted_po_date) . '</td>
                  </tr>
                  <tr>
                    <td style="padding:12px;border:1px solid #e5e7eb;background:#f9fafb;"><strong>Total Amount</strong></td>
                    <td style="padding:12px;border:1px solid #e5e7eb;">RS. ' . htmlspecialchars($total_payable_formatted) . '</td>
                  </tr>
                </table>
                <p style="margin:0;font-size:15px;color:#333;line-height:1.8;">Regards,<br>Billions United Team</p>
              </div>
            </div>
          </div>
        </body>
        </html>';

    try {
      // Admin Email
      $adminMail = new PHPMailer(true);
      $adminMail->isSMTP();
      $adminMail->Host = 'Smtp.rediffmailpro.com';
      $adminMail->SMTPAuth = true;
      $adminMail->Username = 'hello@billionsunited.com';
      $adminMail->Password = 'Gautam123#@';
      $adminMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $adminMail->Port = 587;
      $adminMail->setFrom('hello@billionsunited.com', 'Billions United');
      $adminMail->addAddress('hello@billionsunited.com');
      $adminMail->isHTML(true);
      $adminMail->Subject = "New Vendor P.O - {$po_number}";
      $adminMail->Body = $adminEmailBody;
      $adminMail->addAttachment($outputDocxPath);
      $adminMail->send();

      // Vendor Email
      $vendorMail = new PHPMailer(true);
      $vendorMail->isSMTP();
      $vendorMail->Host = 'Smtp.rediffmailpro.com';
      $vendorMail->SMTPAuth = true;
      $vendorMail->Username = 'hello@billionsunited.com';
      $vendorMail->Password = 'Gautam123#@';
      $vendorMail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $vendorMail->Port = 587;
      $vendorMail->setFrom('hello@billionsunited.com', 'Billions United');
      $vendorMail->addAddress($email_id);
      $vendorMail->isHTML(true);
      $vendorMail->Subject = "Purchase Order Voucher - {$po_number}";
      $vendorMail->Body = $vendorEmailBody;
      $vendorMail->addAttachment($outputDocxPath);
      $vendorMail->send();

    } catch (\Exception $e) {
      Log::error("Vendor Email sending failed: " . $e->getMessage());
    }
  }
}
