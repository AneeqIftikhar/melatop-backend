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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name','last_name','status','role','city', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function get_user_from_email($email){
        return User::where('email', $email)->first();
    }
    public static function authenticate_user_with_password($email,$password){
        //return response()->success( Auth::attempt(['email' => $email, 'password' => $password]),'Logged In SuccessFully');
        if(Auth::attempt(['email' => $email, 'password' => $password])) {
            $user = Auth::user();
            return $user;
        }
        else{
            return false;
        }

    }
}
