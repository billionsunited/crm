<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lead;

class DashboardController extends Controller
{
    public function index()
    {
        $totalLeads = Lead::count();
        $activeLeads = Lead::where('lead_status', 'Active')->count();
        $inactiveLeads = Lead::where('lead_status', 'Non Active')->count();

        // Specific Source Counts
        $crmLeads = Lead::where('creation_source', 'CRM')->count();
        $clientPoLeads = Lead::where('creation_source', 'CLIENT P.O')->count();
        $clientKycLeads = Lead::whereIn('creation_source', ['CLIENT KYC', 'CLIENT MSA', 'CLIENT TERMS', 'CLIENT REGISTRATION'])->count();
        $vendorPoLeads = Lead::whereIn('creation_source', ['VENDOR PO API', 'VENDOR P.O (ADMIN)'])->count();
        $vendorKycLeads = Lead::whereIn('creation_source', ['VENDOR KYC API', 'VENDOR KYC', 'VENDOR REGISTRATION'])->count();
        $campaignLeadsCount = \App\Models\CampaignLead::count();

        $upcomingFollowUps = Lead::where('lead_status', 'Active')
            ->whereNotNull('follow_up_date')
            ->where('follow_up_date', '>=', now()->toDateString())
            ->orderBy('follow_up_date', 'asc')
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalLeads',
            'activeLeads',
            'inactiveLeads',
            'crmLeads',
            'clientPoLeads',
            'clientKycLeads',
            'vendorPoLeads',
            'vendorKycLeads',
            'campaignLeadsCount',
            'upcomingFollowUps'
        ));
    }
}
