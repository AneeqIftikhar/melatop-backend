<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\MyLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class MyLinksController extends Controller
{




    public function testlink(Request $request)
    {
        // return response()->success($request->server('HTTP_REFERER'),'My Links Fetched Successfully');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=Auth::user();
        $links = MyLinks::with('story')->where('user_id',$user->id)->get();
        return response()->success($links,'My Links Fetched Successfully');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'stories_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $user=Auth::user();
        $input = $request->all();
        $user->mylinks()->create($input);
        return response()->success([],'Link Added Successfully');
    }

    public function add_update_mylinks(Request $request)
    {
        $validator = Validator::make($request->all(),  [
            'stories_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }
        $input = $request->all();
        $link=MyLinks::where('user_id',$input['user_id'])->where('stories_id',$input['stories_id'])->first();
        if($link)
        {
            $views_count=$link->views_count+1;
            $link->update(['views_count' => $views_count]);
        }
        else
        {
            MyLinks::create(['user_id'=>$input['user_id'], 'stories_id'=>$input['stories_id'], 'views_count'=>1]);
        }
        return response()->success([],'Link Added/updated Successfully');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
