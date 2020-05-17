<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'tokens';
    public $timestamps = true;
    protected $fillable = array('token', 'client_id', 'platform');


    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}

