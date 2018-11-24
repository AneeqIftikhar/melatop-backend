<?php

namespace Melatop;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property int $bank_id
 * @property int $amount
 * @property string $date
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 * @property Bank $bank
 * @property User $user
 */
class Payments extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['user_id', 'bank_id', 'amount', 'date', 'status', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bank()
    {
        return $this->belongsTo('Melatop\Bank');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Melatop\User');
    }
}
