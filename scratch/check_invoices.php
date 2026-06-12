<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Invoice;
use App\Models\Customer;

$invoices = Invoice::all();
echo "Total Invoices: " . $invoices->count() . "\n";

foreach ($invoices as $invoice) {
    echo "Invoice #{$invoice->id}: customer_id=" . ($invoice->customer_id ?? 'NULL') . ", client_name={$invoice->client_name}\n";
}

$customersWithInvoices = Customer::whereHas('invoices')->get();
echo "\nCustomers with Invoices in DB: " . $customersWithInvoices->count() . "\n";
foreach ($customersWithInvoices as $customer) {
    echo "Customer #{$customer->id}: company_name={$customer->company_name}, client_name={$customer->client_name}\n";
}
