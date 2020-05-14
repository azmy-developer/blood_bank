<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientFavPost extends Model 
{

    protected $table = 'client_fav_post';
    public $timestamps = true;

    protected $hidden = ['pivot'];


}