<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Lead;
use App\Models\Customer;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmailCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;
    protected EmailTemplate $template;
    protected Lead $lead1;
    protected Lead $lead2;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::findOrCreate('email-template-send');

        $adminRole = Role::findOrCreate('admin');
        $adminRole->givePermissionTo(['email-template-send']);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
        $this->adminUser->assignRole($adminRole);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);

        $this->template = EmailTemplate::create([
            'name' => 'Test Campaign',
            'subject' => 'Hello Lead',
            'body' => '<p>This is a test campaign.</p>',
        ]);

        $customer = Customer::create([
            'company_name' => 'Test Corp',
            'client_name' => 'John Doe',
            'mobile_no' => '9876543210',
            'email_id' => 'john@example.com',
            'registered_address' => '456 Lane St',
            'place' => 'Bangalore',
        ]);

        $this->lead1 = Lead::create([
            'customer_id' => $customer->id,
            'creation_source' => 'CRM',
            'company_name' => 'Test Corp 1',
            'customer_name' => 'Lead One',
            'email_id' => 'lead1@example.com',
            'state' => 'Karnataka',
            'state_code' => '29',
        ]);

        $this->lead2 = Lead::create([
            'customer_id' => $customer->id,
            'creation_source' => 'CRM',
            'company_name' => 'Test Corp 2',
            'customer_name' => 'Lead Two',
            'email_id' => 'lead2@example.com',
            'state' => 'Karnataka',
            'state_code' => '29',
        ]);
    }

    public function test_guest_cannot_access_email_campaign_route(): void
    {
        $response = $this->postJson(route('leads.send_email_campaign'), [
            'template_id' => $this->template->id,
            'ids' => $this->lead1->id,
        ]);

        $response->assertStatus(401);
    }

    public function test_user_without_permission_cannot_access_email_campaign_route(): void
    {
        $response = $this->actingAs($this->regularUser)->postJson(route('leads.send_email_campaign'), [
            'template_id' => $this->template->id,
            'ids' => $this->lead1->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_validation_errors_for_email_campaign_route(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('leads.send_email_campaign'), []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['template_id']);
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $response = $this->actingAs($this->adminUser)->postJson(route('leads.send_email_campaign'), [
            'template_id' => $this->template->id,
            'theme' => 'invalid-theme-value',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['theme']);
    }
}
