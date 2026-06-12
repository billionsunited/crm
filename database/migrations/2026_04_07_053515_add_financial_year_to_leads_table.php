<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Lead;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->integer('sequence_number')->nullable()->after('id');
            $table->string('financial_year')->nullable()->after('sequence_number');
        });

        // Backfill existing leads
        $leads = DB::table('leads')->orderBy('id', 'asc')->get();
        
        $sequences = [];
        
        foreach ($leads as $lead) {
            $date = $lead->created_at ? Carbon::parse($lead->created_at) : Carbon::now();
            $fy = $this->getFinancialYear($date);
            
            if (!isset($sequences[$fy])) {
                $sequences[$fy] = 1;
            } else {
                $sequences[$fy]++;
            }
            
            DB::table('leads')->where('id', $lead->id)->update([
                'sequence_number' => $sequences[$fy],
                'financial_year' => $fy
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn(['sequence_number', 'financial_year']);
        });
    }

    private function getFinancialYear($date)
    {
        $year = $date->year;
        $month = $date->month;

        if ($month >= 4) {
            return substr($year, 2) . '-' . substr($year + 1, 2);
        } else {
            return substr($year - 1, 2) . '-' . substr($year, 2);
        }
    }
};
