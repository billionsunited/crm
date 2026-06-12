<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo "Invoice #{$invoice->id}: lead_id=" . ($invoice->lead_id ?? 'NULL') . ", customer_id=" . ($invoice->customer_id ?? 'NULL') . ", client_name={$invoice->client_name}\n";
}
