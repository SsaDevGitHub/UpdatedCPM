<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class Timesheet extends Model

{

   	    protected $table='timesheet';

    	protected $primaryKey='id';

    	public  $timestamps = false;

        // public  $nullable = [              
        // ];

    	public  $fillable = [                  
            
                "date"      ,
                "employee_id"      ,
                "assignment_map_id"      ,
                "am_employee_id"      ,
                "client_id"    ,
                "assignment_id"      ,
                "hours"    ,
                "remarks"     ,
                
        ];
}
