<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class Employee extends Model

{

   	    protected $table='employee';

    	protected $primaryKey='id';

    	public  $timestamps = false;

        //     public  $nullable = [
        // ];

    	public  $fillable = [
                "qualification" ,
                "membership_no" ,
                "date_n","user_id",
                "name"      ,
            "official_email"     ,
            "entity"     ,
            "department"     ,
            "subdepartment"     ,

            "emppc"     ,
            "location"     ,
            "designation"     ,
            "moderator"     ,
            "fname"     ,
            "caddress"     ,
            "paddress"    ,
            "adhaar"   ,
            "pan"   ,
            "qualification"   ,
            "membership_no"   ,
            "email"     ,
            "mobile"     ,
            "dob"      ,
            "doj"     ,
            "date_n"    ,
            "status"     ,
            "ctc_annual"    ,
            "ctc_per_hour"    ,
            "rc_per_hour"     ,
        ];

        public function user(){
            return $this->belongsTo(User::class,'user_id');
        }

}
