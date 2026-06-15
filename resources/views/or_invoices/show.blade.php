@extends('layouts.app')

@section('header', 'Invoice OR Details')

@section('content')
    <!-- Full-Page Loading Overlay -->
    <div x-data="{ loading: false }"
        @submit.window="if ($event.target.tagName === 'FORM' && $event.target.getAttribute('action')?.includes('send-email')) loading = true"
        class="relative">

        <div x-show="loading"
            class="fixed inset-0 z-[100] flex flex-col items-center justify-center bg-slate-900/40 backdrop-blur-sm transition-all pointer-events-auto"
            style="display: none;" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

            <div class="flex flex-col items-center gap-4">
                <!-- GIF Loader Icon -->
                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b1/Loading_icon.gif" alt="Loading..."
                    class="h-16 w-16 mix-blend-screen opacity-90">
                <div class="text-center">
                    <h3 class="text-xl font-medium text-white">Sending Invoice...</h3>
                    <p class="text-white/70 text-sm mt-1">Please wait while we deliver the document.</p>
                </div>
            </div>
        </div>
        <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-5xl mx-auto">
            <!-- Page Header / Actions -->
            <div class="sm:flex sm:justify-between sm:items-center mb-8 no-print">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl md:text-3xl text-slate-800 font-bold font-display uppercase tracking-tight">
                        Invoice OR Details
                    </h1>
                </div>

                <div class="flex flex-nowrap items-center gap-3 overflow-x-auto pb-2 sm:pb-0">
                    <a href="{{ route('or-invoices.index', array_filter(['page' => request('page')])) }}"
                        class="h-10 inline-flex items-center bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900 rounded-md px-4 font-medium shadow-sm transition whitespace-nowrap">
                        Back to List
                    </a>

                    @if(!$invoice->is_cancelled)
                        @if(auth()->user()->isAdmin() && !$invoice->is_paid)
                            <form action="{{ route('or-invoices.mark_paid', $invoice->id) }}" method="POST" class="inline-block m-0">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to mark this as paid? This will generate the final invoice number.')"
                                    class="h-10 inline-flex items-center bg-emerald-50 border border-emerald-300 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900 rounded-md px-4 font-medium shadow-sm transition whitespace-nowrap">
                                    Mark as Paid
                                </button>
                            </form>

                            <form action="{{ route('or-invoices.cancel', $invoice->id) }}" method="POST" class="inline-block m-0">
                                @csrf
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to cancel this PROFORMA invoice?')"
                                    class="h-10 inline-flex items-center bg-rose-50 border border-rose-300 text-rose-700 hover:bg-rose-100 hover:text-rose-900 rounded-md px-4 font-medium shadow-sm transition whitespace-nowrap">
                                    Cancel Proforma
                                </button>
                            </form>
                        @endif

                        @can('email-section')
                            <form action="{{ route('or-invoices.send_email', $invoice->id) }}" method="POST" class="inline-block m-0">
                                @csrf
                                <button type="submit"
                                    class="h-10 inline-flex items-center bg-indigo-50 border border-indigo-300 text-indigo-700 hover:bg-indigo-100 hover:text-indigo-900 rounded-md px-4 font-medium shadow-sm transition whitespace-nowrap">
                                    Send Email
                                </button>
                            </form>
                        @endcan
                        @if(auth()->user()->isAdmin() && $invoice->is_paid)
                            <a href="{{ route('or-invoices.edit', $invoice->id) }}"
                                class="h-10 inline-flex items-center bg-amber-50 border border-amber-300 text-amber-700 hover:bg-amber-100 hover:text-amber-900 rounded-md px-4 font-medium shadow-sm transition whitespace-nowrap">
                                Edit Invoice
                            </a>
                            <a href="{{ route('or-invoices.download', $invoice->id) }}"
                                style="height:40px; display:inline-flex; align-items:center; background:#000000; color:#ffffff !important; border:1px solid #000000; border-radius:6px; padding:0 16px; font-weight:600; text-decoration:none; opacity:1 !important; visibility:visible !important;"
                                class="whitespace-nowrap">
                                <svg class="w-4 h-4 mr-2 flex-shrink-0" viewBox="0 0 16 16" aria-hidden="true"
                                    style="fill:#ffffff; width:16px; height:16px; margin-right:8px;">
                                    <path
                                        d="M15 7H9V1c0-.6-.4-1-1-1S7 .4 7 1v6H1c-.6 0-1 .4-1 1s.4 1 1 1h6v6c0 .6.4 1 1 1s1-.4 1-1V9h6c.6 0 1-.4 1-1s-.4-1-1-1z" />
                                </svg>
                                <span style="color:#ffffff !important; display:inline !important;">Download PDF</span>
                            </a>
                        @endif
                    @else
                        <span
                            class="h-10 inline-flex items-center bg-red-100 text-red-800 rounded-md px-4 font-medium shadow-sm whitespace-nowrap">
                            CANCELLED
                        </span>
                    @endif
                </div>
            </div>

            @if(session('success'))
                <div
                    class="mb-6 px-4 py-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-sm no-print">
                    <div class="flex items-center gap-3">
                        <div class="bg-emerald-500 rounded-full p-1">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <span class="font-bold text-sm">{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <!-- Invoice Preview -->
            <div class="w-full overflow-x-auto print:overflow-visible pb-4">
                <div class="bg-white shadow-2xl rounded-sm border border-slate-200 mx-auto overflow-hidden print:shadow-none print:border-none print:m-0 print:p-0"
                    style="min-width: 210mm; width: 210mm; min-height: 297mm; padding: 12mm 14mm; color: #000000; font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 12px; line-height: 1.35;">

                    <style>
                        .paper-invoice-table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 10px;
                            color: #000000;
                        }

                        .paper-invoice-table td,
                        .paper-invoice-table th {
                            border: 1px solid #000000;
                            padding: 6px;
                            vertical-align: top;
                        }

                        .w-bold {
                            font-weight: 700 !important;
                        }

                        .w-normal {
                            font-weight: 400 !important;
                        }

                        .uppercase {
                            text-transform: uppercase;
                        }

                        .underline {
                            text-decoration: underline;
                        }

                        .text-center {
                            text-align: center;
                        }

                        .text-right {
                            text-align: right;
                        }

                        .header-info-tbl td {
                            border: none !important;
                            padding: 0 !important;
                            line-height: 1.25;
                            vertical-align: top;
                        }

                        .meta-table-web td {
                            border: none !important;
                            padding: 4px 8px !important;
                            border-bottom: 1px solid #000 !important;
                        }

                        .meta-table-web tr:last-child td {
                            border-bottom: none !important;
                        }

                        .items-table-web {
                            width: 100%;
                            border-collapse: collapse;
                            border: 1px solid #000;
                            margin-top: 8px;
                        }

                        .items-table-web th,
                        .items-table-web td {
                            border: 1px solid #000;
                            padding: 5px;
                            font-size: 11px;
                        }

                        .items-table-web th {
                            background-color: #ffffff;
                            font-weight: 700;
                        }

                        .signature-img-web {
                            width: 90px;
                            max-height: 60px;
                            height: auto;
                            display: inline-block;
                        }

                        @media print {
                            .no-print {
                                display: none !important;
                            }

                            body {
                                background: white !important;
                            }

                            .max-w-5xl {
                                max-width: none !important;
                                width: 100% !important;
                                margin: 0 !important;
                                padding: 0 !important;
                            }
                        }

                        .signature-img-web {
                            width: 90px;
                            max-height: 60px;
                            height: auto;
                            display: inline-block;
                        }
                    </style>

                    <!-- Top Header Info -->
                    <table class="w-full mb-4 header-info-tbl">
                        <tr>
                            <td style="width: 50%; vertical-align: top; line-height: 1.4; padding-top: 5px;">
                                <!-- Left side intentionally left blank for OR invoices -->
                            </td>
                            <td style="width: 50%; vertical-align: top; padding-top: 5px;">
                                <table style="width: 100%; border-collapse: collapse;">
                                    <!-- Date of Invoice Row -->
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 12px 16px; width: 45%;" class="w-bold">Date of Invoice</td>
                                        <td style="border: 1px solid #000; padding: 12px 16px;" class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</td>
                                    </tr>
                                    <!-- Spacer Row -->
                                    <tr>
                                        <td colspan="2" style="border: none; padding: 0; height: 12px;">
                                            <div style="height: 12px;"></div>
                                        </td>
                                    </tr>
                                    <!-- Invoice Type Row -->
                                    <tr>
                                        <td style="border: 1px solid #000; padding: 12px 16px; width: 45%;" class="w-bold">Invoice Type</td>
                                        <td style="border: 1px solid #000; padding: 12px 16px;" class="w-bold uppercase">
                                            {{ $invoice->is_paid ? 'Tax Invoice' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <div class="w-bold text-center uppercase border border-black py-2 mb-2"
                        style="font-size: 14px;">{{ $invoice->is_paid ? 'INVOICE' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}</div>

                    <table class="paper-invoice-table">
                        <tr>
                            <td style="width: 50%; padding: 8px; border-right: 1px solid #000; line-height: 1.4;">
                                <div class="w-bold underline mb-4 uppercase">Details of Receiver (Billed to)</div>
                                <div class="mb-1"><span class="w-bold">Name:</span> <span
                                        class="w-normal">{{ $invoice->client_name }}</span></div>
                                <div class="mb-1"><span class="w-bold">Organization Name:</span> <span
                                        class="w-bold uppercase">{{ $invoice->organisation_name !== 'None' ? $invoice->organisation_name : 'None' }}</span>
                                </div>
                                <div class="mb-1"><span class="w-bold">Address:</span> <span
                                        class="w-normal">{{ $invoice->address }}</span></div>
                                <div class="mb-1"><span class="w-bold">State & Code:</span> <span
                                        class="w-normal">{{ optional($invoice->customer)->state_name ?: ($invoice->state ?: 'Karnataka') }} ({{ optional($invoice->customer)->state_code ?: ($invoice->state_code ?: '29') }})</span></div>
                                <div class="mb-1 flex items-center gap-2">
                                    <div><span class="w-bold">Mobile No:</span> <span class="w-normal">{{ optional($invoice->customer)->mobile_no ?? 'NONE' }}</span></div>
                                    @php $mobileNo = optional($invoice->customer)->mobile_no ?? null; @endphp
                                    @if(auth()->user()->isAdmin() || auth()->user()->can('whatsapp-icon'))
                                        @if($mobileNo && $mobileNo !== 'NONE')
                                            @php
                                                $waMobile = preg_replace('/[^0-9]/', '', $mobileNo);
                                                if(strlen($waMobile) == 10) $waMobile = '91' . $waMobile;
                                            @endphp
                                            <a href="https://wa.me/{{ $waMobile }}" target="_blank" class="hover:opacity-80 transition-opacity no-print" title="Chat on WhatsApp">
                                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M12.012 2C6.506 2 2.023 6.478 2.022 11.984C2.022 13.734 2.478 15.422 3.356 16.92L2 22L7.233 20.763C8.683 21.545 10.323 21.968 12.008 21.968H12.012C17.518 21.968 22.001 17.49 22.002 11.984C22.002 6.48 17.523 2 12.012 2Z" fill="#25D366"/>
                                                    <path d="M17.472 14.382C17.175 14.233 15.714 13.515 15.442 13.415C15.169 13.316 14.971 13.267 14.772 13.565C14.575 13.862 14.005 14.531 13.832 14.729C13.659 14.928 13.485 14.952 13.188 14.804C12.891 14.654 11.933 14.341 10.798 13.329C10.003 12.621 9.406 11.648 9.233 11.35C9.06 11.053 9.215 10.892 9.363 10.744C9.497 10.611 9.661 10.397 9.809 10.224C9.958 10.05 10.007 9.926 10.107 9.727C10.206 9.529 10.157 9.356 10.082 9.207C10.007 9.058 9.413 7.595 9.166 7.001C8.924 6.422 8.679 6.5 8.497 6.49C8.324 6.49 8.102 6.49 7.83 6.49C7.558 6.49 7.114 6.589 6.842 6.887C6.57 7.184 5.802 7.903 5.802 9.366C5.802 10.828 6.867 12.241 7.015 12.44C7.164 12.638 9.111 15.64 12.092 16.927C12.801 17.233 13.354 17.416 13.786 17.552C14.498 17.779 15.146 17.747 15.657 17.67C16.228 17.585 17.415 16.951 17.663 16.257C17.911 15.563 17.911 14.968 17.836 14.844C17.762 14.72 17.564 14.646 17.266 14.497V14.382Z" fill="white"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endif
                                </div>
                                <div class="mb-1"><span class="w-bold">Email Id:</span> <span
                                        class="w-normal">{{ $invoice->customer->email_id ?? 'NONE' }}</span></div>
                                <div class="mb-1"><span class="w-bold">Aadhar No: </span> <span
                                        class="w-normal uppercase">{{ $invoice->aadhar_no ?? 'NONE' }}</span></div>
                                <div class="mb-1"><span class="w-bold">PAN No:</span> <span
                                        class="w-normal uppercase">{{ strtoupper($invoice->pan_no) }}</span></div>
                                <div class="mb-1"><span class="w-bold">Udyam/Estd. Certificate:</span> <span
                                        class="w-normal uppercase">{{ strtoupper($invoice->udyam_certificate) }}</span>
                                </div>
                                <div><span class="w-bold">GSTIN/Unique ID:</span> <span
                                        class="w-bold uppercase">{{ strtoupper($invoice->gstin_unique_id) }}</span></div>
                            </td>
                            <td style="width: 50%; padding: 8px;">
                                <table class="w-full">
                                    <tr>
                                        <td class="w-bold" style="width: 45%; padding: 4px 8px; border: none !important;">Invoice Number</td>
                                        <td style="width: 5%; padding: 4px 8px; border: none !important;">:</td>
                                        <td class="w-normal" style="width: 50%; padding: 4px 8px; border: none !important;">
                                            {{ ($invoice->is_paid || $invoice->is_cancelled) ? $invoice->invoice_number : 'PROFORMA' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="w-bold" style="padding: 4px 8px; border: none !important;">Invoice Date</td>
                                        <td style="padding: 4px 8px; border: none !important;">:</td>
                                        <td class="w-normal" style="padding: 4px 8px; border: none !important;">{{ $invoice->invoice_date->format('d-M-y') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-bold" style="padding: 4px 8px; border: none !important;">State</td>
                                        <td style="padding: 4px 8px; border: none !important;">:</td>
                                        <td class="w-normal" style="padding: 4px 8px; border: none !important;">{{ $invoice->state ?? 'Karnataka' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="w-bold" style="padding: 4px 8px; border: none !important;">State Code</td>
                                        <td style="padding: 4px 8px; border: none !important;">:</td>
                                        <td class="w-bold" style="padding: 4px 8px; border: none !important;">{{ $invoice->state_code ?? '29' }}</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <table class="items-table-web">
                        <thead>
                            <tr>
                                <th rowspan="2" style="width: 40px;">Sr No</th>
                                <th rowspan="2" style="width: 150px;">Description of Goods</th>
                                <th rowspan="2" style="width: 70px;">HSN/SAC</th>
                                <th rowspan="2" style="width: 40px;">Qty</th>
                                <th rowspan="2" style="width: 80px;">Rate (Peritem)</th>
                                <th rowspan="2" style="width: 80px;">Total</th>
                                <th rowspan="2" style="width: 85px;">Taxable Value</th>
                                <th colspan="2">CGST</th>
                                <th colspan="2">SGST</th>
                                <th colspan="2">IGST</th>
                            </tr>
                            <tr>
                                <th style="width: 45px;">Rate</th>
                                <th style="width: 55px;">Amt</th>
                                <th style="width: 45px;">Rate</th>
                                <th style="width: 55px;">Amt</th>
                                <th style="width: 45px;">Rate</th>
                                <th style="width: 55px;">Amt</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $index => $item)
                                @php
                                    $lineBase = $item->total;
                                    
                                    $cgstPercent = isset($invoice->cgst_percent) && $invoice->cgst_percent !== null ? (float)$invoice->cgst_percent : ($invoice->tax_type === 'local' ? 1.5 : 0.0);
                                    $sgstPercent = isset($invoice->sgst_percent) && $invoice->sgst_percent !== null ? (float)$invoice->sgst_percent : ($invoice->tax_type === 'local' ? 1.5 : 0.0);
                                    $igstPercent = isset($invoice->igst_percent) && $invoice->igst_percent !== null ? (float)$invoice->igst_percent : ($invoice->tax_type === 'outstation' ? 3.0 : 0.0);

                                    $rowCgst = 0;
                                    $rowSgst = 0;
                                    $rowIgst = 0;

                                    if ($invoice->tax_type === 'local') {
                                        $rowCgst = ($lineBase * $cgstPercent) / 100;
                                        $rowSgst = ($lineBase * $sgstPercent) / 100;
                                    } else {
                                        $rowIgst = ($lineBase * $igstPercent) / 100;
                                    }
                                    $rowInclusive = $lineBase + $rowCgst + $rowSgst + $rowIgst;
                                @endphp
                                <tr>
                                    <td class="text-center w-normal">{{ $index + 1 }}</td>
                                    <td class="text-left w-bold">{{ $item->service_name }}</td>
                                    <td class="text-center w-normal">{{ $item->hsn_sac ?? '7117' }}</td>
                                    <td class="text-center w-normal">
                                        {{ $item->qty === null || $item->qty === '' ? '' : (is_numeric($item->qty) ? number_format((float) $item->qty, (fmod((float) $item->qty, 1) == 0 ? 0 : 2), '.', ',') : $item->qty) }}
                                    </td>
                                    <td class="text-right w-normal">
                                        {{ $item->rate === null || $item->rate === '' ? '' : (is_numeric($item->rate) ? '₹' . number_format((float) $item->rate, (fmod((float) $item->rate, 1) == 0 ? 0 : 2), '.', ',') : $item->rate) }}
                                    </td>
                                    <td class="text-right w-normal">
                                        {{ is_numeric($lineBase) ? '₹' . number_format((float) $lineBase, (fmod((float) $lineBase, 1) == 0 ? 0 : 2), '.', ',') : $lineBase }}
                                    </td>
                                    <td class="text-right w-bold">
                                        {{ is_numeric($rowInclusive) ? '₹' . number_format((float) $rowInclusive, (fmod((float) $rowInclusive, 1) == 0 ? 0 : 2), '.', ',') : $rowInclusive }}
                                    </td>
                                    <td class="text-center w-normal">{{ $invoice->tax_type === 'local' ? $cgstPercent . '%' : '-' }}</td>
                                    <td class="text-right w-normal">
                                        {{ is_numeric($rowCgst) ? number_format((float) $rowCgst, (fmod((float) $rowCgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                    </td>
                                    <td class="text-center w-normal">{{ $invoice->tax_type === 'local' ? $sgstPercent . '%' : '-' }}</td>
                                    <td class="text-right w-normal">
                                        {{ is_numeric($rowSgst) ? number_format((float) $rowSgst, (fmod((float) $rowSgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                    </td>
                                    <td class="text-center w-normal">{{ $invoice->tax_type === 'outstation' ? $igstPercent . '%' : '-' }}
                                    </td>
                                    <td class="text-right w-normal">
                                        {{ is_numeric($rowIgst) ? number_format((float) $rowIgst, (fmod((float) $rowIgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr class="w-bold">
                                <td colspan="5" class="text-left">Total</td>
                                <td class="text-right">₹
                                    {{ is_numeric($invoice->taxable_value) ? number_format((float) $invoice->taxable_value, (fmod((float) $invoice->taxable_value, 1) == 0 ? 0 : 2), '.', ',') : $invoice->taxable_value }}
                                </td>
                                <td class="text-right">₹
                                    {{ is_numeric($invoice->total_invoice_value) ? number_format((float) $invoice->total_invoice_value, (fmod((float) $invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',') : rtrim(rtrim($invoice->total_invoice_value, '0'), '.') }}
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-right">₹
                                    {{ is_numeric($invoice->cgst_amount) ? number_format((float) $invoice->cgst_amount, (fmod((float) $invoice->cgst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-right">₹
                                    {{ is_numeric($invoice->sgst_amount) ? number_format((float) $invoice->sgst_amount, (fmod((float) $invoice->sgst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                </td>
                                <td class="text-center">-</td>
                                <td class="text-right">₹
                                    {{ is_numeric($invoice->igst_amount) ? number_format((float) $invoice->igst_amount, (fmod((float) $invoice->igst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-5 w-bold" style="font-size: 13px;">
                        <div class="mb-1">
                            Total Invoice Value (In figure):
                            <span class="w-normal">₹
                                {{ number_format((float) $invoice->total_invoice_value, (fmod($invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',') }}
                            </span>
                        </div>
                        <div class="mb-1">Total Invoice Value (In Words): <span
                                class="w-normal">{{ $invoice->total_invoice_value_words }}</span></div>
                        <div>Amount of Tax subject to Reverse Charges: <span class="w-normal">NA</span></div>
                    </div>

                    <!-- Declaration & Signatory with bordered cells -->
                    <table class="w-full mt-8" style="border-collapse: collapse; border: 1px solid #000;">
                        <tr>
                            <td style="width: 60%; border: 1px solid #000; height: 95px; vertical-align: top; padding: 8px;">
                                <div><span class="w-bold">Declaration:</span> <span class="w-normal">NA</span></div>
                            </td>
                            <td style="width: 40%; border: 1px solid #000; height: 95px; vertical-align: top; padding: 8px; text-align: left;">
                                <div class="mb-1">
                                    @php
                                        $signaturePathWeb = public_path('signature_or.png');
                                        $signatureAsset = 'signature_or.png';
                                        if (!file_exists($signaturePathWeb)) {
                                            $signaturePathWeb = public_path('signature_or.jpeg');
                                            $signatureAsset = 'signature_or.jpeg';
                                        }
                                        if (!file_exists($signaturePathWeb)) {
                                            $signaturePathWeb = public_path('signature_or.jpg');
                                            $signatureAsset = 'signature_or.jpg';
                                        }
                                        if (!file_exists($signaturePathWeb)) {
                                            $signaturePathWeb = public_path('signature.png');
                                            $signatureAsset = 'signature.png';
                                        }
                                    @endphp
                                    @if (file_exists($signaturePathWeb))
                                        <div style="text-align: left; margin-bottom: 2px; height: 55px;">
                                            <img class="signature-img-web" src="{{ asset($signatureAsset) }}" alt="Signature" style="max-height: 55px; width: auto; max-width: 150px;">
                                        </div>
                                    @else
                                        <div style="height: 57px;"></div>
                                    @endif
                                </div>
                                <div style="line-height: 1.2;">
                                    <div class="w-bold uppercase" style="font-size: 11px;">Authorized Signatory</div>
                                    <div style="font-size: 11px;"><span class="w-bold">Date:</span> <span class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</span></div>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="mt-6 text-[11px] leading-tight w-normal">
                        Note: Client shall NOT Resell, sublicense, lease, distribute, publish our products or services. Client shall irrevocably and unconditionally defend, indemnify and hold us harmless.
                    </div>

                    <div class="mt-5 text-center w-bold" style="font-size: 13px;">
                        This is a <span class="underline">Computer Generated</span>
                        {{ $invoice->is_paid ? 'Invoice' : ($invoice->is_cancelled ? 'Cancelled Invoice' : 'Proforma') }}
                    </div>
                </div>
            </div>
        </div>
@endsection
