@extends('head.head_dashboard')
@section('head')
<style>
    .profile-dept{
        width: 36px;
        height: 36px;
    }

    .chat-body{
        height: 550px;
    }

    .search-body{
        height: 35px;
    }

    .message-body{
        height: 450px;
        background-color: #f2f2f2;
    }
</style>
<div class="page-content" id="page">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6 p-2">
                            <h6 class="card-title">Department List</h6>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-primary mx-1 float-end btn-hover"  data-bs-toggle="modal" data-bs-target="#addDepartmentModal" ><i data-feather="file-plus" class="icon-sm icon-wiggle"></i> Add New Department</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 mb-3">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 text-center" id="deptListTable">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">Department Name</th>
                                        <th class="pt-0">Department Manager</th>
                                        <th class="pt-0">Created By</th>
                                        <th class="pt-0">Status</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($dept))
                                            @foreach($dept as $row)
                                                <tr>
                                                    <td>{{$row->name}}</td>
                                                    <td>
                                                        @if ($row->head_name)
                                                            {{ $row->head_name->name }}
                                                        @else
                                                            No Manager found
                                                        @endif
                                                    </td>
                                                    <td>{{$row->created_by}}</td>
                                                    <td>
                                                        <span class="badge bg-danger">Active</span>
                                                    </td>
                                                    <td class="action-buttons">
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover" id="assignMember" data-id="{{$row->id}}"><i data-feather="user" class="icon-sm icon-wiggle"></i></button>
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover" id="viewDepartment" data-id="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                                        <button type="button" id="editDepartment" data-id="{{$row->id}}" class="btn btn-primary mx-1 btn-hover"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                                        <button type="button" id="deleteDepartment" data-id="{{$row->id}}" class="btn btn-primary mx-1 btn-hover"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if(!empty($dept))
                                <div class="col-12 mb-3">
                                    <label for="searchAllDepartment">Search Name: </label>
                                    <input type="text" class="form-control" id="searchAllDepartment" placeholder="Search department name . . .">
                                </div>
                                @foreach($dept as $row)
                                <div class="col-xl-12 grid-margin stretch-card department-content-list" data-name="{{$row->name}}">

                                    <div class="card shadow-lg">
                                        <div class="card-body">
                                            <h6 class="card-title">{{$row->name}}</h6>
                                            <div class="row">
                                                <div class="col-xl-12 mb-3">
                                                    <div class="table-responsive">
                                                        <hr>
                                                        <h6 class="card-title">Department Manager</h6>
                                                        <table class="table table-hover mb-0 text-center departmentHeadTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>User</th>
                                                                    <th>Manager Name</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if($row->dept_head)
                                                                    @foreach($row->dept_head as $head)
                                                                    <tr>
                                                                        <td class="py-1">
                                                                            <img class="border border-primary" src="{{ (!empty($head->user->photo)) ? url('upload/photo_bank/'.$head->user->photo) : url('upload/nophoto.jfif') }}">
                                                                        </td>
                                                                        <td>{{$head->user->name}}</td>
                                                                        <td><span class="badge {{$head->user->is_online == 1 ? 'bg-success' : 'bg-danger'}}">{{$head->user->is_online == 1 ? 'Online' : 'Offline'}}</span></td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$head->user_id}}" data-dept="{{$head->department_id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                                                            <button class="btn btn-primary mx-1 btn-hover chatWithUser" data-name="{{$head->user->name}}" data-user="{{$head->user_id}}"><i data-feather="send" class="icon-sm icon-wiggle"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                        <hr>
                                                        <h6 class="card-title">Department Employee</h6>
                                                        <table class="table table-hover mb-0 text-center departmentEmployeeTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>User</th>
                                                                    <th>Employee / Intern Name</th>
                                                                    <th>Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if($row->dept_member)
                                                                    @foreach($row->dept_member as $member)
                                                                    <tr>
                                                                        <td class="py-1">
                                                                            <img class="border border-primary" src="{{ (!empty($member->user->photo)) ? url('upload/photo_bank/'.$member->user->photo) : url('upload/nophoto.jfif') }}">
                                                                        </td>
                                                                        <td>{{$member->user->name}}</td>
                                                                        <td><span class="badge {{$member->user->is_online == 1 ? 'bg-success' : 'bg-danger'}}">{{$member->user->is_online == 1 ? 'Online' : 'Offline'}}</span></td>
                                                                        <td class="action-buttons">
                                                                            <button class="btn btn-primary mx-1 btn-hover viewUser" data-user="{{$member->user_id}}" data-dept="{{$member->department_id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                                                            <button class="btn btn-primary mx-1 btn-hover chatWithUser" data-name="{{$member->user->name}}" data-user="{{$member->user_id}}"><i data-feather="send" class="icon-sm icon-wiggle"></i></button>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                @endif
                                                            </tbody>
                                                        </table>
                                                        <hr>
                                                        <h6 class="card-title">Department Task</h6>
                                                        <table class="table table-hover mb-0 text-center departmentTaskTable">
                                                            <thead>
                                                                <tr>
                                                                    <th>Task Name</th>
                                                                    <th>Task Type</th>
                                                                    <th>Assigned Date</th>
                                                                    <th>Due Date</th>
                                                                    <th>Progress</th>
                                                                    <th>Status</th>
                                                                    <th>Assigned To</th>
                                                                    <th>User Status</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @if($row->task)
                                                                    @foreach($row->task as $task)
                                                                    <tr>
                                                                        <td>{{$task->title}}</td>
                                                                        <td>{{$task->type}}</td>
                                                                        <td>{{$task->assigned}}</td>
                                                                        <td>{{$task->due}}</td>
                                                                        <td>
                                                                            <div class="progress">
                                                                                <div class="progress-bar progress-bar-striped {{$task->status === 'Overdue' ? 'bg-danger' : 'bg-success'}} progress-bar-animated" role="progressbar" style="width: {{$task->progress_percentage}}%;" aria-valuenow="{{$task->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$task->progress_percentage}}%</div>
                                                                            </div>
                                                                        </td>
                                                                        <td><span class="badge {{$task->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}}">{{$task->status}}</span></td>
                                                                        <td>{{$task->assigned_to}}</td>
                                                                        <td>
                                                                            <span class="badge
                                                                            {{$task->user_status === 'Active' ? 'bg-primary' :
                                                                            ($task->user_status === 'Away' ? 'bg-danger' :
                                                                            ($task->user_status === 'Idle' ? 'bg-warning' :
                                                                            ($task->user_status === 'Emergency' ? 'bg-dark' :
                                                                            ($task->user_status === 'Sleep' ? 'bg-info' : 'bg-secondary'))))}}">
                                                                                {{$task->user_status}}
                                                                            </span>
                                                                        </td>
                                                                        <td class="action-buttons">
                                                                            <a class="btn btn-primary mx-1 btn-hover" href="{{ route('head.lvtasks', ['task' => $task->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                                        </td>
                                                                    </tr>
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
                            @endforeach
                        @else
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="departmentForm" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addDepartmentModalLabel">Creating New Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12 stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Department Name</label>
                                    <input type="name" name="name" class="form-control" id="name" aria-describedby="name">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
                <button type="button" name="createSubmit" class="btn btn-primary btn-hover">Create Department</button>
            </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="assignDepartmentModal" tabindex="-1" aria-labelledby="assignDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="assignDepartmentModalLabel">Assign Department Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="modal-body border-0 px-4">
        <div class="row modal-body-bg">
            <div class="col-12 mb-3 mt-3">
                <h6 class="card-title">Department Name: </h6>
            </div>
            <div class="col-12 mb-3">
                <h6 class="card-title">Department Manager: </h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="department-head-table">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <h6 class="card-title">Employee / Intern: </h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="department-member-table">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-md-12 mb-3">
                <h6 class="card-title">List of Manager / Employee / Intern: </h6>
                <div id="list-user" class="list-div">

                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewDepartmentModal" tabindex="-1" aria-labelledby="viewDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="viewDepartmentModalLabel">View Department Member</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="modal-body border-0 px-4">
        <div class="row modal-body-bg">
            <div class="col-12 mb-3 mt-3">
                <h6 class="card-title">Department Name: </h6>
            </div>
            <div class="col-12 mb-3">
                <h6 class="card-title">Department Manager: </h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="department-head-table-view">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
                <h6 class="card-title">Employee / Intern: </h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="department-member-table-view">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="departmentEditForm" method="POST">
            @csrf
        <div class="modal-header">
            <h5 class="modal-title" id="addDepartmentModalLabel">Edit Department</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body" id="editDesign">

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
            <button type="button" id="editDept" class="btn btn-primary btn-hover">Edit Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="viewUserDepartmentModal" tabindex="-1" aria-labelledby="viewUserDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewUserDepartmentModalLabel">View Department User Profile</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body" id="displayUserView">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
                <button type="button" id="idUserView" class="btn btn-primary btn-hover sendMessage">Message</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sendMessageChatModal" tabindex="-1" aria-labelledby="sendMessageChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
    <div class="modal-content">
        <form id="messageContactForm" enctype="multipart/form-data">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="worktimeSettingsModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="border-0 px-4">
                <div class="row modal-body-bg border border-primary">
                    <div class="chat-footer d-flex" style="display: flex !important;">
                        <input type="hidden" id="contact_id" name="contact_id" value="">
                        <div class="">
                            <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileButton">
                                <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                            </button>
                            <input type="file" name="attachments[]" id="fileInput" multiple style="display: none;">
                        </div>
                        <div class="input-group">
                            <textarea type="text" name="message" class="form-control rounded-2" id="chatForm" placeholder="Type a message" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="sendMessageContact" class="btn btn-primary btn-hover submitMessage">Send Message</button>
            </div>
        </form>
    </div>
  </div>
</div>

<script>
    let debounceTimer;

    function page() {
        $('#page').load(location.href + " #page > *", function(response, status, xhr) {
            if (status === "success") {
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

    function destroyExistingDataTables() {
        const tableIds = [
            'deptListTable',
            'departmentHeadTable',
            'departmentEmployeeTable',
            'departmentTaskTable'
        ];

        tableIds.forEach(tableId => {
            const table = $('.' + tableId);
            if ($.fn.DataTable.isDataTable(table)) {
                table.DataTable().destroy();
            }
        });
    }

    function initializeDataTables() {
        const tableIds = [
            'deptListTable',
            'departmentHeadTable',
            'departmentEmployeeTable',
            'departmentTaskTable'
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
            const table = $('.' + tableId);

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

$(document).ready(function() {
    $(document).on('keyup', '#searchAllDepartment', function() {
        let searchText = $(this).val().toLowerCase();
        $('.department-content-list').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    })
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

    $(document).on('keydown', '#departmentForm input', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission on Enter key
            $('#departmentForm button[name="createSubmit"]').click(); // Trigger click on the submit button
        }
    });

    $(document).on('click', '#departmentForm button[name="createSubmit"]', function() {


        $.ajax({
            url: '{{ route("head.department.add") }}',
            method: 'POST',
            data: $('#departmentForm').serialize(),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    page();
                    $('#departmentForm')[0].reset();
                    $('#addDepartmentModal').modal('hide');
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added new department'
                    });
                } else if(response.status === 'error') {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
                    });
                } else if(response.status === 'nameExist') {
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'You can\'t use this department name, please try another'
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

    $(document).on('click', '#assignMember', function() {
        $('#assignDepartmentModal').modal('show');
        var id = $(this).data('id');

        viewList(id);
        viewHead(id);
        viewMember(id);
    });

    function viewList(id){
        $.ajax({
            url: '{{ route("head.department.user") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response){
                var listHtml = '';
                response.forEach(function(user) {
                    var photoUrl = user.photo
                    ? '{{ url("upload/photo_bank") }}/' + user.photo
                    : '{{ url("upload/nophoto.jfif") }}';

                    listHtml += `<div class="list-card">
                    <div class="list-header justify-content-start align-items-center">
                        <img src="${photoUrl}" alt="image">
                        <h6 class="ms-2">${user.name}</h6>
                    </div>
                    <div class="list-content">
                        <h3 class="ms-2">${user.role}</h3>
                        <button type="button" id="addMember" class="btn btn-hover btn-primary list-assign" data-dept="${id}" data-user="${user.id}">Assign</button>
                    </div>
                </div>`;
                });

                $('#list-user').html(listHtml);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    var table = $('#department-head-table').DataTable();
    function viewHead(id){
        $.ajax({
            url: '{{ route("head.department.head") }}',
            type: 'GET',
            data:{
                id: id
            },
            dataType: 'json',
            success: function(response){
                var rows = response.list;
                table.clear().rows.add($(rows)).draw();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    var member = $('#department-member-table').DataTable();
    function viewMember(id){
        $.ajax({
            url: '{{ route("head.department.member") }}',
            type: 'GET',
            data:{
                id: id
            },
            dataType: 'json',
            success: function(response){
                var rows = response.list;
                member.clear().rows.add(rows).draw();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    $(document).on('click', '#addMember', function() {
        var dept = $(this).data('dept');
        var user = $(this).data('user');

        Swal.fire({
            title: 'Are you sure you want to add this user to the department?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("head.department.assign") }}',
                    method: 'POST',
                    data: {
                        department_id: dept,
                        user_id: user
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            page();
                            viewList(dept);
                            viewHead(dept);
                            viewMember(dept);
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully added new user to department'
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

    $(document).on('click', '#removeHead', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        Swal.fire({
            title: 'Are you sure you want to remove this user to the department?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("head.department.remove") }}',
                    method: 'POST',
                    data: {
                        department_id: dept,
                        user_id: user
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            page();
                            viewList(dept);
                            viewHead(dept);
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove user to department'
                            });
                        } else if(response.status === 'error') {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
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

    $(document).on('click', '#removeMember', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        Swal.fire({
            title: 'Are you sure you want to remove this user to the department?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("head.department.remove") }}',
                    method: 'POST',
                    data: {
                        department_id: dept,
                        user_id: user
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            page();
                            viewList(dept);
                            viewMember(dept);
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove user to department'
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

    $(document).on('click', '#viewDepartment', function() {
        $('#viewDepartmentModal').modal('show');
        var id = $(this).data('id');
        viewHeadDept(id);
        viewMemberDept(id);
    });

    var tableDept = $('#department-head-table-view').DataTable();
    function viewHeadDept(id){
        $.ajax({
            url: '{{ route("head.department.vhead") }}',
            type: 'GET',
            data:{
                id: id
            },
            dataType: 'json',
            success: function(response){
                var rows = response.list;
                tableDept.clear().rows.add($(rows)).draw();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    var memberDept = $('#department-member-table-view').DataTable();
    function viewMemberDept(id){
        $.ajax({
            url: '{{ route("head.department.vmember") }}',
            type: 'GET',
            data:{
                id: id
            },
            dataType: 'json',
            success: function(response){
                var rows = response.list;
                memberDept.clear().rows.add(rows).draw();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    $(document).on('click', '#editDepartment', function() {
        $('#editDepartmentModal').modal('show');
        var id = $(this).data('id');
        $('#editDept').data('id', id);

        $.ajax({
            url: '{{ route("head.department.edept") }}',
            type: 'GET',
            data:{
                id: id
            },
            dataType: 'json',
            success: function(response){
                var dept = response;
                console.log(dept.name);
                var listHtml = ` <div class="row">
                    <div class="col-lg-12 stretch-card">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Department Name</label>
                                    <input type="text" name="name" class="form-control" id="nameEdit" value="${dept.name}" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>`;

                $('#editDesign').html(listHtml);
                console.log('Input field value:', $('#nameEdit').val());

            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    });

    $(document).on('keydown', '#departmentEditForm input', function(event) {
        if (event.key === 'Enter') {
            event.preventDefault(); // Prevent form submission on Enter key
            $('#editDept').click(); // Trigger click on the submit button
        }
    });

    $(document).on('click', '#editDept', function() {
        var id = $(this).data('id');
        var formData = $('#departmentEditForm').serialize() + '&id=' + id;
        console.log(formData);
        $.ajax({
            url: '{{ route("head.department.edit") }}',
            method: 'POST',
            data: $('#departmentEditForm').serialize() + '&id=' + id,
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    page();
                    $('#departmentEditForm')[0].reset();
                    $('#editDepartmentModal').modal('hide');
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully updated department'
                    });
                } else if(response.status === 'error') {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
                    });
                } else if(response.status === 'nameExist') {
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'You can\'t use this department name, please try another'
                    });
                } else if(response.status === 'notFound') {
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Not Found'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('click', '#deleteDepartment', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure you want to remove this department?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("head.department.rdept") }}',
                    method: 'POST',
                    data: {
                        id: id,
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
                                title: 'Successfully remove department'
                            });
                        } else if(response.status === 'cannotRemove') {
                            Toast.fire({
                                icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'You can\'t remove this department while there\'s member'
                            });
                        } else if(response.status === 'notFound') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Department Not Found'
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

    $(document).on('click', '#vHeadDept', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        viewUserDept(user, dept);
    });

    $(document).on('click', '#vMemberDept', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        viewUserDept(user, dept);
    });

    function viewUserDept(user, dept){
        $('#viewUserDepartmentModal').modal('show');

        $.ajax({
            url: '{{ route("head.department.vuser") }}',
            method: 'GET',
            data: {
                user: user,
                dept: dept
            },
            dataType: 'json',
            success: function(response) {
                var display = response;
                $('#displayUserView').html(display.output);
                $('#idUserView').data('user', user);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.viewUser', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        viewUserDept(user, dept);
    });

    $(document).on('click', '.chatWithUser', function() {
        var user = $(this).data('user');
        var name = $(this).data('name');
        $('#sendMessageChatModal').modal('show');
        $('#worktimeSettingsModalLabel').html(`Message ${name}`);
        $('#contact_id').val(user);
    });

    $('#attachFileButton').on('click', function () {
        $('#fileInput').click(); // Open file dialog
    });

    $('#messageContactForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('head.chat.sendcontactmessage') }}`,
            type: 'POST',
            noLoading: true,
            data: formData,
            processData: false, // Don't process the data
            contentType: false, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    $('#messageContactForm')[0].reset();
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully sent message to '+response.message
                    });
                    $('#chats').load(location.href + ' #chats > *');
                    $('#sendMessageChatModal').modal('hide');
                } else if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });
});
</script>
@endsection