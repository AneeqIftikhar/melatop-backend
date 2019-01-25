    <?php

namespace Melatop\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Melatop\User;
use Melatop\Model\Visits;
use Melatop\Model\Settings;
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
            $Month =$Month -1;

        }
        $last_month = Carbon::create($Year, $Month, 1);
         

        $users=User::where('role','!=','admin')->with(
            [
                'userbanks'
                ,'visits'=> function ($query) use ($Year,$Month) {
                             $query->whereYear('visits.created_at',$Year)
                             ->whereMonth('visits.created_at',$Month);          
                            }
                ,'payments'=>function($query) use ($Year,$Month) {
                             $query->whereYear('payments.date',$Year)
                             ->whereMonth('payments.date',$Month);          
                            }

            ]

        )->get();
           
        $payments=[];
        foreach ($users as $key => $user) {

            if(count($user['payments'])==0)
            {
                if(count($user['visits'])==0)
                {
                    $amount=0;
                }
                else
                {
                   $amount=(double)$user['visits']->sum('rate');
                }
                if(count($user['userbanks'])==1)
                {
                    $bank=$user['userbanks'][0]->id;
                    array_push($payments,['user_id'=>$user->id, 'bank_id'=>$bank, 'amount'=> $amount, 'date'=>$today, 'status'=>'pending', 'created_at'=>$today]);
                }
                else
                {
                    array_push($payments,['user_id'=>$user->id, 'amount'=> $amount, 'date'=>$last_month, 'status'=>'pending', 'created_at'=>$today]);
                }
            }
            
            
        }
        Payments::insert($payments);
        return 1;
    }

    public static function cron_job_payments2()
    {

        $users=User::where('role','!=','admin')->with(['userbanks','payments'])->get();
        $today = Carbon::today();
        $today_month = $today->month;
        $today_year = $today->year;
        $today_parsed = Carbon::create($today_year, $today_month, 1);
        $payments=[];
        foreach ($users as $key => $user) {
            if(count($user->payments)>0)
            {
                $date=Carbon::parse($user->payments[count($user->payments)-1]->date);
                $Month = $date->month;
                $Year = $date->year;
                $last_payment_date = Carbon::create($Year, $Month, 1);
                $users[$key]['visits']=$user->visits()->where('visits.created_at','<',$today_parsed)->where('visits.created_at','>=',$last_payment_date)->get();
                //$users[$key]['visits']=$user->visits()->get();
            }
            else
            {
                $users[$key]['visits']=$user->visits()->get();
            }

            if(count($user['visits'])>0)
            {
                $amount=(double)$user['visits']->sum('rate');
                $settings=Settings::first();
                if($amount >= $settings->min_payment)
                {
                    if(count($user['userbanks'])==1)
                    {
                        $bank=$user['userbanks'][0]->id;
                        array_push($payments,['user_id'=>$user->id, 'bank_id'=>$bank, 'amount'=> $amount, 'date'=>$today_parsed, 'status'=>'pending', 'created_at'=>$today]);
                    }
                    else
                    {
                        array_push($payments,['user_id'=>$user->id, 'amount'=> $amount, 'date'=>$today_parsed, 'status'=>'pending', 'created_at'=>$today]);
                    }
                }
                
            }      
            
        }
        Payments::insert($payments);
        return 1;
        

        
       
    }
}
