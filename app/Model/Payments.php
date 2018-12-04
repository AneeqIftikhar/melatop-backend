<?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Melatop\User;
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
        return $this->belongsTo('Melatop\Model\Banks');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('Melatop\User');
    }

    public static function cron_job_payments()
    {

        $today = Carbon::today();
        $Month = $today->month;
        $Year = $today->year;
        if($Month==1)
        {
            $Month=12;
            $Year=$Year-1;
        }
        else
        {
            //$Month =$Month -1;

        }

         

        $users=User::where('role','!=','admin')->with(['userbanks','visits'=> function ($query) use ($Year,$Month) {
         $query->whereYear('visits.created_at',$Year)
         ->whereMonth('visits.created_at',$Month);          
        }])->get();
           
        $payments=[];
        foreach ($users as $key => $user) {

            if(count($user['visits'])==0)
            {
                $amount=0;
            }
            else
            {
               $amount=$user['visits']->sum('rate');
            }
            if(count($user['userbanks'])==1)
            {
                $bank=$user['userbanks'][0]->id;
                array_push($payments,['user_id'=>$user->id, 'bank_id'=>$bank, 'amount'=> $amount, 'date'=>$today, 'status'=>'pending', 'created_at'=>$today]);
            }
            else
            {
                array_push($payments,['user_id'=>$user->id, 'amount'=> $amount, 'date'=>$today, 'status'=>'pending', 'created_at'=>$today]);
            }
            
        }
        Payments::insert($payments);
        return 1;
    }
}
