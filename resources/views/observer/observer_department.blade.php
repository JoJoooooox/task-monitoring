@extends('observer.observer_dashboard')
@section('observer')
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
        @if(!empty($dept))
            <div class="col-12">
                <div class="card mb-3 p-1">
                    <div class="card-body p-0 m-0">
                        <div class="row d-flex align-items-center m-0">
                            <!-- Title on the left -->
                            <div class="col">
                                <h6 class="card-title mb-0">{{$dept->name}}</h6>
                            </div>
                            <!-- Button on the right -->
                            <div class="col-auto">
                                <button type="button" class="btn btn-primary btn-hover"
                                    data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    Create User
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-12 d-grid mb-2">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHead" aria-expanded="false" aria-controls="collapseHead">
                        <i data-feather="list" class="icon-sm icon-wiggle"></i> Department Head List
                        </button>
                    </div>
                    <div class="col-12 collapse show" id="collapseHead">
                        <div class="card">
                            <div class="card-body">
                                <h1 class="card-title">Department Head</h1>
                                <div class="row justify-content-center g-1">
                                     @if($dept->dept_head)
                                        @foreach($dept->dept_head as $head)
                                        <div class="col-auto m-1">
                                            <div class="department-member-card">
                                                <div class="department-member-header">
                                                    <div class="department-member-status {{$head->is_online === 1 ? 'online' : 'offline'}}"></div>
                                                    <img src="{{ (!empty($head->photo)) ? url('upload/photo_bank/'.$head->photo) : url('upload/nophoto.jfif') }}" alt="Profile Image" class="department-member-profile-img">
                                                </div>
                                                <div class="department-member-body">
                                                    <h2 class="department-member-name">{{$head->name}}</h2>
                                                    {!! $head->user_id != Auth::user()->id ? '<button class="btn btn-primary mb-2 sendContactMessage" data-user="'.$head->user_id.'" data-name="'.$head->name.'"><i data-feather="send" class="icon-sm icon-wiggle"></i> Message</button>' : '' !!}
                                                    @php
                                                        $tasks = $head->tasks; // Decode tasks JSON
                                                        $completed = $head->completed;
                                                        $ongoingOverdueCount = collect($tasks)->whereIn('task_status', ['Ongoing', 'Overdue'])->count();
                                                        $toCheckCount = collect($tasks)->where('task_status', 'To Check')->count();
                                                        $completedCount = $completed;
                                                    @endphp
                                                    <div class="department-member-stats">
                                                        <span class="badge bg-secondary">{{ $ongoingOverdueCount }} Ongoing/Overdue Task</span>
                                                        <span class="badge bg-dark">{{ $toCheckCount }} To Check Task</span>
                                                        <span class="badge bg-primary">{{ $completedCount }} Completed Task</span>
                                                    </div>

                                                    <div class="department-member-about mt-2">
                                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDepartmentStatistic_{{$head->user_id}}" aria-expanded="false" aria-controls="collapseDepartmentStatistic_{{$head->user_id}}">
                                                        <i data-feather="pie-chart" class="icon-sm icon-wiggle"></i> User Statistic
                                                        </button>
                                                        <div class="collapse" id="collapseDepartmentStatistic_{{$head->user_id}}">
                                                            <h3>User Statistic</h3>
                                                            <div id="barChartDisplay_{{$head->user_id}}">
                                                                <canvas id="barChart_{{$head->user_id}}" class="barChartDept" data-dept-head='@json($dept->dept_head)'></canvas>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="department-member-photos mt-2">
                                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDepartmentTask_{{$head->user_id}}" aria-expanded="false" aria-controls="collapseDepartmentTask_{{$head->user_id}}">
                                                        <i data-feather="list" class="icon-sm icon-wiggle"></i> Task List
                                                        </button>
                                                        <div class="collapse" id="collapseDepartmentTask_{{$head->user_id}}">
                                                            <h3>Task List</h3>
                                                            <div class="row g-1">
                                                                @if(collect($head->tasks)->isNotEmpty())
                                                                    @foreach ($head->tasks as $task)
                                                                        @if ($task->task_status == 'Completed')
                                                                            @continue
                                                                        @endif
                                                                        <div class="col-12">
                                                                            <div class="card modal-body-bg border border-primary text-start">
                                                                                <h4 class="card-title">{{$task->task_title}}</h4>
                                                                                <div class="card-body m-0 p-0 text-center d-block">
                                                                                    <span class="badge {{$task->task_status == 'Overdue' ? 'bg-danger' : 'bg-primary' }}">{{$task->task_status}}</span>
                                                                                    <div class="progress w-100 border border-primary rounded-1 mt-1">
                                                                                        <div class="progress-bar progress-bar-striped progress-bar-animated {{$task->task_status === 'Overdue' ? 'bg-danger' : 'bg-success'}}"
                                                                                            role="progressbar"
                                                                                            style="width: {{$task->task_progress}}%;"
                                                                                            aria-valuenow="{{$task->task_progress}}"
                                                                                            aria-valuemin="0"
                                                                                            aria-valuemax="100">
                                                                                            {{$task->task_progress}}%
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="d-grid m-0 mt-1 p-0">
                                                                                        <a class="btn btn-sm btn-primary btn-hover"
                                                                                            href="{{ isset($task->task_id) ? route('observer.lvtasks', ['task' => $task->task_id]) : '#' }}">
                                                                                                <i data-feather="eye" class="icon-sm icon-wiggle"></i> View Task
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div class="col-12">
                                                                        <div class="card modal-body-bg border border-primary">
                                                                            <h4 class="card-title  m-0">There's no existing task for this user</h4>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 d-grid mb-2">
                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMember" aria-expanded="false" aria-controls="collapseMember">
                        <i data-feather="list" class="icon-sm icon-wiggle"></i> Department Member List
                        </button>
                    </div>
                    <div class="col-12 collapse show" id="collapseMember">
                        <div class="card">
                            <div class="card-body">
                                <h1 class="card-title">Department Member</h1>
                                <div class="row justify-content-center g-1">
                                     @if($dept->dept_member)
                                        @foreach($dept->dept_member as $member)
                                        <div class="col-auto m-1">
                                            <div class="department-member-card">
                                                <div class="department-member-header">
                                                    <div class="department-member-status {{$member->is_online === 1 ? 'online' : 'offline'}}"></div>
                                                    <img src="{{ (!empty($member->photo)) ? url('upload/photo_bank/'.$member->photo) : url('upload/nophoto.jfif') }}" alt="Profile Image" class="department-member-profile-img">
                                                </div>
                                                <div class="department-member-body">
                                                    <h2 class="department-member-name">{{$member->name}}</h2>
                                                    {!! $member->user_id != Auth::user()->id ? '<button class="btn btn-primary mb-2 sendContactMessage" data-user="'.$member->user_id.'" data-name="'.$member->name.'"><i data-feather="send" class="icon-sm icon-wiggle"></i> Message</button>' : '' !!}
                                                    @php
                                                        $tasks = $member->tasks; // Decode tasks JSON
                                                        $completed = $member->completed;
                                                        $ongoingOverdueCount = collect($tasks)->whereIn('task_status', ['Ongoing', 'Overdue'])->count();
                                                        $toCheckCount = collect($tasks)->where('task_status', 'To Check')->count();
                                                        $completedCount = $completed;
                                                    @endphp
                                                    <div class="department-member-stats">
                                                        <span class="badge bg-secondary">{{ $ongoingOverdueCount }} Ongoing/Overdue Task</span>
                                                        <span class="badge bg-dark">{{ $toCheckCount }} To Check Task</span>
                                                        <span class="badge bg-primary">{{ $completedCount }} Completed Task</span>
                                                    </div>

                                                    <div class="department-member-about mt-2">
                                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDepartmentStatistic_{{$member->user_id}}" aria-expanded="false" aria-controls="collapseDepartmentStatistic_{{$member->user_id}}">
                                                        <i data-feather="pie-chart" class="icon-sm icon-wiggle"></i> User Statistic
                                                        </button>
                                                        <div class="collapse" id="collapseDepartmentStatistic_{{$member->user_id}}">
                                                            <h3>User Statistic</h3>
                                                            <div id="barChartDisplay_{{$member->user_id}}">
                                                                <canvas id="barChart_{{$member->user_id}}" class="barChartDeptMem" data-dept-member='@json($dept->dept_member)'></canvas>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="department-member-photos mt-2">
                                                        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDepartmentTask_{{$member->user_id}}" aria-expanded="false" aria-controls="collapseDepartmentTask_{{$member->user_id}}">
                                                        <i data-feather="list" class="icon-sm icon-wiggle"></i> Task List
                                                        </button>
                                                        <div class="collapse" id="collapseDepartmentTask_{{$member->user_id}}">
                                                            <h3>Task List</h3>
                                                            <div class="row g-1">
                                                                @if(!empty($member->tasks) && collect($member->tasks)->filter()->isNotEmpty())
                                                                    @foreach ($member->tasks as $task)
                                                                        @if ($task->task_status == 'Completed')
                                                                            @continue
                                                                        @endif
                                                                        <div class="col-12">
                                                                            <div class="card modal-body-bg border border-primary text-start">
                                                                                <h4 class="card-title">{{$task->task_title}}</h4>
                                                                                <div class="card-body m-0 p-0 text-center d-block">
                                                                                    <span class="badge {{$task->task_status == 'Overdue' ? 'bg-danger' : 'bg-primary' }}">{{$task->task_status}}</span>
                                                                                    <div class="progress w-100 border border-primary rounded-1 mt-1">
                                                                                        <div class="progress-bar progress-bar-striped progress-bar-animated {{$task->task_status === 'Overdue' ? 'bg-danger' : 'bg-success'}}"
                                                                                            role="progressbar"
                                                                                            style="width: {{$task->task_progress}}%;"
                                                                                            aria-valuenow="{{$task->task_progress}}"
                                                                                            aria-valuemin="0"
                                                                                            aria-valuemax="100">
                                                                                            {{$task->task_progress}}%
                                                                                        </div>
                                                                                    </div>
                                                                                    <div class="d-grid m-0 mt-1 p-0">
                                                                                        <a class="btn btn-sm btn-primary btn-hover"
                                                                                            href="{{ isset($task->task_id) ? route('observer.lvtasks', ['task' => $task->task_id]) : '#' }}">
                                                                                                <i data-feather="eye" class="icon-sm icon-wiggle"></i> View Task
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    <div class="col-12">
                                                                        <div class="card modal-body-bg border border-primary ">
                                                                            <h4 class="card-title m-0">There's no existing task for this user</h4>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
        <div class="col-12">
            <div class="m-5 text-center card">
                <div class="card-body">
                <h6 class="card-title">You are not currently in any department, wait till you get assigned</h6>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="createForm" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="addUserModalLabel">Creating Department User</h5>
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

<div class="modal fade" id="sendContactMessageModal" tabindex="-1" aria-labelledby="sendContactMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
        <form id="messageContactForm" enctype="multipart/form-data">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="sendMessageTaskLabel"></h5>
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
function page(){
    $('#page').load(location.href + " #page > *", function() {
        initializeCharts();
    });
}

$(document).ready(function() {
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

    $(document).on('click', '#createForm button[name="createSubmit"]', function() {
        $.ajax({
            url: '{{ route("observer.department.account") }}',
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

    initializeCharts();
    function initializeCharts() {
        let deptHeadData = $('.barChartDept').data('dept-head');
        let deptMemberData = $('.barChartDeptMem').data('dept-member');

        // Destroy existing charts
        Chart.getChart('barChart_${user}')?.destroy();

        if (deptHeadData) {
            deptHeadData.forEach(head => {
                createChart(head, 'barChart_');
            });
        }

        if (deptMemberData) {
            deptMemberData.forEach(member => {
                createChart(member, 'barChart_');
            });
        }
    }

    function createChart(data, prefix) {
        var isParticipating = data.isParticipating;
        var user = data.user_id;
        var design = $(`#barChartDisplay_${user}`);

        if (isParticipating) {
            var totalDuration = data.totalDuration || {};

            var activeTimeSec = timeToSeconds(totalDuration.Active);
            var idleTimeSec = timeToSeconds(totalDuration.Idle);
            var awayTimeSec = timeToSeconds(totalDuration.Away);
            var overtimeTimeSec = timeToSeconds(totalDuration.Overtime);
            var totalSumSec = activeTimeSec + idleTimeSec + awayTimeSec + overtimeTimeSec;

            var formattedActive = formatStatisticTime(activeTimeSec);
            var formattedIdle = formatStatisticTime(idleTimeSec);
            var formattedAway = formatStatisticTime(awayTimeSec);
            var formattedOvertime = formatStatisticTime(overtimeTimeSec);
            var formattedTotal = formatStatisticTime(totalSumSec);


            var primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary').trim();
            var warningColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-warning').trim();
            var dangerColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-danger').trim();
            var successColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-success').trim();
            var secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary').trim();

            // Create a new canvas element if it doesn't exist
            if (!document.getElementById(`barChart_${user}`)) {
                var canvas = document.createElement('canvas');
                canvas.id = `barChart_${user}`;
                canvas.classList.add('barChartDept');
                design.html(canvas);
            }

            var ctx = document.getElementById(`barChart_${user}`).getContext('2d');
            var pieChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ["Active", "Idle", "Away", "Overtime", "Total"],
                    datasets: [{
                        label: 'Time Distribution',
                        data: [activeTimeSec, idleTimeSec, awayTimeSec, overtimeTimeSec, totalSumSec],
                        backgroundColor: [
                            primaryColor,
                            warningColor,
                            dangerColor,
                            successColor,
                            secondaryColor
                        ],
                        borderColor: [
                            primaryColor,
                            warningColor,
                            dangerColor,
                            successColor,
                            secondaryColor
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            labels: {
                                usePointStyle: true,
                                boxWidth: 10,
                                font: {
                                    size: 12
                                }
                            },
                            onClick: function(event, legendItem) {
                                var index = legendItem.datasetIndex;
                                var ci = this.chart;
                                var meta = ci.getDatasetMeta(index);
                                meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
                                ci.update();
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    const totalTime = context.raw;
                                    const hours = Math.floor(totalTime / 3600);
                                    const minutes = Math.floor((totalTime % 3600) / 60);
                                    const seconds = totalTime % 60;
                                    label += `${hours}h ${minutes}m ${seconds}s`;
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            design.html(`
                <div class="col-md-12 justify-content-center text-center align-items-center">
                    <h4 class="mb-0 fw-bold">This user is currently not starting or participating in this task</h4>
                </div>
            `);
        }
    }


    function timeToSeconds(timeString) {
        if (!timeString) return 0; // Handle undefined or empty values
        var parts = timeString.split(':').map(Number);
        return parts[0] * 3600 + parts[1] * 60 + parts[2]; // Convert HH:MM:SS to seconds
    }

    function formatStatisticTime(seconds){
        var hrs = Math.floor(seconds / 3600);
        var mins = Math.floor((seconds % 3600) / 60);
        var secs = seconds % 60;
        return `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
    }

    function formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        let result = [];
        if (hours > 0) result.push(`${hours} hrs`);
        if (minutes > 0) result.push(`${minutes} mins`);
        if (secs > 0) result.push(`${secs} secs`);

        return result.length > 0 ? result.join(' ') : '0 sec';
    }

    $(document).on('click', '.sendContactMessage', function() {
        var name = $(this).data('name');
        var user = $(this).data('user');
        $('#sendContactMessageModal').modal('show');
        $('#sendMessageTaskLabel').html(`Message ${name}`);
        $('#contact_id').val(user);
    });

    $('#attachFileButton').on('click', function () {
        $('#fileInput').click(); // Open file dialog
    });

    $('#messageContactForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('employee.chat.sendcontactmessage') }}`,
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
                        title: 'Successfully sent message'
                    });
                    $('#sendContactMessageModal').modal('hide');
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