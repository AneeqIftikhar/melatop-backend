<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\FacebookPages;
class FacebookPagesController extends Controller
{
    public function index()
    {
        $user=Auth::user();
        $fb_pages=$user->facebook_pages();
        return response()->success($fb_pages,'Facebook Pages Fetched Successfully');

        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'name' => 'required|255',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $user=Auth::user();
        $input = $request->all();
        $user->facebook_pages()->create($input);
        return response()->success([],'Facebook Page Saved Successfully');
        
        
        
    }

}
