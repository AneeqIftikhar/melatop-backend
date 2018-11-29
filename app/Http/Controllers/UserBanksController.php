<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Illuminate\Support\Facades\Auth;
class UserBanksController extends Controller
{
    public function add_update_bank_info(Request $request)
    {
         $validator = Validator::make($request->all(),  [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'account' => 'required|max:100',
            'bank_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }


        $input=$request->all();
        $user=Auth::user();
        $banks=$user->userbanks()->get();
        if(count($banks)==0)
        {
        	$user->userbanks()->create($input);
        }
        else
        {
        	$banks=$user->userbanks()->get();
        	$banks[0]->update($input);
        }
        $user['bank']=$user->userbanks()->first();
       	return response()->success($user,'Banks Information Updated Successfully');

    }
}
