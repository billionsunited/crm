<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use App\Models\FollowupNotification;
use Carbon\Carbon;

class ProcessFollowupNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process-followups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch today\'s follow-up leads and create notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = Carbon::today()->toDateString();
        
        // Find leads where follow_up_date is today
        $leads = Lead::whereDate('follow_up_date', $today)->get();
        $count = 0;

        foreach ($leads as $lead) {
            // Use updateOrCreate to avoid duplicates if cron runs multiple times
            FollowupNotification::updateOrCreate(
                [
                    'lead_id' => $lead->id,
                    'follow_up_date' => $today,
                ],
                [
                    'user_id' => $lead->records_owner ?? 1,
                    'title' => 'Follow-up Reminder',
                    'message' => 'Follow-up scheduled with ' . ($lead->customer_name ?? 'Customer') . ($lead->company_name ? ' from ' . $lead->company_name : ''),
                    'redirect_url' => 'leads/' . $lead->id,
                    'customer_name' => $lead->customer_name,
                    'company_name' => $lead->company_name,
                    'is_triggered' => true,
                    'is_read' => false,
                ]
            );
            $count++;
        }

        $this->info("Successfully processed {$count} follow-up notifications for today ({$today}).");
    }
}
