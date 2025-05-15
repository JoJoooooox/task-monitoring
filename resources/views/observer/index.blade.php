@extends('observer.observer_dashboard')
@section('observer')
@php
use App\Models\Task_solo;
use App\Models\Task_group;
@endphp
<div class="page-content" id="page">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <div>
            <h4 class="mb-3 mb-md-0">Welcome to Manager Dashboard "{{ Auth::user()->name }}"</h4>
        </div>
        <div class="d-flex align-items-center flatpickr flex-wrap text-nowrap">
            <div class="input-group w-100 me-2 mb-2 mb-md-0">
              <span class="input-group-text input-group-addon bg-transparent border-primary" data-toggle><i data-feather="calendar" class="text-primary"></i></span>
              <input id="dateTimeNow" type="text" class="form-control bg-transparent border-primary" placeholder="Loading . . . " disabled>
            </div>
        </div>
    </div>
    <div class="row-container">
        <div class="row first-row">
            <div class="col-xl-6 grid-margin stretch-card">
                <div class="card div-hover">
                    <div class="card-body">
                        <h6 class="card-title">Holding Employee / Intern</h6>
                        <div id="userDonut"></div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6 grid-margin stretch-card">
                <div class="card div-hover">
                    <div class="card-body">
                        <h6 class="card-title">Task Count</h6>
                        <div id="taskDonut"></div>
                    </div>
                </div>
            </div>
        </div>
        <button class="btn btn-primary toggle-btn-rank mb-3" data-bs-toggle="collapse" data-bs-target="#secondRowRank">
            Hide Ranking <i data-feather="chevron-up"></i>
        </button>
        <div id="secondRowRank" class="row collapse show">
            @php
                $rankColors = ['gold', 'silver', 'bronze'];
            @endphp
            <div class="col-xl-12 mb-3">
                <h6 class="card-title">Department Ranking</h6>
                <div class="row g-1 d-flex flex-nowrap overflow-auto p-3">
                    @if($PerDepartment->isNotEmpty())
                        @foreach($PerDepartment as  $row)
                            @php
                                $profile = $row->photo;
                                $colSize = count($PerDepartment) === 1 ? 'col-12' : (count($PerDepartment) === 2 ? 'col-6' : 'col-4');
                            @endphp
                            <div class="{{ $colSize }}">
                                <div class="border border-primary profile-card">
                                    <div class="rank-badge rank-gold">#1</div>
                                    <img src="{{ $profile ? url('upload/photo_bank/' . $profile) : url('upload/nophoto.jfif') }}"
                                        alt="Profile Image" class="profile-img shadow-sm border border-primary">
                                    <div class="card-body-profile">
                                        <h5>{{$row->name}}</h5>
                                        <p>Department: {{$row->department_name}}</p>
                                        <h6>Count: {{$row->task_count}}</h6>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <div class="border border-primary profile-card-empty">
                                <h4>There's no existing in department ranking</h4>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @if(Auth::user()->department)
            <div class="col-xl-4 mb-3">
                <h6 class="card-title">Daily Ranking</h6>
                <div class="row g-1">
                    @if($dailyTop3->isNotEmpty())
                        @foreach($dailyTop3 as $index => $daily)
                            @php
                                $daily->rank = $index + 1;
                                $profile = $daily->photo;
                                $colSize = count($dailyTop3) === 1 ? 'col-12' : (count($dailyTop3) === 2 ? 'col-6' : 'col-4');
                                $badgeColor = $rankColors[$index] ?? 'blue'; // Default color if more than 3
                            @endphp
                            <div class="{{ $colSize }}">
                                <div class="border border-primary profile-card">
                                    <div class="rank-badge rank-{{ strtolower($rankColors[$index] ?? 'blue') }}" style="background-color: {{ $badgeColor }};">#{{$daily->rank}}</div>
                                    <img src="{{ $profile ? url('upload/photo_bank/' . $profile) : url('upload/nophoto.jfif') }}"
                                        alt="Profile Image" class="profile-img shadow-sm border border-primary">
                                    <div class="card-body-profile">
                                        <h5>{{$daily->name}}</h5>
                                        <p>Department: {{$daily->department_name}}</p>
                                        <h6>Count: {{$daily->task_count}}</h6>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <div class="border border-primary profile-card-empty">
                                <h4>There's no existing in daily ranking</h4>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xl-4 mb-3">
                <h6 class="card-title">Weekly Ranking</h6>
                <div class="row g-1">
                    @if($weeklyTop3->isNotEmpty())
                        @foreach($weeklyTop3 as $index => $weekly)
                            @php
                                $weekly->rank = $index + 1;
                                $profile = $weekly->photo;
                                $colSize = count($weeklyTop3) === 1 ? 'col-12' : (count($weeklyTop3) === 2 ? 'col-6' : 'col-4');
                                $badgeColor = $rankColors[$index] ?? 'blue'; // Default color if more than 3
                            @endphp
                            <div class="{{ $colSize }}">
                                <div class="border border-primary profile-card">
                                    <div class="rank-badge rank-{{ strtolower($rankColors[$index] ?? 'blue') }}" style="background-color: {{ $badgeColor }};">#{{$weekly->rank}}</div>
                                    <img src="{{ $profile ? url('upload/photo_bank/' . $profile) : url('upload/nophoto.jfif') }}"
                                        alt="Profile Image" class="profile-img shadow-sm border border-primary">
                                    <div class="card-body-profile">
                                        <h5>{{$weekly->name}}</h5>
                                        <p>Department: {{$weekly->department_name}}</p>
                                        <h6>Count: {{$weekly->task_count}}</h6>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <div class="border border-primary profile-card-empty">
                                <h4>There's no existing in weekly ranking</h4>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-xl-4 mb-3">
                <h6 class="card-title">Monthly Ranking</h6>
                <div class="row g-1">
                    @if($monthlyTop3->isNotEmpty())
                        @foreach($monthlyTop3 as $index => $monthly)
                            @php
                                $monthly->rank = $index + 1;
                                $profile = $monthly->photo;
                                $colSize = count($monthlyTop3) === 1 ? 'col-12' : (count($monthlyTop3) === 2 ? 'col-6' : 'col-4');
                                $badgeColor = $rankColors[$index] ?? 'blue'; // Default color if more than 3
                            @endphp
                            <div class="{{ $colSize }}">
                                <div class="border border-primary profile-card">
                                    <div class="rank-badge rank-{{ strtolower($rankColors[$index] ?? 'blue') }}" style="background-color: {{ $badgeColor }};">#{{$monthly->rank}}</div>
                                    <img src="{{ $profile ? url('upload/photo_bank/' . $profile) : url('upload/nophoto.jfif') }}"
                                        alt="Profile Image" class="profile-img shadow-sm border border-primary">
                                    <div class="card-body-profile">
                                        <h5>{{$monthly->name}}</h5>
                                        <p>Department: {{$monthly->department_name}}</p>
                                        <h6>Count: {{$monthly->task_count}}</h6>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12 text-center">
                            <div class="border border-primary profile-card-empty">
                                <h4>There's no existing in monthly ranking</h4>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    <div class="row">
        @if(Auth::user()->department)
        <div class="col-12 stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h6 class="card-title mb-0">On-Going Task</h6>
                    </div>
                    <div class="table-responsive">
                    <table id="ongoingTable" class="table table-hover mb-0 text-center">
                        <thead>
                        <tr>
                            <th class="pt-0">Task Name</th>
                            <th class="pt-0">Created Date</th>
                            <th class="pt-0">Assigned Date</th>
                            <th class="pt-0">Due Date</th>
                            <th class="pt-0">Progress</th>
                            <th class="pt-0">Status</th>
                            <th class="pt-0">User Status</th>
                            <th class="pt-0">Assigned To</th>
                            <th class="pt-0">Assigned By</th>
                            <th class="pt-0">Action</th>
                        </tr>
                        </thead>
                        <tbody id="ongoing-tbody">
                            @if(!empty($myOngoing))
                                @foreach($myOngoing as $row)
                                    <tr id="ongoingRow_{{$row->id}}" class="{{$row->status === 'Overdue' ? 'flicker' : ''}}">
                                        <td >
                                            {{ $row->title }}
                                            @if($row->link_id !== null)
                                                <i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="{{$row->link_id}}"></i>
                                            @endif
                                        </td>
                                        <td>{{$row->created_at}}</td>
                                        <td>{{$row->assigned}}</td>
                                        <td>{{$row->due}}</td>
                                        <td>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-striped {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-success'}} progress-bar-animated" role="progressbar" style="width: {{$row->progress_percentage}}%;" aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$row->progress_percentage}}%</div>
                                            </div>
                                        </td>
                                        <td><span class="badge {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}}">{{$row->status}}</span></td>
                                        <td><span class="badge
                                            {{$row->user_status === 'Active' ? 'bg-primary' :
                                            ($row->user_status === 'Away' ? 'bg-danger' :
                                            ($row->user_status === 'Idle' ? 'bg-warning' :
                                            ($row->user_status === 'Emergency' ? 'bg-dark' :
                                            ($row->user_status === 'Sleep' ? 'bg-info' : 'bg-secondary'))))}}">
                                            {{$row->user_status}}
                                        </span></td>
                                        <td>{{$row->assigned_to}}</td>
                                        <td>{{$row->assigned_by}}</td>
                                        <td class="action-buttons">
                                            <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
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
        @endif
    </div> <!-- row -->

</div>

<script>
$(document).ready(function() {
    function pageContainer(){
        $('#page').load(location.href + " #page > *", function() {

            if ($('#userDonut').length) {
                $('#userDonut').empty(); // Prevent duplicate rendering
                Morris.Donut({
                    element: 'userDonut',
                    data: [
                        { label: "Employee", value: {{Auth::user()->department ? $employeeCount : 0}} },
                        { label: "Intern", value: {{Auth::user()->department ? $internCount : 0}} }
                    ],
                    colors: ['#66ffcc', '#ffff66'],
                    resize: true
                });
            } else {
                console.error("Error: #taskDonut element not found.");
            }


            if ($('#taskDonut').length) {
                $('#taskDonut').empty(); // Prevent duplicate rendering
                Morris.Donut({
                    element: 'taskDonut',
                    data: [
                        { label: "To Assign", value: {{ Auth::user()->department ? $temp->count() : 0}} },
                        { label: "On-Going", value: {{ Auth::user()->department ? $ongoing->count() : 0}} },
                        { label: "To Check", value: {{ Auth::user()->department ? $tocheck->count() : 0}} },
                        { label: "Complete", value: {{ Auth::user()->department ?$complete->count() : 0}} },
                        { label: "Distributed", value: {{ Auth::user()->department ? $dist->count() : 0}} },
                        { label: "Linked", value: {{ Auth::user()->department ?$linked->count() : 0}} }
                    ],
                    colors: ['#ff33cc', '#9966ff', '#3399ff', '#66ffcc', '#ffcc66', '#ff6666'],
                    resize: true
                });
            } else {
                console.error("Error: #taskDonut element not found.");
            }

            feather.replace();
        });
    }
    pageContainer();

    let lastUpdate = null;

    function reloadOngoingDiv() {
        $.ajax({
            url: "{{ route('reloadobs.myongoing.div') }}",
            type: "POST",

					noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastUpdate: JSON.stringify(lastUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire table (if needed)
                    lastUpdate = response.lastUpdate;

                    if (!$.fn.DataTable.isDataTable('#ongoingTable')) {
                        $('#ongoingTable').DataTable({
                            "aLengthMenu": [
                            [10, 30, 50, -1],
                            [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                            search: ""
                            }
                        });

                        $('#ongoingTable').each(function() {
                            var datatable = $(this);
                            // SEARCH - Add the placeholder for Search and Turn this into in-line form control
                            var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
                            search_input.attr('placeholder', 'Search');
                            search_input.removeClass('form-control-sm');
                            // LENGTH - Inline-Form control
                            var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
                            length_sel.removeClass('form-control-sm');
                        });
                    }

                    const table = $('#ongoingTable').DataTable();
                    table.clear().draw();

                    if (response.tasks && response.tasks.length > 0) {
                        response.tasks.forEach(task => {
                            addRowToTable(task);
                        });
                    }
                    // You can reload the entire table or initialize it here
                } else if (response.status === 'count_changed') {
                    // Handle new tasks and deleted tasks
                    lastUpdate = response.lastUpdate;
                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        const table = $('#ongoingTable').DataTable();
                        response.newTasks.forEach(task => {
                            addRowToTable(task);
                        });
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#ongoingTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#ongoingRow_${taskId}`); // Find the row by ID
                            if (row.length) {
                                row.remove().draw(false); // Remove the row and redraw the table
                            }
                        });
                    }
                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastUpdate = response.lastUpdate;
                    if (response.task) {
                        const task = response.task;
                        const formattedDueDate = formatDate(task.created_at);

                        const table = $('#ongoingTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#ongoingRow_${task.id}`); // Find the row by ID

                        if (row.length) {
                            // Update the row's data
                            updateTableRow(row, task)
                        } else {
                            row.remove().draw(false);
                        }

                    }
                } else if (response.status === 'no_changes') {
                    lastUpdate = response.lastUpdate;
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadOngoingDiv, 1000);

    function addRowToTable(task){

        const table = $('#ongoingTable').DataTable();
        const formattedDueDate = formatDate(task.created_at);
        let taskId = task.id; // Get task ID dynamically
        let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        let editUrl = "{{ route('observer.etasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);

        // Add the new row to the DataTable
        const rowNode = table.row.add([
            task.title + (task.link_id !== null
                ? `<i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="${task.link_id}"></i>`
                : ''
            ),
            formattedDueDate,
            task.assigned,
            task.due,
            `
            <div class="progress">
                <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
            </div>
            `,
            `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
            `<span class="badge ${task.user_status === 'Active' ? 'bg-primary' :
                (task.user_status === 'Away' ? 'bg-danger' :
                (task.user_status === 'Idle' ? 'bg-warning' :
                (task.user_status === 'Emergency' ? 'bg-dark' :
                (task.user_status === 'Sleep' ? 'bg-info' : 'bg-secondary'))))}">
                ${task.user_status}
            </span>`,
            task.assigned_to,
            task.assigned_by,
            `
            <td class="action-buttons">
                <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
            </td>
            `
        ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
        rowNode.id = `ongoingRow_${task.id}`;
        // Initialize Feather icons for the new row
        feather.replace();
    }

    function updateTableRow(row, task) {
        const formattedDueDate = formatDate(task.created_at);
        let taskId = task.id;
        let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        let editUrl = "{{ route('observer.etasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        row.data([
            task.title + (task.link_id !== null
                ? `<i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="${task.link_id}"></i>`
                : ''
            ),
            formattedDueDate,
            task.assigned,
            task.due,
            `<div class="progress">
                <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
            </div>`,
            `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
            `<span class="badge ${task.user_status === 'Active' ? 'bg-primary' :
                (task.user_status === 'Away' ? 'bg-danger' :
                (task.user_status === 'Idle' ? 'bg-warning' :
                (task.user_status === 'Emergency' ? 'bg-dark' :
                (task.user_status === 'Sleep' ? 'bg-info' : 'bg-secondary'))))}">
                ${task.user_status}
            </span>`,
            task.assigned_to,
            task.assigned_by,
            `<td class="action-buttons">
                <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
            </td>`
        ]).draw(false);

        const rowNode = row.node();
        if (task.status === 'Overdue') {
            $(rowNode).addClass('flicker');
        } else {
            $(rowNode).removeClass('flicker');
        }

        // Reinitialize Feather Icons
        feather.replace();
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A'; // Handle null or undefined values

        const date = new Date(dateString);
        if (isNaN(date.getTime())) {
            console.error('Invalid date:', dateString);
            return 'Invalid Date';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    }

    setInterval(function() {
        var now = new Date();
        var dateString = now.toLocaleDateString(); // Gets the current date
        var timeString = now.toLocaleTimeString(); // Gets the current time
        $('#dateTimeNow').val(dateString + ' ' + timeString);
    }, 1000);

});

$(document).ready(function () {
    function updateButtonText() {
        let isExpanded = $(".toggle-btn-rank").attr("aria-expanded") === "true";

        let buttonText = isExpanded
            ? 'Hide Ranking <i data-feather="chevron-up"></i>'
            : 'Show Ranking <i data-feather="chevron-down"></i>';

        $(".toggle-btn-rank").html(buttonText);
        setTimeout(() => feather.replace(), 50);
    }

    // Attach event listener for the button
    $(document).on("click", ".toggle-btn-rank", function () {
        setTimeout(updateButtonText, 50); // Delay to allow collapse animation
    });

    // Initialize button text on page load
    setTimeout(updateButtonText, 50);

    // Auto-collapse on mobile, show on desktop
    function handleResize() {
        let isExpanded = $(".toggle-btn-rank").attr("aria-expanded") === "true";

        let buttonText = isExpanded
            ? 'Hide Ranking <i data-feather="chevron-up"></i>'
            : 'Show Ranking <i data-feather="chevron-down"></i>';

        $(".toggle-btn-rank").html(buttonText);
        if ($(window).width() <= 768) {
            $("#secondRowRank").collapse("hide");

        } else {
            $("#secondRowRank").collapse("show");
        }
        setTimeout(() => feather.replace(), 50);
    }

    handleResize();
    $(window).on("resize", handleResize);
});
</script>
@endsection