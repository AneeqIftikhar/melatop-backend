<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\Stories;
use Melatop\Model\MyLinks;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;
use Melatop\User;
use Melatop\Model\Visits;
use Melatop\Model\Settings;
use Browser;
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
        return response()->success(['stories'=>$stories,'categories'=>$category],'Story Fetched Successfully');
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
                'link' => 'required|max:255',
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }
            $input = $request->all();
            $tags = get_meta_tags($input['link']);
            $title = "";
            $description = "";
            $img = "";
            $category="";
            if(isset($tags["title"])){
                $title = $tags["title"];
            }

            if(isset($tags["twitter:title"])){
                $title = $tags["twitter:title"];
            }

            if(isset($tags["description"])){
                $description = $tags["description"];
            }

            if(isset($tags["twitter:description"])){
                $description = $tags["twitter:description"];
            }

            if(isset($tags["twitter:image:src"])){
                $img = $tags["twitter:image:src"];
            }else if(isset($tags["twitter:image"])){
                $img = $tags["twitter:image"];
            }

             if(isset($tags["category"])){
                $category = $tags["category"];
            }else{
                $category= "Undefined";
            }

            $input['image']=$img;
            $input['title']=$title;

            $input['category']= $category;
            
            $stories = Stories::create($input);
            return response()->success($stories,'Story Created Successfully');
        }
        else
        {
            return response()->fail("Not Allowed");
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
        if($user->role=='admin')
        {
            $stories = Stories::find($id);

            $stories->delete();
            return response()->success([],'Story Deleted Successfully');
        }
        else
        {
            return response()->fail("Not Allowed");
        }
    }

    public function visiting_story(Request $request,$user_id,$stories_id)
    {
        $settings=Settings::orderBy('created_at', 'desc')->first();
        $user=User::find($user_id);
        if($user)
        {
            $story=Stories::find($stories_id);
            if($story)
            {
                $link=MyLinks::where('user_id',$user_id)->where('stories_id',$stories_id)->first();
                if($link)
                {
                    $views_count=$link->views_count+1;
                    $link->update(['views_count' => $views_count]);
                }
                else
                {
                    MyLinks::create(['user_id'=>$user_id, 'stories_id'=>$stories_id, 'views_count'=>1]);
                }
                $rate=0;
                $platform='other';
                if($user->level=='beginner')
                {
                    $rate=$settings->beginner_rate;
                }
                else if($user->level=='intermediate')
                {
                    $rate=$settings->intermediate_rate;
                }
                else if($user->level=='expert')
                {
                    $rate=$settings->expert_rate;
                }
                if(Browser::isMobile())
                {
                    $platform='mobile';
                }
                else if(Browser::isTablet())
                {
                    $platform='tablet';
                }
                else if(Browser::isDesktop())
                {
                    $platform='desktop';
                }

                Visits::create(['user_id'=>$user_id, 'stories_id'=>$stories_id,'rate'=>$rate,'level'=>$user->level,'ip'=>$request->ip(),'browser'=>Browser::browserName(),'platform'=>$platform]);

                return response()->success([],'Story Deleted Successfully');
                return Redirect::to($story->link);
            }
        }
        
    }
}
