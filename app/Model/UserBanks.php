<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $bank_id
 * @property int $user_id
 * @property string $first_name
 * @property string $last_name
 * @property string $account
 * @property string $created_at
 * @property string $updated_at
 * @property Bank $bank
 * @property User $user
 */
class UserBanks extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['bank_id', 'user_id', 'first_name', 'last_name', 'account', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bank()
    {
        return $this->belongsTo('Melatop\Model\Bank');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Melatop\User');
    }
}
