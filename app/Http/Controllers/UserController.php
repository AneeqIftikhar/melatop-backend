<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Melatop\Client;
use Melatop\Helpers\Helper;
use Melatop\Model\Banks;
use Melatop\Model\Settings;
use Melatop\Model\MyLinks;
use Melatop\Model\Visits;
use Carbon\Carbon;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|max:50',
            'phone' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $user = User::get_user_from_email( request('email'));
        if($user)
        {
            return response()->fail('Email Already Registered');
        }
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $input['status'] = 'registered';
        $input['role'] = 'advertiser';
        $input['level'] = 'beginner';
        $user = User::create($input);
        if($user)
        {
            $client = Client::where('password_client', 1)->first();
            $request->request->add([
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'grant_type' => 'password',
                'username' => request('email')
            ]);
            $tokenRequest = Request::create('/oauth/token', 'POST',$request->all());
            //return $tokenRequest;
            $response_token =  Route::dispatch($tokenRequest);
            $response_token = json_decode($response_token->getContent());
            if(isset($response_token->error))
            {
                return response()->fail($response_token->message);
            }
            $user['token']=$response_token->access_token;
            $user['refresh_token']=$response_token->refresh_token;
            $user['bank']=$user->userbanks()->first();
            return response()->success($user,'User Registered Successfully');
        }

    }

    public function login(Request $request){

        $validator = Validator::make($request->all(),  [
            'email' => 'required|email|max:100',
            'password' => 'required|max:100',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }

        $user = User::get_user_from_email( request('email'));
        if(!$user) {
            return response()->fail('Email Not Found');
        }
        else if($user=User::authenticate_user_with_password(request('email') , request('password')))
        {
            if($user->status=='banned')
            {
                return response()->fail('User is Banned');
            }
            else
            {
                $userTokens=$user->tokens;
                foreach($userTokens as $token) {
                    $token->delete();
                }
                $client = Client::where('password_client', 1)->first();
                $request->request->add([
                    'client_id' => $client->id,
                    'client_secret' => $client->secret,
                    'grant_type' => 'password',
                    'username' => request('email')
                ]);
                $tokenRequest = Request::create('/oauth/token', 'POST', $request->all());

                $response_token =  Route::dispatch($tokenRequest);
                $response_token = json_decode($response_token->getContent());
                if(isset($response_token->error))
                {
                    return response()->fail('Incorrect Email Or Password');
                }
                $user->update(['last_online'=>Carbon::now()]);
                $user['token']=$response_token->access_token;
                $user['refresh_token']=$response_token->refresh_token;
                $user['expire_time']=$response_token->expires_in;
                $user['bank']=$user->userbanks()->first();
                return response()->success($user,'Logged In SuccessFully');
            }
           
        }
        else
        {
            return response()->fail('Incorrect Email Or Password');
        }


    }
    public function social_login(Request $request){

        $validator = Validator::make($request->all(),  [
            'email' => 'required|email|max:100',
            'full_name' => 'required|max:100'
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }

        $user = User::get_user_from_email( request('email'));
        if(!$user) 
        {
            $input = $request->all();
            $name=explode(' ', $input['full_name']);
            if(count($name)>0)
            {
                $input['first_name']= $name[0];
                if(count($name)>1)
                {
                    $input['last_name']= $name[1];
                }
                else
                {
                    $input['last_name']= " ";
                }
            }
            else
            {
                $input['first_name']= " ";
            }
            
            $input['password'] = bcrypt(rand(0,10000));
            $input['phone'] = '000000';
            $input['status'] = 'registered';
            $input['role'] = 'advertiser';
            $input['level'] = 'beginner';
            $user = User::create($input);
            $token = $user->createToken($user->email)->accessToken;
            $user->update(['last_online'=>Carbon::now()]);
            $user['token']=$token;
            $user['bank']=$user->userbanks()->first();
            return response()->success($user,'Logged In SuccessFully');
        }
        else
        {
            if($user->status=='banned')
            {
                return response()->fail('User is Banned');
            }
            else
            {
                $userTokens=$user->tokens;
                foreach($userTokens as $token) {
                    $token->delete();
                }
                $token = $user->createToken($user->email)->accessToken;
                $user->update(['last_online'=>Carbon::now()]);
                $user['token']=$token;
                $user['bank']=$user->userbanks()->first();
                return response()->success($user,'Logged In SuccessFully');
            }
           
        }


    }
    
    public function create_dummy_admin(Request $request)
    {
        
        $user = User::get_user_from_email('admin@melatop.com');
        if(!$user)
        {
            $input = [];
            $input['email'] = 'admin@melatop.com';
            $input['password'] = bcrypt('melatop123');
            $input['status'] = 'registered';
            $input['role'] = 'admin';
            $input['level'] = 'beginner';
            $input['first_name'] = 'admin';
            $input['last_name'] = 'melatop';
            $input['phone'] = 'melatop';
            $input['city'] = 'melatop';
            $user = User::create($input);
        }
        $banks=Banks::all();
        if(count($banks)==0)
        {

            Banks::create(['id'=>'1','name'=>'Commercial Bank of Ethiopia', 'short'=>'CBE']);
            Banks::create(['id'=>'2','name'=>'Dashen Bank', 'short'=>'Dashen']);
            Banks::create(['id'=>'3','name'=>'United Bank of Ethiopia', 'short'=>'UBE']);
            Banks::create(['id'=>'4','name'=>'Awash Bank', 'short'=>'Awash']);
           
        }
        
        $settings=Settings::all();
        if(count($settings)==0)
        {
            Settings::create([]);
        }
        
         return response()->success([],'Dummy Data Added Successfully');

    }



    public function update_user(Request $request)
    {
         $validator = Validator::make($request->all(),  [
            'first_name' => 'max:100',
            'last_name' => 'max:100',
            'email' => 'email|max:100',
            'phone' => 'max:100',
            'city' => 'max:100',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
            'account' => 'max:100',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $input=$request->all();
        $user=Auth::user();
        if($user) {
            if($request->has('image'))
            {
                if($name=Helper::uploadImage($request->image))
                {
                    $input['image']=$name;
                }
            }
           
            $user->update($input);

            $user['bank']=$user->userbanks()->first();
            $user['token']=$request->bearerToken();
            return response()->success($user,'User Updated Successfully');
        }
        else {
            return response()->fail("User Update Failed");
        }       

    }
    public function admin_update_user(Request $request,$user_id)
    {
         $validator = Validator::make($request->all(),  [
            'first_name' => 'max:100',
            'last_name' => 'max:100',
            'email' => 'email|max:100',
            'phone' => 'max:100',
            'city' => 'max:100',
            'image' => 'image|mimes:jpg,png,jpeg|max:2048',
            'account' => 'max:100',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $input=$request->all();
        $admin=Auth::user();
        if($admin->role=="admin")
        {   
            $user=User::where('id',$user_id)->first();
            if($user) {
                if($request->has('image'))
                {
                    if($name=Helper::uploadImage($request->image))
                    {
                        $input['image']=$name;
                    }
                }
               
                $user->update($input);

                $user['bank']=$user->userbanks()->first();
                $user['token']=$request->bearerToken();
                return response()->success($user,'User Updated Successfully');
            }
            else {
                return response()->fail("User Not Found");
            } 
        }
        else
        {
            return response()->fail("Not Allowed");
        }
              

    }
    public function change_password(Request $request)
    {
         $validator = Validator::make($request->all(),  [
            'password' => 'required|max:100',
            'old_password' => 'required|max:100',
            'email' => 'required|email|max:100'
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $input=$request->all();
        if($user=User::authenticate_user_with_password($input['email'] , $input['old_password']))
        {
            $input['password']=bcrypt($input['password']);
            $user->update(['password'=>$input['password']]);
            return response()->success([],'Password Updated Successfully');
        }
        else
        {
            return response()->fail("Old Password Wrong");
        }

    }
    public function monthly_performance(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'user_id' => 'required',
            'month' => 'required',
            
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $admin=Auth::user();
        $input=$request->all();
        if($admin->role=='admin')
        {
            $Month = $input['month'];
            $user= User::where('id',$input['user_id'])->first();
            $today = Carbon::today();
            if($Month<=$today->month)
            {
                $Year=$today->year;
            }
            else
            {
                $Year=($today->year)-1;
            }
           


            $vistors_earned = DB::table('visits')
                 ->select(DB::raw('DATE(created_at) as date'), DB::raw('sum(rate) as earning'), DB::raw('count(*) as visitor'))
                 ->whereYear('created_at','=',$Year)
                 ->whereMonth('created_at','=',$Month)
                 ->where('user_id',$user->id)
                 ->groupBy(DB::raw('DATE(created_at)'))
                 ->get();

            $monthly_shared = DB::table('links')
                 ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as shared'))
                 ->whereYear('created_at','=',$Year)
                 ->whereMonth('created_at','=',$Month)
                 ->where('user_id',$user->id)
                 ->groupBy(DB::raw('DATE(created_at)'))
                 ->get();
            $summary=[];
            $j=0;
            $k=0;
            $date = Carbon::create($Year, $Month, 1, 0, 0, 0);
            
            for($i=0;$i<$date->daysInMonth;$i++)
            {
                $date = Carbon::create($Year, $Month, 1, 0, 0, 0);
                $day=$date->addDays($i)->format('Y-m-d');
                $day_result=[];
                if($j<count($vistors_earned))
                {
                    $val=$vistors_earned[$j];

                    if($val->date === $day)
                    {
                        $day_result['day']=$day;
                        $day_result['visitor_count']=$val->visitor;
                        $day_result['earning']=$val->earning;

                        $j++;
                    }
                    else
                    {
                        $day_result['day']=$day;
                        $day_result['visitor_count']=0;
                        $day_result['earning']=0;
                        

                    }
                }
                else
                {
                    $day_result['day']=$day;
                    $day_result['visitor_count']=0;
                    $day_result['earning']=0;
                    
                }
                if($k<count($monthly_shared))
                {
                    $val=$monthly_shared[$k];

                    if($val->date == $day)
                    {
                        $day_result['shared_count']=$val->shared;
                        $k++;
                    }
                    else
                    {
                        $day_result['shared_count']=0;
                    }
                }
                else
                {
                        $day_result['shared_count']=0;
                    
                }
                array_push($summary,$day_result);
                
                
            }
            $result=$summary;

            

            // $result=['vistors_earned'=>$vistors_earned,'links_shared'=>$monthly_shared];
            return response()->success($result,'Password Updated Successfully');
        }


    }
    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $yesterday=Carbon::yesterday();
        $Month = $today->month;
        $Year = $today->year;
        $PreviousMonthYear = $Year;
        $PreviousMonth= $Month-1;
        $minus_seven_days=Carbon::now()->subDays(6);
        if($PreviousMonth==0)
        {
            $PreviousMonth=12;
            $PreviousMonthYear=$PreviousMonthYear-1;
        }
        $user=Auth::user();
        if($user->role=='admin')
        {
            $today_visits=Visits::whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $yesterday_visits=Visits::whereDate('created_at',Carbon::yesterday()->toDateString())->count();

            $month_visits=Visits::whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $prvious_month_visits=Visits::whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $result=[];
            $result['today_visits']=$today_visits;
            $result['yesterday_visits']=$yesterday_visits;
            $result['month_visits']=$month_visits;
            $result['prvious_month_visits']=$prvious_month_visits;


            $today_links=MyLinks::whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $yesterday_links=MyLinks::whereDate('created_at',Carbon::yesterday()->toDateString())->count();

            $month_links=MyLinks::whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $previous_month_links=MyLinks::whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $result['today_links']=$today_links;
            $result['yesterday_links']=$yesterday_links;
            $result['month_links']=$month_links;
            $result['previous_month_links']=$previous_month_links;


             $user_role = DB::table('users')
                 ->select(DB::raw('role'), DB::raw('count(*) as total'))
                 ->groupBy(DB::raw('role'))
                 ->get();
            

            $result['user_role']=$user_role;

            $level = DB::table('users')
                 ->select(DB::raw('level'), DB::raw('count(*) as total'))
                 ->groupBy(DB::raw('level'))
                 ->get();
            $user_level=[];
            $total_users=0;
            foreach ($level as $key => $value) 
            {
                if($value->level=="beginner")
                {
                     array_push($user_level,['level'=>'beginner','total'=>$value->total]);
                }
                if($value->level=="intermediate")
                {
                    array_push($user_level,['level'=>'intermediate','total'=>$value->total]);
                }
                if($value->level=="expert")
                {
                    array_push($user_level,['level'=>'expert','total'=>$value->total]);
                }
                $total_users=$total_users+$value->total;
            }

            if(count($user_level)==0)
            {
                array_push($user_level,['level'=>'beginner','total'=>0]);
                array_push($user_level,['level'=>'intermediate','total'=>0]);
                array_push($user_level,['level'=>'expert','total'=>0]);
            }
            else
            {
                $beginner=0;
                $intermediate=0;
                $expert=0;
                foreach ($user_level as $key => $value) {
                    if($value['level']=="beginner")
                    {
                        $beginner=1;
                    }
                    if($value['level']=="intermediate")
                    {
                        $intermediate=1;
                    }
                    if($value['level']=="expert")
                    {
                        $expert=1;
                    }
                }
                if($beginner==0)
                {
                    array_push($user_level,['level'=>'beginner','total'=>0]);
                }
                if($intermediate==0)
                {
                    array_push($user_level,['level'=>'intermediate','total'=>0]);
                }
                if($expert==0)
                {
                    array_push($user_level,['level'=>'expert','total'=>0]);
                }
            }    
            
            $result['user_level']=$user_level;
            $result['total_users']=$total_users;

            return response()->success($result,'Admin Dashboard Fetched Successfully');
        }
        else
        {
            $settings=Settings::first();
            $user_rate=0;
            if($user->level=="beginner")
            {
                $user_rate=$settings->beginner_rate;
            }
            else if($user->level=="intermediate")
            {
                $user_rate=$settings->intermediate_rate;
            }
            else
            {
                $user_rate=$settings->expert_rate;
            }
            $today_visits=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $yesterday_visits=$user->visits()->whereDate('created_at',$yesterday->toDateString())->count();

            $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $last_month_visits=$user->visits()->whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $today_earning=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');
            $yesterday_earning=$user->visits()->whereDate('created_at',Carbon::yesterday()->toDateString())->sum('rate');

            $month_earning=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');

            $last_month_earning=$user->visits()->whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->sum('rate');

            $result=[];
            $result['today_visits']=$today_visits;
            $result['yesterday_visits']=$yesterday_visits;
            $result['this_month_visits']=$month_visits;
            $result['last_month_visits']=$last_month_visits;
            $result['today_earning']=$today_earning;
            $result['yesterday_earning']=$yesterday_earning;
            $result['this_month_earning']=$month_earning;
            $result['last_month_earning']=$last_month_earning;



            $pending=$user->payments()->where('status','!=','paid')->get();
            $total_balance=0;
            foreach ($pending as $pending_payments) {
                $total_balance=$total_balance+$pending_payments->amount;
            }

            $result['balance']=$total_balance;

            $desktop=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','desktop')->count();
            $mobile=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','mobile')->count();
            $tablet=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','tablet')->count();
            $other=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','other')->count();

            $result['today_desktop']=$desktop;
            $result['today_mobile']=$mobile;
            $result['today_tablet']=$tablet;
            $result['today_other']=$other;
            $result['today_total']=$tablet+$mobile+$desktop+$other;

            

            $weekly = DB::table('visits')
                 ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                 ->where('created_at','<=',Carbon::now()->subHours(1))
                 ->where('created_at','>=',$minus_seven_days)
                 ->where('user_id',$user->id)
                 ->groupBy(DB::raw('DATE(created_at)'))
                 ->get();
            $weekly_summary=[];
            $j=0;
            for($i=0;$i<7;$i++)
            {
                if($j<count($weekly))
                {
                    $val=$weekly[$j];
                    if($val->date === Carbon::now()->subDays(6-$i)->format('Y-m-d'))
                    {
                        $weekly_summary[$i] = $val->total; 
                        $j++;
                    }
                    else
                    {
                        $weekly_summary[$i] =0;

                    }
                }
                else
                {
                    $weekly_summary[$i] =0;

                }
                
                
            }
            $result['weekly_summary']=$weekly_summary;
            //Carbon::createFromFormat('Y-m-d', '1975-05-21')->toDateTimeString();
           

            return response()->success($result,'Dashboard Fetched Successfully');
        }
        
    }

    public function get_user_dashboard($user_id)
    {
        $today = Carbon::today();
        $yesterday=Carbon::yesterday();
        $Month = $today->month;
        $Year = $today->year;
        $PreviousMonthYear = $Year;
        $PreviousMonth= $Month-1;
        $minus_seven_days=Carbon::now()->subDays(6);
        if($PreviousMonth==0)
        {
            $PreviousMonth=12;
            $PreviousMonthYear=$PreviousMonthYear-1;
        }
        $admin=Auth::user();
        if($admin->role=='admin')
        {
            $user=User::where('id',$user_id)->first();
            $settings=Settings::first();
            $user_rate=0;
            if($user->level=="beginner")
            {
                $user_rate=$settings->beginner_rate;
            }
            else if($user->level=="intermediate")
            {
                $user_rate=$settings->intermediate_rate;
            }
            else
            {
                $user_rate=$settings->expert_rate;
            }
            $today_visits=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $yesterday_visits=$user->visits()->whereDate('created_at',$yesterday->toDateString())->count();

            $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            $last_month_visits=$user->visits()->whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $today_earning=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->sum('rate');
            $yesterday_earning=$user->visits()->whereDate('created_at',Carbon::yesterday()->toDateString())->sum('rate');

            $month_earning=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->sum('rate');

            $last_month_earning=$user->visits()->whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->sum('rate');

            $result=[];
            $result['today_visits']=$today_visits;
            $result['yesterday_visits']=$yesterday_visits;
            $result['this_month_visits']=$month_visits;
            $result['last_month_visits']=$last_month_visits;
            $result['today_earning']=$today_earning;
            $result['yesterday_earning']=$yesterday_earning;
            $result['this_month_earning']=$month_earning;
            $result['last_month_earning']=$last_month_earning;



            $pending=$user->payments()->where('status','!=','paid')->get();
            $total_balance=0;
            foreach ($pending as $pending_payments) {
                $total_balance=$total_balance+$pending_payments->amount;
            }

            $result['balance']=$total_balance;

            $desktop=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','desktop')->count();
            $mobile=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','mobile')->count();
            $tablet=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','tablet')->count();
            $other=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('created_at', '<=', Carbon::now()->subHours(1))->where('platform','other')->count();

            $result['today_desktop']=$desktop;
            $result['today_mobile']=$mobile;
            $result['today_tablet']=$tablet;
            $result['today_other']=$other;
            $result['today_total']=$tablet+$mobile+$desktop+$other;

            

            $weekly = DB::table('visits')
                 ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
                 ->where('created_at','<=',Carbon::now()->subHours(1))
                 ->where('created_at','>=',$minus_seven_days)
                 ->where('user_id',$user->id)
                 ->groupBy(DB::raw('DATE(created_at)'))
                 ->get();
            $weekly_summary=[];
            $j=0;
            for($i=0;$i<7;$i++)
            {
                if($j<count($weekly))
                {
                    $val=$weekly[$j];
                    if($val->date === Carbon::now()->subDays(6-$i)->format('Y-m-d'))
                    {
                        $weekly_summary[$i] = $val->total; 
                        $j++;
                    }
                    else
                    {
                        $weekly_summary[$i] =0;

                    }
                }
                else
                {
                    $weekly_summary[$i] =0;

                }
                
                
            }
            $result['weekly_summary']=$weekly_summary;
            //Carbon::createFromFormat('Y-m-d', '1975-05-21')->toDateTimeString();
           

            return response()->success($result,'User Dashboard Fetched Successfully');
        }
        else
        {
            return response()->fail('Not Allowed');
        }
        
    }
    public function get_user_dashboard_date(Request $request)
    {
        $input=$request->all();
        $month_visits=0;
        $admin=Auth::user();
        if($admin->role=='admin')
        {
            $user=User::where('id',$input['user_id'])->first();
            if($input['duration']=='today')
            {
                $today = Carbon::today()->toDateString();
                

                $month_visits=$user->visits()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');
                $month_clicks=$user->visits()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->count();

                $links=$user->mylinks()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->count();

            }
            else if($input['duration']=='yesterday')
            {
                $yesterday=Carbon::yesterday()->toDateString();
                $month_visits=$user->visits()->whereDate('created_at',$yesterday)->sum('rate');
                $month_clicks=$user->visits()->whereDate('created_at',$yesterday)->count();
                 $links=$user->mylinks()->whereDate('created_at',$yesterday)->count();
            }
            else if($input['duration']=='this_month')
            {
                $today = Carbon::today();
                $Month = $today->month;
                $Year = $today->year;
                $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');
                $month_clicks=$user->visits()->whereYear('created_at',$Year)->where('created_at', '<=', Carbon::now()->subHours(1))->whereMonth('created_at',$Month)->count();

                 $links=$user->mylinks()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
            }
            else if($input['duration']=='last_month')
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
                    $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->sum('rate');
                }
                $month_clicks=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

                $links=$user->mylinks()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

            }

            $result=[];
            $result['earnings']=$month_visits;
            $result['visits']=$month_clicks;
            $result['links_shared']=$links;

            return response()->success($result,'Dashboard Fetched Successfully');
        }
        else
        {
            return response()->fail('Not Allowed');
        }
        
    }
    public function dashboard_date(Request $request)
    {
        $input=$request->all();
        $month_visits=0;
        $user=Auth::user();
        if($input['duration']=='today')
        {
            $today = Carbon::today()->toDateString();
            

            $month_visits=$user->visits()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');
            $month_clicks=$user->visits()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->count();

            $links=$user->mylinks()->whereDate('created_at',$today)->where('created_at', '<=', Carbon::now()->subHours(1))->count();

        }
        else if($input['duration']=='yesterday')
        {
            $yesterday=Carbon::yesterday()->toDateString();
            $month_visits=$user->visits()->whereDate('created_at',$yesterday)->sum('rate');
            $month_clicks=$user->visits()->whereDate('created_at',$yesterday)->count();
             $links=$user->mylinks()->whereDate('created_at',$yesterday)->count();
        }
        else if($input['duration']=='this_month')
        {
            $today = Carbon::today();
            $Month = $today->month;
            $Year = $today->year;
            $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->sum('rate');
            $month_clicks=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();

             $links=$user->mylinks()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->where('created_at', '<=', Carbon::now()->subHours(1))->count();
        }
        else if($input['duration']=='last_month')
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
                $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->sum('rate');
            }
            $month_clicks=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

            $links=$user->mylinks()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

        }

        $result=[];
        $result['earnings']=$month_visits;
        $result['visits']=$month_clicks;
        $result['links_shared']=$links;


        
        // $result['daily_avg_earning']=($month_visits*$user_rate)/30;
        // $result['week_avg_earning']=($month_visits*$user_rate)/4;


        return response()->success($result,'Dashboard Fetched Successfully');
    }

    public function user($user_id)
    {
        $user=Auth::user();
        if($user->role=="admin" || $user->id==$user_id)
        {
            $result=User::where('id',$user_id)->first();
            $result['bank']=$result->userbanks()->first();
            return response()->success($result,'Dashboard Fetched Successfully');
        }
        return response()->fail('Not Allowed');       
    }
}
