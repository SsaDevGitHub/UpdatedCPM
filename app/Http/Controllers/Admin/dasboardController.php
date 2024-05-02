<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\admin;
use App\Models\User;
use App\Models\Entity;
use App\Models\Client;
use App\Models\Timesheet;
use App\Models\UBTimesheet;
use App\Models\AM_Employee;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use DB;
use Validator;
class dasboardController extends Controller
{

        public function ub_sub(Request $request){
               
            $data = json_decode($request->getContent(), true);
            $status = $data['con'];
            $grp = DB::table('unbillable_list')->where('group',$status)->first();
            if(!$grp){

                $newn = DB::table('unbillable_list')->where(MAX('ub_id'))->first();
                $one_pl = $newn->ub_id + 1 ;
                $datatable = DB::table('unbillable_list')->insert(['name'=>$status,"ub_id"=>$one_pl,'status'=>0]);

                return response()->json(['data'=>$status],200);

            }else{
                $datatable = DB::table('unbillable_list')->update(['status'=>0]);
                return response()->json(['data'=>'Updated'],200);
            }

        }

        public function ub_dlt(Request $request){
               
            $data = json_decode($request->getContent(), true);
            $status = $data['con'];
            $datatable = DB::table('unbillable_list')->where('id',$status)->update(['status'=>1]);
            return response()->json(['data'=>'Updated'],200);

        }

        public function settings(){

            $sesionAmin = session('Admin_id');        
            if(empty($sesionAmin)){
                return redirect('/');
            }

            $data = array(
                'active'          => 'settings',
                'activetxt'          => 'settings',
            );

            return view('Admin/settings')->with($data);

        }
		
    public function view(){
        date_default_timezone_set("Asia/Kolkata");
        $current = date('Y-m-d'); $last_date=date('Y-m-d',strtotime('- 1 days'));
        
        $sesionAmin = session('Admin_id');
        
        if(empty($sesionAmin)){
            return redirect('/');
        }
        
        $user  = User::count();
        $admin = admin::first();
        $now = Carbon::now()->format('Y-m-d');

        $data = array(
            'user'             => $user,
            'admin'          => $admin,
            'active'          => '',
            'activetxt'          => '',
            );
        
    	return view('Admin/index')->with($data);
    }
    
    public function addusersview(){
        $sesionAmin = session('Admin_id');
        
        if(empty($sesionAmin)){
            return redirect('/');
        }
        $data = array(
            'active' => "users",
            'activetxt' => "usersadd",
        );
        return view('Admin/users/addusers')->with($data);
    }
    public function userslist(){
        $sesionAmin = session('Admin_id');
        
        if(empty($sesionAmin)){
            return redirect('/');
        }
        
        $active       = 'users';
        $activetxt    = 'userslist';
        $datatable = User::get();

        $data = array(

            'datatable'        => $datatable,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        
        );
        return view('Admin/users/userslist')->with($data);
    }
    public function addusers(Request $request){
        $sesionAmin = session('Admin_id');
        
        if(empty($sesionAmin)){
            return redirect('/');
        }
        
        $vald = [
            "user_type" => "required",
            "name" => "required",
            "email" => "required|unique:users,email",
            "mobile_number" => "required",
            "password" => "required",
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }

        User::create([
            "role" => $request->user_type,
            "name" => $request->name,
            "email" => $request->email,
            "phone" => $request->mobile_number,
            "address" => 'Address',
            "password" => Hash::make($request->password),
            "text_pass" => $request->password,
        ]);

         return redirect()->route('Admin.userslist');
         
    }
    
    public function editusersview(Request $request){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $active       = 'users';
        $activetxt    = 'userslist';

        $getid = $request->id;
        $tabledata = User::where('user_id',$getid)->first();
           
        $data = array(
            'tabledata' => $tabledata,
            'active' => $active,
            'activetxt' => $activetxt,
        );
         return view('Admin/users/edituser')->with($data);        
    }
    public function edituser(Request $request , $id){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $user = User::find($id);

        if($request->email != $user->email){
            $emailuser = User::where('email',$request->email)->get();
            if(count($emailuser) > 0){
                return redirect()->back()->withErrors('Email already Exists!');
            }
        }

        $vald = [
            "user_type" => "required",
            "name" => "required",
            "email" => "required",
            "mobile_number" => "required",
            "address" => "required",
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }


        $user->role = $request->user_type;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->mobile_number;
        $user->address = $request->address;
        $user->save();    
        return redirect()->route('Admin.userslist');

    }



    public function addbulktimesheetview(){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $data = array(
            'active' => "timesheet",
            'activetxt' => "bulktimesheetadd",
        );
        return view('Admin/timesheet/addbulktimesheet')->with($data);
    }


    public function addtimesheetview(Request $request){
    
        $sesionAmin = session('Admin_id');

        if(empty($sesionAmin)){
            return redirect('/');
        }

        $assignment_map_employee = [];
        $selected = "";
        $date_no = $request->f_date?Carbon::parse($request->f_date)->format('Y-m-d'):Carbon::now()->format('Y-m-d');

        $query = AM_Employee::with(['employee_one','assignment_map','assignment_map_partner']);
        if(isset($request->employee_id)){
            $selected = $request->employee_id;
            $query = $query->where('employee_id',$request->employee_id)->whereDate('from_date', '<=', $date_no)->whereDate('to_date', '>=', $date_no);
        }

        $assignment_map_employee = $query->with(['timesheet' => function ($abcquery) use ($date_no) {
                                                    $abcquery->whereDate('date', $date_no);
                                                }])->get();

        $UBTimesheet = UBTimesheet::where('date',$date_no)->where('employee_id',$selected)->orderBy('ub_id','ASC')->get();
        
        $data = array(
            'active' => "timesheet",
            'activetxt' => "timesheetadd",
            'assignment_map_employee'        => $assignment_map_employee,
            'date_no'        => $date_no,
            'UBTimesheet'        => $UBTimesheet,
            'selected'        => $selected,
        );

        return view('Admin/timesheet/addtimesheet')->with($data);

    }

    public function addtimesheet(Request $request){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $emp_id = $request->emp_id;
        $date_u = Carbon::parse($request->date_u)->format('Y-m-d');

        $am_employee_id  =   $request->am_employee_id; 
        $assignment_map_id  =    $request->assignment_map_id; 
        $client_id  =    $request->client_id; 
        $assignment_id  =    $request->assignment_id; 
        $hours  =    $request->hours; 
        $remarks  =    $request->remarks; 

        $tot_hour = 0;
        foreach($hours as $ho => $hour){
            $tot_hour += $hour;
        }
        $hours_ub  =   $request->hours_ub; 
        foreach($hours_ub as $ho => $hour_ub){
            $tot_hour += $hour_ub;
        }
        if($tot_hour < 8){
            return redirect()->route('Admin.addtimesheetview', ['employee_id' => $emp_id,'f_date'=>$date_u])->withErrors(["hourt"=>"total hours sum should be 8"]);
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

        $ub_id  =   $request->ub_id; 
        $hours_ub  =   $request->hours_ub; 
        $remarks_ub  =   $request->remarks_ub; 

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

        return redirect()->route('Admin.timesheetlist');
        
    }
    
    public function timesheetlist(Request $request){    
        $sesionAmin = session('Admin_id');                
        if(empty($sesionAmin)){
            return redirect('/');
        }
        $active       = 'timesheet';
        $activetxt    = 'timesheetlist';
        $datatable = DB::table('timesheet')->get();

        $assignment_map_employee = [];
        $selected = "";
        if(isset($request->employee_id)){
            $selected = $request->employee_id;
            $assignment_map_employee = AM_Employee::where('employee_id',$request->employee_id)->with(['employee_one','assignment_map','assignment_map_partner'])->get();
        }


        $data = array(
            'assignment_map_employee'        => $assignment_map_employee,
            'selected'        => $selected,
            'datatable'        => $datatable,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        
        );
        return view('Admin/timesheet/timesheetlist')->with($data);
    }
    
    public function edittimesheetview(Request $request,$id){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $active       = 'timesheet';
        $activetxt    = 'timesheetlist';

        $assignment_map_employee = AM_Employee::where('id',$id)->with(['employee_one','assignment_map','assignment_map_partner'])->first();
        $timesheet = Timesheet::where('am_employee_id',$id)->first();

        $data = array(
            'data' => $assignment_map_employee,
            'timesheet' => $timesheet,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        );

        return view('Admin/timesheet/edittimesheet')->with($data);   

    }

    public function edittimesheet(Request $request,$id){
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $AM_Employee = AM_Employee::where('id',$id)->with(['employee_one','assignment_map','assignment_map_partner'])->first();
        $timesheet = Timesheet::updateOrCreate(['am_employee_id'=>$id,],[

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

        return redirect()->route('Admin.timesheetlist', ['employee_id' => $AM_Employee->employee_one->id]);

    }

    public function deletetimesheet(Request $request){
        $sesionAmin = session('Admin_id');                
        if(empty($sesionAmin)){
            return redirect('/');
        }
        $id = $request->id;
        DB::table('timesheet')->where('id',$id)->delete();
        return redirect()->route('Admin.timesheetlist');
    }
    










    
    public function deleteclient(Request $request){
    
            $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $getid = $request['id'];
        $datatable = DB::table('client')->where('id',$getid)->delete();
        return redirect()->back();
    }
    
    public function addgroup(Request $request){
        
        $data = json_decode($request->getContent(), true);
        $status = $data['new_gp'];
        $grp = DB::table('groups')->where('group',$status)->first();
        if(!$grp){
            $datatable = DB::table('groups')->insert(['group'=>$status]);
            return response()->json(['data'=>$status],200);
        }else{
            return response()->json(['data'=>'Already exist'],400);
        }

    }
    
    public function addclientview(){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }


        $data = array(
            'active' => "client",
            'activetxt' => "clientadd",
        );

        return view('Admin/client/addclient')->with($data);

    }

    public function clientlist(){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }


        $active       = 'client';
        $activetxt    = 'clientlist';
        $datatable = Client::get();

        $data = array(

            'datatable'        => $datatable,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        
        );
        return view('Admin/client/clientlist')->with($data);
    }


    public function addclient(Request $request){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $vald = [
            "name"      => 'required',
            "communication_address"     => 'required',
            "pan"   => 'required',
            "mobile"    => 'required',
            "email"     => 'required',
            "status"    => 'required',
            "gstin"     => 'required',
            "state"     => 'required',
            "dob"   => 'required|date',
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }

        Client::create([
            "name"      =>   $request->name,
            "group"     =>   $request->group,
            "kind_att"      =>   $request->kind_att,
            "communication_address"     =>   $request->communication_address,
            "pan"   =>   $request->pan,
            "tan_no"    =>   $request->tan_no,
            "active_status"     =>   $request->active_status,
            "mobile"    =>   $request->mobile,
            "email"     =>   $request->email,
            "associated_from"   =>   $request->associated_from,
            "status"    =>   $request->status,
            "gstin"     =>   $request->gstin,
            "state"     =>   $request->state,
            "dob"   =>   $request->dob,
        ]);

        return redirect()->route('Admin.clientlist');
         
    }
    
    public function editclientview(Request $request){
    
            $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $getid = $request->id;
        $tabledata = DB::table('client')->where('id',$getid)->first();
           
        $active       = 'client';
        $activetxt    = 'clientlist';

        $data = array(
            'tabledata' => $tabledata,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        );
        return view('Admin/client/editclient')->with($data);        
    }

    public function editclient(Request $request , $id){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $vald = [
            "name"      => 'required',
            "communication_address"     => 'required',
            "pan"   => 'required',
            "mobile"    => 'required',
            "email"     => 'required',
            "status"    => 'required',
            "gstin"     => 'required',
            "state"     => 'required',
            "dob"   => 'required|date',
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }

        $user = Client::find($id);
        $user->name = $request->name;
        $user->group = $request->group;
        $user->kind_att = $request->kind_att;
        $user->communication_address = $request->communication_address;
        $user->pan = $request->pan;
        $user->tan_no = $request->tan_no;   
        $user->active_status = $request->active_status;   
        $user->mobile = $request->mobile;   
        $user->email = $request->email;   
        $user->associated_from = $request->associated_from;   
        $user->status = $request->status;   
        $user->gstin = $request->gstin;   
        $user->state = $request->state;   
        $user->dob = $request->dob;   
        $user->save();

        return redirect()->route('Admin.clientlist');

    }











    
    public function deleteentity(Request $request){
    
            $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $getid = $request['id'];
        $datatable = DB::table('entity')->where('id',$getid)->delete();
        return redirect()->back();
    }
    
    public function addentityview(){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }


        $data = array(
            'active' => "entity",
            'activetxt' => "entityadd",
        );

        return view('Admin/entity/addentity')->with($data);

    }

    public function entitylist(){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }

        $active       = 'entity';
        $activetxt    = 'entitylist';
        $datatable = DB::table('entity')->orderByDesc('id')->get();

        $data = array(

            'datatable'        => $datatable,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        
        );

        return view('Admin/entity/entitylist')->with($data);

    }


    public function addentity(Request $request){
    
        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $vald = [
            "name"      => 'required',
            "communication_address"     => 'required',
            "pan"   => 'required',
            "gstin"     => 'required',
            "state"     => 'required',
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }

        DB::table('entity')->insert([
            "name"      =>   $request->name,
            "address"     =>   $request->communication_address,
            "pan"   =>   $request->pan,
            "gstin"     =>   $request->gstin,
            "state"     =>   $request->state,
            "entity_pc"     =>   $request->entity_pc,
            "lut"      =>   $request->lut,
            "bank_name"    =>   $request->bank_name,
            "acc_no"     =>   $request->acc_no,
            "ifsc"    =>   $request->ifsc,
            "branch"     =>   $request->branch,
        ]);

        return redirect()->route('Admin.entitylist');
         
    }
    
    public function editentityview(Request $request){
    
            $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
    
        $getid = $request->id;
        $tabledata = DB::table('entity')->where('id',$getid)->first();
           
        $active       = 'entity';
        $activetxt    = 'entitylist';

        $data = array(
            'tabledata' => $tabledata,
            'active'        => $active,
            'activetxt'     => $activetxt,  
        );
        return view('Admin/entity/editentity')->with($data);        
    }

    public function editentity(Request $request , $id){

        $sesionAmin = session('Admin_id');
                
        if(empty($sesionAmin)){
            return redirect('/');
        }
        
        $vald = [
            "name"      => 'required',
            "communication_address"     => 'required',
            "pan"   => 'required',
            "gstin"     => 'required',
            "state"     => 'required',
        ];

        $validator = Validator::make($request->all(),$vald);

        if($validator->fails()){
            return redirect()->back()->withErrors($validator->errors());
        }

        $user = Entity::find($id);
        $user->name = $request->name;
        $user->address = $request->communication_address;
        $user->gstin = $request->gstin;
        $user->state = $request->state;
        $user->pan = $request->pan;
        $user->lut = $request->lut;   
        $user->entity_pc = $request->entity_pc;   
        $user->bank_name = $request->bank_name;   
        $user->acc_no = $request->acc_no;   
        $user->ifsc = $request->ifsc;   
        $user->branch = $request->branch;   
        $user->save();

        return redirect()->route('Admin.entitylist');

    }



    
    
    
 


}
