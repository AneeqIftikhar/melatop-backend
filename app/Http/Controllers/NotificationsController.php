<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\Notifications;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Illuminate\Support\Facades\Auth;
Use \DB;
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
                'description' => 'required|max:255',
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
	            	$users=User::where('status','active');
	            }
	            else if($input['send_to']=='beginner')
	            {
	            	$users=User::where('role','beginner');
	            }
	            else if($input['send_to']=='intermediate')
	            {
	            	$users=User::where('role','intermediate');
	            }
	            else if($input['send_to']=='expert')
	            {
	            	$users=User::where('role','expert');
	            }
			}
			if(count($users)>0)
			{
				$all = array();
				foreach ($users as $user) {
					$row=['title'=>$input['title'],'description'=>$input['description'],'type'=>$input['type'],'user_id'=>$user->id,'status'=>'unread'];
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
}
