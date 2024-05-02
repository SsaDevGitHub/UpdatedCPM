@include('Admin/homeheader')
<div class="container">
	<div class="row">
        <!-- Area Chart -->
        <div class="col-xl-12 col-lg-7">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 ">

                	<div class="row">

						<div class="col-md-4">
							<h6 class="m-0 font-weight-bold text-primary"> <?= $active?>
							</h6>
							<p class="m-0"><a href="{{ url('Admin/')}}">Dashboard</a> / <?= $active?></p>
						</div>

                        @if(!empty($errors->first()))	
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>		
                        @endif


						<form action="{{ url('Admin/add-timesheet') }}" method="post" enctype="multipart/form-data">@csrf

							<div class="col-md-12 d-flex">

								<div class="col-md-4"> 	
									<label>Employee</label>
									<select name="employee_id"  class="form-control">
										<?php $employee = DB::table('employee')->get(); ?>
										<option value="" @if($selected == "") selected @endif >Select Employee</option>
										@foreach($employee as $employees)
										<option value="{{ $employees->id }}" @if($employees->id == $selected) selected @endif >{{ $employees->name }}</option>
										@endforeach
									</select>
								</div>

								<div class="col-md-6">            		 	
									<label>Date</label>
									<input type="date" class="form-control" name="f_date" >
								</div>

							</div>

							<div class="col-md-12 mt-2">            		 	
								<button class="btn btn-primary" type="submit">Search</button>
							</div>
							
						</form>

                	</div>

                </div>
                <!-- Card Body -->


                <div class="card-body">

					<label>Date : {{ Carbon\Carbon::parse($date_no)->format('d-m-Y') }}</label>
                    
                    <form action="{{ url('Admin/add-timesheet-list') }}" method="post" >@csrf

                    <input type="hidden" value="{{ Carbon\Carbon::parse($date_no)->format('d-m-Y') }}" name="date_u" >
                    <input type="hidden" value="{{ $selected }}" name="emp_id" >

                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Client</th>
                                    <th>Assignment</th>
                                    <th>Hours</th>
                                    <th>Remark</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="3">Total</th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            <tbody>

                                @if(count($assignment_map_employee) > 0 && !empty($selected) )

                                    @foreach($assignment_map_employee as $key=>$data)

                                    <input type="hidden" name="am_employee_id[]" value="{{ $data->id }}" >
                                    <input type="hidden" name="client_id[]" value="{{ $data->assignment_map->client_id }}" >
                                    <input type="hidden" name="employee_id[]" value="{{ $data->employee_id }}" >
                                    <input type="hidden" name="assignment_id[]" value="{{ $data->assignment_map->assignemnt_id }}" >
                                    <input type="hidden" name="assignment_map_id[]" value="{{ $data->assignment_map_id }}" >

                                    <tr>

                                        <td>{{ $key+1 }}</td>
                                        <td>{{ $data->assignment_map->client->name }}</td>
                                        <td>{{ $data->assignment_map->assignment->name }}</td>
                                        <td><input type="text" name="hours[]" value="{{ $data->timesheet?$data->timesheet->hours:0 }}" required></td>
                                        <td><input type="text" name="remarks[]" value="{{ $data->timesheet?$data->timesheet->remarks:'' }}" ></td>

                                    </tr>

                                    @endforeach


                                @else
                                    <tr>
                                        <td colspan="5">No data available</td>
                                    </tr>
                                @endif
								
								@if(!empty($selected))

                                    <?php $arr_ub = ["Bussiness Development","Office Administartion","Idle","Training","Playing","UnBillable 1","UnBillable 2"]; ?>

                                    <?php for($i = 0; $i < count($arr_ub); $i++){ ?>

                                    @if($i == 0)
									<tr>
                                        <td colspan="5">Unbillable Assignments</td>
                                    </tr>@endif

                                    @if($i == 5)
									<tr>
                                        <td colspan="5">Leaves</td>
                                    </tr>@endif

									<tr>

                                        <td>{{ $i+1 }}<input type="hidden" name="ub_id[]" value="{{ $i+1 }}"></td>
                                        <td>{{ $arr_ub[$i] }}</td>
                                        <td></td>
                                        <td><input type="text" name="hours_ub[]" value="{{ count($UBTimesheet) > 0 ? $UBTimesheet[$i]->hours : 0 }}" required></td>
                                        <td><input type="text" name="remarks_ub[]" value="{{ count($UBTimesheet) > 0 ? $UBTimesheet[$i]->remarks : '' }}" ></td>

                                    </tr>

                                    <?php } ?>

								@endif

                            </tbody>
                        </table>
                    </div>

                    
                    <a href="{{ url('Admin/timesheet-list')}}" class="btn btn-secondry m-t-15 waves-effect">Cancel</a>
                    <button type="submit" class="btn btn-primary m-t-15 waves-effect">Submit</button>

                    </form>
                </div>


            </div>
        </div>
    </div>
</div>




@include('Admin/homefooter')