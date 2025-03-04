<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Melatop\Model\Payments;
use Melatop\Model\Settings;
class AdminController extends Controller
{
    /*
    	User Management
    	Only Admin Allowed
    */
    public function get_all_users(Request $request)
    {
        $user=Auth::user();
        if($user->role=='admin')
        {
            $user=User::all();
            return response()->success($user,'User Fetched Successfully');
        }
        else
        {
            return response()->fail("Not Allowed");
        }
    }    
    public function change_user_status(Request $request)
    {
    	$user=Auth::user();
        if($user->role=='admin')
        {
        	$validator = Validator::make($request->all(),  [
                'status' => 'required|max:30',
                'user_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }
            $input=$request->all();
            $user=User::where('id',$input['user_id'])->first();
            if($user)
            {
            	$user->update(['status'=>$input['status']]);
            }
            else
            {
            	return response()->fail("User Not Found");
            }
            return response()->success([],'User Updated Successfully');
        }
        else
        {
        	return response()->fail("Not Allowed");
        }
    }

    /*
    	Payment Management
    */
    public function get_all_payments(Request $request)
    {
        $user=Auth::user();
        if($user->role=='admin')
        {
            $payments=Payments::with('user','bank')->get();

            return response()->success($payments,'Payments Fetched Successfully');
        }
        else
        {
            return response()->fail("Not Allowed");
        }
    }   
    public function add_payment(Request $request)
    {
    	$user=Auth::user();
        if($user->role=='admin')
        {
        	$validator = Validator::make($request->all(),  [
                'status' => 'required|max:30',
                'user_id' => 'required',
                'bank_id' => 'required',
                'amount' => 'required',
                'date' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }
            $input=$request->all();
            $user=User::where('id',$input['user_id'])->first();
            if($user)
            {
            	$user->payments()->create($input);
            }
            else
            {
            	return response()->fail("User Not Found");
            }
             return response()->success($user->payments,'Payment Entered Successfully');
        }
        else
        {
        	return response()->fail("Not Allowed");
        }
    }
    public function change_payment_status(Request $request)
    {
    	$user=Auth::user();
        if($user->role=='admin')
        {
        	$validator = Validator::make($request->all(),  [
                'status' => 'required|max:30',
                'payment_id' => 'required'
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }
            $input=$request->all();
            $payment=Payments::where('id',$input['payment_id'])->first();
            if($payment)
            {
            	$payment->update(['status'=>$input['status']]);
            }
            else
            {
            	return response()->fail("Payment Not Found");
            }
             return response()->success($user->payments,'Payment Updated Successfully');
        }
        else
        {
        	return response()->fail("Not Allowed");
        }
    }

    public function settings(Request $request)
    {
         $user=Auth::user();
        if($user->role=='admin')
        {
            if ($request->isMethod('post')) 
            {
                $validator = Validator::make($request->all(),  [
                'beginner_rate' => 'required',
                'intermediate_rate' => 'required',
                'expert_rate' => 'required',
                'beginner_threshold' => 'required',
                'intermediate_threshold' => 'required',
                'expert_threshold' => 'required',
                'min_payment' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->fail($validator->errors());
                }
                $input=$request->all();
                $settings=Settings::first();
                if($settings)
                {
                    $settings=$settings->update($input);
                }
                else
                {
                    return response()->fail("Settings Not Found");
                }
                 return response()->success($settings,'Settings Updated Successfully');
            }
            else
            {
                $settings=Settings::first();
                return response()->success($settings,'Settings Updated Successfully');
            }
        }
        else
        {
            return response()->fail("Not Allowed");
        }
       
            
        
    }
}
