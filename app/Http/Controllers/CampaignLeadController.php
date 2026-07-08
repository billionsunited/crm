<?php

namespace App\Http\Controllers;

use App\Models\CampaignLead;
use App\Models\Customer;
use App\Models\Lead;
use App\Services\MessagingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CampaignLeadController extends Controller
{
    protected $messagingService;

    public function __construct(MessagingService $messagingService)
    {
        $this->messagingService = $messagingService;
    }

    public function index(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-view'), 403);
        $query = CampaignLead::query()
            ->addSelect([
                'original_id' => DB::table('campaign_leads as cl2')->select('cl2.id')
                    ->where(function($q) {
                        $q->whereColumn('cl2.mobile', 'campaign_leads.mobile')
                          ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_1')
                          ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_2');
                    })
                    ->whereNotNull('cl2.mobile')
                    ->where('cl2.mobile', '!=', '')
                    ->orderBy('cl2.id', 'asc')
                    ->limit(1)
            ])
            ->addSelect([
                'crm_record_id' => DB::table('leads')->select('record_id')
                    ->where(function($q) {
                        $q->whereColumn('leads.mobile', 'campaign_leads.mobile')
                          ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_1')
                          ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_2');
                    })
                    ->whereNotNull('leads.mobile')
                    ->where('leads.mobile', '!=', '')
                    ->orderBy('id', 'asc')
                    ->limit(1)
            ])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%")
                    ->orWhere('mobile_2', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('email_id_1', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rate')) {
            $query->where('rate', $request->rate);
        }

        if ($request->filled('duplicate')) {
            if ($request->duplicate === 'converted') {
                $query->where(function($q) {
                    $q->whereIn('campaign_leads.mobile', function($sub) {
                        $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                    })->orWhereIn('campaign_leads.mobile_1', function($sub) {
                        $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                    })->orWhereIn('campaign_leads.mobile_2', function($sub) {
                        $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                    });
                });
            } elseif ($request->duplicate === 'not_converted') {
                $query->whereNotIn('campaign_leads.mobile', function($sub) {
                    $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                })->whereNotIn('campaign_leads.mobile_1', function($sub) {
                    $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                })->whereNotIn('campaign_leads.mobile_2', function($sub) {
                    $sub->select('mobile')->from('leads')->whereNotNull('mobile')->where('mobile', '!=', '');
                });
            } elseif ($request->duplicate === 'enquiry_duplicate') {
                $query->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('campaign_leads as cl2')
                        ->where(function($q) {
                            $q->whereColumn('cl2.mobile', 'campaign_leads.mobile')
                              ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_1')
                              ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_2');
                        })
                        ->whereNotNull('cl2.mobile')
                        ->where('cl2.mobile', '!=', '')
                        ->whereColumn('cl2.id', '<', 'campaign_leads.id');
                });
            }
        }

        $leads = $query->paginate(100)->withQueryString();

        // Check for duplicates with Client (Customer/Lead)
        foreach ($leads as $lead) {
            $lead->crm_duplicate = !empty($lead->crm_record_id) ? 'Duplicate of Lead #' . $lead->crm_record_id : null;
            $lead->campaign_duplicate = (isset($lead->original_id) && $lead->id > $lead->original_id) ? 'Duplicate of #' . $lead->original_id : null;
        }

        return view('campaign_leads.index', compact('leads'));
    }

    public function create()
    {
        abort_if(!auth()->user()->can('campaign-add'), 403);
        return view('campaign_leads.create');
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-add'), 403);
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'mobile_1' => 'nullable|string|max:20',
            'mobile_2' => 'nullable|string|max:20',
            'email_id' => 'nullable|email|max:255',
            'email_id_1' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'type_of_firm' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'product_interested' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'rate' => 'nullable|string',
        ]);

        $validated['source'] = 'CRM';
        CampaignLead::create($validated);

        return redirect()->route('campaign-leads.index')->with('success', 'Campaign lead created successfully.');
    }

    public function show(CampaignLead $campaignLead)
    {
        abort_if(!auth()->user()->can('campaign-view'), 403);
        return view('campaign_leads.show', compact('campaignLead'));
    }

    public function edit(CampaignLead $campaignLead)
    {
        abort_if(!auth()->user()->can('campaign-edit'), 403);
        return view('campaign_leads.edit', compact('campaignLead'));
    }

    public function update(Request $request, CampaignLead $campaignLead)
    {
        abort_if(!auth()->user()->can('campaign-edit'), 403);
        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'mobile_1' => 'nullable|string|max:20',
            'mobile_2' => 'nullable|string|max:20',
            'email_id' => 'nullable|email|max:255',
            'email_id_1' => 'nullable|email|max:255',
            'company_name' => 'nullable|string|max:255',
            'type_of_firm' => 'nullable|string|max:255',
            'place' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'product_interested' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:255',
            'comment' => 'nullable|string',
            'rate' => 'nullable|string',
        ]);

        $campaignLead->update($validated);

        return redirect()->route('campaign-leads.show', $campaignLead->id)->with('success', 'Campaign lead updated successfully.');
    }

    public function updateBlacklistFlag(Request $request, CampaignLead $campaignLead)
    {
        $request->validate([
            'blacklist_flag' => 'required|integer|min:0|max:3',
        ]);

        $campaignLead->update([
            'blacklist_flag' => $request->blacklist_flag,
        ]);

        return response()->json([
            'success' => true,
            'blacklist_flag' => $campaignLead->blacklist_flag
        ]);
    }

    public function destroy(CampaignLead $campaignLead)
    {
        abort_if(!auth()->user()->can('campaign-delete'), 403);
        $campaignLead->delete();
        return redirect()->route('campaign-leads.index')->with('success', 'Campaign lead deleted successfully.');
    }

    public function bulkDestroy(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-delete'), 403);
        $ids = $request->ids;
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (empty($ids)) {
            return back()->with('error', 'No leads selected.');
        }

        CampaignLead::whereIn('id', $ids)->delete();
        return back()->with('success', count($ids) . ' leads deleted successfully.');
    }

    public function deleteAll(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-delete'), 403);

        $query = CampaignLead::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%")
                    ->orWhere('mobile_2', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('email_id_1', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rate')) {
            $query->where('rate', $request->rate);
        }

        if ($request->filled('duplicate')) {
            if ($request->duplicate === 'converted') {
                $query->where(function($q) {
                    $q->whereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    })->orWhereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile_1')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    })->orWhereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile_2')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    });
                });
            } elseif ($request->duplicate === 'not_converted') {
                $query->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('leads')
                        ->where(function($q) {
                            $q->whereColumn('leads.mobile', 'campaign_leads.mobile')
                              ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_1')
                              ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_2');
                        })
                        ->whereNotNull('leads.mobile')
                        ->where('leads.mobile', '!=', '');
                });
            } elseif ($request->duplicate === 'enquiry_duplicate') {
                $query->whereExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('campaign_leads as cl2')
                        ->where(function($q) {
                            $q->whereColumn('cl2.mobile', 'campaign_leads.mobile')
                              ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_1')
                              ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_2');
                        })
                        ->whereNotNull('cl2.mobile')
                        ->where('cl2.mobile', '!=', '')
                        ->whereColumn('cl2.id', '<', 'campaign_leads.id');
                });
            }
        }

        $count = $query->count();
        if ($count === 0) {
            return redirect()->route('campaign-leads.index')->with('info', 'No campaign leads found to delete.');
        }

        $query->delete();

        $message = $request->hasAny(['search', 'rate', 'duplicate'])
            ? "All {$count} filtered campaign leads have been deleted successfully."
            : "All {$count} campaign leads have been deleted successfully.";

        return redirect()->route('campaign-leads.index')->with('success', $message);
    }

    public function export(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-export'), 403);
        $type = $request->input('type', 'all');
        $query = CampaignLead::latest();

        if ($type === 'selected') {
            $ids = explode(',', $request->input('ids', ''));
            $query->whereIn('id', $ids);
        }

        $fileName = 'campaign-leads-export-' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Name',
            'Mobile',
            'Mobile 1',
            'Mobile 2',
            'Email',
            'Alternate Email',
            'Company Name',
            'Type of Firm',
            'Place',
            'Address',
            'Product Interested',
            'Lead Type',
            'Reference',
            'Comment'
        ];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $query->chunk(100, function ($leads) use ($file) {
                foreach ($leads as $lead) {
                    fputcsv($file, [
                        $lead->customer_name,
                        $lead->mobile,
                        $lead->mobile_1,
                        $lead->mobile_2,
                        $lead->email_id,
                        $lead->email_id_1,
                        $lead->company_name,
                        $lead->type_of_firm,
                        $lead->place,
                        $lead->address,
                        $lead->product_interested,
                        $lead->rate,
                        $lead->reference,
                        $lead->comment,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function import(Request $request)
    {
        abort_if(!auth()->user()->can('campaign-import'), 403);
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('csv_file');

        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');

            if (!$headers) {
                fclose($handle);
                return back()->with('error', 'The CSV file is empty.');
            }

            // Convert headers to UTF-8 to handle any Windows-1252 / ISO-8859-1 header chars
            $headers = array_map(function($header) {
                return mb_convert_encoding($header, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
            }, $headers);

            // Simple mapping logic
            $headerMap = [
                'name' => 'customer_name',
                'customer_name' => 'customer_name',
                'mobile' => 'mobile',
                'mobile_1' => 'mobile_1',
                'mobile_2' => 'mobile_2',
                'email' => 'email_id',
                'email_id' => 'email_id',
                'alternate_email' => 'email_id_1',
                'email_1' => 'email_id_1',
                'company' => 'company_name',
                'company_name' => 'company_name',
                'firm_type' => 'type_of_firm',
                'type_of_firm' => 'type_of_firm',
                'place' => 'place',
                'city' => 'place',
                'address' => 'address',
                'product' => 'product_interested',
                'product_interested' => 'product_interested',
                'reference' => 'reference',
                'comment' => 'comment',
                'rate' => 'rate',
                'lead_type' => 'rate',
            ];

            $imported = 0;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                // Convert values to UTF-8 to avoid SQLSTATE 22007 encoding errors
                $data = array_map(function($val) {
                    if ($val === null) return null;
                    return mb_convert_encoding($val, 'UTF-8', 'UTF-8, ISO-8859-1, Windows-1252');
                }, $data);

                $row = [];
                foreach ($headers as $index => $label) {
                    $cleanLabel = strtolower(str_replace([' ', '_'], ['_', '_'], trim($label)));
                    if (isset($headerMap[$cleanLabel])) {
                        $row[$headerMap[$cleanLabel]] = $data[$index] ?? null;
                    }
                }

                if (!empty($row)) {
                    $row['source'] = 'Import';
                    CampaignLead::create($row);
                    $imported++;
                }
            }

            fclose($handle);
            return back()->with('success', "Successfully imported {$imported} leads.");
        }

        return back()->with('error', 'Failed to read the file.');
    }

    public function sendCampaign(Request $request)
    {
        $lead = CampaignLead::findOrFail($request->lead_id);
        $type = $request->input('type'); // whatsapp, rcs, sms

        $mobiles = array_filter([$lead->mobile, $lead->mobile_1, $lead->mobile_2]);
        $successCount = 0;
        $failCount = 0;

        foreach ($mobiles as $mobile) {
            // Ensure mobile number has country code for India if not present
            $mobile = str_replace(' ', '', $mobile);
            if (strlen($mobile) == 10) {
                $mobile = '+91' . $mobile;
            }

            try {
                $result = ['success' => false];
                if ($type === 'whatsapp') {
                    $result = $this->messagingService->sendWhatsappTemplate(
                        $mobile,
                        $request->template_name,
                        $request->parameters ?? [],
                        null,
                        $request->language ?? 'en',
                        $request->header_image,
                        $lead->id
                    );
                } elseif ($type === 'rcs') {
                    $result = $this->messagingService->sendRcsMessage(
                        $mobile,
                        $request->template_code,
                        $request->params ?? [],
                        $request->bot_id,
                        null,
                        $lead->id
                    );
                } elseif ($type === 'sms') {
                    $result = $this->messagingService->sendSmsMessage(
                        $mobile,
                        $request->sms,
                        $request->senderid,
                        $request->entityid,
                        $request->tempid,
                        null,
                        $request->unicode ?? 0,
                        $lead->id
                    );
                }

                if ($result['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            } catch (\Exception $e) {
                Log::error("Campaign failed for {$mobile}: " . $e->getMessage());
                $failCount++;
            }
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => "Sent to {$successCount} numbers, {$failCount} failed for lead #{$lead->id}"
        ]);
    }

    public function sampleCsv()
    {
        $fileName = 'campaign-leads-sample.csv';
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
        ];

        $columns = [
            'Name',
            'Mobile',
            'Mobile 1',
            'Mobile 2',
            'Email',
            'Alternate Email',
            'Company Name',
            'Type of Firm',
            'Place',
            'Address',
            'Product Interested',
            'Lead Type',
            'Comment'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);
            fputcsv($file, [
                'John Doe',
                '9876543210',
                '9876543211',
                '9876543212',
                'john@example.com',
                'alternate@example.com',
                'Acme Corp',
                'Private Limited',
                'Mumbai',
                '123 Main St, Mumbai, India',
                'SMS, RCS',
                'Qualify',
                'Interested in bulk SMS'
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getAllContacts()
    {
        $leads = CampaignLead::select('id', 'customer_name', 'mobile')
            ->where(function($q) {
                $q->where(function($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function($q2) {
                    $q2->whereNotNull('mobile_1')->where('mobile_1', '!=', '');
                })->orWhere(function($q3) {
                    $q3->whereNotNull('mobile_2')->where('mobile_2', '!=', '');
                });
            })
            ->get();

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads
        ]);
    }

    public function getFilteredContacts(Request $request)
    {
        $query = CampaignLead::select('id', 'customer_name', 'mobile')
            ->where(function($q) {
                $q->where(function($q1) {
                    $q1->whereNotNull('mobile')->where('mobile', '!=', '');
                })->orWhere(function($q2) {
                    $q2->whereNotNull('mobile_1')->where('mobile_1', '!=', '');
                })->orWhere(function($q3) {
                    $q3->whereNotNull('mobile_2')->where('mobile_2', '!=', '');
                });
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('mobile_1', 'like', "%{$search}%")
                    ->orWhere('mobile_2', 'like', "%{$search}%")
                    ->orWhere('email_id', 'like', "%{$search}%")
                    ->orWhere('email_id_1', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('place', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rate')) {
            $query->where('rate', $request->rate);
        }

        if ($request->filled('duplicate')) {
            if ($request->duplicate === 'converted') {
                $query->where(function($q) {
                    $q->whereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    })->orWhereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile_1')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    })->orWhereExists(function($sub) {
                        $sub->select(DB::raw(1))
                            ->from('leads')
                            ->whereColumn('leads.mobile', 'campaign_leads.mobile_2')
                            ->whereNotNull('leads.mobile')
                            ->where('leads.mobile', '!=', '');
                    });
                });
            } elseif ($request->duplicate === 'not_converted') {
                $query->whereNotExists(function($sub) {
                    $sub->select(DB::raw(1))
                        ->from('leads')
                        ->where(function($q) {
                            $q->whereColumn('leads.mobile', 'campaign_leads.mobile')
                              ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_1')
                              ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_2');
                        })
                        ->whereNotNull('leads.mobile')
                        ->where('leads.mobile', '!=', '');
                });
            }
        }

        $leads = $query->get();

        return response()->json([
            'count' => $leads->count(),
            'leads' => $leads
        ]);
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
            $query = CampaignLead::select('id', 'customer_name', 'email_id', 'email_id_1')
                ->where(function($q) {
                    $q->where(function($q1) {
                        $q1->whereNotNull('email_id')->where('email_id', '!=', '');
                    })->orWhere(function($q2) {
                        $q2->whereNotNull('email_id_1')->where('email_id_1', '!=', '');
                    });
                });

            if ($request->is_filtered_campaign === 'true') {
                // Apply same filters as index
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->where(function ($q) use ($search) {
                        $q->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('mobile_1', 'like', "%{$search}%")
                            ->orWhere('mobile_2', 'like', "%{$search}%")
                            ->orWhere('email_id', 'like', "%{$search}%")
                            ->orWhere('email_id_1', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%")
                            ->orWhere('place', 'like', "%{$search}%")
                            ->orWhere('reference', 'like', "%{$search}%");
                    });
                }
                if ($request->filled('rate')) {
                    $query->where('rate', $request->rate);
                }
                if ($request->filled('duplicate')) {
                    if ($request->duplicate === 'converted') {
                        $query->where(function($q) {
                            $q->whereExists(function($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('leads')
                                    ->whereColumn('leads.mobile', 'campaign_leads.mobile')
                                    ->whereNotNull('leads.mobile')
                                    ->where('leads.mobile', '!=', '');
                            })->orWhereExists(function($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('leads')
                                    ->whereColumn('leads.mobile', 'campaign_leads.mobile_1')
                                    ->whereNotNull('leads.mobile')
                                    ->where('leads.mobile', '!=', '');
                            })->orWhereExists(function($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('leads')
                                    ->whereColumn('leads.mobile', 'campaign_leads.mobile_2')
                                    ->whereNotNull('leads.mobile')
                                    ->where('leads.mobile', '!=', '');
                            });
                        });
                    } elseif ($request->duplicate === 'not_converted') {
                        $query->whereNotExists(function($sub) {
                            $sub->select(DB::raw(1))
                                ->from('leads')
                                ->where(function($q) {
                                    $q->whereColumn('leads.mobile', 'campaign_leads.mobile')
                                      ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_1')
                                      ->orWhereColumn('leads.mobile', 'campaign_leads.mobile_2');
                                })
                                ->whereNotNull('leads.mobile')
                                ->where('leads.mobile', '!=', '');
                        });
                    } elseif ($request->duplicate === 'enquiry_duplicate') {
                        $query->whereExists(function($sub) {
                            $sub->select(DB::raw(1))
                                ->from('campaign_leads as cl2')
                                ->where(function($q) {
                                    $q->whereColumn('cl2.mobile', 'campaign_leads.mobile')
                                      ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_1')
                                      ->orWhereColumn('cl2.mobile', 'campaign_leads.mobile_2');
                                })
                                ->whereNotNull('cl2.mobile')
                                ->where('cl2.mobile', '!=', '')
                                ->whereColumn('cl2.id', '<', 'campaign_leads.id');
                        });
                    }
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
                if (!empty($lead->email_id_1)) {
                    $emailList[] = trim($lead->email_id_1);
                }
            }
            // Remove duplicates
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

                $successCount = 0;
                foreach ($emailList as $email) {
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

                if ($successCount === 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to send campaign emails to any of the selected leads.'
                    ], 500);
                }

                return response()->json([
                    'success' => true,
                    'message' => "Campaign sent successfully to " . $successCount . " of " . count($emailList) . " leads."
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
}
