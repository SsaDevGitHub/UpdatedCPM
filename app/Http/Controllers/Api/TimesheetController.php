<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Client;
use App\Models\AssigmentMap;
use App\Models\AM_Employee;
use App\Models\Employee;
use App\Models\AM_Partner;
use App\Models\Timesheet;
use App\Models\Assignment;
use App\Models\UBTimesheet;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use DB;
use Validator;
use Carbon\Carbon;

class TimesheetController extends Controller
{

    public function addtimesheet(Request $request){

        $user = Auth::user();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' =>'User is not authenticated!',
            ],400);
        }

        $emp = Employee::where('user_id',$user->user_id)->first();
        $emp_id = $emp->id;
        $date_u = Carbon::parse($request->date_u)->format('Y-m-d');

        $am_employee_id  =   json_decode($request->am_employee_id);
        $assignment_map_id  =    json_decode($request->assignment_map_id); 
        $client_id  =    json_decode($request->client_id); 
        $assignment_id  =    json_decode($request->assignment_id); 
        $hours  =    json_decode($request->hours); 
        $remarks  =    json_decode($request->remarks); 

        $tot_hour = 0;
        foreach($hours as $ho => $hour){
            $tot_hour += $hour;
        }

        $hours_ub  =   json_decode($request->hours_ub); 
        foreach($hours_ub as $ho => $hour_ub){
            $tot_hour += $hour_ub;
        }

        if($tot_hour < 8){
            return response()->json(['data'=>'','msg'=>'total hours sum should be 8','employee_id' => $emp_id,'f_date'=>$date_u]);
        }

        foreach($am_employee_id as $key=>$am_employees){

            $querytime = Timesheet::whereDate('date',$date_u)->where('employee_id',$emp_id)->where('am_employee_id',$am_employees)->first();

            if($querytime){
                $timesheet = $querytime;
            }else{
                $timesheet = new Timesheet;
            }

            $timesheet->date  =  $date_u;
            $timesheet->employee_id  =  $emp_id;
    
            $timesheet->assignment_map_id  =  $assignment_map_id[$key];
            $timesheet->am_employee_id  =  $am_employees;
    
            $timesheet->client_id  =  $client_id[$key];
            $timesheet->assignment_id  =  $assignment_id[$key];
    
            $timesheet->hours  =  $hours[$key];
            $timesheet->remarks  =  $remarks[$key]?$remarks[$key]:'';
    
            $timesheet->save();

        }

        $ub_id  =   json_decode($request->ub_id); 
        $hours_ub  =   json_decode($request->hours_ub); 
        $remarks_ub  =  json_decode( $request->remarks_ub); 

        foreach($ub_id as $key_ub=>$ub_ids){

            $querytime = UBTimesheet::where('date',$date_u)->where('employee_id',$emp_id)->where('ub_id',$ub_ids)->first();

            if($querytime){
                $timesheet = $querytime;
            }else{
                $timesheet = new UBTimesheet;
            }

            $timesheet->date  =  $date_u;
            $timesheet->employee_id  =  $emp_id;

            $timesheet->ub_id  =  $ub_ids;
            $timesheet->hours  =  $hours_ub[$key_ub];
            $timesheet->remarks  =  $remarks_ub[$key_ub]?$remarks_ub[$key_ub]:'';
    
            $timesheet->save();
        }

            return response()->json(['data'=>'','msg'=>'Data SAved Successfully']);
        
    }


    public function addtimesheetview(Request $request){
    
        $user = Auth::user();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' =>'User is not authenticated!',
            ],400);
        }

        $emp_id = Employee::where('user_id',$user->user_id)->first();
        $assignment_map_employee = [];
        $selected = "";
        $date_no = $request->f_date?Carbon::parse($request->f_date)->format('Y-m-d'):Carbon::now()->format('Y-m-d');

        $query = AM_Employee::with(['employee_one','assignment_map','assignment_map_partner']);
        if(isset($emp_id)){
            $selected = $emp_id->id;
            $query = $query->where('employee_id',$emp_id->id)->whereDate('from_date', '<=', $date_no)->whereDate('to_date', '>=', $date_no);
        }

        $assignment_map_employee = $query->with(['timesheet' => function ($abcquery) use ($date_no) {
                                                    $abcquery->whereDate('date', $date_no);
                                                }])->get();

        $UBTimesheet = UBTimesheet::where('date',$date_no)->where('employee_id',$selected)->orderBy('ub_id','ASC')->get();
        if(count($UBTimesheet) < 1 ){
            $UBTimesheet = [];
        }

        $data = [
            'msg' => "timesheet",
            'active' => "timesheet",
            'activetxt' => "timesheetadd",
            'assignment_map_employee'        => $assignment_map_employee,
            'date_no'        => $date_no,
            'UBTimesheet'        => $UBTimesheet,
            'selected'        => $selected,
        ];

        return response()->json($data,200);

    }

    public function timesheetlist(Request $request){    

        $user = Auth::user();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' =>'User is not authenticated!',
            ],400);
        }

        $assignment_map_employee = [];
        $selected = "";
        $msg = "there are no assignment mapped to this employee";
        if(isset($request->employee_id)){
            $selected = $request->employee_id;
            $assignment_map_employee = AM_Employee::where('employee_id',$request->employee_id)->with(['employee_one','assignment_map','assignment_map_partner'])->get();
            $msg = "Assignment mapped to this employee fetched successfully";
        }

        $data = array(
            'assignment_map_employee'        => $assignment_map_employee,
            'selected'        => $selected,        
        );
        
        return response()->json([
            'msg'=>$msg,
            'data'=>$data,
        ],200);

        
    }

    public function edittimesheetview(Request $request,$id){
    
        $user = Auth::user();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' =>'User is not authenticated!',
            ],400);
        }
        $assignment_map_employee = AM_Employee::where('id',$id)->with(['employee_one','assignment_map','assignment_map_partner'])->first();
        $timesheet = Timesheet::where('am_employee_id',$id)->first();
        $data = array(
            'data' => $assignment_map_employee,
            'timesheet' => $timesheet,
        );
        return response()->json([
            'msg'=>'timesheet details fetched successfully',
            'data'=>$data,
        ],200);

    }

    public function edittimesheetsubmit(Request $request,$id){
        $user = Auth::user();
        if(!$user){
            return response()->json([
                'status' => false,
                'msg' =>'User is not authenticated!',
            ],400);
        }
        $AM_Employee = AM_Employee::where('id',$id)->with(['employee_one','assignment_map','assignment_map_partner'])->first();
        $timesheet = Timesheet::updateOrCreate(['am_employee_id'=> $id,],[

            "client_id"    => $AM_Employee->assignment_map->client->id,
            "employee_id"    => $AM_Employee->employee_one->id ,
            "assignment_id"      => $AM_Employee->assignment_map->assignment->id ,
            "assignment_map_id"    => $AM_Employee->assignment_map->id ,

            "hours"    => $request->hours ,
            "remarks"     => $request->remarks ,
            "mode"   => $request->mode ,
            "location"      => $request->location ,
            "amount"     => $request->amount ,
            "location_gps"   => $request->location_gps ,
            "location_address"    => $request->location_address ,
        ]);

        return response()->json([
            'msg'=>'timesheet details submitted successfully',
            'data'=>'',
        ],200);

    }

}