<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class OrInvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected Customer $customer;
    protected Lead $lead;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create the permissions
        Permission::findOrCreate('invoice-section');
        Permission::findOrCreate('invoice-or-section');
        Permission::findOrCreate('email-section');

        // Create admin and regular users
        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(['invoice-section', 'invoice-or-section', 'email-section']);
        
        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        // Create customer and lead for testing
        $this->customer = Customer::create([
            'company_name' => 'Test Corp',
            'client_name' => 'John Doe',
            'mobile_no' => '9876543210',
            'email_id' => 'john@example.com',
            'registered_address' => '456 Lane St',
            'place' => 'Bangalore',
        ]);

        $this->lead = Lead::create([
            'customer_id' => $this->customer->id,
            'creation_source' => 'CRM',
            'company_name' => 'Test Corp',
            'state' => 'Karnataka',
            'state_code' => '29',
        ]);
    }

    public function test_guest_cannot_access_or_invoice_routes(): void
    {
        $this->get(route('or-invoices.index'))->assertRedirect(route('login'));
        $this->get(route('or-invoices.create'))->assertRedirect(route('login'));
        $this->post(route('or-invoices.store'), [])->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_or_invoice_routes(): void
    {
        $response = $this->actingAs($this->regularUser)->get(route('or-invoices.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->regularUser)->get(route('or-invoices.create'));
        $response->assertStatus(403);
    }

    public function test_admin_with_permission_can_access_or_invoice_routes(): void
    {
        $response = $this->actingAs($this->adminUser)->get(route('or-invoices.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)->get(route('or-invoices.create'));
        $response->assertStatus(200);
    }

    public function test_listings_are_isolated(): void
    {
        // 1. Create a standard invoice
        $standardInvoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-STANDARD-1',
            'invoice_date' => '2026-05-15',
            'invoice_per_type' => 'standard',
            'client_name' => 'John Doe Standard',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9,
            'cgst_amount' => 9,
            'sgst_percent' => 9,
            'sgst_amount' => 9,
            'total_invoice_value' => 118.00,
        ]);

        // 2. Create an OR invoice
        $orInvoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-OR-1',
            'invoice_date' => '2026-05-15',
            'invoice_per_type' => 'or',
            'client_name' => 'John Doe OR',
            'tax_type' => 'outstation',
            'taxable_value' => 200.00,
            'igst_percent' => 3,
            'igst_amount' => 6,
            'total_invoice_value' => 206.00,
        ]);

        // Standard index should only show standard invoice
        $response = $this->actingAs($this->adminUser)->get(route('invoices.index'));
        $response->assertSee('John Doe Standard');
        $response->assertDontSee('John Doe OR');

        // OR index should only show OR invoice
        $response = $this->actingAs($this->adminUser)->get(route('or-invoices.index'));
        $response->assertSee('John Doe OR');
        $response->assertDontSee('John Doe Standard');

        // Standard routes should enforce standard type and return 404 for OR invoice
        $this->actingAs($this->adminUser)->get(route('invoices.show', $orInvoice->id))->assertStatus(404);
        $this->actingAs($this->adminUser)->get(route('invoices.download', $orInvoice->id))->assertStatus(404);

        // OR routes should enforce OR type and return 404 for standard invoice
        $this->actingAs($this->adminUser)->get(route('or-invoices.show', $standardInvoice->id))->assertStatus(404);
        $this->actingAs($this->adminUser)->get(route('or-invoices.download', $standardInvoice->id))->assertStatus(404);
    }

    public function test_create_or_invoice_with_local_tax(): void
    {
        $payload = [
            'lead_id' => $this->lead->id,
            'client_name' => 'Acme Corp',
            'organisation_name' => 'Acme Corp Org',
            'address' => '123 Main St',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'state_code' => '29',
            'tax_type' => 'local',
            'items' => [
                [
                    'service_name' => 'Testing Local Service',
                    'hsn_sac' => '998599',
                    'qty' => 2,
                    'rate' => 50.00,
                    'total' => 100.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('or-invoices.store'), $payload);

        // Verify redirect
        $invoice = Invoice::where('invoice_per_type', 'or')->firstOrFail();
        $response->assertRedirect(route('or-invoices.show', $invoice->id));

        // Assert DB calculations
        $this->assertEquals('or', $invoice->invoice_per_type);
        $this->assertEquals(100.00, $invoice->taxable_value);
        $this->assertEquals('local', $invoice->tax_type);
        $this->assertEquals(1.5, $invoice->cgst_percent);
        $this->assertEquals(1.50, $invoice->cgst_amount);
        $this->assertEquals(1.5, $invoice->sgst_percent);
        $this->assertEquals(1.50, $invoice->sgst_amount);
        $this->assertEquals(0, $invoice->igst_percent);
        $this->assertEquals(0.00, $invoice->igst_amount);
        $this->assertEquals(103.00, $invoice->total_invoice_value);
        $this->assertEquals('Design Services', $invoice->service_description_meta);
    }

    public function test_create_or_invoice_with_outstation_tax(): void
    {
        $payload = [
            'lead_id' => $this->lead->id,
            'client_name' => 'Outstation Corp',
            'organisation_name' => 'Outstation Corp Org',
            'address' => '456 Other St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'state_code' => '27',
            'tax_type' => 'outstation',
            'items' => [
                [
                    'service_name' => 'Testing Outstation Service',
                    'hsn_sac' => '998599',
                    'qty' => 1,
                    'rate' => 100.00,
                    'total' => 100.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('or-invoices.store'), $payload);

        // Verify redirect
        $invoice = Invoice::where('invoice_per_type', 'or')->firstOrFail();
        $response->assertRedirect(route('or-invoices.show', $invoice->id));

        // Assert DB calculations (IGST should be 3% for OR)
        $this->assertEquals('or', $invoice->invoice_per_type);
        $this->assertEquals(100.00, $invoice->taxable_value);
        $this->assertEquals('outstation', $invoice->tax_type);
        $this->assertEquals(0, $invoice->cgst_percent);
        $this->assertEquals(0.00, $invoice->cgst_amount);
        $this->assertEquals(0, $invoice->sgst_percent);
        $this->assertEquals(0.00, $invoice->sgst_amount);
        $this->assertEquals(3, $invoice->igst_percent);
        $this->assertEquals(3.00, $invoice->igst_amount);
        $this->assertEquals(103.00, $invoice->total_invoice_value);
    }

    public function test_mark_as_paid_generates_correct_sequence_number(): void
    {
        // 1. Create a proforma invoice of type OR
        $invoice1 = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PROFORMA-OR-1',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'John Doe OR 1',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9,
            'cgst_amount' => 9,
            'sgst_percent' => 9,
            'sgst_amount' => 9,
            'total_invoice_value' => 118.00,
            'is_paid' => false,
        ]);

        $invoice2 = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PROFORMA-OR-2',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'John Doe OR 2',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9,
            'cgst_amount' => 9,
            'sgst_percent' => 9,
            'sgst_amount' => 9,
            'total_invoice_value' => 118.00,
            'is_paid' => false,
        ]);

        // Verify we start at sequence 43 with prefix OR/
        $response = $this->actingAs($this->adminUser)->post(route('or-invoices.mark_paid', $invoice1->id));
        $response->assertRedirect();
        
        $invoice1->refresh();
        $this->assertTrue($invoice1->is_paid);
        $this->assertEquals(43, $invoice1->invoice_sequence);
        $financialYear = Invoice::getFinancialYear($invoice1->invoice_date);
        $expectedNumber1 = "OR/043/{$financialYear}";
        $this->assertEquals($expectedNumber1, $invoice1->invoice_number);

        // Next invoice should be 44
        $response2 = $this->actingAs($this->adminUser)->post(route('or-invoices.mark_paid', $invoice2->id));
        $response2->assertRedirect();

        $invoice2->refresh();
        $this->assertTrue($invoice2->is_paid);
        $this->assertEquals(44, $invoice2->invoice_sequence);
        $expectedNumber2 = "OR/044/{$financialYear}";
        $this->assertEquals($expectedNumber2, $invoice2->invoice_number);
    }

    public function test_export_only_returns_or_invoices_for_selected_month_and_year(): void
    {
        $currentMonth = (int) now()->format('m');
        $currentYear = (int) now()->format('Y');

        // Create standard invoice in the same month/year
        $stdInvoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-STD-EXP',
            'invoice_date' => now()->format('Y-m-d'),
            'invoice_per_type' => 'standard',
            'client_name' => 'Standard Export Client',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 9,
            'cgst_amount' => 9,
            'sgst_percent' => 9,
            'sgst_amount' => 9,
            'total_invoice_value' => 118.00,
        ]);

        // Create OR invoice in the same month/year
        $orInvoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-OR-EXP',
            'invoice_date' => now()->format('Y-m-d'),
            'paid_at' => now(),
            'invoice_per_type' => 'or',
            'is_paid' => true,
            'client_name' => 'OR Export Client',
            'tax_type' => 'outstation',
            'taxable_value' => 100.00,
            'igst_percent' => 3,
            'igst_amount' => 3,
            'total_invoice_value' => 103.00,
        ]);

        // Insert at least one item since export with('items') will check items
        InvoiceItem::create([
            'invoice_id' => $orInvoice->id,
            'service_name' => 'Consulting Service OR',
            'qty' => 1,
            'rate' => 100.00,
            'total' => 100.00,
        ]);

        $response = $this->actingAs($this->adminUser)->get(route('or-invoices.export', [
            'month' => $currentMonth,
            'year' => $currentYear,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check that OR invoice is present, and standard invoice is NOT present
        $this->assertStringContainsString('OR Export Client', $content);
        $this->assertStringContainsString('103', $content); // Total invoice value
        
        $this->assertStringNotContainsString('Standard Export Client', $content);

        // Check that removed columns are NOT present
        $this->assertStringNotContainsString('"Invoice ID"', $content);
        $this->assertStringNotContainsString('"Invoice Type"', $content);
        $this->assertStringNotContainsString('"Payment Status"', $content);
        $this->assertStringNotContainsString('"Invoice Per Type"', $content);
        $this->assertStringNotContainsString('"Item Service Name"', $content);
    }

    public function test_cancel_or_invoice_assigns_sequence_and_generates_pdf(): void
    {
        Storage::fake('local');

        // Create a proforma OR invoice
        $invoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PROFORMA-OR-CANCEL',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'Cancel Test Client',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 1.5,
            'cgst_amount' => 1.5,
            'sgst_percent' => 1.5,
            'sgst_amount' => 1.5,
            'total_invoice_value' => 103.00,
            'is_paid' => false,
            'is_cancelled' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('or-invoices.cancel', $invoice->id));
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Invoice cancelled and sequence generated successfully.');

        $invoice->refresh();
        $this->assertTrue($invoice->is_cancelled);
        $this->assertFalse($invoice->is_paid);
        $this->assertNull($invoice->paid_at);
        $this->assertEquals('final', $invoice->invoice_type);
        $this->assertEquals(43, $invoice->invoice_sequence);

        $financialYear = Invoice::getFinancialYear($invoice->invoice_date);
        $expectedNumber = "OR/043/{$financialYear}";
        $this->assertEquals($expectedNumber, $invoice->invoice_number);

        // Verify PDF was generated and saved
        $this->assertNotNull($invoice->pdf_file_path);
        Storage::assertExists($invoice->pdf_file_path);

        // Verify the index page shows the sequence number and not the CANCELLED badge
        $indexResponse = $this->actingAs($this->adminUser)->get(route('or-invoices.index'));
        $indexResponse->assertSee($expectedNumber);
        $indexResponse->assertDontSee('<span class="text-red-600">CANCELLED</span>', false);
    }

    public function test_deleting_cancelled_or_invoice_preserves_sequence_number(): void
    {
        Storage::fake('local');

        // Create two proforma OR invoices
        $invoice1 = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PRO-OR-1',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'Delete Test Client 1',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 1.5,
            'cgst_amount' => 1.5,
            'sgst_percent' => 1.5,
            'sgst_amount' => 1.5,
            'total_invoice_value' => 103.00,
            'is_paid' => false,
            'is_cancelled' => false,
        ]);

        $invoice2 = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PRO-OR-2',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'Delete Test Client 2',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 1.5,
            'cgst_amount' => 1.5,
            'sgst_percent' => 1.5,
            'sgst_amount' => 1.5,
            'total_invoice_value' => 103.00,
            'is_paid' => false,
            'is_cancelled' => false,
        ]);

        // 1. Cancel the first invoice (assigns sequence 43)
        $this->actingAs($this->adminUser)->post(route('or-invoices.cancel', $invoice1->id))->assertRedirect();
        $invoice1->refresh();
        $this->assertEquals(43, $invoice1->invoice_sequence);

        // 2. Soft delete the first invoice (must be cancelled first, which it is)
        $this->actingAs($this->adminUser)->delete(route('or-invoices.destroy', $invoice1->id))->assertRedirect();
        
        // Assert the model is soft deleted
        $this->assertSoftDeleted($invoice1);

        // 3. Cancel the second invoice. It should get sequence 44, NOT 43!
        $this->actingAs($this->adminUser)->post(route('or-invoices.cancel', $invoice2->id))->assertRedirect();
        $invoice2->refresh();
        
        $this->assertEquals(44, $invoice2->invoice_sequence);
        $financialYear = Invoice::getFinancialYear($invoice2->invoice_date);
        $expectedNumber = "OR/044/{$financialYear}";
        $this->assertEquals($expectedNumber, $invoice2->invoice_number);
    }

    public function test_admin_can_delete_non_cancelled_or_invoice(): void
    {
        $invoice = Invoice::create([
            'lead_id' => $this->lead->id,
            'customer_id' => $this->customer->id,
            'invoice_number' => 'PROFORMA-OR-DELETE',
            'invoice_date' => now(),
            'invoice_per_type' => 'or',
            'client_name' => 'Delete Test Client',
            'tax_type' => 'local',
            'taxable_value' => 100.00,
            'cgst_percent' => 1.5,
            'cgst_amount' => 1.5,
            'sgst_percent' => 1.5,
            'sgst_amount' => 1.5,
            'total_invoice_value' => 103.00,
            'is_paid' => false,
            'is_cancelled' => false,
        ]);

        $response = $this->actingAs($this->adminUser)->delete(route('or-invoices.destroy', $invoice->id));
        $response->assertRedirect(route('or-invoices.index'));
        $this->assertSoftDeleted($invoice);
    }

    public function test_create_or_invoice_with_blank_qty_and_rate(): void
    {
        $payload = [
            'lead_id' => $this->lead->id,
            'client_name' => 'Blank Qty Rate Corp',
            'organisation_name' => 'Blank Qty Rate Corp Org',
            'address' => '123 Main St',
            'city' => 'Bangalore',
            'state' => 'Karnataka',
            'state_code' => '29',
            'tax_type' => 'local',
            'items' => [
                [
                    'service_name' => 'Testing Blank Qty Rate',
                    'hsn_sac' => '998599',
                    'qty' => '',
                    'rate' => '',
                    'total' => 100.00,
                ]
            ]
        ];

        $response = $this->actingAs($this->adminUser)->post(route('or-invoices.store'), $payload);

        // Verify redirect
        $invoice = Invoice::where('invoice_per_type', 'or')->where('client_name', 'Blank Qty Rate Corp')->firstOrFail();
        $response->assertRedirect(route('or-invoices.show', $invoice->id));

        // Assert DB has null qty and rate
        $item = $invoice->items()->firstOrFail();
        $this->assertNull($item->qty);
        $this->assertNull($item->rate);
        $this->assertEquals(100.00, $item->total);

        // Show page should render blank for Qty and Rate
        $showResponse = $this->actingAs($this->adminUser)->get(route('or-invoices.show', $invoice->id));
        $showResponse->assertStatus(200);
        
        // Assert it does NOT contain '1' or '0' in the qty/rate columns of the show template.
        // Wait, the show template renders them inside: <td class="text-center w-normal">...</td>
        // Let's check that the rendered HTML has an empty tag or does not contain specific '0' or '1'
        $showContent = $showResponse->getContent();
        $this->assertStringContainsString('<td class="text-center w-normal">' . "\n" . '                                        ' . "\n" . '                                    </td>', $showContent);
    }
}
