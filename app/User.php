<?php

namespace Melatop;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
      /**
     * The table associated with the model.
     * 
     * @var string
     */
    protected $table = 'users';

    /**
     * @var array
     */
    protected $fillable = ['country_id', 'state_id', 'bank_id', 'first_name', 'last_name', 'email', 'password', 'phone', 'status', 'role', 'level', 'city', 'image', 'account', 'deleted_at', 'remember_token', 'created_at', 'updated_at'];


        /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];


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
    public function country()
    {
        return $this->belongsTo('Melatop\Model\Country');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function state()
    {
        return $this->belongsTo('Melatop\Model\State');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function links()
    {
        return $this->hasMany('Melatop\Model\Links');
    }

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
    public function savedLinks()
    {
        return $this->hasMany('Melatop\Model\SavedLinks');
    }




    public static function get_user_from_email($email){
        return User::where('email', $email)->first();
    }
    public static function authenticate_user_with_password($email,$password){

        if(Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            return $user;
        }
        else{
            return false;
        }

    }
}
