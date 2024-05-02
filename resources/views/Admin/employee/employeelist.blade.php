@include('Admin/homeheader')

<style>

.toggleBtnCls {
  display: none;
}

.toggle {
  width: 50px;
  height: 25px;
  background-color: #ccc;
  border-radius: 25px;
  display: inline-block;
  position: relative;
  cursor: pointer;
}

.toggle::before {
  content: '';
  position: absolute;
  width: 21px;
  height: 21px;
  border-radius: 50%;
  background-color: white;
  top: 2px;
  left: 2px;
  transition: transform 0.3s;
}

.toggleBtnCls:checked + .toggle::before {
  transform: translateX(25px);
}

.toggleBtnCls:checked + .toggle {
  background-color: #66bb6a; 
}

</style>


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
                		<div class="col-md-6 ">
                			<div style="text-align: end;">
        			      		<a  href="{{ url('Admin/add-employee')}}" class="btn btn-primary"><i class="fas fa-plus"></i> Add</a>
                			</div>
                		</div>
                	</div>
                </div>
                <!-- Card Body -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Team Leader</th>
                                    <th>Password</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>S. No.</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile Number</th>
                                    <th>Team Leader</th>
                                    <th>Password</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                @foreach($datatable as $key=> $data)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>{{ $data->name }}</td>
                                    <td>{{ $data->email }}</td>
                                    <td>{{ $data->mobile }}</td>

                                    <td>

                                        <input type="checkbox" onchange="changeTog({{ $data->user->user_id }})" 
                                            id="toggleBtn_{{ $data->user->user_id }}" 
                                            data-id="{{ $data->user->user_id }}" class="toggleBtnCls"
                                            @if($data->user->role == "2") checked @endif >
                                        <label for="toggleBtn_{{ $data->user->user_id }}" class="toggle"></label>

                                    </td>

                                    <td>{{ $data->user->text_pass }}</td>
                                    <td>  
                                        <a href="edit-employee?id={{ $data->id }}" class="btn btn-sm btn-success btn-circle">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <a href="delete-employee?id={{ $data->id }}"class="btn btn-sm btn-danger btn-circle">
                                            <i class="fas fa-trash"></i>
                                        </a>                                        
                                    </td>
                                </tr>
                                @endforeach()
                            </tbody>
                        </table>
                        <script>

                            function changeTog(e) {
                                var user_id = e;
                                if ($('#toggleBtn_'+e).is(':checked')) {
                                    var status = 2;
                                } else {
                                    var status = 1;
                                }
                                var url = "{{{ url('Admin/set_user_role') }}}" ;
                                
                                $.ajax({

                                    type:'POST',
                                    url:url,
                                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                                    contentType: false,       
                                    cache: false,             
                                    processData:false,
                                    data :JSON.stringify({
                                        'status' : status,
                                        'user_id' : user_id
                                    }),
                                    success:function(data)
                                    {
                                        alert('Role Changed Successfully');
                                    },

                                });

                            };
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('Admin/homefooter')