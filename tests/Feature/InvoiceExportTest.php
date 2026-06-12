<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Customer;
use App\Models\Lead;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InvoiceExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create the permission required for invoice routes
        Permission::findOrCreate('invoice-section');
    }

    public function test_guest_cannot_access_export(): void
    {
        $response = $this->get(route('invoices.export', ['month' => 5, 'year' => 2026]));
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_export(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('invoices.export', ['month' => 5, 'year' => 2026]));
        $response->assertStatus(403);
    }

    public function test_export_redirects_with_error_if_no_invoices_found(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo('invoice-section');
        $admin->assignRole($adminRole);

        $response = $this->actingAs($admin)
            ->from(route('invoices.index'))
            ->get(route('invoices.export', ['month' => 5, 'year' => 2026]));

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('error', 'No invoices found for the selected month and year.');
    }

    public function test_export_streams_csv_for_matching_invoices(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo('invoice-section');
        $admin->assignRole($adminRole);

        // Create a customer
        $customer = Customer::create([
            'company_name' => 'Acme Corp',
            'client_name' => 'Jane Doe',
            'mobile_no' => '1234567890',
            'email_id' => 'jane@example.com',
        ]);

        // Create a lead
        $lead = Lead::create([
            'customer_id' => $customer->id,
            'creation_source' => 'CRM',
            'company_name' => 'Acme Corp',
        ]);

        // Create a matching invoice and item
        $invoice = Invoice::create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-2026-001',
            'invoice_date' => '2026-05-15',
            'paid_at' => '2026-05-15 12:00:00',
            'invoice_type' => 'final',
            'invoice_per_type' => 'standard',
            'is_paid' => true,
            'client_name' => 'Jane Doe',
            'organisation_name' => 'Acme Corp',
            'address' => '123 Main St',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'state_code' => '29',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9.00,
            'cgst_amount' => 9.00,
            'sgst_percent' => 9.00,
            'sgst_amount' => 9.00,
            'total_invoice_value' => 118.00,
        ]);

        $item1 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_name' => 'Consulting Service A',
            'qty' => 1,
            'rate' => 60.00,
            'total' => 60.00,
        ]);

        $item2 = InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'service_name' => 'Consulting Service B',
            'qty' => 1,
            'rate' => 40.00,
            'total' => 40.00,
        ]);

        $response = $this->actingAs($admin)->get(route('invoices.export', ['month' => 5, 'year' => 2026]));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename="invoices-export-May-2026.csv"');

        // Capture streamed content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check header columns are present/absent
        $this->assertStringNotContainsString('"Invoice ID"', $content);
        $this->assertStringContainsString('"Invoice Number"', $content);
        $this->assertStringNotContainsString('"Invoice Type"', $content);
        $this->assertStringNotContainsString('"Invoice Per Type"', $content);
        $this->assertStringNotContainsString('"Item Service Name"', $content);
        $this->assertStringNotContainsString('"Payment Status"', $content);
        $this->assertStringContainsString('"Total Invoice"', $content);

        // Check row contents
        $this->assertStringContainsString('INV-2026-001,2026-05-15', $content);
        $this->assertStringContainsString('100,9,9,0,118', $content); // Taxable value, CGST, SGST, IGST, Total Invoice
    }

    public function test_unpaid_invoices_are_excluded_from_export(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);
        
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo('invoice-section');
        $admin->assignRole($adminRole);

        // Create a customer
        $customer = Customer::create([
            'company_name' => 'Acme Corp',
            'client_name' => 'Jane Doe',
            'mobile_no' => '1234567890',
            'email_id' => 'jane@example.com',
        ]);

        // Create a lead
        $lead = Lead::create([
            'customer_id' => $customer->id,
            'creation_source' => 'CRM',
            'company_name' => 'Acme Corp',
        ]);

        // Create an UNPAID invoice (is_paid = false)
        Invoice::create([
            'lead_id' => $lead->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-UNPAID-001',
            'invoice_date' => '2026-05-15',
            'invoice_per_type' => 'standard',
            'is_paid' => false,
            'client_name' => 'Jane Doe',
            'organisation_name' => 'Acme Corp',
            'address' => '123 Main St',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'state_code' => '29',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9.00,
            'cgst_amount' => 9.00,
            'sgst_percent' => 9.00,
            'sgst_amount' => 9.00,
            'total_invoice_value' => 118.00,
        ]);

        // Access the export route, which should return a redirect if no matching PAID invoices exist
        $response = $this->actingAs($admin)
            ->from(route('invoices.index'))
            ->get(route('invoices.export', ['month' => 5, 'year' => 2026]));

        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('error', 'No invoices found for the selected month and year.');
    }
}
