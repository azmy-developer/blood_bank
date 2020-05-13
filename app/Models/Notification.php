<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model 
{

    protected $table = 'notifications';
    public $timestamps = true;
    protected $fillable = array('title', 'content', 'donation_request_id');

    public function donationRequests()
    {
        return $this->belongsTo('DonationRequest');
    }

    public function clients()
    {
        return $this->belongsTo('App\Models\Client');
    }

}