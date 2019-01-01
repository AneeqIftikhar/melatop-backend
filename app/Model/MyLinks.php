<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
/**
 * @property int $id
 * @property int $user_id
 * @property int $stories_id
 * @property int $views_count
 * @property string $created_at
 * @property string $updated_at
 * @property Story $story
 * @property User $user
 */
class MyLinks extends Model
{
    /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'links';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'stories_id', 'views_count', 'created_at', 'updated_at'];
    protected $appends = ['time_ago'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function story()
    {
        return $this->belongsTo('Melatop\Model\Stories', 'stories_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Melatop\User');
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
