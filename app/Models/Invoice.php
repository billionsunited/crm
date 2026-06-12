<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'is_paid' => 'boolean',
        'is_cancelled' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public static function getFinancialYear($date = null)
    {
        if ($date instanceof \Carbon\Carbon) {
            $carbonDate = $date;
        } else {
            $carbonDate = $date ? \Carbon\Carbon::parse($date) : now();
        }
        
        $year = $carbonDate->year;
        $month = $carbonDate->month;

        if ($month >= 4) {
            return substr($year, 2) . '-' . substr($year + 1, 2);
        } else {
            return substr($year - 1, 2) . '-' . substr($year, 2);
        }
    }

    public static function generateSequenceNumber()
    {
        // For backwards compatibility, though we don't use this directly anymore, keep it or modify it.
        return 'PRO-' . now()->format('YmdHis');
    }

    public function assignFinalSequence($isCancelled = false)
    {
        // Must be called within a DB::transaction closure!
        
        $date = $this->invoice_date ?? now();
        $financialYear = self::getFinancialYear($date);
        $isOr = $this->invoice_per_type === 'or';
        
        $latest = self::withTrashed()
                      ->where('financial_year', $financialYear)
                      ->where('invoice_per_type', $this->invoice_per_type ?: 'standard')
                      ->whereNotNull('invoice_sequence')
                      ->lockForUpdate()
                      ->orderBy('invoice_sequence', 'desc')
                      ->first();

        if ($isOr) {
            $startNum = ($financialYear === '26-27') ? 43 : 1;
        } else {
            $startNum = ($financialYear === '26-27') ? 7 : 1;
        }
        
        $sequence = $latest ? $latest->invoice_sequence + 1 : $startNum;
        
        if ($isOr) {
            $finalNumber = 'OR/' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '/' . $financialYear;
        } else {
            $prefix = config('invoice.prefix', 'BU');
            $finalNumber = $prefix . '/' . str_pad($sequence, 3, '0', STR_PAD_LEFT) . '/' . $financialYear;
        }

        $updateData = [
            'invoice_type' => 'final',
            'invoice_sequence' => $sequence,
            'financial_year' => $financialYear,
            'invoice_number' => $finalNumber,
        ];

        if ($isCancelled) {
            $updateData['is_cancelled'] = 1;
            $updateData['is_paid'] = 0;
            $updateData['paid_at'] = null;
        } else {
            $updateData['is_paid'] = 1;
            $updateData['paid_at'] = now();
            $updateData['is_cancelled'] = 0;
        }

        $this->update($updateData);

        return $this;
    }
}
