<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    public const PRODUCT_INTEREST_OPTIONS = ['Data', 'SMS', 'RCS', 'Whatsapp'];
    
    public const SOURCE_GROUPS = [
        'CLIENT_KYC'   => ['CLIENT KYC', 'CLIENT MSA', 'CLIENT REGISTRATION'],
        'CLIENT_PO'    => ['CLIENT P.O'],
        'VENDOR_KYC'   => ['VENDOR KYC', 'VENDOR KYC API', 'VENDOR REGISTRATION'],
        'VENDOR_PO'    => ['VENDOR P.O (ADMIN)', 'VENDOR PO API'],
        'CLIENT_TERMS' => ['CLIENT TERMS'],
        'CRM'          => ['CRM'],
    ];

    public function isClient()
    {
        $clientGroups = ['CLIENT_KYC', 'CLIENT_PO', 'CLIENT_TERMS', 'CRM'];
        foreach ($clientGroups as $group) {
            if (in_array($this->creation_source, self::SOURCE_GROUPS[$group] ?? [])) {
                return true;
            }
        }
        return false;
    }

    public function isVendor()
    {
        $vendorGroups = ['VENDOR_KYC', 'VENDOR_PO'];
        foreach ($vendorGroups as $group) {
            if (in_array($this->creation_source, self::SOURCE_GROUPS[$group] ?? [])) {
                return true;
            }
        }
        return false;
    }

    public static function getSourceGroup($source) {
        foreach (self::SOURCE_GROUPS as $group => $sources) {
            if (in_array($source, $sources)) {
                return $sources;
            }
        }
        return [$source]; // Fallback to exact match if not in groups
    }

    protected $fillable = [
        'customer_id',
        'service_type',
        'sequence_number',
        'financial_year',
        'record_id',
        'kyc',
        'master_service_agreement_signed',
        'msa_document',
        'customer_type',
        'lead_status',
        'blacklist_flag',
        'creation_source',
        'customer_name',
        'contact_person',
        'is_agreement_sent',
        'reference',
        'mobile',
        'alternate_mobile',
        'email_id',
        'alternate_email_id',
        'designation',
        'city',
        'nature_of_industry',
        'company_type',
        'company_name',
        'company_address',
        'gst_no',
        'pan_number',
        'aadhar_no',
        'udyam_registration_certificate',
        'website',
        'initial_product_interest',
        'product_demand',
        'quantity',
        'rate',
        'previous_deals_and_date',
        'follow_up_date',
        'records_owner',
        'comment',
        'admin_comment',
        'admin_rate',
        'doc_pan',
        'doc_aadhar',
        'doc_gst',
        'doc_certificate_incorporation_udyam',
        'doc_trai_dlt',
        'doc_dsa_license',
        'doc_company_id_card',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function documentTrackings()
    {
        return $this->hasMany(LeadDocumentTracking::class);
    }

    public function setInitialProductInterestAttribute($value)
    {
        $this->attributes['initial_product_interest'] = is_array($value) ? implode(', ', $value) : $value;
    }

    public function getRecordIdAttribute()
    {
        // Return the sequence number formatted as 001. 
        // This resets every Financial Year (April 1st).
        $sequence = $this->sequence_number ?? 0;
        return str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public static function getFinancialYear($date = null)
    {
        $carbonDate = $date ? \Carbon\Carbon::parse($date) : now();
        $year = $carbonDate->year;
        $month = $carbonDate->month;

        if ($month >= 4) {
            return substr($year, 2) . '-' . substr($year + 1, 2);
        } else {
            return substr($year - 1, 2) . '-' . substr($year, 2);
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($lead) {
            // Set default creation source for manual entries if not already set
            if (empty($lead->creation_source)) {
                $lead->creation_source = 'CRM';
            }

            $date = $lead->created_at ?? now();
            $fy = self::getFinancialYear($date);

            $latestLead = static::where('financial_year', $fy)
                ->orderBy('sequence_number', 'desc')
                ->first();

            $nextSequence = $latestLead ? $latestLead->sequence_number + 1 : 1;

            $lead->financial_year = $fy;
            $lead->sequence_number = $nextSequence;
            $lead->record_id = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
        });
        static::deleting(function ($lead) {
            $email = $lead->email_id;
            $mobile = $lead->mobile;
            $linkedCustomerId = $lead->customer_id;

            // Rule 2 & 3: Check matching customers (including the linked one and any duplicates)
            $customers = Customer::where(function($q) use ($email, $mobile, $linkedCustomerId) {
                if ($email) $q->where('email_id', $email);
                if ($mobile) $q->orWhere('mobile_no', $mobile);
                if ($linkedCustomerId) $q->orWhere('id', $linkedCustomerId);
            })->get();
            
            foreach ($customers as $customer) {
                // Rule 2: If customer has OTHER leads, we do NOT delete the customer
                // Rule 3: If customer has NO other leads, we delete the customer
                $hasOtherLeads = static::where('customer_id', $customer->id)
                    ->where('id', '!=', $lead->id)
                    ->exists();

                if (!$hasOtherLeads) {
                    $customer->delete();
                }
            }
        });
    }
}
