<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;

$invoices = Invoice::all();
foreach ($invoices as $invoice) {
    echo "Invoice #{$invoice->id}: org={$invoice->organisation_name}, client={$invoice->client_name}, customer_id=" . ($invoice->customer_id ?? 'NULL') . "\n";
}
