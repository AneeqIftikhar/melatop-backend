<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\Notifications;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Illuminate\Support\Facades\Auth;
Use \DB;
use Carbon\Carbon;
class NotificationsController extends Controller
{
    public function index()
    {
        $user=Auth::user();
        $notifications=$user->notifications()->get();
        return response()->success($notifications,'Notifications Fetched Successfully');
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $admin=Auth::user();
        if($admin->role=='admin')
        {
            $validator = Validator::make($request->all(),  [
                'title' => 'required|max:100',
                'description' => 'required|max:512',
                'type' => 'required|max:25',
                'send_to' => 'required|max:20000'
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }

            $input=$request->all();


			$idsArr = explode(',',$input['send_to']); 
			$users=[];
			if(count($idsArr)>1)
			{
				$users=DB::table('users')->whereIn('id',$idsArr)->get();
			} 
			else
			{
				if($input['send_to']=='all')
	            {   	
	            	$users=User::all();            	
	            }
	            else if($input['send_to']=='active')
	            {
	            	$users=User::where('status','active')->get();
	            }
	            else if($input['send_to']=='beginner')
	            {
	            	$users=User::where('level','beginner')->get();
	            }
	            else if($input['send_to']=='intermediate')
	            {
	            	$users=User::where('level','intermediate')->get();
	            }
	            else if($input['send_to']=='expert')
	            {
	            	$users=User::where('level','expert')->get();
	            }
			}
			if(count($users)>0)
			{
				$all = array();
				foreach ($users as $user) {
					$row=['title'=>$input['title'],'description'=>$input['description'],'type'=>$input['type'],'user_id'=>$user->id,'status'=>'unread','created_at'=> Carbon::now()];
					array_push($all,$row);
				}

				$notifications=Notifications::insert($all);
				return response()->success($notifications,'Notifications Sent Successfully');
			}
			return response()->fail('No User');
			
			 
            
        }
        else
        {
            return response()->fail("Not Allowed");
        }
    }	

    public function update_notification_status(Request $request)
    {
    	$validator = Validator::make($request->all(),  [
            'notification_id' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }

        $user=Auth::user();
        $input=$request->all();
        $notification=Notifications::where('id',$input['notification_id'])->first();
        if($user->id==$notification->user_id)
        {
         	 $notification->update(['status'=>'read']);
        }
        else
        {
            return response()->fail("Not Allowed");
        }
    }
    
}
