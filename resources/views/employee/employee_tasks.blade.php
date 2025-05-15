@extends('employee.employee_dashboard')
@section('employee')

<div class="page-content">
    <div class="row">
        <div class="col-xl-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Task Count</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div id="taskDonut"></div>
                        </div>
                        <div class="col-md-4 text-center">
                            <div class="row d-flex align-items-center justify-content-center">
                                <div class="col-md-12 text-center">
                                    <div class="card shadow-lg">
                                        <div class="card-body">
                                            <h6 class="m-3"><b>Holding Task:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#9966ff">{{$tasks->count()}}</span></h6>
                                            <h6 class="m-3"><b>To-Check:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#3399ff">{{$tocheck->count()}}</span></h6>
                                            <h6 class="m-3"><b>Complete:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#66ffcc">{{$complete->count()}}</span></h6>
                                            <h6 class="m-3"><b>Total:</b> <span class="p-1 px-3 rounded-2 text-white text-hover bg-primary">{{$complete->count() + $tocheck->count() + $tasks->count()}}</span></h6>
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
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills mb-3 justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#ongoingTaskTab">Ongoing Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tocheckTaskTab">To Check Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#completeTaskTab">Complete Tab</a>
                </li>
            </ul>
            <div class="tab-content border border-0 bg-transparent shadow-none ">
                <div class="tab-pane fade active show" id="ongoingTaskTab" role="tabpanel" >
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card" id="holding-div">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                    <h6 class="card-title mb-0">On-Going Task</h6>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="holdingTable" class="table table-hover mb-0 text-center">
                                            <thead>
                                            <tr>
                                                <th class="pt-0">Task Name</th>
                                                <th class="pt-0">Assigned Date</th>
                                                <th class="pt-0">Due Date</th>
                                                <th class="pt-0">Progress</th>
                                                <th class="pt-0">Status</th>
                                                <th class="pt-0">Assigned To</th>
                                                <th class="pt-0">Assigned By</th>
                                                <th class="pt-0">Action</th>
                                            </tr>
                                            </thead>
                                            <tbody id="ongoing-tbody">
                                                @if(!empty($tasks))
                                                    @foreach($tasks as $row)
                                                        <tr id="holdingRow_{{$row->id}}" class="{{$row->status === 'Overdue' ? 'flicker' : ''}}">
                                                            <td >
                                                                {{ $row->title }}
                                                                @if($row->link_id !== null)
                                                                    <i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="{{$row->link_id}}"></i>
                                                                @endif
                                                            </td>
                                                            <td>{{$row->assigned}}</td>
                                                            <td>{{$row->due}}</td>
                                                            <td>
                                                                <div class="progress">
                                                                    <div class="progress-bar progress-bar-striped {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-success'}} progress-bar-animated" role="progressbar" style="width: {{$row->progress_percentage}}%;" aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$row->progress_percentage}}%</div>
                                                                </div>
                                                            </td>
                                                            <td><span class="badge {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}}">{{$row->status}}</span></td>
                                                            <td>{{$row->assigned_to}}</td>
                                                            <td>{{$row->assigned_by}}</td>
                                                            <td class="action-buttons">
                                                                <a class="btn btn-primary mx-1 btn-hover" href="{{ route('employee.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                                @if(($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime'))
                                                                <a class="btn btn-primary mx-1 btn-hover" href="{{ route('employee.etasks', ['task' => $row->id]) }}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>
                                                                @elseif($row->user_status === 'Emergency')
                                                                <button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                                                @elseif($row->user_status === 'Sleep')
                                                                <button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                                                @endif
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
                <div class="tab-pane fade" id="tocheckTaskTab" role="tabpanel" >
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">To-Check Task</h6>
                                </div>
                                <div class="table-responsive">
                                <table id="tocheckTable" class="table table-hover mb-0 text-center">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">Task Name</th>
                                        <th class="pt-0">Start Date</th>
                                        <th class="pt-0">Due Date</th>
                                        <th class="pt-0">Progress</th>
                                        <th class="pt-0">Status</th>
                                        <th class="pt-0">Assigned To</th>
                                        <th class="pt-0">Assigned By</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($tocheck))
                                            @foreach($tocheck as $row)
                                                <tr id="tocheckRow_{{$row->id}}" class="{{$row->status === 'Overdue' ? 'flicker' : ''}}">
                                                    <td>{{$row->title}}</td>
                                                    <td>{{$row->assigned}}</td>
                                                    <td>{{$row->due}}</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar progress-bar-striped {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-success'}} progress-bar-animated" role="progressbar" style="width: {{$row->progress_percentage}}%;" aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">{{$row->progress_percentage}}%</div>
                                                        </div>
                                                    </td>
                                                    <td><span class="badge {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}}">{{$row->status}}</span></td>
                                                    <td>{{$row->assigned_to}}</td>
                                                    <td>{{$row->assigned_by}}</td>
                                                    <td class="action-buttons">
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('employee.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
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
                <div class="tab-pane fade" id="completeTaskTab" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Completed Task</h6>
                                </div>
                                <div class="table-responsive">
                                <table id="completeTable" class="table table-hover mb-0  text-center">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">Task Name</th>
                                        <th class="pt-0">Status</th>
                                        <th class="pt-0">Assigned To</th>
                                        <th class="pt-0">Assigned By</th>
                                        <th class="pt-0">Approved By</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($complete))
                                            @foreach($complete as $row)
                                                <tr id="completeRow_{{$row->id}}" class="{{$row->status === 'Overdue' ? 'flicker' : ''}}">
                                                    <td>{{$row->title}}</td>
                                                    <td><span class="badge {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}}">{{$row->status}}</span></td>
                                                    <td>{{$row->assigned_to}}</td>
                                                    <td>{{$row->assigned_by}}</td>
                                                    <td>{{$row->approved_by}}</td>
                                                    <td class="action-buttons">
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('employee.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('employee.ptasks', ['task' => $row->id]) }}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
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
            </div>
        </div>
    </div>
</div>

<script>


$(document).ready(function(){

    Morris.Donut({
        element: 'taskDonut',  // This should match the ID of the container
        data: [
            { label: "Holding", value: {{$tasks->count()}} },
            { label: "To Check", value: {{$tocheck->count()}} },
            { label: "Complete", value: {{$complete->count()}} }
        ],
        colors: [ '#9966ff', '#3399ff', '#66ffcc'],  // Optional: Custom colors
        resize: true  // Ensures the chart is responsive
    });

    feather.replace();


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

    var successMessage = "{{ session('success') }}";

    if (successMessage) {
        Toast.fire({
            icon: 'success',
            title: 'Successfully Submit',
            text: successMessage
        });
    }

    let lastUpdate = null;

    function reloadOngoingDiv() {
        $.ajax({
            url: "{{ route('reloademp.ongoing.div') }}",
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

                    if (!$.fn.DataTable.isDataTable('#holdingTable')) {
                        $('#holdingTable').DataTable({
                            "aLengthMenu": [
                            [10, 30, 50, -1],
                            [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                            search: ""
                            }
                        });

                        $('#holdingTable').each(function() {
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

                    const table = $('#holdingTable').DataTable();
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
                        const table = $('#holdingTable').DataTable();
                        response.newTasks.forEach(task => {
                            addRowToTable(task);
                        });
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#holdingTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#holdingRow_${taskId}`); // Find the row by ID
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

                        const table = $('#holdingTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#holdingRow_${task.id}`); // Find the row by ID

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

    let lastToCheckUpdate = null;

    function reloadToCheckDiv() {
        $.ajax({
            url: "{{ route('reloademp.tocheck.div') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastToCheckUpdate: JSON.stringify(lastToCheckUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire table (if needed)
                    lastToCheckUpdate = response.lastToCheckUpdate;

                    if (!$.fn.DataTable.isDataTable('#tocheckTable')) {
                        $('#tocheckTable').DataTable({
                            "aLengthMenu": [
                                [10, 30, 50, -1],
                                [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                                search: ""
                            }
                        });

                        $('#tocheckTable').each(function() {
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

                    const table = $('#tocheckTable').DataTable();
                    table.clear().draw();

                    if (response.tasks && response.tasks.length > 0) {
                        response.tasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                task.assigned,
                                task.due,
                                `
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
                                </div>
                                `,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `tocheckRow_${task.id}`;

                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // You can reload the entire table or initialize it here
                } else if (response.status === 'count_changed') {
                    // Handle new tasks and deleted tasks
                    lastToCheckUpdate = response.lastToCheckUpdate;

                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        const table = $('#tocheckTable').DataTable();
                        response.newTasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                task.assigned,
                                task.due,
                                `
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
                                </div>
                                `,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `tocheckRow_${task.id}`;

                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#tocheckTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#tocheckRow_${taskId}`); // Find the row by ID
                            if (row.length) {
                                row.remove().draw(false); // Remove the row and redraw the table
                            }
                        });
                    }
                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastToCheckUpdate = response.lastToCheckUpdate;

                    if (response.task) {
                        const task = response.task;

                        const table = $('#tocheckTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#tocheckRow_${task.id}`); // Find the row by ID

                        if (row.length) {
                            // Update the row's data
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            row.data([
                                task.title,
                                task.assigned,
                                task.due,
                                `
                                <div class="progress">
                                    <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
                                </div>
                                `,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false); // Update the row and redraw the table
                        }

                        // Add or remove the flicker class
                        const rowNode = row.node();


                        // Reinitialize Feather Icons
                        feather.replace();
                    }
                } else if (response.status === 'no_changes') {
                    lastToCheckUpdate = response.lastToCheckUpdate;

                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadToCheckDiv, 1000);

    let lastCompleteUpdate = null;

    function reloadComplete() {
        $.ajax({
            url: "{{ route('reloademp.complete.div') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastCompleteUpdate: JSON.stringify(lastCompleteUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire table (if needed)
                    lastCompleteUpdate = response.lastCompleteUpdate;

                    if (!$.fn.DataTable.isDataTable('#completeTable')) {
                        $('#completeTable').DataTable({
                            "aLengthMenu": [
                                [10, 30, 50, -1],
                                [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                                search: ""
                            }
                        });

                        $('#completeTable').each(function() {
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

                    const table = $('#completeTable').DataTable();
                    table.clear().draw();

                    if (response.tasks && response.tasks.length > 0) {
                        response.tasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('employee.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `completeRow_${task.id}`;

                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // You can reload the entire table or initialize it here
                } else if (response.status === 'count_changed') {
                    // Handle new tasks and deleted tasks
                    lastCompleteUpdate = response.lastCompleteUpdate;

                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        const table = $('#completeTable').DataTable();
                        response.newTasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('employee.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `completeRow_${task.id}`;

                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#completeTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#completeRow_${taskId}`); // Find the row by ID
                            if (row.length) {
                                row.remove().draw(false); // Remove the row and redraw the table
                            }
                        });
                    }
                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastCompleteUpdate = response.lastCompleteUpdate;

                    if (response.task) {
                        const task = response.task;

                        const table = $('#completeTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#completeRow_${task.id}`); // Find the row by ID

                        if (row.length) {
                            // Update the row's data
                            let taskId = task.id;
                            let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('employee.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            row.data([
                                task.title,
                                `<span class="badge bg-primary">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false); // Update the row and redraw the table
                        }
                        const rowNode = row.node();

                        feather.replace();
                    }
                } else if (response.status === 'no_changes') {
                    lastCompleteUpdate = response.lastCompleteUpdate;

                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadComplete, 1000);

    function addRowToTable(task){
        const table = $('#holdingTable').DataTable();
        let taskId = task.id; // Get task ID dynamically
        let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        let editUrl = "{{ route('employee.etasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);

        // Add the new row to the DataTable
        const rowNode = table.row.add([
            task.title + (task.link_id !== null
                ? `<i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="${task.link_id}"></i>`
                : ''
            ),
            task.assigned,
            task.due,
            `
            <div class="progress">
                <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
            </div>
            `,
            `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
            task.assigned_to,
            task.assigned_by,
            `
            <td class="action-buttons">
                <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                ${task.checker == true && (task.user_status !== 'Emergency' && task.user_status !== 'Sleep' && task.user_status !== 'Request Overtime') ? '<a class="btn btn-primary mx-1 btn-hover" href="'+editUrl+'"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>' : ''}
                ${task.checker == true && (task.user_status === 'Emergency') ? `<button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                ${task.checker == true && (task.user_status === 'Sleep') ? `<button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
            </td>
            `
        ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
        rowNode.id = `holdingRow_${task.id}`;
        // Initialize Feather icons for the new row
        feather.replace();
    }

    function updateTableRow(row, task) {
        const formattedDueDate = formatDate(task.created_at);
        let taskId = task.id;
        let viewUrl = "{{ route('employee.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        let editUrl = "{{ route('employee.etasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
        row.data([
            task.title + (task.link_id !== null
                ? `<i data-feather="link" id="viewLinkedTask" class="text-primary icon-wiggle ms-2" style="width: 17px; height: 17px;" data-task="${task.link_id}"></i>`
                : ''
            ),
            task.assigned,
            task.due,
            `<div class="progress">
                <div class="progress-bar progress-bar-striped ${task.status === 'Overdue' ? 'bg-danger' : 'bg-success'} progress-bar-animated" role="progressbar" style="width: ${task.progress_percentage}%;" aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">${task.progress_percentage}%</div>
            </div>`,
            `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
            task.assigned_to,
            task.assigned_by,
            `<td class="action-buttons">
                <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                ${task.checker == true && (task.user_status !== 'Emergency' && task.user_status !== 'Sleep' && task.user_status !== 'Request Overtime') ? '<a class="btn btn-primary mx-1 btn-hover" href="'+editUrl+'"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>' : ''}
                ${task.checker == true && (task.user_status === 'Emergency') ? `<button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                ${task.checker == true && (task.user_status === 'Sleep') ? `<button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
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

    let token = $('meta[name="csrf-token"]').attr('content');

    $(document).on('click', '#viewLinkedTask', function() {
        var task = $(this).data('task');
        window.location.href = `/employee/lvtasks/${task}`;
    });

    $(document).on('click', '#cancelEmergency', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to cancel your emergency?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to cancel it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `/employee/etasks/${task}`;
            } else {
                pageContainer();
            }
        });
    })

    $(document).on('click', '#requestOvertime', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to request overtime?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to request it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("employee.tasks.requestovertimetask") }}',
                    method: 'POST',
                    data: {
                        task: task
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully requested'
                            });
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            pageContainer();
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }
                });
            } else {
                pageContainer();
            }
        });
    })
});
</script>
@endsection