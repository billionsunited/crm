<?php

namespace App\Services;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use Exception;

class InvoiceService
{
    /**
     * Mark an invoice as paid, generate sequence, and create final PDF.
     */
    public function markAsPaid(Invoice $invoice): array
    {
        if ($invoice->is_paid) {
            return ['success' => false, 'message' => 'Invoice is already paid.'];
        }

        try {
            DB::transaction(function () use ($invoice) {
                // 1. Generate sequence and update model securely
                $invoice->assignFinalSequence();

                // 2. Generate Final PDF
                $view = $invoice->invoice_per_type === 'or' ? 'or_invoices.pdf' : 'invoices.pdf';
                $pdf = Pdf::loadView($view, compact('invoice'));

                // Define new storage path for final invoice
                $filename = "final_{$invoice->id}.pdf";
                $path = 'public/invoices/' . $filename;

                // Store final natively physically to file
                Storage::put($path, $pdf->output());

                // Update the pdf path in DB
                $invoice->update(['pdf_file_path' => $path]);
            });

            Log::info('[Invoice System] Invoice marked as paid', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'financial_year' => $invoice->financial_year
            ]);

            return ['success' => true, 'message' => 'Invoice marked as paid and sequence generated successfully.'];
        } catch (Exception $e) {
            Log::error('[Invoice System] Failed to mark invoice as paid', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Failed to mark as paid due to a system error.'];
        }
    }

    /**
     * Send email via PHPMailer.
     */
    public function sendEmail(Invoice $invoice): array
    {
        $invoice->load(['customer', 'lead']);

        $customerEmail = $invoice->customer?->email_id ?? $invoice->lead?->email_id;
        $alternateEmail = $invoice->lead?->alternate_email_id;
        $alternateEmail2 = $invoice->lead?->alternate_email_id_2;

        //$adminEmail = config('mail.from.address');

        $adminEmail = 'accounts@billionsunited.com';

        if (!$customerEmail) {
            return ['success' => false, 'message' => 'No customer email found for this invoice.'];
        }

        // Ensure PDF is generated/regenerated with the latest templates and signature
        try {
            $view = $invoice->invoice_per_type === 'or' ? 'or_invoices.pdf' : 'invoices.pdf';
            $pdf = Pdf::loadView($view, compact('invoice'));
            if (!$invoice->pdf_file_path) {
                $filename = ($invoice->is_paid ? 'final_' : 'proforma_') . "{$invoice->id}.pdf";
                $invoice->pdf_file_path = 'public/invoices/' . $filename;
                $invoice->save();
            }
            Storage::put($invoice->pdf_file_path, $pdf->output());
        } catch (Exception $e) {
            Log::error('[Invoice System] Failed to generate PDF during email dispatch', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
        }

        if (!$invoice->pdf_file_path || !Storage::exists($invoice->pdf_file_path)) {
            return ['success' => false, 'message' => 'Invoice PDF not found to attach.'];
        }

        try {
            $isOr = $invoice->invoice_per_type === 'or';
            $senderName = $isOr ? 'Orabela' : 'Billions United';
            $senderTeam = $isOr ? 'Orabela Team' : 'Billions United Team';
            $senderWebsiteHtml = $isOr ? '' : "<a href='https://www.billionsunited.com'>www.billionsunited.com</a>";
            $fromName = 'Invoice';
            $fromAddress = 'accounts@billionsunited.com';

            $displayNumber = $invoice->is_paid ? $invoice->invoice_number : 'PROFORMA';
            $pdfPath = Storage::path($invoice->pdf_file_path);
            $companyName = $invoice->customer?->company_name ?? $invoice->lead?->company_name ?? null;
            $companyName = ($companyName && strtoupper($companyName) !== 'NONE') ? $companyName : null;
            $clientName = $invoice->client_name;
            $leadId = $invoice->lead?->record_id ?? $invoice->lead_id;
            $baseName = $invoice->is_paid ? 'Tax Invoice' : 'Proforma Invoice';
            $dateStr = date('d-m-Y');
            
            $parts = array_filter([$baseName, $clientName, $companyName, $leadId, $dateStr]);
            $attachmentName = implode(' - ', $parts) . '.pdf';
            $attachmentName = str_replace(['/', '\\'], '-', $attachmentName);

            // 1. Send Email to CLIENT
            $clientMail = new PHPMailer(true);
            $this->configureSmtp($clientMail);
            $clientMail->setFrom($fromAddress, $fromName);
            $clientMail->addAddress($customerEmail);

            if ($alternateEmail) {
                $clientMail->addCC($alternateEmail);
            }
            if ($alternateEmail2) {
                $clientMail->addCC($alternateEmail2);
            }
            //$clientMail->addCC('vir.vijay@rupeeq.com');

            $clientMail->isHTML(true);

            if ($invoice->is_paid) {
                // PAID: Send PDF + Success Message
                $clientMail->Subject = "Tax Invoice";
                $clientMail->addAttachment($pdfPath, $attachmentName);

                $messageBody = "
                    <p>Dear <strong>{$invoice->client_name}</strong>,</p>
                    <p>Thank you for your payment! We are pleased to inform you that your payment for invoice (<strong>{$invoice->invoice_number}</strong>) has been successfully received.</p>
                    <p>Please find your final <strong>Tax Invoice</strong> attached to this email for your records.</p>
                    <div style='background-color: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                        <h3 style='color: #166534; margin-top: 0;'>Payment Confirmation</h3>
                        <p style='margin: 0; color: #166534;'>Status: <strong>Payment Received & Verified</strong></p>
                    </div>
                ";
            } else {
                // UNPAID: Send Bank Details + QR (No PDF)
                $clientMail->Subject = "Proforma Invoice";

                // Embed QR Code if it exists
                $qrPath = null;
                if ($isOr) {
                    $possiblePaths = [
                        public_path('images/qr-payment-or.jpeg'),
                        public_path('images/qr-payment-or.jpg'),
                        public_path('images/qr-payment-or.jpeg'),
                        public_path('images/qr-payment-or.png'),
                    ];
                    foreach ($possiblePaths as $pathToCheck) {
                        if (file_exists($pathToCheck)) {
                            $qrPath = $pathToCheck;
                            break;
                        }
                    }
                }
                if (!$qrPath) {
                    $qrPath = public_path('images/qr-payment.jpg');
                }

                $qrHtml = '';
                if (file_exists($qrPath)) {
                    $clientMail->addEmbeddedImage($qrPath, 'qrcode_img', 'payment-qr-code.jpg');
                    $qrHtml = "
                        <div style='margin-top: 20px; padding-top: 15px; border-top: 1px solid #e2e8f0;'>
                            <p style='color: #64748b; font-size: 13px; margin-bottom: 10px;'><strong>Alternatively, Scan to Pay:</strong></p>
                            <img src='cid:qrcode_img' alt='Payment QR Code' style='max-width: 200px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);' />
                        </div>
                    ";
                }

                // CLIENT ALSO GETS PROFORMA ATTACHED
                $clientMail->addAttachment($pdfPath, $attachmentName);

                // Define Bank details dynamically based on OR Invoice type
                if ($isOr) {
                    $bankName = "Kotak Mahindra";
                    $accountName = "Orabela";
                    $accountType = "Current";
                    $accountNumber = "5245307327";
                    $branch = "JP Nagar 4th Phase";
                    $ifscCode = "KKBK0000433";
                } else {
                    $bankName = "ICICI";
                    $accountName = "Billions United";
                    $accountType = "Current";
                    $accountNumber = "100705000705";
                    $branch = "Bannerghatta Road, Bangalore";
                    $ifscCode = "ICIC0001007";
                }

                $fromSenderText = $isOr ? '' : " from <strong>{$senderName}</strong>";
                $messageBody = "
                    <p>Dear <strong>{$invoice->client_name}</strong>,</p>
                    <p>We hope you are having a great day.</p>
                    <p>Please find attached the <strong>PROFORMA</strong> invoice{$fromSenderText}.</p>
                    <p>Please find the bank details and QR code below for your payment.</p>
                    
                    <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 25px 0;'>
                        <h3 style='color: #4f46e5; margin-top: 0; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;'>Payment Information</h3>
                        <p style='margin: 10px 0;'>Please complete the payment using the bank details or the QR code below.</p>
                        <table style='width: 100%; font-size: 14px;'>
                            <tr><td style='width: 160px; color: #64748b;'><strong>Bank:</strong></td><td>{$bankName}</td></tr>
                            <tr><td style='color: #64748b;'><strong>A/c Name:</strong></td><td>{$accountName}</td></tr>
                            <tr><td style='color: #64748b;'><strong>A/c Type:</strong></td><td>Current</td></tr>
                            <tr><td style='color: #64748b;'><strong>A/c No:</strong></td><td><strong>{$accountNumber}</strong></td></tr>
                            <tr><td style='color: #64748b;'><strong>Branch:</strong></td><td>{$branch}</td></tr>
                            <tr><td style='color: #64748b;'><strong>RTGS/NEFT IFSC Code:</strong></td><td><strong>{$ifscCode}</strong></td></tr>
                        </table>
                        {$qrHtml}
                    </div>
                    <p>Upon receipt of payment, we will issue your final tax invoice.</p>
                ";
            }

            if ($isOr) {
                if ($invoice->is_paid) {
                    $clientMail->Body = "
                        <div style='font-family: sans-serif; line-height: 1.6; color: #334155;'>
                            {$messageBody}
                            <p>If you have any questions or require further assistance, please feel free to reach out to our team.</p>
                            <p>Thank you for choosing Orabela. We look forward to continuing our partnership.</p>
                            <br>
                            <p>Best Regards & Thanks</p>
                        </div>
                    ";
                } else {
                    $clientMail->Body = "
                        <div style='font-family: sans-serif; line-height: 1.6; color: #334155;'>
                            {$messageBody}
                            <p>If you have any questions or require further assistance, please feel free to reach out to our team.</p>
                            <p>Thank you for choosing us. We look forward to continuing our partnership.</p>
                            <br>
                            <p>Best Regards & Thanks</p>
                        </div>
                    ";
                }
            } else {
                $clientMail->Body = "
                    <div style='font-family: sans-serif; line-height: 1.6; color: #334155;'>
                        {$messageBody}
                        <p>If you have any questions or require further assistance, please feel free to reach out to our team.</p>
                        <p>Thank you for choosing {$senderName}. We look forward to continuing our partnership.</p>
                        <br>
                        <p>Best regards,<br>
                        <strong>{$senderTeam}</strong><br>
                        {$senderWebsiteHtml}</p>
                    </div>
                ";
            }

            $clientMail->send();

            // 2. Send Email to ADMIN
            if ($adminEmail) {
                $statusLabel = $invoice->is_paid ? 'Tax Invoice' : 'Proforma';
                $adminMail = new PHPMailer(true);
                $this->configureSmtp($adminMail);
                $adminMail->setFrom(config('mail.from.address'), 'CRM System');
                $adminMail->addAddress($adminEmail);
                $adminMail->addAttachment($pdfPath, $attachmentName);
                $adminMail->isHTML(true);
                $adminMail->Subject = "({$statusLabel}) - {$senderName}";

                $orgName = $invoice->organisation_name !== 'None' ? $invoice->organisation_name : 'No Organisation';
                $formattedTotal = number_format((float) $invoice->total_invoice_value, (fmod($invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',');

                $adminMail->Body = "
                    <div style='font-family: sans-serif; line-height: 1.6; color: #1e293b;'>
                        <h2 style='color: #4f46e5; margin-bottom: 20px;'>Invoice Dispatch Confirmation</h2>
                        <p>Hello Admin,</p>
                        <p>This is a formal confirmation that the following invoice has been successfully delivered to the client's inbox.</p>
                        
                        <table style='width: 100%; border-collapse: collapse; margin: 25px 0; border: 1px solid #e2e8f0;'>
                            <tr style='background-color: #f8fafc;'>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0; width: 140px;'>Invoice #</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0;'><strong>{$displayNumber}</strong></td>
                            </tr>
                            <tr>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0;'>Status</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0;'><strong>" . ($invoice->is_paid ? 'PAID' : 'UNPAID/PROFORMA') . "</strong></td>
                            </tr>
                            <tr style='background-color: #f8fafc;'>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0;'>Client Name</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0;'>{$invoice->client_name}</td>
                            </tr>
                            <tr>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0;'>Organisation</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0;'>{$orgName}</td>
                            </tr>
                            <tr style='background-color: #f8fafc;'>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0;'>Client Email</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0;'>{$customerEmail}</td>
                            </tr>
                            <tr>
                                <th style='padding: 12px; text-align: left; border: 1px solid #e2e8f0;'>Total Value</th>
                                <td style='padding: 12px; border: 1px solid #e2e8f0; color: #059669; font-weight: bold;'>RS. {$formattedTotal}</td>
                            </tr>
                        </table>

                        <p>The invoice PDF has been attached to this email for your internal records.</p>
                        <br>
                        <p style='color: #64748b; font-size: 13px;'>Regards,<br><strong>" . ($isOr ? 'CRM Automator' : "{$senderName} CRM Automator") . "</strong></p>
                    </div>
                ";
                $adminMail->send();
            }

            Log::info('[Invoice System] Professional emails dispatched successfully', [
                'invoice_id' => $invoice->id,
                'client_recipient' => $customerEmail,
                'admin_recipient' => $adminEmail
            ]);

            return ['success' => true, 'message' => 'Invoice sent to client and admin confirmation recorded.'];
        } catch (PHPMailerException $e) {
            Log::error('[Invoice System] SMTP Error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'SMTP failure. Please check logs.'];
        } catch (Exception $e) {
            Log::error('[Invoice System] Email failure', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return ['success' => false, 'message' => 'An unexpected error occurred.'];
        }
    }

    /**
     * Helper to configure SMTP settings for a PHPMailer instance.
     */
    private function configureSmtp(PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host = config('mail.mailers.smtp.host');
        $mail->SMTPAuth = true;
        $mail->Username = config('mail.mailers.smtp.username');
        $mail->Password = config('mail.mailers.smtp.password');
        $mail->SMTPSecure = config('mail.mailers.smtp.encryption', 'tls');
        $mail->Port = config('mail.mailers.smtp.port');

        // SSL bypass for local development
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }
}
