<?php

namespace Melatop\Http\Controllers;

use Illuminate\Http\Request;
use Melatop\Model\Payments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class PaymentsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=Auth::user();
        $payments=$user->payments();
        $result=[];
        if($payments)
        {
            
            
            $last_paid=$user->payments()->where('status','paid')->first();
            if($last_paid)
            {
                $result['last_paid']=$last_paid->amount;
            }
            else
            {
                 $result['last_paid']=0;
            }
            $pending=$user->payments()->where('status','pending')->get();
            $pending_amount=0;
            foreach ($pending as $pending_payments) {
                $pending_amount=$pending_amount+$pending_payments->amount;
            }
            $result['pending_amount']=$pending_amount;
            $result['history']=$user->payments()->with('bank')->get();
            return response()->success($result,'Payments Fetched Successfully');

        }
        // $result['last_paid']=0;
        // $result['pending_amount']=0;
        // $result['payments']=[];
        // return response()->success($result,'Payments Fetched Successfully');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

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
             $payments=Payments::cron_job_payments();
            return response()->success($payments
           ,'Payments Created Successfully');
        }
        else
        {
            return response()->fail('Not Allowed');
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
