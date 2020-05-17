<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DonationRequest extends Model 
{

    protected $table = 'donation_requests';
    public $timestamps = true;
    protected $fillable = array('patient_name', 'patient_phone', 'hospital_name', 'city_id', 'blood_type_id', 'patient_age', 'num_bags', 'hospital_address', 'latitude', 'longitude', 'notes', 'client_id');

    public function clients()
    {
        return $this->belongsTo('App\Models\Client','client_id');
    }

    public function notification()
    {
        return $this->hasMany('App\Models\Notification');
    }

    public function cities()
    {
        return $this->belongsTo('App\Models\City','city_id');
    }

    public function bloodType()
    {
        return $this->belongsTo('App\Models\BloodType');
    }

}