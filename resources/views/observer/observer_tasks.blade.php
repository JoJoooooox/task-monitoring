@extends('observer.observer_dashboard')
@section('observer')

@php
use App\Models\Task_solo;
use App\Models\Task_group;
@endphp
<div class="page-content">
    @if(Auth::user()->department)
    <div id="page">
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
                                    <div class="col-md-12 text-center grid-margin stretch-card">
                                        <div class="card shadow-lg">
                                            <div class="card-body">
                                                <h6 class="m-3"><b>To Assign:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#ff33cc">{{$temp->count()}}</span></h6>
                                                <h6 class="m-3"><b>On-Going:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#9966ff">{{$ongoing->count()}}</span></h6>
                                                <h6 class="m-3"><b>To-Check:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#3399ff">{{$tocheck->count()}}</span></h6>
                                                <h6 class="m-3"><b>Complete:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#66ffcc">{{$complete->count()}}</span></h6>
                                                <h6 class="m-3"><b>Distributed:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#ffcc66">{{$dist->count()}}</span></h6>
                                                <h6 class="m-3"><b>Linked:</b> <span class="p-1 px-3 rounded-2 text-white text-hover" style="background-color:#ff6666">{{$linked->count()}}</span></h6>
                                                <h6 class="m-3"><b>Total:</b> <span class="p-1 px-3 rounded-2 text-white bg-primary text-hover">{{$complete->count() + $temp->count() + $ongoing->count() + $tocheck->count() + $linked->count() + $dist->count()}}</span></h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 text-center grid-margin stretch-card">
                                        <div class="card shadow-lg">
                                            <div class="card-body">
                                                <h6 class="card-title">Action</h6>
                                                <div class="d-grid gap-2">
                                                    <button type="button" class="btn btn-primary btn-hover" id="createTask">Create Task</button>
                                                    <button type="button" class="btn btn-primary btn-hover" data-dept="{{$dept_info->id}}" id="workingTimeDept">Working Time Settings</button>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" role="switch" id="overtimeAutomation" {{$dept_info->overtime_auto === 'On' ? 'checked' : ''}}>
                                                        <label class="form-check-label" for="overtimeAutomation">Overtime Automation Accept</label>
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
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h6 class="card-title mb-0">Task to Assign</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-center" id="taskTable">
                            <thead>
                            <tr>
                                <th class="pt-0">Department</th>
                                <th class="pt-0">Task Name</th>
                                <th class="pt-0">Task Type</th>
                                <th class="pt-0">Created By</th>
                                <th class="pt-0">Pages</th>
                                <th class="pt-0">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(!empty($temp))
                                    @foreach($temp as $row)
                                    <tr>
                                        <td>{{$row->department_name}}</td>
                                        <td>{{$row->title}}</td>
                                        <td>{{$row->type}}</td>
                                        <td>{{$row->created_by}}</td>
                                        <td>{{$row->pages}}</td>
                                        <td class="action-buttons">
                                            @php
                                                $btnId = ($row->type == 'Solo') ? 'btnSoloAssign' : (($row->type == 'Group') ? 'btnGroupAssign' : '');
                                                $btnAutoId = ($row->type == 'Solo') ? 'btnSoloAutoAssign' : (($row->type == 'Group') ? 'btnGroupAutoAssign' : '');
                                            @endphp
                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="{{$btnId}}" data-temp="{{$row->id}}" data-name="{{$row->title}}" data-dept="{{$row->department_id}}" @if($row->pages == 0) disabled @endif><i data-feather="arrow-right" class="icon-sm icon-wiggle"></i></button>
                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="{{$btnAutoId}}" data-temp="{{$row->id}}" data-name="{{$row->title}}" data-dept="{{$row->department_id}}" @if($row->pages == 0) disabled @endif><i data-feather="fast-forward" class="icon-sm icon-wiggle"></i></button>
                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="viewTemp" data-temp="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="editTemp" data-temp="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="archiveTemp" data-temp="{{$row->id}}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills mb-3 justify-content-center">
                <li class="nav-item">
                    <a class="nav-link active" data-bs-toggle="tab" href="#distributedTab">Distributed & Link Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#ongoingTaskTab">Ongoing Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#tocheckTaskTab">To Check Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#completeTaskTab">Complete Tab</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="tab" href="#archivedTaskTab">Archived Tab</a>
                </li>
            </ul>
            <div class="tab-content border border-0 bg-transparent shadow-none ">
                <div class="tab-pane fade active show" id="distributedTab" role="tabpanel" >
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card" id="divBtnReqTab">
                            <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Request Tab (Distributed Task)</h6>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="button" id="distReqCounter" class="btn btn-primary position-relative" data-bs-toggle="collapse" data-bs-target="#collapseDistReqExample" aria-expanded="false" aria-controls="collapseDistReqExample">
                                            <i data-feather="file-text" class="icon-sm icon-wiggle"></i> Distribution Request
                                            @if($distReq > 0)
                                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                                {{$distReq}}
                                                <span class="visually-hidden">unread messages</span>
                                            </span>
                                            @endif
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="collapse" id="collapseDistReqExample">
                            <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                                <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Request Distributed Task</h6>
                                    </div>
                                    <div class="d-grid gap-2 mb-3">
                                        <button type="button" class="btn btn-primary btn-hover" id="settingsAccept" data-dept="{{$dept_id}}"><i data-feather="settings" class="icon-sm icon-wiggle"></i> Automation Accept Request Settings</button>
                                    </div>
                                    <div class="table-responsive">
                                        <table id="distReqTable" class="table table-hover mb-0 text-center">
                                            <thead>
                                                <tr>
                                                    <th class="pt-0">Task Name</th>
                                                    <th class="pt-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="distReq-tbody">
                                                @if(!empty($distReqView))
                                                    @foreach($distReqView as $row)
                                                        <tr id="distReqRow_{{$row->id}}">
                                                            <td>{{$row->title}}</td>
                                                            <td class="action-buttons">
                                                                <button class="btn btn-primary mx-1 btn-hover" id="approveTaskDistribution" data-dist="{{$row->id}}"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                                                <button class="btn btn-primary mx-1 btn-hover" id="declineTaskDistribution" data-dist="{{$row->id}}"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
                                                                <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.lvtasks', ['task' => $row->task_id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-6 d-grid">
                                            <button class="btn btn-success" id="approveAllTaskDistribution"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i> Accept All</button>
                                        </div>
                                        <div class="col-6 d-grid">
                                            <button class="btn btn-danger"  id="declineAllTaskDistribution"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i> Decline All</button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Automation Tab (Link To New Task)</h6>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary btn-hover" id="settingsLinkNew" data-dept="{{$dept_id}}"><i data-feather="settings" class="icon-sm icon-wiggle"></i> Automation Link To New Task Settings</button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card" id="dist-div">
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                    <h6 class="card-title mb-0">Distributed Task</h6>
                                </div>
                                <div class="table-responsive">
                                    <table id="distTable" class="table table-hover mb-0 text-center">
                                        <thead>
                                            <tr>
                                                <th class="pt-0">Task Name</th>
                                                <th class="pt-0">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="dist-tbody">
                                            @if(!empty($dist))
                                                @foreach($dist as $row)
                                                    <tr id="distRow_{{$row->id}}">
                                                        <td>{{$row->title}}</td>
                                                        <td class="action-buttons">
                                                            <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="{{$row->id}}" data-name="{{$row->title}}" id="linkTaskBtn"><i data-feather="link" class="icon-sm icon-wiggle"></i></button>
                                                            <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                            <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.ptasks', ['task' => $row->id]) }}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                                            <button type="button" class="btn btn-primary mx-1 btn-hover archiveDistributeTask" data-task="{{$row->id}}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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
                <div class="tab-pane fade" id="ongoingTaskTab" role="tabpanel" >
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card" >
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                    <h6 class="card-title mb-0">Task Overtime Request</h6>
                                </div>
                                @if($requestOvertime->isNotEmpty())
                                <div class="row d-flex flex-nowrap gap-2 overflow-auto p-3" id="divBtnReqOvertimeTab">
                                        @foreach($requestOvertime as $row)
                                        <div id="OvertimeRow_{{$row->id}}" class="text-white {{$row->status === 'Overdue' ? 'bg-danger' : 'bg-primary'}} pt-2 pb-2 rounded-2 d-inline-block w-auto px-3">
                                            {{$row->title}}
                                            <span class="badge bg-white text-primary ms-2">Name: {{ $row->assigned_user_names }}</span>
                                            <button type="button" data-task="{{$row->id}}" class="btn btn-light border border-0 ms-3 acceptOvertimeBtn"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i> Accept</button>
                                            <button type="button" data-task="{{$row->id}}" class="btn btn-light border border-0 declineOvertimeBtn"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i> Decline</button>
                                        </div>
                                        @endforeach
                                </div>
                                @else
                                    <div class="row d-flex flex-nowrap gap-2 overflow-auto p-3" id="divBtnReqOvertimeTab">
                                        <h4 class="justify-content-center text-center align-items-center no-overtime-message">There's no existing overtime request right now</h4>
                                    </div>
                                @endif
                            </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card" id="ongoing-div">
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
                                            @if(!empty($ongoing))
                                                @foreach($ongoing as $row)
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
                                                            <button type="button" class="btn btn-primary mx-1 btn-hover" id="viewUserStatusStatistic" data-task="{{$row->id}}"><i data-feather="bar-chart-2" class="icon-sm icon-wiggle"></i></button>
                                                            @php
                                                                $check = false;

                                                                $solo = Task_solo::where('user_id', Auth::user()->id)->where('task_id', $row->id)->first();
                                                                if(!empty($solo)){
                                                                    $check = true;
                                                                }
                                                                $group = Task_group::where('user_id', Auth::user()->id)->where('task_id', $row->id)->first();
                                                                if(!empty($group)){
                                                                    $check = true;
                                                                }
                                                            @endphp
                                                            @if($check && ($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime'))
                                                            <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.etasks', ['task' => $row->id]) }}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>
                                                            @elseif($check && $row->user_status === 'Emergency')
                                                            <button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                                            @elseif($check && $row->user_status === 'Sleep')
                                                            <button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="{{$row->id}}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>
                                                            @endif
                                                            <button class="btn btn-primary mx-1 btn-hover" id="sendMessageOverdue" data-task="{{$row->id}}" data-name="{{$row->assigned_to}}" ><i data-feather="send" class="icon-sm icon-wiggle"></i></button>
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
                                <div class="row">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Automation Tab (To Check Task)</h6>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary btn-hover" id="settingsToCheck" data-dept="{{$dept_id}}"><i data-feather="settings" class="icon-sm icon-wiggle"></i> Automation Settings</button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
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
                                                        <button class="btn btn-primary mx-1 btn-hover" id="approveTask" data-task="{{$row->id}}"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                                        <button class="btn btn-primary mx-1 btn-hover" id="declineTask" data-task="{{$row->id}}"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
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
                    </div>
                </div>
                <div class="tab-pane fade" id="completeTaskTab" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                                        <h6 class="card-title mb-0">Automate Distribution Tab (Completed Task)</h6>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-primary btn-hover" id="settingsDistribute" data-dept="{{$dept_id}}"><i data-feather="settings" class="icon-sm icon-wiggle"></i> Automation Distribution Settings</button>
                                    </div>
                                </div>
                            </div>
                            </div>
                        </div>
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
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="{{$row->id}}" id="distributeTaskBtn"><i data-feather="corner-up-right" class="icon-sm icon-wiggle"></i></button>
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover archiveCompletedTask" data-task="{{$row->id}}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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
                <div class="tab-pane fade" id="archivedTaskTab" role="tabpanel">
                    <div class="row">
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Archived Task</h6>
                                </div>
                                <div class="table-responsive">
                                <table id="archivedTable" class="table table-hover mb-0  text-center">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">Task Name</th>
                                        <th class="pt-0">Assigned To</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($archived))
                                            @foreach($archived as $row)
                                                <tr>
                                                    <td>{{$row->title}}</td>
                                                    <td>{{$row->assigned_to}}</td>
                                                    <td class="action-buttons">
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="{{$row->id}}" id="retrieveTask"><i data-feather="folder-plus" class="icon-sm icon-wiggle"></i></button>
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.lvtasks', ['task' => $row->id]) }}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                        <a class="btn btn-primary mx-1 btn-hover" href="{{ route('observer.ptasks', ['task' => $row->id]) }}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover deleteTask" data-task="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
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
                        <div class="col-lg-12 col-xl-12 mb-3 stretch-card">
                            <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-baseline mb-2">
                                <h6 class="card-title mb-0">Archived Template</h6>
                                </div>
                                <div class="table-responsive">
                                <table id="archivedTempTable" class="table table-hover mb-0  text-center">
                                    <thead>
                                    <tr>
                                        <th class="pt-0">Template Name</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                        @if(!empty($archivedTemp))
                                            @foreach($archivedTemp as $row)
                                                <tr>
                                                    <td>{{$row->title}}</td>
                                                    <td class="action-buttons">
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover" data-temp="{{$row->id}}" id="retrieveTemp"><i data-feather="folder-plus" class="icon-sm icon-wiggle"></i></button>
                                                        <a class="btn btn-primary mx-1 btn-hover" href="javascript:;" id="viewTemp" data-temp="{{$row->id}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                                        <button type="button" class="btn btn-primary mx-1 btn-hover deleteTemp" data-temp="{{$row->id}}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
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
    @else
    <div class="row">
        <div class="col-12">
            <div class="m-5 text-center card">
                <div class="card-body">
                    <h6 class="card-title">You are not currently in any department, wait till you get assigned</h6>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
<!-- region Head Section -->
    <div class="modal fade" id="saveTemplateModal" tabindex="-1" aria-labelledby="saveTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="saveTemplateModalLabel">Save Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg">
                <div class="col-12 mb-3">
                    <label for="">Template Name</label>
                    <input type="text" class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="">Department</label>
                    <select class="form-select" aria-label="Default select example">
                        <option selected>Open this select menu</option>
                        <option value="">Empty</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="">Task Type</label>
                    <select class="form-select" aria-label="Default select example">
                        <option selected>Open this select menu</option>
                        <option value="solo">Solo</option>
                        <option value="group">Group</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary btn-hover">Save changes</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="assignSoloTaskModal" tabindex="-1" aria-labelledby="assignSoloTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="assignSoloTaskModalLabel">Assign Solo Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="assignSoloDisplay">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="assignGroupTaskModal" tabindex="-1" aria-labelledby="assignGroupTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="assignGroupTaskModalLabel">Assign Group Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="assignGroupDisplay">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="autoAssignSoloTaskModal" tabindex="-1" aria-labelledby="assignSoloTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="assignSoloTaskModalLabel">Assign Solo Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary mb-3">
                <div class="col-12 mb-3 text-center">
                    <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                    <span><b>Task automation will only assign a task if the user is not currently working on the same task and same type. For example</b>: </span><br>
                    <span>
                        If User 1 is already assigned to Task 1, they will not receive another "Solo" or "Group" task of the same type (Task 1).
                        However, they can be assigned a different task (Task 2).
                        Once Task 1 or Task 2 is completed, they become eligible for new task assignments.
                    </span>
                </div>
            </div>
            <div class="row modal-body-bg border border-primary" id="autoAssignSoloDisplay">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="autoAssignGroupTaskModal" tabindex="-1" aria-labelledby="assignGroupTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="assignGroupTaskModalLabel">Assign Group Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary mb-3">
                <div class="col-12 mb-3 text-center">
                    <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                    <span><b>Task automation will only assign a task if the user is not currently working on the same task and same type. For example</b>: </span><br>
                    <span>
                        If User 1 is already assigned to Task 1, they will not receive another "Solo" or "Group" task of the same type (Task 1).
                        However, they can be assigned a different task (Task 2).
                        Once Task 1 or Task 2 is completed, they become eligible for new task assignments.
                        <i data-feather="info" class="icon-wiggle"></i> Additional: Automation in group task will not work if the user is participating in other group task, if you still want to add the user in the new group task you can manually create one
                    </span>
                </div>
            </div>
            <div class="row modal-body-bg border border-primary" id="autoAssignGroupDisplay">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="createTaskModal" tabindex="-1" aria-labelledby="assignGroupTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Create Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="createTaskForm" method="POST">
            @csrf
        <div class="modal-body">
            <div class="row">
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label">Task Title</label>
                                <input type="text" name="title" class="form-control" id="title" aria-describedby="title">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="type" class="form-label">Task Type</label>
                                <select class="form-select" name="type" id="type">
                                    <option value="Solo">Solo</option>
                                    <option value="Group">Group</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="department" class="form-label">Department</label>
                                <select class="form-select" name="department_id" id="department">
                                    @if(!empty($department))
                                        @foreach($department as $dept)
                                        <option value="{{$dept->id}}">{{$dept->name}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="submitCreateTask">Save changes</button>
        </div>
        </form>
        </div>
    </div>
    </div>

    <div class="modal fade" id="editTemplateModal" tabindex="-1" aria-labelledby="editTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="editTemplateModalLabel">Edit Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="taskInformation">

            </div>
            <div class="row modal-body-bg mt-3 border border-primary" id="taskContainer">
                <div class="col-12 mb-3 d-grid">
                    <button class="btn btn-primary float-end" id="addTaskPage"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Add Page</button>
                    <div id="toggleDisplay">

                    </div>
                </div>
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs pageContainer" role="tablist">
                            </ul>
                            <div class="tab-content border border-top-0 p-3 contentContainer" id="myTabContent">
                            </div>
                        </div>
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

    <div class="modal fade" id="editFieldTaskModal" tabindex="-1" aria-labelledby="editFieldTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="editFieldTaskModalLabel">Edit Field</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="editFieldContainer">
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="distributeTaskModal" tabindex="-1" aria-labelledby="distributeTasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="distributeTasModalLabel">Distribute Task Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary">
                <div class="col-12 mb-3 mt-3">
                    <h6 class="card-title">Departments: </h6>
                </div>
                <div class="col-md-12 mb-3">
                    <div class="list-div" id="distributeContainer">
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

    <div class="modal fade" id="viewTemplateModal" tabindex="-1" aria-labelledby="viewTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="viewTemplateModalLabel">View Task Template</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg mt-3 border border-primary" id="taskContainerTwo">
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs vtPageContainer" role="tablist">
                            </ul>
                            <div class="tab-content border border-top-0 p-3 vtContentContainer" id="myTabContentTwo">
                            </div>
                        </div>
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

    <div class="modal fade" id="assignSoloSettingsModal" tabindex="-1" aria-labelledby="assignSoloSettingsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="assignSoloSettingsModalLabel">Task Settings</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="modal-body border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="assignSoloSettingsDisplay">
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="autoCheckTaskModal" tabindex="-1" aria-labelledby="autoCheckTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="autoCheckTaskModalLabel">Checking Task Automation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary mb-3">
                <div class="col-12 mb-3 text-center">
                    <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                    <span><b>Task checking automation will only check a task if the user is meet the prepared answer</b></span>
                </div>
            </div>
            <div class="row modal-body-bg border border-primary" id="autoCheckTask">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="autoDistributeTaskModal" tabindex="-1" aria-labelledby="autoDistributeTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="autoDistributeTaskModalLabel">Distribute Automation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary mb-3">
                <div class="col-12 mb-3 text-center">
                    <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                    <span><b>Task distribution automation will only distribute a task that you choose and only to the department you pick, and also it can only distribute once</b></span>
                </div>
            </div>
            <div class="row modal-body-bg border border-primary" id="autoDistributeTask">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="manuallyDistributeTaskModal" tabindex="-1" aria-labelledby="manuallyDistributeTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="manuallyDistributeTaskModalLabel">Distribute Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary">
                <form action="" id="manuallyDistributeForm">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="department" class="form-label">Select Department</label>
                                    <select class="form-select" name="department_id" id="department">
                                        @if(!empty($department_dist))
                                            @foreach($department_dist as $dept)
                                            <option value="{{$dept->id}}">{{$dept->name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
                <div class="col-12 d-grid">
                    <button type="button" class="btn btn-primary" id="submitManuallyDistribute">Submit</button>
                </div>
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="autoAcceptDistributeTaskModal" tabindex="-1" aria-labelledby="autoDistributeTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="autoDistributeTaskModalLabel">Accept Distribute Automation</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary mb-3">
                <div class="col-12 mb-3 text-center">
                    <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                    <span><b>Task accept distribution automation will only accept a distributed task that you accept before</b></span>
                </div>
            </div>
            <div class="row modal-body-bg border border-primary" id="autoAcceptDistributeTask">

            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="NewTaskLinkModal" tabindex="-1" aria-labelledby="NewTaskLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-bg">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="NewTaskLinkModalLabel">Link To New Task</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="linkNewDisplay">


            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
    </div>
<!-- endregion -->
<div class="modal fade" id="OngoingTaskLinkModal" tabindex="-1" aria-labelledby="OngoingTaskLinkModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="OngoingTaskLinkModalLabel">Link To Ongoing Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="border-0 px-4">
        <div class="row modal-body-bg border border-primary">
            <div class="col-lg-12 col-xl-12 mb-3 stretch-card" id="ongoingLink-div">
                <div class="card">
                <div class="card-body">
                    <h6 class="card-title mb-3">Task To Link: <b class="text-primary toLinkOngoingTitle"></b></h6>
                    <div class="d-flex justify-content-between align-items-baseline mb-2">
                    <h6 class="card-title mb-0">On-Going Task</h6>
                    </div>
                    <div class="table-responsive">
                    <table id="ongoingLinkTable" class="table table-hover mb-0 text-center">
                        <thead>
                        <tr>
                            <th class="pt-0">Task Name</th>
                            <th class="pt-0">Created Date</th>
                            <th class="pt-0">Assigned To</th>
                            <th class="pt-0">Assigned By</th>
                            <th class="pt-0">Action</th>
                        </tr>
                        </thead>
                        <tbody id="ongoing-tbody">
                            @if(!empty($ongoingLink))
                                @foreach($ongoingLink as $row)
                                    <tr>
                                        <td>{{$row->title}}</td>
                                        <td>{{$row->created_at}}</td>
                                        <td>{{$row->assigned_to}}</td>
                                        <td>{{$row->assigned_by}}</td>
                                        <td class="action-buttons">
                                            <button class="btn btn-primary mx-1 btn-hover linkToOngoingTask" data-task="{{$row->id}}"><i data-feather="link" class="icon-sm icon-wiggle"></i> Link</button>
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
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="autoLinkToNewTaskModal" tabindex="-1" aria-labelledby="autoLinkToNewTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="autoLinkToNewTaskModalLabel">Accept Distribute Automation</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="border-0 px-4">
        <div class="row modal-body-bg border border-primary mb-3">
            <div class="col-12 mb-3 text-center">
                <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                <span><b>Link to new task automation will only assign as new task, it work same as task assigning automation</b></span>
            </div>
        </div>
        <div class="row modal-body-bg border border-primary" id="autoLinkNewTask">

        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="viewStatisticTaskModal" tabindex="-1" aria-labelledby="viewStatisticTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="viewStatisticTaskModalLabel">View Users Task Status</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="border-0 px-4">
        <div class="row modal-body-bg border border-primary" id="viewStatisticDisplay">

        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="worktimeSettingsModal" tabindex="-1" aria-labelledby="worktimeSettingsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content modal-bg">
      <div class="modal-header border-0">
        <h5 class="modal-title" id="worktimeSettingsModalLabel">Work Time Settings</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
      </div>
      <div class="border-0 px-4">
        <div class="row modal-body-bg border border-primary mb-3">
            <div class="col-12 mb-3 text-center">
                <h3 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information</h3>
                <span><b>By setting specific days and times, all tasks will automatically be set to sleep if they fall outside the defined time range.</b></span>
            </div>
        </div>
        <div class="row modal-body-bg border border-primary" id="worktimeDisplay">

        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="sendMessageTaskModal" tabindex="-1" aria-labelledby="sendMessageTaskModalLabel" aria-hidden="true">
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
                        <input type="hidden" id="task_id" name="task_id" value="">
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
$(document).ready(function(){
    var auth = `{{ Auth::user()->department->name ?? 'No Department' }}`;
    if(auth !== 'No Department'){
//region Codes

    function pageContainer(){
        $('#page').load(location.href + " #page > *", function() {
            if ($.fn.DataTable.isDataTable('#taskTable')) {
                $('#taskTable').DataTable().destroy();
            }
            if ($.fn.DataTable.isDataTable('#ongoingLinkTable')) {
                $('#ongoingLinkTable').DataTable().destroy();
            }

            // Reinitialize Morris Donut Chart
            if ($('#taskDonut').length) {
                $('#taskDonut').empty(); // Prevent duplicate rendering
                Morris.Donut({
                    element: 'taskDonut',
                    data: [
                        { label: "To Assign", value: {{ Auth::user()->department ? $temp->count() : 0}} } ,
                        { label: "On-Going", value: {{ Auth::user()->department ? $ongoing->count() : 0}} } ,
                        { label: "To Check", value: {{ Auth::user()->department ? $tocheck->count() : 0}} } ,
                        { label: "Complete", value: {{ Auth::user()->department ? $complete->count() : 0}} } ,
                        { label: "Distributed", value: {{ Auth::user()->department ? $dist->count() : 0}} } ,
                        { label: "Linked", value: {{ Auth::user()->department ? $linked->count() : 0}} }
                    ],
                    colors: ['#ff33cc', '#9966ff', '#3399ff', '#66ffcc', '#ffcc66', '#ff6666'],
                    resize: true
                });
            } else {
                console.error("Error: #taskDonut element not found.");
            }


            if (!$.fn.DataTable.isDataTable('#taskTable')) {
                $('#taskTable').DataTable({
                    "aLengthMenu": [
                    [10, 30, 50, -1],
                    [10, 30, 50, "All"]
                    ],
                    "iDisplayLength": 10,
                    "language": {
                        search: ""
                    },
                    "order": [], // This prevents DataTables from applying its own initial sorting
                    "ordering": true
                });

                $('#taskTable').each(function() {
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

            if (!$.fn.DataTable.isDataTable('#ongoingLinkTable')) {
                $('#ongoingLinkTable').DataTable({
                    "aLengthMenu": [
                    [5, 15, 30, -1],
                    [5, 15, 30, "All"]
                    ],
                    "iDisplayLength": 5,
                    "language": {
                    search: ""
                    }
                });

                $('#ongoingLinkTable').each(function() {
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

            if (!$.fn.DataTable.isDataTable('#archivedTempTable')) {
                $('#archivedTempTable').DataTable({
                    "aLengthMenu": [
                        [10, 30, 50, -1],
                        [10, 30, 50, "All"]
                    ],
                    "iDisplayLength": 10,
                    "language": {
                        search: ""
                    }
                });

                $('#archivedTempTable').each(function() {
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

            if (!$.fn.DataTable.isDataTable('#archivedTable')) {
                $('#archivedTable').DataTable({
                    "aLengthMenu": [
                        [10, 30, 50, -1],
                        [10, 30, 50, "All"]
                    ],
                    "iDisplayLength": 10,
                    "language": {
                        search: ""
                    }
                });

                $('#archivedTable').each(function() {
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

            feather.replace();
        });
    }

    pageContainer();

    let lastUpdate = null;

    function reloadOngoingDiv() {
        $.ajax({
            url: "{{ route('reloadobs.ongoing.div') }}",
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

    let lastToCheckUpdate = null;

    function reloadToCheckDiv() {
        $.ajax({
            url: "{{ route('reloadobs.tocheck.div') }}",
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
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
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="approveTask"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="declineTask"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
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
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="approveTask"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="declineTask"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
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
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="approveTask"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="declineTask"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
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
            url: "{{ route('reloadobs.complete.div') }}",
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"' id="distributeTaskBtn"><i data-feather="corner-up-right" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></button>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover archiveCompletedTask" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `<span class="badge ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'}">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"' id="distributeTaskBtn"><i data-feather="corner-up-right" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></button>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover archiveCompletedTask" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            row.data([
                                task.title,
                                `<span class="badge bg-primary">${task.status}</span>`,
                                task.assigned_to,
                                task.assigned_by,
                                task.approved_by,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="distributeTaskBtn"><i data-feather="corner-up-right" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></button>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover archiveCompletedTask" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
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

    let lastDistributeUpdate = null;

    function reloadDistribute() {
        $.ajax({
            url: "{{ route('reloadobs.distribute.div') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastDistributeUpdate: JSON.stringify(lastDistributeUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire table (if needed)
                    lastDistributeUpdate = response.lastDistributeUpdate;

                    if (!$.fn.DataTable.isDataTable('#distTable')) {
                        $('#distTable').DataTable({
                            "aLengthMenu": [
                                [10, 30, 50, -1],
                                [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                                search: ""
                            }
                        });

                        $('#distTable').each(function() {
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

                    const table = $('#distTable').DataTable();
                    table.clear().draw();

                    if (response.tasks && response.tasks.length > 0) {

                        response.tasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('observer.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="linkTaskBtn"><i data-feather="link" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover archiveDistributeTask" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `distRow_${task.id}`;
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
                    lastDistributeUpdate = response.lastDistributeUpdate;

                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        const table = $('#distTable').DataTable();
                        response.newTasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('observer.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="linkTaskBtn"><i data-feather="link" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover archiveDistributeTask" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `distRow_${task.id}`;
                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#distTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#distRow_${taskId}`); // Find the row by ID
                            if (row.length) {
                                row.remove().draw(false); // Remove the row and redraw the table
                            }
                        });
                    }
                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastDistributeUpdate = response.lastDistributeUpdate;

                    if (response.task) {
                        const task = response.task;

                        const table = $('#distTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#distRow_${task.id}`); // Find the row by ID

                        if (row.length) {
                            // Update the row's data
                            let taskId = task.id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            let printUrl = "{{ route('observer.ptasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            row.data([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}" id="linkTaskBtn"><i data-feather="link" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${printUrl}"><i data-feather="printer" class="icon-sm icon-wiggle"></i></a>
                                    <button type="button" class="btn btn-primary mx-1 btn-hover" data-task="${task.id}"><i data-feather="archive" class="icon-sm icon-wiggle"></i></button>
                                </td>
                                `
                            ]).draw(false); // Update the row and redraw the table
                        }
                        const rowNode = row.node();

                        feather.replace();
                    }
                } else if (response.status === 'no_changes') {
                    lastDistributeUpdate = response.lastDistributeUpdate;

                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadDistribute, 1000);

    let lastDistributeReqUpdate = null;

    function reloadDistributeReq() {
        $.ajax({
            url: "{{ route('reloadobs.distributereq.div') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastDistributeReqUpdate: JSON.stringify(lastDistributeReqUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire table (if needed)
                    lastDistributeReqUpdate = response.lastDistributeReqUpdate;

                    if (!$.fn.DataTable.isDataTable('#distReqTable')) {
                        $('#distReqTable').DataTable({
                            "aLengthMenu": [
                                [10, 30, 50, -1],
                                [10, 30, 50, "All"]
                            ],
                            "iDisplayLength": 10,
                            "language": {
                                search: ""
                            }
                        });

                        $('#distReqTable').each(function() {
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

                    const table = $('#distReqTable').DataTable();
                    table.clear().draw();

                    if (response.tasks && response.tasks.length > 0) {
                        response.tasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.task_id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button class="btn btn-primary mx-1 btn-hover" id="approveTaskDistribution" data-dist="${task.id}"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" id="declineTaskDistribution" data-dist="${task.id}"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `distReqRow_${task.id}`;
                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        $('#divBtnReqTab').load(location.href + " #divBtnReqTab > *");
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }
                    // You can reload the entire table or initialize it here
                } else if (response.status === 'count_changed') {
                    // Handle new tasks and deleted tasks
                    lastDistributeReqUpdate = response.lastDistributeReqUpdate;

                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        const table = $('#distReqTable').DataTable();
                        response.newTasks.forEach(task => {
                            // Add the new row to the DataTable
                            let taskId = task.task_id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            const rowNode = table.row.add([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button class="btn btn-primary mx-1 btn-hover" id="approveTaskDistribution" data-dist="${task.id}"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" id="declineTaskDistribution" data-dist="${task.id}"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false).node(); // Add the row and redraw the table (without resetting pagination)
                            rowNode.id = `distReqRow_${task.id}`;
                            // Initialize Feather icons for the new row
                            feather.replace({ scope: rowNode })
                        });
                        $('#divBtnReqTab').load(location.href + " #divBtnReqTab > *");
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        const table = $('#distReqTable').DataTable(); // Get the DataTable instance

                        response.deletedTaskIds.forEach(taskId => {
                            const row = table.row(`#distReqRow_${taskId}`); // Find the row by ID
                            if (row.length) {
                                row.remove().draw(false); // Remove the row and redraw the table
                                $('#divBtnReqTab').load(location.href + " #divBtnReqTab > *");
                                setTimeout(() => {
                                    feather.replace();
                                }, 300);
                            }
                        });
                    }


                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastDistributeReqUpdate = response.lastDistributeReqUpdate;

                    if (response.task) {
                        const task = response.task;

                        const table = $('#distReqTable').DataTable(); // Get the DataTable instance
                        const row = table.row(`#distReqRow_${task.id}`); // Find the row by ID

                        if (row.length) {
                            // Update the row's data
                            let taskId = task.task_id;
                            let viewUrl = "{{ route('observer.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', taskId);
                            row.data([
                                task.title,
                                `
                                <td class="action-buttons">
                                    <button class="btn btn-primary mx-1 btn-hover" id="approveTaskDistribution" data-dist="${task.id}"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i></button>
                                    <button class="btn btn-primary mx-1 btn-hover" id="declineTaskDistribution" data-dist="${task.id}"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i></button>
                                    <a class="btn btn-primary mx-1 btn-hover" href="${viewUrl}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></a>
                                </td>
                                `
                            ]).draw(false); // Update the row and redraw the table
                        }
                        const rowNode = row.node();
                        feather.replace();
                    }
                } else if (response.status === 'no_changes') {
                    lastDistributeReqUpdate = response.lastDistributeReqUpdate;
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadDistributeReq, 1000);

    let lastOvertimeRequest = {};

    function reloadOvertimeRequest() {
        $.ajax({
            url: "{{ route('reloadobs.overtimereq.div') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastOvertimeRequest: JSON.stringify(lastOvertimeRequest) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load' || response.status === 'count_changed') {
                    // Handle new tasks and deleted tasks
                    lastOvertimeRequest = response.lastOvertimeRequest;
                    let container = $('#divBtnReqOvertimeTab');
                    container.find('.no-overtime-message').remove();

                    // Add new tasks to the table
                    if (response.newTasks && response.newTasks.length > 0) {
                        response.newTasks.forEach(task => {
                            container.append(`
                            <div id="OvertimeRow_${task.id}" class="text-white ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'} pt-2 pb-2 rounded-2 d-inline-block w-auto px-3">
                                ${task.title}
                                <span class="badge bg-white text-primary ms-2">Name: ${task.assigned_user_names}</span>
                                <button type="button" data-task="${task.id}" class="btn btn-light border border-0 ms-3 acceptOvertimeBtn"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i> Accept</button>
                                <button type="button" data-task="${task.id}" class="btn btn-light border border-0 declineOvertimeBtn"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i> Decline</button>
                            </div>
                            `);
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }

                    // Remove deleted tasks from the table
                    if (response.deletedTaskIds && response.deletedTaskIds.length > 0) {
                        response.deletedTaskIds.forEach(task => {
                            $(`#OvertimeRow_${task}`).remove();
                        });
                    }

                    if ($('#divBtnReqOvertimeTab').children().length === 0) {
                        $('#divBtnReqOvertimeTab').html(`
                            <h4 class="justify-content-center text-center align-items-center no-overtime-message">There's no existing overtime request right now</h4>
                        `);
                    }

                    feather.replace();
                } else if (response.status === 'task_updated') {
                    // Handle updated task
                    lastOvertimeRequest = response.lastOvertimeRequest;

                    if (response.tasks && response.tasks.length > 0) {
                        let container = $('#divBtnReqOvertimeTab');
                        response.tasks.forEach(task => {
                            // Check if the task already exists in the container
                            if ($(`#OvertimeRow_${task.id}`).length) {
                                // Update the existing row
                                $(`#OvertimeRow_${task.id}`).html(`
                                    ${task.title}
                                    <span class="badge bg-white text-primary ms-2">Name: ${task.assigned_user_names}</span>
                                    <button type="button" data-task="${task.id}" class="btn btn-light border border-0 ms-3 acceptOvertimeBtn"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i> Accept</button>
                                    <button type="button" data-task="${task.id}" class="btn btn-light border border-0 declineOvertimeBtn"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i> Decline</button>
                                `);
                            } else {
                                // Append a new row
                                container.append(`
                                <div id="OvertimeRow_${task.id}" class="text-white ${task.status === 'Overdue' ? 'bg-danger' : 'bg-primary'} pt-2 pb-2 rounded-2 d-inline-block w-auto px-3">
                                    ${task.title}
                                    <span class="badge bg-white text-primary ms-2">Name: ${task.assigned_user_names}</span>
                                    <button type="button" data-task="${task.id}" class="btn btn-light border border-0 ms-3 acceptOvertimeBtn"><i data-feather="check-circle" class="icon-sm icon-wiggle"></i> Accept</button>
                                    <button type="button" data-task="${task.id}" class="btn btn-light border border-0 declineOvertimeBtn"><i data-feather="x-circle" class="icon-sm icon-wiggle"></i> Decline</button>
                                </div>
                                `);
                            }
                        });
                        setTimeout(() => {
                            feather.replace();
                        }, 300);
                    }
                } else if (response.status === 'no_changes') {
                    lastOvertimeRequest = response.lastOvertimeRequest;
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    setInterval(reloadOvertimeRequest, 1000);

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
                <button type="button" class="btn btn-primary mx-1 btn-hover" id="viewUserStatusStatistic" data-task="${taskId}"><i data-feather="bar-chart-2" class="icon-sm icon-wiggle"></i></button>
                ${task.checker == true && (task.user_status !== 'Emergency' && task.user_status !== 'Sleep' && task.user_status !== 'Request Overtime') ? '<a class="btn btn-primary mx-1 btn-hover" href="'+editUrl+'"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>' : ''}
                ${task.checker == true && (task.user_status === 'Emergency') ? `<button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                ${task.checker == true && (task.user_status === 'Sleep') ? `<button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                <button class="btn btn-primary mx-1 btn-hover" id="sendMessageOverdue" data-name="${task.assigned_to}" data-task="${task.id}"><i data-feather="send" class="icon-sm icon-wiggle"></i></button>
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
                <button type="button" class="btn btn-primary mx-1 btn-hover" id="viewUserStatusStatistic" data-task="${taskId}"><i data-feather="bar-chart-2" class="icon-sm icon-wiggle"></i></button>
                ${task.checker == true && (task.user_status !== 'Emergency' && task.user_status !== 'Sleep' && task.user_status !== 'Request Overtime') ? '<a class="btn btn-primary mx-1 btn-hover" href="'+editUrl+'"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></a>' : ''}
                ${task.checker == true && (task.user_status === 'Emergency') ? `<button class="btn btn-primary mx-1 btn-hover" id="cancelEmergency" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                ${task.checker == true && (task.user_status === 'Sleep') ? `<button class="btn btn-primary mx-1 btn-hover" id="requestOvertime" data-task="${task.id}"><i data-feather="edit-2" class="icon-sm icon-wiggle"></i></button>` : ''}
                <button class="btn btn-primary mx-1 btn-hover" id="sendMessageOverdue" data-name="${task.assigned_to}" data-task="${task.id}"><i data-feather="send" class="icon-sm icon-wiggle"></i></button>
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

    $(document).on('click', '#createTask', function() {
        $('#createTaskModal').modal('show');
    });

    $(document).on('click', '#submitCreateTask', function() {
        $.ajax({
            url: '{{ route("observer.tasks.create") }}',
            method: 'POST',
            data: $('#createTaskForm').serialize(),
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    pageContainer();
                    $('#createTaskForm')[0].reset();
                    $('#createTaskModal').modal('hide');
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully create task'
                    });
                } else if(response.status === 'error') {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
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

    $(document).on('click', '#editTemp', function() {
        var id = $(this).data('temp');
        $('#editTemplateModal').modal('show');
        $('#addTaskPage').data('temp', id);
        task(id);
        task_info(id);
    });

    $(document).on('change', '#sbsToggle', function() {
        var id = $(this).data('temp');
        var status = $(this).is(':checked') ? 'Yes' : 'No';

        $.ajax({
            url: '{{ route("observer.tasks.cstask") }}',
            method: 'POST',
            noLoading: true,
            data: {
                id: id,
                stepper: status
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if(response.status === 'success') {

                } else {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'ERROR'
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

    function task_info(id){
        $.ajax({
            url: '{{ route("observer.tasks.eitemp") }}',
            method: 'GET',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response){
                var info = response.info;
                var INFO_HTML = `
                <div class="col-12 d-flex justify-content-between align-items-center">
                    <div>
                        <h2>Task Title: <b class="ms-2 text-primary">${info.title}</b><span class="badge text-bg-secondary ms-2">Type: <b class="ms-1">${info.type}</b></span></h2>
                    </div>
                    <button type="button" class="btn btn-icon text-primary taskTitleEdit" title="Edit Task Title"
                        data-bs-toggle="collapse" data-bs-target="#editTaskTitle_${info.id}"
                        aria-expanded="false" aria-controls="editTaskTitle_${info.id}">
                        <i data-feather="edit" class="icon-sm icon-wiggle"></i>
                    </button>
                </div>

                ${info.title !== null
                ? `
                <form id="taskInfoForm_${info.id}">
                    @csrf
                    <input type="hidden" name="id" value="${info.id}">
                    <div class="collapse mt-3 " id="editTaskTitle_${info.id}">
                        <div class="card card-body border-secondary rounded-2">
                            <div class="input-group">
                                <span class="input-group-text border border-secondary"><b class="text-primary">Title</b>:</span>
                                <input class="form-control border border-secondary" placeholder="Enter New Task Title" name="title" required value="${info.title}">
                                <span class="input-group-text border border-secondary"><b class="text-primary">Type</b>:</span>
                                <select class="form-select border border-secondary" name="type" id="type_${info.id}">
                                    <option value="Solo">Solo</option>
                                    <option value="Group">Group</option>
                                </select>
                                <button type="button" class="btn text-success border border-secondary taskInfoEditSave" title="Save Changes" data-id="${id}">
                                    <i data-feather="check" class="icon-sm icon-wiggle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
                `
                : ''
                }
                `;

                $('#taskInformation').html(INFO_HTML);
                $(`#type_${info.id}`).val(info.type);
                feather.replace();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        });
    }

    function task(id, active = null){
        $.ajax({
            url: '{{ route("observer.tasks.etemp") }}',
            method: 'GET',
            data: {
                temp: id
            },
            dataType: 'json',
            success: function(response){

                var page_container = $('.pageContainer');
                var content_container = $('.contentContainer');
                var pager = response.page;
                var stepper = response.stepper;
                $('.pageContainer').empty();
                $('.contentContainer').empty();
                $('#toggleDisplay').empty();

                if(pager.length > 1){
                    var toggle_div = `
                    <div class="form-check form-switch mt-2 mb-2">
                        <input type="checkbox" class="form-check-input" id="sbsToggle" data-temp="${id}" ${stepper.stepper === 'Yes' ? 'checked' : ''}>
                        <label class="form-check-label" id="formSwitchLabel1" for="sbsToggle">Required Step By Step </label>
                    </div>
                    `;
                    $('#toggleDisplay').html(toggle_div);
                }
                response.pagesWithContent.forEach((item, index) => {
                    let page = item.pages;
                    let contents = item.contents;
                    let page_count = page.id;
                    let activation = (active !== null && page_count === active) || (active === null && page_count === 1) ? 'active' : '';
                    let activationContent = (active !== null && page_count === active) || (active === null && page_count === 1) ? 'show active' : '';



                    var new_page_tab_html = `
                    <li class="nav-item pageCount" id="page_tab_count_${page_count}">
                        <a class="nav-link ${activation}" data-bs-toggle="tab" href="#newPage${page_count}" role="tab" aria-selected="false">
                            <form id="pageForm_${page.id}" data-temp="${id}">
                            @csrf
                                <div class="input-group">
                                    ${page.page_title === null
                                    ? '<input class="form-control border border-secondary" placeholder="Enter Page Title" name="page_title" required><button type="button" class="btn btn-icon text-success border border-secondary pageTitleSave" title="Save Page Title"><i data-feather="check" class="icon-sm icon-wiggle"></i></button>'
                                    : '<span class="input-group-text border border-secondary">'+page.page_title+'</span><button type="button" class="btn btn-icon text-primary border border-secondary pageTitleEdit" title="Edit Page Title" data-bs-toggle="collapse" data-bs-target="#editPageTitle_'+page.id+'" aria-expanded="false" aria-controls="editPageTitle_'+page.id+'"><i data-feather="edit" class="icon-sm icon-wiggle"></i></button>'
                                    }
                                    <button class="btn btn-icon text-danger border border-secondary pageRemove" data-page="${page.id}" data-temp="${id}">
                                        <i data-feather="trash-2" class="icon-sm icon-wiggle"></i>
                                    </button>
                                </div>
                                ${page.page_title !== null
                                ? `
                                <div class="collapse mt-3 " id="editPageTitle_${page.id}">
                                    <div class="card card-body border-secondary rounded-2">
                                        <div class="input-group">
                                            <input class="form-control border border-secondary" placeholder="Enter New Page Title" name="page_title" required>
                                            <button type="button" class="btn btn-icon text-success border border-secondary pageTitleSave" title="Save Page Title">
                                                <i data-feather="check" class="icon-sm icon-wiggle"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>`
                                : ''
                                }
                            </form>
                        </a>
                    </li>`;

                    var content_html = '';
                    contents.forEach((content) => {
                        if (content.field_page === page.id) {
                            if(content.field_type === 'Radio'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 radioContainer_${content.id}">
                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    getRadioField(content.id).then(function(radioFields) {
                                        $('.radioContainer_' + content.id).html(radioFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        @csrf
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="button" class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 btn-group mb-3" role="group">
                                                    <button type="button" class="btn btn-icon btn-sm btn-primary border-0 incRadio float-end" data-type="radios"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Add Radio Button</button>
                                                    <button type="button" class="btn btn-icon btn-sm btn-primary border-0 decRadio float-end" data-type="radios"><i data-feather="x" class="icon-sm icon-wiggle"></i> Decrease Radio Button</button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 radioContainer_${content.id}">
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'Checkbox'){
                                if(content.field_name !== null && content.options !== null){

                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="check_field_${content.id}" name="check_label_${content.id}" ${content.field_pre_answer !== null ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="check_field_${content.id}">
                                                        ${content.options}
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end"  data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check">
                                                        <input class="form-control border border-primary" type="text" name="check_label_${content.id}" placeholder="Enter Check Box Label">
                                                        <input type="radio" class="form-check-input" name="field_pre_answer" value="check_label_${content.id}" id="checkbox_${content.id}">
                                                        <label class="form-check-label" for="checkbox_${content.id}">Prepared Answer</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'Text'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="text" class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}

                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-group">
                                                        <input class="form-control border border-primary" type="text" name="text_label_${content.id}" placeholder="Enter Text Box Label">
                                                        <label class="form-check-label" for="req_ans_${content.id}">
                                                            Prepared Answer:
                                                        </label>
                                                        <input class="form-control border border-primary mt-3" id="req_ans_${content.id}" type="text" name="field_pre_answer" placeholder="Enter Prepared Answer (Optional)">
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'Textarea'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled></textarea>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}

                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end"  data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-group">
                                                        <input class="form-control border border-primary" type="text" name="area_label_${content.id}" placeholder="Enter Textarea Label">
                                                        <label class="form-check-label" for="req_ans_${content.id}">
                                                            Prepared Answer:
                                                        </label>
                                                        <input class="form-control border border-primary mt-3" id="req_ans_${content.id}" type="text" name="field_pre_answer" placeholder="Enter Prepared Answer (Optional)">
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'File'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <div class="form-group mt-3">
                                                    <div class="contUpload">
                                                        <div class="upload">
                                                            <div class="up-container">
                                                                <div class="header">
                                                                    <div class="text">
                                                                        <h1>Upload and Attach Files</h1>
                                                                        <p>Upload and attach files to this project.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="upload-box">
                                                                    <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                    <span id="file-selected">No file selected</span>
                                                                    <label for="file-upload" class="custom-file-upload">
                                                                        Click to upload<br>
                                                                        <input type="file" class="file" id="file-upload" class="drop_${content.id}" multiple disabled>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                    </label>
                                                                    <span>Maximum file size 5MB.</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end"  data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-group">
                                                        <input class="form-control border border-primary" type="text" name="drop_label_${content.id}" placeholder="Enter File Dropper Label">
                                                    </div>
                                                    <div class="form-group mt-3">
                                                        <div class="contUpload">
                                                            <div class="upload">
                                                                <div class="up-container">
                                                                    <div class="header">
                                                                        <div class="text">
                                                                            <h1>Upload and Attach Files</h1>
                                                                            <p>Upload and attach files to this project.</p>
                                                                        </div>
                                                                    </div>
                                                                    <div class="upload-box">
                                                                        <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                        <span id="file-selected">No file selected</span>
                                                                        <label for="file-upload" class="custom-file-upload">
                                                                            Click to upload<br>
                                                                            <input type="file" class="file" id="file-upload" class="drop_${content.id}" multiple>
                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                        </label>
                                                                        <span>Maximum file size 5MB.</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'Typography'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="typography_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control border border-primary" id="typography_${content.id}"  ${content?.field_pre_answer !== null ? 'required' : ''} disabled></textarea>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}
                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    setTimeout(() => {
                                        tinymce.init({
                                            selector: `#typography_${content.id}`,
                                            height: 300,
                                            plugins: 'advlist autolink link image lists charmap preview anchor pagebreak ' +
                                                        'searchreplace wordcount visualblocks code fullscreen insertdatetime media ' +
                                                        'table emoticons template codesample',
                                            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | ' +
                                                        'bullist numlist outdent indent | link image | preview fullscreen | ' +
                                                        'forecolor backcolor emoticons',
                                            menubar: 'file edit view insert format tools table',
                                            content_style: 'body {font-family:Helvetica,Arial,sans-serif; font-size:16px}'
                                        });
                                    }, 100);
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end"  data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-group">
                                                        <input class="form-control border border-primary" type="text" name="typography_label_${content.id}" placeholder="Enter Typography Label">
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if (content.field_type === 'Date'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="date_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="date" class="form-control" id="date_field_${content.id}" name="date_label_${content.id}" } disabled>
                                            </div>
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-group">
                                                        <input class="form-control border border-primary" type="text" name="date_label_${content.id}" placeholder="Enter Text Box Label">
                                                    </div>
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            } else if(content.field_type === 'Dropdown'){
                                if(content.field_name !== null && content.options !== null){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-4 mb-3">
                                                <button type="button" class="btn btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 downContainer_${content.id}">
                                            </div>
                                            <div class="col-12 d-grid mt-3">
                                                <button type="button" class="btn btn-success editField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                    <i data-feather="edit" class="icon-sm icon-wiggle"></i> Edit Field
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    getDownField(content.id).then(function(downFields) {
                                        $('.downContainer_' + content.id).html(downFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                                } else {
                                    content_html += `
                                    <form id="contentForm_${content.id}">
                                        @csrf
                                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                            <h5>${content.field_type}</h5>
                                            <div class="row">
                                                <div class="col-12">
                                                    <button type="button" class="btn ms-5 btn-icon btn-sm text-danger border-0 fieldRemove float-end" data-type="radios" data-content="${content.id}" data-temp="${id}" data-page="${page.id}"><i data-feather="trash-2" class="icon-sm icon-wiggle"></i></button>
                                                </div>
                                                <div class="col-12 btn-group mb-3" role="group">
                                                    <button type="button" class="btn btn-icon btn-sm btn-primary border-0 incDown float-end" data-type="radios"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Add Radio Button</button>
                                                    <button type="button" class="btn btn-icon btn-sm btn-primary border-0 decDown float-end" data-type="radios"><i data-feather="x" class="icon-sm icon-wiggle"></i> Decrease Radio Button</button>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label">Label:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_name" placeholder="Enter ${content.field_type} Label" value="${content.field_label !== null ? content.field_label : ''}" required>
                                                    <label class="form-check-label">Description:</label>
                                                    <input class="form-control border border-primary" type="text" name="field_description" placeholder="Enter ${content.field_type} Description" value="${content.field_description !== null ? content.field_description : ''}" required>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required">
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 downContainer_${content.id}">
                                                </div>
                                                <div class="col-12 d-grid">
                                                    <button type="button" class="btn btn-success saveField" data-page="${page.id}" data-temp="${id}" data-content="${content.id}">
                                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    `;
                                }
                            }
                        }
                    });

                    var new_content_page_html = `
                    <div class="tab-pane fade ${activationContent}" id="newPage${page_count}" role="tabpanel" data-temp="${page.template_id}">
                        <div class="row" id="fieldContainer${page_count}">
                            ${content_html}
                        </div>
                        <div class="row">
                            <div class="col-12 modal-body-bg text-center border border-primary">
                                <div class="row">
                                    <div class="col-12 d-grid">
                                        <a class="btn btn-primary rounded-3 .colPageButton" data-bs-toggle="collapse" href="#colPage_${page_count}" role="button" aria-expanded="false" aria-controls="colPage_${page_count}">
                                            <b><i data-feather="plus" class="icon-sm icon-wiggle"></i>Add Field</b>
                                        </a>
                                    </div>
                                </div>
                                <div class="collapse" id="colPage_${page_count}">
                                    <div class="row row-cols-1 row-cols-md-2 g-4 stretch-card mt-3">
                                        ${generateFieldButtons(page.id, id)}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>`;


                    // Append to DOM

                    page_container.append(new_page_tab_html);
                    content_container.append(new_content_page_html);

                    feather.replace();
                });

                document.querySelectorAll('.collapse').forEach(el => {
                    new bootstrap.Collapse(el, { toggle: false });
                });
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    function generateFieldButtons(page_id, temp_id) {
        const fields = [
            { type: 'Radio', title: 'Radio Button', desc: 'A button for multiple choices.' },
            { type: 'CheckBox', title: 'Check Box', desc: 'A box for multiple answers.' },
            { type: 'Text', title: 'Text Box', desc: 'An input field for words.' },
            { type: 'Textarea', title: 'Text Area', desc: 'An input field with tabs.' },
            { type: 'File', title: 'File Drop Box', desc: 'A box for file uploads.' },
            { type: 'Typography', title: 'Typography', desc: 'Styled text input field.' },
            { type: 'Date', title: 'Date Box', desc: 'An input field for dates.' },
            { type: 'Dropdown', title: 'Drop Down', desc: 'A dropdown selection field.' }
        ];

        return fields.map(field => `
            <div class="col-12 col-md-6 col-xl-3 mb-2 mt-2 stretch-card">
                <div type="button" class="card btn btn-outline-primary border border-primary fieldAdd"
                    data-type="${field.type}" data-page="${page_id}" data-temp="${temp_id}">
                    <div class="card-body">
                        <h5 class="card-title">${field.title}</h5>
                        <p class="card-text mb-3">${field.desc}</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function getRadioField(id, containerId) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("observer.tasks.goradio") }}',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    var options = response.options;
                    var radioHTML = '';

                    if (options && Object.keys(options).length > 0) {
                        $.each(options, function(contentId, optionSet) {
                            let fieldPreAnswer = response.answer && response.answer[contentId]
                            ? response.answer[contentId].replace('options_', '') // Extract the number
                            : null;

                            optionSet.options.forEach(function(option, index) {
                                let isChecked = (fieldPreAnswer !== null && parseInt(fieldPreAnswer) === index) ? 'checked' : '';

                                radioHTML += `
                                    <div class="form-check mb-2 radioCount">
                                        <input type="radio" class="form-check-input"
                                            name="radio_${contentId}"
                                            value="${option}" id="radio_${contentId}_${index}" ${isChecked} disabled>
                                        <label class="form-check-label" for="radio_${contentId}_${index}">${option}</label>
                                    </div>
                                `;
                            });
                        });
                    }

                    resolve(radioHTML);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    reject(error);
                }
            });
        });
    }

    function getDownField(id, containerId) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("observer.tasks.godown") }}',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    var options = response.options;
                    var downHTML = '';

                    if (options && Object.keys(options).length > 0) {
                        $.each(options, function(contentId, optionSet) {
                            downHTML += `
                            <select class="form-select" aria-label="Default select example" id="down_${contentId}">
                                <option selected disabled>Open this select menu</option>
                            `;
                            let fieldPreAnswer = response.answer && response.answer[contentId]
                            ? response.answer[contentId].replace('options_', '') // Extract the number
                            : null;
                            optionSet.options.forEach(function(option, index) {
                                let isChecked = (fieldPreAnswer !== null && parseInt(fieldPreAnswer) === index) ? 'checked' : '';

                                downHTML += `
                                    <option value="${option}">${option}</option>
                                `;
                            });
                        });
                    }
                    downHTML += `</select>`;
                    resolve(downHTML);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    reject(error);
                }
            });
        });
    }

    $(document).on('click', '.pageTitleSave', function() {
        var formId = $(this).closest('form').attr('id');
        var temp_id = $('#'+formId).data('temp');
        var parts = formId.split('_');
        var page_id = parts[1];

        $.ajax({
            url: '{{ route("observer.tasks.sptitle") }}',
            method: 'POST',
            noLoading: true,
            data: $('#'+formId).serialize() + "&page_id=" + page_id,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if(response.status === 'success') {
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully saved title'
                    });
                    task(temp_id);
                    pageContainer();
                } else {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
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

    $(document).on('click', '.taskInfoEditSave', function() {
        var formId = $(this).closest('form').attr('id');
        var id = $(this).data('id');

        $.ajax({
            url: '{{ route("observer.tasks.stinfo") }}',
            method: 'POST',
            data: $(`#${formId}`).serialize(),
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if(response.status === 'success') {
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully saved changes'
                    });
                    task_info(id);
                    pageContainer();
                } else {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
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

    $(document).on('click', '#addTaskPage', function() {
        var temp = $(this).data('temp');
        var page_count = $('.pageCount').length + 1;

        $.ajax({
            url: '{{ route("observer.tasks.apage") }}',
            method: 'POST',
            noLoading: true,
            data: {
                count: page_count,
                temp_id: temp,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added page'
                    });
                    task(temp);
                    pageContainer();
                } else {
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field'
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

    $(document).on('click', '.pageRemove', function() {
        var page = $(this).data('page');
        var temp = $(this).data('temp');

        Swal.fire({
            title: 'Are you sure you want to delete this page?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("observer.tasks.rpage") }}',
                    method: 'POST',
					noLoading: true,
                    data: {
                        page_id: page,
                        template_id: temp
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully removed page'
                            });
                            task(temp);
                            pageContainer();
                        } else {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Complete the required field'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }


                })
            }
        });

    });

    $(document).on('click', '.fieldAdd', function() {
        var temp = $(this).data('temp');
        var page = $(this).data('page');
        var type = $(this).data('type');
        $.ajax({
            url: '{{ route("observer.tasks.afield") }}',
            method: 'POST',
            noLoading: true,
            data: {
                field_page: page,
                template_id: temp,
                field_type: type
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    task(temp, page);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added row'
                    });

                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    })

    $(document).on('click', '.fieldRemove', function() {
        var content = $(this).data('content');
        var temp = $(this).data('temp');
        var page = $(this).data('page');

        Swal.fire({
            title: 'Are you sure you want to remove field row?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("observer.tasks.rfrow") }}',
                    method: 'POST',
					noLoading: true,
                    data: {
                        id: content
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            task(temp, page);
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove field row'
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

    $(document).on('click', '.incRadio', function() {
        var row = $(this).closest('.field_row');
        var count = row.find('.radioCount').length;

        var parts = row.attr('id').split('_');
        var page = parts[2];
        var content = parts[3];

        var radioHTML = `
        <div class="form-check mb-2 radioCount">
            <input class="form-control border border-primary"  type="text" name="options[${content}][${count}]" placeholder="Enter Radio Label">
            <input type="radio" class="form-check-input" name="field_pre_answer[${content}]" value="options_${count}" id="radio_${content}_${count}">
            <label class="form-check-label" for="radio_${content}_${count}">Prepared Answer</label>
        </div>
        `;

        row.find(`.radioContainer_${content}`).append(radioHTML);
        feather.replace();
    });

    $(document).on('click', '.decRadio', function() {
        var row = $(this).closest('.field_row');
        var lastRadio = row.find('.radioCount').last();

        if (lastRadio.length > 0) {
            lastRadio.remove();  // Remove the last added radio button
        }

        feather.replace();
    });

    $(document).on('click', '.incDown', function() {
        var row = $(this).closest('.field_row');
        var count = row.find('.downCount').length;

        var parts = row.attr('id').split('_');
        var page = parts[2];
        var content = parts[3];

        var downHTML = `
        <div class="form-group mb-2 downCount">
            <input class="form-control border border-primary"  type="text" name="options[${content}][${count}]" placeholder="Enter Drop Down Choices">
            <input type="radio" class="form-check-input" name="field_pre_answer[${content}]" value="options_${count}" id="down_${content}_${count}">
            <label class="form-check-label" for="down_${content}_${count}">Prepared Answer</label>
        </div>
        `;

        row.find(`.downContainer_${content}`).append(downHTML);
        feather.replace();
    });

    $(document).on('click', '.decDown', function() {
        var row = $(this).closest('.field_row');
        var lastRadio = row.find('.downCount').last();

        if (lastRadio.length > 0) {
            lastRadio.remove();  // Remove the last added radio button
        }

        feather.replace();
    });

    $(document).on('click', '.saveField', function() {
        var content = $(this).data('content');
        var temp = $(this).data('temp');
        var page = $(this).data('page');
        var form = $(this).closest('form');
        if($(this).attr('data-edit') !== undefined){
            $('#editFieldTaskModal').modal('hide');
        }

        var optionLabel = form.find('[name^="check_label_"]').val();
        var optionLabelText = form.find('[name^="text_label_"]').val();
        var optionLabelArea = form.find('[name^="area_label_"]').val();
        var optionLabelDrop = form.find('[name^="drop_label_"]').val();
        var optionLabelTypo = form.find('[name^="typography_label_"]').val();
        var optionLabelDate = form.find('[name^="date_label_"]').val();
        var option = '';
        if(optionLabel){
            option = '&option='+ optionLabel;
        } else if(optionLabelText){
            option = '&option='+ optionLabelText;
        } else if(optionLabelArea){
            option = '&option='+ optionLabelArea;
        } else if(optionLabelDrop){
            option = '&option='+ optionLabelDrop;
        } else if(optionLabelTypo){
            option = '&option='+ optionLabelTypo;
        } else if(optionLabelDate){
            option = '&option='+ optionLabelDate;
        }


        $.ajax({
            url: '{{ route("observer.tasks.sfinput") }}',
            method: 'POST',
            data: form.serialize() + option + '&page=' + page + '&temp=' + temp + '&content=' + content,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    task(temp, page);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully saved change in field row'
                    });

                } else if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Complete the required field message: '+ response.message
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

    $(document).on('change', '#file-upload', function() {
        var files = $(this).prop('files');
        var fileName = files.length > 1
            ? files.length + ' files selected'
            : files[0]?.name || 'No file selected';

        $('#file-selected').text(fileName);

        // Show remove button only if a file is selected
        if (files.length > 0) {
            $('#remove-file').show();
        }
    });

    $(document).on('click', '#remove-file', function() {
        $('#file-upload').val('');  // Clear file input
        $('#file-selected').text('No file selected');  // Reset text
        $(this).hide();  // Hide remove button
    });

    $(document).on('click', '.editField', function() {
        var page = $(this).data('page');
        var temp = $(this).data('temp');
        var content = $(this).data('content');
        $('#editFieldTaskModal').modal('show');
        edit_task(page, temp, content);
    });

    function edit_task(page, temp, content){
        $.ajax({
            url: '{{ route("observer.tasks.eftask") }}',
            method: 'GET',
            data: {
                content: content
            },
            dataType: 'json',
            success: function(response) {
                var field = response.field;
                var HTML_Display = '';
                if(field.field_type == "Radio"){
                    var fieldOption = JSON.parse(field.options);
                    var parsedAnswer = JSON.parse(field.field_pre_answer);
                    answerKey = field.field_pre_answer ? Object.values(parsedAnswer)[0].replace("options_", "") : null;

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 btn-group mb-3" role="group">
                            <button type="button" class="btn btn-icon btn-sm btn-primary border-0 incEditRadio float-end" data-type="radios" data-content="${field.id}"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Add Radio Button</button>
                            <button type="button" class="btn btn-icon btn-sm btn-primary border-0 decEditRadio float-end" data-type="radios" data-content="${field.id}"><i data-feather="x" class="icon-sm icon-wiggle"></i> Decrease Radio Button</button>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <span class="mt-3">Radio Options:</span>
                        <div class="col-12 radioContainerEdit_${field.id}">
                                        `;
                    Object.keys(fieldOption).forEach(contentId => {
                        fieldOption[contentId].options.forEach((option, index) => {
                            var isChecked = (index).toString() === answerKey ? "checked" : "";
                            HTML_Display += `
                                <div class="form-check mb-2 radioCount">
                                    <input type="text" class="form-control border border-primary"
                                        name="options[${contentId}][${index}]"
                                        value="${option}" id="radio_${contentId}_${index}">
                                    <input type="radio" class="form-check-input"
                                        name="field_pre_answer[${contentId}]"
                                        value="options_${index}" id="radio_${contentId}_${index}" ${isChecked}>
                                    <label class="form-check-label" for="radio_${contentId}_${index}">Prepared Answer</label>
                                </div>
                            `;
                        });
                    });
                    HTML_Display += `
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Checkbox") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check">
                                <input class="form-control border border-primary" type="text" name="check_label_${field.id}" placeholder="Enter Check Box Label" value="${field.options}">
                                <input type="radio" class="form-check-input" name="field_pre_answer" value="check_label_${field.id}" id="checkbox_${field.id}" ${field.field_pre_answer === `check_label_${field.id}` ? 'checked' : ''}>
                                <label class="form-check-label" for="checkbox_${field.id}">Prepared Answer</label>
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Text") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <input class="form-control border border-primary" type="text" name="text_label_${field.id}" placeholder="Enter Text Box Label" value="${field.options}">
                                <label class="form-check-label mt-3" for="req_ans_${field.id}">
                                    Prepared Answer:
                                </label>
                                <input class="form-control border border-primary" id="req_ans_${field.id}" type="text" name="field_pre_answer" placeholder="Enter Prepared Answer (Optional)" value="${field.field_pre_answer !== null ? field.field_pre_answer : ''}">
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Textarea") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <input class="form-control border border-primary" type="text" name="area_label_${field.id}" placeholder="Enter Textarea Label" value="${field.options}">
                                <label class="form-check-label mt-3" for="req_ans_${field.id}">
                                    Prepared Answer:
                                </label>
                                <input class="form-control border border-primary" id="req_ans_${field.id}" type="text" name="field_pre_answer" placeholder="Enter Prepared Answer (Optional)" value="${field.field_pre_answer !== null ? field.field_pre_answer : ''}">
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "File") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <input class="form-control border border-primary" type="text" name="drop_label_${field.id}" placeholder="Enter File Dropper Label" value="${field.options}">
                            </div>
                            <div class="form-group mt-3">
                                <div class="contUpload">
                                    <div class="upload">
                                        <div class="up-container">
                                            <div class="header">
                                                <div class="text">
                                                    <h1>Upload and Attach Files</h1>
                                                    <p>Upload and attach files to this project.</p>
                                                </div>
                                            </div>
                                            <div class="upload-box">
                                                <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                <span id="file-selected">No file selected</span>
                                                <label for="file-upload" class="custom-file-upload">
                                                    Click to upload<br>
                                                    <input type="file" class="file" id="file-upload" class="drop_${field.id}" multiple>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                </label>
                                                <span>Maximum file size 5MB.</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Typography") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <input class="form-control border border-primary" type="text" name="typography_label_${field.id}" placeholder="Enter Typography Label" value="${field.options}">
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Date") {

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <input class="form-control border border-primary" type="text" name="date_label_${field.id}" placeholder="Enter Text Box Label" value="${field.options}">
                            </div>
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else if(field.field_type == "Dropdown"){
                    var fieldOption = JSON.parse(field.options);
                    var parsedAnswer = JSON.parse(field.field_pre_answer);
                    answerKey = field.field_pre_answer ? Object.values(parsedAnswer)[0].replace("options_", "") : null;

                    HTML_Display += `
                    <form id="contentEditForm_${field.id}">
                        @csrf
                        <label for="field_name_${field.id}">Label:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_name"
                            value="${field.field_name}" id="field_name_${field.id}">
                        <label for="field_name_${field.id}">Description:</label>
                        <input type="text" class="form-control mb-2"
                            name="field_description"
                            value="${field.field_description}" id="field_name_${field.id}">
                        <div class="col-12 btn-group mb-3" role="group">
                            <button type="button" class="btn btn-icon btn-sm btn-primary border-0 incEditDown float-end" data-type="radios" data-content="${field.id}"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Add Dropdown Choices</button>
                            <button type="button" class="btn btn-icon btn-sm btn-primary border-0 decEditDown float-end" data-type="radios" data-content="${field.id}"><i data-feather="x" class="icon-sm icon-wiggle"></i> Decrease Dropdown Choices</button>
                        </div>
                        <div class="col-12 mb-3">
                            <div class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="checkInline">
                                    Required Field
                                </label>
                            </div>
                        </div>
                        <span class="mt-3">Dropdown Choices:</span>
                        <div class="col-12 downContainerEdit_${field.id}">
                                        `;
                        Object.keys(fieldOption).forEach(contentId => {
                            fieldOption[contentId].options.forEach((option, index) => {
                                var isChecked = (index).toString() === answerKey ? "checked" : "";
                                HTML_Display += `
                                    <div class="form-group mb-2 downCount">
                                        <input class="form-control border border-primary"  type="text" name="options[${contentId}][${index}]" value="${option}" placeholder="Enter Drop Down Choices">
                                        <input type="radio" class="form-check-input" name="field_pre_answer[${contentId}]" value="options_${index}" id="down_${contentId}_${index}" ${isChecked}>
                                        <label class="form-check-label" for="down_${contentId}_${index}">Prepared Answer</label>
                                    </div>
                                `;
                            });
                        });
                    HTML_Display += `
                        </div>
                        <div class="col-12 mt-3 d-grid">
                            <button type="button" class="btn btn-success saveField" data-page="${page}" data-temp="${temp}"  data-content="${field.id}" data-edit="true">
                                <i data-feather="check" class="icon-sm icon-wiggle"></i> Save Changes
                            </button>
                        </div>
                    </form>
                    `;
                    $('#editFieldContainer').html(HTML_Display);
                } else {
                    $('#editFieldContainer').html('<h1>This field is not existing to our list of field</h1>');
                }

                feather.replace();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.incEditRadio', function() {
        var row = $(this).closest('#editFieldContainer');
        var count = row.find('.radioCount').length;
        var content = $(this).data('content');

        var radioHTML = `
        <div class="form-check mb-2 radioCount">
            <input class="form-control border border-primary"  type="text" name="options[${content}][${count}]" placeholder="Enter Radio Label">
            <input type="radio" class="form-check-input" name="field_pre_answer[${content}]" value="options_${count}" id="radio_${content}_${count}">
            <label class="form-check-label" for="radio_${content}_${count}">Prepared Answer</label>
        </div>
        `;

        row.find(`.radioContainerEdit_${content}`).append(radioHTML);
        feather.replace();
    });

    $(document).on('click', '.decEditRadio', function() {
        var row = $(this).closest('#editFieldContainer');
        var lastRadio = row.find('.radioCount').last();

        if (lastRadio.length > 0) {
            lastRadio.remove();  // Remove the last added radio button
        }

        feather.replace();
    });

    $(document).on('click', '.incEditDown', function() {
        var row = $(this).closest('#editFieldContainer');
        var count = row.find('.downCount').length;
        var content = $(this).data('content');

        var downHTML = `
        <div class="form-group mb-2 downCount">
            <input class="form-control border border-primary"  type="text" name="options[${content}][${count}]" placeholder="Enter Drop Down Choices">
            <input type="radio" class="form-check-input" name="field_pre_answer[${content}]" value="options_${count}" id="down_${content}_${count}">
            <label class="form-check-label" for="down_${content}_${count}">Prepared Answer</label>
        </div>
        `;

        row.find(`.downContainerEdit_${content}`).append(downHTML);
        feather.replace();
    });

    $(document).on('click', '.decEditDown', function() {
        var row = $(this).closest('#editFieldContainer');
        var lastRadio = row.find('.downCount').last();

        if (lastRadio.length > 0) {
            lastRadio.remove();  // Remove the last added radio button
        }

        feather.replace();
    });

    $(document).on('click', '#btnDistribute', function() {
        var temp = $(this).data('temp');
        $('#distributeTaskModal').modal('show');

        distribute_department(temp);
    });

    function distribute_department(temp){
        $.ajax({
            url: '{{ route("observer.tasks.vdtdept") }}',
            method: 'GET',
            data: {
                id: temp
            },
            dataType: 'json',
            success: function(response) {
                var dept = response.dept;
                var distribute_html = ``;

                dept.forEach((row, index) => {
                    distribute_html += `
                    <div class="col-12 d-flex justify-content-between align-items-center my-2 modal-body-bg border border-primary">
                        <div>
                           <h3>${row.name}</h3>
                        </div>
                        <button class="btn btn-hover btn-primary list-assign submitDistribute" data-temp="${temp}" data-dept="${row.id}">Distribute</button>
                    </div>
                    `;
                });

                $('#distributeContainer').html(distribute_html);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        });
    }

    $(document).on('click', '.submitDistribute', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');

        Swal.fire({
            title: 'Are you sure you want to distribute this template?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I am sure',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: '{{ route("observer.tasks.cdtdept") }}',
                    method: 'GET',
                    data: {
                        temp: temp,
                        dept: dept
                    },
                    dataType: 'json',
                    success: function(response) {
                        if(response.status === 'success') {
                            $.ajax({
                                url: '{{ route("observer.tasks.sdtdept") }}',
                                method: 'POST',
                                data: {
                                    temp: temp,
                                    dept: dept
                                },
                                dataType: 'json',
                                headers: {
                                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                },
                                success: function(response) {
                                    if(response.status === 'success') {
                                        pageContainer();
                                        Toast.fire({
                                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                            title: 'Successfully distributed task template'
                                        });
                                        $('#distributeTaskModal').modal('hide');
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error('Error occurred:', xhr.responseText);
                                    console.error('Error occurred:', status);
                                    console.error('Error occurred:', error);
                                }

                            });
                        } else if(response.status === 'error') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                text: response.message
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

    $(document).on('click', '#viewTemp', function() {
        var id = $(this).data('temp');
        $('#viewTemplateModal').modal('show');
        view_task(id);
    });

    function view_task(id, active = null){
        $.ajax({
            url: '{{ route("observer.tasks.etemp") }}',
            method: 'GET',
            data: {
                temp: id
            },
            dataType: 'json',
            success: function(response){

                var page_container = $('.vtPageContainer');
                var content_container = $('.vtContentContainer');
                $('.vtPageContainer').empty();
                $('.vtContentContainer').empty();

                response.pagesWithContent.forEach((item, index) => {
                    let page = item.pages;
                    let contents = item.contents;
                    let page_count = page.id;
                    let activation = (active !== null && page_count === active) || (active === null && page_count === 1) ? 'active' : '';
                    let activationContent = (active !== null && page_count === active) || (active === null && page_count === 1) ? 'show active' : '';



                    var new_page_tab_html = `
                    <li class="nav-item pageCount" id="view_page_tab_count_${page_count}">
                        <a class="nav-link ${activation}" data-bs-toggle="tab" href="#newViewPage${page_count}" role="tab" aria-selected="false">
                            ${page.page_title !== null ? page.page_title : 'No Title Page ' + (index + 1)}
                        </a>
                    </li>`;

                    var content_html = '';
                    if(contents.length > 0){
                        contents.forEach((content) => {
                            if (content.field_page === page.id) {
                                if(content.field_type === 'Radio'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 radioContainer_${content.id}">
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    getRadioField(content.id).then(function(radioFields) {
                                        $('.radioContainer_' + content.id).html(radioFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                                } else if (content.field_type === 'Checkbox'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="check_field_${content.id}" name="check_label_${content.id}" ${content.field_pre_answer !== null ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="check_field_${content.id}">
                                                        ${content.options}
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (content.field_type === 'Text'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="text" class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}

                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (content.field_type === 'Textarea'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled></textarea>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}

                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (content.field_type === 'File'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <div class="form-group mt-3">
                                                    <div class="contUpload">
                                                        <div class="upload">
                                                            <div class="up-container">
                                                                <div class="header">
                                                                    <div class="text">
                                                                        <h1>Upload and Attach Files</h1>
                                                                        <p>Upload and attach files to this project.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="upload-box">
                                                                    <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                    <span id="file-selected">No file selected</span>
                                                                    <label for="file-upload" class="custom-file-upload">
                                                                        Click to upload<br>
                                                                        <input type="file" class="file" id="file-upload" class="drop_${content.id}" multiple disabled>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                    </label>
                                                                    <span>Maximum file size 5MB.</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if (content.field_type === 'Typography'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="typographyView_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control border border-primary" id="typographyView_${content.id}"  ${content?.field_pre_answer !== null ? 'required' : ''} disabled></textarea>
                                                ${content?.field_pre_answer ? `<span class="text-secondary">Answer: "<b>${content.field_pre_answer}</b>"</span>` : ''}
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    setTimeout(() => {
                                        tinymce.init({
                                            selector: `#typographyView_${content.id}`,
                                            height: 300,
                                            plugins: 'advlist autolink link image lists charmap preview anchor pagebreak ' +
                                                        'searchreplace wordcount visualblocks code fullscreen insertdatetime media ' +
                                                        'table emoticons template codesample',
                                            toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | ' +
                                                        'bullist numlist outdent indent | link image | preview fullscreen | ' +
                                                        'forecolor backcolor emoticons',
                                            menubar: 'file edit view insert format tools table',
                                            content_style: 'body {font-family:Helvetica,Arial,sans-serif; font-size:16px}'
                                        });
                                    }, 100);
                                } else if (content.field_type === 'Date'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="date_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="date" class="form-control" id="date_field_${content.id}" name="date_label_${content.id}" } disabled>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                                } else if(content.field_type === 'Dropdown'){
                                    content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_${content.field_page}_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 downContainer_${content.id}">
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    getDownField(content.id).then(function(downFields) {
                                        $('.downContainer_' + content.id).html(downFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                                }
                            }
                        });
                    } else {
                        content_html += `
                        <div class="col-12 modal-body-bg field_row mb-3 border border-primary">
                            <div class="row text-center">
                                <h3 class="m-3">There's no existing field on this page</h3>
                            </div>
                        </div>
                        `;
                    }

                    var new_content_page_html = `
                    <div class="tab-pane fade ${activationContent}" id="newViewPage${page_count}" role="tabpanel" data-temp="${page.template_id}">
                        <div class="row" id="fieldContainer${page_count}">
                            ${content_html}
                        </div>
                    </div>`;


                    // Append to DOM

                    page_container.append(new_page_tab_html);
                    content_container.append(new_content_page_html);

                    feather.replace();
                });

                document.querySelectorAll('.collapse').forEach(el => {
                    new bootstrap.Collapse(el, { toggle: false });
                });
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    $(document).on('click', '#btnSoloAssign', function() {
        $('#assignSoloTaskModal').modal('show');
        var id = $(this).data('temp');
        var name = $(this).data('name');
        var dept = $(this).data('dept');

        assign_solo(id, name, dept);
    });

    function assign_solo(id, name, dept){

        $.ajax({
            url: '{{ route("observer.tasks.vlsatask") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(response) {
                var solo_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
                    </div>
                    <div class="col-md-12 mb-3">
                        <h6 class="card-title">List of Department Member: </h6>
                        <div class="list-div">
                `;
                if(response.users.length > 0){
                    response.users.forEach((users, index) => {

                    var profile = users.profile;
                    var count = users.count;
                    var department = users.department;

                    var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                    solo_html += `
                    <div class="list-card">
                        <div class="list-header justify-content-start align-items-center">
                            <img src="${photoUrl}" alt="image">
                            <h6 class="ms-2">${profile.name}</h6>
                        </div>
                        <div class="list-content">
                            <h6>Currently Holding Task: ${count}</h6>
                            <h6>Department: ${department}</h6>
                            <button class="btn btn-hover btn-primary list-assign submitSoloAssign" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}">Assign</button>
                        </div>
                    </div>`;
                    });
                } else {
                    solo_html += `
                    <div class="modal-body-bg border border-primary text-wrap text-center">
                            <h2 class="text-primary m-3">There's no existing member in this department</h2>
                    </div>`;
                }
                solo_html += `
                        </div>
                    </div>
                `;



                $('#assignSoloDisplay').html(solo_html);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    $(document).on('click', '.submitSoloAssign', function() {
        var temp = $(this).data('temp');
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        $('#assignSoloSettingsModal').modal('show');

        var soloSettings_html = `
        <form id="submitSoloSettings">
            @csrf
            <div class="col-12 mb-3">
                <div class="form-group">
                    <input type="hidden" name="temp" value="${temp}">
                    <input type="hidden" name="dept" value="${dept}">
                    <input type="hidden" name="user" value="${user}">
                    <label class="form-check-label" for="dueAssign">
                        Set Due Date
                    </label>
                    <input class="form-control border border-primary" type="date" name="due" id="dueAssign" required>
                </div>
            </div>
            <div class="col-12 d-grid">
                <button type="submit" class="btn btn-success taskAssignSubmit">
                    <i data-feather="check" class="icon-sm icon-wiggle"></i> Assign Task
                </button>
            </div>
        </form>
        `;

        $('#assignSoloSettingsDisplay').html(soloSettings_html);

        const today = new Date();
        const formattedToday = today.toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

        $('#dueAssign').attr('min', formattedToday);
    });

    $(document).on('click', '.taskAssignSubmit', function(e){
        e.preventDefault();
        var form = $('#submitSoloSettings').serialize();
        Swal.fire({
            title: 'Are you sure you want to assign it on this member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to assign it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                let manualSwal = Swal.fire({
                    title: 'Assigning Task...',
                    html: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: '{{ route("observer.tasks.sastask") }}',
                    method: 'POST',
                    noLoading: true,
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            manualSwal.close();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully assign task'
                            });
                            $('#assignSoloSettingsModal').modal('hide');
                            $('#assignSoloTaskModal').modal('hide');
                        } else if(response.status === 'error') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'You need to enter due date first'
                            });
                        } else if(response.status === 'existTask'){
                            Swal.fire({
                                title: 'This task is already assigned to this member, do you want to assign it again on the same user?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes! I want to assign it',
                                cancelButtonText: 'No, I don\'t want to'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    let manualSwal = Swal.fire({
                                        title: 'Assigning Task...',
                                        html: 'Please wait while we process your request',
                                        allowOutsideClick: false,
                                        didOpen: () => Swal.showLoading()
                                    });
                                    // If user confirmed, proceed with AJAX request
                                    $.ajax({
                                        url: '{{ route("observer.tasks.sastask") }}',
                                        method: 'POST',
                                        noLoading: true,
                                        data: form + '&reassign=1',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                pageContainer();
                                                manualSwal.close();
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully assign task'
                                                });
                                                $('#assignSoloSettingsModal').modal('hide');
                                                $('#assignSoloTaskModal').modal('hide');
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error'
                                                });
                                            } else if(response.status === 'errorTask') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    text: response.message
                                                });
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'You need to enter due date first'
                                                });
                                            }

                                            manualSwal.close();
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Error occurred:', xhr.responseText);
                                            console.error('Error occurred:', status);
                                            console.error('Error occurred:', error);
                                        }
                                    });


                                }
                            });
                        }  else if(response.status === 'errorTask') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                text: response.message
                            });
                        }

                        manualSwal.close();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }
                })
            }
        });
    });

    $(document).on('click', '#btnGroupAssign', function() {
        $('#assignGroupTaskModal').modal('show');
        var id = $(this).data('temp');
        var name = $(this).data('name');
        var dept = $(this).data('dept');

        assign_group(id, name, dept);
    });

    function assign_group(id, name, dept, assigning = 0){
        $.ajax({
            url: '{{ route("observer.tasks.vlgatask") }}',
            method: 'GET',
            data: {
                dept: dept,
                temp: id,
                assigning: assigning
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'tempoMember'){
                    Swal.fire({
                        title: 'Do you want to use the member you pick before?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes! I want to use it',
                        cancelButtonText: 'No, I don\'t want to'
                    }).then((result) => {
                        var contValue = result.isConfirmed ? 1 : 0;
                        $.ajax({
                        url: '{{ route("observer.tasks.vlgatask") }}',
                        method: 'GET',
                        data: {
                            dept: dept,
                            temp: id,
                            cont: contValue
                        },
                        dataType: 'json',
                        success: function(response) {
                            group_layout(id, name, dept, response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', xhr.responseText);
                            console.error('Error occurred:', status);
                            console.error('Error occurred:', error);
                        }
                    });
                    });
                } else {
                    group_layout(id, name, dept, response);
                }

            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    function group_layout(id, name, dept, response){
        var group_html = `
        <div class="col-12 mb-3 mt-3">
            <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
        </div>
        <div class="col-12 mb-3">
            <h6 class="card-title">Selected Employee </h6>
            <div class="table-responsive">
                <table class="table table-hover m-0" id="selectedGroupTable">
                    <thead>
                    <tr>
                        <th class="pt-0">Profile</th>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>`;
                    if(response.selectedUser.length > 0){
                        response.selectedUser.forEach((users, index) => {
                            var profile = users.profile;
                            var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                            group_html += `
                                <tr>
                                    <td>
                                        <img src="${photoUrl}" alt="image">
                                    </td>
                                    <td>${profile.name}</td>
                                    <td class="action-buttons">
                                        <button type="button" class="btn border-0 nav-link removeMemberTempoList" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                    </td>
                                </tr>
                            `;
                        })
                    } else {
                        group_html += `
                        <tr class="align-middle">
                            <td colspan="3" class="text-center">
                                There's no existing member yet
                            </td>
                        </tr>
                        `;
                    }
                    group_html += `
                    </tbody>
                </table>
            </div>
        </div>
        <form id="submitGroupSettings">
            @csrf
            <div class="form-group">
                <input type="hidden" name="temp" value="${id}">
                <input type="hidden" name="dept" value="${dept}">
                <label class="form-check-label" for="dueAssign">
                    Set Due Date
                </label>
                <input class="form-control border border-primary" type="date" name="due" id="dueAssign" required>
            </div>
            <div class="col-12 d-grid my-3">
                <button type="submit" class="btn btn-success taskGroupAssignSubmit">
                    <i data-feather="check" class="icon-sm icon-wiggle"></i> Submit Assigned Group Task
                </button>
            </div>
        </form>
        <div class="col-md-12 mb-3">
            <h6 class="card-title">List of Department Member: </h6>
            <div class="list-div">
        `;
        if(response.users.length > 0){
            response.users.forEach((users, index) => {

            var profile = users.profile;
            var count = users.count;
            var department = users.department;

            var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

            group_html += `
            <div class="list-card">
                <div class="list-header justify-content-start align-items-center">
                    <img src="${photoUrl}" alt="image">
                    <h6 class="ms-2">${profile.name}</h6>
                </div>
                <div class="list-content">
                    <h6>Currently Holding Task: ${count}</h6>
                    <h6>Department: ${department}</h6>
                    <button class="btn btn-hover btn-primary list-assign submitGroupTempAssign" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}">Assign</button>
                </div>
            </div>`;
            });
        } else {
            group_html += `
            <div class="modal-body-bg border border-primary text-wrap text-center">
                    <h2 class="text-primary m-3">There's no existing member in this department</h2>
            </div>`;
        }
        group_html += `
                </div>
            </div>
        `;

        $('#assignGroupDisplay').html(group_html);

        const today = new Date();
        const formattedToday = today.toISOString().split('T')[0]; // Get today's date in YYYY-MM-DD format

        $('#dueAssign').attr('min', formattedToday);
        feather.replace();
    }

    $(document).on('click', '.submitGroupTempAssign', function() {
        var temp = $(this).data('temp');
        var user = $(this).data('user');
        var dept = $(this).data('dept');
        var name = $(this).data('name');

        $.ajax({
            url: '{{ route("observer.tasks.atagtask") }}',
            method: 'POST',
            noLoading: true,
            data: {
                temp: temp,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    assign_group(temp, name, dept, 1);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        })
    });

    $(document).on('click', '.removeMemberTempoList', function() {
        var temp = $(this).data('temp');
        var user = $(this).data('user');
        var dept = $(this).data('dept');
        var name = $(this).data('name');

        $.ajax({
            url: '{{ route("observer.tasks.rtagtask") }}',
            method: 'POST',
            noLoading: true,
            data: {
                temp: temp,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    assign_group(temp, name, dept, 1);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        })
    });

    $(document).on('click', '.taskGroupAssignSubmit', function(e) {
        e.preventDefault();
        var form = $('#submitGroupSettings').serialize();

        Swal.fire({
            title: 'Are you sure you want to assign it on this member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to assign it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                let manualSwal = Swal.fire({
                    title: 'Assigning Task...',
                    html: 'Please wait while we process your request',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
                $.ajax({
                    url: '{{ route("observer.tasks.sagtask") }}',
                    method: 'POST',
                    noLoading: true,
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            manualSwal.close();
                            pageContainer();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully assign task'
                            });
                            $('#assignGroupTaskModal').modal('hide');
                        } else if(response.status === 'error') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'You need to enter due date first'
                            });
                        } else if(response.status === 'existTask'){
                            let manualSwal = Swal.fire({
                                title: 'Assigning Task...',
                                html: 'Please wait while we process your request',
                                allowOutsideClick: false,
                                didOpen: () => Swal.showLoading()
                            });
                            Swal.fire({
                                title: 'This task is already assigned to this member '+response.message+', do you want to assign it again on the same user?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes! I want to assign it',
                                cancelButtonText: 'No, I don\'t want to'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    var formData = form + '&reassign=1';
                                    $.ajax({
                                        url: '{{ route("observer.tasks.sagtask") }}',
                                        method: 'POST',
                                        noLoading: true,
                                        data: formData,
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                manualSwal.close();
                                                pageContainer();
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully assign task'
                                                });
                                                $('#assignGroupTaskModal').modal('hide');
                                            } else if(response.status === 'errorTask') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    text: response.message
                                                });
                                            }  else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'You need to enter due date first'
                                                });
                                            }
                                            manualSwal.close();
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Error occurred:', xhr.responseText);
                                            console.error('Error occurred:', status);
                                            console.error('Error occurred:', error);
                                        }
                                    });
                                }
                            });
                        }  else if(response.status === 'errorTask') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                text: response.message
                            });
                        }
                        manualSwal.close();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }
                })
            }
        });
    });
//endregion
    $(document).on('click', '#btnSoloAutoAssign', function(){
        $('#autoAssignSoloTaskModal').modal('show');
        var id = $(this).data('temp');
        var name = $(this).data('name');
        var dept = $(this).data('dept');

        auto_solo_layout(id, name, dept);
    });

    function auto_solo_layout(id, name, dept){
        $.ajax({
            url: '{{ route("observer.tasks.vlsaatask") }}',
            method: 'GET',
            data: {
                dept: dept,
                temp: id,
            },
            dataType: 'json',
            success: function(response) {

                var solo_auto_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
                    </div>
                    <form id="formSoloAutomation">
                        @csrf
                        <div class="form-group">
                            <label class="form-check-label" for="dueAssign">
                                Set Due Date (Max 31 Days)
                            </label>
                            <select id="dueAssign" name="due" class="form-select" aria-label="Default select example">
                                <option selected value="" disabled>Open this select menu</option>
                                <option value="1">1 Day</option>
                                <option value="2">2 Days</option>
                                <option value="3">3 Days</option>
                                <option value="4">4 Days</option>
                                <option value="5">5 Days</option>
                                <option value="6">6 Days</option>
                                <option value="7">7 Days</option>
                                <option value="8">8 Days</option>
                                <option value="9">9 Days</option>
                                <option value="10">10 Days</option>
                                <option value="11">11 Days</option>
                                <option value="12">12 Days</option>
                                <option value="13">13 Days</option>
                                <option value="14">14 Days</option>
                                <option value="15">15 Days</option>
                                <option value="16">16 Days</option>
                                <option value="17">17 Days</option>
                                <option value="18">18 Days</option>
                                <option value="19">19 Days</option>
                                <option value="20">20 Days</option>
                                <option value="21">21 Days</option>
                                <option value="22">22 Days</option>
                                <option value="23">23 Days</option>
                                <option value="24">24 Days</option>
                                <option value="25">25 Days</option>
                                <option value="26">26 Days</option>
                                <option value="27">27 Days</option>
                                <option value="28">28 Days</option>
                                <option value="29">29 Days</option>
                                <option value="30">30 Days</option>
                                <option value="31">31 Days</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label for="automationType">Automation Type</label>
                            <select id="automationType" name="type" class="form-select" aria-label="Default select example">
                                <option selected value="" disabled>Open this select menu</option>
                                <option value="week">Every Week (Once A Week)</option>
                                <option value="day">Every Day (Once A Day)</option>
                                <option value="time">Time to Time (Anytime if Finished Task)</option>
                            </select>
                        </div>
                        <div class="collapse mt-3" id="singleDaySetterSolo">
                            <div class="card card-body border-secondary rounded-2">
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="startDay">Select Day:</label>
                                        <select id="startDay" name="only_day" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="sunday">Sunday</option>
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="collapse mt-3" id="daySetterSolo">
                            <div class="card card-body border-secondary rounded-2">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="startDay">Select Start Day:</label>
                                        <select id="startDay" name="start_day" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="sunday">Sunday</option>
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                        </select>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="endDay">Select End Day:</label>
                                        <select id="endDay" name="end_day" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="sunday">Sunday</option>
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="collapse mt-3" id="timeSetterSolo">
                            <div class="card card-body border-secondary rounded-2">
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label for="startTime" >Select Start Time:</label>
                                        <input class="form-control" type="time" id="startTime" name="start_time">
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="endTime">Select End Time:</label>
                                        <input class="form-control" type="time" id="endTime" name="end_time">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="col-12 my-3">
                        <h6 class="card-title mb-3">List of setted in automation task assigning employee:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover m-0" id="selectedSoloAutomationTable">
                                <thead>
                                    <tr>
                                        <th class="pt-0">Profile</th>
                                        <th class="pt-0">Name</th>
                                        <th class="pt-0">Type</th>
                                        <th class="pt-0">Day</th>
                                        <th class="pt-0">Time</th>
                                        <th class="pt-0">Task Due Date</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                                if(response.selectedUser.length > 0){
                                    response.selectedUser.forEach((users, index) => {
                                        var profile = users.profile;
                                        var auto = users.autoSolo;

                                        var TypeWF = '';
                                        if(auto.type === 'time'){
                                            TypeWF = 'Time to Time';
                                        } else if(auto.type === 'day'){
                                            TypeWF = 'Every Day';
                                        } else if(auto.type === 'week'){
                                            TypeWF = 'Every Week';
                                        }
                                        var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                        solo_auto_html += `
                                            <tr>
                                                <td>
                                                    <img src="${photoUrl}" alt="image">
                                                </td>
                                                <td>${profile.name}</td>
                                                <td>${TypeWF}</td>
                                                <td>${auto.start_day} - ${auto.end_day}</td>
                                                <td>${auto.start_time !== null && auto.end_time !== null ? auto.start_time+' - '+auto.end_time : 'Time is not available in '+auto.type}</td>
                                                <td>${auto.due} Day${auto.due != 1 ? 's' : ''}</td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn border-0 nav-link removeMemberAutomationSoloList" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                </td>
                                            </tr>
                                        `;
                                    })
                                } else {
                                    solo_auto_html += `
                                    <tr class="align-middle">
                                        <td colspan="7" class="text-center">
                                            There's no existing member yet
                                        </td>
                                    </tr>
                                    `;
                                }
                                solo_auto_html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6 class="card-title">List of Department Member: </h6>
                        <div class="list-div">`;
                            if(response.users.length > 0){
                                response.users.forEach((users, index) => {

                                var profile = users.profile;
                                var count = users.count;
                                var department = users.department;

                                var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                                solo_auto_html += `
                                <div class="list-card">
                                    <div class="list-header justify-content-start align-items-center">
                                        <img src="${photoUrl}" alt="image">
                                        <h6 class="ms-2">${profile.name}</h6>
                                    </div>
                                    <div class="list-content">
                                        <h6>Currently Holding Task: ${count}</h6>
                                        <h6>Department: ${department}</h6>
                                        <button class="btn btn-hover btn-primary list-assign submitSoloAutoAssign" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}">Assign</button>
                                    </div>
                                </div>`;
                                });
                            } else {
                                solo_auto_html += `
                                <div class="modal-body-bg border border-primary text-wrap text-center">
                                        <h2 class="text-primary m-3">There's no existing member in this department</h2>
                                </div>`;
                            }
                    solo_auto_html += `
                            </div>
                        </div>
                    `;

                $('#autoAssignSoloDisplay').html(solo_auto_html);
                feather.replace();

                $(document).on('change', '#automationType', function(){
                    var selected = $(this).val();
                    var timeSetterSolo = $('#timeSetterSolo'); // Get or create instance
                    var daySetterSolo = $('#daySetterSolo');
                    var singleDaySetterSolo = $('#singleDaySetterSolo');

                    if (selected === 'day') {
                        daySetterSolo.collapse('show');
                        timeSetterSolo.collapse('show');
                        singleDaySetterSolo.collapse('hide');
                    } else if(selected === 'week') {
                        singleDaySetterSolo.collapse('show');
                        daySetterSolo.collapse('hide');
                        timeSetterSolo.collapse('show');
                    }else if (selected === 'time') {
                        timeSetterSolo.collapse('show');
                        daySetterSolo.collapse('show');
                        singleDaySetterSolo.collapse('hide');
                    } else {
                        timeSetterSolo.collapse('hide');
                    }
                });

            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.submitSoloAutoAssign', function(){
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');
        var name = $(this).data('name');
        var user = $(this).data('user');
        var form = $('#formSoloAutomation').serialize() + `&temp=${temp}&dept=${dept}&user=${user}`;

        $.ajax({
            url: '{{ route("observer.tasks.slsaatask") }}',
            method: 'POST',
            data: form,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    pageContainer();
                    auto_solo_layout(temp, name, dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully assign automation task'
                    });
                } else if(response.status === 'required'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'There\'s some missing field'
                    });
                }  else if(response.status === 'timeGreater'){
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Time start can\'t be higher than end'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    });

    $(document).on('click', '.removeMemberAutomationSoloList', function() {
        var temp = $(this).data('temp');
        var user = $(this).data('user');
        var dept = $(this).data('dept');
        var name = $(this).data('name');

        $.ajax({
            url: '{{ route("observer.tasks.rtaastask") }}',
            method: 'POST',
            data: {
                temp: temp,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    auto_solo_layout(temp, name, dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully remove to automation task'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        })
    });

    $(document).on('click', '#btnGroupAutoAssign', function(){
        $('#autoAssignGroupTaskModal').modal('show');
        var id = $(this).data('temp');
        var name = $(this).data('name');
        var dept = $(this).data('dept');

        assign_auto_group(id, name, dept);
    });

    function assign_auto_group(id, name, dept, assigning = 0){
        $.ajax({
            url: '{{ route("observer.tasks.vlgaatask") }}',
            method: 'GET',
            data: {
                dept: dept,
                temp: id,
                assigning: assigning
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'tempoMember'){
                    Swal.fire({
                        title: 'Do you want to use the member you pick before?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes! I want to use it',
                        cancelButtonText: 'No, I don\'t want to'
                    }).then((result) => {
                        var contValue = result.isConfirmed ? 1 : 0;
                        $.ajax({
                        url: '{{ route("observer.tasks.vlgaatask") }}',
                        method: 'GET',
                        data: {
                            dept: dept,
                            temp: id,
                            cont: contValue
                        },
                        dataType: 'json',
                        success: function(response) {
                            auto_group_layout(id, name, dept, response);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error occurred:', xhr.responseText);
                            console.error('Error occurred:', status);
                            console.error('Error occurred:', error);
                        }
                    });
                    });
                } else {
                    auto_group_layout(id, name, dept, response);
                }

            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    function auto_group_layout(id, name, dept, response){
        var group_auto_html = `
            <div class="col-12 mb-3 mt-3">
                <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
            </div>
            <form id="formGroupAutomation">
                @csrf
                <input type="hidden" name="temp" value="${id}">
                <input type="hidden" name="dept" value="${dept}">
                <div class="form-group">
                    <label class="form-check-label" for="dueAssign">
                        Set Due Date (Max 31 Days)
                    </label>
                    <select id="dueAssign" name="due" class="form-select" aria-label="Default select example">
                        <option selected value="" disabled>Open this select menu</option>
                        <option value="1">1 Day</option>
                        <option value="2">2 Days</option>
                        <option value="3">3 Days</option>
                        <option value="4">4 Days</option>
                        <option value="5">5 Days</option>
                        <option value="6">6 Days</option>
                        <option value="7">7 Days</option>
                        <option value="8">8 Days</option>
                        <option value="9">9 Days</option>
                        <option value="10">10 Days</option>
                        <option value="11">11 Days</option>
                        <option value="12">12 Days</option>
                        <option value="13">13 Days</option>
                        <option value="14">14 Days</option>
                        <option value="15">15 Days</option>
                        <option value="16">16 Days</option>
                        <option value="17">17 Days</option>
                        <option value="18">18 Days</option>
                        <option value="19">19 Days</option>
                        <option value="20">20 Days</option>
                        <option value="21">21 Days</option>
                        <option value="22">22 Days</option>
                        <option value="23">23 Days</option>
                        <option value="24">24 Days</option>
                        <option value="25">25 Days</option>
                        <option value="26">26 Days</option>
                        <option value="27">27 Days</option>
                        <option value="28">28 Days</option>
                        <option value="29">29 Days</option>
                        <option value="30">30 Days</option>
                        <option value="31">31 Days</option>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label for="automationType">Automation Type</label>
                    <select id="automationType" name="type" class="form-select" aria-label="Default select example">
                        <option selected value="" disabled>Open this select menu</option>
                        <option value="week">Every Week (Once A Week)</option>
                        <option value="day">Every Day (Once A Day)</option>
                        <option value="time">Time to Time (Anytime if Finished Task)</option>
                    </select>
                </div>
                <div class="collapse mt-3" id="singleDaySetterGroup">
                    <div class="card card-body border-secondary rounded-2">
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="startDay">Select Day:</label>
                                <select id="startDay" name="only_day" class="form-select" aria-label="Default select example">
                                    <option selected value="" disabled>Open this select menu</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse mt-3" id="daySetterGroup">
                    <div class="card card-body border-secondary rounded-2">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="startDay">Select Start Day:</label>
                                <select id="startDay" name="start_day" class="form-select" aria-label="Default select example">
                                    <option selected value="" disabled>Open this select menu</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="endDay">Select End Day:</label>
                                <select id="endDay" name="end_day" class="form-select" aria-label="Default select example">
                                    <option selected value="" disabled>Open this select menu</option>
                                    <option value="sunday">Sunday</option>
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse mt-3" id="timeSetterGroup">
                    <div class="card card-body border-secondary rounded-2">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="startTime" >Select Start Time:</label>
                                <input class="form-control" type="time" id="startTime" name="start_time">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="endTime">Select End Time:</label>
                                <input class="form-control" type="time" id="endTime" name="end_time">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            <div class="col-12 my-3">
                <h6 class="card-title mb-3">List of setted in automation task assigning employee:</h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="selectedGroupAutomationTable">
                        <thead>
                            <tr>
                                <th class="pt-0">Group No.</th>
                                <th class="pt-0">Type</th>
                                <th class="pt-0">Day</th>
                                <th class="pt-0">Time</th>
                                <th class="pt-0">Task Due Date</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>`;
                        if(response.selectedUser.length > 0){
                            response.selectedUser.forEach((users, index) => {
                                var auto = users.autoGroup;

                                var TypeWF = '';
                                if(auto.type === 'time'){
                                    TypeWF = 'Time to Time';
                                } else if(auto.type === 'day'){
                                    TypeWF = 'Every Day';
                                } else if(auto.type === 'week'){
                                    TypeWF = 'Every Week';
                                }
                                group_auto_html += `
                                    <tr>
                                        <td>${index + 1}</td>
                                        <td>${TypeWF}</td>
                                        <td>${auto.start_day} - ${auto.end_day}</td>
                                        <td>${auto.start_time !== null && auto.end_time !== null ? auto.start_time+' - '+auto.end_time : 'Time is not available in '+auto.type}</td>
                                        <td>${auto.due} Day${auto.due != 1 ? 's' : ''}</td>
                                        <td class="action-buttons d-flex">
                                            <button type="button" class="btn me-2 border-0 nav-link removeMemberAutomationGroup" data-temp="${id}" data-group="${auto.id}" data-dept="${dept}" data-name="${name}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                        </td>
                                    </tr>
                                `;
                            })
                        } else {
                            group_auto_html += `
                            <tr class="align-middle">
                                <td colspan="7" class="text-center">
                                    There's no existing member yet
                                </td>
                            </tr>
                            `;
                        }
                        group_auto_html += `
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 my-3">
                <h6 class="card-title mb-3">List of to set in automation task assigning employee:</h6>
                <div class="table-responsive">
                    <table class="table table-hover m-0" id="selectedUserToGroupAutomationTable">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>`;
                        if(response.selectedUserToGroup.length > 0){
                            response.selectedUserToGroup.forEach((users, index) => {
                                var profile = users.profile;
                                var auto = users.autoSolo;
                                var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                group_auto_html += `
                                    <tr>
                                        <td>
                                            <img src="${photoUrl}" alt="image">
                                        </td>
                                        <td>${profile.name}</td>
                                        <td class="action-buttons">
                                            <button type="button" class="btn border-0 nav-link removeMemberAutomationGroupList" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                        </td>
                                    </tr>
                                `;
                            })
                        } else {
                            group_auto_html += `
                            <tr class="align-middle">
                                <td colspan="6" class="text-center">
                                    There's no existing member yet
                                </td>
                            </tr>
                            `;
                        }
                        group_auto_html += `
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-12 d-grid my-3">
                <button type="submit" class="btn btn-success taskGroupAutomationAssignSubmit" data-temp="${id}" data-dept="${dept}" data-name="${name}">
                    <i data-feather="check" class="icon-sm icon-wiggle"></i> Submit Assigned Group List Task
                </button>
            </div>

            <div class="col-12 mt-3">
                <h6 class="card-title">List of Department Member: </h6>
                <div class="list-div">`;
                    if(response.users.length > 0){
                        response.users.forEach((users, index) => {

                        var profile = users.profile;
                        var count = users.count;
                        var department = users.department;

                        var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                        group_auto_html += `
                        <div class="list-card">
                            <div class="list-header justify-content-start align-items-center">
                                <img src="${photoUrl}" alt="image">
                                <h6 class="ms-2">${profile.name}</h6>
                            </div>
                            <div class="list-content">
                                <h6>Currently Holding Task: ${count}</h6>
                                <h6>Department: ${department}</h6>
                                <button class="btn btn-hover btn-primary list-assign submitGroupAutoAssignTempo" data-temp="${id}" data-user="${profile.id}" data-dept="${dept}" data-name="${name}">Assign</button>
                            </div>
                        </div>`;
                        });
                    } else {
                        group_auto_html += `
                        <div class="modal-body-bg border border-primary text-wrap text-center">
                                <h2 class="text-primary m-3">There's no existing member in this department</h2>
                        </div>`;
                    }
        group_auto_html += `
                </div>
            </div>
        `;

        $('#autoAssignGroupDisplay').html(group_auto_html);
        feather.replace();

        $(document).on('change', '#automationType', function(){
            var selected = $(this).val();
            var timeSetterGroup = $('#timeSetterGroup'); // Get or create instance
            var daySetterGroup = $('#daySetterGroup');
            var singleDaySetterGroup = $('#singleDaySetterGroup');

            if (selected === 'day') {
                daySetterGroup.collapse('show');
                timeSetterGroup.collapse('show');
                singleDaySetterGroup.collapse('hide');
            } else if(selected === 'week') {
                singleDaySetterGroup.collapse('show');
                daySetterGroup.collapse('hide');
                timeSetterGroup.collapse('show');
            }else if (selected === 'time') {
                timeSetterGroup.collapse('show');
                daySetterGroup.collapse('show');
                singleDaySetterGroup.collapse('hide');
            } else {
                timeSetterGroup.collapse('hide');
            }
        });
    }

//region Head Section
    $(document).on('click', '.submitGroupAutoAssignTempo', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');
        var name = $(this).data('name');
        var user = $(this).data('user');

        $.ajax({
            url: '{{ route("observer.tasks.slgaatask") }}',
            method: 'POST',
            noLoading: true,
            data: {
                temp: temp,
                dept: dept,
                name: name,
                user: user,
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    assign_auto_group(temp, name, dept, 1);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    });

    $(document).on('click', '.removeMemberAutomationGroupList', function() {
        var temp = $(this).data('temp');
        var user = $(this).data('user');
        var dept = $(this).data('dept');
        var name = $(this).data('name');

        $.ajax({
            url: '{{ route("observer.tasks.rtaagtask") }}',
            method: 'POST',
            noLoading: true,
            data: {
                temp: temp,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    assign_auto_group(temp, name, dept, 1);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        })
    });

    $(document).on('click', '.removeMemberAutomationGroup', function() {
        var temp = $(this).data('temp');
        var group = $(this).data('group');
        var dept = $(this).data('dept');
        var name = $(this).data('name');

        Swal.fire({
            title: 'Are you sure you want to delete this group?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: '{{ route("observer.tasks.rtaagltask") }}',
                    method: 'POST',
                    data: {
                        temp: temp,
                        group: group
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            assign_auto_group(temp, name, dept, 1);
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove group automation task'
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

    $(document).on('click', '.taskGroupAutomationAssignSubmit', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');
        var name = $(this).data('name');
        var form = $('#formGroupAutomation').serialize();

        $.ajax({
            url: '{{ route("observer.tasks.slglaatask") }}',
            method: 'POST',
            data: form,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    pageContainer();
                    assign_auto_group(temp, name, dept, 1);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully assign automation task'
                    });
                } else if(response.status === 'required'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'There\'s some missing field'
                    });
                }  else if(response.status === 'timeGreater'){
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Time start can\'t be higher than end'
                    });
                } else if(response.status === 'onlyOne'){
                    Toast.fire({
                        icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'You can\'t assign a single member in a group task'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    });

    $(document).on('click', '#archiveTemp', function(){
        var temp = $(this).data('temp');

        Swal.fire({
            title: 'Are you sure you want to archive this template?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: '{{ route("observer.tasks.arctemp") }}',
                    method: 'POST',
                    data: {
                        temp: temp,
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
                            pageContainer();
                            Toast.fire({
                                icon: 'success',
                                title: 'Template Archived!',
                                text: 'Check the Archive tab if you want to view or restore it.'
                            });
                        } else if(response.status === 'exist'){
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
                            Toast.fire({
                                icon: 'error',
                                title: 'Template Cannot Be Archived!',
                                text: 'There\'s currently running task that using this template, you can archive it first before archiving this template.'
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

    $(document).on('click', '#settingsToCheck', function(){
        $('#autoCheckTaskModal').modal('show');

        var dept = $(this).data('dept');

        auto_check_layout(dept);
    });

    function auto_check_layout(dept){
        $.ajax({
            url: '{{ route("observer.tasks.vtctask") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(response) {

                var check_auto_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
                    </div>
                    <div class="col-12 my-3">
                        <h6 class="card-title mb-3">List of setted in automation checking task:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover m-0" id="selectedSoloAutomationTable">
                                <thead>
                                    <tr>
                                        <th class="pt-0">Task</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                                if(response.selected.length > 0){
                                    response.selected.forEach((select, index) => {

                                        check_auto_html += `
                                            <tr>
                                                <td>${select.title}</td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn border-0 nav-link removeTaskAutoCheck" data-temp="${select.template_id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                </td>
                                            </tr>
                                        `;
                                    })
                                } else {
                                    check_auto_html += `
                                    <tr class="align-middle">
                                        <td colspan="7" class="text-center">
                                            There's no existing task yet
                                        </td>
                                    </tr>
                                    `;
                                }
                                check_auto_html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <h6 class="card-title">List of Department Member: </h6>
                        <div class="list-div">`;
                            if(response.temp.length > 0){
                                response.temp.forEach((temp, index) => {

                                check_auto_html += `
                                <div class="list-card">
                                    <div class="list-content">
                                        <h4>Task Title: ${temp.title}</h4>
                                        <button class="btn btn-hover btn-primary list-assign submitTaskAutoCheck" data-temp="${temp.id}" data-dept="${dept}">Set Task Check Automation</button>
                                    </div>
                                </div>`;
                                });
                            } else {
                                check_auto_html += `
                                <div class="modal-body-bg border border-primary text-wrap text-center">
                                        <h2 class="text-primary m-3">There's no existing task in this department</h2>
                                </div>`;
                            }
                    check_auto_html += `
                            </div>
                        </div>
                    `;

                $('#autoCheckTask').html(check_auto_html);
                feather.replace();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.submitTaskAutoCheck', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');

        $.ajax({
            url: '{{ route("observer.tasks.satctask") }}',
            method: 'POST',
            data: {
                temp: temp,
                dept: dept
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    auto_check_layout(dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added to automation check task'
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

    $(document).on('click', '.removeTaskAutoCheck', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');

        $.ajax({
            url: '{{ route("observer.tasks.ratctask") }}',
            method: 'POST',
            data: {
                temp: temp,
                dept: dept
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    auto_check_layout(dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully remove to automation check task'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        })
    });

    $(document).on('click', '#approveTask', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: 'Are you sure you want to approve this task?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to approve it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.approvetask") }}',
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
                            pageContainer();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully approved task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '#declineTask', function() {
        var task = $(this).data('task');
        let selectedRating = 3;
        Swal.fire({
            title: 'Feedback to the user',
            html: `
                <div id="star-rating">
                    <i data-feather="star" class="star-icon" data-rate="1"></i>
                    <i data-feather="star" class="star-icon" data-rate="2"></i>
                    <i data-feather="star" class="star-icon" data-rate="3"></i>
                    <i data-feather="star" class="star-icon" data-rate="4"></i>
                    <i data-feather="star" class="star-icon" data-rate="5"></i>
                </div>
                <div style="display: flex; justify-content: center;">
                    <textarea id="feedbackText" class="swal2-textarea" placeholder="Enter your feedback here..."></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: "Submit Feedback",
            cancelButtonText: "Cancel",
            didOpen: () => {
                feather.replace(); // Render Feather icons

                let stars = document.querySelectorAll("#star-rating .star-icon");

                stars.forEach(star => {
                    star.addEventListener("click", function () {
                        selectedRating = this.getAttribute("data-rate");

                        // Reset stars
                        stars.forEach(s => s.classList.remove("selected"));

                        // Highlight selected stars
                        for (let i = 0; i < selectedRating; i++) {
                            stars[i].classList.add("selected");
                        }
                    });
                });
            },
            preConfirm: () => {
                let feedbackText = document.getElementById("feedbackText").value;
                let finalRating = selectedRating || 3; // Set default rating to 3 if none is selected

                if (feedbackText.trim() === "") {
                    Swal.showValidationMessage("Please enter your feedback!");
                    return false;
                }

                return { rating: finalRating, feedback: feedbackText };
            }
        }).then((result) => {
            if (result.isConfirmed) {

                // Send data to the server (AJAX request)
                $.ajax({
                    url: '{{ route("observer.tasks.declinetask") }}',
                    method: 'POST',
                    data: {
                        task: task,
                        rating: result.value.rating,
                        feedback: result.value.feedback
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully declined task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });

    });

    $(document).on('click', '#settingsDistribute', function(){
        $('#autoDistributeTaskModal').modal('show');

        var dept = $(this).data('dept');

        auto_distribute_layout(dept);
    });

    function auto_distribute_layout(dept){
        $.ajax({
            url: '{{ route("observer.tasks.vdtask") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(response) {

                var distribute_auto_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
                    </div>
                    <div class="col-12 my-3">
                        <h6 class="card-title mb-3">List of setted in automation task distribution to other department:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover m-0" id="selectedSoloAutomationTable">
                                <thead>
                                    <tr>
                                        <th class="pt-0">Task</th>
                                        <th class="pt-0">To Department</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                                if(response.selected.length > 0){
                                    response.selected.forEach((select, index) => {

                                        distribute_auto_html += `
                                            <tr>
                                                <td>${select.title}</td>
                                                <td>${select.to_department_name}</td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn border-0 nav-link removeTaskAutoDistribute" data-temp="${select.template_id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                </td>
                                            </tr>
                                        `;
                                    })
                                } else {
                                    distribute_auto_html += `
                                    <tr class="align-middle">
                                        <td colspan="7" class="text-center">
                                            There's no existing task yet
                                        </td>
                                    </tr>
                                    `;
                                }
                                distribute_auto_html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <form id="distributeFormDept">
                            <label for="department" class="form-label">Department</label>
                            <select class="form-select mb-3" name="department_id" id="department">
                                `;
                                if(response.department.length > 0){
                                   response.department.forEach((dept, index) => {
                                        distribute_auto_html += `<option value="${dept.id}">${dept.name}</option>`;
                                   })
                                }
                                distribute_auto_html += `
                            </select>
                        </form>
                        <h6 class="card-title">List of Department Member: </h6>
                        <div class="list-div">`;
                            if(response.temp.length > 0){
                                response.temp.forEach((temp, index) => {

                                distribute_auto_html += `
                                <div class="list-card">
                                    <div class="list-content">
                                        <h4>Task Title: ${temp.title}</h4>
                                        <button class="btn btn-hover btn-primary list-assign submitTaskAutoDistribute" data-temp="${temp.id}" data-dept="${dept}">Set Task Distribution Automation</button>
                                    </div>
                                </div>`;
                                });
                            } else {
                                distribute_auto_html += `
                                <div class="modal-body-bg border border-primary text-wrap text-center">
                                        <h2 class="text-primary m-3">There's no existing task in this department</h2>
                                </div>`;
                            }
                    distribute_auto_html += `
                            </div>
                        </div>
                    `;

                $('#autoDistributeTask').html(distribute_auto_html);
                feather.replace();
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.submitTaskAutoDistribute', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');
        var form = $('#distributeFormDept').serialize() + '&temp=' + temp + '&dept=' + dept;

        $.ajax({
            url: '{{ route("observer.tasks.sadtask") }}',
            method: 'POST',
            data: form,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    auto_distribute_layout(dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added to automation check task'
                    });
                } else if(response.status === 'exist'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'This template already automated in this department'
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

    $(document).on('click', '.removeTaskAutoDistribute', function() {
        var temp = $(this).data('temp');
        var dept = $(this).data('dept');


        $.ajax({
            url: '{{ route("observer.tasks.radtask") }}',
            method: 'POST',
            data: {
                temp: temp,
                dept: dept
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    auto_distribute_layout(dept);
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully added to automation check task'
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

    $(document).on('click', '#approveTaskDistribution', function() {
        var dist = $(this).data('dist');

        Swal.fire({
            title: 'Are you sure you want to accept this task distribution?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to accept it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.adisttask") }}',
                    method: 'POST',
                    data: {
                        dist: dist
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully accept task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '#declineTaskDistribution', function() {
        var dist = $(this).data('dist');

        Swal.fire({
            title: 'Are you sure you want to decline this task distribution?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to decline it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.ddisttask") }}',
                    method: 'POST',
                    data: {
                        dist: dist
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully decline task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '#distributeTaskBtn', function() {
        $('#manuallyDistributeTaskModal').modal('show');
        var task = $(this).data('task');
        $('#submitManuallyDistribute').data('task', task);
    })

    $(document).on('click', '#submitManuallyDistribute', function() {
        var task = $(this).data('task');
        var form = $('#manuallyDistributeForm').serialize() + '&task=' + task ;
        Swal.fire({
            title: 'Are you sure you want to distribute this task to other department?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to distribute it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.disttask") }}',
                    method: 'POST',
                    data: form + '&cont=0',
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully distribute task'
                            });
                            $('#manuallyDistributeTaskModal').modal('hide');
                        } else if(response.status === 'exist'){
                            Swal.fire({
                                title: 'This task is already sended as request to department you choose. Do you still want to distribute it?',
                                icon: 'question',
                                text: response.message,
                                showCancelButton: true,
                                confirmButtonText: 'Yes! I want to distribute it',
                                cancelButtonText: 'No, I don\'t want to'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: '{{ route("observer.tasks.disttask") }}',
                                        method: 'POST',
                                        data: form + '&cont=1',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully distribute task'
                                                });
                                                $('#manuallyDistributeTaskModal').modal('hide');
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Error occurred:', xhr.responseText);
                                            console.error('Error occurred:', status);
                                            console.error('Error occurred:', error);
                                        }

                                    })
                                }
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    })

    $(document).on('click', '#approveAllTaskDistribution', function() {
        Swal.fire({
            title: 'Are you sure you want to accept all this task distribution?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to accept it all',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.aadisttask") }}',
                    method: 'POST',
                    data: {
                        dist: 0
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            pageContainer();
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully accept task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }
                })
            }
        });
    });

    $(document).on('click', '#declineAllTaskDistribution', function() {
        Swal.fire({
            title: 'Are you sure you want to decline all this task distribution?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to decline it all',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.dadisttask") }}',
                    method: 'POST',
                    data: {
                        dist: 0
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully decline task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '#settingsAccept', function() {
        var dept = $(this).data('dept');
        $('#autoAcceptDistributeTaskModal').modal('show');
        auto_accept_distribute_layout(dept);
    });

    function auto_accept_distribute_layout(dept){
        $.ajax({
            url: '{{ route("observer.tasks.vaadtask") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(response) {

                var accept_distribute_auto_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${name}</b></h6>
                    </div>
                    <div class="col-12 my-3">
                        <h6 class="card-title mb-3">List of setted in automation accept distributed task:</h6>
                        <div class="table-responsive">
                            <table class="table table-hover m-0" id="selectedSoloAutomationTable">
                                <thead>
                                    <tr>
                                        <th class="pt-0">Task</th>
                                        <th class="pt-0">From Department</th>
                                        <th class="pt-0">Action</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                                if(response.selected.length > 0){
                                    response.selected.forEach((select, index) => {
                                        var task = select.task_title;
                                        var department = select.department;
                                        var selects = select.select;

                                        accept_distribute_auto_html += `
                                            <tr>
                                                <td>${task.title}</td>
                                                <td>${department.name}</td>
                                                <td class="action-buttons">
                                                    <button type="button" class="btn border-0 nav-link removeTaskAutoAcceptDistribute" data-temp="${selects.template_id}" data-todept="${selects.department_id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                </td>
                                            </tr>
                                        `;
                                    })
                                } else {
                                    accept_distribute_auto_html += `
                                    <tr class="align-middle">
                                        <td colspan="7" class="text-center">
                                            There's no existing task yet
                                        </td>
                                    </tr>
                                    `;
                                }
                                accept_distribute_auto_html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 mt-3"
                        <h6 class="card-title">List of Department Member: </h6>
                        <div class="list-div">`;
                            if(response.accepts.length > 0){
                                response.accepts.forEach((accepts, index) => {
                                var task = accepts.task_title;
                                var department = accepts.department;
                                var accepted = accepts.accepted;
                                accept_distribute_auto_html += `
                                <div class="list-card">
                                    <div class="list-content">
                                        <h6>Department: ${department.name}</h6>
                                        <h6>Task Title: ${task.title}</h6>
                                        <button class="btn btn-hover btn-primary list-assign submitTaskAutoAcceptDistribute" data-temp="${accepted.template_id}" data-todept="${accepted.department_id}" data-dept="${dept}">Set Accept Distribution Automation</button>
                                    </div>
                                </div>`;
                                });
                            } else {
                                accept_distribute_auto_html += `
                                <div class="modal-body-bg border border-primary text-wrap text-center">
                                        <h2 class="text-primary m-3">There's no existing task in this department</h2>
                                </div>`;
                            }
                    accept_distribute_auto_html += `
                            </div>
                        </div>
                    `;

                $('#autoAcceptDistributeTask').html(accept_distribute_auto_html);
                setTimeout(() => {
                    feather.replace();
                }, 300);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.submitTaskAutoAcceptDistribute', function() {
        var dept = $(this).data('dept');
        var todept = $(this).data('todept');
        var temp = $(this).data('temp');

        Swal.fire({
            title: 'Are you sure you want to set this as automatic accept distributed task?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.saadtask") }}',
                    method: 'POST',
                    data: {
                        dept: dept,
                        todept: todept,
                        temp: temp,
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            auto_accept_distribute_layout(dept)
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully accept task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '.removeTaskAutoAcceptDistribute', function() {
        var dept = $(this).data('dept');
        var todept = $(this).data('todept');
        var temp = $(this).data('temp');

        Swal.fire({
            title: 'Are you sure you want to remove this as automatic accept distributed task?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to remove it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.raadtask") }}',
                    method: 'POST',
                    data: {
                        dept: dept,
                        todept: todept,
                        temp: temp,
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            auto_accept_distribute_layout(dept)
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully accept task'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }

                })
            }
        });
    });

    $(document).on('click', '#linkTaskBtn', function() {
        var task = $(this).data('task');
        var title = $(this).data('name')
        Swal.fire({
            title: 'Choose Task To Link',
            text: 'Select an option below:',
            icon: 'info',
            showCancelButton: true,
            showDenyButton: true, // Add a separate cancel button
            confirmButtonText: 'New Task',
            cancelButtonText: 'Cancel',  // Explicit Cancel button
            denyButtonText: 'Ongoing Task', // New deny button for ongoing task
            allowOutsideClick: false,
            allowEscapeKey: false,
            customClass: {
                confirmButton: 'swal2-confirm btn',  // Bootstrap class applied
                denyButton: 'swal2-deny btn bg-danger border border-danger text-white',
                cancelButton: 'swal2-cancel btn bg-warning border border-0 text-black'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('#NewTaskLinkModal').modal('show');
                link_new_task(task, 0);
            } else if (result.isDenied) {
                $('#OngoingTaskLinkModal').modal('show');
                $('.toLinkOngoingTitle').html(title);
                $('.linkToOngoingTask').data('link', task);
            }
        });
    });

    function link_new_task(task, assign = 0){
        $('#linkNewDisplay').html('');
        $.ajax({
            url: '{{ route("observer.tasks.vnttlink") }}',
            method: 'GET',
            data: {
                task: task
            },
            dataType: 'json',
            success: function(response) {
                var title = response.name;
                var link_new_html = `
                    <div class="col-12 mb-3 mt-3">
                        <h6 class="card-title">TASK NAME: <b class="text-primary">${title}</b></h6>
                    </div>
                    <div class="col-md-12 mb-3">
                        <form id="linkTaskForm">
                            <label for="templates" class="form-label">Templates: </label>
                            <select class="form-select mb-3" name="templates_id" id="templates">
                                <option value="" selected disabled>Select Template</option>
                                `;
                                if(response.templates.length > 0){
                                    response.templates.forEach((temp, index) => {
                                        link_new_html += `<option value="${temp.type}, ${task}, ${temp.id}">${temp.title}</option>`;
                                    })
                                }
                                link_new_html += `
                            </select>
                            <label class="form-check-label" for="dueAssign">
                                Set Due Date
                            </label>
                            <input class="form-control border border-primary mb-3" type="date" name="due" id="dueAssign" required>
                        </form>
                    </div>
                    <div class="collapse mb-3" id="soloUserDiv">
                        <div class="card card-body border-secondary rounded-2">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <h6 class="card-title mb-2">Type: Solo Task</h6>
                                    <h6 class="card-title">List of Department Member: </h6>
                                    <div class="list-div">`;
                                if(response.soloUsers.length > 0){
                                    response.soloUsers.forEach((users, index) => {

                                    var profile = users.profile;
                                    var count = users.count;
                                    var department = users.department;

                                    var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                                    link_new_html += `
                                    <div class="list-card">
                                        <div class="list-header justify-content-start align-items-center">
                                            <img src="${photoUrl}" alt="image">
                                            <h6 class="ms-2">${profile.name}</h6>
                                        </div>
                                        <div class="list-content">
                                            <h6>Currently Holding Task: ${count}</h6>
                                            <h6>Department: ${department}</h6>
                                            <button class="btn btn-hover btn-primary list-assign submitSoloLink" data-user="${profile.id}">Assign</button>
                                        </div>
                                    </div>`;
                                    });
                                } else {
                                    link_new_html += `
                                    <div class="modal-body-bg border border-primary text-wrap text-center">
                                            <h2 class="text-primary m-3">There's no existing member in this department</h2>
                                    </div>`;
                                }
                                link_new_html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="collapse mb-3" id="groupUserDiv">
                        <div class="card card-body border-secondary rounded-2">
                            <div class="row">
                                <div class="col-12 mb-3">
                                    <h6 class="card-title">Type: Group Task</h6>
                                    <table class="table table-hover m-0" id="selectedGroupTable">
                                        <thead>
                                        <tr>
                                            <th class="pt-0">Profile</th>
                                            <th class="pt-0">Name</th>
                                            <th class="pt-0">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>`;
                                        if(response.selectedGroupUser.length > 0){
                                            response.selectedGroupUser.forEach((users, index) => {
                                                var profile = users.profile;
                                                var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                                link_new_html += `
                                                    <tr>
                                                        <td>
                                                            <img src="${photoUrl}" alt="image">
                                                        </td>
                                                        <td>${profile.name}</td>
                                                        <td class="action-buttons">
                                                            <button type="button" class="btn border-0 nav-link removeMemberLinkTempoList" data-user="${profile.id}" data-task="${task}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                        </td>
                                                    </tr>
                                                `;
                                            })
                                        } else {
                                            link_new_html += `
                                            <tr class="align-middle">
                                                <td colspan="3" class="text-center">
                                                    There's no existing member yet
                                                </td>
                                            </tr>
                                            `;
                                        }
                                        link_new_html += `
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-12 mb-3 d-grid gap-2">
                                    <button class="btn btn-hover btn-success submitGroupLink">Submit Group To Assign With Linked Task</button>
                                </div>
                                <div class="col-12 mb-3">
                                    <h6 class="card-title">List of Department Member: </h6>
                                    <div class="list-div">`;
                                if(response.groupUsers.length > 0){
                                    response.groupUsers.forEach((users, index) => {

                                    var profile = users.profile;
                                    var count = users.count;
                                    var department = users.department;

                                    var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                                    link_new_html += `
                                    <div class="list-card">
                                        <div class="list-header justify-content-start align-items-center">
                                            <img src="${photoUrl}" alt="image">
                                            <h6 class="ms-2">${profile.name}</h6>
                                        </div>
                                        <div class="list-content">
                                            <h6>Currently Holding Task: ${count}</h6>
                                            <h6>Department: ${department}</h6>
                                            <button class="btn btn-hover btn-primary list-assign submitMemberLinkTempoList" data-user="${profile.id}" data-task="${task}">Assign</button>
                                        </div>
                                    </div>`;
                                    });
                                } else {
                                    link_new_html += `
                                    <div class="modal-body-bg border border-primary text-wrap text-center">
                                            <h2 class="text-primary m-3">There's no existing member in this department</h2>
                                    </div>`;
                                }
                                link_new_html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#linkNewDisplay').html(link_new_html);
                feather.replace();
                $(document).off('change', '#templates');
                $(document).on('change', '#templates', function(){
                    var selected = $(this).val();
                    var values = selected.split(",");
                    var soloUser = $('#soloUserDiv'); // Get or create instance
                    var groupUser = $('#groupUserDiv');

                    if (values[0] === 'Solo') {
                        soloUser.collapse('show');
                        groupUser.collapse('hide');
                    } else if(values[0] === 'Group') {
                        soloUser.collapse('hide');
                        groupUser.collapse('show');
                    }

                    if(assign === 0){
                        var taskValue = values[1].trim();
                        $.ajax({
                            url: '{{ route("observer.tasks.checklinktemp") }}',
                            method: 'GET',
                            data: {
                                task: taskValue
                            },
                            dataType: 'json',
                            success: function(response) {
                                if(response.status === 'exist'){
                                    Swal.fire({
                                        title: 'There\'s existing selected user in group link assigning do you still want to use it?',
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes! I want to use it',
                                        cancelButtonText: 'No, I don\'t want to'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            // User chose to keep the existing assignment
                                            return;
                                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                                            $.ajax({
                                                url: '{{ route("observer.tasks.removelinktemp") }}',
                                                method: 'POST',
                                                data: {
                                                    task: taskValue,
                                                },
                                                dataType: 'json',
                                                headers: {
                                                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                                },
                                                success: function(response) {
                                                    if(response.status === 'success') {
                                                        link_new_task(task, 1);
                                                        Toast.fire({
                                                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                            title: 'Successfully removed selected user'
                                                        });
                                                    }
                                                },
                                                error: function(xhr, status, error) {
                                                    console.error('Error occurred:', xhr.responseText);
                                                    console.error('Error occurred:', status);
                                                    console.error('Error occurred:', error);
                                                }

                                            })
                                        }
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
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        })
    }

    $(document).on('click', '.submitMemberLinkTempoList', function() {
        var user = $(this).data('user');
        var task = $(this).data('task');

        $.ajax({
            url: '{{ route("observer.tasks.addmemberlinktemp") }}',
            method: 'POST',
            data: {
                task: task,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    updateGroupUserDiv(response.datas, task)
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        });
    });

    $(document).on('click', '.removeMemberLinkTempoList', function() {
        var user = $(this).data('user');
        var task = $(this).data('task');

        $.ajax({
            url: '{{ route("observer.tasks.removememberlinktemp") }}',
            method: 'POST',
            data: {
                task: task,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    updateGroupUserDiv(response.datas, task)
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }

        });
    });

    function updateGroupUserDiv(response, task) {
        var link_new_html = `
        <div class="card card-body border-secondary rounded-2">
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="card-title">Type: Group Task</h6>
                    <table class="table table-hover m-0" id="selectedGroupTable">
                        <thead>
                            <tr>
                                <th class="pt-0">Profile</th>
                                <th class="pt-0">Name</th>
                                <th class="pt-0">Action</th>
                            </tr>
                        </thead>
                        <tbody>`;

        if(response.selectedGroupUser.length > 0) {
            response.selectedGroupUser.forEach((users) => {
                var profile = users.profile;
                var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                link_new_html += `
                    <tr>
                        <td><img src="${photoUrl}" alt="image"></td>
                        <td>${profile.name}</td>
                        <td class="action-buttons">
                            <button type="button" class="btn border-0 nav-link removeMemberLinkTempoList"
                                data-user="${profile.id}" data-task="${task}">
                                <i data-feather="trash" class="icon-sm icon-wiggle"></i>
                            </button>
                        </td>
                    </tr>`;
            });
        } else {
            link_new_html += `
            <tr class="align-middle">
                <td colspan="3" class="text-center">There's no existing member yet</td>
            </tr>`;
        }

        link_new_html += `</tbody></table></div>
        <div class="col-12 mb-3 d-grid gap-2">
            <button class="btn btn-hover btn-success submitGroupLink">Submit Group To Assign With Linked Task</button>
        </div>
        <div class="col-12 mb-3"><h6 class="card-title">List of Department Member:</h6><div class="list-div">`;

        if(response.groupUsers.length > 0) {
            response.groupUsers.forEach((users) => {
                var profile = users.profile;
                var count = users.count;
                var department = users.department;
                var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                link_new_html += `
                <div class="list-card">
                    <div class="list-header justify-content-start align-items-center">
                        <img src="${photoUrl}" alt="image">
                        <h6 class="ms-2">${profile.name}</h6>
                    </div>
                    <div class="list-content">
                        <h6>Currently Holding Task: ${count}</h6>
                        <h6>Department: ${department}</h6>
                        <button class="btn btn-hover btn-primary list-assign submitMemberLinkTempoList"
                            data-user="${profile.id}" data-task="${task}">
                            Assign
                        </button>
                    </div>
                </div>`;
            });
        } else {
            link_new_html += `
            <div class="modal-body-bg border border-primary text-wrap text-center">
                <h2 class="text-primary m-3">There's no existing member in this department</h2>
            </div>`;
        }

        link_new_html += `</div></div></div>`;

        // ✅ Replace only #groupUserDiv content
        $('#groupUserDiv').html(link_new_html);
        feather.replace();
    }

    $(document).on('click', '.submitSoloLink', function() {
        var selected = $('#templates').val();
        var values = selected.split(",");
        var user = $(this).data('user');
        var form = $('#linkTaskForm').serialize() + '&temp='+values[2]+'&type='+values[0]+'&task='+values[1]+'&user='+user;

        Swal.fire({
            title: 'Are you sure you want to link it and assign to this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to link and assign it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.linkandassigntask") }}',
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully link and assign task'
                            });
                            $('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Must enter due date before assigning'
                            });
                        } else if(response.status === 'existTask'){
                            Swal.fire({
                                title: 'This task is already assigned to this member, do you want to assign it again on the same user?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes! I want to assign it',
                                cancelButtonText: 'No, I don\'t want to'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // If user confirmed, proceed with AJAX request
                                    $.ajax({
                                        url: '{{ route("observer.tasks.linkandassigntask") }}',
                                        method: 'POST',
                                        data: form + '&reassign=1',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                pageContainer();
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully assign task'
                                                });
                                                $('#NewTaskLinkModal').modal('hide');
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error'
                                                });
                                            } else if(response.status === 'errorTask') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    text: response.message
                                                });
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'You need to enter due date first'
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
                        }  else if(response.status === 'errorTask') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                text: response.message
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

    $(document).on('click', '.submitGroupLink', function() {
        var selected = $('#templates').val();
        var values = selected.split(",");
        var form = $('#linkTaskForm').serialize() + '&temp='+values[2]+'&type='+values[0]+'&task='+values[1];

        Swal.fire({
            title: 'Are you sure you want to link it and assign to this group?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to link and assign it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.grouplinkandassigntask") }}',
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully link and assign task'
                            });
                            $('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Must enter due date before assigning'
                            });
                        } else if(response.status === 'existTask'){
                            Swal.fire({
                                title: 'This task is already assigned to this member, do you want to assign it again on the same user?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes! I want to assign it',
                                cancelButtonText: 'No, I don\'t want to'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // If user confirmed, proceed with AJAX request
                                    $.ajax({
                                        url: '{{ route("observer.tasks.grouplinkandassigntask") }}',
                                        method: 'POST',
                                        data: form + '&reassign=1',
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                pageContainer();
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully assign task'
                                                });
                                                $('#NewTaskLinkModal').modal('hide');
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error'
                                                });
                                            } else if(response.status === 'errorTask') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    text: response.message
                                                });
                                            } else if(response.status === 'error') {
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'You need to enter due date first'
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
                        }  else if(response.status === 'errorTask') {
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                text: response.message
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

    $(document).on('click', '.linkToOngoingTask', function() {
        var task = $(this).data('task');
        var link = $(this).data('link');
        Swal.fire({
            title: 'Are you sure you want to link it and assign to this user?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to link and assign it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.linkandassignongoingtask") }}',
                    method: 'POST',
                    data: {
                        task: task,
                        link: link
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully link and assign to ongoing task'
                            });
                            $('#OngoingTaskLinkModal').modal('hide');
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'warning',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: response.message
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
//endregion

//region Link Task
    $(document).on('click', '#settingsLinkNew', function() {
        var dept = $(this).data('dept');
        $('#autoLinkToNewTaskModal').modal('show');

        link_auto_layout(dept);
    });

    function link_auto_layout(dept, page = 0, assign = 0){
        $.ajax({
            url: '{{ route("observer.tasks.viewlinkauto") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            success: function(response) {
                var link_auto_html = `
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link ${page === 0 ? 'active' : ''}" data-bs-toggle="tab" href="#soloLinkAuto" role="tab" aria-selected="false">
                                Solo Task
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link ${page === 1 ? 'active' : ''}" data-bs-toggle="tab" href="#groupLinkAuto" role="tab" aria-selected="false">
                                Group Task
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content border border-top-0 p-3 contentContainer">
                        <div class="tab-pane fade ${page === 0 ? 'active show' : ''}" id="soloLinkAuto" role="tabpanel" >
                            <div class="row">
                                <form id="formLinkSoloAutomation">
                                    @csrf
                                    <div class="col-md-12 mb-3">
                                        <label for="templates_link">Templates</label>
                                        <select id="templates_link" name="template_id" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>`;
                                            if(response.templateSolo.length > 0){
                                                response.templateSolo.forEach((templates, index) => {

                                                    link_auto_html += `<option value="${templates.id}">${templates.title}</option>`;

                                                });
                                            }
                                            link_auto_html += `
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="templates_to_link">To Link</label>
                                        <select id="templates_to_link" name="link_id" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>`;
                                            if(response.linkTask.length > 0){
                                                response.linkTask.forEach((templates, index) => {
                                                    var task = templates.task_title;
                                                    var department = templates.department;
                                                    var accepted = templates.accepted;
                                                    link_auto_html += `<option value="${accepted.template_id}">${task.title}</option>`;

                                                });
                                            }
                                            link_auto_html += `
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12 mb-3">
                                        <label class="form-check-label" for="dueAssign">
                                            Set Due Date (Max 31 Days)
                                        </label>
                                        <select id="dueAssign" name="due" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="1">1 Day</option>
                                            <option value="2">2 Days</option>
                                            <option value="3">3 Days</option>
                                            <option value="4">4 Days</option>
                                            <option value="5">5 Days</option>
                                            <option value="6">6 Days</option>
                                            <option value="7">7 Days</option>
                                            <option value="8">8 Days</option>
                                            <option value="9">9 Days</option>
                                            <option value="10">10 Days</option>
                                            <option value="11">11 Days</option>
                                            <option value="12">12 Days</option>
                                            <option value="13">13 Days</option>
                                            <option value="14">14 Days</option>
                                            <option value="15">15 Days</option>
                                            <option value="16">16 Days</option>
                                            <option value="17">17 Days</option>
                                            <option value="18">18 Days</option>
                                            <option value="19">19 Days</option>
                                            <option value="20">20 Days</option>
                                            <option value="21">21 Days</option>
                                            <option value="22">22 Days</option>
                                            <option value="23">23 Days</option>
                                            <option value="24">24 Days</option>
                                            <option value="25">25 Days</option>
                                            <option value="26">26 Days</option>
                                            <option value="27">27 Days</option>
                                            <option value="28">28 Days</option>
                                            <option value="29">29 Days</option>
                                            <option value="30">30 Days</option>
                                            <option value="31">31 Days</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="automationType">Automation Type</label>
                                        <select id="automationType" name="type" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="week">Every Week (Once A Week)</option>
                                            <option value="day">Every Day (Once A Day)</option>
                                            <option value="time">Time to Time (Anytime if Finished Task)</option>
                                        </select>
                                    </div>
                                    <div class="collapse mt-3" id="singleDaySetterSolo">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="startDay">Select Day:</label>
                                                    <select id="startDay" name="only_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collapse mt-3" id="daySetterSolo">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="startDay">Select Start Day:</label>
                                                    <select id="startDay" name="start_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="endDay">Select End Day:</label>
                                                    <select id="endDay" name="end_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collapse mt-3" id="timeSetterSolo">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="startTime" >Select Start Time:</label>
                                                    <input class="form-control" type="time" id="startTime" name="start_time">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="endTime">Select End Time:</label>
                                                    <input class="form-control" type="time" id="endTime" name="end_time">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="col-12 my-3">
                                    <h6 class="card-title mb-3">List of setted in automation task assigning employee:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-0" id="selectedSoloAutomationTable">
                                            <thead>
                                                <tr>
                                                    <th class="pt-0">Profile</th>
                                                    <th class="pt-0">Name</th>
                                                    <th class="pt-0">Type</th>
                                                    <th class="pt-0">Day</th>
                                                    <th class="pt-0">Time</th>
                                                    <th class="pt-0">Task Due Date</th>
                                                    <th class="pt-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;
                                            if(response.selectedUserSolo.length > 0){
                                                response.selectedUserSolo.forEach((users, index) => {
                                                    var profile = users.profile;
                                                    var auto = users.autoSolo;

                                                    var TypeWF = '';
                                                    if(auto.type === 'time'){
                                                        TypeWF = 'Time to Time';
                                                    } else if(auto.type === 'day'){
                                                        TypeWF = 'Every Day';
                                                    } else if(auto.type === 'week'){
                                                        TypeWF = 'Every Week';
                                                    }
                                                    var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                                    link_auto_html += `
                                                        <tr>
                                                            <td>
                                                                <img src="${photoUrl}" alt="image">
                                                            </td>
                                                            <td>${profile.name}</td>
                                                            <td>${TypeWF}</td>
                                                            <td>${auto.start_day} - ${auto.end_day}</td>
                                                            <td>${auto.start_time !== null && auto.end_time !== null ? auto.start_time+' - '+auto.end_time : 'Time is not available in '+auto.type}</td>
                                                            <td>${auto.due} Day${auto.due != 1 ? 's' : ''}</td>
                                                            <td class="action-buttons">
                                                                <button type="button" class="btn border-0 nav-link removeSoloLinkAuto" data-user="${profile.id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                            </td>
                                                        </tr>
                                                    `;
                                                })
                                            } else {
                                                link_auto_html += `
                                                <tr class="align-middle">
                                                    <td colspan="7" class="text-center">
                                                        There's no existing member yet
                                                    </td>
                                                </tr>
                                                `;
                                            }
                                            link_auto_html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 mt-3">
                                    <h6 class="card-title">List of Department Member: </h6>
                                    <div class="list-div">`;
                                        if(response.usersSolo.length > 0){
                                            response.usersSolo.forEach((users, index) => {

                                            var profile = users.profile;
                                            var count = users.count;
                                            var department = users.department;

                                            var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                                            link_auto_html += `
                                            <div class="list-card">
                                                <div class="list-header justify-content-start align-items-center">
                                                    <img src="${photoUrl}" alt="image">
                                                    <h6 class="ms-2">${profile.name}</h6>
                                                </div>
                                                <div class="list-content">
                                                    <h6>Currently Holding Task: ${count}</h6>
                                                    <h6>Department: ${department}</h6>
                                                    <button class="btn btn-hover btn-primary list-assign submitSoloLinkAuto" data-user="${profile.id}" data-dept="${dept}">Assign</button>
                                                </div>
                                            </div>`;
                                            });
                                        } else {
                                            link_auto_html += `
                                            <div class="modal-body-bg border border-primary text-wrap text-center">
                                                    <h2 class="text-primary m-3">There's no existing member in this department</h2>
                                            </div>`;
                                        }
                                link_auto_html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade ${page === 1 ? 'active show' : ''}" id="groupLinkAuto" role="tabpanel" >
                            <div class="row">
                                <form id="formLinkGroupAutomation">
                                    @csrf
                                    <div class="col-md-12 mb-3">
                                        <label for="templates_link">Templates</label>
                                        <select id="templates_link" name="template_id" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>`;
                                            if(response.templateGroup.length > 0){
                                                response.templateGroup.forEach((templates, index) => {

                                                    link_auto_html += `<option value="${templates.id}">${templates.title}</option>`;

                                                });
                                            }
                                            link_auto_html += `
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="templates_to_link">To Link</label>
                                        <select id="templates_to_link" name="link_id" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>`;
                                            if(response.linkTask.length > 0){
                                                response.linkTask.forEach((templates, index) => {
                                                    var task = templates.task_title;
                                                    var department = templates.department;
                                                    var accepted = templates.accepted;
                                                    link_auto_html += `<option value="${accepted.template_id}">${task.title}</option>`;

                                                });
                                            }
                                            link_auto_html += `
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-check-label" for="dueAssign">
                                            Set Due Date (Max 31 Days)
                                        </label>
                                        <select id="dueAssign" name="due" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="1">1 Day</option>
                                            <option value="2">2 Days</option>
                                            <option value="3">3 Days</option>
                                            <option value="4">4 Days</option>
                                            <option value="5">5 Days</option>
                                            <option value="6">6 Days</option>
                                            <option value="7">7 Days</option>
                                            <option value="8">8 Days</option>
                                            <option value="9">9 Days</option>
                                            <option value="10">10 Days</option>
                                            <option value="11">11 Days</option>
                                            <option value="12">12 Days</option>
                                            <option value="13">13 Days</option>
                                            <option value="14">14 Days</option>
                                            <option value="15">15 Days</option>
                                            <option value="16">16 Days</option>
                                            <option value="17">17 Days</option>
                                            <option value="18">18 Days</option>
                                            <option value="19">19 Days</option>
                                            <option value="20">20 Days</option>
                                            <option value="21">21 Days</option>
                                            <option value="22">22 Days</option>
                                            <option value="23">23 Days</option>
                                            <option value="24">24 Days</option>
                                            <option value="25">25 Days</option>
                                            <option value="26">26 Days</option>
                                            <option value="27">27 Days</option>
                                            <option value="28">28 Days</option>
                                            <option value="29">29 Days</option>
                                            <option value="30">30 Days</option>
                                            <option value="31">31 Days</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="automationTypeGroup">Automation Type</label>
                                        <select id="automationTypeGroup" name="type" class="form-select" aria-label="Default select example">
                                            <option selected value="" disabled>Open this select menu</option>
                                            <option value="week">Every Week (Once A Week)</option>
                                            <option value="day">Every Day (Once A Day)</option>
                                            <option value="time">Time to Time (Anytime if Finished Task)</option>
                                        </select>
                                    </div>
                                    <div class="collapse mt-3" id="singleDaySetterGroup">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="startDay">Select Day:</label>
                                                    <select id="startDay" name="only_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collapse mt-3" id="daySetterGroup">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="startDay">Select Start Day:</label>
                                                    <select id="startDay" name="start_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="endDay">Select End Day:</label>
                                                    <select id="endDay" name="end_day" class="form-select" aria-label="Default select example">
                                                        <option selected value="" disabled>Open this select menu</option>
                                                        <option value="sunday">Sunday</option>
                                                        <option value="monday">Monday</option>
                                                        <option value="tuesday">Tuesday</option>
                                                        <option value="wednesday">Wednesday</option>
                                                        <option value="thursday">Thursday</option>
                                                        <option value="friday">Friday</option>
                                                        <option value="saturday">Saturday</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collapse mt-3" id="timeSetterGroup">
                                        <div class="card card-body border-secondary rounded-2">
                                            <div class="row">
                                                <div class="col-6 mb-3">
                                                    <label for="startTime" >Select Start Time:</label>
                                                    <input class="form-control" type="time" id="startTime" name="start_time">
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="endTime">Select End Time:</label>
                                                    <input class="form-control" type="time" id="endTime" name="end_time">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="col-12 my-3">
                                    <h6 class="card-title mb-3">List of setted in automation task assigning employee:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-0" id="selectedGroupAutomationTable">
                                            <thead>
                                                <tr>
                                                    <th class="pt-0">Group No.</th>
                                                    <th class="pt-0">Type</th>
                                                    <th class="pt-0">Day</th>
                                                    <th class="pt-0">Time</th>
                                                    <th class="pt-0">Task Due Date</th>
                                                    <th class="pt-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;
                                            if(response.selectedGroupUser.length > 0){
                                                response.selectedGroupUser.forEach((users, index) => {
                                                    var auto = users.autoGroup;

                                                    var TypeWF = '';
                                                    if(auto.type === 'time'){
                                                        TypeWF = 'Time to Time';
                                                    } else if(auto.type === 'day'){
                                                        TypeWF = 'Every Day';
                                                    } else if(auto.type === 'week'){
                                                        TypeWF = 'Every Week';
                                                    }
                                                    link_auto_html += `
                                                        <tr>
                                                            <td>${index + 1}</td>
                                                            <td>${TypeWF}</td>
                                                            <td>${auto.start_day} - ${auto.end_day}</td>
                                                            <td>${auto.start_time !== null && auto.end_time !== null ? auto.start_time+' - '+auto.end_time : 'Time is not available in '+auto.type}</td>
                                                            <td>${auto.due} Day${auto.due != 1 ? 's' : ''}</td>
                                                            <td class="action-buttons d-flex">
                                                                <button type="button" class="btn me-2 border-0 nav-link removeLinkGroupAuto" data-group="${auto.id}" data-temp="${auto.template_id}" data-link="${auto.link_id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                            </td>
                                                        </tr>
                                                    `;
                                                })
                                            } else {
                                                link_auto_html += `
                                                <tr class="align-middle">
                                                    <td colspan="7" class="text-center">
                                                        There's no existing member yet
                                                    </td>
                                                </tr>
                                                `;
                                            }
                                            link_auto_html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 my-3">
                                    <h6 class="card-title mb-3">List of to set in automation task assigning employee:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-hover m-0" id="selectedUserToGroupAutomationTable">
                                            <thead>
                                                <tr>
                                                    <th class="pt-0">Profile</th>
                                                    <th class="pt-0">Name</th>
                                                    <th class="pt-0">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>`;
                                            if(response.selectedUserToGroup.length > 0){
                                                response.selectedUserToGroup.forEach((users, index) => {
                                                    var profile = users.profile;
                                                    var auto = users.autoSolo;
                                                    var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                                    link_auto_html += `
                                                        <tr>
                                                            <td>
                                                                <img src="${photoUrl}" alt="image">
                                                            </td>
                                                            <td>${profile.name}</td>
                                                            <td class="action-buttons">
                                                                <button type="button" class="btn border-0 nav-link removeMemberToTempoGroupLink" data-user="${profile.id}" data-dept="${dept}"><i data-feather="trash" class="icon-sm icon-wiggle"></i></button>
                                                            </td>
                                                        </tr>
                                                    `;
                                                })
                                            } else {
                                                link_auto_html += `
                                                <tr class="align-middle">
                                                    <td colspan="6" class="text-center">
                                                        There's no existing member yet
                                                    </td>
                                                </tr>
                                                `;
                                            }
                                            link_auto_html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 d-grid my-3">
                                    <button type="submit" class="btn btn-success submitMemberGroupLink"data-dept="${dept}">
                                        <i data-feather="check" class="icon-sm icon-wiggle"></i> Submit Assigned Group List Task
                                    </button>
                                </div>
                                <div class="col-12 mt-3">
                                    <h6 class="card-title">List of Department Member: </h6>
                                    <div class="list-div">`;
                                        if(response.usersGroup.length > 0){
                                            response.usersGroup.forEach((users, index) => {

                                            var profile = users.profile;
                                            var count = users.count;
                                            var department = users.department;

                                            var photoUrl = profile.photo ? `{{ url('upload/photo_bank/${profile.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;

                                            link_auto_html += `
                                            <div class="list-card">
                                                <div class="list-header justify-content-start align-items-center">
                                                    <img src="${photoUrl}" alt="image">
                                                    <h6 class="ms-2">${profile.name}</h6>
                                                </div>
                                                <div class="list-content">
                                                    <h6>Currently Holding Task: ${count}</h6>
                                                    <h6>Department: ${department}</h6>
                                                    <button class="btn btn-hover btn-primary list-assign addMemberToTempoGroupLink" data-user="${profile.id}" data-dept="${dept}">Assign</button>
                                                </div>
                                            </div>`;
                                            });
                                        } else {
                                            link_auto_html += `
                                            <div class="modal-body-bg border border-primary text-wrap text-center">
                                                    <h2 class="text-primary m-3">There's no existing member in this department</h2>
                                            </div>`;
                                        }
                            link_auto_html += `
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                $('#autoLinkNewTask').html(link_auto_html);
                feather.replace();

                $(document).on('change', '#automationType', function(){
                    console.log('Automation Type changed');
                    var selected = $(this).val();
                    var timeSetterSolo = $('#timeSetterSolo'); // Get or create instance
                    var daySetterSolo = $('#daySetterSolo');
                    var singleDaySetterSolo = $('#singleDaySetterSolo');

                    if (selected === 'day') {
                        daySetterSolo.collapse('show');
                        timeSetterSolo.collapse('show');
                        singleDaySetterSolo.collapse('hide');
                    } else if(selected === 'week') {
                        singleDaySetterSolo.collapse('show');
                        daySetterSolo.collapse('hide');
                        timeSetterSolo.collapse('show');
                    }else if (selected === 'time') {
                        timeSetterSolo.collapse('show');
                        daySetterSolo.collapse('show');
                        singleDaySetterSolo.collapse('hide');
                    } else {
                        timeSetterSolo.collapse('hide');
                    }
                });

                $(document).on('change', '#automationTypeGroup', function(){
                    var selected = $(this).val();
                    var timeSetterGroup = $('#timeSetterGroup'); // Get or create instance
                    var daySetterGroup = $('#daySetterGroup');
                    var singleDaySetterGroup = $('#singleDaySetterGroup');

                    if (selected === 'day') {
                        daySetterGroup.collapse('show');
                        timeSetterGroup.collapse('show');
                        singleDaySetterGroup.collapse('hide');
                    } else if(selected === 'week') {
                        singleDaySetterGroup.collapse('show');
                        daySetterGroup.collapse('hide');
                        timeSetterGroup.collapse('show');
                    }else if (selected === 'time') {
                        timeSetterGroup.collapse('show');
                        daySetterGroup.collapse('show');
                        singleDaySetterGroup.collapse('hide');
                    } else {
                        timeSetterGroup.collapse('hide');
                    }
                });

                $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function (e) {
                    let targetTab = $(e.target).attr("href");
                    if (targetTab === "#groupLinkAuto" && assign === 0) {
                        $.ajax({
                            url: '{{ route("observer.tasks.groupchecktempomemberlink") }}',
                            method: 'POST',
                            noLoading: true,
                            data: {
                                dept: dept
                            },
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                            },
                            success: function(response) {
                                if(response.status === 'exist') {
                                    Swal.fire({
                                        title: 'There\'s existing temporary group member you add before, do you want to use it?',
                                        icon: 'question',
                                        showCancelButton: true,
                                        confirmButtonText: 'Yes! I want to use it',
                                        cancelButtonText: 'No, I don\'t want to'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            link_auto_layout(dept, 1, 1)
                                        } else if (result.dismiss === Swal.DismissReason.cancel) {
                                            $.ajax({
                                                url: '{{ route("observer.tasks.groupchecktempomemberlink") }}',
                                                method: 'POST',
                                                noLoading: true,
                                                data: {
                                                    dept: dept,
                                                    cont: 0
                                                },
                                                dataType: 'json',
                                                headers: {
                                                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                                },
                                                success: function(response) {
                                                    if(response.status === 'deleted') {
                                                        link_auto_layout(dept, 1, 1)
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
                                } else if(response.status === 'notexist'){
                                    link_auto_layout(dept, 1, 1)
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

            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    $(document).on('click', '.submitSoloLinkAuto', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');
        var form = $('#formLinkSoloAutomation').serialize() + '&user='+user+'&dept='+dept;

        Swal.fire({
            title: 'Are you sure you want to set this user to automation link?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.setsololinkauto") }}',
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully link and assign task'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                            link_auto_layout(dept, 0, 1)
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
            }
        });
    });

    $(document).on('click', '.removeSoloLinkAuto', function() {
        var user = $(this).data('user');
        var dept = $(this).data('dept');

        Swal.fire({
            title: 'Are you sure you want to remove this user to automation link?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to remove it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.removesololinkauto") }}',
                    method: 'POST',
                    data: {
                        user: user,
                        dept: dept
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove link and assign task'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                            link_auto_layout(dept, 0, 1)
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            link_auto_layout(dept, 0, 1)
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

    $(document).on('click', '.addMemberToTempoGroupLink', function() {
        var dept = $(this).data('dept');
        var user = $(this).data('user');

        $.ajax({
            url: '{{ route("observer.tasks.addtempogrouplinkauto") }}',
            method: 'POST',
            noLoading: true,
            data: {
                dept: dept,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    //$('#NewTaskLinkModal').modal('hide');
                    pageContainer();
                    link_auto_layout(dept, 1, 1)
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

    $(document).on('click', '.removeMemberToTempoGroupLink', function() {
        var dept = $(this).data('dept');
        var user = $(this).data('user');

        $.ajax({
            url: '{{ route("observer.tasks.removetempogrouplinkauto") }}',
            method: 'POST',
            noLoading: true,
            data: {
                dept: dept,
                user: user
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'success') {
                    //$('#NewTaskLinkModal').modal('hide');
                    pageContainer();
                    link_auto_layout(dept, 1, 1)
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('click', '.submitMemberGroupLink', function () {
        var dept = $(this).data('dept');
        var form = $('#formLinkGroupAutomation').serialize() + '&dept='+dept;

        Swal.fire({
            title: 'Are you sure you want to set this group to automation link?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.setgrouplinkauto") }}',
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully link and assign task'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                            link_auto_layout(dept, 1, 1)
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
            }
        });
    });

    $(document).on('click', '.removeLinkGroupAuto', function() {
        var dept = $(this).data('dept');
        var group = $(this).data('group');
        var temp = $(this).data('temp');
        var link = $(this).data('link');

        Swal.fire({
            title: 'Are you sure you want to remove this group to automation link?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to remove it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.removegrouplinkauto") }}',
                    method: 'POST',
                    data: {
                        dept: dept,
                        group: group,
                        temp: temp,
                        link: link
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully remove link and assign task'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                            link_auto_layout(dept, 1, 1)
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            link_auto_layout(dept, 1, 1)
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

    $(document).on('click', '#viewLinkedTask', function() {
        var task = $(this).data('task');
        window.location.href = `/observer/lvtasks/${task}`;
    });

//endregion

//region User Statistic

    $(document).on('click', '#viewUserStatusStatistic', function() {
        $('#viewStatisticTaskModal').modal('show');
        var task = $(this).data('task');

        $.ajax({
            url: '{{ route("observer.tasks.viewstatistic") }}',
            method: 'GET',
            data: {
                task: task
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'notExist'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Task Not Exist',
                        html: `<ul><li>Task is currently not existing to the data, it might be archived</li></ul>`
                    });
                } else if(response.status === 'noSoloTask'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'User Not Exist In Task',
                        html: `<ul><li>The task currently don't have any user in this task</li></ul>`
                    });
                } else if(response.status === 'noGroupMembers'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Members Not Exist In Task',
                        html: `<ul><li>The task currently don't have any member in this task</li></ul>`
                    });
                }
                var status_html = ``;
                var type = response.type;
                if(type === 'Solo'){
                    var user = response.user;
                    var info = response.task;
                    var photoUrl = user.photo ? `{{ url('upload/photo_bank/${user.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                    status_html += `
                    <div class="col-12">
                        <ul class="nav nav-pills mb-3 justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#graphStatus">User Statistic</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#listStatus">Status List</a>
                            </li>
                        </ul>
                        <div class="tab-content border border-0 p-3 shadow-none">
                            <div class="row mb-3">
                                <div class="col-12 d-grid gap-2">
                                    <button type="button" class="btn btn-warning text-white" data-task="${task}" id="emergencyButton">Emergency Button</button>
                                </div>
                            </div>
                            <div class="tab-pane fade active show" id="graphStatus" role="tabpanel" >
                                <div class="row align-items-center mb-3">
                                    <div class="col-md-12 d-flex justify-content-center text-center align-items-center">
                                        <img src="${photoUrl}" alt="User Profile" class="rounded-circle me-3 shadow-sm" width="100" height="100">
                                        <div>
                                            <h5 class="mb-0 fw-bold">${user.name}</h5>
                                            <small class="text-muted">Task: ${info.title}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12" id="barChartDisplay_${user.id}">
                                        <canvas id="barChart_${user.id}"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="listStatus" role="tabpanel" >
                                <h6 class="mb-3">User Status List <b class="text-primary">(Name: ${user.name})</b>:</h6>
                                <div class="row d-flex flex-nowrap gap-2 overflow-auto" id="statusListDisplay">`;
                                if(response.statuses.length > 0){
                                    response.statuses.forEach((status, index) => {
                                        let durationText = formatDuration(status.duration);

                                        if(status.user_status === 'Active'){
                                            status_html += `<div class="text-white col bg-primary pt-2 pb-2 rounded-2">Active <span class="badge bg-white text-primary">Time: ${durationText}</span></div>`;
                                        } else if(status.user_status === 'Idle'){
                                            status_html += `<div class="text-white col bg-warning pt-2 pb-2 rounded-2">Idle <span class="badge bg-white text-warning">Time: ${durationText}</span></div>`;
                                        } else if(status.user_status === 'Away'){
                                            status_html += `<div class="text-white col bg-danger pt-2 pb-2 rounded-2">Away <span class="badge bg-white text-danger">Time: ${durationText}</span></div>`;
                                        } else if(status.user_status === 'Emergency'){
                                            status_html += `<div class="text-white col bg-dark pt-2 pb-2 rounded-2">Emergency <span class="badge bg-white text-dark">Time: ${durationText}</span></div>`;
                                        } else if(status.user_status === 'Sleep'){
                                            status_html += `<div class="text-white col bg-info pt-2 pb-2 rounded-2">Sleep <span class="badge bg-white text-info">Time: ${durationText}</span></div>`;
                                        } else if(status.user_status === 'Overtime'){
                                            status_html += `<div class="text-white col bg-success pt-2 pb-2 rounded-2">Overtime <span class="badge bg-white text-info">Time: ${durationText}</span></div>`;
                                        }
                                    });
                                }
                                status_html += `
                                </div>
                            </div>
                        </div>
                    </div>
                    `;
                } else if(type === 'Group'){
                    status_html += `
                    <div class="col-12">
                        <ul class="nav nav-pills mb-3 justify-content-center">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#graphStatus">User Statistic</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#listStatus">Status List</a>
                            </li>
                        </ul>
                        <div class="tab-content border border-0 p-3 shadow-none">
                            <div class="row mb-3">
                                <div class="col-12 d-grid gap-2">
                                    <button type="button" class="btn btn-warning text-white" data-task="${task}" id="emergencyButton">Emergency Button</button>
                                </div>
                            </div>
                            <div class="tab-pane fade active show" id="graphStatus" role="tabpanel" >
                                <div class="card modal-body-bg border border-primary">
                            `;

                            if(response.groupData.length > 0){
                                response.groupData.forEach((member, index) => {
                                    var user = member.user;
                                    var info = member.task;
                                    var photoUrl = user.photo ? `{{ url('upload/photo_bank/${user.photo}') }}` : `{{ url('upload/nophoto.jfif') }}`;
                                    status_html += `
                                    <div class="card-body rounded shadow-sm mb-3">
                                        <div class="row align-items-center mb-3">
                                            <div class="col-md-12 d-flex justify-content-center text-center align-items-center">
                                                <img src="${photoUrl}" alt="User Profile" class="rounded-circle me-3 shadow-sm" width="100" height="100">
                                                <div>
                                                    <h5 class="mb-0 fw-bold">${user.name}</h5>
                                                    <small class="text-muted">Task: ${info.title}</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12" id="barChartDisplay_${user.id}">
                                                <canvas id="barChart_${user.id}"></canvas>
                                            </div>
                                        </div>
                                    </div>`;
                                });
                            }
                                status_html += `

                                </div>
                            </div>
                            <div class="tab-pane fade" id="listStatus" role="tabpanel" >
                                <div class="card modal-body-bg border border-primary">`;
                                if(response.groupData.length > 0){
                                    response.groupData.forEach((member, index) => {
                                    var user = member.user;
                                    var info = member.task;
                                    var statuses = member.statuses;
                                    status_html += `
                                    <div class="card-body rounded shadow-sm mb-3">
                                    <h6 class="mb-3">User Status List <b class="text-primary">(Name: ${user.name})</b>:</h6>
                                    <div class="row d-flex flex-nowrap gap-2 overflow-auto" id="statusListDisplay_${user.id}">`;
                                        if(statuses.length > 0){
                                            statuses.forEach((status, index) => {
                                                let durationText = formatDuration(status.duration);

                                                if(status.user_status === 'Active'){
                                                    status_html += `<div class="text-white col bg-primary pt-2 pb-2 rounded-2">Active <span class="badge bg-white text-primary">Time: ${durationText}</span></div>`;
                                                } else if(status.user_status === 'Idle'){
                                                    status_html += `<div class="text-white col bg-warning pt-2 pb-2 rounded-2">Idle <span class="badge bg-white text-warning">Time: ${durationText}</span></div>`;
                                                } else if(status.user_status === 'Away'){
                                                    status_html += `<div class="text-white col bg-danger pt-2 pb-2 rounded-2">Away <span class="badge bg-white text-danger">Time: ${durationText}</span></div>`;
                                                } else if(status.user_status === 'Emergency'){
                                                    status_html += `<div class="text-white col bg-dark pt-2 pb-2 rounded-2">Emergency <span class="badge bg-white text-dark">Time: ${durationText}</span></div>`;
                                                } else if(status.user_status === 'Sleep'){
                                                    status_html += `<div class="text-white col bg-info pt-2 pb-2 rounded-2">Sleep <span class="badge bg-white text-info">Time: ${durationText}</span></div>`;
                                                } else if(status.user_status === 'Overtime'){
                                                    status_html += `<div class="text-white col bg-success pt-2 pb-2 rounded-2">Overtime <span class="badge bg-white text-info">Time: ${durationText}</span></div>`;
                                                }
                                            });
                                        }
                                    status_html += `</div>
                                    </div>`;
                                    });
                                }
                                status_html += `

                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                }

                $('#viewStatisticDisplay').html(status_html);

                if(type === 'Solo'){
                    var isParticipating = response.isParticipating;
                    var user = response.user;
                    if(isParticipating){
                        var totalDuration = response.totalDuration || {};

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
                        var secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary').trim(); // Added for Total color


                        var ctx = $(`#barChart_${user.id}`);
                        var barChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: ["Active", "Idle", "Away", "Overtime", "Total"],
                                datasets: [{
                                    label: `Active (${formattedActive})`,
                                    data: [activeTimeSec, 0, 0], // Only Active value
                                    backgroundColor: primaryColor,
                                    borderColor: primaryColor,
                                    borderWidth: 1
                                },
                                {
                                    label: `Idle (${formattedIdle})`,
                                    data: [0, idleTimeSec, 0], // Only Idle value
                                    backgroundColor: warningColor,
                                    borderColor: warningColor,
                                    borderWidth: 1
                                },
                                {
                                    label: `Away (${formattedAway})`,
                                    data: [0, 0, awayTimeSec], // Only Away value
                                    backgroundColor: dangerColor,
                                    borderColor: dangerColor,
                                    borderWidth: 1
                                },
                                {
                                    label: `Overtime (${formattedOvertime})`,
                                    data: [0, 0, 0, overtimeTimeSec], // Only Away value
                                    backgroundColor: successColor,
                                    borderColor: successColor,
                                    borderWidth: 1
                                },
                                {
                                    label: `Total (${formattedTotal})`, // New dataset for Total
                                    data: [0, 0, 0, 0, totalSumSec], // Only Total value
                                    backgroundColor: secondaryColor, // You can choose another color for Total
                                    borderColor: secondaryColor,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        display: true,
                                        labels: {
                                            usePointStyle: true,  // Use small dots instead of boxes
                                            boxWidth: 10,  // Reduce size of legend box
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
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        title: {
                                            display: true,
                                            text: "Time in Seconds"
                                        }
                                    }
                                }
                            }
                        });
                    } else {
                        var design = $(`#barChartDisplay_${user.id}`);
                        $(design).html(`
                            <div class="col-md-12 justify-content-center text-center align-items-center">
                                <h4 class="mb-0 fw-bold">This user is currently not starting or participating in this task</h4>
                            </div>
                        `);

                        $('#statusListDisplay').html(`
                            <div class="col-md-12 justify-content-center text-center align-items-center">
                                <h4 class="mb-0 fw-bold">This user is currently not starting or participating in this task</h4>
                            </div>
                        `);
                    }
                } else if(type === 'Group'){

                    if(response.groupData.length > 0){
                        response.groupData.forEach((member, index) => {
                            var isParticipating = member.isParticipating;
                            var user = member.user;
                            if(isParticipating){
                                var totalDuration = member.totalDuration || {};

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
                                var secondaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-secondary').trim(); // Added for Total color


                                var ctx = $(`#barChart_${user.id}`);
                                var barChart = new Chart(ctx, {
                                    type: 'bar',
                                    data: {
                                        labels: ["Active", "Idle", "Away", "Overtime", "Total"],
                                        datasets: [{
                                            label: `Active (${formattedActive})`,
                                            data: [activeTimeSec, 0, 0], // Only Active value
                                            backgroundColor: primaryColor,
                                            borderColor: primaryColor,
                                            borderWidth: 1
                                        },
                                        {
                                            label: `Idle (${formattedIdle})`,
                                            data: [0, idleTimeSec, 0], // Only Idle value
                                            backgroundColor: warningColor,
                                            borderColor: warningColor,
                                            borderWidth: 1
                                        },
                                        {
                                            label: `Away (${formattedAway})`,
                                            data: [0, 0, awayTimeSec], // Only Away value
                                            backgroundColor: dangerColor,
                                            borderColor: dangerColor,
                                            borderWidth: 1
                                        },
                                        {
                                            label: `Overtime (${formattedOvertime})`,
                                            data: [0, 0, 0, overtimeTimeSec], // Only Away value
                                            backgroundColor: successColor,
                                            borderColor: successColor,
                                            borderWidth: 1
                                        },
                                        {
                                            label: `Total (${formattedTotal})`, // New dataset for Total
                                            data: [0, 0, 0, 0, totalSumSec], // Only Total value
                                            backgroundColor: secondaryColor, // You can choose another color for Total
                                            borderColor: secondaryColor,
                                            borderWidth: 1
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        plugins: {
                                            legend: {
                                                display: true,
                                                labels: {
                                                    usePointStyle: true,  // Use small dots instead of boxes
                                                    boxWidth: 10,  // Reduce size of legend box
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
                                            }
                                        },
                                        scales: {
                                            y: {
                                                beginAtZero: true,
                                                title: {
                                                    display: true,
                                                    text: "Time in Seconds"
                                                }
                                            }
                                        }
                                    }
                                });
                            } else {
                                var design = $(`#barChartDisplay_${user.id}`);
                                $(design).html(`
                                    <div class="col-md-12 justify-content-center text-center align-items-center">
                                        <h4 class="mb-0 fw-bold">This user is currently not starting or participating in this task</h4>
                                    </div>
                                `);

                                $(`#statusListDisplay_${user.id}`).html(`
                                    <div class="col-md-12 justify-content-center text-center align-items-center">
                                        <h4 class="mb-0 fw-bold">This user is currently not starting or participating in this task</h4>
                                    </div>
                                `);
                            }
                        })
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

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

    $(document).on('click', '#emergencyButton', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: 'Are you sure you want to set this task as emergency status?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.setemergencyuserstatus") }}',
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
                                title: 'Successfully set to emergency'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
                            pageContainer();
                            $('#viewStatisticTaskModal').modal('hide');
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
            }
        });
    })

//endregion

//region Working Time

    $(document).on('change', '#overtimeAutomation', function() {
        if ($(this).prop('checked')) {
        // The toggle is ON
            overtimeToggle('On');
        } else {
            // The toggle is OFF
            overtimeToggle('Off');
        }
    });

    function overtimeToggle(status){

        Swal.fire({
            title: `${status === `On` ? `Are you sure you want to turn on overtime automatic accept?`: `Are you sure you want to turn off overtime automatic accept?`}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.setovertimeautomation") }}',
                    method: 'POST',
					noLoading: true,
                    data: {
                        status: status
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully sent to overtime automatic accept'
                            });
                            //$('#NewTaskLinkModal').modal('hide');
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
    }

    $(document).on('click', '#workingTimeDept', function() {
        $('#worktimeSettingsModal').modal('show');
        var dept = $(this).data('dept');

        $.ajax({
            url: '{{ route("observer.tasks.getworktimesettings") }}',
            method: 'GET',
            data: {
                dept: dept
            },
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
            },
            success: function(response) {
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    pageContainer();
                }
                var department = response.department;
                var work_time_html = `
                <div class="col-12">
                    <form id="formTimeWorkSet">
                        @csrf
                        <input type="hidden" name="department_id" value="${dept ? dept : ''}">
                        <div class="card card-body border-secondary rounded-2 mb-3">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="startDay">Select Start Day:</label>
                                    <select id="startDay" name="start_day" class="form-select" aria-label="Default select example">
                                        <option value="" disabled ${!department.start_day ? 'selected' : ''}>Open this select menu</option>
                                        <option value="sunday" ${department.start_day === 'sunday' ? 'selected' : ''}>Sunday</option>
                                        <option value="monday" ${department.start_day === 'monday' ? 'selected' : ''}>Monday</option>
                                        <option value="tuesday" ${department.start_day === 'tuesday' ? 'selected' : ''}>Tuesday</option>
                                        <option value="wednesday" ${department.start_day === 'wednesday' ? 'selected' : ''}>Wednesday</option>
                                        <option value="thursday" ${department.start_day === 'thursday' ? 'selected' : ''}>Thursday</option>
                                        <option value="friday" ${department.start_day === 'friday' ? 'selected' : ''}>Friday</option>
                                        <option value="saturday" ${department.start_day === 'saturday' ? 'selected' : ''}>Saturday</option>
                                    </select>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="endDay">Select End Day:</label>
                                    <select id="endDay" name="end_day" class="form-select" aria-label="Default select example">
                                        <option value="" disabled ${!department.end_day ? 'selected' : ''}>Open this select menu</option>
                                        <option value="sunday" ${department.end_day === 'sunday' ? 'selected' : ''}>Sunday</option>
                                        <option value="monday" ${department.end_day === 'monday' ? 'selected' : ''}>Monday</option>
                                        <option value="tuesday" ${department.end_day === 'tuesday' ? 'selected' : ''}>Tuesday</option>
                                        <option value="wednesday" ${department.end_day === 'wednesday' ? 'selected' : ''}>Wednesday</option>
                                        <option value="thursday" ${department.end_day === 'thursday' ? 'selected' : ''}>Thursday</option>
                                        <option value="friday" ${department.end_day === 'friday' ? 'selected' : ''}>Friday</option>
                                        <option value="saturday" ${department.end_day === 'saturday' ? 'selected' : ''}>Saturday</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card card-body border-secondary rounded-2 mb-3">
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="startTime">Select Start Time:</label>
                                    <input class="form-control" type="time" id="startTime" name="start_time" value="${department.start_time !== null ? department.start_time : ``}">
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="endTime">Select End Time:</label>
                                    <input class="form-control" type="time" id="endTime" name="end_time" value="${department.end_time !== null ? department.end_time : ``}">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 d-grid gap-2">
                                <button type="button" class="btn btn-primary btn-hover" id="workingTimeSubmit">Working Time Settings</button>
                            </div>
                        </div>
                    </form>
                </div>
                `;

                $('#worktimeDisplay').html(work_time_html);
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('click', '#workingTimeSubmit', function() {
        var form = $('#formTimeWorkSet').serialize();
        Swal.fire({
            title: `Are you sure you want to set this time?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to set it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.setworktimeautomation") }}',
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully set to work time settings'
                            });
                            $('#worktimeSettingsModal').modal('hide');
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
                window.location.href = `/observer/etasks/${task}`;
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
                    url: '{{ route("observer.tasks.requestovertimetask") }}',
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

    $(document).on('click', '.acceptOvertimeBtn', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to accept request overtime?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to accept it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.acceptovertimerequest") }}',
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
                                title: 'Successfully accepted'
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
    });

    $(document).on('click', '.declineOvertimeBtn', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to decline request overtime?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to decline it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.declineovertimerequest") }}',
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
                                title: 'Successfully declined'
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
    });

//endregion

//region Chat Task Member

    $(document).on('click', '#sendMessageOverdue', function() {
        var name = $(this).data('name');
        var task = $(this).data('task');
        $('#sendMessageTaskModal').modal('show');
        $('#sendMessageTaskLabel').html(`Message ${name}`);
        $('#task_id').val(task);
    });

    $('#attachFileButton').on('click', function () {
        $('#fileInput').click(); // Open file dialog
    });

    $('#messageContactForm').on('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('observer.chat.sendtaskcontactmessage') }}`,
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
                    $('#sendMessageTaskModal').modal('hide');
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
//endregion

//region Archive Task

    $(document).on('click', '.archiveDistributeTask', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to archive this distributed task?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to archive it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.archivedistributedtask") }}',
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
                                title: 'Successfully archived'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
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
    });

    $(document).on('click', '.archiveCompletedTask', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to archive this completed task?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to archive it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.archivecompletedtask") }}',
                    method: 'POST',
                    data: {
                        task: task
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'ask'){
                            Swal.fire({
                                title: 'Want to archive the linked task too?',
                                text: 'Select an option below:',
                                icon: 'info',
                                showCancelButton: true,
                                showDenyButton: true, // Add a separate cancel button
                                confirmButtonText: 'Yes, archive the linked task too',
                                cancelButtonText: 'Cancel',  // Explicit Cancel button
                                denyButtonText: 'No, don\'t archive the linked task', // New deny button for ongoing task
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                customClass: {
                                    confirmButton: 'swal2-confirm btn',  // Bootstrap class applied
                                    denyButton: 'swal2-deny btn bg-danger border border-danger text-white',
                                    cancelButton: 'swal2-cancel btn bg-warning border border-0 text-black'
                                }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: '{{ route("observer.tasks.archivecompletedtask") }}',
                                        method: 'POST',
                                        data: {
                                            task: task,
                                            linked: 'Yes'
                                        },
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully archived'
                                                });
                                                $('#archivedTable').load(location.href + " #archivedTable > *");
                                                pageContainer();
                                            } else if(response.status === 'error'){
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                                                });
                                                $('#archivedTable').load(location.href + " #archivedTable > *");
                                                pageContainer();
                                            }
                                        },
                                        error: function(xhr, status, error) {
                                            console.error('Error occurred:', xhr.responseText);
                                            console.error('Error occurred:', status);
                                            console.error('Error occurred:', error);
                                        }
                                    });
                                } else if (result.isDenied) {
                                    $.ajax({
                                        url: '{{ route("observer.tasks.archivecompletedtask") }}',
                                        method: 'POST',
                                        data: {
                                            task: task,
                                            linked: 'No'
                                        },
                                        dataType: 'json',
                                        headers: {
                                            'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                                        },
                                        success: function(response) {
                                            if(response.status === 'success') {
                                                Toast.fire({
                                                    icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Successfully archived'
                                                });
                                                $('#archivedTable').load(location.href + " #archivedTable > *");
                                                pageContainer();
                                            } else if(response.status === 'error'){
                                                Toast.fire({
                                                    icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                                    title: 'Error',
                                                    html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                                                });
                                                $('#archivedTable').load(location.href + " #archivedTable > *");
                                                pageContainer();
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
                        } else if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully archived'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
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
    });

    $(document).on('click', '#retrieveTask', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to retrieve this task?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to retrieve it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.retrievetask") }}',
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
                                title: 'Successfully retrieve'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
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

    $(document).on('click', '.deleteTask', function() {
        var task = $(this).data('task');

        Swal.fire({
            title: `Are you sure you want to fully delete this task?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to fully delete it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.deletetask") }}',
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
                                title: 'Successfully fully deleted'
                            });
                            pageContainer();
                            $('#archivedTable').load(location.href + " #archivedTable > *");
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTable').load(location.href + " #archivedTable > *");
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

    $(document).on('click', '#retrieveTemp', function() {
        var temp = $(this).data('temp');

        Swal.fire({
            title: `Are you sure you want to retrieve this template?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to retrieve it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.retrievetemp") }}',
                    method: 'POST',
                    data: {
                        temp: temp
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully retrieve'
                            });
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
                            pageContainer();
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
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

    $(document).on('click', '.deleteTemp', function() {
        var temp = $(this).data('temp');

        Swal.fire({
            title: `Are you sure you want to fully delete this template?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to fully delete it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("observer.tasks.deletetemp") }}',
                    method: 'POST',
                    data: {
                        temp: temp
                    },
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                    },
                    success: function(response) {
                        if(response.status === 'success') {
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully fully deleted'
                            });
                            pageContainer();
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            $('#archivedTempTable').load(location.href + " #archivedTempTable > *");
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

//endregion
    }
});
</script>
@endsection