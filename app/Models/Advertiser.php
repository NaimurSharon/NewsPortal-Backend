<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertiser extends Model
{
    protected $fillable = ['name', 'email', 'phone', 'website', 'contact_person', 'status'];

    public function campaigns()
    {
        return $this->hasMany(AdCampaign::class);
    }
}
