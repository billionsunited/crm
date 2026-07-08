<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignLead extends Model
{
    public const LEAD_TYPE_OPTIONS = [
        'Qualify',
        'Only PAN & Aadhar',
        'Didn’t sign MSA',
        'No company',
        'Unqualified'
    ];

    public const FIRM_TYPE_OPTIONS = [
        'Proprietorship',
        'Private Limited',
        'Partnership Firm',
        'Company',
        'Individual Consultant'
    ];

    protected $fillable = [
        'customer_name',
        'mobile',
        'mobile_1',
        'mobile_2',
        'email_id',
        'email_id_1',
        'company_name',
        'type_of_firm',
        'place',
        'address',
        'product_interested',
        'comment',
        'rate',
        'source',
        'blacklist_flag',
        'reference'
    ];
}
