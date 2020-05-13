<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model 
{

    protected $table = 'settings';
    public $timestamps = true;
    protected $fillable = array('text_notification', 'about_app', 'phone_app', 'email_app', 'fb_link', 'tw_link', 'you_app', 'inst_link');

}