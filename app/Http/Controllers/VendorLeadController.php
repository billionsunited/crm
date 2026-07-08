<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class VendorLeadController extends Controller
{
    public function kyc(Request $request)
    {
        $query = Lead::with('customer')
            ->whereIn('creation_source', ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('aadhar_no', 'like', "%{$search}%");
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

        if ($request->filled('assigned_user')) {
            $query->where('records_owner', $request->assigned_user);
        }

        if ($request->filled('kyc')) {
            $query->where('kyc', $request->kyc);
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

        $leads = $query->paginate(10)->withQueryString();

        // Handle empty page due to deletion or filtering
        if ($leads->isEmpty() && $leads->currentPage() > 1) {
            return redirect($leads->previousPageUrl());
        }

        $title = "Vendor KYC Leads";
        $users = \App\Models\User::all();

        return view('vendor_leads.index', compact('leads', 'title', 'users'));
    }

    public function po(Request $request)
    {
        $query = Lead::with('customer')
            ->whereIn('creation_source', ['VENDOR PO API', 'VENDOR P.O (ADMIN)'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('aadhar_no', 'like', "%{$search}%");
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

        if ($request->filled('assigned_user')) {
            $query->where('records_owner', $request->assigned_user);
        }

        if ($request->filled('kyc')) {
            $query->where('kyc', $request->kyc);
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

        $leads = $query->paginate(10)->withQueryString();

        // Handle empty page due to deletion or filtering
        if ($leads->isEmpty() && $leads->currentPage() > 1) {
            return redirect($leads->previousPageUrl());
        }

        $title = "Vendor PO Leads";
        $users = \App\Models\User::all();

        return view('vendor_leads.index', compact('leads', 'title', 'users'));
    }

    public function sendKycAgreement($id)
    {
        $lead = Lead::findOrFail($id);

        if ($lead->is_agreement_sent) {
            return back()->with('error', 'Agreement has already been sent to this vendor.');
        }

        if (!$lead->msa_document) {
            return back()->with('error', 'No MSA document found for this vendor.');
        }

        if (!$lead->email_id) {
            return back()->with('error', 'No email address found for this vendor.');
        }

        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $this->configureSmtp($mail);

            $poNumber = "BU/" . str_pad($lead->sequence_number, 3, '0', STR_PAD_LEFT) . "/" . ($lead->created_at ? $lead->created_at->format('Y') : date('Y'));

            $mail->setFrom(config('mail.from.address'), config('mail.from.name'));
            $mail->addAddress($lead->email_id);
            $mail->isHTML(true);
           $mail->Subject = "Vendor MSA";

            $body = "
                <div style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                    <p>Dear " . htmlspecialchars($lead->customer_name) . ",</p>
                    <p>Please find attached your Vendor MSA.</p>
                    <p>If you have any questions, feel free to reach out to us.</p>
                    <br>
                    <p>Regards,<br><strong>Billions United Team</strong></p>
                </div>
            ";

            $mail->Body = $body;

            $filePath = storage_path('app/public/vendor_kyc_docs/' . basename($lead->msa_document));
            if (file_exists($filePath)) {
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                $attachmentName = "MSA-".$lead->contact_person;
                if (!empty($lead->company_name) && strtoupper($lead->company_name) !== 'NONE') {
                    $attachmentName .= " - " . $lead->company_name;
                }
                $attachmentName .= "." . $extension;

                $mail->addAttachment($filePath, $attachmentName);
            } else {
                return back()->with('error', 'Document file not found on server.');
            }

            $mail->send();

            // Update sent status
            $lead->update(['is_agreement_sent' => true]);

            return back()->with('success', 'KYC Agreement email sent successfully to ' . $lead->email_id);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    public function getAllContacts(Request $request)
    {
        $type = $request->input('type', 'kyc');
        $sources = $type === 'kyc'
            ? ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION']
            : ['VENDOR PO API', 'VENDOR P.O (ADMIN)'];
            
        $leads = Lead::select('id', 'customer_name', 'mobile')
            ->whereIn('creation_source', $sources)
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('alternate_mobile')->where('alternate_mobile', '!=', '');
                })->orWhere(function ($q3) {
                    $q3->whereNotNull('alternate_mobile_2')->where('alternate_mobile_2', '!=', '');
                });
            })
            ->get();

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads->values()
        ]);
    }

    public function getFilteredContacts(Request $request)
    {
        $type = $request->input('type', 'kyc');
        $sources = $type === 'kyc'
            ? ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION']
            : ['VENDOR PO API', 'VENDOR P.O (ADMIN)'];
            
        $query = Lead::select('id', 'customer_name', 'mobile')
            ->whereIn('creation_source', $sources)
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('alternate_mobile')->where('alternate_mobile', '!=', '');
                })->orWhere(function ($q3) {
                    $q3->whereNotNull('alternate_mobile_2')->where('alternate_mobile_2', '!=', '');
                });
            });

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id', 'like', "%{$search}%")
                    ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                    ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('aadhar_no', 'like', "%{$search}%");
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

        if ($request->filled('assigned_user')) {
            $query->where('records_owner', $request->assigned_user);
        }

        if ($request->filled('kyc')) {
            $query->where('kyc', $request->kyc);
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

        $leads = $query->get();

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads->values()
        ]);
    }

    public function sendEmailCampaign(Request $request)
    {
        abort_if(!auth()->user()->can('email-template-send'), 403);

        $request->validate([
            'template_id' => 'required|exists:email_templates,id',
            'theme' => 'nullable|string|in:default,emerald,purple,charcoal',
            'ids' => 'nullable|string',
            'is_filtered_campaign' => 'nullable|string',
            'type' => 'nullable|string'
        ]);

        $template = \App\Models\EmailTemplate::findOrFail($request->template_id);

        $type = $request->input('type', 'kyc');
        $sources = $type === 'kyc'
            ? ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION']
            : ['VENDOR PO API', 'VENDOR P.O (ADMIN)'];

        try {
            $query = Lead::select('id', 'customer_name', 'email_id', 'alternate_email_id', 'alternate_email_id_2')
                ->whereIn('creation_source', $sources)
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
                // Apply same filters as getFilteredContacts
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email_id', 'like', "%{$search}%")
                            ->orWhere('alternate_email_id', 'like', "%{$search}%")
                            ->orWhere('alternate_mobile_2', 'like', "%{$search}%")
                            ->orWhere('alternate_email_id_2', 'like', "%{$search}%")
                            ->orWhere('record_id', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('aadhar_no', 'like', "%{$search}%");
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

                if ($request->filled('assigned_user')) {
                    $query->where('records_owner', $request->assigned_user);
                }

                if ($request->filled('kyc')) {
                    $query->where('kyc', $request->kyc);
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

            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'Smtp.rediffmailpro.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'hello@billionsunited.com';
                $mail->Password = 'Gautam123#@';
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
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
            } catch (\Exception $e) {
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

    private function configureSmtp(\PHPMailer\PHPMailer\PHPMailer $mail): void
    {
        $mail->isSMTP();
        $mail->Host = config('mail.mailers.smtp.host');
        $mail->SMTPAuth = true;
        $mail->Username = config('mail.mailers.smtp.username');
        $mail->Password = config('mail.mailers.smtp.password');
        $mail->SMTPSecure = config('mail.mailers.smtp.encryption', 'tls');
        $mail->Port = config('mail.mailers.smtp.port');

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            ]
        ];
    }
}
