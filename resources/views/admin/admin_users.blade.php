@extends('admin.admin_dashboard')
@section('admin')
<style>
    .imgUser{
        object-fit: cover; /* Ensures the image covers the container */
        object-position: center;
        width: 35px;
        height: 35px;
    }

    .modal-body{
        background-color: #f2f2f2;
    }

    .password-strength {
        margin-top: 0.5rem;
    }

    #password-strength-bar {
        transition: width 0.3s ease, background-color 0.3s ease;
    }

    #password-match-text {
        font-size: 0.875rem;
    }
</style>
<div class="page-content" id="page">
    <div class="row">
        <div class="col-xl-12 mb-3 stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Users Count</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="userDonut"></div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="row d-flex align-items-center justify-content-center">
                                <div class="col-md-12 text-center grid-margin stretch-card">
                                    <div class="card shadow-lg">
                                        <div class="card-body">
                                            <h6 class="m-3"><b>Admin:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#ff33cc">{{$adminCount}}</span></h6>
                                            <h6 class="m-3"><b>Head:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#9966ff">{{$headCount}}</span></h6>
                                            <h6 class="m-3"><b>Manager:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#3399ff">{{$observerCount}}</span></h6>
                                            <h6 class="m-3"><b>Employee:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#66ffcc">{{$employeeCount}}</span></h6>
                                            <h6 class="m-3"><b>Intern:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#ffff66">{{$internCount}}</span></h6>
                                            <h6 class="m-3"><b>Total:</b> <span class="p-1 px-3 rounded-2 text-white text-hover bg-primary">{{$total}}</span></h6>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 text-center grid-margin stretch-card">
                                    <div class="card shadow-lg">
                                        <div class="card-body">
                                            <h6 class="card-title">Action</h6>
                                            <div class="d-grid gap-2">
                                                <button type="button" class="btn btn-primary btn-hover"  data-bs-toggle="modal" data-bs-target="#addUserModal" >Create User</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3 stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Intern</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="internTable">
                    <thead>
                    <tr>
                        <th class="pt-0">User</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Username</th>
                        <th class="pt-0">Contact</th>
                        <th class="pt-0">Email</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($account))
                            @foreach($account as $row)
                                @if($row->role === 'intern')

                                <tr>
                                    <td class="py-1">
                                        <img class="border border-primary imgUser" src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}">
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->username}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>{{$row->email}}</td>
                                    <td><span class="badge bg-danger">{{$row->status}}</span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover editUser" data-user="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover deleteUser" data-user="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3 stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Employee</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="employeeTable">
                    <thead>
                    <tr>
                        <th class="pt-0">User</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Username</th>
                        <th class="pt-0">Contact</th>
                        <th class="pt-0">Email</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($account))
                            @foreach($account as $row)
                                @if($row->role === 'employee')

                                <tr>
                                    <td class="py-1">
                                        <img class="border border-primary imgUser" src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}">
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->username}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>{{$row->email}}</td>
                                    <td><span class="badge bg-danger">{{$row->status}}</span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover editUser" data-user="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover deleteUser" data-user="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3 stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Manager</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="observerTable">
                    <thead>
                    <tr>
                        <th class="pt-0">User</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Username</th>
                        <th class="pt-0">Contact</th>
                        <th class="pt-0">Email</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($account))
                            @foreach($account as $row)
                                @if($row->role === 'observer')

                                <tr>
                                    <td class="py-1">
                                        <img class="border border-primary imgUser" src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}">
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->username}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>{{$row->email}}</td>
                                    <td><span class="badge bg-danger">{{$row->status}}</span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover editUser" data-user="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover deleteUser" data-user="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        <div class="col-xl-6 mb-3 stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Head</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="headTable">
                    <thead>
                    <tr>
                        <th class="pt-0">User</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Username</th>
                        <th class="pt-0">Contact</th>
                        <th class="pt-0">Email</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($account))
                            @foreach($account as $row)
                                @if($row->role === 'head')

                                <tr>
                                    <td class="py-1">
                                        <img class="border border-primary imgUser" src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}">
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->username}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>{{$row->email}}</td>
                                    <td><span class="badge bg-danger">{{$row->status}}</span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover editUser" data-user="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover deleteUser" data-user="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
        <div class="col-xl-12 mb-3 stretch-card">
            <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">Admin</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="adminTable">
                    <thead>
                    <tr>
                        <th class="pt-0">User</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Username</th>
                        <th class="pt-0">Contact</th>
                        <th class="pt-0">Email</th>
                        <th class="pt-0">Status</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($account))
                            @foreach($account as $row)
                                @if($row->role === 'admin')

                                <tr>
                                    <td class="py-1">
                                        <img class="border border-primary imgUser" src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}">
                                    </td>
                                    <td>{{$row->name}}</td>
                                    <td>{{$row->username}}</td>
                                    <td>{{$row->phone}}</td>
                                    <td>{{$row->email}}</td>
                                    <td><span class="badge bg-danger">{{$row->status}}</span></td>
                                    <td class="action-buttons">
                                        <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover editUser" data-user="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                        <button class="btn btn-primary mx-1 btn-hover deleteUser" data-user="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="createForm" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Creating New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="card">
                    <div class="card-body">
                        <div class="row g-4"> <!-- g-4 adds spacing between columns -->
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="name" class="form-label"><span class="text-danger me-1">&#x25CF;</span>Name</label>
                                    <input type="text" name="name" class="form-control" id="name">
                                    <div class="form-text">We'll never share your name with anyone else.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="username" class="form-label"><span class="text-danger me-1">&#x25CF;</span>Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input
                                        type="text"
                                        name="username"
                                        class="form-control"
                                        id="username"
                                        readonly
                                        style="background-color: #f8f9fa; cursor: not-allowed;"
                                        >
                                    </div>
                                    <div class="form-text">Auto-generated based on name and role.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="role" class="form-label"><span class="text-danger me-1">&#x25CF;</span>Role</label>
                                    <select name="role" id="role" class="form-select" required>
                                        <option value="" selected>Open this select role</option>
                                        <option value="admin">Admin</option>
                                        <option value="head">Head</option>
                                        <option value="observer">Manager</option>
                                        <option value="employee">Employee</option>
                                        <option value="intern">Intern</option>
                                    </select>
                                    <div class="form-text">Set the role of this user.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label"><span class="text-danger me-1">&#x25CF;</span>Password</label>
                                    <input type="password" name="password" class="form-control" id="password" required>
                                    <div class="form-text">Enter the password.</div>
                                    <div class="password-strength mt-2">
                                        <div class="progress" style="height: 5px;">
                                            <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                        </div>
                                        <small id="password-strength-text" class="text-muted"></small>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPassword" class="form-label"><span class="text-danger me-1">&#x25CF;</span>Confirm Password</label>
                                    <input type="password" name="password_confirmation" class="form-control" id="confirmPassword" required>
                                    <div class="form-text">Enter the confirm password.</div>
                                    <small id="password-match-text" class="d-block mt-1"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
                <button type="button" name="createSubmit" class="btn btn-primary btn-hover">Create Account</button>
            </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserModalLabel">View Department User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body" id="displayUser">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="editInfoForm" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body" id="displayEditUser">

            </div>
            <div class="modal-footer">
                <button type="button" id="editInfo" class="btn btn-primary btn-hover">Submit Information Change</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
    let debounceTimer;

    function destroyExistingDataTables() {
        const tableIds = [
            'internTable',
            'employeeTable',
            'observerTable',
            'headTable',
            'adminTable'
        ];

        tableIds.forEach(tableId => {
            const table = $('#' + tableId);
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
            }
        });
    }

    function initializeDataTables() {
        const tableIds = [
            'internTable',
            'employeeTable',
            'observerTable',
            'headTable',
            'adminTable'
        ];

        const dataTableConfig = {
            "aLengthMenu": [
                [10, 30, 50, -1],
                [10, 30, 50, "All"]
            ],
            "iDisplayLength": 10,
            "language": {
                search: ""
            }
        };

        tableIds.forEach(tableId => {
            const table = $('#' + tableId);

            if (table.length) {
                // Initialize DataTable
                const dataTable = table.DataTable(dataTableConfig);

                // Customize search input and length selector
                const wrapper = table.closest('.dataTables_wrapper');

                wrapper.find('div[id$=_filter] input')
                    .attr('placeholder', 'Search')
                    .removeClass('form-control-sm');

                wrapper.find('div[id$=_length] select')
                    .removeClass('form-control-sm');
            }
        });
    }

    function page() {
        $('#page').load(location.href + " #page > *", function(response, status) {
            if (status === "success") {
                // Initialize Morris Donut chart
                if ($('#userDonut').length) {
                    $('#userDonut').empty(); // Clear previous chart
                    Morris.Donut({
                        element: 'userDonut',
                        data: [
                            { label: "Admin", value: {{$adminCount}} },
                            { label: "Head", value: {{$headCount}} },
                            { label: "Manager", value: {{$observerCount}} },
                            { label: "Employee", value: {{$employeeCount}} },
                            { label: "Intern", value: {{$internCount}} }
                        ],
                        colors: ['#ff33cc', '#9966ff', '#3399ff', '#66ffcc', '#ffff66'],
                        resize: true
                    });
                }

                // Destroy existing DataTables before initializing new ones
                destroyExistingDataTables();
                initializeDataTables();

                // Debounce feather icon replacement
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    feather.replace();
                }, 300);
            }
        });
    }

    $(document).ready(function() {
        page();
        let token = $('meta[name="csrf-token"]').attr('content');

        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',  // You can change the position (top, bottom, etc.)
            showConfirmButton: false,
            timer: 3000,  // Time in milliseconds
            timerProgressBar: true,  // Horizontal loading bar
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        $(document).on('keydown', '#createForm input', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission on Enter key
                $('#createForm button[name="createSubmit"]').click(); // Trigger click on the submit button
            }
        });

        $(document).on('click', '#createForm button[name="createSubmit"]', function() {

            $.ajax({
                url: '{{ route("admin.users.add") }}',
                method: 'POST',
                data: $('#createForm').serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                },
                success: function(response) {
                    if(response.status === 'success') {
                        page();
                        $('#createForm')[0].reset();
                        $('#addUserModal').modal('hide');
                        Toast.fire({
                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Successfully added new user account'
                        });
                    } else if(response.status === 'error') {
                        Toast.fire({
                            icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Error',
                            html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                        });
                    } else if(response.status === 'emailExist') {
                        Toast.fire({
                            icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'You can\'t use this email address, please try another'
                        });
                    } else if(response.status === 'phoneExist') {
                        Toast.fire({
                            icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'You can\'t use this phone number, please try another'
                        });
                    } else if(response.status === 'userExist') {
                        Toast.fire({
                            icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'You can\'t use this username, please try another'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                }
            });
        });

        $(document).on('click', '.viewUser', function() {
            $('#viewUserModal').modal('show');
            var user = $(this).data('user');

            $.ajax({
                url: '{{ route("admin.users.view") }}',
                method: 'GET',
                data: {
                    user: user
                },
                dataType: 'json',
                success: function(response) {
                    var display = response;
                    $('#displayUser').html(display.output);
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                }
            });
        });

        $(document).on('click', '.editUser', function() {
            $('#editUserModal').modal('show');
            var user = $(this).data('user');

            $.ajax({
                url: '{{ route("admin.users.edit") }}',
                method: 'GET',
                data: {
                    user: user
                },
                dataType: 'json',
                success: function(response) {
                    var display = response;
                    $('#displayEditUser').html(display.output);
                    $('#editInfo').data('user', user);
                    $('#editPass').data('user', user);
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                }
            });
        });

        $(document).on('keydown', '#editInfoForm input', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault(); // Prevent form submission on Enter key
                $('#editInfo').click(); // Trigger click on the submit button
            }
        });

        $(document).on('click', '#editInfo', function() {
            var user = $(this).data('user');

            Swal.fire({
                title: 'Are you sure you want to edit this user information?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes! I am sure',
                cancelButtonText: 'No, I don\'t want to'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirmed, proceed with AJAX request
                    $.ajax({
                        url: '{{ route("admin.users.siedit") }}',
                        method: 'POST',
                        data: $('#editInfoForm').serialize() + '&id=' + user,
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                        },
                        success: function(response) {
                            if(response.status === 'success') {
                                page();
                                $('#editUserModal').modal('hide');
                                Toast.fire({
                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                    title: 'Successfully Edited Information'
                                });

                            } else if(response.status === 'error') {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'Information Error',
                                    html: response.errors.join('<br>')
                                });
                            } else {
                                Toast.fire({
                                    icon: 'error',
                                    title: 'An unexpected error occurred.'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', xhr);
                            console.error('Error occurred:', status);
                            console.error('Error occurred:', error);
                        }
                    });
                }
            });

        });

        $(document).on('click', '.deleteUser', function() {
            var user = $(this).data('user');

            Swal.fire({
                title: 'Are you sure you want to delete this user?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes! I am sure',
                cancelButtonText: 'No, I don\'t want to'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirmed, proceed with AJAX request
                    $.ajax({
                        url: '{{ route("admin.users.delete") }}',
                        method: 'POST',
                        data: {
                            user: user
                        },
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                        },
                        success: function(response) {
                            if(response.status === 'success') {
                                page();
                                Toast.fire({
                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                    title: 'Successfully deleted user'
                                });
                            } else if(response.status === 'cannotRemove') {
                                Toast.fire({
                                    icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                    title: 'Cannot Delete User',
                                    text: 'this user is currently in department, remove it first before deleting'
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Something went wrong!'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', xhr.responseText);
                            console.error('Error occurred:', status);
                            console.error('Error occurred:', error);
                        }
                    });
                }
            });
        });
    });

    $(document).ready(function () {
        // Restrict spaces in the username
        $('#username').on('input', function () {
            let username = $(this).val();
            $(this).val(username.replace(/\s/g, ''));
        });

        // Set birthdate restrictions (18–100 years old)
        const today = new Date();
        const maxDate = new Date(today); // 100 years old limit
        maxDate.setFullYear(today.getFullYear() - 18);

        const minDate = new Date(today); // 18 years old limit
        minDate.setFullYear(today.getFullYear() - 100);

        $('#birthdate').attr('max', maxDate.toISOString().split('T')[0]);
        $('#birthdate').attr('min', minDate.toISOString().split('T')[0]);

        // Form submission validation
        $('#userForm').on('submit', function (e) {
            let isValid = true;

            // Check username for spaces
            if (/\s/.test($('#username').val())) {
                $('#usernameError').show();
                isValid = false;
            } else {
                $('#usernameError').hide();
            }

            // Check birthdate for valid range
            const birthdate = new Date($('#birthdate').val());

            if (birthdate < minDate || birthdate > maxDate) {
                $('#birthdateError').show();
                isValid = false;
            } else {
                $('#birthdateError').hide();
            }

            if (!isValid) {
                e.preventDefault();
            }
        });

        const rolePrefixes = {
            'admin': 'AD',
            'head': 'HD',
            'observer': 'OB',
            'employee': 'EM',
            'intern': 'IN'
        };

        const $nameInput = $('#name');
        const $roleSelect = $('#role');
        const $usernameInput = $('#username');

        // Generate username when name or role changes
        $nameInput.add($roleSelect).on('change keyup', function() {
            const name = $nameInput.val().trim().toUpperCase();
            const role = $roleSelect.val();

            if (role && rolePrefixes[role]) {
                // Get first 2 letters of name (pad with X if shorter than 2 chars)
                const namePrefix = name.length >= 2 ? name.substring(0, 2) :
                                (name + 'XX').substring(0, 2);

                // Generate 11 random digits (ensures uniqueness)
                const randomDigits = Math.floor(Math.random() * 90000000000) + 10000000000;
                const randomPart = randomDigits.toString().substring(0, 11);

                // Combine parts: @ + role prefix + name prefix + random digits
                $usernameInput.val('@' + rolePrefixes[role] + namePrefix + randomPart);
            }
        });

        // Ensure username starts with @
        $usernameInput.on('blur', function() {
            if (this.value && !this.value.startsWith('@')) {
                $(this).val('@' + this.value);
            }
        });

        // Password strength indicator
        $('#password').on('input', function() {
            const password = $(this).val();
            const strength = checkPasswordStrength(password);

            $('#password-strength-bar')
                .css('width', strength.percentage + '%')
                .removeClass('bg-danger bg-warning bg-success')
                .addClass(strength.class);

            $('#password-strength-text')
                .text(strength.text)
                .removeClass('text-danger text-warning text-success')
                .addClass(strength.textClass);
        });

        // Password match indicator
        $('#confirmPassword').on('input', function() {
            const password = $('#password').val();
            const confirmPassword = $(this).val();

            if (confirmPassword.length === 0) {
                $('#password-match-text').text('').removeClass('text-danger text-success');
            } else if (password === confirmPassword) {
                $('#password-match-text').text('✓ Passwords match').addClass('text-success').removeClass('text-danger');
            } else {
                $('#password-match-text').text('✗ Passwords do not match').addClass('text-danger').removeClass('text-success');
            }
        });

        // Password strength calculator
        function checkPasswordStrength(password) {
            let strength = 0;

            // Length check
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;

            // Character diversity
            if (/[A-Z]/.test(password)) strength += 1; // Uppercase
            if (/[a-z]/.test(password)) strength += 1; // Lowercase
            if (/[0-9]/.test(password)) strength += 1; // Numbers
            if (/[^A-Za-z0-9]/.test(password)) strength += 1; // Special chars

            // Determine strength level
            if (strength <= 2) {
                return {
                    percentage: 33,
                    class: 'bg-danger',
                    text: 'Weak password',
                    textClass: 'text-danger'
                };
            } else if (strength <= 4) {
                return {
                    percentage: 66,
                    class: 'bg-warning',
                    text: 'Moderate password',
                    textClass: 'text-warning'
                };
            } else {
                return {
                    percentage: 100,
                    class: 'bg-success',
                    text: 'Strong password',
                    textClass: 'text-success'
                };
            }
        }
    });
</script>
@endsection