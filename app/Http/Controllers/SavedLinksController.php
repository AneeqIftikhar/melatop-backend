<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\SavedLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class SavedLinksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=Auth::user();
        $links = SavedLinks::with('story')->where('user_id',$user->id)->get();
        return response()->success($links,'Saved Links Fetched Successfully');
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
        $link=SavedLinks::where('user_id',$user->id)->where('stories_id',$input['stories_id'])->first();
        if($link)
        {
            return response()->fail('Link Already Saved');
        }
        else
        {
            $user->savedLinks()->create($input);
            return response()->success([],'Link Saved Successfully');
        }
        
        
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
        $user=Auth::user();
        $link=SavedLinks::where('user_id',$user->id)->where('stories_id',$id)->first();
        if($link)
        {
            $link->delete();
            return response()->success([],'Link Deleted Successfully');
        }
        else
        {
            return response()->fail('Link Not Found');
        }
    }
}
