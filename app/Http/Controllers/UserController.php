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
            return response()->success($user,'User Updated Successfully');
        }
        else {
            return response()->fail("User Update Failed");
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

    public function dashboard(Request $request)
    {
        $today = Carbon::today();
        $yesterday=Carbon::yesterday();
        $Month = $today->month;
        $Year = $today->year;
        $PreviousMonthYear = $Year;
        $PreviousMonth= $Month-1;
        if($LastMonth==0)
        {
            $LastMonth=12;
            $LastMonthYear=$LastMonthYear-1;
        }
        $user=Auth::user();
        if($user->role=='admin')
        {
            $today_visits=Visits::whereDate('created_at',Carbon::today()->toDateString())->count();
            $yesterday_visits=Visits::whereDate('created_at',Carbon::today()->toDateString())->count();

            $month_visits=Visits::whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();
            $prvious_month_visits=Visits::whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $result=[];
            $result['today_visits']=$today_visits;
            $result['yesterday_visits']=$yesterday_visits;
            $result['month_visits']=$month_visits;
            $result['prvious_month_visits']=$prvious_month_visits;


            $today_links=MyLinks::whereDate('created_at',Carbon::today()->toDateString())->count();
            $yesterday_links=MyLinks::whereDate('created_at',Carbon::today()->toDateString())->count();

            $month_links=MyLinks::whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();
            $previous_month_links=MyLinks::whereYear('created_at',$PreviousMonthYear)->whereMonth('created_at',$PreviousMonth)->count();

            $result['today_links']=$today_links;
            $result['yesterday_links']=$yesterday_links;
            $result['month_links']=$month_links;
            $result['previous_month_links']=$previous_month_links;

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
            $today_visits=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->count();
            $yesterday_visits=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->count();

            $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

            $result=[];
            $result['today_earning']=$today_visits*$user_rate;
            $result['yesterday_earning']=$yesterday_visits*$user_rate;
            $result['month_earning']=$month_visits*$user_rate;



            $pending=$user->payments()->where('status','!=','paid')->get();
            $total_balance=0;
            foreach ($pending as $pending_payments) {
                $total_balance=$pending_amount+$pending_payments->amount;
            }

            $result['balance']=$total_balance;

            $desktop=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('platform','desktop')->count();
            $mobile=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('platform','mobile')->count();
            $tablet=$user->visits()->whereDate('created_at',Carbon::today()->toDateString())->where('platform','tablet')->count();

            $result['today_desktop']=$desktop;
            $result['today_mobile']=$mobile;
            $result['today_tablet']=$tablet;
            $result['today_total']=$tablet+$mobile+$desktop;



            return response()->success($result,'Dashboard Fetched Successfully');
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
            

            $month_visits=$user->visits()->whereDate('created_at',$today)->sum('rate');
            $month_clicks=$user->visits()->whereDate('created_at',$today)->count();

            $links=$user->mylinks()->whereDate('created_at',$today)->count();

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
            $month_visits=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->sum('rate');
            $month_clicks=$user->visits()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();

             $links=$user->mylinks()->whereYear('created_at',$Year)->whereMonth('created_at',$Month)->count();
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
}
