<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Melatop\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Melatop\Client;
class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
            'email' => 'required|email|max:100',
            'password' => 'required|max:100',
            'status' => 'required|max:100',
            'role' => 'required|max:100',
            'city' => 'required|max:100',
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
            $user['token']=$response_token->access_token;
            $user['refresh_token']=$response_token->refresh_token;
            $user['expire_time']=$response_token->expires_in;

            return response()->success($user,'Logged In SuccessFully');
        }
        else
        {
            return response()->fail('Incorrect Email Or Password');
        }


    }
}
