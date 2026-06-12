<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== OR INVOICES WITH NULL lead_id ===" . PHP_EOL;
$nullLeadCount = App\Models\Invoice::where('invoice_per_type', 'or')
    ->whereNull('lead_id')
    ->count();
echo "OR Invoices with lead_id = NULL: {$nullLeadCount}" . PHP_EOL;

echo PHP_EOL . "=== SAMPLE OR INVOICES ===" . PHP_EOL;
$invoices = App\Models\Invoice::with('lead')
    ->where('invoice_per_type', 'or')
    ->latest()
    ->take(5)
    ->get();

foreach ($invoices as $inv) {
    $leadId  = $inv->lead_id ?? 'NULL';
    $seqNum  = $inv->lead ? $inv->lead->sequence_number : 'NO_LEAD';
    $ref     = $inv->lead ? ($inv->lead->reference ?? 'NULL') : 'NO_LEAD';
    echo "Invoice: {$inv->invoice_number} | client: {$inv->client_name} | lead_id: {$leadId} | seq_num: {$seqNum} | reference: {$ref}" . PHP_EOL;
}

echo PHP_EOL . "=== LEAD #236 ===" . PHP_EOL;
$lead = App\Models\Lead::where('sequence_number', 236)->first();
if ($lead) {
    echo "Lead found: ID={$lead->id} | record_id={$lead->record_id} | customer_id={$lead->customer_id}" . PHP_EOL;
    $linkedInvoices = App\Models\Invoice::where('lead_id', $lead->id)->count();
    echo "Invoices linked to this lead: {$linkedInvoices}" . PHP_EOL;
} else {
    echo "Lead with sequence_number=236 NOT FOUND in this database" . PHP_EOL;
}
