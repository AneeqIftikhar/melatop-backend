<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $stories_id
 * @property string $created_at
 * @property string $updated_at
 * @property Story $story
 */
class SavedLinks extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['stories_id', 'created_at', 'updated_at'];
    protected $guarded = ['id','user_id'];
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
}
