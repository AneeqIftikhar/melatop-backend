<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

class FacebookPages extends Model
{
    
	protected $fillable = ['user_id', 'access_token', 'category', 'page_id', 'name', 'created_at', 'updated_at'];
    
}
