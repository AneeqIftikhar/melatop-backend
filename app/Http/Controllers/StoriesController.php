<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\Stories;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class StoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $stories = Stories::all();
        $values=array();
        $category=array();
        foreach ($stories as $story) {
            
            array_push($values,$story->category);
        }
        $values = array_unique($values);
        foreach ($values as $value) {
            
            array_push($category,$value);
        }
        return response()->success(['stories'=>$stories,'category'=>$category],'Story Fetched Successfully');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user=Auth::user();
        if($user->role=='admin')
        {
            $validator = Validator::make($request->all(),  [
                'category' => 'required|max:30',
                'link' => 'required|max:100',
                'image' => 'required|max:50',
                'title' => 'required|max:100',
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }
            $input = $request->all();
            $stories = Stories::create($input);
            return response()->success($stories,'Story Created Successfully');
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
        //
    }
}
