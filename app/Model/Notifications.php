<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class Notifications extends Model
{
   
	protected $appends = ['time_passed'];
    protected $fillable = ['user_id', 'title', 'description','type','status', 'created_at', 'updated_at'];


    


    public function getTimePassedAttribute()
    {
       if($this->created_at)
       {
           return Carbon::now()->diffForHumans($this->created_at);
       }
       return null;
    }
}
