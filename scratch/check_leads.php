<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\Lead;

$invoices = Invoice::whereNull('customer_id')->get();
echo "Invoices with NULL customer_id: " . $invoices->count() . "\n";

foreach ($invoices as $invoice) {
    $lead = Lead::find($invoice->lead_id);
    if ($lead) {
        echo "Invoice #{$invoice->id}: Lead #{$lead->id}, Lead customer_id=" . ($lead->customer_id ?? 'NULL') . ", Lead client_name={$lead->client_name}\n";
    } else {
        echo "Invoice #{$invoice->id}: Lead #{$invoice->lead_id} NOT FOUND\n";
    }
}
