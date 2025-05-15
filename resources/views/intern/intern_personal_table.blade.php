@extends('intern.intern_dashboard')
@section('intern')
<div class="page-content" id="page">
    <div class="row inbox-wrapper">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 border-end-lg">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <button class="navbar-toggle btn btn-icon border d-block d-lg-none" data-bs-target=".email-aside-nav" data-bs-toggle="collapse" type="button">
                                <span class="icon"><i data-feather="chevron-down"></i></span>
                                </button>
                                <div class="order-first">
                                <h4>Welcome to your personal table "<b class="text-primary">{{Auth::user()->name}}</b>"</h4>
                                </div>
                            </div>
                            <div class="email-aside-nav collapse">
                                <p class="text-muted tx-12 fw-bolder text-uppercase mb-2 mt-4">Section</p>
                                <ul class="nav row px-3"  style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                                    <li class="nav-item nav-buttons-personal active" data-tab="task-tab">
                                        <a class="nav-link d-flex align-items-center" href="javascript:;">
                                            <i data-feather="file" class="icon-lg icon-wiggle me-2"></i>
                                                Task List
                                        </a>
                                    </li>
                                    <li class="nav-item nav-buttons-personal" data-tab="notes-tab">
                                        <a class="nav-link d-flex align-items-center" href="javascript:;">
                                            <i data-feather="paperclip" class="icon-lg icon-wiggle me-2"></i>
                                                Note's
                                        </a>
                                    </li>
                                    <li class="nav-item nav-buttons-personal" data-tab="important-tab">
                                        <a class="nav-link d-flex align-items-center" href="javascript:;">
                                            <i data-feather="briefcase" class="icon-lg icon-wiggle me-2"></i>
                                                Important
                                        </a>
                                    </li>
                                    <li class="nav-item nav-buttons-personal" data-tab="favorites-tab">
                                        <a class="nav-link d-flex align-items-center" href="javascript:;">
                                            <i data-feather="star" class="icon-lg icon-wiggle me-2"></i>
                                                Favorites
                                        </a>
                                    </li>
                                </ul>
                                <p class="text-muted tx-12 fw-bolder text-uppercase mb-2 mt-4">Shortcuts</p>
                                <ul class="nav row px-3" id="shortcut-list" style="max-height: 300px; overflow-y: auto; overflow-x: hidden;">
                                    @if($tag->count() > 0)
                                        @foreach($tag as $row)
                                            @if($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime')
                                            <li class="nav-item d-grid col-12 p-1" data-task-id="{{ $row->id }}" data-name="{{$row->title}}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <a class="nav-link d-flex align-items-center flex-grow-1" href="{{ route('intern.etasks', ['task' => $row->id]) }}">
                                                        <div class="d-flex align-items-center">
                                                            <i data-feather="file" class="{{ $row->status === 'Overdue' ? 'text-danger' : 'text-primary' }} icon-lg me-3"></i>
                                                            <span class="fw-bold">{{$row->title}}</span>
                                                        </div>
                                                    </a>
                                                    <div class="d-flex align-items-center gap-2 ms-3">
                                                        <a href="{{ route('intern.lvtasks', ['task' => $row->id]) }}" class="btn btn-sm btn-light">
                                                            <i data-feather="eye" class="icon-sm"></i>
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-sm btn-light shortcutTask" data-id="{{$row->id}}" data-is-tagged="yes">
                                                            <i data-feather="x" class="icon-sm"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}"
                                                    role="progressbar"
                                                    aria-valuenow="{{$row->progress_percentage}}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$row->progress_percentage}}%">
                                                        {{$row->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </li>
                                            @elseif($row->user_status === 'Emergency')
                                            <li class="nav-item d-grid col-12 p-1" data-task-id="{{ $row->id }}" data-name="{{$row->title}}">
                                                <div class="d-flex align-items-center justify-content-between bg-warning-light p-2">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="alert-triangle" class="text-warning icon-lg me-3"></i>
                                                        <span class="fw-bold">{{$row->title}}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 ms-3">
                                                        <button class="btn btn-sm btn-light" id="cancelEmergency" data-task="{{$row->id}}">
                                                            Cancel Emergency
                                                        </button>
                                                        <a href="javascript:;" class="btn btn-sm btn-light shortcutTask" data-id="{{$row->id}}" data-is-tagged="yes">
                                                            <i data-feather="x" class="icon-sm"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}"
                                                    role="progressbar"
                                                    aria-valuenow="{{$row->progress_percentage}}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$row->progress_percentage}}%">
                                                        {{$row->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </li>
                                            @elseif($row->user_status === 'Sleep')
                                            <li class="nav-item d-grid col-12 p-1" data-task-id="{{ $row->id }}" data-name="{{$row->title}}">
                                                <div class="d-flex align-items-center justify-content-between bg-info-light p-2">
                                                    <div class="d-flex align-items-center">
                                                        <i data-feather="moon" class="text-info icon-lg me-3"></i>
                                                        <span class="fw-bold">{{$row->title}}</span>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 ms-3">
                                                        <button class="btn btn-sm btn-light" id="requestOvertime" data-task="{{$row->id}}">
                                                            Request Overtime
                                                        </button>
                                                        <a href="javascript:;" class="btn btn-sm btn-light shortcutTask" data-id="{{$row->id}}" data-is-tagged="yes">
                                                            <i data-feather="x" class="icon-sm"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}"
                                                    role="progressbar"
                                                    aria-valuenow="{{$row->progress_percentage}}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$row->progress_percentage}}%">
                                                        {{$row->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </li>
                                            @endif
                                        @endforeach
                                    @else
                                    <p class="text-center text-muted">No shortcuts available.</p>
                                    @endif
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-9 tab-content-personal show" id="task-tab">
                            <div class="p-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-end mb-2 mb-md-2">
                                            <i data-feather="file" class="text-muted me-2"></i>
                                            <h4 class="me-1">Task List</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-group convo-info-text" style="overflow: hidden;">
                                            <input class="form-control" id="searchTaskPersonal" type="text" placeholder="Search task...">
                                            <span class="input-group-text"><i data-feather="search"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3 border-bottom d-flex align-items-center justify-content-between flex-wrap mb-2">
                                <div class="d-none d-md-flex align-items-center flex-wrap">
                                    <div class="form-check me-3">
                                        <input type="checkbox" class="form-check-input" id="inboxCheckAll">
                                    </div>
                                    <div class="btn-group me-2">
                                        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" type="button"> With selected <span class="caret"></span></button>
                                        <div class="dropdown-menu" role="menu">
                                            <a class="dropdown-item markAsPersonal" data-type="favorites" href="javascript:;">Mark as favorites</a>
                                            <a class="dropdown-item markAsPersonal" data-type="important" href="javascript:;">Mark as important</a>
                                            <a class="dropdown-item markAsPersonal" data-type="tag" href="javascript:;">Create Shortcut</a>
                                        </div>
                                    </div>
                                    <div class="btn-group me-2 d-none d-xl-block">
                                        <button class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" type="button">Order by <span class="caret"></span></button>
                                        <div class="dropdown-menu" role="menu">
                                            <a class="dropdown-item sort-option" href="javascript:;" data-sort="due">Date</a>
                                            <a class="dropdown-item sort-option" href="javascript:;" data-sort="title">Name</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item sort-option" href="javascript:;" data-sort="progress_percentage">Percentage</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="email-list" style="padding-inline: 10px; max-height: 100vh; overflow-y: auto; overflow-x: hidden;">
                                <form id="taskPersonalForm">
                                @if($tasks->count() > 0)
                                    @foreach($tasks as $row)
                                    <div class="personal-content d-flex align-items-center mb-3 {{$row->is_important ? 'border border-primary' : ''}} task-personal-tab" data-name="{{$row->title}}">
                                        <div class="personal-actions d-flex align-items-center">
                                            <div class="form-check me-2">
                                                <input type="checkbox" name="task_ids[]" class="form-check-input" value="{{$row->id}}">
                                            </div>
                                            <a class="personal-favorite {{$row->is_favorites ? 'text-warning' : 'text-muted'}} favoritesTask" data-id="{{$row->id}}" data-is-favorites="{{$row->is_favorites ? 'yes' : 'no'}}" href="javascript:;">
                                                <i data-feather="star" class="icon-lg icon-wiggle"></i>
                                            </a>
                                        </div>
                                        <div class="personal-details flex-grow-1">
                                            <div class="personal-title fw-bold">{{$row->title}}</div>
                                            <div class="personal-meta text-muted">
                                                Task Type: <b class="text-primary">{{$row->type}}</b> -
                                                Due Date: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->due.'</b>' : '<b class="text-primary">'.$row->due.'</b>' !!} -
                                                Task Status: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->status.'</b>' : '<b class="text-primary">'.$row->status.'</b>' !!}
                                            </div>
                                            <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}" role="progressbar"
                                                aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                    style="width: {{$row->progress_percentage}}%">
                                                    {{$row->progress_percentage}}%
                                                </div>
                                            </div>
                                        </div>
                                        <div class="personal-options ms-auto">
                                            <div class="dropdown">
                                                <a type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i data-feather="more-horizontal" class="icon-sm icon-wiggle"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    @if($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime')
                                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.etasks', ['task' => $row->id]) }}">
                                                        <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                    </a>
                                                    @elseif($row->user_status === 'Emergency')
                                                    <a class="dropdown-item d-flex align-items-center"  id="cancelEmergency" data-task="{{$row->id}}">
                                                        <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                    </a>
                                                    @elseif($row->user_status === 'Sleep')
                                                    <a class="dropdown-item d-flex align-items-center"  id="requestOvertime" data-task="{{$row->id}}">
                                                        <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                    </a>
                                                    @endif
                                                    <a class="dropdown-item d-flex align-items-center" data-bs-toggle="collapse" href="#taskNotesCollapse_{{$row->id}}" role="button" aria-expanded="false" aria-controls="taskNotesCollapse_{{$row->id}}">
                                                        <i data-feather="edit" class="icon-sm me-2"></i> Create Note's
                                                    </a>
                                                    <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.lvtasks', ['task' => $row->id]) }}">
                                                        <i data-feather="eye" class="icon-sm me-2"></i> View Task
                                                    </a>
                                                    <a class="dropdown-item d-flex align-items-center importantTask" data-id="{{$row->id}}" data-is-important="{{$row->is_important ? 'yes' : 'no'}}"  href="javascript:;">
                                                        <i data-feather="{{$row->is_important ? 'trash' : 'briefcase' }}" class="icon-sm me-2"></i> {{$row->is_important ? 'Remove as important' : 'Set as important' }}
                                                    </a>
                                                    <a class="dropdown-item d-flex align-items-center shortcutTask" data-id="{{$row->id}}" data-is-tagged="{{$row->is_tagged ? 'yes' : 'no'}}"  href="javascript:;">
                                                        <i data-feather="{{$row->is_tagged ? 'trash' : 'tag' }}" class="icon-sm me-2"></i> {{$row->is_tagged ? 'Remove shortcut' : 'Create shortcut' }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="collapse row px-3" id="taskNotesCollapse_{{$row->id}}">
                                        <input class="form-control convo-info-text mb-2" id="titleTaskNotes__{{ $row->id }}" type="text" placeholder="Enter title">
                                        <textarea class="form-control convo-info-text mb-2" id="contentTaskNotes__{{ $row->id }}" rows="3" type="text" placeholder="Enter notes"></textarea>
                                        <div class="col-12">
                                            <button class="btn btn-light convo-info-text float-end mb-2 ms-2 taskNotesSubmit" type="button" data-task="{{$row->id}}"><i data-feather="check"></i> Save Notes</button>
                                            <button class="btn btn-light convo-info-text float-end mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#taskNotesCollapse_{{$row->id}}" aria-expanded="false" aria-controls="taskNotesCollapse_{{$row->id}}"><i data-feather="x"></i> Close</button>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <p class="text-center text-muted">No tasks available.</p>
                                @endif
                                </form>
                            </div>
                        </div>
                        <div class="col-lg-9 tab-content-personal" id="notes-tab">
                            <div class="p-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-end mb-2 mb-md-2">
                                            <i data-feather="file-text" class="text-muted me-2"></i>
                                            <h4 class="me-1">Notes List</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-group convo-info-text" style="overflow: hidden;">
                                            <input class="form-control" id="searchPrivateNotes" type="text" placeholder="Search self notes...">
                                            <span class="input-group-text"><i data-feather="search"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class=" mt-2" style="padding-inline: 10px; max-height: 100vh; overflow-y: auto; overflow-x: hidden;">
                                <div class="d-grid mb-2">
                                    <button class="btn convo-info-btn btn-sm createPrivateNotes" type="button"><i data-feather="plus" class="icon-sm icon-wiggle"></i> Create Self Note's</button>
                                </div>
                                <div class="mb-3">
                                    <div class="grid-margin stretch-card">
                                        <div class="card convo-info-text">
                                        <div class="card-body">
                                            <h6 class="card-title mb-3">Self Note's List</h6>
                                            <div id="grid-private-notes" class="mb-2">
                                                @if($personalPrivateNotes->count() > 0)
                                                    @foreach($personalPrivateNotes as $note)
                                                    <div class="w-100 convo-info-btn rounded text-white d-block align-items-center justify-content-center text-center p-2 notes-private-tab" data-name="{{$note->title}}" data-notes-id="{{$note->id}}">
                                                        <div class="d-block align-items-center justify-content-center text-center p-2">
                                                            <p class="text-dark" style="font-size: 12px;">Title: "<b class="text-primary">{{$note->title}}</b>"</p>
                                                            {!! $note->description ? ' <span class="text-muted text-truncate" title="'.$note->description.'" style="font-size: 10px;">'.$note->description.'</span>' : '' !!}
                                                        </div>
                                                        <div class="d-flex align-items-center justify-content-center text-center p-2">
                                                            <p class="text-dark"><i data-feather="maximize" class="icon-lg icon-wiggle showPrivateNotes" data-note="{{$note->id}}" type="button"></i></p>
                                                            <p class="text-dark ms-2"><i data-feather="trash" class="icon-lg icon-wiggle private-remove-notes" type="button" data-notes="{{$note->id}}"></i></p>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                @else
                                                <p class="text-center text-muted">No private note's available.</p>
                                                @endif
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                <hr class="mb-3">
                                @if($personalNotes->count() > 0)
                                    <div class="d-grid mb-2">
                                        <button class="btn convo-info-btn btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#notesTaskCollapse" aria-expanded="false" aria-controls="notesTaskCollapse"><i data-feather="file" class="icon-sm icon-wiggle"></i> Toggle Task Notes</button>
                                    </div>
                                    <div class="collapse" id="notesTaskCollapse">
                                        <div class="d-grid mb-2">
                                            <div class="input-group convo-info-text" style="overflow: hidden;">
                                                <input class="form-control" type="text" id="searchTaskNotes" placeholder="Search task...">
                                                <span class="input-group-text"><i data-feather="search"></i></span>
                                            </div>
                                        </div>
                                        @foreach($personalNotes as $task_id => $notes)
                                        <div class="notes-task-tab" data-name="{{$relatedTasks[$task_id]->title}}">
                                        <div class="personal-content d-flex align-items-center mb-2">
                                            <div class="personal-details flex-grow-1">
                                                <div class="personal-title fw-bold">{{$relatedTasks[$task_id]->title}}</div>
                                                <div class="personal-meta text-muted">
                                                    Task Type: <b class="text-primary">{{$relatedTasks[$task_id]->type}}</b> -
                                                    Due Date: {!! $relatedTasks[$task_id]->status === 'Overdue' ? '<b class="text-danger">'.$relatedTasks[$task_id]->due.'</b>' : '<b class="text-primary">'.$relatedTasks[$task_id]->due.'</b>' !!} -
                                                    Task Status: {!! $relatedTasks[$task_id]->status === 'Overdue' ? '<b class="text-danger">'.$relatedTasks[$task_id]->status.'</b>' : '<b class="text-primary">'.$relatedTasks[$task_id]->status.'</b>' !!}
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $relatedTasks[$task_id]->status === 'Overdue' ? 'border-danger' : 'border-primary' }}" role="progressbar"
                                                    aria-valuenow="{{$relatedTasks[$task_id]->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $relatedTasks[$task_id]->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$relatedTasks[$task_id]->progress_percentage}}%">
                                                        {{$relatedTasks[$task_id]->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="personal-options ms-auto">
                                                <div class="dropdown">
                                                    <a type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-horizontal" class="icon-sm icon-wiggle"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        @if($relatedTasks[$task_id]->user_status !== 'Emergency' && $relatedTasks[$task_id]->user_status !== 'Sleep' && $relatedTasks[$task_id]->user_status !== 'Request Overtime')
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.etasks', ['task' => $relatedTasks[$task_id]->id]) }}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($relatedTasks[$task_id]->user_status === 'Emergency')
                                                        <a class="dropdown-item d-flex align-items-center" id="cancelEmergency" data-task="{{$relatedTasks[$task_id]->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($relatedTasks[$task_id]->user_status === 'Sleep')
                                                        <a class="dropdown-item d-flex align-items-center" id="requestOvertime" data-task="{{$relatedTasks[$task_id]->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @endif
                                                        <a class="dropdown-item d-flex align-items-center" data-bs-toggle="collapse" href="#fromNotesCollapse_{{$relatedTasks[$task_id]->id}}" role="button" aria-expanded="false" aria-controls="fromNotesCollapse_{{$relatedTasks[$task_id]->id}}">
                                                            <i data-feather="edit" class="icon-sm me-2"></i> Create Note's
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.lvtasks', ['task' => $relatedTasks[$task_id]->id]) }}">
                                                            <i data-feather="eye" class="icon-sm me-2"></i> View Task
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="collapse row px-3" id="fromNotesCollapse_{{$relatedTasks[$task_id]->id}}">
                                            <input class="form-control convo-info-text mb-2" id="titleFromNotes__{{$relatedTasks[$task_id]->id}}" type="text" placeholder="Enter title">
                                            <textarea class="form-control convo-info-text mb-2" id="contentFromNotes__{{$relatedTasks[$task_id]->id}}" rows="3" type="text" placeholder="Enter notes"></textarea>
                                            <div class="col-12">
                                                <button class="btn btn-light convo-info-text float-end mb-2 ms-2 fromNotesSubmit" type="button" data-task="{{$relatedTasks[$task_id]->id}}"><i data-feather="check"></i> Save Notes</button>
                                                <button class="btn btn-light convo-info-text float-end mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#fromNotesCollapse_{{$relatedTasks[$task_id]->id}}" aria-expanded="false" aria-controls="fromNotesCollapse_{{$relatedTasks[$task_id]->id}}"><i data-feather="x"></i> Close</button>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12 d-grid">
                                                <button type="button" class="btn convo-info-btn mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNotesTask_{{$relatedTasks[$task_id]->id}}" aria-expanded="false" aria-controls="collapseNotesTask_{{$relatedTasks[$task_id]->id}}"><i data-feather="file-text" class="text-muted me-2"></i> Notes Toggle</button>
                                            </div>
                                            <div class="col-12 grid-margin stretch-card collapse" id="collapseNotesTask_{{$relatedTasks[$task_id]->id}}">
                                                <div class="card convo-info-text">
                                                    <div class="card-body">
                                                        <h6 class="card-title mb-3">{{$relatedTasks[$task_id]->title}} Notes:</h6>
                                                        <div id="grid-notes-sort-{{ $task_id }}" class="grid-notes-sort">
                                                            @foreach($notes as $note)
                                                            <div class="w-100 convo-info-btn rounded text-white d-block align-items-center justify-content-center text-center p-2" data-notes-id="{{$note->id}}" data-task-id="{{ $relatedTasks[$task_id]->id }}">
                                                                <div class="d-block align-items-center justify-content-center text-center p-2">
                                                                    <p class="text-dark" style="font-size: 12px;">Title: "<b class="text-primary">{{$note->title}}</b>"</p>
                                                                    {!! $note->description ? ' <span class="text-muted text-truncate" title="'.$note->description.'" style="font-size: 10px;">'.$note->description.'</span>' : '' !!}
                                                                </div>
                                                                <div class="d-flex align-items-center justify-content-center text-center p-2">
                                                                    <p class="text-dark"><i data-feather="maximize" class="icon-lg icon-wiggle showNotes" data-note="{{$note->id}}" type="button"></i></p>
                                                                    <p class="text-dark ms-2"><i data-feather="trash" class="icon-lg icon-wiggle remove-notes" type="button" data-notes="{{$note->id}}" data-task="{{ $relatedTasks[$task_id]->id }}"></i></p>
                                                                </div>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="mb-3">
                                        </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-center text-muted">No note's available.</p>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-9 tab-content-personal" id="important-tab">
                            <div class="p-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-end mb-2 mb-md-2">
                                            <i data-feather="briefcase" class="text-muted me-2"></i>
                                            <h4 class="me-1">Important Tab</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-group convo-info-text" style="overflow: hidden;">
                                            <input class="form-control" type="text" id="searchImportantPersonal" placeholder="Search task...">
                                            <span class="input-group-text"><i data-feather="search"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2" style="padding-inline: 10px; max-height: 100vh; overflow-y: auto; overflow-x: hidden;">
                                <div id="important-list" style="padding-inline: 10px; max-height: 440px; overflow-y: auto; overflow-x: hidden;">
                                    @if($important->count() > 0)
                                        @foreach($important as $row)
                                        <div class="personal-content personal-content-important d-flex align-items-center mb-3 task-important-tab" data-name="{{$row->title}}"  data-task-id="{{ $row->id }}">
                                            <div class="personal-details flex-grow-1">
                                                <div class="personal-title fw-bold">{{$row->title}}</div>
                                                <div class="personal-meta text-muted">
                                                    Task Type: <b class="text-primary">{{$row->type}}</b> -
                                                    Due Date: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->due.'</b>' : '<b class="text-primary">'.$row->due.'</b>' !!} -
                                                    Task Status: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->status.'</b>' : '<b class="text-primary">'.$row->status.'</b>' !!}
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}" role="progressbar"
                                                    aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$row->progress_percentage}}%">
                                                        {{$row->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="personal-options ms-auto">
                                                <div class="dropdown">
                                                    <a type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-horizontal" class="icon-sm icon-wiggle"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        @if($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime')
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.etasks', ['task' => $row->id]) }}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($row->user_status === 'Emergency')
                                                        <a class="dropdown-item d-flex align-items-center"  id="cancelEmergency" data-task="{{$row->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($row->user_status === 'Sleep')
                                                        <a class="dropdown-item d-flex align-items-center"  id="requestOvertime" data-task="{{$row->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @endif
                                                        <a class="dropdown-item d-flex align-items-center" data-bs-toggle="collapse" href="#importantNotesCollapse_{{$row->id}}" role="button" aria-expanded="false" aria-controls="importantNotesCollapse_{{$row->id}}">
                                                            <i data-feather="edit" class="icon-sm me-2"></i> Create Note's
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.lvtasks', ['task' => $row->id]) }}">
                                                            <i data-feather="eye" class="icon-sm me-2"></i> View Task
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center importantTask" data-id="{{$row->id}}" data-is-important="yes" href="javascript:;">
                                                            <i data-feather="trash" class="icon-sm me-2"></i> Remove as important
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center shortcutTask" data-id="{{$row->id}}" data-is-tagged="no" href="javascript:;">
                                                            <i data-feather="tag" class="icon-sm me-2"></i> Create shortcut
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="collapse row px-3" id="importantNotesCollapse_{{$row->id}}">
                                            <input class="form-control convo-info-text mb-2" id="titleImportantNotes__{{ $row->id }}" type="text" placeholder="Enter title">
                                            <textarea class="form-control convo-info-text mb-2" id="contentImportantNotes__{{ $row->id }}" rows="3" type="text" placeholder="Enter notes"></textarea>
                                            <div class="col-12">
                                                <button class="btn btn-light convo-info-text float-end mb-2 ms-2 importantNotesSubmit" type="button" data-task="{{$row->id}}"><i data-feather="check"></i> Save Notes</button>
                                                <button class="btn btn-light convo-info-text float-end mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#importantNotesCollapse_{{$row->id}}" aria-expanded="false" aria-controls="importantNotesCollapse_{{$row->id}}"><i data-feather="x"></i> Close</button>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-center text-muted">No tasks available.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-9 tab-content-personal" id="favorites-tab">
                            <div class="p-3 border-bottom">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="d-flex align-items-end mb-2 mb-md-2">
                                            <i data-feather="star" class="text-muted me-2"></i>
                                            <h4 class="me-1">Favorites Tab</h4>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="input-group convo-info-text" style="overflow: hidden;">
                                            <input class="form-control" type="text" id="searchFavoritesPersonal" placeholder="Search task...">
                                            <span class="input-group-text"><i data-feather="search"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2" style="padding-inline: 10px; max-height: 100vh; overflow-y: auto; overflow-x: hidden;">
                                <form id="favoritesPersonalForm">
                                    <div id="favorites-list">
                                    @if($favorites->count() > 0)
                                        @foreach($favorites as $row)
                                        <div class="personal-content d-flex align-items-center mb-3 task-favorites-tab" data-name="{{$row->title}}" data-task-id="{{ $row->id }}">
                                            <div class="personal-details flex-grow-1">
                                                <div class="personal-title fw-bold">{{$row->title}}</div>
                                                <div class="personal-meta text-muted">
                                                    Task Type: <b class="text-primary">{{$row->type}}</b> -
                                                    Due Date: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->due.'</b>' : '<b class="text-primary">'.$row->due.'</b>' !!} -
                                                    Task Status: {!! $row->status === 'Overdue' ? '<b class="text-danger">'.$row->status.'</b>' : '<b class="text-primary">'.$row->status.'</b>' !!}
                                                </div>
                                                <div class="progress personal-progress mt-2 border {{ $row->status === 'Overdue' ? 'border-danger' : 'border-primary' }}" role="progressbar"
                                                    aria-valuenow="{{$row->progress_percentage}}" aria-valuemin="0" aria-valuemax="100">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated {{ $row->status === 'Overdue' ? 'bg-danger' : 'bg-primary' }}"
                                                        style="width: {{$row->progress_percentage}}%">
                                                        {{$row->progress_percentage}}%
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="personal-options ms-auto">
                                                <div class="dropdown">
                                                    <a type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i data-feather="more-horizontal" class="icon-sm icon-wiggle"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        @if($row->user_status !== 'Emergency' && $row->user_status !== 'Sleep' && $row->user_status !== 'Request Overtime')
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.etasks', ['task' => $row->id]) }}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($row->user_status === 'Emergency')
                                                        <a class="dropdown-item d-flex align-items-center"  id="cancelEmergency" data-task="{{$row->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @elseif($row->user_status === 'Sleep')
                                                        <a class="dropdown-item d-flex align-items-center"  id="requestOvertime" data-task="{{$row->id}}">
                                                            <i data-feather="edit-2" class="icon-sm me-2"></i> Edit Task
                                                        </a>
                                                        @endif
                                                        <a class="dropdown-item d-flex align-items-center" data-bs-toggle="collapse" href="#favoritesNotesCollapse_{{$row->id}}" role="button" aria-expanded="false" aria-controls="favoritesNotesCollapse_{{$row->id}}">
                                                            <i data-feather="edit" class="icon-sm me-2"></i> Create Note's
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center" href="{{ route('intern.lvtasks', ['task' => $row->id]) }}">
                                                            <i data-feather="eye" class="icon-sm me-2"></i> View Task
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center favoritesTask" data-id="{{$row->id}}" data-is-favorites="yes" href="javascript:;">
                                                            <i data-feather="trash" class="icon-sm me-2"></i> Remove as favorites
                                                        </a>
                                                        <a class="dropdown-item d-flex align-items-center shortcutTask" data-id="{{$row->id}}" data-is-tagged="no" href="javascript:;">
                                                            <i data-feather="tag" class="icon-sm me-2"></i> Create shortcut
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="collapse row px-3" id="favoritesNotesCollapse_{{$row->id}}">
                                            <input class="form-control convo-info-text mb-2" id="titleFavoritesNotes__{{ $row->id }}" type="text" placeholder="Enter title">
                                            <textarea class="form-control convo-info-text mb-2" id="contentFavoritesNotes__{{ $row->id }}" rows="3" type="text" placeholder="Enter notes"></textarea>
                                            <div class="col-12">
                                                <button class="btn btn-light convo-info-text float-end mb-2 ms-2 favoritesNotesSubmit" type="button" data-task="{{$row->id}}"><i data-feather="check"></i> Save Notes</button>
                                                <button class="btn btn-light convo-info-text float-end mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#favoritesNotesCollapse_{{$row->id}}" aria-expanded="false" aria-controls="favoritesNotesCollapse_{{$row->id}}"><i data-feather="x"></i> Close</button>
                                            </div>
                                        </div>
                                        @endforeach
                                    @else
                                        <p class="text-center text-muted">No tasks available.</p>
                                    @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="notes-page">
        @foreach($personalPrivateNotes as $note)
            <div class="mt-2 draggablePrivates position-absolute d-none" id="collapsePrivateNotes_{{$note->id}}" style="z-index: 100; top: 50px; right: 30px; width: 500px;">
                <div class="draggableHeader text-center" style="padding: 5px; cursor: move; z-index: 110; background: rgba(162, 135, 231, 0.45); border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; border-top-left-radius: 20px !important; border-top-right-radius: 20px !important; border: 1px solid #B4B2B2; border-bottom: none;">
                    <button type="button" class="btn convo-info-btn btn-icon ms-auto showPrivateNotes" data-note="{{$note->id}}" type="button"><i data-feather="x" class="text-muted icon-wiggle"></i></button>
                </div>
                <form id="notesEditForm_{{$note->id}}">
                    <div class="card convo-info-text p-0" style="background: rgba(220, 211, 255, 0.35); border-bottom-left-radius: 20px !important; border-bottom-right-radius: 20px !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;">
                        <div class="card-body p-0">
                            <div class="row p-0 py-2">
                                <input type="hidden" name="id" value="{{$note->id}}">
                                <div class="mb-2 px-5">
                                    <label for="notesTitle">Title:</label>
                                    <input type="text" name="title" placeholder="Enter Title" class="form-control convo-info-text" value="{{$note->title}}">
                                </div>
                                <div class="mb-2 px-5">
                                    <label for="notesDescription">Description:</label>
                                    <textarea type="text" rows="1" name="description" placeholder="Enter Description" class="form-control convo-info-text">{{$note->description}}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label for="notesNotes" class="ps-4">Notes:</label>
                                    <textarea type="text" rows="6" name="notes" placeholder="Enter Notes" class="form-control convo-info-text m-0 p-2 rounded-0  border-start-0 border-end-0">{{$note->notes}}</textarea>
                                </div>
                                <div class="col-12 px-5">
                                    <button type="button" class="btn convo-info-btn float-end saveNotesEdit" type="button" data-note="{{$note->id}}"><i data-feather="check" class="text-muted icon-wiggle me-2"></i> Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
        @foreach($personalNotes as $notes)
            @foreach($notes as $note)
            <div class="mt-2 draggablePrivates position-absolute d-none" id="collapseNotesContent_{{$note->id}}" style="z-index: 100; top: 50px; right: 30px; width: 500px;">
                <div class="draggableHeader text-center" style="padding: 5px; cursor: move; z-index: 110; background: rgba(162, 135, 231, 0.45); border-bottom-left-radius: 0 !important; border-bottom-right-radius: 0 !important; border-top-left-radius: 20px !important; border-top-right-radius: 20px !important; border: 1px solid #B4B2B2; border-bottom: none;">
                    <button type="button" class="btn convo-info-btn btn-icon ms-auto showNotes" data-note="{{$note->id}}" type="button"><i data-feather="x" class="text-muted icon-wiggle"></i></button>
                </div>
                <form id="notesEditForm_{{$note->id}}">
                    <div class="card convo-info-text p-0" style="background: rgba(220, 211, 255, 0.35); border-bottom-left-radius: 20px !important; border-bottom-right-radius: 20px !important; border-top-left-radius: 0 !important; border-top-right-radius: 0 !important;">
                        <div class="card-body p-0">
                            <div class="row p-0 py-2">
                                <input type="hidden" name="id" value="{{$note->id}}">
                                <div class="mb-2 px-5">
                                    <label for="notesTitle">Title:</label>
                                    <input type="text" name="title" placeholder="Enter Title" class="form-control convo-info-text" value="{{$note->title}}">
                                </div>
                                <div class="mb-2 px-5">
                                    <label for="notesDescription">Description:</label>
                                    <textarea type="text" rows="1" name="description" placeholder="Enter Description" class="form-control convo-info-text">{{$note->description}}</textarea>
                                </div>
                                <div class="mb-2">
                                    <label for="notesNotes" class="ps-4">Notes:</label>
                                    <textarea type="text" rows="6" name="notes" placeholder="Enter Notes" class="form-control convo-info-text m-0 p-2 rounded-0  border-start-0 border-end-0">{{$note->notes}}</textarea>
                                </div>
                                <div class="col-12 px-5">
                                    <button type="button" class="btn convo-info-btn float-end saveNotesEdit" type="button" data-note="{{$note->id}}"><i data-feather="check" class="text-muted icon-wiggle me-2"></i> Save</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            @endforeach
        @endforeach
    </div>
</div>


<script>
function page(notload = true){
    if(notload){
        $('#page').load(location.href + " #page > *");
    }

    setTimeout(() => {
        if ($("#shortcut-list").length) {
            var simpleList = document.querySelector("#shortcut-list");
            const sortable = new Sortable(simpleList, {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function(evt) {
                    updateSortOrder();
                }
            });

            function updateSortOrder() {
                const sortedIds = [];
                $('#shortcut-list li').each(function() {
                    sortedIds.push($(this).data('task-id'));
                });
                console.log('Sending sort order:', sortedIds);
                $.ajax({
                    url: '{{ route("intern.personal_table.update_sort") }}',
                    method: 'POST',
                    data: {
                        type: 'tag',
                        sorted_ids: sortedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            return;
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Toast.fire({
                            icon: 'error',
                            title: 'Error updating sort order'
                        });
                    }
                });
            }
        }

        if($('#favorites-list').length){
            var simpleList = document.querySelector("#favorites-list");
            const sortable = new Sortable(simpleList, {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function(evt) {
                    updateSortOrder();
                }
            });

            function updateSortOrder() {
                const sortedIds = [];
                $('#favorites-list div').each(function() {
                    sortedIds.push($(this).data('task-id'));
                });
                console.log('Sending sort order:', sortedIds);
                $.ajax({
                    url: '{{ route("intern.personal_table.update_sort") }}',
                    method: 'POST',
                    data: {
                        type: 'favorites',
                        sorted_ids: sortedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            return;
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Toast.fire({
                            icon: 'error',
                            title: 'Error updating sort order'
                        });
                    }
                });
            }
        }

        if($('#important-list').length){
            var simpleList = document.querySelector("#important-list");
            const sortable = new Sortable(simpleList, {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function(evt) {
                    updateSortOrder();
                }
            });

            function updateSortOrder() {
                const sortedIds = [];
                $('#important-list div').each(function() {
                    sortedIds.push($(this).data('task-id'));
                });
                console.log('Sending sort order:', sortedIds);
                $.ajax({
                    url: '{{ route("intern.personal_table.update_sort") }}',
                    method: 'POST',
                    data: {
                        type: 'important',
                        sorted_ids: sortedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            return;
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Toast.fire({
                            icon: 'error',
                            title: 'Error updating sort order'
                        });
                    }
                });
            }
        }

        if ($(".grid-notes-sort").length) {
            document.querySelectorAll('.grid-notes-sort').forEach(grid => {
                new Sortable(grid, {
                    animation: 150,
                    ghostClass: 'bg-light',
                    onUpdate: function(evt) {
                        updateSortOrder(evt.from); // Pass the grid element
                    }
                });
            });

            function updateSortOrder(gridElement) {
                const taskId = gridElement.id.split('-').pop(); // Extract task ID from grid ID
                const sortedNotesIds = [];

                $(gridElement).find('.convo-info-btn').each(function() {
                    sortedNotesIds.push($(this).data('notes-id'));
                });

                $.ajax({
                    url: '{{ route("intern.personal_table.update_sort") }}',
                    method: 'POST',
                    data: {
                        type: 'task',
                        task_id: taskId,
                        sorted_notes_ids: sortedNotesIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            return;
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Toast.fire({
                            icon: 'error',
                            title: 'Error updating sort order'
                        });
                    }
                });
            }
        }

        if ($("#grid-private-notes").length) {
            document.querySelectorAll('#grid-private-notes').forEach(grid => {
                new Sortable(grid, {
                    animation: 150,
                    ghostClass: 'bg-light',
                    onUpdate: function(evt) {
                        updateSortOrder(); // Pass the grid element
                    }
                });
            });

            function updateSortOrder() {
                const sortedIds = [];

                $('#grid-private-notes .convo-info-btn').each(function() {
                    sortedIds.push($(this).data('notes-id'));
                });

                $.ajax({
                    url: '{{ route("intern.personal_table.update_sort") }}',
                    method: 'POST',
                    data: {
                        type: 'private',
                        sorted_ids: sortedIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            return;
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                        Toast.fire({
                            icon: 'error',
                            title: 'Error updating sort order'
                        });
                    }
                });
            }

        }

        feather.replace();
    }, 100);
}

$(document).ready(function() {
    page(false);
    initializeComponents();

    function initializeComponents() {
        // Initialize Sortable lists
        if ($("#shortcut-list").length) {
            new Sortable(document.querySelector("#shortcut-list"), {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function() {
                    const sortedIds = [];
                    $('#shortcut-list li').each(function() {
                        sortedIds.push($(this).data('task-id'));
                    });
                    $.ajax({
                        url: '{{ route("intern.personal_table.update_sort") }}',
                        method: 'POST',
                        data: {
                            type: 'tag',
                            sorted_ids: sortedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status !== 'success') {
                                console.error('Error updating sort order');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Toast.fire({
                                icon: 'error',
                                title: 'Error updating sort order'
                            });
                        }
                    });
                }
            });
        }

        if ($('#favorites-list').length) {
            new Sortable(document.querySelector("#favorites-list"), {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function() {
                    const sortedIds = [];
                    $('#favorites-list div').each(function() {
                        sortedIds.push($(this).data('task-id'));
                    });
                    $.ajax({
                        url: '{{ route("intern.personal_table.update_sort") }}',
                        method: 'POST',
                        data: {
                            type: 'favorites',
                            sorted_ids: sortedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status !== 'success') {
                                console.error('Error updating sort order');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Toast.fire({
                                icon: 'error',
                                title: 'Error updating sort order'
                            });
                        }
                    });
                }
            });
        }

        if ($('#important-list').length) {
            new Sortable(document.querySelector("#important-list"), {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function() {
                    const sortedIds = [];
                    $('#important-list div').each(function() {
                        sortedIds.push($(this).data('task-id'));
                    });
                    $.ajax({
                        url: '{{ route("intern.personal_table.update_sort") }}',
                        method: 'POST',
                        data: {
                            type: 'important',
                            sorted_ids: sortedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status !== 'success') {
                                console.error('Error updating sort order');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Toast.fire({
                                icon: 'error',
                                title: 'Error updating sort order'
                            });
                        }
                    });
                }
            });
        }

        // Initialize Sortable grids
        $(".grid-notes-sort").each(function() {
            new Sortable(this, {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function(evt) {
                    const taskId = evt.from.id.split('-').pop();
                    const sortedNotesIds = [];
                    $(evt.from).find('.convo-info-btn').each(function() {
                        sortedNotesIds.push($(this).data('notes-id'));
                    });
                    $.ajax({
                        url: '{{ route("intern.personal_table.update_sort") }}',
                        method: 'POST',
                        data: {
                            type: 'task',
                            task_id: taskId,
                            sorted_notes_ids: sortedNotesIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status !== 'success') {
                                console.error('Error updating sort order');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Toast.fire({
                                icon: 'error',
                                title: 'Error updating sort order'
                            });
                        }
                    });
                }
            });
        });

        if ($("#grid-private-notes").length) {
            new Sortable(document.querySelector("#grid-private-notes"), {
                animation: 150,
                ghostClass: 'bg-light',
                onUpdate: function() {
                    const sortedIds = [];
                    $('#grid-private-notes .convo-info-btn').each(function() {
                        sortedIds.push($(this).data('notes-id'));
                    });
                    $.ajax({
                        url: '{{ route("intern.personal_table.update_sort") }}',
                        method: 'POST',
                        data: {
                            type: 'private',
                            sorted_ids: sortedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.status !== 'success') {
                                console.error('Error updating sort order');
                            }
                        },
                        error: function(xhr) {
                            console.error('Error:', xhr.responseText);
                            Toast.fire({
                                icon: 'error',
                                title: 'Error updating sort order'
                            });
                        }
                    });
                }
            });
        }

        // Initialize draggable private notes
        let maxZIndex = 100;
        $('.draggablePrivates').each(function() {
            let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            const element = $(this);
            const header = element.find('.draggableHeader');

            header.off('mousedown').on('mousedown', function(e) {
                maxZIndex += 1;
                element.css('z-index', maxZIndex);
                $('.draggablePrivates').not(element).each(function() {
                    $(this).css('z-index', '100');
                });

                dragMouseDown(e);
            });

            function dragMouseDown(e) {
                e.preventDefault();
                pos3 = e.clientX;
                pos4 = e.clientY;
                element.data('start-top', element.offset().top);
                element.data('start-right', parseInt(element.css('right')));

                $(document).on('mousemove', elementDrag);
                $(document).on('mouseup', closeDragElement);
            }

            function elementDrag(e) {
                e.preventDefault();
                const deltaX = e.clientX - pos3;
                const deltaY = e.clientY - pos4;
                const newTop = element.data('start-top') + deltaY;
                const newRight = element.data('start-right') - deltaX;
                element.css({
                    top: newTop + 'px',
                    right: newRight + 'px'
                });
            }

            function closeDragElement() {
                $(document).off('mousemove');
                $(document).off('mouseup');
            }
        });

        // Replace Feather icons
        feather.replace();

        // Initialize search functionality
        searchTaskTab();
        searchNotesTaskTab();
        searchImportantTab();
        searchFavoritesTab();
    }

    // Initialize after short delay
    setTimeout(initializeComponents, 100);

    // Toast configuration
    let token = $('meta[name="csrf-token"]').attr('content');
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });

    // Checkbox handling
    $('#inboxCheckAll').change(function() {
        $('.email-list .form-check-input').prop('checked', $(this).prop('checked'));
    });

    $('.email-list').on('change', '.form-check-input', function() {
        if (!$(this).prop('checked')) {
            $('#inboxCheckAll').prop('checked', false);
        }
        if ($('.email-list .form-check-input:checked').length === $('.email-list .form-check-input').length) {
            $('#inboxCheckAll').prop('checked', true);
        }
    });

    // Sort option handling
    $(document).on('click', '.sort-option', function(e) {
        e.preventDefault();
        page(false);
        const sortBy = $(this).data('sort');

        $.ajax({
            url: "{{ route('intern.personal_table_sort') }}",
            type: 'GET',
            data: { sort: sortBy },
            success: function(response) {
                if(response.status == 'success'){
                    var html_task = '';
                    if(response.tasks.length > 0) {
                        response.tasks.forEach(tasks => {
                            html_task += `
                            <div class="personal-content d-flex align-items-center mb-3 ${tasks.is_important ? 'border border-primary' : ''} task-personal-tab" data-name="${tasks.title}">
                                <!-- Task HTML structure -->
                            </div>`;
                        });
                    } else {
                        html_task += `<p class="text-center text-muted">No tasks available.</p>`;
                    }
                    $('.email-list').html(html_task);
                    initializeComponents();
                }
            },
            error: function(xhr) {
                console.error('Error sorting tasks:', xhr.responseText);
            }
        });
    });

    // Tab switching
    $(document).on('click', '.nav-buttons-personal', function() {
        var tabId = $(this).data('tab');
        $('.tab-content-personal').removeClass('show');
        $('#' + tabId).addClass('show');
        $('.nav-buttons-personal').removeClass('active');
        $(this).addClass('active');
        initializeComponents();
    });

    // Task marking
    $(document).on('click', '.markAsPersonal', function() {
        var type = $(this).data('type');
        var formData = $('#taskPersonalForm').serializeArray();
        formData.push({name: 'type', value: type});
        $.ajax({
            url: '{{route("intern.personal_table.mark_task")}}',
            type: 'POST',
            data: $.param(formData),
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    $('#taskPersonalForm')[0].reset();
                    Toast.fire({ icon: 'success', title: response.message });
                    page();
                    initializeComponents();
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Favorites task
    $(document).on('click', '.favoritesTask', function() {
        var task = $(this).data('id');
        var is_fav = $(this).data('is-favorites');
        $.ajax({
            url: '{{route("intern.personal_table.task_favorites")}}',
            type: 'POST',
            data: { task: task, is_fav: is_fav },
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully ${is_fav == 'yes' ? 'remove' : 'set'} as favorites`
                    });
                    page();
                    initializeComponents();
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Important task
    $(document).on('click', '.importantTask', function() {
        var task = $(this).data('id');
        var is_important = $(this).data('is-important');
        $.ajax({
            url: '{{route("intern.personal_table.task_important")}}',
            type: 'POST',
            data: { task: task, is_important: is_important },
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully ${is_important == 'yes' ? 'remove' : 'set'} as important`
                    });
                    page();
                    initializeComponents();
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Shortcut task
    $(document).on('click', '.shortcutTask', function() {
        var task = $(this).data('id');
        var is_tagged = $(this).data('is-tagged');
        $.ajax({
            url: '{{route("intern.personal_table.task_tag")}}',
            type: 'POST',
            data: { task: task, is_tag: is_tagged },
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully ${is_tagged == 'yes' ? 'remove' : 'set'} shortcut`
                    });
                    page();
                    initializeComponents();
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
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
                window.location.href = `/observer/etasks/${task}`;
            } else {
                setTimeout(() => {
                    page(false);
                    initializeComponents();
                }, 1000);
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
                    url: '{{ route("intern.tasks.requestovertimetask") }}',
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
                            setTimeout(() => {
                                page(false);
                                initializeComponents();
                            }, 1000);
                        } else if(response.status === 'error'){
                            Toast.fire({
                                icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                            setTimeout(() => {
                                page(false);
                                initializeComponents();
                            }, 1000);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);
                    }
                });
            } else {
                setTimeout(() => {
                    page(false);
                    initializeComponents();
                }, 1000);
            }
        });
    });

    // Notes submission handlers
    $(document).on('click', '.taskNotesSubmit', function() {
        var task = $(this).data('task');
        var titleField = $(`#titleTaskNotes__${task}`);
        var notesField = $(`#contentTaskNotes__${task}`);
        var form = `title=${titleField.val()}&notes=${notesField.val()}&task=${task}`;

        $.ajax({
            url: '{{route("intern.personal_table.task_notes")}}',
            type: 'POST',
            data: form,
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if (response.status == 'success') {
                    Toast.fire({ icon: 'success', title: `Successfully saved notes.` });
                    titleField.val('');
                    notesField.val('');
                    $('#notes-tab').load(location.href + " #notes-tab > *", initializeComponents);
                    $('#notes-page').load(location.href + " #notes-page > *", initializeComponents);
                } else if (response.status == 'error') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Similar handlers for:
    // - importantNotesSubmit
    // - favoritesNotesSubmit
    // - fromNotesSubmit
    // (with the same pattern of calling initializeComponents after updates)
    $(document).on('click', '.importantNotesSubmit', function() {
        var task = $(this).data('task');
        var titleField = $(`#titleImportantNotes__${task}`);
        var notesField = $(`#contentImportantNotes__${task}`);
        var title = titleField.val();
        var notes = notesField.val();
        var form = `title=${title}&notes=${notes}&task=${task}`;

        $.ajax({
            url: '{{route("intern.personal_table.task_notes")}}',
            type: 'POST',
            data: form,
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if (response.status == 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully saved notes. Check it on the notes tab.`
                    });

                    // Clear the input fields
                    titleField.val('');
                    notesField.val('');

                    $('#notes-tab').load(location.href + " #notes-tab > *");
                    setTimeout(() => {
                        page(false);
                        applyDraggablePrivates();
                    }, 1000);
                } else if (response.status == 'error') {
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
    });

    $(document).on('click', '.favoritesNotesSubmit', function() {
        var task = $(this).data('task');
        var titleField = $(`#titleFavoritesNotes__${task}`);
        var notesField = $(`#contentFavoritesNotes__${task}`);
        var title = titleField.val();
        var notes = notesField.val();
        var form = `title=${title}&notes=${notes}&task=${task}`;

        $.ajax({
            url: '{{route("intern.personal_table.task_notes")}}',
            type: 'POST',
            data: form,
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if (response.status == 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully saved notes. Check it on the notes tab.`
                    });

                    // Clear the input fields
                    titleField.val('');
                    notesField.val('');

                    $('#notes-tab').load(location.href + " #notes-tab > *");
                    setTimeout(() => {
                        page(false);
                        applyDraggablePrivates();
                    }, 1000);
                } else if (response.status == 'error') {
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
    });

    $(document).on('click', '.fromNotesSubmit', function() {
        var task = $(this).data('task');
        var titleField = $(`#titleFromNotes__${task}`);
        var notesField = $(`#contentFromNotes__${task}`);
        var title = titleField.val();
        var notes = notesField.val();
        var form = `title=${title}&notes=${notes}&task=${task}`;

        $.ajax({
            url: '{{route("intern.personal_table.task_notes")}}',
            type: 'POST',
            data: form,
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if (response.status == 'success') {
                    Toast.fire({
                        icon: 'success',
                        title: `Successfully saved notes.`
                    });

                    // Clear the input fields
                    titleField.val('');
                    notesField.val('');

                    $('#notes-tab').load(location.href + " #notes-tab > *");
                    setTimeout(() => {
                        page(false);
                        applyDraggablePrivates();
                    }, 1000);
                } else if (response.status == 'error') {
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
    });

    // Notes removal
    $(document).on('click', '.remove-notes', function() {
        var notes = $(this).data('notes');
        var task = $(this).data('task');
        $.ajax({
            url: '{{route("intern.personal_table.task_notes_remove")}}',
            type: 'POST',
            data: { task: task, notes: notes },
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    Toast.fire({ icon: 'success', title: `Successfully remove notes in task` });
                    $('#notes-tab').load(location.href + " #notes-tab > *", initializeComponents);
                    $('#notes-page').load(location.href + " #notes-page > *", initializeComponents);
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Private notes removal
    $(document).on('click', '.private-remove-notes', function() {
        var notes = $(this).data('notes');
        $.ajax({
            url: '{{route("intern.personal_table.notes_remove")}}',
            type: 'POST',
            data: { notes: notes },
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if(response.status == 'success'){
                    Toast.fire({ icon: 'success', title: `Successfully remove notes` });
                    $('#notes-tab').load(location.href + " #notes-tab > *", initializeComponents);
                    $('#notes-page').load(location.href + " #notes-page > *", initializeComponents);
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Notes editing
    $(document).on('click', '.saveNotesEdit', function() {
        var note = $(this).data('note');
        var form = $(`#notesEditForm_${note}`).serialize();
        $.ajax({
            url: '{{route("intern.personal_table.edit_notes")}}',
            type: 'POST',
            data: form,
            headers: { 'X-CSRF-TOKEN': token },
            success: function(response) {
                if (response.status == 'success') {
                    Toast.fire({ icon: 'success', title: `Successfully saved notes.` });
                } else if (response.status == 'error') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    $('#notes-tab').load(location.href + " #notes-tab > *", initializeComponents);
                    $('#notes-page').load(location.href + " #notes-page > *", initializeComponents);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
            }
        });
    });

    // Private notes creation
    $(document).on('click', '.createPrivateNotes', function() {
        Swal.fire({
            title: 'Create New Self Note',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label for="swal-title" class="form-label">Title</label>
                        <input type="text" id="swal-title" class="form-control convo-info-text" placeholder="Enter note title">
                    </div>
                    <div class="mb-3">
                        <label for="swal-description" class="form-label">Description</label>
                        <textarea id="swal-description" class="form-control convo-info-text" rows="2" placeholder="Enter short description"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="swal-notes" class="form-label">Notes</label>
                        <textarea id="swal-notes" class="form-control convo-info-text" rows="5" placeholder="Enter detailed notes"></textarea>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Note',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            preConfirm: () => {
                return {
                    title: $('#swal-title').val(),
                    description: $('#swal-description').val(),
                    notes: $('#swal-notes').val()
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const noteData = result.value;
                if (!noteData.title) {
                    Toast.fire({ icon: 'error', title: 'Title is required' });
                    return;
                }
                $.ajax({
                    url: '{{ route("intern.personal_table.create_note") }}',
                    method: 'POST',
                    data: {
                        title: noteData.title,
                        description: noteData.description,
                        notes: noteData.notes,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Toast.fire({ icon: 'success', title: 'Note created successfully!' });
                            $('#grid-private-notes').load(location.href + " #grid-private-notes", initializeComponents);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                        Toast.fire({ icon: 'error', title: 'Error creating note' });
                    }
                });
            }
        });
    });

    // Toggle note visibility
    $(document).on('click', '.showPrivateNotes', function() {
        var note = $(this).data('note');
        $(`#collapsePrivateNotes_${note}`).toggleClass('d-none');
    });

    $(document).on('click', '.showNotes', function() {
        var note = $(this).data('note');
        $(`#collapseNotesContent_${note}`).toggleClass('d-none');
    });

    // Search functionality
    function searchTaskTab() {
        $(document).on('input', '#searchTaskPersonal', function() {
            let searchText = $(this).val().toLowerCase().trim();
            $('.task-personal-tab').each(function() {
                let $tab = $(this);
                let chatName = ($tab.data('name') || '').toLowerCase();
                $tab.toggleClass('task-personal-tab-hidden', !chatName.includes(searchText));
            });
        });
    }
    function searchImportantTab() {
        $(document).on('input', '#searchImportantPersonal', function() {
            let searchText = $(this).val().toLowerCase().trim();
            $('.task-important-tab').each(function() {
                let $tab = $(this);
                let chatName = ($tab.data('name') || '').toLowerCase();
                $tab.toggleClass('task-personal-tab-hidden', !chatName.includes(searchText));
            });
        });
    }
    function searchFavoritesTab() {
        $(document).on('input', '#searchFavoritesPersonal', function() {
            let searchText = $(this).val().toLowerCase().trim();
            $('.task-favorites-tab').each(function() {
                let $tab = $(this);
                let chatName = ($tab.data('name') || '').toLowerCase();
                $tab.toggleClass('task-personal-tab-hidden', !chatName.includes(searchText));
            });
        });
    }

    $(document).on('input', '#searchPrivateNotes', function() {
        let searchText = $(this).val().toLowerCase().trim();
        $('.notes-private-tab').each(function() {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).removeClass('d-none');
                $(this).addClass('d-block');
            } else {
                $(this).addClass('d-none');
                $(this).removeClass('d-block');
            }
        });
    });

    function searchNotesTaskTab() {
        $(document).on('input', '#searchTaskNotes', function() {
            let searchText = $(this).val().toLowerCase().trim();
            $('.notes-task-tab').each(function() {
                let chatName = $(this).attr('data-name').toLowerCase();
                if (chatName.includes(searchText)) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
    }
    // Initialize search on load
    searchTaskTab();
    searchNotesTaskTab();
    searchImportantTab();
    searchFavoritesTab();
});
</script>
@endsection