<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Lead;
use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Services\InvoiceService;
use Illuminate\Support\Facades\URL;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with('lead')->where('invoice_per_type', 'standard')->latest();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('organisation_name', 'like', "%{$search}%")
                  // Search via lead_id relationship (newer invoices)
                  ->orWhereHas('lead', function($sub) use ($search) {
                      $sub->where('reference', 'like', "%{$search}%")
                          ->orWhere('sequence_number', 'like', "%{$search}%");
                  })
                  // Fallback: search via customer_id → leads (covers older invoices where lead_id may be NULL)
                  ->orWhereHas('customer.leads', function($sub) use ($search) {
                      $sub->where('reference', 'like', "%{$search}%")
                          ->orWhere('sequence_number', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('client_filter') && $request->client_filter != '') {
            $filter = $request->client_filter;
            $query->whereRaw("
                CASE 
                    WHEN organisation_name IS NOT NULL AND organisation_name != 'None' THEN CONCAT(organisation_name, ' - ', client_name) 
                    ELSE client_name 
                END = ?
            ", [$filter]);
        }

        $invoices = $query->paginate(50)->withQueryString();

        // Handle empty page due to deletion or filtering
        if ($invoices->isEmpty() && $invoices->currentPage() > 1) {
            return redirect($invoices->previousPageUrl());
        }

        // Fetch unique display names from Invoices table for the filter dropdown
        $clients = Invoice::where('invoice_per_type', 'standard')
            ->select(\Illuminate\Support\Facades\DB::raw("
            CASE 
                WHEN organisation_name IS NOT NULL AND organisation_name != 'None' THEN CONCAT(organisation_name, ' - ', client_name) 
                ELSE client_name 
            END as display_name
        "), \Illuminate\Support\Facades\DB::raw("COUNT(*) as invoices_count"))
            ->groupBy('display_name')
            ->orderBy('display_name')
            ->get()
            ->map(function ($item) {
                return (object) [
                    'name' => $item->display_name,
                    'invoices_count' => $item->invoices_count
                ];
            });

        return view('invoices.index', compact('invoices', 'clients'));
    }

    public function export(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        $query = Invoice::with(['items', 'customer'])
            ->where('invoice_per_type', 'standard')
            ->where('is_paid', true)
            ->whereYear('paid_at', $year)
            ->whereMonth('paid_at', $month)
            ->orderBy('paid_at', 'asc')
            ->orderBy('id', 'asc');

        if (!$query->exists()) {
            return back()->with('error', 'No invoices found for the selected month and year.');
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $fileName = "invoices-export-{$monthName}-{$year}.csv";

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $columns = [
            'Invoice Number',
            'Date',
            'Paid Date',
            'Cancelled',
            'Client Name',
            'Organisation Name',
            'City',
            'State',
            'State Code',
            'GSTIN',
            'Tax Type',
            'Item HSN/SAC',
            'Service Totals',
            'Taxable Value',
            'CGST Amount',
            'SGST Amount',
            'IGST Amount',
            'Total Invoice',
            'Service Description Meta',
            'Financial Year',
            'Invoice Link',
        ];

        $callback = function () use ($query, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns);

            $query->chunk(100, function ($invoices) use ($file) {
                foreach ($invoices as $invoice) {
                    $items = $invoice->items;
                    
                    // Join unique HSN/SAC codes, fallback to 998599 if empty
                    $hsnSacs = $items->pluck('hsn_sac')->filter()->unique()->implode(', ');
                    if (empty($hsnSacs)) {
                        $hsnSacs = '998599';
                    }
                    
                    $serviceTotals = $items->map(function($item) {
                        return $item->service_name . ': ' . $item->total;
                    })->implode(' | ');

                    $row = [
                        $invoice->invoice_number,
                        $invoice->invoice_date ? $invoice->invoice_date->format('Y-m-d') : '',
                        $invoice->paid_at ? $invoice->paid_at->format('Y-m-d H:i:s') : '',
                        $invoice->is_cancelled ? 'Yes' : 'No',
                        $invoice->client_name,
                        $invoice->organisation_name,
                        $invoice->city,
                        $invoice->customer->state_name ?? ($invoice->state ?? 'Karnataka'),
                        $invoice->customer->state_code ?? ($invoice->state_code ?? '29'),
                        strtoupper($invoice->gstin_unique_id),
                        $invoice->tax_type,
                        $hsnSacs ?: '',
                        $serviceTotals,
                        $invoice->taxable_value,
                        $invoice->cgst_amount,
                        $invoice->sgst_amount,
                        $invoice->igst_amount,
                        $invoice->total_invoice_value,
                        $invoice->service_description_meta,
                        $invoice->financial_year,
                        '=HYPERLINK("' . URL::signedRoute('invoices.download-page', ['invoice' => $invoice->id]) . '","Download Invoice")',
                    ];
                    fputcsv($file, $row);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function downloadZip(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020|max:2100',
        ]);

        $month = $request->input('month');
        $year = $request->input('year');

        $invoices = Invoice::with(['items', 'customer', 'lead'])
            ->where('invoice_per_type', 'standard')
            ->whereYear('invoice_date', $year)
            ->whereMonth('invoice_date', $month)
            ->orderBy('invoice_date', 'asc')
            ->get();

        if ($invoices->isEmpty()) {
            return back()->with('error', 'No invoices found for the selected month and year.');
        }

        $monthName = date('F', mktime(0, 0, 0, $month, 10));
        $zipFileName = "invoices-{$monthName}-{$year}.zip";
        $zipFilePath = storage_path('app/public/tmp_' . time() . '.zip');

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($invoices as $invoice) {
                $companyName = $invoice->customer?->company_name ?? $invoice->lead?->company_name ?? null;
                $companyName = ($companyName && strtoupper($companyName) !== 'NONE') ? $companyName : null;
                $invoiceNumber = ($invoice->is_paid || $invoice->is_cancelled) ? $invoice->invoice_number : 'PROFORMA';
                $clientName = $invoice->client_name;
                $leadId = $invoice->lead?->record_id ?? $invoice->lead_id;
                $dateStr = $invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : date('d-m-Y');
                
                $invoiceType = $invoice->is_paid ? 'Tax Invoice' : ($invoice->is_cancelled ? 'Cancelled Invoice' : 'Proforma Invoice');
                $parts = array_filter([$invoiceType, $invoiceNumber, $clientName, $companyName, $leadId, $dateStr]);
                $downloadName = implode(' - ', $parts) . '.pdf';
                $downloadName = str_replace(['/', '\\'], '-', $downloadName);

                if ($invoice->pdf_file_path && Storage::exists($invoice->pdf_file_path)) {
                    // File exists, add it from storage
                    $pdfContent = Storage::get($invoice->pdf_file_path);
                    $zip->addFromString($downloadName, $pdfContent);
                } else {
                    // Generate it on the fly
                    $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
                    $zip->addFromString($downloadName, $pdf->output());
                }
            }
            $zip->close();
        } else {
            return back()->with('error', 'Failed to create zip file.');
        }

        return response()->download($zipFilePath, $zipFileName)->deleteFileAfterSend(true);
    }

    public function create()
    {
        // Load only customers who have 'Client KYC' or 'Client Terms' leads
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
                    'state' => $customer?->state_name ?? $lead->state ?? 'Karnataka',
                    'state_code' => $customer?->state_code ?? $lead->state_code ?? '29',
                    'udyam_certificate' => $lead->udyam_registration_certificate ?? 'None',
                    'pan_no' => $lead->pan_number ?? 'None',
                    'aadhar_no' => $lead->aadhar_no ?? 'None',
                    'gstin_unique_id' => $lead->gst_no ?? 'None',
                    'reference' => $lead->reference ?? 'None',
                ];
            });
        return view('invoices.create', compact('leads'));
    }

    private function formatNumber($value)
    {
        return fmod($value, 1) == 0
            ? (int) $value
            : round($value, 2);
    }

    public function store(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'client_name' => 'required|string|max:255',
            'organisation_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:255',
            'purchase_order' => 'nullable|string|max:255',
            'service_description_meta' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'udyam_certificate' => 'nullable|string|max:255',
            'pan_no' => 'nullable|string|max:255',
            'aadhar_no' => 'nullable|string|max:255',
            'gstin_unique_id' => 'nullable|string|max:255',
            'tax_type' => 'required|in:local,outstation',
            'items' => 'required|array|min:1',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.hsn_sac' => 'nullable|string|max:255',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        $lead = Lead::with('customer')->findOrFail($request->lead_id);

        // Security Check: Ensure the lead source is allowed for invoicing
        $targetSources = array_merge(
            Lead::SOURCE_GROUPS['CLIENT_KYC'],
            Lead::SOURCE_GROUPS['CLIENT_TERMS'],
            Lead::SOURCE_GROUPS['CRM']
        );
        if (!in_array($lead->creation_source, $targetSources)) {
            return redirect()->back()->with('error', 'Invoices can only be created for Client KYC, Client Terms, or CRM leads.');
        }

        // Calculate Totals backend side for consistency
        $taxableValue = 0;
        $dbItems = [];
        foreach ($request->items as $item) {
            $qty = ($item['qty'] !== null && $item['qty'] !== '') ? (float) $item['qty'] : null;
            $rate = ($item['rate'] !== null && $item['rate'] !== '') ? (float) $item['rate'] : null;
            $total = ($item['total'] !== null && $item['total'] !== '') ? (float) $item['total'] : null;

            // If total isn't provided but qty and rate are, calculate it
            if ($total === null && $qty !== null && $rate !== null) {
                $total = round($qty * $rate, 2);
            }

            $dbItems[] = [
                'service_name' => $item['service_name'],
                'hsn_sac' => $item['hsn_sac'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'total' => $total ?? 0,
            ];
            $taxableValue += ($total ?? 0);
        }

        $cgst_amount = 0;
        $sgst_amount = 0;
        $igst_amount = 0;

        if ($request->tax_type === 'local') {
            $cgst_amount = round($taxableValue * 0.09, 2);
            $sgst_amount = round($taxableValue * 0.09, 2);
        } else {
            $igst_amount = round($taxableValue * 0.18, 2);
        }

        $totalInvoiceValue = $this->formatNumber($taxableValue + $cgst_amount + $sgst_amount + $igst_amount);
        $totalWords = $this->amountInWords($totalInvoiceValue);

        // Generate Invoice specific data
        $invoice = Invoice::create([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
            'invoice_number' => Invoice::generateSequenceNumber(),
            'invoice_date' => now(),
            'invoice_per_type' => 'standard',

            // Strictly map Snapshot fields directly from user Request Form inputs!
            'client_name' => $request->client_name ?? 'None',
            'organisation_name' => $request->organisation_name ?? 'None',
            'address' => $request->address ?? 'None',
            'city' => $request->city ?? 'None',
            'state' => $request->state ?? 'Karnataka',
            'state_code' => $request->state_code ?? '29',
            'purchase_order' => $request->purchase_order ?? 'NA',
            'service_description_meta' => $request->service_description_meta ?? 'Marketing Services',
            'due_date' => $request->due_date ? date('Y-m-d', strtotime($request->due_date)) : null,
            'udyam_certificate' => $request->udyam_certificate ?? 'None',
            'pan_no' => $request->pan_no ?? 'None',
            'aadhar_no' => $request->aadhar_no ?? 'None',
            'gstin_unique_id' => $request->gstin_unique_id ?? 'None',

            'tax_type' => $request->tax_type,
            'taxable_value' => $this->formatNumber($taxableValue),
            'cgst_percent' => ($request->tax_type === 'local' ? 9 : 0),
            'cgst_amount' => $this->formatNumber($cgst_amount),
            'sgst_percent' => ($request->tax_type === 'local' ? 9 : 0),
            'sgst_amount' => $this->formatNumber($sgst_amount),
            'igst_percent' => ($request->tax_type === 'outstation' ? 18 : 0),
            'igst_amount' => $this->formatNumber($igst_amount),
            'total_invoice_value' => $this->formatNumber($totalInvoiceValue),
            'total_invoice_value_words' => $totalWords,
        ]);

        foreach ($dbItems as $dbItem) {
            $invoice->items()->create($dbItem);
        }

        // Generate PDF immediately natively to freeze the historical record
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        // Define storage path
        $filename = "proforma_{$invoice->id}.pdf";
        $path = 'public/invoices/' . $filename;

        // Store natively physically to file
        Storage::put($path, $pdf->output());

        $invoice->update(['pdf_file_path' => $path]);

        return redirect()->route('invoices.show', $invoice->id)->with('success', 'Invoice generated successfully.');
    }

    private function enforceStandardType(Invoice $invoice)
    {
        if (($invoice->invoice_per_type ?? 'standard') !== 'standard') {
            abort(404);
        }
    }

    public function show(Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        $invoice->load('items');
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin() && !auth()->user()->can('invoice-edit')) {
            abort(403, 'Unauthorized action.');
        }
        $invoice->load('items');
        
        // Load only customers who have 'Client KYC' or 'Client Terms' leads
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
                    'state' => $customer?->state_name ?? $lead->state ?? 'Karnataka',
                    'state_code' => $customer?->state_code ?? $lead->state_code ?? '29',
                    'udyam_certificate' => $lead->udyam_registration_certificate ?? 'None',
                    'pan_no' => $lead->pan_number ?? 'None',
                    'aadhar_no' => $lead->aadhar_no ?? 'None',
                    'gstin_unique_id' => $lead->gst_no ?? 'None',
                    'reference' => $lead->reference ?? 'None',
                ];
            });
        return view('invoices.edit', compact('invoice', 'leads'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin() && !auth()->user()->can('invoice-edit')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'client_name' => 'required|string|max:255',
            'organisation_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'state_code' => 'nullable|string|max:255',
            'purchase_order' => 'nullable|string|max:255',
            'service_description_meta' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'udyam_certificate' => 'nullable|string|max:255',
            'pan_no' => 'nullable|string|max:255',
            'aadhar_no' => 'nullable|string|max:255',
            'gstin_unique_id' => 'nullable|string|max:255',
            'tax_type' => 'required|in:local,outstation',
            'items' => 'required|array|min:1',
            'items.*.service_name' => 'required|string|max:255',
            'items.*.hsn_sac' => 'nullable|string|max:255',
            'items.*.qty' => 'nullable|numeric|min:0',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.total' => 'nullable|numeric|min:0',
        ]);

        $lead = Lead::with('customer')->findOrFail($request->lead_id);

        $targetSources = array_merge(
            Lead::SOURCE_GROUPS['CLIENT_KYC'],
            Lead::SOURCE_GROUPS['CLIENT_TERMS'],
            Lead::SOURCE_GROUPS['CRM']
        );
        if (!in_array($lead->creation_source, $targetSources)) {
            return redirect()->back()->with('error', 'Invoices can only be created for Client KYC, Client Terms, or CRM leads.');
        }

        $taxableValue = 0;
        $dbItems = [];
        foreach ($request->items as $item) {
            $qty = ($item['qty'] !== null && $item['qty'] !== '') ? (float) $item['qty'] : null;
            $rate = ($item['rate'] !== null && $item['rate'] !== '') ? (float) $item['rate'] : null;
            $total = ($item['total'] !== null && $item['total'] !== '') ? (float) $item['total'] : null;

            if ($total === null && $qty !== null && $rate !== null) {
                $total = round($qty * $rate, 2);
            }

            $dbItems[] = [
                'service_name' => $item['service_name'],
                'hsn_sac' => $item['hsn_sac'] ?? null,
                'qty' => $qty,
                'rate' => $rate,
                'total' => $total ?? 0,
            ];
            $taxableValue += ($total ?? 0);
        }

        $cgst_amount = 0;
        $sgst_amount = 0;
        $igst_amount = 0;

        if ($request->tax_type === 'local') {
            $cgst_amount = round($taxableValue * 0.09, 2);
            $sgst_amount = round($taxableValue * 0.09, 2);
        } else {
            $igst_amount = round($taxableValue * 0.18, 2);
        }

        $totalInvoiceValue = $this->formatNumber($taxableValue + $cgst_amount + $sgst_amount + $igst_amount);
        $totalWords = $this->amountInWords($totalInvoiceValue);

        $invoice->update([
            'lead_id' => $lead->id,
            'customer_id' => $lead->customer_id,
            'client_name' => $request->client_name ?? 'None',
            'organisation_name' => $request->organisation_name ?? 'None',
            'address' => $request->address ?? 'None',
            'city' => $request->city ?? 'None',
            'state' => $request->state ?? 'Karnataka',
            'state_code' => $request->state_code ?? '29',
            'purchase_order' => $request->purchase_order ?? 'NA',
            'service_description_meta' => $request->service_description_meta ?? 'Marketing Services',
            'due_date' => $request->due_date ? date('Y-m-d', strtotime($request->due_date)) : null,
            'udyam_certificate' => $request->udyam_certificate ?? 'None',
            'pan_no' => $request->pan_no ?? 'None',
            'aadhar_no' => $request->aadhar_no ?? 'None',
            'gstin_unique_id' => $request->gstin_unique_id ?? 'None',
            'tax_type' => $request->tax_type,
            'taxable_value' => $this->formatNumber($taxableValue),
            'cgst_percent' => ($request->tax_type === 'local' ? 9 : 0),
            'cgst_amount' => $this->formatNumber($cgst_amount),
            'sgst_percent' => ($request->tax_type === 'local' ? 9 : 0),
            'sgst_amount' => $this->formatNumber($sgst_amount),
            'igst_percent' => ($request->tax_type === 'outstation' ? 18 : 0),
            'igst_amount' => $this->formatNumber($igst_amount),
            'total_invoice_value' => $this->formatNumber($totalInvoiceValue),
            'total_invoice_value_words' => $totalWords,
        ]);

        $invoice->items()->delete();
        foreach ($dbItems as $dbItem) {
            $invoice->items()->create($dbItem);
        }

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        $filename = ($invoice->is_paid ? "final_" : "proforma_") . $invoice->id . ".pdf";
        $path = 'public/invoices/' . $filename;
        Storage::put($path, $pdf->output());

        $invoice->update(['pdf_file_path' => $path]);

        return redirect()->route('invoices.show', [$invoice->id, 'page' => $request->query('page')])->with('success', 'Invoice updated successfully.');
    }

    public function download(Request $request, Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!$request->hasValidSignature() && !(auth()->check() && auth()->user()->isAdmin())) {
            abort(403, 'Downloading invoices is restricted.');
        }

        $invoice->load(['customer', 'lead']);

        $companyName = $invoice->customer?->company_name ?? $invoice->lead?->company_name ?? null;
        $companyName = ($companyName && strtoupper($companyName) !== 'NONE') ? $companyName : null;
        $invoiceNumber = ($invoice->is_paid && $invoice->invoice_number) ? $invoice->invoice_number : null;
        $clientName = $invoice->client_name;
        $leadId = $invoice->lead?->record_id ?? $invoice->lead_id;
        $baseName = $invoice->is_paid ? 'Tax Invoice' : 'Proforma Invoice';
        $dateStr = $invoice->paid_at ? $invoice->paid_at->format('d-m-Y') : ($invoice->invoice_date ? $invoice->invoice_date->format('d-m-Y') : date('d-m-Y'));
        
        $parts = array_filter([$baseName, $invoiceNumber, $clientName, $companyName, $leadId, $dateStr]);
        $downloadName = implode(' - ', $parts) . '.pdf';
        $downloadName = str_replace(['/', '\\'], '-', $downloadName);

        if ($invoice->pdf_file_path && Storage::exists($invoice->pdf_file_path)) {
            return Storage::download($invoice->pdf_file_path, $downloadName);
        }

        // Fallback generator in case file was lost from disk but DB exists
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download($downloadName);
    }

    public function downloadPage(Request $request, Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!$request->hasValidSignature()) {
            abort(403, 'Unauthorized action.');
        }

        $downloadUrl = URL::signedRoute('invoices.download', ['invoice' => $invoice->id]);

        $htmlContent = $this->getDownloadPageHtml($downloadUrl);

        return response($htmlContent)->header('Content-Type', 'text/html');
    }

    private function getDownloadPageHtml($downloadUrl)
    {
        return '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Downloading Invoice...</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .message { background: rgba(30, 41, 59, 0.7); padding: 2rem; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); text-align: center; max-width: 400px; }
        h1 { font-size: 1.5rem; margin-top: 0; color: #3b82f6; }
        p { color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin-bottom: 0; }
        .spinner { width: 40px; height: 40px; border: 4px solid rgba(255,255,255,0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1.5rem; }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="message">
        <div class="spinner"></div>
        <h1>Download Started</h1>
        <p>Your invoice download has been securely initiated. You can safely close this browser tab.</p>
    </div>
    <script>
        // Use a DOM-based anchor trigger to bypass Excel interception and prevent double execution
        setTimeout(function() {
            var a = document.createElement("a");
            a.href = "' . $downloadUrl . '";
            a.style.display = "none";
            document.body.appendChild(a);
            a.click();
            
            // Optionally attempt to self-close the tab after download starts (browsers may block this)
            setTimeout(function() { window.close(); }, 3000);
        }, 300);
    </script>
</body>
</html>';
    }


    public function markAsPaid(Invoice $invoice, InvoiceService $invoiceService)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin() && !auth()->user()->can('invoice-mark-paid')) {
            abort(403, 'Unauthorized action.');
        }

        $result = $invoiceService->markAsPaid($invoice);

        if ($result['success']) {
            return redirect()->route('invoices.show', $invoice->id)->with('success', $result['message']);
        } else {
            return redirect()->route('invoices.show', $invoice->id)->with('error', $result['message']);
        }
    }

    public function sendEmail(Invoice $invoice, InvoiceService $invoiceService)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $result = $invoiceService->sendEmail($invoice);

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }

    public function cancel(Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin() && !auth()->user()->can('invoice-cancel')) {
            abort(403, 'Unauthorized action.');
        }

        if ($invoice->is_paid) {
            return redirect()->back()->with('error', 'Paid invoices cannot be cancelled.');
        }

        $invoice->update(['is_cancelled' => true]);

        return redirect()->back()->with('success', 'PROFORMA invoice cancelled successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        $this->enforceStandardType($invoice);
        if (!auth()->user()->isAdmin() && !auth()->user()->can('invoice-delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (!$invoice->is_cancelled) {
            return redirect()->back()->with('error', 'Only cancelled invoices can be deleted.');
        }

        if ($invoice->pdf_file_path && Storage::exists($invoice->pdf_file_path)) {
            Storage::delete($invoice->pdf_file_path);
        }

        $invoice->delete();

        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    // Custom Indian Ext-Intl free number converter
    private function amountInWords(float $number)
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $hundred = null;
        $digits_1 = strlen((string) $no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '',
            '1' => 'one',
            '2' => 'two',
            '3' => 'three',
            '4' => 'four',
            '5' => 'five',
            '6' => 'six',
            '7' => 'seven',
            '8' => 'eight',
            '9' => 'nine',
            '10' => 'ten',
            '11' => 'eleven',
            '12' => 'twelve',
            '13' => 'thirteen',
            '14' => 'fourteen',
            '15' => 'fifteen',
            '16' => 'sixteen',
            '17' => 'seventeen',
            '18' => 'eighteen',
            '19' => 'nineteen',
            '20' => 'twenty',
            '30' => 'thirty',
            '40' => 'forty',
            '50' => 'fifty',
            '60' => 'sixty',
            '70' => 'seventy',
            '80' => 'eighty',
            '90' => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');

        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }

        $str = array_reverse($str);
        $result = implode('', $str);
        $result = preg_replace('/ and $/i', '', trim(str_replace('  ', ' ', $result)));

        $points = '';
        if ($point) {
            $tens = floor($point / 10) * 10;
            $ones = $point % 10;
            $pointStr = ($point < 21) ? $words[$point] : $words[$tens] . " " . $words[$ones];
            $points = " and " . $pointStr . " paise";
        }

        if (empty($result)) {
            $result = "zero";
        }

        return ucfirst(strtolower(trim($result . " rupees" . $points . " only")));
    }
}
