<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

class Notifications extends Model
{
   

    protected $fillable = ['user_id', 'title', 'description','type','status', 'created_at', 'updated_at'];
}
