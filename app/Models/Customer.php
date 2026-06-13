<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'company_name',
        'client_name',
        'place',
        'state_name',
        'state_code',
        'registered_address',
        'mobile_no',
        'email_id',
        'signature_path',
        'ip_address',
    ];

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Find a customer by mobile number within a specific context (Client or Vendor)
     */
    public static function findByMobileAndContext($mobile, $source)
    {
        if (empty($mobile)) return null;

        $contextSources = Lead::getSourceGroup($source);

        return self::where('mobile_no', $mobile)
            ->whereHas('leads', function ($query) use ($contextSources) {
                $query->whereIn('creation_source', $contextSources);
            })
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($customer) {
            if ($customer->signature_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($customer->signature_path);
            }
        });
    }
}
