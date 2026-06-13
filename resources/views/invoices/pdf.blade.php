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
            border-bottom: none;
            text-align: center;
            font-weight: 700;
            text-transform: uppercase;
            padding: 4px;
            font-size: 11px;
        }

        .header-info td {
            border: none;
            padding: 0;
            line-height: 1.2;
        }

        .meta-table td {
            border: none;
            border-bottom: 1px solid #000;
            padding: 4px 6px;
        }

        .meta-table tr:last-child td {
            border-bottom: none;
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

        .signature-img {
            width: 90px;
            max-width: 90px;
            max-height: 60px;
            height: auto;
            display: inline-block;
        }
    </style>
</head>

<body>
    @php
        $signaturePath = public_path('signature.png');
        $signatureBase64 = file_exists($signaturePath) ? base64_encode(file_get_contents($signaturePath)) : null;
    @endphp

    <table class="header-info keep-together">
        <tr>
            <td style="width: 48%;"></td>
            <td style="width: 52%; padding-left: 28px;">
                <div class="mb-4"><span class="w-bold">GSTIN:</span> <span class="w-bold">29AKNPG5479L1ZB</span></div>
                <div class="mb-4"><span class="w-bold">Name:</span> <span class="w-bold uppercase">Billions
                        United</span></div>
                <div class="mb-4"><span class="w-bold">Address:</span> <span class="w-normal">#35, 1st Floor, 24th Main,
                        J.P.Nagar 7th Phase, Above Tyre Care Center, Bangalore-560078</span></div>
                <div class="mb-4"><span class="w-bold">Date of Invoice:</span> <span
                        class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</span></div>
                <div class="mb-1"><span class="w-bold">Invoice Type:</span> <span
                        class="w-bold uppercase">{{ $invoice->is_paid ? 'Tax Invoice' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}</span>
                </div>
                <div class="mb-4"><span class="w-bold">Contact Details:</span> <span class="w-normal">+91
                        8048516757</span></div>
                <div class="mb-4"><span class="w-bold underline">www.billionsunited.com</span></div>
            </td>
        </tr>
    </table>
    <br>
    <div class="invoice-title keep-together">{{ $invoice->is_paid ? 'INVOICE' : ($invoice->is_cancelled ? 'CANCELLED' : 'PROFORMA') }}</div>

    <table class="border-box keep-together">
        <tr>
            <td style="width: 50%; border-right: 1px solid #000; padding: 7px;">
                <div class="w-bold underline uppercase mb-6">Details of Receiver (Billed to)</div>
                <div class="mb-4"><span class="w-bold">Name:</span> <span
                        class="w-normal">{{ $invoice->client_name }}</span></div>
                <div class="mb-4"><span class="w-bold">Organization Name:</span> <span
                        class="w-bold uppercase">{{ $invoice->organisation_name !== 'None' ? $invoice->organisation_name : 'None' }}</span>
                </div>
                <div class="mb-4"><span class="w-bold">Address:</span> <span
                        class="w-normal">{{ $invoice->address }}</span></div>
                <div class="mb-4"><span class="w-bold">State & Code:</span> <span
                        class="w-normal">{{ $invoice->state ?? 'Karnataka' }} ({{ $invoice->state_code ?? '29' }})</span></div>
                <div class="mb-1"><span class="w-bold">Mobile No:</span> <span
                        class="w-normal">{{ $invoice->customer->mobile_no ?? 'NONE' }}</span></div>
                <div class="mb-1"><span class="w-bold">Email Id:</span> <span
                        class="w-normal">{{ $invoice->customer->email_id ?? 'NONE' }}</span></div>
                <div class="mt-1"><span class="w-bold">Aadhar No: </span> <span
                        class="w-normal uppercase">{{ $invoice->aadhar_no ?? 'NONE' }}</span>
                </div>
                <div class="mt-1"><span class="w-bold">PAN No -</span> <span
                        class="w-normal uppercase">{{ strtoupper($invoice->pan_no) }}</span></div>
                <div class="mb-1"><span class="w-bold">Udyam/Estd. Certificate -</span> <span
                        class="w-normal uppercase">{{ strtoupper($invoice->udyam_certificate) }}</span></div>

                <div><span class="w-bold">GSTIN/Unique ID:</span> <span
                        class="w-bold uppercase">{{ strtoupper($invoice->gstin_unique_id) }}</span></div>
            </td>

            <td style="width: 50%; padding: 0;">
                <table class="meta-table">
                    <tr>
                        <td class="w-bold" style="width: 45%;">Purchase Order</td>
                        <td style="width: 5%;">:</td>
                        <td class="w-normal" style="width: 50%;">{{ $invoice->purchase_order ?: 'NA' }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold">Service Description</td>
                        <td>:</td>
                        <td class="w-normal">{{ $invoice->service_description_meta ?: 'SMS & WhatsApp' }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold">Invoice Number</td>
                        <td>:</td>
                        <td class="w-normal">{{ ($invoice->is_paid || $invoice->is_cancelled) ? $invoice->invoice_number : 'PROFORMA' }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold">Invoice Date</td>
                        <td>:</td>
                        <td class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold">PAN</td>
                        <td>:</td>
                        <td class="w-normal">AKNPG5479L</td>
                    </tr>
                    <tr>
                        <td class="w-bold">GSTIN/Unique ID</td>
                        <td>:</td>
                        <td class="w-bold">29AKNPG5479L1ZB</td>
                    </tr>
                    <tr>
                        <td class="w-bold">Due Date</td>
                        <td>:</td>
                        <td class="w-normal">
                            {{ $invoice->due_date ? (is_string($invoice->due_date) ? $invoice->due_date : $invoice->due_date->format('d-M-y')) : 'Immediate' }}
                        </td>
                    </tr>
                    <tr>
                        <td class="w-bold">State</td>
                        <td>:</td>
                        <td class="w-normal">{{ $invoice->state ?? 'Karnataka' }}</td>
                    </tr>
                    <tr>
                        <td class="w-bold">State Code</td>
                        <td>:</td>
                        <td class="w-bold">{{ $invoice->state_code ?? '29' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

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
                    
                    $fallbackLocalRate = ($invoice->invoice_per_type === 'or') ? 1.5 : 9.0;
                    $fallbackOutstationRate = ($invoice->invoice_per_type === 'or') ? 3.0 : 18.0;
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
                    <td class="text-center w-normal">{{ $item->hsn_sac ?? ($invoice->invoice_per_type === 'or' ? '7117' : '998599') }}</td>
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

    <div class="mt-6 keep-together" style="font-size: 10px;">
        <div class="w-bold mb-4">
            Total Invoice Value (In figure):
            <span class="w-normal">₹
                {{ number_format((float) $invoice->total_invoice_value, (fmod($invoice->total_invoice_value, 1) == 0 ? 0 : 2), '.', ',') }}
            </span>
        </div>
        <div class="w-bold mb-4">Total Invoice Value (In Words): <span
                class="w-normal">{{ $invoice->total_invoice_value_words }}</span></div>
        <div class="w-bold">Amount of Tax subject to Reverse Charges: <span class="w-normal">NA</span></div>
    </div>

    <table class="mt-8 keep-together">
        <tr>
            <td style="width: 65%; vertical-align: top;">
                <div class="mb-6"><span class="w-bold">Declaration:</span> <span class="w-normal italic">NA</span></div>
                <div><span class="w-bold">Signatory:</span> <span class="w-normal">Billions United</span></div>
            </td>
            <td style="width: 35%; text-align:left; vertical-align: top;">
                <div class="w-bold mb-4">For Billions United</div>
                @if($signatureBase64)
                    <div class="mb-4">
                        <img class="signature-img" src="data:image/png;base64,{{ $signatureBase64 }}" alt="Signature">
                    </div><br>
                @endif
                <div style="line-height: 1.15;">
                    <div class="w-bold uppercase">Authorized Signatory</div>
                    <div><span class="w-bold">Name:</span> <span class="w-normal">Billions United</span></div>
                    <div><span class="w-bold">Designation / Status:</span> <span class="w-normal">Proprietor</span>
                    </div>
                    <div><span class="w-bold">Date:</span> <span
                            class="w-normal">{{ $invoice->invoice_date->format('d-M-y') }}</span></div>
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-text mt-6 keep-together">
        <span class="w-bold underline">Terms:</span><br>
        Services & prices are subject to TRAI Rules & Regulations and other Applicable Laws<br><br>
        Payments to be made through Cheque/D.D in favour of BILLIONS UNITED OR Direct <span
            class="w-bold underline">Bank Transfer</span> to ICICI BANK LTD, A/C.No.<span
            class="w-bold">100705000705</span>, IFSC/NEFT/RTGS-ICIC0001007, MICR Code- 560229039<br><br>
        Note: Client shall NOT Resell, sublicense, lease, distribute, publish our services. Client shall not use our
        services for phishing, impersonation, fraud, financial scams, digital arrest or cybercrime. T&C is on MSA.
        Client shall irrevocably and unconditionally defend, indemnify and hold harmless Billions United. Billions
        United, in its sole discretion, reserves the right to disconnect the services without any notice.
    </div>

    <div class="computer-generated keep-together">
        This is a <span class="underline">Computer Generated</span> {{ $invoice->is_paid ? 'Invoice' : ($invoice->is_cancelled ? 'Cancelled Invoice' : 'Proforma') }}
    </div>
</body>

</html>