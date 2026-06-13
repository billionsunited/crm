<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 12mm 8mm 12mm;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 9.5px;
            color: #000000;
            margin: 0;
            padding: 0;
            line-height: 1.25;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        td,
        th {
            padding: 3px 4px;
            vertical-align: top;
            color: #000000;
        }

        .w-bold {
            font-weight: 700;
        }

        .w-normal {
            font-weight: 400;
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

        .keep-together {
            page-break-inside: avoid !important;
        }

        .mb-2 {
            margin-bottom: 2px;
        }

        .mb-4 {
            margin-bottom: 4px;
        }

        .mb-6 {
            margin-bottom: 6px;
        }

        .mt-4 {
            margin-top: 4px;
        }

        .mt-6 {
            margin-top: 6px;
        }

        .mt-8 {
            margin-top: 8px;
        }

        .border-box {
            border: 1px solid #000;
        }

        .invoice-title {
            border: 1px solid #000;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            padding: 5px;
            font-size: 11px;
            margin-bottom: 8px;
        }

        .header-info td {
            border: none;
            padding: 0;
            line-height: 1.2;
        }

        .meta-table td {
            border: none;
            padding: 4px 6px;
        }

        .items-table {
            border: 1px solid #000;
            margin-top: 6px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #000;
            font-size: 9px;
            padding: 4px;
        }

        .items-table th {
            font-weight: 700;
            background: #fff;
        }

        .numeric {
            text-align: right;
        }

        .signature-img {
            width: 90px;
            max-width: 90px;
            max-height: 60px;
            height: auto;
            display: inline-block;
        }

        .footer-text {
            font-size: 8px;
            line-height: 1.15;
        }

        .computer-generated {
            text-align: center;
            font-weight: 700;
            font-size: 11px;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    @php
        $signaturePath = public_path('signature_or.png');
        if (!file_exists($signaturePath)) {
            $signaturePath = public_path('signature_or.jpeg');
        }
        if (!file_exists($signaturePath)) {
            $signaturePath = public_path('signature_or.jpg');
        }
        if (!file_exists($signaturePath)) {
            $signaturePath = public_path('signature.png');
        }
        $signatureBase64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;
    @endphp

    <!-- Header info with top-left blank and top-right bordered boxes -->
    <table class="header-info keep-together" style="margin-bottom: 10px;">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <table style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px 0;" class="w-bold">Date of Invoice</td>
                        <td style="border: 1px solid #000; padding: 4px 0;" class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px 0;" class="w-bold">Invoice Type</td>
                        <td style="border: 1px solid #000; padding: 4px 0;" class="w-bold uppercase">
                            {{ $invoice->is_paid ? 'Tax Invoice' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="invoice-title keep-together">
        {{ $invoice->is_paid ? 'INVOICE' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}
    </div>

    <!-- Receiver details (left) and Invoice details (right) -->
    <table class="border-box keep-together" style="margin-bottom: 8px;">
        <tr>
            <!-- Left Side: Receiver details (billed to) -->
            <td style="width: 50%; border-right: 1px solid #000; padding: 7px; line-height: 1.35;">
                <div class="w-bold underline uppercase mb-6">Details of Receiver (Billed to)</div>
                <div class="mb-4"><span class="w-bold">Name:</span> <span class="w-normal">{{ $invoice->client_name }}</span></div>
                <div class="mb-4"><span class="w-bold">Organization Name:</span> <span class="w-bold uppercase">{{ $invoice->organisation_name !== 'None' ? $invoice->organisation_name : 'None' }}</span></div>
                <div class="mb-4"><span class="w-bold">Address:</span> <span class="w-normal">{{ $invoice->address }}</span></div>
                <div class="mb-2"><span class="w-bold">State & Code:</span> <span class="w-normal">{{ $invoice->state ?? 'Karnataka' }} ({{ $invoice->state_code ?? '29' }})</span></div>
                <div class="mb-2"><span class="w-bold">Mobile No:</span> <span class="w-normal">{{ $invoice->customer->mobile_no ?? 'NONE' }}</span></div>
                <div class="mb-2"><span class="w-bold">Email Id:</span> <span class="w-normal">{{ $invoice->customer->email_id ?? 'NONE' }}</span></div>
                <div class="mb-2"><span class="w-bold">Aadhar No:</span> <span class="w-normal uppercase">{{ $invoice->aadhar_no ?? 'NONE' }}</span></div>
                <div class="mb-2"><span class="w-bold">PAN No:</span> <span class="w-normal uppercase">{{ strtoupper($invoice->pan_no) }}</span></div>
                <div class="mb-2"><span class="w-bold">Udyam/Estd. Certificate:</span> <span class="w-normal uppercase">{{ strtoupper($invoice->udyam_certificate) }}</span></div>
                <div><span class="w-bold">GSTIN/Unique ID:</span> <span class="w-bold uppercase">{{ strtoupper($invoice->gstin_unique_id) }}</span></div>
            </td>

            <!-- Right Side: Invoice details -->
            <td style="width: 50%; padding: 7px;">
                <table class="meta-table">
                    <tr>
                        <td class="w-bold" style="width: 45%; padding: 4px 0;">Invoice Number</td>
                        <td style="width: 5%; padding: 4px 0;">:</td>
                        <td class="w-normal" style="width: 50%; padding: 4px 0;">
                            {{ ($invoice->is_paid || $invoice->is_cancelled) ? $invoice->invoice_number : 'PROFORMA' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-bold" style="padding: 4px 0;">Invoice Date</td>
                        <td style="padding: 4px 0;">:</td>
                        <td class="w-normal" style="padding: 4px 0;">{{ $invoice->invoice_date->format('d-M-y') }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold" style="padding: 4px 0;">State</td>
                        <td style="padding: 4px 0;">:</td>
                        <td class="w-normal" style="padding: 4px 0;">{{ $invoice->state ?? 'Karnataka' }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold" style="padding: 4px 0;">State Code</td>
                        <td style="padding: 4px 0;">:</td>
                        <td class="w-bold" style="padding: 4px 0;">{{ $invoice->state_code ?? '29' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table keep-together">
        <thead>
            <tr>
                <th rowspan="2" style="width: 28px;">Sr No</th>
                <th rowspan="2" style="width: 125px;">Description of Goods</th>
                <th rowspan="2" style="width: 55px;">HSN/SAC</th>
                <th rowspan="2" style="width: 30px;">Qty</th>
                <th rowspan="2" style="width: 52px;">Rate (Peritem)</th>
                <th rowspan="2" style="width: 52px;">Total</th>
                <th rowspan="2" style="width: 60px;">Taxable Value</th>
                <th colspan="2">CGST</th>
                <th colspan="2">SGST</th>
                <th colspan="2">IGST</th>
            </tr>
            <tr>
                <th style="width: 28px;">Rate</th>
                <th style="width: 38px;">Amt</th>
                <th style="width: 28px;">Rate</th>
                <th style="width: 38px;">Amt</th>
                <th style="width: 28px;">Rate</th>
                <th style="width: 38px;">Amt</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $index => $item)
                @php
                    $lineBase = $item->total;
                    
                    $fallbackLocalRate = 1.5;
                    $fallbackOutstationRate = 3.0;
                    $cgstPercent = isset($invoice->cgst_percent) && $invoice->cgst_percent !== null ? (float)$invoice->cgst_percent : ($invoice->tax_type === 'local' ? $fallbackLocalRate : 0.0);
                    $sgstPercent = isset($invoice->sgst_percent) && $invoice->sgst_percent !== null ? (float)$invoice->sgst_percent : ($invoice->tax_type === 'local' ? $fallbackLocalRate : 0.0);
                    $igstPercent = isset($invoice->igst_percent) && $invoice->igst_percent !== null ? (float)$invoice->igst_percent : ($invoice->tax_type === 'outstation' ? $fallbackOutstationRate : 0.0);

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
                    <td class="w-bold">{{ $item->service_name }}</td>
                    <td class="text-center w-normal">{{ $item->hsn_sac ?? '7117' }}</td>
                    <td class="text-center w-normal">
                        {{ $item->qty === null || $item->qty === '' ? '' : (is_numeric($item->qty) ? number_format((float) $item->qty, (fmod((float) $item->qty, 1) == 0 ? 0 : 2), '.', ',') : $item->qty) }}
                    </td>
                    <td class="numeric w-normal">
                        {{ $item->rate === null || $item->rate === '' ? '' : (is_numeric($item->rate) ? '₹' . number_format((float) $item->rate, (fmod((float) $item->rate, 1) == 0 ? 0 : 2), '.', ',') : $item->rate) }}
                    </td>
                    <td class="numeric w-normal">
                        {{ is_numeric($lineBase) ? '₹' . number_format((float) $lineBase, (fmod((float) $lineBase, 1) == 0 ? 0 : 2), '.', ',') : $lineBase }}
                    </td>
                    <td class="numeric w-bold">
                        {{ is_numeric($rowInclusive) ? '₹' . number_format((float) $rowInclusive, (fmod((float) $rowInclusive, 1) == 0 ? 0 : 2), '.', ',') : $rowInclusive }}
                    </td>
                    <td class="text-center w-normal">{{ $invoice->tax_type === 'local' ? $cgstPercent . '%' : '-' }}</td>
                    <td class="numeric w-normal">
                        {{ is_numeric($rowCgst) ? number_format((float) $rowCgst, (fmod((float) $rowCgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                    </td>
                    <td class="text-center w-normal">{{ $invoice->tax_type === 'local' ? $sgstPercent . '%' : '-' }}</td>
                    <td class="numeric w-normal">
                        {{ is_numeric($rowSgst) ? number_format((float) $rowSgst, (fmod((float) $rowSgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                    </td>
                    <td class="text-center w-normal">{{ $invoice->tax_type === 'outstation' ? $igstPercent . '%' : '-' }}</td>
                    <td class="numeric w-normal">
                        {{ is_numeric($rowIgst) ? number_format((float) $rowIgst, (fmod((float) $rowIgst, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                    </td>
                </tr>
            @endforeach

            <tr class="w-bold">
                <td colspan="5" class="text-left">Total</td>
                <td class="numeric">₹
                    {{ is_numeric($invoice->taxable_value) ? number_format((float) $invoice->taxable_value, (fmod((float) $invoice->taxable_value, 1) == 0 ? 0 : 2), '.', ',') : $invoice->taxable_value }}
                </td>
                <td class="numeric">₹
                    {{ is_numeric($invoice->total_invoice_value) ? number_format((float) $invoice->total_invoice_value, (fmod((float) $invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',') : rtrim(rtrim($invoice->total_invoice_value, '0'), '.') }}
                </td>
                <td class="text-center">-</td>
                <td class="numeric">₹
                    {{ is_numeric($invoice->cgst_amount) ? number_format((float) $invoice->cgst_amount, (fmod((float) $invoice->cgst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                </td>
                <td class="text-center">-</td>
                <td class="numeric">₹
                    {{ is_numeric($invoice->sgst_amount) ? number_format((float) $invoice->sgst_amount, (fmod((float) $invoice->sgst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                </td>
                <td class="text-center">-</td>
                <td class="numeric">₹
                    {{ is_numeric($invoice->igst_amount) ? number_format((float) $invoice->igst_amount, (fmod((float) $invoice->igst_amount, 1) == 0 ? 0 : 2), '.', ',') : '-' }}
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Total In Words & reverse charge section -->
    <div class="mt-6 keep-together" style="font-size: 10px;">
        <div class="w-bold mb-4">
            Total Invoice Value (In figure):
            <span class="w-normal">₹
                {{ number_format((float) $invoice->total_invoice_value, (fmod($invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',') }}
            </span>
        </div>
        <div class="w-bold mb-4">Total Invoice Value (In Words): <span class="w-normal">{{ $invoice->total_invoice_value_words }}</span></div>
        <div class="w-bold">Amount of Tax subject to Reverse Charges: <span class="w-normal">NA</span></div>
    </div>

    <!-- Declaration & Signatory with bordered cells -->
    <table class="mt-8 keep-together" style="width: 100%; border-collapse: collapse; border: 1px solid #000;">
        <tr>
            <td style="width: 60%; border: 1px solid #000; height: 95px; vertical-align: top; padding: 6px;">
                <div><span class="w-bold">Declaration:</span> <span class="w-normal">NA</span></div>
            </td>
            <td style="width: 40%; border: 1px solid #000; height: 95px; vertical-align: top; padding: 6px; text-align: left;">
                @if($signatureBase64)
                    <div style="text-align: left; margin-bottom: 2px; height: 55px;">
                        <img class="signature-img" src="data:image/png;base64,{{ $signatureBase64 }}" alt="Signature" style="max-height: 55px; width: auto; max-width: 150px;">
                    </div>
                @else
                    <div style="height: 57px;"></div>
                @endif
                <div style="line-height: 1.2;">
                    <div class="w-bold uppercase" style="font-size: 9px;">Authorized Signatory</div>
                    <div style="font-size: 9px;"><span class="w-bold">Date:</span> <span class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</span></div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Footer Terms Note -->
    <div class="footer-text mt-6 keep-together" style="font-size: 8px; line-height: 1.25;">
        Note: Client shall NOT Resell, sublicense, lease, distribute, publish our products or services. Client shall irrevocably and unconditionally defend, indemnify and hold us harmless.
    </div>

    <!-- Computer Generated Note -->
    <div class="computer-generated keep-together">
        This is a <span class="underline">Computer Generated</span> {{ $invoice->is_paid ? 'Invoice' : ($invoice->is_cancelled ? 'Cancelled Invoice' : 'Proforma') }}
    </div>
</body>

</html>
