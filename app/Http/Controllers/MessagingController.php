<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MessagingService;
use App\Models\Lead;
use Illuminate\Support\Facades\Validator;

class MessagingController extends Controller
{
    protected $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    /**
     * Get WhatsApp templates.
     */
    public function getWhatsappTemplates()
    {
        $templates = $this->messagingService->getWhatsappTemplates();
        return response()->json($templates);
    }

    /**
     * Send WhatsApp template message.
     */
    public function sendWhatsappTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'to' => 'required|string',
            'template_name' => 'required|string',
            'parameters' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $leadId = $request->lead_id;
        $templateName = $request->template_name;
        $parameters = $request->parameters ?? [];
        $language = $request->language ?? 'en';
        $headerImage = $request->header_image;

        $lead = Lead::findOrFail($leadId);
        $mobiles = array_filter([$lead->mobile, $lead->alternate_mobile]);
        
        $successCount = 0;
        $failCount = 0;
        $lastResult = null;

        foreach ($mobiles as $to) {
            if (strlen($to) == 10) {
                $to = '+91' . $to;
            }

            $result = $this->messagingService->sendWhatsappTemplate($to, $templateName, $parameters, $leadId, $language, $headerImage);
            $lastResult = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "WhatsApp message sent successfully to {$successCount} numbers.",
                'data' => $lastResult['data'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $lastResult['error'] ?? 'Failed to send WhatsApp message.',
            'data' => $lastResult['data'] ?? null
        ], 500);
    }

    /**
     * Get RCS template.
     */
    public function getRcsTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'template_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $result = $this->messagingService->getRcsTemplate($request->template_name);
        \Log::info('RCS Template Detail:', ['template' => $request->template_name, 'result' => $result]);
        return response()->json($result);
    }

    /**
     * Get RCS templates.
     */
    public function getRcsTemplates()
    {
        $templates = $this->messagingService->getRcsTemplates();
        \Log::info('RCS Templates Response:', ['response' => $templates]);
        return response()->json($templates);
    }

    /**
     * Send RCS message.
     */
    public function sendRcsMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_id' => 'required|exists:leads,id',
            'to' => 'required|string',
            'template_code' => 'required|string',
            'params' => 'nullable|array',
            'bot_id' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $leadId = $request->lead_id;
        $lead = Lead::findOrFail($leadId);
        $mobiles = array_filter([$lead->mobile, $lead->alternate_mobile]);

        $successCount = 0;
        $failCount = 0;
        $lastResult = null;

        foreach ($mobiles as $to) {
            if (strlen($to) == 10) {
                $to = '+91' . $to;
            }

            $result = $this->messagingService->sendRcsMessage(
                $to,
                $request->template_code,
                $request->params,
                $request->bot_id,
                $leadId
            );
            $lastResult = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "RCS message sent successfully to {$successCount} numbers.",
                'data' => $lastResult['data'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $lastResult['error'] ?? 'Failed to send RCS message.',
            'data' => $lastResult['data'] ?? null
        ], 500);
    }
    /**
     * Get SMS templates.
     */
    public function getSmsTemplates()
    {
        $templates = $this->messagingService->getSmsTemplates();
        return response()->json($templates);
    }

    public function sendSmsMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'to' => 'required|string',
            'sms' => 'required|string',
            'senderid' => 'required|string',
            'entityid' => 'required|string',
            'tempid' => 'required|string',
            'unicode' => 'nullable|integer',
            'lead_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $to = $request->to;
        $leadId = $request->lead_id;
        
        $mobiles = [];
        if ($leadId) {
            $lead = Lead::find($leadId);
            if ($lead) {
                $mobiles = array_filter([$lead->mobile, $lead->alternate_mobile]);
            }
        }
        
        if (empty($mobiles)) {
            $mobiles = [$to];
        }

        $successCount = 0;
        $failCount = 0;
        $lastResult = null;

        foreach ($mobiles as $mobile) {
            if (strlen($mobile) == 10) {
                $mobile = '+91' . $mobile;
            }

            $result = $this->messagingService->sendSmsMessage(
                $mobile,
                $request->sms,
                $request->senderid,
                $request->entityid,
                $request->tempid,
                $leadId,
                $request->unicode ?? 0
            );
            $lastResult = $result;

            if ($result['success']) {
                $successCount++;
            } else {
                $failCount++;
            }
        }

        if ($successCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "SMS message sent successfully to {$successCount} numbers.",
                'data' => $lastResult['data'] ?? null
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $lastResult['error'] ?? 'Failed to send SMS message.',
        ], 500);
    }
    /**
     * Get unique contacts for "Send to ALL" functionality.
     */
    public function getAllContacts()
    {
        // Get unique leads by mobile number to avoid duplicate messages to the same customer
        $leads = Lead::select('id', 'customer_name', 'mobile')
            ->whereIn('creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC'])
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('alternate_mobile')->where('alternate_mobile', '!=', '');
                });
            })
            ->get();
            // Removed unique('mobile') since we rely on lead ID to send to all mobiles

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads->values()
        ]);
    }

    /**
     * Get filtered contacts based on current UI filters.
     */
    public function getFilteredContacts(Request $request)
    {
        $query = Lead::select('id', 'customer_name', 'mobile')
            ->whereIn('creation_source', ['CRM', 'CLIENT P.O', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT KYC'])
            ->where(function ($q) {
                $q->where(function ($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function ($q2) {
                    $q2->whereNotNull('alternate_mobile')->where('alternate_mobile', '!=', '');
                });
            });

        // Apply filters (same as LeadController index)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('lead_status')) {
            $query->where('lead_status', $request->lead_status);
        }

        if ($request->filled('customer_type')) {
            $query->where('customer_type', $request->customer_type);
        }

        if ($request->filled('industry')) {
            $query->where('nature_of_industry', 'like', "%{$request->industry}%");
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }

        if ($request->filled('product')) {
            $query->where('initial_product_interest', 'like', "%{$request->product}%");
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
}
