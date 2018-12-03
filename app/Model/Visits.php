<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

class Visits extends Model
{
     protected $fillable = ['user_id', 'stories_id','rate', 'ip', 'platform','browser','social_media', 'created_at', 'updated_at'];
}
