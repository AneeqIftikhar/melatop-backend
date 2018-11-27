<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $short
 * @property string $created_at
 * @property string $updated_at
 * @property Payment[] $payments
 * @property User[] $users
 */
class Banks extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['id','name', 'short', 'created_at', 'updated_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payments()
    {
        return $this->hasMany('Melatop\Model\Payments');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany('Melatop\User');
    }
}
