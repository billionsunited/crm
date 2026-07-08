<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrInvoiceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\VendorLeadController;
use App\Http\Controllers\MessagingController;
use App\Http\Controllers\CampaignLeadController;
use App\Http\Controllers\EmailTemplateController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/office-closed', function () {
    $now = \Carbon\Carbon::now('Asia/Kolkata');
    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    $currentDayIndex = $now->dayOfWeek;
    
    $nextOpenTime = null;
    $nextOpenDay = null;

    $today = \App\Models\OfficeTiming::where('day_of_week', $now->format('l'))->first();
    if ($today && $today->is_working_day && $now->format('H:i:s') < $today->start_time) {
        $nextOpenTime = $today->start_time;
        $nextOpenDay = 'Today';
    } else {
        for ($i = 1; $i <= 7; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $checkDayName = $days[$checkDayIndex];
            $timing = \App\Models\OfficeTiming::where('day_of_week', $checkDayName)->first();
            
            if ($timing && $timing->is_working_day && $timing->start_time) {
                $nextOpenTime = $timing->start_time;
                $nextOpenDay = $i === 1 ? 'Tomorrow' : $checkDayName;
                break;
            }
        }
    }

    $formattedTime = $nextOpenTime ? \Carbon\Carbon::parse($nextOpenTime)->format('h:i A') : null;

    return view('office_closed', compact('nextOpenDay', 'formattedTime'));
})->name('office_closed');

Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
Route::get('invoices/{invoice}/download-page', [InvoiceController::class, 'downloadPage'])->name('invoices.download-page');
Route::get('or-invoices/{invoice}/download', [OrInvoiceController::class, 'download'])->name('or-invoices.download');
Route::get('or-invoices/{invoice}/download-page', [OrInvoiceController::class, 'downloadPage'])->name('or-invoices.download-page');

Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Server cache cleared successfully!';
});


Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('permission:dashboard-access')->get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::resource('customers', CustomerController::class);

    // Leads - IMPORTANT: Order matters! Specific routes before parameter routes.
    Route::middleware('permission:lead-add')->group(function () {
        Route::get('leads/create', [LeadController::class, 'create'])->name('leads.create');
        Route::post('leads', [LeadController::class, 'store'])->name('leads.store');
    });

    Route::middleware('permission:lead-view')->group(function () {
        Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
        Route::get('leads/{lead}', [LeadController::class, 'show'])->name('leads.show');
    });

    Route::middleware('permission:lead-send-document')->post('leads/{lead}/send-file', [LeadController::class, 'sendFile'])->name('leads.send_file');
    Route::middleware('permission:lead-send-document')->post('leads/{lead}/send-agreement', [LeadController::class, 'sendClientAgreement'])->name('leads.send_agreement');
    Route::middleware('permission:lead-edit')->post('leads/{lead}/blacklist-flag', [LeadController::class, 'updateBlacklistFlag'])->name('leads.blacklist_flag');

    Route::middleware('permission:lead-edit')->group(function () {
        Route::get('leads/{lead}/edit', [LeadController::class, 'edit'])->name('leads.edit');
        Route::put('leads/{lead}', [LeadController::class, 'update'])->name('leads.update');
    });

    Route::middleware('permission:lead-delete')->delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
    Route::middleware('permission:lead-delete')->post('leads/bulk-destroy', [LeadController::class, 'bulkDestroy'])->name('leads.bulk_destroy');

    Route::middleware('permission:lead-import')->post('leads-import', [LeadController::class, 'import'])->name('leads.import');
    Route::middleware('permission:lead-export')->get('leads-export', [LeadController::class, 'export'])->name('leads.export');
    Route::get('leads-sample-csv', [LeadController::class, 'sampleCsv'])->name('leads.sample_csv');

    // Enquiry Campaign Leads
    Route::middleware('permission:campaign-add')->group(function () {
        Route::get('campaign-leads/create', [CampaignLeadController::class, 'create'])->name('campaign-leads.create');
        Route::post('campaign-leads', [CampaignLeadController::class, 'store'])->name('campaign-leads.store');
    });

    Route::middleware('permission:campaign-import')->group(function () {
        Route::post('campaign-leads/import', [CampaignLeadController::class, 'import'])->name('campaign-leads.import');
        Route::get('campaign-leads/sample-csv', [CampaignLeadController::class, 'sampleCsv'])->name('campaign-leads.sample-csv');
    });

    Route::middleware('permission:campaign-export')->get('campaign-leads/export', [CampaignLeadController::class, 'export'])->name('campaign-leads.export');

    Route::middleware('permission:campaign-view')->group(function () {
        Route::get('campaign-leads', [CampaignLeadController::class, 'index'])->name('campaign-leads.index');
        Route::get('campaign-leads/all-contacts', [CampaignLeadController::class, 'getAllContacts'])->name('campaign-leads.all-contacts');
        Route::get('campaign-leads/filtered-contacts', [CampaignLeadController::class, 'getFilteredContacts'])->name('campaign-leads.filtered-contacts');
        Route::get('campaign-leads/{campaignLead}', [CampaignLeadController::class, 'show'])->name('campaign-leads.show');
    });

    Route::middleware('permission:campaign-edit')->group(function () {
        Route::get('campaign-leads/{campaignLead}/edit', [CampaignLeadController::class, 'edit'])->name('campaign-leads.edit');
        Route::put('campaign-leads/{campaignLead}', [CampaignLeadController::class, 'update'])->name('campaign-leads.update');
        Route::post('campaign-leads/{campaignLead}/blacklist-flag', [CampaignLeadController::class, 'updateBlacklistFlag'])->name('campaign-leads.blacklist_flag');
    });

    Route::middleware('permission:campaign-delete')->group(function () {
        Route::delete('campaign-leads/{campaignLead}', [CampaignLeadController::class, 'destroy'])->name('campaign-leads.destroy');
        Route::post('campaign-leads/bulk-destroy', [CampaignLeadController::class, 'bulkDestroy'])->name('campaign-leads.bulk-destroy');
        Route::post('campaign-leads/delete-all', [CampaignLeadController::class, 'deleteAll'])->name('campaign-leads.delete-all');
    });
    
    Route::middleware('permission:campaign-send')->group(function () {
        Route::post('campaign-leads/send-campaign', [CampaignLeadController::class, 'sendCampaign'])->name('campaign-leads.send-campaign');
        Route::post('campaign-leads/send-email', [CampaignLeadController::class, 'sendEmailCampaign'])->name('campaign-leads.send_email_campaign');
    });

    // Vendor Leads
    Route::middleware('permission:vendor-section')->group(function () {
        Route::get('vendor-leads/kyc', [VendorLeadController::class, 'kyc'])->name('vendor_leads.kyc');
        Route::get('vendor-leads/po', [VendorLeadController::class, 'po'])->name('vendor_leads.po');
        Route::post('vendor-leads/{id}/send-kyc-agreement', [VendorLeadController::class, 'sendKycAgreement'])->name('vendor_leads.send_kyc_agreement');
        Route::get('vendor-leads/all-contacts', [VendorLeadController::class, 'getAllContacts'])->name('vendor_leads.all_contacts');
        Route::get('vendor-leads/filtered-contacts', [VendorLeadController::class, 'getFilteredContacts'])->name('vendor_leads.filtered_contacts');
        Route::post('vendor-leads/send-email', [VendorLeadController::class, 'sendEmailCampaign'])->name('vendor_leads.send_email_campaign');
    });

    // Invoices with permission checks
    Route::middleware('permission:invoice-export')->get('invoices-export', [InvoiceController::class, 'export'])->name('invoices.export');
    Route::middleware('permission:invoice-export')->get('invoices-zip', [InvoiceController::class, 'downloadZip'])->name('invoices.download_zip');

    Route::middleware('permission:invoice-section')->group(function () {
        Route::resource('invoices', InvoiceController::class);
        Route::post('invoices/{invoice}/mark-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.mark_paid');
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
    });

    Route::middleware('permission:email-section')->post('invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])->name('invoices.send_email');

    // OR Invoices with permission checks
    Route::middleware('permission:invoice-or-export')->get('or-invoices-export', [OrInvoiceController::class, 'export'])->name('or-invoices.export');
    Route::middleware('permission:invoice-or-export')->get('or-invoices-zip', [OrInvoiceController::class, 'downloadZip'])->name('or-invoices.download_zip');

    Route::middleware('permission:invoice-or-section')->group(function () {
        Route::resource('or-invoices', OrInvoiceController::class)->parameters([
            'or-invoices' => 'invoice'
        ]);
        Route::post('or-invoices/{invoice}/mark-paid', [OrInvoiceController::class, 'markAsPaid'])->name('or-invoices.mark_paid');
        Route::post('or-invoices/{invoice}/cancel', [OrInvoiceController::class, 'cancel'])->name('or-invoices.cancel');
    });

    Route::middleware('permission:email-section')->post('or-invoices/{invoice}/send-email', [OrInvoiceController::class, 'sendEmail'])->name('or-invoices.send_email');

    // Email Templates
    Route::middleware('permission:email-template-delete')->post('email-templates/bulk-destroy', [EmailTemplateController::class, 'bulkDestroy'])->name('email-templates.bulk-destroy');
    Route::resource('email-templates', EmailTemplateController::class)->except(['show']);
    Route::post('leads/send-email', [LeadController::class, 'sendEmailCampaign'])->name('leads.send_email_campaign');

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/fetch', [\App\Http\Controllers\NotificationController::class, 'fetch'])->name('notifications.fetch');
    Route::post('/notifications/{id}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');

    // Messaging
    Route::prefix('messaging')->name('messaging.')->group(function () {
        Route::get('whatsapp/templates', [MessagingController::class, 'getWhatsappTemplates'])->name('whatsapp.templates');
        Route::post('whatsapp/send', [MessagingController::class, 'sendWhatsappTemplate'])->name('whatsapp.send');
        Route::get('rcs/template', [MessagingController::class, 'getRcsTemplate'])->name('rcs.template');
        Route::get('rcs/templates', [MessagingController::class, 'getRcsTemplates'])->name('rcs.templates');
        Route::post('rcs/send', [MessagingController::class, 'sendRcsMessage'])->name('rcs.send');
        Route::get('sms/templates', [MessagingController::class, 'getSmsTemplates'])->name('sms.templates');
        Route::post('sms/send', [MessagingController::class, 'sendSmsMessage'])->name('sms.send');
        Route::get('all-contacts', [MessagingController::class, 'getAllContacts'])->name('all_contacts');
        Route::get('filtered-contacts', [MessagingController::class, 'getFilteredContacts'])->name('filtered_contacts');
    });

    // Manage P.O
    Route::prefix('manage-po')->name('manage_po.')->group(function () {
        Route::middleware('permission:client-po-access')->group(function () {
            Route::get('client-po', [\App\Http\Controllers\ManagePoController::class, 'createClientPo'])->name('client_po.create');
            Route::post('client-po', [\App\Http\Controllers\ManagePoController::class, 'storeClientPo'])->name('client_po.store');
            Route::get('client-po/fetch-customer', [\App\Http\Controllers\ManagePoController::class, 'fetchCustomer'])->name('client_po.fetch_customer');
        });

        Route::middleware('permission:vendor-po-access')->group(function () {
            Route::get('vendor-po', [\App\Http\Controllers\ManagePoController::class, 'vendorPo'])->name('vendor_po');
            Route::post('vendor-po', [\App\Http\Controllers\ManagePoController::class, 'storeVendorPo'])->name('vendor_po.store');
        });
    });

    // Admin only routes
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role_update');
        Route::put('/users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password_update');

        Route::resource('roles', RoleController::class);

        Route::get('/office-timings', [\App\Http\Controllers\OfficeTimingController::class, 'index'])->name('office_timings.index');
        Route::post('/office-timings', [\App\Http\Controllers\OfficeTimingController::class, 'update'])->name('office_timings.update');
    });
});
