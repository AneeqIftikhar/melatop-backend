<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Melatop\Model\SavedLinks;
use Melatop\Model\Visits;
use Carbon\Carbon;
/**
 * @property int $id
 * @property string $category
 * @property string $link
 * @property string $image
 * @property string $title
 * @property string $created_at
 * @property string $updated_at
 */
class Stories extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['category', 'link', 'image', 'title', 'created_at', 'updated_at'];

    protected $guarded = ['id','created_by'];
    protected $appends = ['user_link','is_saved','total_shared','total_visits','time_ago'];
    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        // static::creating(function($table)  {
        //     $table->created_by = Auth::user()->id;
            
        // });

	}

    public function getUserLinkAttribute()
    {
        $user=Auth::user();
        $base = config('app.url');
        return $base.'api/visiting_story_secure/'.$user->id.'/'.$this->id;

    }
    public function getIsSavedAttribute()
    {
        $user=Auth::user();
        $user_story=$user->savedLinks()->where('stories_id',$this->id)->first();
        if($user_story)
        {
            return true;
        }
        return false;

    }
    public function getTotalSharedAttribute()
    {
        $user=Auth::user();
        if($user->role=='admin')
        {
            return SavedLinks::where('stories_id',$this->id)->count();
        }
        else
        {
            return -1;
        }
        

    }
    public function getTotalVisitsAttribute()
    {
        $user=Auth::user();
        if($user->role=='admin')
        {
            return Visits::where('stories_id',$this->id)->count();
        }
        else
        {
            return -1;
        }
        

    }
    public function getTimeAgoAttribute()
    {
        if($this->created_at)
        {
           return Carbon::parse($this->created_at)->diffForHumans();
        }
        return null;
    }
}
