@include('Admin/homeheader')


<div class="container">
	<div class="row">
        <!-- Area Chart -->
        <div class="col-xl-12 col-lg-7">
            <div class="card shadow mb-4">
                <!-- Card Header - Dropdown -->
                <div class="card-header py-3 ">
                	<div class="row">
                		<div class="col-md-6">
		                    <h6 class="m-0 font-weight-bold text-primary"> <?= $active?>
		                    </h6>
		                	<p class="m-0"><a href="{{ url('Admin/')}}">Dashboard</a> / <?= $active?></p>
                		</div>
                	</div>
                </div>
				
				@if(!empty($errors->first()))				
					<div class="alert alert-danger">
						{{ $errors->first() }}
					</div>
				@endif
                <!-- Card Body -->
                <div class="card-body">
                    <form  method="post" action="{{ url('Admin/add-settings')}}" >
                        @csrf
                    	<div class="row">

                		 	<div class="col-md-12">            		 	
		                        <label>Timesheet Validity</label>
		                        <div class="form-group">
		                            <div class="form-line">
		                                <input type="number" name="time_sheet" value="get_config_data('time_sheet')" class="form-control" placeholder="Enter Name" required>
		                            </div>
		                        </div>
            		 		</div>
                            
                		 	<div class="col-md-12">

		                        <label>List of unbillable assignments</label>
	
								<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
									Add New+
								</button>

                                <?php $unbillable = DB::table('unbillable_list')->get(); ?>

		                        <div class="form-group">

                                    <ul id="modal_ul">
                                        @foreach($unbillable as $unbillables)
                                            <li>{{ $unbillables->name }} <button class="btn btn-danger" height="10px" width="10px" data-url="{{ url('admin-panel/uproot-UB') }}" data-id="{{ $unbillables->id }}"><span>x</span></button></li>
                                        @endforeach
                                    </ul>
									
		                        </div>

                		 	</div>

                    	</div>
                        <button type="submit" class="btn btn-primary m-t-15 waves-effect">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add Unbillables</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  	
	  	<form class="submit_ub" id="submit_ub" >
			<div class="modal-body">
				<label>Enter Unbillables Tag</label>
				<input type="text" class="form-control" name="name_ub" id="name_ub">
			</div>
      		<div class="modal-footer">
        		<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        		<button type="submit" class="btn btn-primary" id="form_sub_add">Save changes</button>
      		</div>
		</form>
    
	</div>
  </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

<script>


	$('#submit_ub').on('submit',function(){

		var con = $('#name_ub').val();
		url = "{{{ url('admin-panel/submit_new_ub') }}}";

		if(con == ""){

			alert('Please enter Any Unbillable Name');

		} else{ 

			$.ajax({

				type:'POST',

				url:url,

				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },

				contentType: false,       

				cache: false,             

				processData:false,

				data :JSON.stringify({con:con}),

					beforeSend : function () {

						$("#form_sub_add").prop('disabled', true);

					},

					success:function(data)

					{

						console.log(con)
						
						// $('#group').append('<option value='+new_gp+'>'+new_gp+'</option>');

						$('#modal_ul').append('<li>'+con+'</li>');

						// $('#new_gp').val('');

						$("#form_sub_add").prop('disabled', false);

						// return false;

					},
					error:function(data){
						
						// data = JSON.stringify(data)
						data = data.responseJSON.data;
						alert(data)
						$("#form_sub_add").prop('disabled', false);
					}


				});

			}

	});

</script>


@include('Admin/homefooter')