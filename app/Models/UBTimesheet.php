<?php



namespace App\Models;



use Illuminate\Database\Eloquent\Model;



class UBTimesheet extends Model

{

   	    protected $table='unbillable_timesheet';

    	protected $primaryKey='id';

    	public  $timestamps = false;

        // public  $nullable = [              
        // ];

    	public  $fillable = [                  
            
                "date"      ,
                "employee_id"      ,
                "ub_id"      ,
                "hours"    ,
                "remarks"     ,
                
        ];
}
