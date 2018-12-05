<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
    protected $appends = ['user_link'];
    public static function boot() {
        parent::boot();

        // create a event to happen on saving
        static::creating(function($table)  {
            $table->created_by = Auth::user()->id;
        });

	}

    public function getUserLinkAttribute()
    {
        $user=Auth::user();
        $base = config('app.url');
        return $base.'api/visiting_story/'.$user->id.'/'.$this->id;

    }
}
