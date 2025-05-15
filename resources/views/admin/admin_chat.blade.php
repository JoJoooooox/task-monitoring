@extends('admin.admin_dashboard')
@section('admin')

@php
    use Illuminate\Support\Str;
@endphp

<script src='https://meet.jit.si/external_api.js'></script>
<div class="page-content">
    <div id="video-conference-container" style="position: relative;">
        <div id="btn-meet" class="d-flex justify-content-between"></div>
        <div id="meet-container" class="meet-wrapper"></div>
    </div>
    <div class="image-wrapper mb-3" style="display: none;">
        <div class="image-viewer">
            <div class="image-viewer-header">
                <button class="close-btn">&times;</button>
                <div class="sender-info">
                    <img src="" class="sender-avatar">
                    <div class="sender-details">
                        <span class="sender-name"></span>
                        <span class="timestamp"></span>
                    </div>
                    <div class="header-controls">
                        <button class="download-btn" title="Download">
                            <i data-feather="download"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="image-viewer-main">
                <button class="nav-btn prev-btn">‹</button>
                <img src="" class="main-image">
                <button class="nav-btn next-btn">›</button>
            </div>

            <div class="image-thumbnails">
                <div class="thumbnails-container"></div>
            </div>
        </div>
    </div>
    <div class="chat-wrapper">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row position-relative">
                        <div class="col-lg-4 chat-aside border-end-lg">
                            <div class="aside-content">
                                <div class="aside-header">
                                    <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                                        <div class="d-flex align-items-center">
                                            <figure class="me-2 mb-0">
                                                <img src="{{ (!empty(Auth::user()->photo)) ? url('upload/photo_bank/'.Auth::user()->photo) : url('upload/nophoto.jfif') }}" class="img-sm rounded-circle" alt="profile">
                                                <div class="status {{Auth::user()->is_online === 1 ? 'online' : 'offline'}}"></div>
                                            </figure>
                                            <div>
                                                <h6>{{Auth::user()->name}}</h6>
                                                <p class="text-muted tx-13">{{ Auth::user()->department ? Auth::user()->department->name : 'No Department' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <form class="search-form">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                            </span>
                                            <input type="text" class="form-control" id="searchAll" placeholder="Search here...">
                                        </div>
                                    </form>
                                </div>
                                <div class="aside-body">
                                    <ul class="nav nav-tabs nav-fill mt-3" role="tablist">
                                        <li class="nav-item">
                                        <a class="nav-link active" id="chats-tab" data-bs-toggle="tab" data-bs-target="#chats" role="tab" aria-controls="chats" aria-selected="true">
                                            <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                                            <i data-feather="message-square" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0 icon-wiggle"></i>
                                            <p class="d-none d-sm-block">Chats</p>
                                            </div>
                                        </a>
                                        </li>
                                        <li class="nav-item">
                                        <a class="nav-link" id="calls-tab" data-bs-toggle="tab" data-bs-target="#calls" role="tab" aria-controls="calls" aria-selected="false">
                                            <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                                            <i data-feather="video" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0 icon-wiggle"></i>
                                            <p class="d-none d-sm-block"> Meet</p>
                                            </div>
                                        </a>
                                        </li>
                                        <li class="nav-item">
                                        <a class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" role="tab" aria-controls="contacts" aria-selected="false">
                                            <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                                            <i data-feather="users" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0 icon-wiggle"></i>
                                            <p class="d-none d-sm-block"> Contacts</p>
                                            </div>
                                        </a>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3" style="overflow: auto;">
                                        <div class="tab-pane fade show active" id="chats" role="tabpanel" aria-labelledby="chats-tab">
                                            <div>
                                                <div class="col-12 d-flex mb-1 mt-2">
                                                    <p class="text-muted my-auto">Recent Chats</p>
                                                    <a type="button" class="ms-auto me-3 icon-wiggle" id="createGroupChat"  data-bs-toggle="modal" data-bs-target="#createGroupModal">
                                                        <i class="mdi mdi-account-multiple-plus" style="font-size: 18px;"></i>
                                                    </a>
                                                </div>
                                                <ul class="list-unstyled chat-list px-1" id="chatContainer">
                                                    @if($chatResult)
                                                        @foreach($chatResult as $chat)
                                                            <li class="chat-item pe-1 recent-chat-item" data-name="{{ $chat['name'] }}" id="chatList_{{$chat['chat_id']}}" data-timestamp="{{$chat['last_message_actual_time']}}">
                                                                <a href="javascript:;" id="viewChat" data-chat="{{$chat['chat_id']}}" class="d-flex align-items-center">
                                                                <figure class="mb-0 me-2">
                                                                    @if($chat['type'] === 'group' && is_array($chat['photo']))
                                                                        @if($chat['convo_photo'] === null)
                                                                        <div class="group-image">
                                                                            <div class="group-photos">
                                                                                @foreach(array_slice($chat['photo'], 0, 2) as $photo)
                                                                                    <img src="{{ !empty($photo) ? url('upload/photo_bank/' . $photo) : url('upload/nophoto.jfif') }}"
                                                                                        class="img-xs rounded-circle border participant-photo" alt="user" style="object-fit: cover; object-position: center;">
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                        @else
                                                                        <img src="{{ !empty($chat['convo_photo']) ? url('/' . $chat['convo_photo']) : url('upload/nophoto.jfif') }}"
                                                                            class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                                                                        @endif
                                                                    @else
                                                                        <img src="{{ !empty($chat['photo']) ? url('upload/photo_bank/' . $chat['photo']) : url('upload/nophoto.jfif') }}"
                                                                            class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                                                                    @endif
                                                                    <div class="status {{$chat['is_online'] == 1 ? 'online' : 'offline'}}"></div>
                                                                </figure>
                                                                <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                                                                    <div>
                                                                        <p class="text-body fw-bolder chat-name text-truncate" style="max-width: 200px">{{$chat['name']}}</p>
                                                                        {!! $chat['is_attached'] ? '<div class="d-flex align-items-center"><i data-feather="file" class="text-muted icon-md mb-2px"></i>  <p class="text-muted ms-1 text-truncate">File Attached</p></div>' : '' !!}

                                                                        <p class="text-muted tx-13 text-truncate chat-last-message" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                        {!!$chat['unseen_count'] > 0 ? '<b>' : ''!!}
                                                                        {{ intval($chat['from_message']) == Auth::id()
                                                                            ? 'You :'
                                                                            : ($chat['type'] == 'group' ? 'Message :' : $chat['name'].' :')
                                                                        }}
                                                                        {{ $chat['last_message'] ? $chat['last_message'] : 'Sent Attachment' }}
                                                                        {!!$chat['unseen_count'] > 0 ? '</b>' : ''!!}
                                                                        </p>

                                                                    </div>
                                                                    <div class="d-flex flex-column align-items-end">
                                                                        <p class="text-muted tx-13 mb-1 chat-last-message-time">{{$chat['last_message_time']}}</p>

                                                                        @if ($chat['unseen_count'] > 0)
                                                                            <div class="badge rounded-pill bg-primary ms-auto">
                                                                                {{ $chat['unseen_count'] }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    @else
                                                        <li class="chat-item pe-1">
                                                            <h6>Currently no recent chat result</h6>
                                                        </li>
                                                        <div class="text-center py-4">
                                                            <div class="avatar avatar-lg mb-2">
                                                                <i class="mdi mdi-wechat text-muted" style="font-size: 38px;"></i>
                                                            </div>
                                                            <h6 class="text-muted">No existing messages</h6>
                                                            <p class="small text-muted">Create your first message and go to "Contacts Tab" to get started</p>
                                                        </div>
                                                    @endif
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="calls" role="tabpanel" aria-labelledby="calls-tab">
                                            <div class="col-12 d-flex mb-1 mt-2">
                                                <p class="text-muted my-auto">Meeting Rooms</p>
                                                <a type="button" class="ms-auto me-3 icon-wiggle" id="createMeeting">
                                                    <i class="mdi mdi-plus-box" style="font-size: 18px;"></i>
                                                </a>
                                            </div>

                                            @if($rooms->isNotEmpty())
                                                <div class="d-flex flex-wrap px-1" style="max-height: 430px; overflow-y: auto; overflow-x: hidden;">
                                                    @foreach($rooms as $room)
                                                    <div class="col-12 col-md-6 col-lg-12 col-xl-12 p-2  meeting-item" data-name="{{ $room['room_name'] }}">
                                                        <div class="chat-item h-100">
                                                            <a href="javascript:;" class="d-flex align-items-center p-2 rounded-3 meeting-room-item h-100"
                                                                data-room-id="{{ $room['room_id'] }}"
                                                                data-room-url="{{ $room['room_url'] }}"
                                                                data-room-name="{{ $room['room_name'] }}"
                                                                data-user-name="{{ Auth::user()->name }}"
                                                                data-creator="{{ $room['is_creator'] ? true : false }}"
                                                                style="background: {{ $room['is_creator'] ? '#f8f9fa' : 'white' }};">

                                                                <!-- Room status indicator -->

                                                                <div class="position-relative me-3">
                                                                    <div class="avatar avatar-md">
                                                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center">
                                                                            <i data-feather="video" class="text-primary"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Room details -->
                                                                <div class="flex-grow-1 pb-2">
                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                        <div>
                                                                            <h6 class="mb-0 text-dark fw-semibold">{{ $room['room_name'] }}</h6>
                                                                            <div class="d-flex align-items-center mt-1">
                                                                                <span class="text-muted small d-flex align-items-center">
                                                                                    <i data-feather="user" class="icon-xs me-1"></i>
                                                                                    {{ $room['user_info']['name'] }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-end">
                                                                            <span class="badge bg-light text-muted small">
                                                                                {{ $room['created_at_human'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Room description and action -->

                                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                                        @if($room['description'])
                                                                        <p class="mb-0 text-muted small text-truncate" style="max-width: 200px;">
                                                                            <i data-feather="message-square" class="icon-xs me-1"></i>
                                                                            {{ $room['description'] }}
                                                                        </p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <p class="mb-0 text-muted small">
                                                                            <i data-feather="clock" class="icon-xs me-1"></i>
                                                                            Started: {{$room['created_at']}}
                                                                        </p>
                                                                    </div>

                                                                    <div class="row g-2 mt-3">
                                                                        @if($room['is_creator'])
                                                                            <div class="col-6 col-lg-12 col-xl-12">
                                                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 end-meeting-btn w-100"
                                                                                        data-room-id="{{ $room['room_id'] }}" data-room-url="{{ $room['room_url'] }}">
                                                                                    End Meet <i data-feather="x" class="icon-xs ms-1"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                        <div class="{{ $room['is_creator'] ? 'col-6' : 'col-12' }} col-lg-12 col-xl-12">
                                                                            <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 w-100">
                                                                                Join <i data-feather="arrow-right" class="icon-xs ms-1"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="avatar avatar-lg mb-3">
                                                        <i data-feather="video-off" class="text-muted"></i>
                                                    </div>
                                                    <h6 class="text-muted">No active meetings</h6>
                                                    <p class="small text-muted">Create your first meeting room to get started</p>
                                                    <button type="button" id="createMeeting" class="btn btn-sm btn-primary">
                                                        <i data-feather="plus" class="icon-xs me-1"></i> Create Meeting
                                                    </button>
                                                </div>
                                            @endif

                                            <hr class="mt-2">
                                            <div class="col-12 d-flex mb-1 mt-2">
                                                <p class="text-muted my-auto">Meet Ended</p>
                                            </div>
                                            @if($roomsEnd->isNotEmpty())
                                                <div class="d-flex flex-wrap px-1" style="max-height: 430px; overflow-y: auto; overflow-x: hidden;">
                                                    @foreach($roomsEnd as $room)
                                                    <div class="col-12 col-md-6 col-lg-12 col-xl-12 p-2  meeting-item" data-name="{{ $room['room_name'] }}">
                                                        <div class="chat-item h-100">
                                                            <a href="javascript:;" class="d-flex align-items-center p-2 rounded-3 meeting-room-item-ended h-100"
                                                                style="background: {{ $room['is_creator'] ? '#f8f9fa' : 'white' }};">

                                                                <!-- Room status indicator -->

                                                                <div class="position-relative me-3">
                                                                    <div class="avatar avatar-md">
                                                                        <div class="rounded-circle bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center">
                                                                            <i data-feather="video" class="text-primary"></i>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- Room details -->
                                                                <div class="flex-grow-1 pb-2">
                                                                    <div class="d-flex justify-content-between align-items-start">
                                                                        <div>
                                                                            <h6 class="mb-0 text-dark fw-semibold">{{ $room['room_name'] }}</h6>
                                                                            <div class="d-flex align-items-center mt-1">
                                                                                <span class="text-muted small d-flex align-items-center">
                                                                                    <i data-feather="user" class="icon-xs me-1"></i>
                                                                                    {{ $room['user_info']['name'] }}
                                                                                </span>
                                                                            </div>
                                                                        </div>
                                                                        <div class="text-end">
                                                                            <span class="badge bg-light text-muted small">
                                                                                {{ $room['created_at_human'] }}
                                                                            </span>
                                                                        </div>
                                                                    </div>

                                                                    <!-- Room description and action -->

                                                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                                                        @if($room['description'])
                                                                        <p class="mb-0 text-muted small text-truncate" style="max-width: 200px;">
                                                                            <i data-feather="message-square" class="icon-xs me-1"></i>
                                                                            {{ $room['description'] }}
                                                                        </p>
                                                                        @endif
                                                                    </div>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <p class="mb-0 text-muted small">
                                                                            <i data-feather="clock" class="icon-xs me-1"></i>
                                                                            Started: {{$room['created_at']}}
                                                                        </p>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                                        <p class="mb-0 text-muted small">
                                                                            <i data-feather="clock" class="icon-xs me-1"></i>
                                                                            Ended: {{$room['updated_at']}}
                                                                        </p>
                                                                    </div>

                                                                    <div class="row g-2 mt-3">
                                                                        @if($room['is_creator'])
                                                                            <div class="col-6 col-lg-12 col-xl-12">
                                                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 delete-meeting-btn w-100"
                                                                                        data-room-id="{{ $room['room_id'] }}" data-room-url="{{ $room['room_url'] }}">
                                                                                    Delete Meet <i data-feather="trash" class="icon-xs ms-1"></i>
                                                                                </button>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center py-4">
                                                    <div class="avatar avatar-lg mb-3">
                                                        <i data-feather="video-off" class="text-muted"></i>
                                                    </div>
                                                    <h6 class="text-muted">No ended meetings</h6>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">
                                            <p class="text-muted mb-1">Contacts</p>
                                            <ul class="list-unstyled chat-list px-1">
                                                @if($users->isNotEmpty())
                                                    @foreach($users as $row)
                                                    @if ($row->id == Auth::id())
                                                        @continue
                                                    @endif
                                                    <li class="chat-item pe-1 contacts-list-item" data-name="{{$row->name}}">
                                                        <a href="javascript:;" id="chatWithUser" data-user="{{$row->id}}" data-name="{{$row->name}}" class="d-flex align-items-center">
                                                            <figure class="mb-0 me-2">
                                                                <img src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}" class="img-xs rounded-circle" alt="user" style="object-fit: cover; object-position: center;">
                                                                <div class="status {{$row->is_online === 1 ? 'online' : 'offline'}}"></div>
                                                            </figure>
                                                            <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                                                                <div>
                                                                    <p class="text-body">{{$row->name}}</p>
                                                                    <div class="d-flex align-items-center">
                                                                        <p class="text-muted tx-13">
                                                                            {{ $row->department ? $row->department->name : (!in_array($row->role, ['employee', 'intern', 'observer']) ? Str::ucfirst($row->role) : 'No Department') }}
                                                                        </p>
                                                                    </div>
                                                                </div>
                                                                <div class="d-flex align-items-end text-body">
                                                                    <i data-feather="message-square" class="icon-md text-primary me-2"></i>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </li>
                                                    @endforeach
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-8 chat-content p-0" id="chatViewDisplay">
                            <div class="chat-body text-center h-100 justify-content-center align-items-center p-0" style="overflow: auto;">
                                <h1>There's Currently No Chat Existing</h1>
                            </div>
                        </div>
                    </div>
                </div>
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

<div class="modal fade" id="forwardMessageModal" tabindex="-1" aria-labelledby="forwardMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
        <form id="messageContactForm" enctype="multipart/form-data">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="worktimeSettingsModalLabel">Forward Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="border-0 px-4">
                <div class="row modal-body-bg border border-primary" id="forwardMessageDisplay">

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

<div class="modal fade" id="unsendMessageModal" tabindex="-1" aria-labelledby="unsendMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
        <form id="messageContactForm" enctype="multipart/form-data">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="worktimeSettingsModalLabel">Unsend Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="border-0 px-4 mb-3">
                <div class="row modal-body-bg border border-primary" id="unsendMessageDisplay">

                </div>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="pinnedMessageModal" tabindex="-1" aria-labelledby="pinnedMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <form id="messageContactForm" enctype="multipart/form-data">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="worktimeSettingsModalLabel">Pinned Message</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="border-0 px-4 mb-3">
                <div class="row modal-body-bg border border-primary" id="pinnedMessageDisplay">

                </div>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="createMeetModal" tabindex="-1" aria-labelledby="createMeetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
        <form id="meetForm" >
            <div class="modal-header border-0">
                <h5 class="modal-title">Create Meet Room</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="border-0 px-4">
                <div class="row modal-body-bg border border-primary">
                    <div class="col-12">
                        <label>Meeting Name</label>
                        <input type="text" name="name" class="form-control rounded-2" placeholder="Enter name">
                    </div>
                    <div class="col-12">
                        <label>Description</label>
                        <textarea type="text" name="description" class="form-control rounded-2" placeholder="Enter Description" rows="4"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
                <button type="submit" id="submitMeetCreate" class="btn btn-primary btn-hover submitMessage">Create Meet</button>
            </div>
        </form>
    </div>
  </div>
</div>

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-labelledby="createGroupModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
        <div class="modal-header border-0">
            <h5 class="modal-title" id="worktimeSettingsModalLabel">Create Group</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
        </div>
        <div class="border-0 px-4">
            <div class="row modal-body-bg border border-primary" id="createGroupDisplay">
                <div class="input-group input-group-sm mb-2">
                    <span class="input-group-text">
                        <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                    </span>
                    <input type="text" class="form-control form-control-sm" id="searchMemberAdd" placeholder="Search user here...">
                </div>
                <span class="reply-span">User List:<span>
                <ul class="list-unstyled chat-list px-1 list-to-select-member" style="overflow-y: auto; height: 200px;">
                    @if($users->isNotEmpty())
                        @foreach($users as $row)
                        @if ($row->id == Auth::id())
                            @continue
                        @endif
                        <li class="chat-item pe-1 member-list-add user-{{$row->id}}" data-name="{{$row->name}}" data-dept="{{ $row->department ? $row->department->name : (!in_array($row->role, ['employee', 'intern', 'observer']) ? Str::ucfirst($row->role) : 'No Department') }}" data-photo="{{$row->photo}}" data-id="{{$row->id}}">
                            <a href="javascript:;" data-user="{{$row->id}}" data-name="{{$row->name}}" class="d-flex align-items-center">
                                <figure class="mb-0 me-2">
                                    <img src="{{ (!empty($row->photo)) ? url('upload/photo_bank/'.$row->photo) : url('upload/nophoto.jfif') }}" class="img-xs rounded-circle" alt="user" style="object-fit: cover; object-position: center;">
                                    <div class="status {{$row->is_online === 1 ? 'online' : 'offline'}}"></div>
                                </figure>
                                <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                                    <div>
                                        <p class="text-body">{{$row->name}}</p>
                                        <div class="d-flex align-items-center">
                                            <p class="text-muted tx-13">
                                                {{ $row->department ? $row->department->name : (!in_array($row->role, ['employee', 'intern', 'observer']) ? Str::ucfirst($row->role) : 'No Department') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end text-body">
                                    <i data-feather="plus" class="icon-md me-2"></i>
                                    </div>
                                </div>
                            </a>
                        </li>
                        @endforeach
                    @endif
                </ul>
                <form id="createGroupForm">
                    @csrf
                    <div class="d-block px-1 selectedMemberGroup" style="overflow-y: auto; max-height: 370px;">
                    </div>
                </form>
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
            <button type="submit" id="" class="btn btn-primary btn-hover submitCreateGroup">Create Group</button>
        </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
    let token = $('meta[name="csrf-token"]').attr('content');
    var baseUrl = "{{ url('/') }}";
    let jitsiApi = null;
    let isMeetingActive = false;

    //region meet daily

    function showError(message) {
        $('#meet-container').html(`
            <div class="alert alert-danger">
                ${message}
                <button class="btn btn-sm btn-light mt-2" onclick="location.reload()">
                    Refresh Page
                </button>
            </div>
        `);
        $('.chat-wrapper').show();
        isMeetingActive = false;
    }

    function showSuccess(message) {
        $('#meet-container').html(`
            <div class="alert alert-success">
                ${message}
            </div>
        `);
        $('.chat-wrapper').show();
        setTimeout(() => cleanupMeeting(), 3000); // Auto-close after 3 seconds
    }

    $(document).on('click', '.meeting-room-item', function() {
        if (isMeetingActive) return;
        const room_id = $(this).data('room-id');
        const roomUrl = $(this).data('room-url');
        const userName = $(this).data('user-name');
        const isCreator = $(this).data('creator');

        cleanupMeeting(true);

        $('.chat-wrapper').hide();
        $('#meet-container')
            .show()
            .html('<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Loading meeting...</p></div>');

            initializeJitsiMeeting(roomUrl, userName, isCreator, room_id);
    });

    function initializeJitsiMeeting(roomUrl, userName, isCreator, room_id) {
        const container = document.getElementById('meet-container');
        container.innerHTML = '';
        isMeetingActive = true;

        try {
            const domain = 'meet.jit.si';
            const options = {
                roomName: new URL(roomUrl).pathname.split('/').pop(),
                width: '100%',
                height: '100%',
                parentNode: container,
                userInfo: {
                    displayName: userName,
                    moderator: isCreator ? 'true' : 'false'
                },
                configOverwrite: {
                    startWithAudioMuted: !isCreator,
                    startWithVideoMuted: !isCreator,
                    disableModeratorIndicator: false,
                    disableRemoteMute: !isCreator, // Blocks non-creators from muting others
                    disableKick: !isCreator,
                    // Disable moderation UI for non-creators
                    toolbarButtons: isCreator ?
                        ['microphone', 'camera', 'desktop', 'fullscreen',
                        'settings', 'videoquality', 'filmstrip', 'shortcuts',
                        'tileview', 'select-background', 'mute-everyone', 'security', 'participants-pane']
                        :
                        ['microphone', 'camera', 'desktop', 'fullscreen',
                        'settings', 'raisehand', 'chat'],
                    // Disable participant moderation for non-creators
                    disableRemoteMute: !isCreator
                },
                interfaceConfigOverwrite: {
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    // Hide moderation buttons for non-creators
                    TOOLBAR_BUTTONS: isCreator ?
                        ['microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                        'tileview', 'select-background', 'download', 'help', 'mute-everyone']
                        :
                        ['microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'profile', 'chat', 'settings',
                        'raisehand', 'videoquality', 'filmstrip', 'feedback', 'shortcuts']
                }
            };

            jitsiApi = new JitsiMeetExternalAPI(domain, options);

            // Jitsi event listeners
            jitsiApi.on('readyToClose', () => {
                cleanupMeeting();
            });

            jitsiApi.on('participantRoleChanged', (event) => {
                if (event.role === 'moderator') {
                    setupCommonUI(true, roomUrl, room_id);
                }
            });

            setupCommonUI(isCreator, roomUrl, room_id);

        } catch (err) {
            console.error('Jitsi error:', err);
            showError(err.message || 'Failed to start Jitsi meeting');
            isMeetingActive = false;
        }
    }

    function setupCommonUI(isCreator, roomUrl, room_id = null) {
        // Clear previous buttons
        $('#btn-meet').empty().show();
        $('#btn-maximize').hide();

        // Create buttons
        const leaveBtn = createButton('Leave', 'arrow-left', cleanupMeeting);
        const minimizeBtn = createButton('Minimize', 'minus-square', () => {
            $('#meet-container').addClass('minimized');
            $('#btn-meet').addClass('d-none');
            $('.chat-wrapper').show();
            $('#btn-maximize').show();
            $('#btn-hide').show();
        });

        if (isCreator) {
            const endMeetingBtn = createButton('End Meeting', 'x', async () => {
                try {
                    await $.ajax({
                        url: `{{ route('admin.chat.removemeeting') }}`,
                        type: 'POST',
                        data: { room: room_id },
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
                    });

                    if (jitsiApi) {
                        jitsiApi.executeCommand('endConference');
                    }

                    $('#calls').load(location.href + ' #calls > *');
                    cleanupMeeting();

                    Toast.fire({
                        icon: 'success',
                        title: 'Meeting ended successfully',
                        timer: 3000
                    });

                } catch (err) {
                    console.error('End meeting error:', err);
                    showError(err.message || 'Failed to end meeting');
                }
            }).addClass('btn-danger');
            $('#btn-meet').append(endMeetingBtn);
        }

        const maximizeBtn = $('<button id="btn-maximize" class="jitsi-maximize-button"></button>')
            .html('<i data-feather="maximize-2"></i>')
            .click(() => {
                $('#meet-container').removeClass('minimized');
                $('#btn-meet').removeClass('d-none');
                $('.chat-wrapper').hide();
                $('#btn-maximize').hide();
                $('#btn-hide').hide();
            });

        const hideBtn = $('<button id="btn-hide" class="jitsi-hide-button"></button>')
            .html('<i data-feather="eye-off"></i>')
            .click(() => {
                const isMinimized = $('#meet-container').hasClass('super-minimized');

                if (isMinimized) {
                    // Restore full view
                    if (jitsiApi) {
                        jitsiApi.executeCommand('toggleVideo');
                        jitsiApi.executeCommand('toggleAudio');
                    }
                    $('#meet-container').removeClass('super-minimized');
                    hideBtn.html('<i data-feather="eye-off"></i>');
                    feather.replace();
                } else {
                    // Minimize but keep audio active
                    if (jitsiApi) {
                        jitsiApi.executeCommand('toggleVideo'); // Hide camera
                    }
                    $('#meet-container').addClass('super-minimized');
                    hideBtn.html('<i data-feather="eye"></i>');
                    feather.replace();
                }
            });

        // Add common buttons
        $('#btn-meet').append(leaveBtn).append(minimizeBtn);
        $('#meet-container').append(maximizeBtn).append(hideBtn);
        feather.replace();

        // Handle meeting events
        if (jitsiApi) {
            jitsiApi.on('readyToClose', () => cleanupMeeting(false));
        }
    }

    function createButton(text, icon, clickHandler) {
        return $(`<button class="btn btn-sm btn-light jitsi-back-button"></button>`)
            .html(`<i data-feather="${icon}"></i> ${text}`)
            .click(clickHandler);
    }

    function cleanupMeeting(shouldShowChat = true) {
        if (!isMeetingActive) return;
        isMeetingActive = false;

        if (jitsiApi) {
            jitsiApi.dispose();
            jitsiApi = null;
        }

        $('#meet-container').hide().empty().removeClass('minimized');
        $('#btn-meet').empty().hide();
        $('#btn-maximize').hide();
        if (shouldShowChat) $('.chat-wrapper').show();
    }

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

    $(document).on('click', '.chat-item a#viewChat[data-chat]', function() {
        const chatId = $(this).data('chat');
        localStorage.setItem('selectedChatId', chatId);
    });

    // Auto-select chat on page load
    function autoSelectChat() {
        const chatContainer = $('#chatContainer');
        const selectedChatId = localStorage.getItem('selectedChatId');

        // Try to find stored chat
        if (selectedChatId) {
            const targetChat = chatContainer.find(`li.chat-item a#viewChat[data-chat="${selectedChatId}"]`);
            if (targetChat.length) {
                setTimeout(() => {
                    if (targetChat[0]) targetChat[0].click(); // Check if element exists
                }, 100);
                return;
            }
        }

        // Fallback to first chat if no stored chat found
        const firstChat = chatContainer.find('li.chat-item:not(:has(h6)) a#viewChat[data-chat]').first();
        if (firstChat.length) {
            setTimeout(() => firstChat[0].click(), 100);
        }
    }

    // Initialize chat selection
    autoSelectChat();

    $(document).on('click', '#chatWithUser', function() {
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
            url: `{{ route('admin.chat.sendcontactmessage') }}`,
            type: 'POST',
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

    //endregion

    //region Chats
    let lastUpdate = null;

    function reloadChatList() {
        $.ajax({
            url: "{{ route('reloadad.chat.list') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}", // CSRF token for Laravel
                lastUpdate: JSON.stringify(lastUpdate) // Send the last update data
            },
            success: function(response) {
                if (response.status === 'initial_load') {
                    // Initial load: Update the entire chat list
                    lastUpdate = response.lastUpdate;
                } else if (response.status === 'count_changed') {
                    // Handle new chats and deleted chats
                    lastUpdate = response.lastUpdate;

                    // Add new chats to the list
                    if (response.newChats && response.newChats.length > 0) {
                        response.newChats.forEach(chat => {
                            addChatToList(chat);
                        });
                    }

                    // Remove deleted chats from the list
                    if (response.deletedChatIds && response.deletedChatIds.length > 0) {
                        response.deletedChatIds.forEach(chatId => {
                            $(`#chatList_${chatId}`).remove(); // Remove the chat by ID
                        });
                    }
                } else if (response.status === 'chat_updated') {
                    // Handle updated chat
                    lastUpdate = response.lastUpdate;

                    if (response.chat) {
                        const chat = response.chat;
                        const chatRow = $(`#chatList_${chat.chat_id}`);

                        if (chatRow.length) {
                            // Update the chat row's data
                            updateChatRow(chatRow, chat);
                        } else {
                            // If the chat row doesn't exist, add it to the list
                            addChatToList(chat);
                        }
                    }
                } else if (response.status === 'no_changes') {
                    // No changes detected
                    lastUpdate = response.lastUpdate;
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    }

    setInterval(reloadChatList, 1000);

    function addChatToList(chat){
        const chatList = $('#chatContainer'); // Replace with your chat list container ID
        const baseUrl = window.baseUrl;
        const chatRow = `
            <li class="chat-item pe-1 recent-chat-item" data-name="${chat.name}" id="chatList_${chat.chat_id}" data-timestamp="${chat.last_message_actual_time}">
                <a href="javascript:;" id="viewChat" data-chat="${chat.chat_id}" class="d-flex align-items-center">
                <figure class="mb-0 me-2">
                    ${chat.type === 'group' && Array.isArray(chat.photo) ?
                        (chat.convo_photo === null ? `
                            <div class="group-image">
                                <div class="group-photos">
                                    ${chat.photo.slice(0, 2).map(photo => `
                                        <img src="${photo ? `/upload/photo_bank/${photo}` : `/upload/nophoto.jfif`}"
                                            class="img-xs rounded-circle border participant-photo" alt="user" style="object-fit: cover; object-position: center;">
                                    `).join('')}
                                </div>
                            </div>
                        ` :
                        `
                            <img src="${chat.convo_photo ? `/${chat.convo_photo}` : `/upload/nophoto.jfif`}"
                                class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                        `)
                    : `
                        <img src="${chat.photo ? `/upload/photo_bank/${chat.photo}` : `/upload/nophoto.jfif`}"
                            class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                    `}
                    <div class="status ${chat.is_online ? 'online' : 'offline'}"></div>
                </figure>
                <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                    <div>
                    <p class="text-body fw-bolder chat-name text-truncate" style="max-width: 200px">${chat.name}</p>
                    ${chat.is_attached ? `
                        <div class="d-flex align-items-center">
                            <i data-feather="file" class="text-muted icon-md mb-2px"></i>
                            <p class="text-muted ms-1 text-truncate">File Attached</p>
                        </div>
                    ` : ''}

                    <p class="text-muted tx-13 text-truncate chat-last-message" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${chat.unseen_count > 0 ? '<b>' : ''}
                        ${chat.from_message === chat.auth_id
                            ? 'You :'
                            : (chat.type === 'group' ? 'Message :' : chat.name + ' :')}
                        ${chat.last_message !== null ? chat.last_message : 'Sent Attachment'}
                    ${chat.unseen_count > 0 ? '</b>' : ''}
                    </p>

                    </div>
                    <div class="d-flex flex-column align-items-end">
                    <p class="text-muted tx-13 mb-1 chat-last-message-time">${chat.last_message_time}</p>

                    ${chat.unseen_count > 0 ? `
                        <div class="badge rounded-pill bg-primary ms-auto">
                            ${chat.unseen_count}
                        </div>
                    ` : ''}
                    </div>
                </div>
                </a>
            </li>
        `;
        chatList.prepend(chatRow);

        // Reinitialize Feather Icons (if needed)
        feather.replace();
    }

    function updateChatRow(chatRow, chat) {
        chatRow.html(`
        <a href="javascript:;" id="viewChat" data-chat="${chat.chat_id}" class="d-flex align-items-center">
            <figure class="mb-0 me-2">
                ${chat.type === 'group' && Array.isArray(chat.photo) ?
                    (chat.convo_photo === null ? `
                        <div class="group-image">
                            <div class="group-photos">
                                ${chat.photo.slice(0, 2).map(photo => `
                                    <img src="${photo ? `/upload/photo_bank/${photo}` : `/upload/nophoto.jfif`}"
                                        class="img-xs rounded-circle border participant-photo" alt="user" style="object-fit: cover; object-position: center;">
                                `).join('')}
                            </div>
                        </div>
                    ` :
                    `
                        <img src="${chat.convo_photo ? `/${chat.convo_photo}` : `/upload/nophoto.jfif`}"
                            class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                    `)
                : `
                    <img src="${chat.photo ? `/upload/photo_bank/${chat.photo}` : `/upload/nophoto.jfif`}"
                        class="img-xs rounded-circle border solo-img" alt="user" style="object-fit: cover; object-position: center;">
                `}
                <div class="status ${chat.is_online ? 'online' : 'offline'}"></div>
            </figure>
            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                <div>
                <p class="text-body fw-bolder chat-name text-truncate" style="max-width: 200px">${chat.name}</p>
                ${chat.is_attached ? `
                    <div class="d-flex align-items-center">
                        <i data-feather="file" class="text-muted icon-md mb-2px"></i>
                        <p class="text-muted ms-1 text-truncate">File Attached</p>
                    </div>
                ` : ''}
                <p class="text-muted tx-13 text-truncate chat-last-message" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${chat.unseen_count > 0 ? '<b>' : ''}
                        ${chat.from_message === chat.auth_id
                            ? 'You :'
                            : (chat.type === 'group' ? 'Message :' : chat.name + ' :')}
                        ${chat.last_message !== null ? chat.last_message : 'Sent Attachment'}
                    ${chat.unseen_count > 0 ? '</b>' : ''}
                </p>
                </div>
                <div class="d-flex flex-column align-items-end">
                <p class="text-muted tx-13 mb-1 chat-last-message-time">${chat.last_message_time}</p>

                ${chat.unseen_count > 0 ? `
                    <div class="badge rounded-pill bg-primary ms-auto">
                        ${chat.unseen_count}
                    </div>
                ` : ''}
                </div>
            </div>
        </a>
        `);

        chatRow.attr('data-timestamp', chat.last_message_actual_time);

        // Get the current top chat's timestamp
        const chatContainer = $('#chatContainer');
        const firstChat = chatContainer.children().first();

        if (firstChat.length) {
            const currentTopTime = new Date(firstChat.data('timestamp')).getTime();
            const updatedTime = new Date(chat.last_message_actual_time).getTime();

            // Move to top ONLY if the updated chat is newer
            if (updatedTime > currentTopTime) {
                chatRow.prependTo(chatContainer);
            }
        } else {
            chatRow.prependTo(chatContainer);
        }

        feather.replace();
    }

    $(document).on('click', '#viewChat', function() {
        var chat = $(this).data('chat');
        $('.chat-content').addClass('show');
        chatDisplay(chat);
    });

    $(document).on('click', '#backToChatList', function() {
        $('.chat-content').removeClass('show');
    })

    function isNearBottom(element, threshold = 100) {
        const { scrollTop, clientHeight, scrollHeight } = element;
        return scrollTop + clientHeight >= scrollHeight - threshold;
    }

    let currentPage = 1;
    let isLoading = false;
    let hasMoreMessages = true;

    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    function chatDisplay(chat, loadMore = false){
        const prevChatBody = document.querySelector('.chat-body');
        let wasNearBottom = false;

        if (prevChatBody) {
            wasNearBottom = isNearBottom(prevChatBody);
        }

        if (!loadMore) {
            // Reset if it's a new chat
            currentPage = 1;
            hasMoreMessages = true;
        } else if (isLoading || !hasMoreMessages) {
            return; // Don't load if already loading or no more messages
        }
        isLoading = true;

        // Show loading indicator if loading more
        if (loadMore) {
            $(`#messagesContainer_${chat}`).prepend('<div class="text-center py-2 loading-more">Loading more messages...</div>');
        }
        $.ajax({
            url: "{{ route('admin.chat.viewchats') }}",
            type: "GET",
            data: {
                chat: chat,
                page: currentPage
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'error'){
                    $('#chats').load(location.href + ' #chats > *', function() {
                        let firstChat = $('#chatContainer').find('li:first-child a#viewChat');
                        if (firstChat.length > 0) {
                            firstChat.trigger('click');
                        }
                        feather.replace(); // Reinitialize icons
                    });
                    return;
                }

                $('.loading-more').remove();
                if (!loadMore) {
                    var chat_html = ``;
                    var chat_info = response.chat_info;
                    chat_html = `
                        <div id="chatDisplayId" data-chat="${chat}" class="chat-header border-bottom pb-2 px-3">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i data-feather="corner-up-left" id="backToChatList" class="icon-lg me-2 ms-n2 text-muted d-lg-none"></i>`;
                                    if(response.otherPart){
                                        response.otherPart.forEach((other, index) => {
                                            chat_html += `
                                            <figure class="mb-0 me-2">
                                            ${other.type === 'group' && Array.isArray(other.other_photo) ?
                                                (response.convo_photo === null ? `
                                                <div class="group-image">
                                                    <div class="group-photos">
                                                        ${other.other_photo.slice(0, 2).map(photo => `
                                                            <img src="${photo ? `/upload/photo_bank/${photo}` : `/upload/nophoto.jfif`}"
                                                                class="img-xs rounded-circle border participant-photo" alt="user" style="object-fit: cover; object-position: center;">
                                                        `).join('')}
                                                    </div>
                                                </div>
                                            ` :
                                            `
                                                <img src="${response.convo_photo ? `/${response.convo_photo}` : `/upload/nophoto.jfif`}"
                                                    class="img-xs rounded-circle border" alt="user" style="object-fit: cover; object-position: center;">
                                                `)
                                            : `
                                                <img src="${other.other_photo ? `/upload/photo_bank/${other.other_photo}` : `/upload/nophoto.jfif`}"
                                                    class="img-xs rounded-circle border" alt="user" style="object-fit: cover; object-position: center;">
                                            `}
                                            <div class="status ${other.other_online ? 'online' : 'offline'}"></div>
                                            </figure>
                                            <div>
                                            <p class="chat-name-inside text-truncate">${other.other_name}</p>
                                            </div>`;
                                        });
                                    }
                                    chat_html += `
                                </div>
                                <div class="d-flex align-items-center me-n1">
                                    <a type="button" class="icon-wiggle convo-info-toggle">
                                        <i class="mdi mdi-information text-secondary" style="font-size: 25px;"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="convo-info-body" style="overflow-y: auto !important; overflow-x: hidden !important;">
                            <div class="row p-2 mt-0">
                                <div class="convo-info-container col-12" id="convoInfo">
                                    <div class="card shadow-sm" style="border-radius: 10px !important">
                                        <div class="position-relative d-flex justify-content-center align-items-center">
                                            <div class="row px-2 px-md-4 pt-2">
                                                <div class="col-12 text-center d-block">
                                                    <div class="d-flex flex-column align-items-center">`;
                                                    if(response.otherPart){
                                                        response.otherPart.forEach((other, index) => {

                                                        chat_html += `
                                                            ${other.type === 'group' && Array.isArray(other.other_photo) ?
                                                                (response.convo_photo === null ? `
                                                                    <div class="group-image mt-2 mb-2">
                                                                        <div class="group-photos-info" style="height: 120px; width: 120px">
                                                                            ${other.other_photo.slice(0, 2).map(photo => `
                                                                                <img src="${photo ? `/upload/photo_bank/${photo}` : `/upload/nophoto.jfif`}"
                                                                                    class="rounded-circle border border-2 border-white mb-2" alt="user"  style="width: 120px; height: 120px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">
                                                                            `).join('')}
                                                                        </div>
                                                                    </div>
                                                                ` :
                                                                `
                                                                <img class="rounded-circle border border-2 border-white mb-2"
                                                                    src="${response.convo_photo ? `/${response.convo_photo}` : `/upload/nophoto.jfif`}"
                                                                    alt="profile"
                                                                    style="width: 150px; height: 150px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">
                                                                `)
                                                            : `
                                                            <img class="rounded-circle border border-2 border-white mb-2"
                                                                src="${other.other_photo ? `/upload/photo_bank/${other.other_photo}` : `/upload/nophoto.jfif`}"
                                                                alt="profile"
                                                                style="width: 150px; height: 150px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">
                                                            `}
                                                            <span class="h4 text-dark">${other.other_name}</span>`;
                                                        });
                                                    }
                                                    chat_html += `
                                                    </div>
                                                </div>
                                                <div class="col-12 text-center d-block mt-2">`;
                                                    if(response.isMuted){
                                                        var isMuted = response.isMuted;
                                                        if(isMuted.is_muted === 0){
                                                            chat_html += `
                                                            <button class="btn btn-primary btn-icon-text convo-info-btn chatMuteSubmit" data-chat="${chat}" data-muted="${isMuted.is_muted}">
                                                                <i data-feather="bell-off" class="btn-icon-prepend"></i> Mute
                                                            </button>
                                                            `;
                                                        } else {
                                                            chat_html += `
                                                            <button class="btn btn-primary btn-icon-text convo-info-btn chatMuteSubmit" data-chat="${chat}" data-muted="${isMuted.is_muted}">
                                                                <i data-feather="bell" class="btn-icon-prepend"></i> Unmute
                                                            </button>
                                                            `;

                                                        }
                                                    }
                                                    chat_html += `
                                                    <button class="btn btn-primary btn-icon-text convo-info-btn" id="searchBtn">
                                                        <i data-feather="search" class="btn-icon-prepend"></i> Search
                                                    </button>`;
                                                    if(chat_info.type == 'group'){
                                                        chat_html += `
                                                        <button class="btn btn-primary btn-icon-text convo-info-btn leaveConversation" data-chat="${chat}">
                                                            <i data-feather="log-out" class="btn-icon-prepend"></i> Leave Conversation
                                                        </button>`;
                                                    }
                                                    chat_html += `
                                                </div>
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center p-3 rounded-bottom">
                                            <ul class="d-flex align-items-center m-0 p-0">
                                                <li class="d-flex align-items-center info-link active" data-tab="tab1">
                                                    <i class="me-1 icon-md" data-feather="info"></i>
                                                    <a class="pt-1px d-none d-md-block text-body" href="#">Chat Information</a>
                                                </li>
                                                <li class="ms-3 ps-3 border-start d-flex align-items-center info-link" data-tab="tab2">
                                                    <i class="me-1 icon-md" data-feather="sliders"></i>
                                                    <a class="pt-1px d-none d-md-block text-body" href="#">Customize Chat</a>
                                                </li>`;
                                                if(chat_info.type == 'group'){
                                                    chat_html += `
                                                    <li class="ms-3 ps-3 border-start d-flex align-items-center info-link" data-tab="tab3">
                                                        <i class="me-1 icon-md" data-feather="users"></i>
                                                        <a class="pt-1px d-none d-md-block text-body" href="#">Group Participants</a>
                                                    </li>`;
                                                }
                                                chat_html += `
                                                <li class="ms-3 ps-3 border-start d-flex align-items-center info-link" data-tab="tab4">
                                                    <i class="me-1 icon-md" data-feather="file"></i>
                                                    <a class="pt-1px d-none d-md-block text-body" href="#">Media & Files</a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div id="tab1" class="info-content shadow-sm mt-2 border show">
                                        <span class="reply-span" style="font-size: 14px !important">Chat Information</span>
                                        <button class="btn btn-primary btn-icon-text convo-info-btn w-100 viewPinnedMessage" data-chat="${chat}">
                                            <i data-feather="paperclip" class="btn-icon-prepend"></i> View Pinned Message
                                        </button>
                                    </div>
                                    <div id="tab2" class="info-content shadow-sm mt-2 border">
                                        <span class="reply-span" style="font-size: 14px !important">Customize Chat</span>`;
                                        if(chat_info.type == 'group'){
                                            chat_html += `
                                            <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#convoPhotoCollapse" aria-expanded="false" aria-controls="convoPhotoCollapse">
                                                <i data-feather="image" class="btn-icon-prepend"></i> Change Conversation Photo
                                            </button>
                                            <div class="collapse p-2" id="convoPhotoCollapse">
                                                <form id="convoPhotoForm" data-chat="${chat}">
                                                    <div class="input-group convo-info-text mb-2" style="overflow: hidden;">
                                                        <label class="input-group-text" for="inputGroupFile01"><i data-feather="paperclip" class="btn-icon-prepend"></i></label>
                                                        <input type="file" name="photo" class="form-control" id="inputGroupFile01" accept="image/*">
                                                        <input type="hidden" name="chat_id" value="${chat}">
                                                    </div>
                                                    <button class="btn btn-icon-text convo-info-btn float-end" type="submit">
                                                        <i data-feather="check" class="btn-icon-prepend"></i> Submit
                                                    </button>
                                                    <button class="btn btn-icon-text convo-info-btn float-end clearConvoImage" type="button" data-chat="${chat}">
                                                        <i data-feather="x" class="btn-icon-prepend"></i> Clear Existing Photo
                                                    </button>
                                                </form>
                                            </div>
                                            <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#convoNameCollapse" aria-expanded="false" aria-controls="convoNameCollapse">
                                                <i data-feather="edit-3" class="btn-icon-prepend"></i> Change Conversation Name
                                            </button>
                                            <div class="collapse p-2" id="convoNameCollapse">
                                                <form id="convoNameForm" data-chat="${chat}">
                                                    <div class="input-group convo-info-text mb-2" style="overflow: hidden;">
                                                        <input type="name" name="name" class="form-control" id="exampleFormControlInput1" placeholder="Enter conversation name">
                                                        <input type="hidden" name="chat_id" value="${chat}">
                                                    </div>
                                                    <button class="btn btn-icon-text convo-info-btn float-end" type="submit">
                                                        <i data-feather="check" class="btn-icon-prepend"></i> Submit
                                                    </button>
                                                    <button class="btn btn-icon-text convo-info-btn float-end clearConvoName" type="button" data-chat="${chat}">
                                                        <i data-feather="x" class="btn-icon-prepend"></i> Clear Conversation Name
                                                    </button>
                                                </form>
                                            </div>
                                            `;
                                        }
                                        chat_html += `
                                        <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#convoNicknameCollapse" aria-expanded="false" aria-controls="convoNicknameCollapse">
                                            <i data-feather="edit-3" class="btn-icon-prepend"></i> Edit nicknames
                                        </button>
                                        <div class="collapse p-2" id="convoNicknameCollapse">
                                            <span class="reply-span" style="font-size: 14px !important">Users</span>
                                            <div class="input-group mb-3 convo-info-text" style="overflow: hidden;">
                                                <span class="input-group-text">
                                                    <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                                </span>
                                                <input type="text" class="form-control" id="searchNick" placeholder="Search here...">
                                            </div>
                                            <div class="d-flex flex-column align-items-center mb-0">`;
                                                if (response.customNickname.length > 0) {
                                                    response.customNickname.forEach((users, index) => {
                                                        let isNext = index >= 1 ? 'mt-2' : '';
                                                        let photoHtml = "";
                                                                let userPhoto = users.photo ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif';
                                                                photoHtml = `<img src="${userPhoto}"
                                                                        class="img-xs rounded-circle border mb-0" alt="user" style="width: 50px; height: 50px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">`;
                                                        chat_html += `
                                                        <div class="convo-participants convo-nicks d-flex justify-content-between align-items-start ${isNext}" style="height: 75px !important" data-name="${users.name}">
                                                            <div class="d-flex flex-grow-1 mb-0"> <!-- Added flex-grow-1 -->
                                                                <figure class="mb-0 me-2">
                                                                    ${photoHtml}
                                                                </figure>
                                                                <div class="convo-participants-content d-flex flex-column justify-content-center">
                                                                    ${users.nickname !== null ? `
                                                                    <p class="mb-0">${users.nickname}</p>
                                                                    <span class="reply-span">Name: ${users.name}</span>
                                                                    ` : `
                                                                    <p class="mb-0">${users.name}</p>
                                                                    <span class="reply-span">Set nickname</span>
                                                                    `}
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-center align-items-center h-100 mb-0" style="min-height: 100% !important">
                                                                <a type="button icon-wiggle" data-bs-toggle="collapse" data-bs-target="#editNicknameUser_${users.user_id}" aria-expanded="false" aria-controls="editNicknameUser_${users.user_id}">
                                                                    <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="edit-3"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="collapse p-2 mt-2 w-100" id="editNicknameUser_${users.user_id}">
                                                            <form id="nicknameForm_${chat}_${users.user_id}" class="input-group">
                                                                <input type="text" class="form-control convo-info-text" name="nickname" placeholder="Enter Nickname" value="${users.nickname !== null ? users.nickname : ''}">
                                                                <button type="button" class="btn btn-icon-text convo-info-btn submit-nickname" data-chat="${chat}" data-user="${users.user_id}" style="padding-inline: 10px !important; box-shadow: 0 6px 0 0 rgba(101, 113, 255, 0.25) !important;">
                                                                    <i data-feather="edit-3" class="btn-icon-prepend"></i> Edit
                                                                </button>
                                                            </form>
                                                        </div>
                                                        `;
                                                    });
                                                }

                                                chat_html += `
                                            </div>
                                        </div>
                                    </div>`;
                                    if(chat_info.type == 'group'){
                                        chat_html += `
                                        <div id="tab3" class="info-content shadow-sm mt-2 border">
                                            <span class="reply-span" style="font-size: 14px !important">Group Participants</span>
                                            <div class="d-grid">
                                            <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2 mb-2" data-bs-toggle="collapse" data-bs-target="#convoAddMemberCollapse" aria-expanded="false" aria-controls="convoAddMemberCollapse">
                                                <i data-feather="user-plus" class="btn-icon-prepend"></i> Add Member
                                            </button>
                                            </div>
                                            <div class="collapse p-2 mb-2" id="convoAddMemberCollapse" style="max-height: 420px; overflow-y: auto; overflow-x: hidden;">
                                                <span class="reply-span" style="font-size: 14px !important">Users</span>
                                                <div class="input-group mb-3 convo-info-text" style="overflow: hidden;">
                                                    <span class="input-group-text">
                                                        <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                                    </span>
                                                    <input type="text" class="form-control" id="searchToAdd" placeholder="Search here...">
                                                </div>
                                                <div class="remove-member-here">
                                                `;
                                                if(response.toAddMember.length > 0){
                                                    response.toAddMember.forEach((users, index) => {
                                                        let isNext = index >= 1 ? 'mt-2' : '';
                                                        let photoHtml = "";
                                                        let userPhoto = users.photo ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif';
                                                        photoHtml = `<img src="${userPhoto}"
                                                            class="img-xs rounded-circle border mb-0" alt="user" style="width: 50px; height: 50px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">`;
                                                        chat_html += `
                                                        <div class="convo-participants d-flex justify-content-between align-items-start search-to-add user-to-add-${users.id} ${isNext}" data-name="${users.name}" style="height: 75px !important">
                                                            <div class="d-flex flex-grow-1 mb-0"> <!-- Added flex-grow-1 -->
                                                                <figure class="mb-0 me-2">
                                                                    ${photoHtml}
                                                                </figure>
                                                                <div class="convo-participants-content d-flex flex-column justify-content-center">
                                                                    <p class="mb-0">${users.name}</p>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-center align-items-center h-100 mb-0" style="min-height: 100% !important">
                                                                <a type="button" href="javascript:;" class="addMemberGroupSubmit" data-chat="${chat}" data-user="${users.id}">
                                                                    <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="user-plus"></i>
                                                                </a>
                                                            </div>
                                                        </div>
                                                        `;
                                                    });
                                                } else {
                                                    chat_html += `
                                                    <div class="convo-participants text-center w-100 p-5" >
                                                        <h4 class="mb-0">All user is already in group</h4>
                                                    </div>
                                                    `;
                                                }
                                                chat_html += `
                                                </div>
                                            </div>
                                            <span class="reply-span" style="font-size: 14px !important">Members</span>
                                            <div class="input-group mb-3 convo-info-text" style="overflow: hidden;">
                                                <span class="input-group-text">
                                                    <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                                </span>
                                                <input type="text" class="form-control" id="searchMember" placeholder="Search here...">
                                            </div>
                                            <div class="d-flex flex-column align-items-center mb-0 add-member-here">
                                            `;
                                                if (response.customNickname.length > 0) {
                                                    response.customNickname.forEach((users, index) => {
                                                        let isNext = index >= 1 ? 'mt-2' : '';
                                                        let photoHtml = "";
                                                        let userPhoto = users.photo ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif';
                                                        photoHtml = `<img src="${userPhoto}"
                                                            class="img-xs rounded-circle border mb-0" alt="user" style="width: 50px; height: 50px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">`;
                                                        chat_html += `
                                                        <div class="convo-participants d-flex justify-content-between align-items-start search-member-list convo-member-${users.user_id} ${isNext}" data-name="${users.name}" style="height: 75px !important">
                                                            <div class="d-flex flex-grow-1 mb-0"> <!-- Added flex-grow-1 -->
                                                                <figure class="mb-0 me-2">
                                                                    ${photoHtml}
                                                                </figure>
                                                                <div class="convo-participants-content d-flex flex-column justify-content-center">
                                                                     ${users.nickname !== null ? `
                                                                    <p class="mb-0">${users.nickname}</p>
                                                                    <span class="reply-span">Name: ${users.name}</span>
                                                                    ` : `
                                                                    <p class="mb-0">${users.name}</p>
                                                                    `}
                                                                </div>
                                                            </div>
                                                            <div class="d-flex justify-content-center align-items-center h-100 mb-0" style="min-height: 100% !important">
                                                                <div class="dropdown">
                                                                    <a type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                        <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="more-horizontal"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                        ${!users.im_user ? `<a class="dropdown-item d-flex align-items-center" id="chatWithUser" data-user="${users.user_id}" data-name="${users.name}" href="javascript:;"><i data-feather="send" class="icon-sm me-2 icon-wiggle"></i> <span class="">Message</span></a>` : ``}
                                                                        ${users.im_admin && !users.is_creator && !users.im_user ?
                                                                        `
                                                                        <a class="dropdown-item d-flex align-items-center toggleAsAdminChat toggle-admin-${users.user_id}" href="javascript:;" data-type="${!users.is_admin ? `add` : `remove`}" data-user="${users.user_id}" data-chat="${chat}">
                                                                            <i data-feather="${!users.is_admin ? `shield` : `shield-off`}" class="icon-sm me-2 icon-wiggle"></i>
                                                                            <span class="">${!users.is_admin ? `Set as admin` : `Remove as admin`}</span>
                                                                        </a>
                                                                        ` : `
                                                                        `
                                                                        }
                                                                        ${users.im_admin && !users.is_admin && !users.is_creator && !users.im_user? `<a class="dropdown-item d-flex align-items-center kickAsAdminChat kick-convo-${users.user_id}" href="javascript:;" data-type="remove" data-user="${users.user_id}" data-chat="${chat}" href="javascript:;"><i data-feather="user-minus" class="icon-sm me-2 icon-wiggle"></i> <span class="">Remove member</span></a>` : ``}
                                                                        ${users.im_user ? `<a class="dropdown-item d-flex align-items-center leaveConversation" data-chat="${chat}" href="javascript:;"><i data-feather="log-out" class="icon-sm me-2 icon-wiggle"></i> <span class="">Leave Group Conversation</span></a>` : ``}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        `;
                                                    });
                                                }

                                                chat_html += `
                                            </div>
                                        </div>`;
                                    }
                                    chat_html += `
                                    <div id="tab4" class="info-content shadow-sm mt-2 border">
                                        <span class="reply-span" style="font-size: 14px !important">Media & Files</span>
                                        <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#convoMediaCollapse" aria-expanded="false" aria-controls="convoMediaCollapse">
                                            <i data-feather="image" class="btn-icon-prepend"></i> Media
                                        </button>
                                        <div class="collapse p-2" id="convoMediaCollapse">
                                            <span class="reply-span mb-2" style="font-size: 14px !important">Media</span>
                                            <div class="convo-media-gallery">`;


                                                const images = response.convoAttachments.filter(att => att.type.startsWith('image/'));
                                                const files = response.convoAttachments.filter(att => !att.type.startsWith('image/'));
                                                if (images.length > 0) {
                                                    images.forEach(media => {
                                                        chat_html += `
                                                        <div class="convo-media-item" data-media-id="${media.id}" data-chat="${chat}">
                                                            <img src="/${media.path}" class="convo-media-img" alt="${media.name}" loading="lazy">
                                                        </div>`;
                                                    });
                                                }

                                                chat_html += `
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-icon-text convo-info-btn w-100 mt-2" data-bs-toggle="collapse" data-bs-target="#convoFilesCollapse" aria-expanded="false" aria-controls="convoFilesCollapse">
                                            <i data-feather="file" class="btn-icon-prepend"></i> Files
                                        </button>
                                        <div class="collapse p-2" id="convoFilesCollapse">
                                            <span class="reply-span mb-2" style="font-size: 14px !important;">Files</span>
                                            <div class="convo-file-container">`;
                                            if (files.length > 0) {
                                                files.forEach(file => {
                                                    const fileUrl = `/${file.path}`;
                                                    const fileExtension = file.name.split('.').pop().toLowerCase();

                                                    // Determine icon based on file type
                                                    let icon = '📄'; // Default icon
                                                    const iconMap = {
                                                        pdf: '📕',
                                                        doc: '📝', docx: '📝',
                                                        txt: '📄', rtf: '📄', csv: '📊',
                                                        ppt: '📊', pptx: '📊',
                                                        xls: '📊', xlsx: '📊', ods: '📊',
                                                        zip: '🗜️', rar: '🗜️', '7z': '🗜️',
                                                        psd: '🎨', ai: '🎨', eps: '🎨',
                                                        svg: '🖼️', ico: '🖼️', tiff: '🖼️'
                                                    };

                                                    if (iconMap[fileExtension]) {
                                                        icon = iconMap[fileExtension];
                                                    }

                                                    chat_html += `
                                                    <div class="convo-file-item">
                                                        <div class="convo-file-icon">${icon}</div>
                                                        <div class="convo-file-name">${file.name}</div>
                                                        <a href="${fileUrl}" download class="convo-file-download" title="Download">
                                                            <i data-feather="download-cloud"></i>
                                                        </a>
                                                    </div>`;
                                                });
                                            }

                                            chat_html += `
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="convo-search-container d-none col-12" id="convoSearch">
                                    <button class="btn btn-primary btn-icon-text convo-info-btn d-none" id="backBtn">
                                        <i data-feather="arrow-left" class="btn-icon-prepend"></i> Back
                                    </button>
                                    <div class="input-group mb-2 mt-3 convo-info-text">
                                        <span class="input-group-text convo-info-text">
                                            <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                        </span>
                                        <input type="text" class="form-control convo-info-text" id="searchMessage" placeholder="Search here..." data-chat="${chat}">
                                    </div>
                                    <div class="row"><div class="col-12 text-center"><span class="text-muted showMessageGuide"></span></div></div>
                                    <div class="searchedDisplay"></div>
                                </div>
                            </div>
                        </div>
                        <div class="pinnedDisplay" data-chat="${chat}">`;
                        if(response.pinnedMessages.length > 0){
                            var pin = response.pinnedMessages[0];
                            let photoHtml = "";
                            let userPhoto = pin.photo ? `/upload/photo_bank/${pin.photo}` : '/upload/nophoto.jfif';
                            photoHtml = `<img src="${userPhoto}" class="img-xs rounded-circle border" alt="user">`;
                            chat_html += `
                            <div class="pinned-body border-bottom pt-2 px-3 viewPinnedMessage" data-chat="${pin.chat_id}">
                                <h5 class="text-muted"><i data-feather="paperclip" class="icon-sm icon-wiggle"></i> Latest Pinned Messages</h5>
                                <ul class="list-unstyled chat-list p-0 m-0 mb-1 px-1">
                                    <li class="chat-item pe-1 mt-2 pinned-message" id="pinnedMessage_${pin.chat_id}" data-chat="${pin.chat_id}">
                                        <a href="javascript:;" class="d-flex align-items-center">
                                            <figure class="mb-0 me-2">
                                                ${photoHtml}
                                            </figure>
                                            <div class="d-flex align-items-center" style="width: 100% !important;">
                                                <div class="w-100 pe-4">
                                                    <p class="text-body reply-span text-muted">${pin.user_name}</p>
                                                    <div class="align-items-center">
                                                    <p class="text-muted reply-bubble tx-13 w-100">
                                                        ${pin.message !== null
                                                            ? pin.message.replace(/\n/g, " ")  // Converts newlines to <br> for display
                                                            : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}</span>
                                                    </p>
                                                    <p class="text-body m-0 reply-span text-muted">Pinned at ${pin.created_at}</p>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-end text-body">
                                                    <i data-feather="chevron-down" class="icon-md text-secondary icon-wiggle"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>`;
                        }
                        chat_html += `
                        </div>
                        <div class="chat-body position-relative p-0 pt-2" style="overflow: auto;">
                            <ul class="messages" id="messagesContainer_${chat}">
                            </ul>
                        </div>
                        <div class="chat-footer px-3">
                            <form id="sendMessageForm" class="d-flex">
                                <input type="hidden" name="chat_id" value="${chat}">
                                <div class="d-none d-md-block">
                                    <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileSendButton">
                                        <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                                    </button>
                                    <input type="file" name="attachments[]" id="fileSendInput" multiple style="display: none;">
                                </div>
                                <div class="search-form flex-grow-1 me-2">
                                    <div class="input-group">
                                        <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Type a message"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex">
                                    <div class="emoji-dropdown me-2">
                                        <button class="btn border btn-icon rounded-circle emoji-dropdown-toggle" type="button">
                                            <i class="text-muted icon-wiggle" data-feather="smile"></i>
                                        </button>
                                        <div class="emoji-container">
                                            ${emojiList()}
                                        </div>
                                    </div>
                                    <button type="button" id="sendToUserMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${chat}">
                                        <i data-feather="send" class="icon-wiggle"></i>
                                    </button>
                                </div>
                            </form>
                        </div>
                    `;


                    $('#chatViewDisplay').html(chat_html);

                    initEmojiTabs()
                    feather.replace();
                }

                if(response.messages && response.messages.length > 0) {
                    let messagesHtml = '';
                    let currentGroup = [];
                    let currentUser = null;

                    // Group consecutive messages from the same user
                    response.messages.forEach((item, index) => {
                        if (item.user_id !== currentUser) {
                            if (currentGroup.length > 0) {
                                messagesHtml += buildMessageGroup(currentGroup);
                            }
                            currentGroup = [item];
                            currentUser = item.user_id;
                        } else {
                            currentGroup.push(item);
                        }

                    });

                    // Add the last group
                    if (currentGroup.length > 0) {
                        messagesHtml += buildMessageGroup(currentGroup);
                    }

                    if (loadMore) {
                        const scrollContainer = document.querySelector(`#messagesContainer_${chat}`);
                        const oldScrollHeight = scrollContainer.scrollHeight;

                        // Prepend for pagination
                        $(`#messagesContainer_${chat}`).prepend(messagesHtml);

                        setTimeout(() => {
                            const newScrollHeight = scrollContainer.scrollHeight;
                            scrollContainer.parentElement.scrollTop = newScrollHeight - oldScrollHeight;
                        }, 0);

                    } else {
                        // Initial load
                        $(`#messagesContainer_${chat}`).html(messagesHtml);
                    }

                    hasMoreMessages = response.hasMore;
                    currentPage++;
                }
                feather.replace();
                const chatBody = document.querySelector('.chat-body');
                if (chatBody) {
                    if (!loadMore) {
                        // Initial load - scroll to bottom
                         setTimeout(() => {
                            chatBody.scrollTop = chatBody.scrollHeight;
                        }, 50);
                    }

                    $(chatBody).off('scroll').on('scroll', function() {
                        if (isNearTop(chatBody) && hasMoreMessages && !isLoading) {
                            chatDisplay(chat, true);
                        }
                    });
                }
                isLoading = false;
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
                $('.loading-more').remove();
                isLoading = false;
            }
        });
    }

    let currentMediaIndex = 0;

    let allMedia = [];

    $(document).on('click', '.convo-media-item', function() {
        const chatId = $(this).data('chat');
        const mediaId = $(this).data('media-id');

        $.ajax({
            url: `{{ route('admin.chat.media', ['chat' => 'CHAT_ID']) }}`.replace('CHAT_ID', chatId),
            type: 'GET',
            success: function(response) {
                if(response.status === 'success') {
                    allMedia = response.media;
                    currentMediaIndex = allMedia.findIndex(m => m.id == mediaId);
                    updateImageViewer();
                    $('.chat-wrapper').hide();
                    // Show image viewer
                    $('.image-wrapper').fadeIn();
                }
            },
            error: function(xhr) {
                console.error('Error loading media:', xhr.responseText);
                Toast.fire({ icon: 'error', title: 'Failed to load media' });
            }
        });
    });

    // Update viewer content
    function updateImageViewer() {
        const media = allMedia[currentMediaIndex];
        const $viewer = $('.image-wrapper');

        $viewer.find('.download-btn')
        .attr('data-url', media.url)
        .attr('data-filename', media.name);
        $viewer.find('.main-image').attr('src', media.url);
        $viewer.find('.sender-avatar').attr('src', media.sender?.avatar || '/upload/nophoto.jfif');
        $viewer.find('.sender-name').text(media.sender?.name || 'Unknown User');
        $viewer.find('.timestamp').text(media.timestamp);

        // Update thumbnails
        const thumbsContainer = $viewer.find('.thumbnails-container');
        thumbsContainer.empty();
        allMedia.forEach((m, index) => {
            thumbsContainer.append(`
                <img src="${m.url}"
                    class="thumbnail ${index === currentMediaIndex ? 'active' : ''}"
                    data-index="${index}">
            `);
        });
    }

    $('.image-wrapper').on('click', '.download-btn', function() {
        const currentImage = $('.main-image').attr('src');
        const fileName = allMedia[currentMediaIndex].name || 'download';

        // Create temporary link
        const link = document.createElement('a');
        link.href = currentImage;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Navigation controls
    $('.image-wrapper').on('click', '.nav-btn', function() {
        const direction = $(this).hasClass('prev-btn') ? -1 : 1;
        currentMediaIndex = (currentMediaIndex + direction + allMedia.length) % allMedia.length;
        updateImageViewer();
    });

    // Thumbnail click
    $('.image-wrapper').on('click', '.thumbnail', function() {
        currentMediaIndex = parseInt($(this).data('index'));
        updateImageViewer();
    });

    // Close viewer
    $('.image-wrapper').on('click', '.close-btn', function() {
        $('.image-wrapper').fadeOut();
        $('.chat-wrapper').show();
    });

    // Keyboard navigation
    $(document).keydown(function(e) {
        if ($('.image-wrapper').is(':visible')) {
            switch(e.key) {
                case 'ArrowLeft':
                    currentMediaIndex = (currentMediaIndex - 1 + allMedia.length) % allMedia.length;
                    updateImageViewer();
                    break;
                case 'ArrowRight':
                    currentMediaIndex = (currentMediaIndex + 1) % allMedia.length;
                    updateImageViewer();
                    break;
                case 'Escape':
                    $('.image-wrapper').fadeOut();
                    // Show chat wrapper
                    $('.chat-wrapper').show();
                    break;
            }
        }
    });

    function isNearTop(element) {
        return element.scrollTop === 0;// Adjust the threshold if needed
    }

    function buildMessageGroup(messages) {
        const isMe = messages[0].user_id === messages[0].my_id;
        const senderPhoto = messages[0].photo;
        let avatarUrl = '/upload/nophoto.jfif';

        if (senderPhoto) {
            avatarUrl = Array.isArray(senderPhoto)
                ? `/upload/photo_bank/${senderPhoto[0]}`
                : `/upload/photo_bank/${senderPhoto}`;
        }

        const avatar = `<img src="${avatarUrl}" class="img-xs rounded-circle border" alt="user" style="object-fit: cover; object-position: center;">`;

        let firstVisibleIndex = -1;
        messages.forEach((msg, index) => {
            if (firstVisibleIndex === -1 && msg.is_unsend != 1) {
                firstVisibleIndex = index;
            }
        });


        let groupHtml =
            `<li class="message-item ${isMe ? 'me' : 'friend'}" data-user-id="${messages[0].user_id}">
                ${!isMe ? avatar : ''}
                <div class="content ${isMe ? 'me-2' : ''}">`;

        messages.forEach((message, index) => {
            const isLast = index === messages.length - 1;
            const isFirstVisibleMessage = index === firstVisibleIndex;
            groupHtml += buildMessageHtml(message, isMe, isFirstVisibleMessage, isLast);
        });

        groupHtml += `</div></li>`;
        return groupHtml;
    }

    function buildMessageHtml(message, isMe, isFirstMessage = true, showTimestamp = true) {
        if (message.is_unsend == 1 && message.user_id === message.my_id) {
            return ""; // Hide message for the sender only
        }

        if(message.status != 'announcement' && message.status != 'nickname'){
            let html = `

            <div class="d-flex flex-column message-container" data-message-id="${message.message_id}">
                <div class="d-flex align-items-center${isMe ? ' justify-content-end' : ''}">
                    ${isMe && !message.is_forwarded && message.is_unsend != 2 ? buildDropdownHtml(message.message_id, isMe, message.message, message.chat_id, message.is_pinned) : ''}
                    ${isMe && message.is_unsend != 2 ? buildReactHtml(message.message_id, isMe, message.message, message.chat_id) : ''}
                    ${isMe && message.is_unsend != 2 ? `
                    <div class="dropdown ${isMe ? 'me-2' : 'ms-2'} align-self-center message-dropdown">
                        <a type="button" class="replyMessage" data-message="${message.message_id}" data-content="${message.message}" data-chat="${message.chat_id}">
                            <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="arrow-up-right"></i>
                        </a>
                    </div>
                    ` : ''}
                    ${message.is_unsend != 2 ? `
                    <div class="message" id="messageRow_${message.message_id}">
                        ${message.replied_id !== null ? `<div class="replyContainer-${message.message_id} mt-2 d-block"></div>` : ''}
                        ${message.is_pinned ? `<span class="edited-span mt-2 ${isMe ? 'align-self-end' : 'align-self-start'} unpinMessage" title="Click To Unpinned" data-chat="${message.chat_id}" data-message="${message.message_id}"><i data-feather="paperclip" class="icon-sm me-2"></i> Pinned</span>` : ''}
                        ${message.is_edited && !message.is_forwarded ? `
                        <span class="edited-span ${isMe ? 'align-self-end' : 'align-self-start'} mt-2 viewEditedMessage" data-message="${message.message_id}" data-bs-toggle="collapse" href="#collapseEditMessage-${message.message_id}" role="button" aria-expanded="false" aria-controls="collapseEditMessage-${message.message_id}">Edited Message</span>
                        ` : ''}
                        ${message.is_forwarded && isMe ? `<span class="reply-span mt-2 align-self-end"><i>"<b>You</b>" forwarded a message</i></span>` : ''}
                        ${message.is_forwarded && !isMe ? `<div class="forwardedByContainer-${message.message_id} mt-2"></div>` : ''}
                        <div class="${!showTimestamp && !isFirstMessage ? 'middlepoint' : ''} ${isFirstMessage ? 'bubble' : 'bubble nopoint'} " style="${isFirstMessage && !showTimestamp && isMe ?  'border-radius: 5px 0 0 5px !important' : ''} ${isFirstMessage && !showTimestamp && !isMe ?  'border-radius: 0 5px 5px 0 !important' : ''}">
                            <p>
                                ${message.message !== null
                                    ? message.message.replace(/\n/g, "<br>")  // Converts newlines to <br> for display
                                    : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}
                            </p>
                            ${message.reactions.length > 0 ? `<div class="reactionsContainer-${message.message_id} react-message mt-2 d-flex">${checkReactions(message.message_id, message.reactions, message.my_id)}</div>` : ''}
                        </div>
                    </div>
                    ` : `
                    <div class="message" id="messageRow_${message.message_id}">
                        <div class="reply-bubble nopoint border border-2 bg-transparent" style="padding: 10px; padding-inline: 50px; color:rgb(150, 155, 165);">
                            <p><i><b>${isMe ? '"You"' : `"${message.user_name}"`} Unsent Message</b></i></p>
                        </div>
                    </div>
                    `}
                    ${!isMe && message.is_unsend != 2 ? `
                    <div class="dropdown ${isMe ? 'me-2' : 'ms-2'} align-self-center message-dropdown">
                        <a type="button" class="replyMessage" data-message="${message.message_id}" data-content="${message.message}" data-chat="${message.chat_id}">
                            <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="arrow-up-left"></i>
                        </a>
                    </div>
                    ` : ''}
                    ${!isMe && message.is_unsend != 2 ? buildReactHtml(message.message_id, isMe, message.message, message.chat_id) : ''}
                    ${!isMe && !message.is_forwarded && message.is_unsend != 2 ? buildDropdownHtml(message.message_id, isMe, message.message, message.chat_id, message.is_pinned) : ''}
                </div>
                ${message.is_unsend != 2 && message.task_id != null ? `<div class="task-container-${message.message_id}"></div>` : ''}
                ${message.is_unsend != 2 ? `<div class="media-container-${message.message_id}"></div>` : ''}
                ${showTimestamp && message.is_unsend != 2 ? `<span class="time-text">${message.created_at}</span>` : ''}
                ${message.last_message_id === message.message_id ? `<div class="seen-container-${message.message_id} d-flex ${isMe ? 'flex-row-reverse gap-1' : ''}"></div>`: ''}
                ${message.is_edited && message.is_unsend != 2 ? `
                <div class="collapse p-1" id="collapseEditMessage-${message.message_id}">
                    <div class="card card-body p-3 edited-message-display-${message.message_id}">

                    </div>
                </div>
                ` : ''}
            </div>`;

            if($(`.media-container-${message.message_id}`).length === 0){
                checkMedia(message.message_id, isMe);
            }

            if($(`.replyContainer-${message.message_id}`).length === 0){
                checkReplies(message.message_id, isMe);
            }

            if($(`.forwardedByContainer-${message.message_id}`).length === 0 && !isMe){
                checkWhoForward(message.message_id);
            }

            if($(`.seen-container-${message.message_id}`).length === 0){
                buildSeen(message.message_id, isMe, message.chat_id);
            }

            if($(`.task-container-${message.message_id}`).length === 0){
                buildTask(message.message_id, message.task_id, isMe, message.user_id);
            }
            return html;
        } else if (message.is_unsend != 1 && message.status == 'announcement'){
            return `
            <div class="d-flex flex-column message-container w-100" data-message-id="${message.message_id}">
                <div class="d-flex align-items-center${isMe ? ' justify-content-end' : 'justify-content-start'}">
                    <div class="message" id="messageRow_${message.message_id}">
                        <div class="announcement-content text-center reply-bubble d-flex">
                            <span class="text-muted small me-2">${message.user_id === message.my_id ? '<b>You</b>' : `<b>${message.user_name}</b>`} ${message.message} </span><b><span class="edited-span viewPinnedMessage" data-chat="${message.chat_id}">See all.</span></b>
                        </div>
                    </div>
                </div>
            </div>
        `;
        } else if (message.is_unsend != 1 && message.status == 'nickname'){
            return `
            <div class="d-flex flex-column message-container w-100" data-message-id="${message.message_id}">
                <div class="d-flex align-items-center${isMe ? ' justify-content-end' : 'justify-content-start'}">
                    <div class="message" id="messageRow_${message.message_id}">
                        <div class="announcement-content text-center reply-bubble d-flex">
                            <span class="text-muted small">${message.user_id === message.my_id ? '<b>You</b>' : `<b>${message.user_name}</b>`} ${message.message} </span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        }
        return ``;
    }

    function buildDropdownHtml(messageId, isMe, message, chatId, is_pinned) {
        return `
        <div class="dropdown ${isMe ? 'me-2' : 'ms-2'} align-self-center message-dropdown">
            <a type="button" id="dropdownMenuButton-${messageId}" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="more-vertical"></i>
            </a>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton-${messageId}">
                ${isMe ? `
                <a class="dropdown-item d-flex align-items-center editMessage" data-message="${messageId}" href="javascript:;">
                    <i data-feather="edit-2" class="icon-sm me-2"></i>
                    <span>Edit Message</span>
                </a>
                ` : ''}
                <a class="dropdown-item d-flex align-items-center forwardMessage" data-message="${messageId}" data-content="${message}" data-chat="${chatId}" href="javascript:;">
                    <i data-feather="share-2" class="icon-sm me-2"></i>
                    <span>Forward Message</span>
                </a>
                ${isMe ? `
                <a class="dropdown-item d-flex align-items-center unsendMessage" href="javascript:;" data-message="${messageId}" data-content="${message}">
                    <i data-feather="trash-2" class="icon-sm me-2"></i>
                    <span>Unsend Message</span>
                </a>
                ` : ``}
                ${!is_pinned ? `
                <a class="dropdown-item d-flex align-items-center pinMessage" href="javascript:;" data-message="${messageId}" data-chat="${chatId}">
                    <i data-feather="paperclip" class="icon-sm me-2"></i>
                    <span>Pin Message</span>
                </a>
                ` : `
                <a class="dropdown-item d-flex align-items-center unpinMessage" href="javascript:;" data-message="${messageId}" data-chat="${chatId}">
                    <i data-feather="paperclip" class="icon-sm me-2"></i>
                    <span>Unpin Message</span>
                </a>
                `}
            </div>
        </div>`;
    }

    function buildReactHtml(messageId, isMe, message, chatId) {
        return `
        <div class="dropdown ${isMe ? 'me-2' : 'ms-2'} align-self-center message-dropdown">
            <a type="button" id="reactMenuButton-${messageId}" class="react-toggle" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-message="${messageId}" data-chat="${chatId}">
                <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="smile"></i>
            </a>
            <div class="dropdown-menu reaction-bar" aria-labelledby="reactMenuButton-${messageId}">
                <span class="reaction" data-type="👍">👍</span>
                <span class="reaction" data-type="😂">😂</span>
                <span class="reaction" data-type="❤️">❤️</span>
                <span class="reaction" data-type="😮">😮</span>
                <span class="reaction" data-type="😢">😢</span>
                <span class="reaction" data-type="😡">😡</span>
            </div>
        </div>`;
    }

    function buildSeen(messageId, isMe, chatId) {
        $.ajax({
            url: "{{ route('admin.chat.checkwhoseen') }}",
            type: "GET",
            data: {
                message: messageId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'error'){
                    $(`.seen-container-${messageId}`).html(``);
                } else if(response.status === 'success'){
                    var seen = response.seen;
                    if(seen.length > 0){
                        var photo_html = ``;
                        seen.forEach(users => {
                            photo_html += `<img src="${users.user.photo != null && users.user.photo != '' ? `/upload/photo_bank/${users.user.photo}` : '/upload/nophoto.jfif'}"
                            class="user-avatar border"
                            alt="${users.user.name}'s avatar" style="height: 20px; width: 20px; margin-right: 0 !important; margin-inline: 2px" title="${users.user.name}">`;
                        });
                        $(`.seen-container-${messageId}`).append(photo_html);
                    }
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    function buildTask(messageId, taskId, isMe, user_id){
        $.ajax({
            url: "{{ route('admin.chat.gettaskinfo') }}",
            type: "GET",
            data: { task: taskId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'error') {
                    $(`.task-container-${messageId}`).html(``);
                } else if (response.status === 'success') {
                    const container = $(`.task-container-${messageId}`);
                    container.html(''); // Clear previous content

                    if (response.task) {
                        var task = response.task;
                        const taskUrl = `/${task.path}`; // Ensure correct path
                        var userIds = response.userIds || [];
                        var myId = parseInt('{{Auth::id()}}');
                        var isOwner = userIds.some(id => id.toString() === myId.toString());
                        let viewUrl = "{{ route('admin.lvtasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', task.id);
                        let editUrl = "{{ route('admin.etasks', ['task' => '__TASK_ID__']) }}".replace('__TASK_ID__', task.id);
                        const task_html = $(
                            `
                            <div class="task-bubble d-flex ${isMe ? 'me' : 'friend'}">
                                <div class="personal-details flex-grow-1">
                                    <div class="personal-title fw-bold"><i class="icon-lg text-muted icon-wiggle me-2" data-feather="file"></i> ${task.title}</div>
                                    <div class="personal-meta text-muted">
                                        Task Type: <b class="text-primary">${task.type}</b> -
                                        Due Date: ${ task.status === 'Overdue' ? `<b class="text-danger">${task.due}</b>` : `<b class="text-primary">${task.due}</b>` } -
                                        Task Status: ${ task.status === 'Overdue' ? `<b class="text-danger">${task.status}</b>` : `<b class="text-primary">${task.status}</b>` }
                                    </div>
                                    <div class="progress personal-progress mt-2 border ${ task.status === 'Overdue' ? 'border-danger' : 'border-primary' }" role="progressbar"
                                        aria-valuenow="${task.progress_percentage}" aria-valuemin="0" aria-valuemax="100">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated ${ task.status === 'Overdue' ? 'bg-danger' : 'bg-primary' }"
                                            style="width: ${task.progress_percentage}%">
                                            ${task.progress_percentage}%
                                        </div>
                                    </div>
                                    <div class="justify-content-center align-items-center mt-2" style="width: 100%;">
                                        <a class="btn btn-outline-primary mb-2" style="width: 100%;" href="${viewUrl}">View Task</a>
                                        ${isOwner && (task.user_status !== 'Emergency' && task.user_status !== 'Sleep' && task.user_status !== 'Request Overtime') ? '<a class="btn btn-primary" style="width: 100%;" href="${editUrl}">Edit Task</a>' : ''}
                                        ${isOwner && (task.user_status === 'Emergency') ? `<button class="btn btn-warning" id="cancelEmergency" style="width: 100%;" data-task="${task.id}">Edit Task</button>` : ''}
                                        ${isOwner && (task.user_status === 'Sleep') ? `<button class="btn btn-info" id="requestOvertime" style="width: 100%;" data-task="${task.id}">Edit Task</button>` : ''}
                                    </div>
                                </div>
                            </div>
                            `
                        );

                        container.append(task_html);

                        // Refresh Feather icons after dynamic content addition
                        feather.replace();
                    }
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

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
                return;
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
                    url: '{{ route("admin.tasks.requestovertimetask") }}',
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

    function checkMedia(messageId, isMe) {
        $.ajax({
            url: "{{ route('admin.chat.checkmessageattachment') }}",
            type: "GET",
            data: { message: messageId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'error') {
                    $(`.media-container-${messageId}`).html(``);
                } else if (response.status === 'success' && response.attachments.length > 0) {
                    const container = $(`.media-container-${messageId}`);
                    container.html(''); // Clear previous content

                    // Separate images and files
                    const images = response.attachments.filter(att => att.type.startsWith('image/'));
                    const files = response.attachments.filter(att => !att.type.startsWith('image/'));

                    // Process images
                    // In your checkMedia() function's image processing section:
                    if (images.length > 0) {
                        const count = images.length;
                        let mediaHtml = `
                            <div class="message-attachment-grid" data-count="${count}">
                        `;

                        // Always show max 4 images
                        const imagesToShow = images.slice(0, 4);

                        imagesToShow.forEach((img, index) => {
                            mediaHtml += `
                                 <div class="grid-item convo-media-item" data-media-id="${img.id}" data-chat="${img.chat_id}" style="position:relative; padding: 0;">
                                    <img src="/${img.path}"
                                        class="img-fluid rounded"
                                        alt="${img.name}"
                                        loading="lazy">
                                    ${index === 3 && count > 4 ?
                                        `<div class="more-images-overlay">+${count - 4}</div>` : ''}
                                </div>
                            `;
                        });

                        mediaHtml += `</div>`;
                        container.append(mediaHtml);
                    }

                    // Process non-image files
                    // In your checkMedia() success handler's file processing section:
                    if (files.length > 0) {
                        files.forEach(file => {
                            const fileUrl = `/${file.path}`; // Ensure correct path
                            const $bubble = $(
                                `<a href="${fileUrl}" download="${file.name}" class="file-bubble d-flex ${isMe ? 'me' : 'friend'}">
                                    <i class="icon-lg text-muted icon-wiggle me-2" data-feather="download-cloud"></i>
                                    <p class="m-0">${file.name}</p>
                                </a>`
                            );

                            // Add hover effects
                            $bubble.hover(
                                () => $bubble.css('opacity', '0.8'),
                                () => $bubble.css('opacity', '1')
                            );

                            container.append($bubble);
                        });

                        // Refresh Feather icons after dynamic content addition
                        feather.replace();
                    }
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    function checkReplies(messageId, isMe) {
        $.ajax({
            url: "{{ route('admin.chat.checkmessagereply') }}",
            type: "GET",
            data: {
                message: messageId
            },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'error'){
                    $(`.replyContainer-${messageId}`).html(``);
                } else if(response.status === 'success'){
                    var fromuser = response.fromuser;
                    var user = response.user;
                    var reply = response.reply;
                    $(`.replyContainer-${messageId}`).append(`
                        <span class="reply-span">${user.id === fromuser.id ? (isMe ? `"<b>You</b>" Replied to your message`: `"<b>${fromuser.name}</b>" Replied own message`) : `"<b>${fromuser.name}</b>" Replied to "<b>${user.name}</b>"`}</span>
                        <div class="d-flex mb-2">
                        ${!isMe ? `<div class="border-start me-2"></div>` : ''}
                        <div class="view-reply-bubble ${isMe ? 'align-self-end' : 'align-self-start'} w-100 m-0">
                            <p>${reply.message !== null ? reply.message : '<span><i data-feather="file" class="text-muted icon-md "></i>  Attachment Sent</span>'}</p>
                        </div>
                        ${isMe ? `<div class="border-end ms-2 "></div>` : ''}
                        </div>

                    `);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    function checkWhoForward(messageId) {
        $.ajax({
            url: "{{ route('admin.chat.checkwhoforward') }}",
            type: "GET",
            dataType: 'json',
            success: function(response) {
                if(response.status === 'error'){
                    $(`.forwardedByContainer-${messageId}`).html(``);
                } else if(response.status === 'success'){
                    var user = response.user;
                    $(`.forwardedByContainer-${messageId}`).append(`
                        <span class="reply-span mt-2 align-self-start"><i>"<b>${user.name}</b>" forwarded a message</i></span>
                    `);
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    function checkReactions(message_id, reactions, myId) {
        var reactionCounts = {};
        var reactionUsers = {}; // Store users for each reaction type
        var userReactions = new Set(); // Store reactions made by myId

        // Process reactions
        reactions.forEach(react => {
            let reactionType = react.reaction;

            // Count reactions
            reactionCounts[reactionType] = (reactionCounts[reactionType] || 0) + 1;

            // Store user names (with null check)
            if (!reactionUsers[reactionType]) {
                reactionUsers[reactionType] = [];
            }

            // Safely get username - fallback to 'Unknown' if user data is missing
            const userName = react.user?.name ||
                            react.username ||
                            `${react.username || 'Unknown'}`;

            reactionUsers[reactionType].push(userName);

            // Track user's reactions
            if (react.user_id === myId) {
                userReactions.add(reactionType);
            }
        });

        // Define reaction display order
        const reactionOrder = ['👍', '😂', '❤️', '😮', '😢', '😡'];

        // Generate HTML
        return reactionOrder
            .filter(reaction => reactionCounts[reaction])
            .map(reaction => {
                const isSelected = userReactions.has(reaction) ? 'selected' : '';
                // Format users with each on new line, comma after all except last
                const userList = reactionUsers[reaction]
                    .map((user, index, array) =>
                        index < array.length - 1 ? `${user},` : user
                    )
                    .join('\n');

                return `
                    <span class="reaction ${isSelected} rounded-5"
                        data-type="${reaction}"
                        data-message="${message_id}"
                        title="Reacted by:\n${userList}">
                        ${reaction} ${reactionCounts[reaction]}
                    </span>
                `;
            })
            .join('');
    }

    let chatUpdates = {}; // Changed to object to track multiple chats

    function reloadChatMessage() {
        if($('#chatDisplayId').length > 0){
            var chat = $('#chatDisplayId').data('chat');
            const activeChatId = localStorage.getItem('selectedChatId');
            $.ajax({
                url: "{{ route('reloadad.chat.message') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    chatUpdate: JSON.stringify(chatUpdates),
                    chat_display_id: chat
                },
                success: function(response) {
                    // Check if response is for multiple chats (object) or single chat (legacy)
                    if (response.status === 'nothere') {
                        $('#chats').load(location.href + ' #chats > *', function() {
                            const chatContainer = $('#chatContainer');
                            let newActiveChat = chatContainer.find(`a#viewChat[data-chat="${activeChatId}"]`);

                            if (response.status === 'nothere') {
                                let firstChat = chatContainer.find('li:first-child a#viewChat');
                                if (firstChat.length > 0) {
                                    firstChat.trigger('click');
                                    localStorage.setItem('selectedChatId', firstChat.data('chat')); // Update selected chat
                                }
                            } else {
                                if (newActiveChat.length > 0) {
                                    newActiveChat.trigger('click'); // Click only if the same chat exists
                                } else {
                                    // Fallback to the first available chat if previous chat doesn't exist
                                    let firstChat = chatContainer.find('li.chat-item:not(:has(h6)) a#viewChat[data-chat]').first();
                                    if (firstChat.length > 0) {
                                        firstChat.trigger('click');
                                        localStorage.setItem('selectedChatId', firstChat.data('chat')); // Update selected chat
                                    }
                                }
                            }

                            feather.replace(); // Reinitialize icons
                        });
                    }
                    if (typeof response === 'object' && !response.status) {
                        // Handle multiple chat response
                        for (const chatId in response) {
                            if (response.hasOwnProperty(chatId)) {
                                const chatData = response[chatId];
                                processChatUpdate(chatData, chatId);
                            }
                        }
                    } else {
                        // Handle single chat response (backward compatibility)
                        processChatUpdate(response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                }
            });
        }
    }

    function processChatUpdate(chatData, chatId = null) {
        const currentChatId = chatId || chatData.messages?.[0]?.chat_id || chatData.newMessages?.[0]?.chat_id;

        if (!currentChatId) return;


        // Update our chat tracking state
        if (chatData.chatUpdate) {
            if (!chatUpdates[currentChatId]) chatUpdates[currentChatId] = {};
            Object.assign(chatUpdates[currentChatId], chatData.chatUpdate);
        }

        chatData.newMessages?.forEach(message => {
            if (!$(`#messagesContainer_${currentChatId} #messageRow_${message.message_id}`).length) {
                appendMessage(message);
            }
        });

        // 2. Remove deleted messages
        chatData.deletedMessageIds?.forEach(id => {
            $(`#messagesContainer_${currentChatId} #messageRow_${id}`).remove();
        });

        // 3. Update existing messages
        chatData.updatedMessages?.forEach(message => {
            const messageRow = $(`#messagesContainer_${currentChatId} #messageRow_${message.message_id}`);
            if (messageRow.length) updateMessageRow(messageRow, message);
        });
    }

    function handleInitialLoad(chatId, messages) {
        const container = $(`#messagesContainer_${chatId}`);
        container.empty();

        if (messages && messages.length) {
            messages.forEach(message => {
                appendMessage(message);
            });
        }
    }

    function handleCountChanged(chatId, newMessages, deletedMessageIds) {
        // Add new messages
        if (newMessages && newMessages.length) {
            newMessages.forEach(message => {
                if ($(`#messagesContainer_${chatId} #messageRow_${message.message_id}`).length === 0) {
                    appendMessage(message);
                }
            });
        }

        // Remove deleted messages
        if (deletedMessageIds && deletedMessageIds.length) {
            deletedMessageIds.forEach(id => {
                $(`#messagesContainer_${chatId} #messageRow_${id}`).remove();
            });
        }
    }

    function handleChatUpdated(chatId, messages) {
        if (messages && messages.length) {
            messages.forEach(message => {
                const messageRow = $(`#messagesContainer_${chatId} #messageRow_${message.message_id}`);
                if (messageRow.length) {
                    updateMessageRow(messageRow, message);
                }
            });
        }
    }

    setInterval(reloadChatMessage, 1000);

    function appendMessage(message) {
        const isMe = message.user_id === message.my_id;
        const containerId = `messagesContainer_${message.chat_id}`;
        const $container = $(`#${containerId}`);

        // Verify container exists
        if ($container.length === 0) {
            return;
        }

        const $lastGroup = $container.find('li.message-item:last');
        const lastGroupUserId = $lastGroup.data('user-id');
        const $lastRow = $lastGroup.find('.message').last();

        // Safely get the last message ID
        const lastId = $lastRow.length ? parseInt($lastRow.attr('id')?.replace('messageRow_', '') || 0) : 0;

        // Check if message should be appended to existing group or create new group
        if ($lastGroup.length && lastGroupUserId === message.user_id) {
            const $content = $lastGroup.find('.content');
            const $existingMessages = $content.find('.message-container');

            // Calculate first visible status
            const isFirstVisible = $existingMessages.length === 0;
            $content.find('.flex-column:last .time-text').remove();

            if (lastId < parseInt(message.message_id)) {
                $content.append(buildMessageHtml(message, isMe, isFirstVisible, true));
            }
        } else {
            if (lastId < parseInt(message.message_id)) {
                $container.append(buildMessageGroup([message]));
            }
        }

        feather.replace();
    }

    function updateMessageRow(messageRow, message) {
        const containerId = `messagesContainer_${message.chat_id}`;
        const $container = $(`#${containerId}`);

        // Verify container exists
        if ($container.length === 0) {
            return;
        }

        // Find the message row within the specific container
        const $messageRow = $container.find(`#messageRow_${message.message_id}`);
        if ($messageRow.length === 0) {
            return;
        }

        const $group = $messageRow.closest('li.message-item');
        const groupUserId = $group.data('user-id');
        const isMe = message.user_id === message.my_id;

        if (groupUserId === message.user_id) {
            const $content = $group.find('.content');
            const $messageContainers = $content.find('.message-container');
            const currentContainer = $messageRow.closest('.message-container');

            const messageIndex = $messageContainers.index(currentContainer);
            const isFirstMessage = messageIndex === 0;
            const isLastMessage = messageIndex === $messageContainers.length - 1;

            // Store UI state before replacement
            const dropdownElement = currentContainer.find('.dropdown');
            const wasDropdownOpen = dropdownElement.hasClass('show');
            const existingReplyHtml = currentContainer.find(`.replyContainer-${message.message_id}`).html();
            const existingMediaHtml = currentContainer.find(`.media-container-${message.message_id}`).html();
            const existingForwardHtml = currentContainer.find(`.forwardedByContainer-${message.message_id}`).html();

            // Build new message HTML
            const newMessageHtml = $(buildMessageHtml(message, isMe, isFirstMessage, isLastMessage));

            // Replace the container
            currentContainer.replaceWith(newMessageHtml);

            // Restore replies if they existed
            if (existingMediaHtml) {
                newMessageHtml.find(`.media-container-${message.message_id}`).html(existingMediaHtml);
            } else {
                // Reinitialize media if it's a new attachment
                checkMedia(message.message_id, isMe);
            }

            if (existingForwardHtml) {
                newMessageHtml.find(`.forwardedByContainer-${message.message_id}`).html(existingForwardHtml);
            } else {
                // Reinitialize media if it's a new attachment
                checkWhoForward(message.message_id);
            }


            if (existingReplyHtml) {
                newMessageHtml.find(`.replyContainer-${message.message_id}`).html(existingReplyHtml);
            } else {
                checkReplies(message.message_id, isMe);
            }

            // Restore dropdown state
            if (wasDropdownOpen) {
                newMessageHtml.find('.dropdown').addClass('show');
                newMessageHtml.find('.dropdown-menu').addClass('show');
            }
        }

        feather.replace();
    }

    function scrollToBottom() {
        const container = $('.chat-body');
        container.scrollTop(container.prop('scrollHeight'));
    }

    $(document).on('click', '#attachFileSendButton', function () {
        $('#fileSendInput').click(); // Open file dialog
    });

    $(document).on('click', '#sendToUserMessage', function () {
        var chat = $(this).data('chat');
        let form = $('#sendMessageForm')[0]; // Get the raw form element
        let formData = new FormData(form);
        $.ajax({
            url: `{{ route('admin.chat.sendmessage') }}`,
            type: 'POST',
            data: formData,
            processData: false, // Don't process the data
            contentType: false, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    $('#sendMessageForm')[0].reset();
                    if ($('#sendMessageForm').find('[name="replied_id"]').length > 0) {
                        $('.chat-footer').html(`
                        <form id="sendMessageForm" class="d-flex">
                            <input type="hidden" name="chat_id" value="${chat}">
                            <div class="d-none d-md-block">
                                <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileSendButton">
                                    <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                                </button>
                                <input type="file" name="attachments[]" id="fileSendInput" multiple style="display: none;">
                            </div>
                            <div class="search-form flex-grow-1 me-2">
                                <div class="input-group">
                                    <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Type a message"></textarea>
                                </div>
                            </div>
                            <div class="d-flex">
                                <div class="dropdown me-2">
                                    <button class="btn border btn-icon rounded-circle" type="button" id="emojiDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <i class="text-muted icon-wiggle" data-feather="smile"></i>
                                    </button>
                                    <div class="dropdown-menu emoji-container" aria-labelledby="emojiDropdown">
                                        ${emojiList()}
                                    </div>
                                </div>
                                <button type="button" id="sendToUserMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${chat}">
                                    <i data-feather="send" class="icon-wiggle"></i>
                                </button>
                            </div>
                        </form>
                        `); // Open file dialog
                        feather.replace();
                        initEmojiTabs();
                    }
                    scrollToBottom();
                } else if(response.status === 'error'){
                    return;
                } else if(response.status === 'attachmentError'){
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

    $(document).on('click', '.replyMessage', function() {
        var message = $(this).data('message');
        var content = $(this).data('content');
        var chat = $(this).data('chat');

        $.ajax({
            url: `{{ route('admin.chat.replieduser') }}`,
            type: 'GET',
            data: {
                message: message
            },
            dataType: 'json',
            success: function (response) {
                // Display success message
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                } else if(response.status === 'success'){
                    var user = response.user;

                    var reply_html = `
                    <div class="row border-top pt-2">
                        <div class="col-12">
                            <span class="reply-span">Replying To "<b>${user.name}</b>"</span>
                            <div class="d-grid reply-bubble position-relative">
                                <p>${content !== null ? content : 'Sent Attachment'}</p>
                                <button type="button" class="btn btn-icon rounded cancelReplyBtnClass" data-bs-toggle="tooltip" data-bs-title="Attach files" id="cancelReplyBtn" data-chat="${chat}">
                                    <i data-feather="x" class="text-muted icon-wiggle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <form id="sendMessageForm" class="d-flex">
                        <input type="hidden" name="chat_id" value="${chat}">
                        <input type="hidden" name="replied_id" value="${message}">
                        <div class="d-none d-md-block">
                            <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileReplySendButton">
                                <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                            </button>
                            <input type="file" name="attachments[]" id="fileReplySendInput" multiple style="display: none;">
                        </div>
                        <div class="search-form flex-grow-1 me-2">
                            <div class="input-group">
                                <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Reply message" rows="1"></textarea>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="emoji-dropdown me-2">
                                <button class="btn border btn-icon rounded-circle emoji-dropdown-toggle" type="button">
                                    <i class="text-muted icon-wiggle" data-feather="smile"></i>
                                </button>
                                <div class="emoji-container">
                                    ${emojiList()}
                                </div>
                            </div>
                            <button type="button" id="sendToUserMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${chat}">
                                <i data-feather="send" class="icon-wiggle"></i>
                            </button>
                        </div>
                    </form>
                    `;

                    $('.chat-footer').html(reply_html);
                    feather.replace();
                    initEmojiTabs();
                }


            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });

    });

    $(document).on('click', '#attachFileReplySendButton', function () {
        $('#fileReplySendInput').click(); // Open file dialog
    });

    $(document).on('click', '#cancelReplyBtn', function () {
        var chat = $(this).data('chat');
        $('.chat-footer').html(`
        <form id="sendMessageForm" class="d-flex">
            <input type="hidden" name="chat_id" value="${chat}">
            <div class="d-none d-md-block">
                <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileSendButton">
                    <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                </button>
                <input type="file" name="attachments[]" id="fileSendInput" multiple style="display: none;">
            </div>
            <div class="search-form flex-grow-1 me-2">
                <div class="input-group">
                    <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Type a message"></textarea>
                </div>
            </div>
            <div class="d-flex">
                <div class="dropdown me-2">
                    <button class="btn border btn-icon rounded-circle" type="button" id="emojiDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="text-muted icon-wiggle" data-feather="smile"></i>
                    </button>
                    <div class="dropdown-menu emoji-container" aria-labelledby="emojiDropdown">
                        ${emojiList()}
                    </div>
                </div>
                <button type="button" id="sendToUserMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${chat}">
                    <i data-feather="send" class="icon-wiggle"></i>
                </button>
            </div>
        </form>
        `); // Open file dialog
        feather.replace();
    });

    $(document).on('click', '.editMessage', function() {
        var message = $(this).data('message');

        $.ajax({
            url: `{{ route('admin.chat.geteditmessage') }}`,
            type: 'GET',
            data: {
                message: message
            },
            dataType: 'json',
            success: function (response) {
                // Display success message
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                } else if(response.status === 'success'){
                    var messages = response.message;

                    var edit_html = `
                    <div class="row border-top pt-2">
                        <div class="col-12">
                            <span class="reply-span">Editing Message</span>
                            <div class="d-grid reply-bubble position-relative">
                                <p>${messages.message !== null ? messages.message : 'Sent Attachment'}</p>
                                <button type="button" class="btn btn-icon rounded cancelReplyBtnClass" data-bs-toggle="tooltip" data-bs-title="Attach files" id="cancelReplyBtn" data-chat="${messages.chat_id}">
                                    <i data-feather="x" class="text-muted icon-wiggle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <form id="editMessageForm" class="d-flex">
                        <input type="hidden" name="chat_id" value="${messages.chat_id}">
                        <input type="hidden" name="message_id" value="${message}">
                        <div class="search-form flex-grow-1 me-2">
                            <div class="input-group">
                                <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Reply message" rows="1">${messages.message !== null ? messages.message : ''}</textarea>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="emoji-dropdown me-2">
                                <button class="btn border btn-icon rounded-circle emoji-dropdown-toggle" type="button">
                                    <i class="text-muted icon-wiggle" data-feather="smile"></i>
                                </button>
                                <div class="emoji-container">
                                    ${emojiList()}
                                </div>
                            </div>
                            <button type="button" id="submitEditMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${message.chat_id}">
                                <i data-feather="send" class="icon-wiggle"></i>
                            </button>
                        </div>
                    </form>
                    `;

                    $('.chat-footer').html(edit_html);
                    feather.replace();
                    initEmojiTabs();
                }


            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('click', '#submitEditMessage', function () {
        var chat = $(this).data('chat');
        let form = $('#editMessageForm')[0]; // Get the raw form element
        let formData = new FormData(form);
        $.ajax({
            url: `{{ route('admin.chat.editmessage') }}`,
            type: 'POST',
            data: formData,
            processData: false, // Don't process the data
            contentType: false, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    $('#editMessageForm')[0].reset();
                    $('.chat-footer').html(`
                    <form id="sendMessageForm" class="d-flex">
                        <input type="hidden" name="chat_id" value="${chat}">
                        <div class="d-none d-md-block">
                            <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attach files" id="attachFileSendButton">
                                <i data-feather="paperclip" class="text-muted icon-wiggle"></i>
                            </button>
                            <input type="file" name="attachments[]" id="fileSendInput" multiple style="display: none;">
                        </div>
                        <div class="search-form flex-grow-1 me-2">
                            <div class="input-group">
                                <textarea name="message" class="form-control rounded-pill" id="chatForm" placeholder="Type a message"></textarea>
                            </div>
                        </div>
                       <div class="d-flex">
                            <div class="emoji-dropdown me-2">
                                <button class="btn border btn-icon rounded-circle emoji-dropdown-toggle" type="button">
                                    <i class="text-muted icon-wiggle" data-feather="smile"></i>
                                </button>
                                <div class="emoji-container">
                                    ${emojiList()}
                                </div>
                            </div>
                            <button type="button" id="sendToUserMessage" class="btn btn-primary btn-icon rounded-circle btn-hover" data-chat="${chat}">
                                <i data-feather="send" class="icon-wiggle"></i>
                            </button>
                        </div>
                    </form>
                    `); // Open file dialog
                    feather.replace();
                    initEmojiTabs();

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

    $(document).on('click', '.viewEditedMessage', function() {
        var message = $(this).data('message');
        $.ajax({
            url: `{{ route('admin.chat.vieweditmessage') }}`,
            type: 'GET',
            data: {
                message: message
            },
            dataType: 'json',
            success: function (response) {
                // Display success message
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                } else if(response.status === 'success'){
                    var edited = response.edited;

                    var edit_html = ``;
                    if(edited.length > 0){
                        edited.forEach((edit, index) => {
                            edit_html += `
                            <span class="reply-span">Edited at ${edit.time}</span>
                            <div class="reply-bubble">
                                <p>${edit.message !== null ? edit.message : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}</p>
                            </div>
                            `;

                            if (index !== edited.length - 1) {
                                edit_html += `<div class="border-bottom my-2" style="height: 1px; background-color: #ccc;"></div>`;
                            }
                        });
                    }

                    $(`.edited-message-display-${message}`).html(edit_html);
                    feather.replace();
                }


            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    })

    $(document).on('keydown', 'textarea[name="message"]', function (event) {
        if ((event.which === 13 || event.keyCode === 13) && !event.shiftKey) {

            event.preventDefault(); // Prevents new line in textarea
            const $form = $(this).closest('form'); // Find the closest form
            const message = $(this).val().trim();

            if (message !== "") {
                const $sendButton = $form.find('#sendToUserMessage');
                const $editButton = $form.find('#submitEditMessage');

                if ($sendButton.length) {
                    $sendButton.trigger('click'); // Click send button if it exists
                } else if ($editButton.length) {
                    $editButton.trigger('click'); // Click edit button if send button is missing
                }
            }
        }

    });

    $(document).on('click', '.forwardMessage', function() {
        var message = $(this).data('message');
        var content = $(this).data('content');
        var chat = $(this).data('chat');
        $('#forwardMessageModal').modal('show');
        $.ajax({
            url: `{{ route('admin.chat.viewmessagecontact') }}`,
            type: 'GET',
            data: {
                message: message,
                chat: chat
            },
            dataType: 'json',
            success: function (response) {
                // Display success message
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                } else if(response.status === 'success'){
                    var list_html = ``;
                    if(response.chatResult.length > 0){
                        response.chatResult.forEach(chat => {
                            let photoHtml = "";

                            if (chat.type === "group" && Array.isArray(chat.photo)) {
                                let photoCount = chat.photo.length;
                                let displayedPhotos = chat.photo.slice(0, 2).map(photo => {
                                    return `<img src="${photo ? `/upload/photo_bank/${photo}` : '/upload/nophoto.jfif'}" class="img-xs rounded-circle border participant-photo" alt="user">`;
                                }).join("");

                                let additionalParticipants = photoCount > 2 ? `<div class="additional-participants">+${photoCount - 2}</div>` : "";

                                photoHtml = `
                                    <div class="group-image">
                                        <div class="group-photos">
                                            ${displayedPhotos}
                                            ${additionalParticipants}
                                        </div>
                                    </div>
                                `;
                            } else {
                                let userPhoto = chat.photo ? `/upload/photo_bank/${chat.photo}` : '/upload/nophoto.jfif';
                                photoHtml = `<img src="${userPhoto}" class="img-xs rounded-circle border" alt="user">`;
                            }

                            list_html += `
                            <li class="chat-item pe-1 my-2 forward-item-search" data-name="${chat.name}">
                                <a href="javascript:;" class="d-flex align-items-center submitForwardMessage" data-message="${message}" data-chat="${chat.chat_id}">
                                    <figure class="mb-0 me-2">
                                        ${photoHtml}
                                        <div class="status ${chat.is_online == 1 ? 'online' : 'offline'}"></div>
                                    </figure>
                                    <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                                        <div>
                                            <p class="text-body">${chat.name}</p>
                                            <div class="d-flex align-items-center">
                                            <p class="text-muted tx-13">
                                                ${chat.is_online == 1 ? 'Online' : 'Offline'}
                                            </p>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-end text-body">
                                            <i data-feather="arrow-right-circle" class="icon-md me-2"></i>
                                        </div>
                                    </div>
                                </a>
                            </li>`;
                        });
                    }

                    $('#forwardMessageDisplay').html(`
                        <span class="reply-span">Forwarding Message:<span>
                        <div class="reply-bubble mb-4 text-black">
                            <p>${content}</p>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">
                                <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                            </span>
                            <input type="text" class="form-control form-control-sm" id="searchChat" placeholder="Search user here...">
                        </div>
                        <span class="reply-span">Forward To:<span>
                        <ul class="list-unstyled chat-list px-1">
                            ${list_html}
                        </ul>
                    `);
                    feather.replace();
                }


            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });

    })

    $(document).on('click', '.submitForwardMessage', function() {
        var message = $(this).data('message');
        var chat = $(this).data('chat');

        $.ajax({
            url: `{{ route('admin.chat.sendforwardmessage') }}`,
            type: 'POST',
            data: {
                message: message,
                chat: chat
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully forward message'
                    });
                    $('#forwardMessageModal').modal('hide');
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

    $(document).on('keyup', '#searchChat', function () {
        let searchText = $(this).val().toLowerCase();

        $('.forward-item-search').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $(document).on('keyup', '#searchAll', function () {
        let searchText = $(this).val().toLowerCase();

        $('.recent-chat-item').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('.meeting-item').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('.contacts-list-item').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $(document).on('keyup', '#searchMemberAdd', function () {
        let searchText = $(this).val().toLowerCase();

        $('.member-list-add').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    $(document).on('click', '.member-list-add', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var photo = $(this).data('photo');
        var dept = $(this).data('dept');
        $(`.user-${id}`).remove();

        var selected_html = `
        <div class="col-12 selected-user-${id}">
            <input type="hidden" name="user_ids[]" value="${id}">
            <div class="message-results">
                <div class="message-card">
                    <div class="message-header" style="margin: 0 !important;">
                        <img src="${photo != null && photo != '' ? `/upload/photo_bank/${photo}` : '/upload/nophoto.jfif'}"
                            class="user-avatar"
                            alt="${name}'s avatar">
                        <div class="user-info">
                            <span class="username">${name}</span>
                            <span class="timestamp">${dept}</span>
                        </div>
                        <div class="message-actions">
                            <a type="button" class="member-list-remove" href="javascript:;" data-name="${name}" data-dept="${dept}" data-photo="${photo}" data-id="${id}">
                                <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="x"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
        $('.selectedMemberGroup').prepend(selected_html);
        feather.replace();
    });

    $(document).on('click', '.member-list-remove', function() {
        var id = $(this).data('id');
        var name = $(this).data('name');
        var photo = $(this).data('photo');
        var dept = $(this).data('dept');
        $(`.selected-user-${id}`).remove();

        var removed_html = `
        <li class="chat-item pe-1 member-list-add user-${id}" data-name="${name}" data-dept="${dept}" data-photo="${photo}" data-id="${id}">
            <a href="javascript:;" class="d-flex align-items-center">
                <figure class="mb-0 me-2">
                    <img src="${photo != null && photo != '' ? `/upload/photo_bank/${photo}` : '/upload/nophoto.jfif'}" class="img-xs rounded-circle" alt="user" style="object-fit: cover; object-position: center;">
                    <div class="status {{$row->is_online === 1 ? 'online' : 'offline'}}"></div>
                </figure>
                <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                    <div>
                        <p class="text-body">${name}</p>
                        <div class="d-flex align-items-center">
                            <p class="text-muted tx-13">
                                ${dept}
                            </p>
                        </div>
                    </div>
                    <div class="d-flex align-items-end text-body">
                    <i data-feather="plus" class="icon-md me-2"></i>
                    </div>
                </div>
            </a>
        </li>
        `;
        $('.list-to-select-member').prepend(removed_html);
        feather.replace();
    });

    $(document).on('submit', '#createGroupForm', function(e) {
        e.preventDefault(); // Prevent form submission for testing
        console.log("Form exists on submit:", $(this).length);
    });

    $(document).on('click', '.submitCreateGroup', function() {
        const form = $('#createGroupForm').serialize();
        $.ajax({
            url: `{{ route('admin.chat.creategroupmessage') }}`,
            type: 'POST',
            data: form,
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully created group'
                    });
                    $('#unsendMessageModal').modal('hide');
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

    $(document).on('click', '.unsendMessage', function() {
        var message = $(this).data('message');
        var content = $(this).data('content');
        $('#unsendMessageModal').modal('show');
        $('#unsendMessageDisplay').html(`
            <span class="reply-span">Unsending Your Message:<span>
            <div class="reply-bubble mb-4 text-black">
                <p>${content}</p>
            </div>
            <span class="reply-span">Choose Unsend Method:<span>
            <ul class="list-unstyled chat-list px-1">
                <li class="message-item">
                    <a class="dropdown-item d-flex align-items-center submitUnsend" data-message="${message}" data-type="1" href="javascript:;">
                        <i data-feather="user" class="icon-sm me-2"></i>
                        <p>Unsend For You</p>
                    </a>
                </li>
                 <li class="message-item">
                    <a class="dropdown-item d-flex align-items-center submitUnsend" data-message="${message}" data-type="2" href="javascript:;">
                        <i data-feather="users" class="icon-sm me-2"></i>
                        <p>Unsend For Everyone</p>
                    </a>
                </li>
            </ul>
        `);
        feather.replace();
    });

    $(document).on('click', '.submitUnsend', function() {
        var message = $(this).data('message');
        var type = $(this).data('type');

        $.ajax({
            url: `{{ route('admin.chat.unsendmessage') }}`,
            type: 'POST',
            data: {
                message: message,
                type: parseInt(type)
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully unsend message'
                    });
                    $('#unsendMessageModal').modal('hide');
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
    })

    $(document).on('click', '.pinMessage', function() {
        var message = $(this).data('message');
        var chat = $(this).data('chat');
        $.ajax({
            url: `{{ route('admin.chat.pinmessage') }}`,
            type: 'POST',
            data: {
                message: message,
                chat: chat
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    return;
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
    })

    $(document).on('click', '.unpinMessage', function() {
        var message = $(this).data('message');
        var chat = $(this).data('chat');
        $.ajax({
            url: `{{ route('admin.chat.unpinmessage') }}`,
            type: 'POST',
            data: {
                message: message,
                chat: chat
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    if($('#pinnedMessageModal').length > 0){
                        $('#pinnedMessageModal').modal('hide');
                    }
                    return;
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
    })

    function emojiList(){
        return `
        <!-- Smileys & People -->
        <div class="emoji-tab-btn drag-scroll">
            <a class="btn btn-link emoji-link active" data-bs-toggle="collapse" href="#tab1" role="button" aria-expanded="true" aria-controls="tab1"><span class="icon-wiggle">😀</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab2" role="button" aria-expanded="false" aria-controls="tab2"><span class="icon-wiggle">👦</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab3" role="button" aria-expanded="false" aria-controls="tab3"><span class="icon-wiggle">😺</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab4" role="button" aria-expanded="false" aria-controls="tab4"><span class="icon-wiggle">🌦️</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab5" role="button" aria-expanded="false" aria-controls="tab5"><span class="icon-wiggle">💐</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab6" role="button" aria-expanded="false" aria-controls="tab6"><span class="icon-wiggle">🌍</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab7" role="button" aria-expanded="false" aria-controls="tab7"><span class="icon-wiggle">🍎</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab8" role="button" aria-expanded="false" aria-controls="tab8"><span class="icon-wiggle">🏇</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab9" role="button" aria-expanded="false" aria-controls="tab9"><span class="icon-wiggle">🚗</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab10" role="button" aria-expanded="false" aria-controls="tab10"><span class="icon-wiggle">📱</span></a>
            <a class="btn btn-link emoji-link" data-bs-toggle="collapse" href="#tab11" role="button" aria-expanded="false" aria-controls="tab11"><span class="icon-wiggle">🔣</span></a>
        </div>

        <div class="emoji-tab collapse show" id="tab1">
            <div class="section-header">😊 Smileys</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">😀</span> <span class="emoji icon-wiggle">😁</span> <span class="emoji icon-wiggle">😂</span> <span class="emoji icon-wiggle">🤣</span>
                <span class="emoji icon-wiggle">😃</span> <span class="emoji icon-wiggle">😄</span> <span class="emoji icon-wiggle">😅</span> <span class="emoji icon-wiggle">😆</span>
                <span class="emoji icon-wiggle">😉</span> <span class="emoji icon-wiggle">😊</span> <span class="emoji icon-wiggle">😋</span> <span class="emoji icon-wiggle">😍</span>
                <span class="emoji icon-wiggle">😘</span> <span class="emoji icon-wiggle">😗</span> <span class="emoji icon-wiggle">😙</span> <span class="emoji icon-wiggle">😚</span> <span class="emoji icon-wiggle">😎</span>
                <span class="emoji icon-wiggle">🙂</span> <span class="emoji icon-wiggle">🤗</span> <span class="emoji icon-wiggle">🤩</span> <span class="emoji icon-wiggle">🤔</span>
                <span class="emoji icon-wiggle">🤨</span> <span class="emoji icon-wiggle">😐</span> <span class="emoji icon-wiggle">😑</span> <span class="emoji icon-wiggle">😶</span>
                <span class="emoji icon-wiggle">🙄</span> <span class="emoji icon-wiggle">😏</span> <span class="emoji icon-wiggle">😣</span> <span class="emoji icon-wiggle">😥</span>
                <span class="emoji icon-wiggle">😮</span> <span class="emoji icon-wiggle">🤐</span> <span class="emoji icon-wiggle">😯</span> <span class="emoji icon-wiggle">😪</span>
                <span class="emoji icon-wiggle">😫</span> <span class="emoji icon-wiggle">😴</span> <span class="emoji icon-wiggle">😌</span> <span class="emoji icon-wiggle">😛</span>
                <span class="emoji icon-wiggle">😜</span> <span class="emoji icon-wiggle">😝</span> <span class="emoji icon-wiggle">🤤</span> <span class="emoji icon-wiggle">😒</span>
                <span class="emoji icon-wiggle">😓</span> <span class="emoji icon-wiggle">😔</span> <span class="emoji icon-wiggle">😕</span> <span class="emoji icon-wiggle">🙃</span>
                <span class="emoji icon-wiggle">🤑</span> <span class="emoji icon-wiggle">😲</span> <span class="emoji icon-wiggle">🙁</span> <span class="emoji icon-wiggle">🥵</span>
                <span class="emoji icon-wiggle">😖</span> <span class="emoji icon-wiggle">😞</span> <span class="emoji icon-wiggle">😟</span> <span class="emoji icon-wiggle">😤</span>
                <span class="emoji icon-wiggle">😢</span> <span class="emoji icon-wiggle">😭</span> <span class="emoji icon-wiggle">😦</span> <span class="emoji icon-wiggle">😧</span>
                <span class="emoji icon-wiggle">😨</span> <span class="emoji icon-wiggle">😩</span> <span class="emoji icon-wiggle">🤯</span> <span class="emoji icon-wiggle">😬</span>
                <span class="emoji icon-wiggle">😰</span> <span class="emoji icon-wiggle">😱</span> <span class="emoji icon-wiggle">😳</span> <span class="emoji icon-wiggle">🤪</span>
                <span class="emoji icon-wiggle">😵</span> <span class="emoji icon-wiggle">😡</span> <span class="emoji icon-wiggle">😠</span> <span class="emoji icon-wiggle">🤬</span>
                <span class="emoji icon-wiggle">😷</span> <span class="emoji icon-wiggle">🤒</span> <span class="emoji icon-wiggle">🤕</span> <span class="emoji icon-wiggle">🤢</span>
                <span class="emoji icon-wiggle">🤮</span> <span class="emoji icon-wiggle">🤧</span> <span class="emoji icon-wiggle">😇</span> <span class="emoji icon-wiggle">🤠</span>
                <span class="emoji icon-wiggle">🤡</span> <span class="emoji icon-wiggle">🤥</span> <span class="emoji icon-wiggle">🤫</span> <span class="emoji icon-wiggle">🤭</span>
                <span class="emoji icon-wiggle">🧐</span> <span class="emoji icon-wiggle">🤓</span> <span class="emoji icon-wiggle">😈</span> <span class="emoji icon-wiggle">👿</span>
                <span class="emoji icon-wiggle">👹</span> <span class="emoji icon-wiggle">👺</span> <span class="emoji icon-wiggle">💀</span> <span class="emoji icon-wiggle">👻</span>
                <span class="emoji icon-wiggle">👽</span> <span class="emoji icon-wiggle">🤖</span> <span class="emoji icon-wiggle">💩</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab2">
            <div class="section-header">👦 People</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">👦</span>
                <span class="emoji icon-wiggle">👶</span>
                <span class="emoji icon-wiggle">👧</span>
                <span class="emoji icon-wiggle">👨</span>
                <span class="emoji icon-wiggle">👩</span>
                <span class="emoji icon-wiggle">👴</span>
                <span class="emoji icon-wiggle">👵</span>
                <span class="emoji icon-wiggle">👾</span>
                <span class="emoji icon-wiggle">👨‍⚕️</span>
                <span class="emoji icon-wiggle">👩‍⚕️</span>
                <span class="emoji icon-wiggle">👨‍🎓</span>
                <span class="emoji icon-wiggle">👩‍🎓</span>
                <span class="emoji icon-wiggle">👨‍⚖️</span>
                <span class="emoji icon-wiggle">👩‍⚖️</span>
                <span class="emoji icon-wiggle">👨‍🌾</span>
                <span class="emoji icon-wiggle">👩‍🌾</span>
                <span class="emoji icon-wiggle">👨‍🍳</span>
                <span class="emoji icon-wiggle">👩‍🍳</span>
                <span class="emoji icon-wiggle">👨‍🔧</span>
                <span class="emoji icon-wiggle">👩‍🔧</span>
                <span class="emoji icon-wiggle">👨‍🏭</span>
                <span class="emoji icon-wiggle">👩‍🏭</span>
                <span class="emoji icon-wiggle">👨‍💼</span>
                <span class="emoji icon-wiggle">👩‍💼</span>
                <span class="emoji icon-wiggle">👨‍🔬</span>
                <span class="emoji icon-wiggle">👩‍🔬</span>
                <span class="emoji icon-wiggle">👨‍💻</span>
                <span class="emoji icon-wiggle">👩‍💻</span>
                <span class="emoji icon-wiggle">👨‍🎤</span>
                <span class="emoji icon-wiggle">👩‍🎤</span>
                <span class="emoji icon-wiggle">👨‍🎨</span>
                <span class="emoji icon-wiggle">👩‍🎨</span>
                <span class="emoji icon-wiggle">👨‍✈️</span>
                <span class="emoji icon-wiggle">👩‍✈️</span>
                <span class="emoji icon-wiggle">👨‍🚀</span>
                <span class="emoji icon-wiggle">👩‍🚀</span>
                <span class="emoji icon-wiggle">👨‍🚒</span>
                <span class="emoji icon-wiggle">👩‍🚒</span>
                <span class="emoji icon-wiggle">👮</span>
                <span class="emoji icon-wiggle">👮‍♂️</span>
                <span class="emoji icon-wiggle">👮‍♀️</span>
                <span class="emoji icon-wiggle">🕵</span>
                <span class="emoji icon-wiggle">🕵️‍♂️</span>
                <span class="emoji icon-wiggle">🕵️‍♀️</span>
                <span class="emoji icon-wiggle">💂</span>
                <span class="emoji icon-wiggle">💂‍♂️</span>
                <span class="emoji icon-wiggle">💂‍♀️</span>
                <span class="emoji icon-wiggle">👷</span>
                <span class="emoji icon-wiggle">👷‍♂️</span>
                <span class="emoji icon-wiggle">👷‍♀️</span>
                <span class="emoji icon-wiggle">🤴</span>
                <span class="emoji icon-wiggle">👸</span>
                <span class="emoji icon-wiggle">👳</span>
                <span class="emoji icon-wiggle">👳‍♂️</span>
                <span class="emoji icon-wiggle">👳‍♀️</span>
                <span class="emoji icon-wiggle">👲</span>
                <span class="emoji icon-wiggle">🧕</span>
                <span class="emoji icon-wiggle">🧔</span>
                <span class="emoji icon-wiggle">👱</span>
                <span class="emoji icon-wiggle">👱‍♂️</span>
                <span class="emoji icon-wiggle">👱‍♀️</span>
                <span class="emoji icon-wiggle">🤵</span>
                <span class="emoji icon-wiggle">👰</span>
                <span class="emoji icon-wiggle">🤰</span>
                <span class="emoji icon-wiggle">🤱</span>
                <span class="emoji icon-wiggle">👼</span>
                <span class="emoji icon-wiggle">🎅</span>
                <span class="emoji icon-wiggle">🤶</span>
                <span class="emoji icon-wiggle">🧙‍♀️</span>
                <span class="emoji icon-wiggle">🧙‍♂️</span>
                <span class="emoji icon-wiggle">🧚‍♀️</span>
                <span class="emoji icon-wiggle">🧚‍♂️</span>
                <span class="emoji icon-wiggle">🧛‍♀️</span>
                <span class="emoji icon-wiggle">🧛‍♂️</span>
                <span class="emoji icon-wiggle">🧜‍♀️</span>
                <span class="emoji icon-wiggle">🧜‍♂️</span>
                <span class="emoji icon-wiggle">🧝‍♀️</span>
                <span class="emoji icon-wiggle">🧝‍♂️</span>
                <span class="emoji icon-wiggle">🧞‍♀️</span>
                <span class="emoji icon-wiggle">🧞‍♂️</span>
                <span class="emoji icon-wiggle">🧟‍♀️</span>
                <span class="emoji icon-wiggle">🧟‍♂️</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab3">
            <div class="section-header">😺 Animals</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">😺</span>
                <span class="emoji icon-wiggle">😸</span>
                <span class="emoji icon-wiggle">😹</span>
                <span class="emoji icon-wiggle">😻</span>
                <span class="emoji icon-wiggle">😼</span>
                <span class="emoji icon-wiggle">😽</span>
                <span class="emoji icon-wiggle">🙀</span>
                <span class="emoji icon-wiggle">😿</span>
                <span class="emoji icon-wiggle">😾</span>
                <span class="emoji icon-wiggle">🙈</span>
                <span class="emoji icon-wiggle">🙉</span>
                <span class="emoji icon-wiggle">🙊</span>
                <span class="emoji icon-wiggle">💥</span>
                <span class="emoji icon-wiggle">🐵</span>
                <span class="emoji icon-wiggle">🐒</span>
                <span class="emoji icon-wiggle">🦍</span>
                <span class="emoji icon-wiggle">🐶</span>
                <span class="emoji icon-wiggle">🐕</span>
                <span class="emoji icon-wiggle">🐩</span>
                <span class="emoji icon-wiggle">🐺</span>
                <span class="emoji icon-wiggle">🦊</span>
                <span class="emoji icon-wiggle">🐱</span>
                <span class="emoji icon-wiggle">🐈</span>
                <span class="emoji icon-wiggle">🦁</span>
                <span class="emoji icon-wiggle">🐯</span>
                <span class="emoji icon-wiggle">🐅</span>
                <span class="emoji icon-wiggle">🐆</span>
                <span class="emoji icon-wiggle">🐴</span>
                <span class="emoji icon-wiggle">🐎</span>
                <span class="emoji icon-wiggle">🦄</span>
                <span class="emoji icon-wiggle">🦓</span>
                <span class="emoji icon-wiggle">🐮</span>
                <span class="emoji icon-wiggle">🐂</span>
                <span class="emoji icon-wiggle">🐃</span>
                <span class="emoji icon-wiggle">🐄</span>
                <span class="emoji icon-wiggle">🐷</span>
                <span class="emoji icon-wiggle">🐖</span>
                <span class="emoji icon-wiggle">🐗</span>
                <span class="emoji icon-wiggle">🐽</span>
                <span class="emoji icon-wiggle">🐏</span>
                <span class="emoji icon-wiggle">🐑</span>
                <span class="emoji icon-wiggle">🐐</span>
                <span class="emoji icon-wiggle">🐪</span>
                <span class="emoji icon-wiggle">🐫</span>
                <span class="emoji icon-wiggle">🦒</span>
                <span class="emoji icon-wiggle">🐘</span>
                <span class="emoji icon-wiggle">🦏</span>
                <span class="emoji icon-wiggle">🐭</span>
                <span class="emoji icon-wiggle">🐁</span>
                <span class="emoji icon-wiggle">🐀</span>
                <span class="emoji icon-wiggle">🐹</span>
                <span class="emoji icon-wiggle">🐰</span>
                <span class="emoji icon-wiggle">🐇</span>
                <span class="emoji icon-wiggle">🐿</span>
                <span class="emoji icon-wiggle">🦔</span>
                <span class="emoji icon-wiggle">🦇</span>
                <span class="emoji icon-wiggle">🐻</span>
                <span class="emoji icon-wiggle">🐨</span>
                <span class="emoji icon-wiggle">🐼</span>
                <span class="emoji icon-wiggle">🐾</span>
                <span class="emoji icon-wiggle">🦃</span>
                <span class="emoji icon-wiggle">🐔</span>
                <span class="emoji icon-wiggle">🐓</span>
                <span class="emoji icon-wiggle">🐣</span>
                <span class="emoji icon-wiggle">🐤</span>
                <span class="emoji icon-wiggle">🐥</span>
                <span class="emoji icon-wiggle">🐦</span>
                <span class="emoji icon-wiggle">🐧</span>
                <span class="emoji icon-wiggle">🕊</span>
                <span class="emoji icon-wiggle">🦅</span>
                <span class="emoji icon-wiggle">🦆</span>
                <span class="emoji icon-wiggle">🦉</span>
                <span class="emoji icon-wiggle">🐸</span>
                <span class="emoji icon-wiggle">🐊</span>
                <span class="emoji icon-wiggle">🐢</span>
                <span class="emoji icon-wiggle">🦎</span>
                <span class="emoji icon-wiggle">🐍</span>
                <span class="emoji icon-wiggle">🐲</span>
                <span class="emoji icon-wiggle">🐉</span>
                <span class="emoji icon-wiggle">🦕</span>
                <span class="emoji icon-wiggle">🦖</span>
                <span class="emoji icon-wiggle">🐳</span>
                <span class="emoji icon-wiggle">🐋</span>
                <span class="emoji icon-wiggle">🐬</span>
                <span class="emoji icon-wiggle">🐟</span>
                <span class="emoji icon-wiggle">🐠</span>
                <span class="emoji icon-wiggle">🐡</span>
                <span class="emoji icon-wiggle">🦈</span>
                <span class="emoji icon-wiggle">🐙</span>
                <span class="emoji icon-wiggle">🐚</span>
                <span class="emoji icon-wiggle">🦀</span>
                <span class="emoji icon-wiggle">🦐</span>
                <span class="emoji icon-wiggle">🦑</span>
                <span class="emoji icon-wiggle">🐌</span>
                <span class="emoji icon-wiggle">🦋</span>
                <span class="emoji icon-wiggle">🐛</span>
                <span class="emoji icon-wiggle">🐜</span>
                <span class="emoji icon-wiggle">🐝</span>
                <span class="emoji icon-wiggle">🐞</span>
                <span class="emoji icon-wiggle">🦗</span>
                <span class="emoji icon-wiggle">🕷</span>
                <span class="emoji icon-wiggle">🕸</span>
                <span class="emoji icon-wiggle">🦂</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab4">
            <div class="section-header">🌦️ Weather & Sky</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">☀️</span> <span class="emoji icon-wiggle">🌤️</span> <span class="emoji icon-wiggle">⛅</span> <span class="emoji icon-wiggle">🌥️</span>
                <span class="emoji icon-wiggle">☁️</span> <span class="emoji icon-wiggle">🌧️</span> <span class="emoji icon-wiggle">⛈️</span> <span class="emoji icon-wiggle">🌩️</span>
                <span class="emoji icon-wiggle">🌨️</span> <span class="emoji icon-wiggle">❄️</span> <span class="emoji icon-wiggle">🌪️</span> <span class="emoji icon-wiggle">🌈</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab5">
            <div class="section-header">💐 Plants</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">💐</span>
                <span class="emoji icon-wiggle">🌸</span>
                <span class="emoji icon-wiggle">💮</span>
                <span class="emoji icon-wiggle">🏵</span>
                <span class="emoji icon-wiggle">🌹</span>
                <span class="emoji icon-wiggle">🥀</span>
                <span class="emoji icon-wiggle">🌺</span>
                <span class="emoji icon-wiggle">🌻</span>
                <span class="emoji icon-wiggle">🌼</span>
                <span class="emoji icon-wiggle">🌷</span>
                <span class="emoji icon-wiggle">🌱</span>
                <span class="emoji icon-wiggle">🌲</span>
                <span class="emoji icon-wiggle">🌳</span>
                <span class="emoji icon-wiggle">🌴</span>
                <span class="emoji icon-wiggle">🌵</span>
                <span class="emoji icon-wiggle">🌾</span>
                <span class="emoji icon-wiggle">🌿</span>
                <span class="emoji icon-wiggle">☘</span>
                <span class="emoji icon-wiggle">🍀</span>
                <span class="emoji icon-wiggle">🍁</span>
                <span class="emoji icon-wiggle">🍂</span>
                <span class="emoji icon-wiggle">🍃</span>
                <span class="emoji icon-wiggle">🍄</span>
                <span class="emoji icon-wiggle">🌰</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab6">
            <div class="section-header">🌍 Nature</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">🌍</span>
                <span class="emoji icon-wiggle">🌎</span>
                <span class="emoji icon-wiggle">🌏</span>
                <span class="emoji icon-wiggle">🌐</span>
                <span class="emoji icon-wiggle">🌑</span>
                <span class="emoji icon-wiggle">🌒</span>
                <span class="emoji icon-wiggle">🌓</span>
                <span class="emoji icon-wiggle">🌔</span>
                <span class="emoji icon-wiggle">🌕</span>
                <span class="emoji icon-wiggle">🌖</span>
                <span class="emoji icon-wiggle">🌗</span>
                <span class="emoji icon-wiggle">🌘</span>
                <span class="emoji icon-wiggle">🌙</span>
                <span class="emoji icon-wiggle">🌚</span>
                <span class="emoji icon-wiggle">🌛</span>
                <span class="emoji icon-wiggle">🌜</span>
                <span class="emoji icon-wiggle">☀</span>
                <span class="emoji icon-wiggle">🌝</span>
                <span class="emoji icon-wiggle">🌞</span>
                <span class="emoji icon-wiggle">⭐</span>
                <span class="emoji icon-wiggle">🌟</span>
                <span class="emoji icon-wiggle">🌠</span>
                <span class="emoji icon-wiggle">☁</span>
                <span class="emoji icon-wiggle">⛅</span>
                <span class="emoji icon-wiggle">⛈</span>
                <span class="emoji icon-wiggle">🌤</span>
                <span class="emoji icon-wiggle">🌥</span>
                <span class="emoji icon-wiggle">🌦</span>
                <span class="emoji icon-wiggle">🌧</span>
                <span class="emoji icon-wiggle">🌨</span>
                <span class="emoji icon-wiggle">🌩</span>
                <span class="emoji icon-wiggle">🌪</span>
                <span class="emoji icon-wiggle">🌫</span>
                <span class="emoji icon-wiggle">🌬</span>
                <span class="emoji icon-wiggle">🌈</span>
                <span class="emoji icon-wiggle">☔</span>
                <span class="emoji icon-wiggle">⚡</span>
                <span class="emoji icon-wiggle">❄</span>
                <span class="emoji icon-wiggle">☃</span>
                <span class="emoji icon-wiggle">⛄</span>
                <span class="emoji icon-wiggle">☄</span>
                <span class="emoji icon-wiggle">🔥</span>
                <span class="emoji icon-wiggle">💧</span>
                <span class="emoji icon-wiggle">🌊</span>
                <span class="emoji icon-wiggle">🎄</span>
                <span class="emoji icon-wiggle">✨</span>
                <span class="emoji icon-wiggle">🎋</span>
                <span class="emoji icon-wiggle">🎍</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab7"><!-- Food & Drinks -->
            <div class="section-header">🍎 Food</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">🍇</span>
                <span class="emoji icon-wiggle">🍈</span>
                <span class="emoji icon-wiggle">🍉</span>
                <span class="emoji icon-wiggle">🍊</span>
                <span class="emoji icon-wiggle">🍋</span>
                <span class="emoji icon-wiggle">🍌</span>
                <span class="emoji icon-wiggle">🍍</span>
                <span class="emoji icon-wiggle">🍎</span>
                <span class="emoji icon-wiggle">🍏</span>
                <span class="emoji icon-wiggle">🍐</span>
                <span class="emoji icon-wiggle">🍑</span>
                <span class="emoji icon-wiggle">🍒</span>
                <span class="emoji icon-wiggle">🍓</span>
                <span class="emoji icon-wiggle">🥝</span>
                <span class="emoji icon-wiggle">🍅</span>
                <span class="emoji icon-wiggle">🥥</span>
                <span class="emoji icon-wiggle">🥑</span>
                <span class="emoji icon-wiggle">🍆</span>
                <span class="emoji icon-wiggle">🥔</span>
                <span class="emoji icon-wiggle">🥕</span>
                <span class="emoji icon-wiggle">🌽</span>
                <span class="emoji icon-wiggle">🌶</span>
                <span class="emoji icon-wiggle">🥒</span>
                <span class="emoji icon-wiggle">🥦</span>
                <span class="emoji icon-wiggle">🥜</span>
                <span class="emoji icon-wiggle">🍞</span>
                <span class="emoji icon-wiggle">🥐</span>
                <span class="emoji icon-wiggle">🥖</span>
                <span class="emoji icon-wiggle">🥨</span>
                <span class="emoji icon-wiggle">🥞</span>
                <span class="emoji icon-wiggle">🧀</span>
                <span class="emoji icon-wiggle">🍖</span>
                <span class="emoji icon-wiggle">🍗</span>
                <span class="emoji icon-wiggle">🥩</span>
                <span class="emoji icon-wiggle">🥓</span>
                <span class="emoji icon-wiggle">🍔</span>
                <span class="emoji icon-wiggle">🍟</span>
                <span class="emoji icon-wiggle">🍕</span>
                <span class="emoji icon-wiggle">🌭</span>
                <span class="emoji icon-wiggle">🥪</span>
                <span class="emoji icon-wiggle">🌮</span>
                <span class="emoji icon-wiggle">🌯</span>
                <span class="emoji icon-wiggle">🍳</span>
                <span class="emoji icon-wiggle">🍲</span>
                <span class="emoji icon-wiggle">🥣</span>
                <span class="emoji icon-wiggle">🥗</span>
                <span class="emoji icon-wiggle">🍿</span>
                <span class="emoji icon-wiggle">🥫</span>
                <span class="emoji icon-wiggle">🍱</span>
                <span class="emoji icon-wiggle">🍘</span>
                <span class="emoji icon-wiggle">🍙</span>
                <span class="emoji icon-wiggle">🍚</span>
                <span class="emoji icon-wiggle">🍛</span>
                <span class="emoji icon-wiggle">🍜</span>
                <span class="emoji icon-wiggle">🍝</span>
                <span class="emoji icon-wiggle">🍠</span>
                <span class="emoji icon-wiggle">🍢</span>
                <span class="emoji icon-wiggle">🍣</span>
                <span class="emoji icon-wiggle">🍤</span>
                <span class="emoji icon-wiggle">🍥</span>
                <span class="emoji icon-wiggle">🍡</span>
                <span class="emoji icon-wiggle">🥟</span>
                <span class="emoji icon-wiggle">🥠</span>
                <span class="emoji icon-wiggle">🥡</span>
                <span class="emoji icon-wiggle">🍦</span>
                <span class="emoji icon-wiggle">🍧</span>
                <span class="emoji icon-wiggle">🍨</span>
                <span class="emoji icon-wiggle">🍩</span>
                <span class="emoji icon-wiggle">🍪</span>
                <span class="emoji icon-wiggle">🎂</span>
                <span class="emoji icon-wiggle">🍰</span>
                <span class="emoji icon-wiggle">🥧</span>
                <span class="emoji icon-wiggle">🍫</span>
                <span class="emoji icon-wiggle">🍬</span>
                <span class="emoji icon-wiggle">🍭</span>
                <span class="emoji icon-wiggle">🍮</span>
                <span class="emoji icon-wiggle">🍯</span>
                <span class="emoji icon-wiggle">🍼</span>
                <span class="emoji icon-wiggle">🥛</span>
                <span class="emoji icon-wiggle">☕</span>
                <span class="emoji icon-wiggle">🍵</span>
                <span class="emoji icon-wiggle">🍶</span>
                <span class="emoji icon-wiggle">🍾</span>
                <span class="emoji icon-wiggle">🍷</span>
                <span class="emoji icon-wiggle">🍸</span>
                <span class="emoji icon-wiggle">🍹</span>
                <span class="emoji icon-wiggle">🍺</span>
                <span class="emoji icon-wiggle">🍻</span>
                <span class="emoji icon-wiggle">🥂</span>
                <span class="emoji icon-wiggle">🥃</span>
                <span class="emoji icon-wiggle">🥤</span>
                <span class="emoji icon-wiggle">🥢</span>
                <span class="emoji icon-wiggle">🍽</span>
                <span class="emoji icon-wiggle">🍴</span>
                <span class="emoji icon-wiggle">🥄</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab8">
            <div class="section-header">🏇 Activities</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">🏇</span>
                <span class="emoji icon-wiggle">⛷</span>
                <span class="emoji icon-wiggle">🏂</span>
                <span class="emoji icon-wiggle">🧗‍♀️</span>
                <span class="emoji icon-wiggle">🧗‍♂️</span>
                <span class="emoji icon-wiggle">🧘‍♀️</span>
                <span class="emoji icon-wiggle">🧘‍♂️</span>
                <span class="emoji icon-wiggle">🏌</span>
                <span class="emoji icon-wiggle">🏌️‍♀️</span>
                <span class="emoji icon-wiggle">🏄</span>
                <span class="emoji icon-wiggle">🏄‍♀️</span>
                <span class="emoji icon-wiggle">🚣</span>
                <span class="emoji icon-wiggle">🏊</span>
                <span class="emoji icon-wiggle">🏊‍♀️</span>
                <span class="emoji icon-wiggle">⛹</span>
                <span class="emoji icon-wiggle">⛹️‍♀️</span>
                <span class="emoji icon-wiggle">🏋</span>
                <span class="emoji icon-wiggle">🏋️‍♀️</span>
                <span class="emoji icon-wiggle">🚴</span>
                <span class="emoji icon-wiggle">🚴‍♀️</span>
                <span class="emoji icon-wiggle">🚵</span>
                <span class="emoji icon-wiggle">🚵‍♀️</span>
                <span class="emoji icon-wiggle">🤸</span>
                <span class="emoji icon-wiggle">🤸‍♂️</span>
                <span class="emoji icon-wiggle">🤼‍♂️</span>
                <span class="emoji icon-wiggle">🤼‍♀️</span>
                <span class="emoji icon-wiggle">🤽</span>
                <span class="emoji icon-wiggle">🤽‍♀️</span>
                <span class="emoji icon-wiggle">🤾‍♂️</span>
                <span class="emoji icon-wiggle">🤾‍♀️</span>
                <span class="emoji icon-wiggle">🤹</span>
                <span class="emoji icon-wiggle">🤹‍♀️</span>
                <span class="emoji icon-wiggle">🎪</span>
                <span class="emoji icon-wiggle">🎗</span>
                <span class="emoji icon-wiggle">🎟</span>
                <span class="emoji icon-wiggle">🎫</span>
                <span class="emoji icon-wiggle">🎖</span>
                <span class="emoji icon-wiggle">🏆</span>
                <span class="emoji icon-wiggle">🏅</span>
                <span class="emoji icon-wiggle">🥇</span>
                <span class="emoji icon-wiggle">🥈</span>
                <span class="emoji icon-wiggle">🥉</span>
                <span class="emoji icon-wiggle">⚽</span>
                <span class="emoji icon-wiggle">⚾</span>
                <span class="emoji icon-wiggle">🏀</span>
                <span class="emoji icon-wiggle">🏐</span>
                <span class="emoji icon-wiggle">🏈</span>
                <span class="emoji icon-wiggle">🏉</span>
                <span class="emoji icon-wiggle">🎾</span>
                <span class="emoji icon-wiggle">🎳</span>
                <span class="emoji icon-wiggle">🏏</span>
                <span class="emoji icon-wiggle">🏑</span>
                <span class="emoji icon-wiggle">🏒</span>
                <span class="emoji icon-wiggle">🏓</span>
                <span class="emoji icon-wiggle">🏸</span>
                <span class="emoji icon-wiggle">🥊</span>
                <span class="emoji icon-wiggle">🥋</span>
                <span class="emoji icon-wiggle">⛳</span>
                <span class="emoji icon-wiggle">⛸</span>
                <span class="emoji icon-wiggle">🎣</span>
                <span class="emoji icon-wiggle">🎽</span>
                <span class="emoji icon-wiggle">🎿</span>
                <span class="emoji icon-wiggle">🛷</span>
                <span class="emoji icon-wiggle">🥌</span>
                <span class="emoji icon-wiggle">🎯</span>
                <span class="emoji icon-wiggle">🎱</span>
                <span class="emoji icon-wiggle">🎮</span>
                <span class="emoji icon-wiggle">🎰</span>
                <span class="emoji icon-wiggle">🎲</span>
                <span class="emoji icon-wiggle">🎭</span>
                <span class="emoji icon-wiggle">🎨</span>
                <span class="emoji icon-wiggle">🎼</span>
                <span class="emoji icon-wiggle">🎤</span>
                <span class="emoji icon-wiggle">🎧</span>
                <span class="emoji icon-wiggle">🎷</span>
                <span class="emoji icon-wiggle">🎸</span>
                <span class="emoji icon-wiggle">🎹</span>
                <span class="emoji icon-wiggle">🎺</span>
                <span class="emoji icon-wiggle">🎻</span>
                <span class="emoji icon-wiggle">🥁</span>
                <span class="emoji icon-wiggle">🎬</span>
                <span class="emoji icon-wiggle">🏹</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab9"><!-- Travel & Places -->
            <div class="section-header">🚗 Travel</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">🏖</span>
                <span class="emoji icon-wiggle">🏎</span>
                <span class="emoji icon-wiggle">🏍</span>
                <span class="emoji icon-wiggle">🗾</span>
                <span class="emoji icon-wiggle">🏔</span>
                <span class="emoji icon-wiggle">⛰</span>
                <span class="emoji icon-wiggle">🌋</span>
                <span class="emoji icon-wiggle">🗻</span>
                <span class="emoji icon-wiggle">🏕</span>
                <span class="emoji icon-wiggle">🏜</span>
                <span class="emoji icon-wiggle">🏝</span>
                <span class="emoji icon-wiggle">🏞</span>
                <span class="emoji icon-wiggle">🏟</span>
                <span class="emoji icon-wiggle">🏛</span>
                <span class="emoji icon-wiggle">🏗</span>
                <span class="emoji icon-wiggle">🏘</span>
                <span class="emoji icon-wiggle">🏚</span>
                <span class="emoji icon-wiggle">🏠</span>
                <span class="emoji icon-wiggle">🏡</span>
                <span class="emoji icon-wiggle">🏢</span>
                <span class="emoji icon-wiggle">🏣</span>
                <span class="emoji icon-wiggle">🏤</span>
                <span class="emoji icon-wiggle">🏥</span>
                <span class="emoji icon-wiggle">🏦</span>
                <span class="emoji icon-wiggle">🏨</span>
                <span class="emoji icon-wiggle">🏩</span>
                <span class="emoji icon-wiggle">🏪</span>
                <span class="emoji icon-wiggle">🏫</span>
                <span class="emoji icon-wiggle">🏬</span>
                <span class="emoji icon-wiggle">🏭</span>
                <span class="emoji icon-wiggle">🏯</span>
                <span class="emoji icon-wiggle">🏰</span>
                <span class="emoji icon-wiggle">💒</span>
                <span class="emoji icon-wiggle">🗼</span>
                <span class="emoji icon-wiggle">🗽</span>
                <span class="emoji icon-wiggle">⛪</span>
                <span class="emoji icon-wiggle">🕌</span>
                <span class="emoji icon-wiggle">🕍</span>
                <span class="emoji icon-wiggle">⛩</span>
                <span class="emoji icon-wiggle">🕋</span>
                <span class="emoji icon-wiggle">⛲</span>
                <span class="emoji icon-wiggle">⛺</span>
                <span class="emoji icon-wiggle">🌁</span>
                <span class="emoji icon-wiggle">🌃</span>
                <span class="emoji icon-wiggle">🏙</span>
                <span class="emoji icon-wiggle">🌄</span>
                <span class="emoji icon-wiggle">🌅</span>
                <span class="emoji icon-wiggle">🌆</span>
                <span class="emoji icon-wiggle">🌇</span>
                <span class="emoji icon-wiggle">🌉</span>
                <span class="emoji icon-wiggle">🌌</span>
                <span class="emoji icon-wiggle">🎠</span>
                <span class="emoji icon-wiggle">🎡</span>
                <span class="emoji icon-wiggle">🎢</span>
                <span class="emoji icon-wiggle">🚂</span>
                <span class="emoji icon-wiggle">🚃</span>
                <span class="emoji icon-wiggle">🚄</span>
                <span class="emoji icon-wiggle">🚅</span>
                <span class="emoji icon-wiggle">🚆</span>
                <span class="emoji icon-wiggle">🚇</span>
                <span class="emoji icon-wiggle">🚈</span>
                <span class="emoji icon-wiggle">🚉</span>
                <span class="emoji icon-wiggle">🚊</span>
                <span class="emoji icon-wiggle">🚝</span>
                <span class="emoji icon-wiggle">🚞</span>
                <span class="emoji icon-wiggle">🚋</span>
                <span class="emoji icon-wiggle">🚌</span>
                <span class="emoji icon-wiggle">🚍</span>
                <span class="emoji icon-wiggle">🚎</span>
                <span class="emoji icon-wiggle">🚐</span>
                <span class="emoji icon-wiggle">🚑</span>
                <span class="emoji icon-wiggle">🚒</span>
                <span class="emoji icon-wiggle">🚓</span>
                <span class="emoji icon-wiggle">🚔</span>
                <span class="emoji icon-wiggle">🚕</span>
                <span class="emoji icon-wiggle">🚖</span>
                <span class="emoji icon-wiggle">🚗</span>
                <span class="emoji icon-wiggle">🚘</span>
                <span class="emoji icon-wiggle">🚙</span>
                <span class="emoji icon-wiggle">🚚</span>
                <span class="emoji icon-wiggle">🚛</span>
                <span class="emoji icon-wiggle">🚜</span>
                <span class="emoji icon-wiggle">🚲</span>
                <span class="emoji icon-wiggle">🛴</span>
                <span class="emoji icon-wiggle">🛵</span>
                <span class="emoji icon-wiggle">🚏</span>
                <span class="emoji icon-wiggle">🛤</span>
                <span class="emoji icon-wiggle">⛽</span>
                <span class="emoji icon-wiggle">🚨</span>
                <span class="emoji icon-wiggle">⛵</span>
                <span class="emoji icon-wiggle">🚤</span>
                <span class="emoji icon-wiggle">🛳</span>
                <span class="emoji icon-wiggle">⛴</span>
                <span class="emoji icon-wiggle">🛥</span>
                <span class="emoji icon-wiggle">🚢</span>
                <span class="emoji icon-wiggle">✈</span>
                <span class="emoji icon-wiggle">🛩</span>
                <span class="emoji icon-wiggle">🛫</span>
                <span class="emoji icon-wiggle">🛬</span>
                <span class="emoji icon-wiggle">💺</span>
                <span class="emoji icon-wiggle">🚁</span>
                <span class="emoji icon-wiggle">🚟</span>
                <span class="emoji icon-wiggle">🚠</span>
                <span class="emoji icon-wiggle">🚡</span>
                <span class="emoji icon-wiggle">🛰</span>
                <span class="emoji icon-wiggle">🚀</span>
                <span class="emoji icon-wiggle">🛸</span>
                <span class="emoji icon-wiggle">⛱</span>
                <span class="emoji icon-wiggle">🎆</span>
                <span class="emoji icon-wiggle">🎇</span>
                <span class="emoji icon-wiggle">🎑</span>
                <span class="emoji icon-wiggle">🗿</span>
                <span class="emoji icon-wiggle">🛂</span>
                <span class="emoji icon-wiggle">🛃</span>
                <span class="emoji icon-wiggle">🛄</span>
                <span class="emoji icon-wiggle">🛅</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab10"><!-- Objects -->
            <div class="section-header">📱 Objects</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">💎</span>
                <span class="emoji icon-wiggle">👓</span>
                <span class="emoji icon-wiggle">🕶</span>
                <span class="emoji icon-wiggle">👔</span>
                <span class="emoji icon-wiggle">👕</span>
                <span class="emoji icon-wiggle">👖</span>
                <span class="emoji icon-wiggle">🧣</span>
                <span class="emoji icon-wiggle">🧤</span>
                <span class="emoji icon-wiggle">🧥</span>
                <span class="emoji icon-wiggle">🧦</span>
                <span class="emoji icon-wiggle">👗</span>
                <span class="emoji icon-wiggle">👘</span>
                <span class="emoji icon-wiggle">👙</span>
                <span class="emoji icon-wiggle">👚</span>
                <span class="emoji icon-wiggle">👛</span>
                <span class="emoji icon-wiggle">👜</span>
                <span class="emoji icon-wiggle">👝</span>
                <span class="emoji icon-wiggle">🎒</span>
                <span class="emoji icon-wiggle">👞</span>
                <span class="emoji icon-wiggle">👟</span>
                <span class="emoji icon-wiggle">👠</span>
                <span class="emoji icon-wiggle">👡</span>
                <span class="emoji icon-wiggle">👢</span>
                <span class="emoji icon-wiggle">👑</span>
                <span class="emoji icon-wiggle">👒</span>
                <span class="emoji icon-wiggle">🎩</span>
                <span class="emoji icon-wiggle">🎓</span>
                <span class="emoji icon-wiggle">🧢</span>
                <span class="emoji icon-wiggle">⛑</span>
                <span class="emoji icon-wiggle">💄</span>
                <span class="emoji icon-wiggle">💍</span>
                <span class="emoji icon-wiggle">🌂</span>
                <span class="emoji icon-wiggle">☂</span>
                <span class="emoji icon-wiggle">💼</span>
                <span class="emoji icon-wiggle">☠</span>
                <span class="emoji icon-wiggle">🛀</span>
                <span class="emoji icon-wiggle">🛌</span>
                <span class="emoji icon-wiggle">💌</span>
                <span class="emoji icon-wiggle">💣</span>
                <span class="emoji icon-wiggle">🚥</span>
                <span class="emoji icon-wiggle">🚦</span>
                <span class="emoji icon-wiggle">🚧</span>
                <span class="emoji icon-wiggle">⚓</span>
                <span class="emoji icon-wiggle">🕳</span>
                <span class="emoji icon-wiggle">🛍</span>
                <span class="emoji icon-wiggle">📿</span>
                <span class="emoji icon-wiggle">🔪</span>
                <span class="emoji icon-wiggle">🏺</span>
                <span class="emoji icon-wiggle">🗺</span>
                <span class="emoji icon-wiggle">💈</span>
                <span class="emoji icon-wiggle">🛢</span>
                <span class="emoji icon-wiggle">🛎</span>
                <span class="emoji icon-wiggle">⌛</span>
                <span class="emoji icon-wiggle">⏳</span>
                <span class="emoji icon-wiggle">⌚</span>
                <span class="emoji icon-wiggle">⏰</span>
                <span class="emoji icon-wiggle">⏱</span>
                <span class="emoji icon-wiggle">⏲</span>
                <span class="emoji icon-wiggle">🕰</span>
                <span class="emoji icon-wiggle">🌡</span>
                <span class="emoji icon-wiggle">🎈</span>
                <span class="emoji icon-wiggle">🎉</span>
                <span class="emoji icon-wiggle">🎊</span>
                <span class="emoji icon-wiggle">🎎</span>
                <span class="emoji icon-wiggle">🎏</span>
                <span class="emoji icon-wiggle">🎐</span>
                <span class="emoji icon-wiggle">🎀</span>
                <span class="emoji icon-wiggle">🎁</span>
                <span class="emoji icon-wiggle">🔮</span>
                <span class="emoji icon-wiggle">🕹</span>
                <span class="emoji icon-wiggle">🖼</span>
                <span class="emoji icon-wiggle">🎙</span>
                <span class="emoji icon-wiggle">🎚</span>
                <span class="emoji icon-wiggle">🎛</span>
                <span class="emoji icon-wiggle">📻</span>
                <span class="emoji icon-wiggle">📱</span>
                <span class="emoji icon-wiggle">📲</span>
                <span class="emoji icon-wiggle">☎</span>
                <span class="emoji icon-wiggle">📞</span>
                <span class="emoji icon-wiggle">📟</span>
                <span class="emoji icon-wiggle">📠</span>
                <span class="emoji icon-wiggle">🔋</span>
                <span class="emoji icon-wiggle">🔌</span>
                <span class="emoji icon-wiggle">💻</span>
                <span class="emoji icon-wiggle">🖥</span>
                <span class="emoji icon-wiggle">🖨</span>
                <span class="emoji icon-wiggle">⌨</span>
                <span class="emoji icon-wiggle">🖱</span>
                <span class="emoji icon-wiggle">🖲</span>
                <span class="emoji icon-wiggle">💽</span>
                <span class="emoji icon-wiggle">💾</span>
                <span class="emoji icon-wiggle">💿</span>
                <span class="emoji icon-wiggle">📀</span>
                <span class="emoji icon-wiggle">🎥</span>
                <span class="emoji icon-wiggle">🎞</span>
                <span class="emoji icon-wiggle">📽</span>
                <span class="emoji icon-wiggle">📺</span>
                <span class="emoji icon-wiggle">📷</span>
                <span class="emoji icon-wiggle">📸</span>
                <span class="emoji icon-wiggle">📹</span>
                <span class="emoji icon-wiggle">📼</span>
                <span class="emoji icon-wiggle">🔍</span>
                <span class="emoji icon-wiggle">🔎</span>
                <span class="emoji icon-wiggle">🕯</span>
                <span class="emoji icon-wiggle">💡</span>
                <span class="emoji icon-wiggle">🔦</span>
                <span class="emoji icon-wiggle">🏮</span>
                <span class="emoji icon-wiggle">📔</span>
                <span class="emoji icon-wiggle">📕</span>
                <span class="emoji icon-wiggle">📖</span>
                <span class="emoji icon-wiggle">📗</span>
                <span class="emoji icon-wiggle">📘</span>
                <span class="emoji icon-wiggle">📙</span>
                <span class="emoji icon-wiggle">📚</span>
                <span class="emoji icon-wiggle">📓</span>
                <span class="emoji icon-wiggle">📃</span>
                <span class="emoji icon-wiggle">📜</span>
                <span class="emoji icon-wiggle">📄</span>
                <span class="emoji icon-wiggle">📰</span>
                <span class="emoji icon-wiggle">🗞</span>
                <span class="emoji icon-wiggle">📑</span>
                <span class="emoji icon-wiggle">🔖</span>
                <span class="emoji icon-wiggle">🏷</span>
                <span class="emoji icon-wiggle">💰</span>
                <span class="emoji icon-wiggle">💸</span>
                <span class="emoji icon-wiggle">💳</span>
                <span class="emoji icon-wiggle">✉</span>
                <span class="emoji icon-wiggle">📧</span>
                <span class="emoji icon-wiggle">📨</span>
                <span class="emoji icon-wiggle">📩</span>
                <span class="emoji icon-wiggle">📤</span>
                <span class="emoji icon-wiggle">📥</span>
                <span class="emoji icon-wiggle">📦</span>
                <span class="emoji icon-wiggle">📫</span>
                <span class="emoji icon-wiggle">📪</span>
                <span class="emoji icon-wiggle">📬</span>
                <span class="emoji icon-wiggle">📭</span>
                <span class="emoji icon-wiggle">📮</span>
                <span class="emoji icon-wiggle">🗳</span>
                <span class="emoji icon-wiggle">✏</span>
                <span class="emoji icon-wiggle">✎</span>
                <span class="emoji icon-wiggle">🖉</span>
                <span class="emoji icon-wiggle">✒</span>
                <span class="emoji icon-wiggle">🖋</span>
                <span class="emoji icon-wiggle">🖊</span>
                <span class="emoji icon-wiggle">🖌</span>
                <span class="emoji icon-wiggle">🖍</span>
                <span class="emoji icon-wiggle">📝</span>
                <span class="emoji icon-wiggle">📁</span>
                <span class="emoji icon-wiggle">📂</span>
                <span class="emoji icon-wiggle">🗂</span>
                <span class="emoji icon-wiggle">📅</span>
                <span class="emoji icon-wiggle">📆</span>
                <span class="emoji icon-wiggle">🗒</span>
                <span class="emoji icon-wiggle">🗓</span>
                <span class="emoji icon-wiggle">📇</span>
                <span class="emoji icon-wiggle">📈</span>
                <span class="emoji icon-wiggle">📉</span>
                <span class="emoji icon-wiggle">📊</span>
                <span class="emoji icon-wiggle">📋</span>
                <span class="emoji icon-wiggle">📌</span>
                <span class="emoji icon-wiggle">📍</span>
                <span class="emoji icon-wiggle">📎</span>
                <span class="emoji icon-wiggle">🖇</span>
                <span class="emoji icon-wiggle">📏</span>
                <span class="emoji icon-wiggle">📐</span>
                <span class="emoji icon-wiggle">✂</span>
                <span class="emoji icon-wiggle">🗃</span>
                <span class="emoji icon-wiggle">🗄</span>
                <span class="emoji icon-wiggle">🗑</span>
                <span class="emoji icon-wiggle">🔒</span>
                <span class="emoji icon-wiggle">🔓</span>
                <span class="emoji icon-wiggle">🔏</span>
                <span class="emoji icon-wiggle">🔐</span>
                <span class="emoji icon-wiggle">🔑</span>
                <span class="emoji icon-wiggle">🗝</span>
                <span class="emoji icon-wiggle">🔨</span>
                <span class="emoji icon-wiggle">⛏</span>
                <span class="emoji icon-wiggle">⚒</span>
                <span class="emoji icon-wiggle">🛠</span>
                <span class="emoji icon-wiggle">🗡</span>
                <span class="emoji icon-wiggle">⚔</span>
                <span class="emoji icon-wiggle">🔫</span>
                <span class="emoji icon-wiggle">🛡</span>
                <span class="emoji icon-wiggle">🔧</span>
                <span class="emoji icon-wiggle">🔩</span>
                <span class="emoji icon-wiggle">⚙</span>
                <span class="emoji icon-wiggle">🗜</span>
                <span class="emoji icon-wiggle">⚖</span>
                <span class="emoji icon-wiggle">🔗</span>
                <span class="emoji icon-wiggle">⛓</span>
                <span class="emoji icon-wiggle">⚗</span>
                <span class="emoji icon-wiggle">🔬</span>
                <span class="emoji icon-wiggle">🔭</span>
                <span class="emoji icon-wiggle">📡</span>
                <span class="emoji icon-wiggle">💉</span>
                <span class="emoji icon-wiggle">💊</span>
                <span class="emoji icon-wiggle">🚪</span>
                <span class="emoji icon-wiggle">🛏</span>
                <span class="emoji icon-wiggle">🛋</span>
                <span class="emoji icon-wiggle">🚽</span>
                <span class="emoji icon-wiggle">🚿</span>
                <span class="emoji icon-wiggle">🛁</span>
                <span class="emoji icon-wiggle">🚬</span>
                <span class="emoji icon-wiggle">⚰</span>
                <span class="emoji icon-wiggle">⚱</span>
                <span class="emoji icon-wiggle">💘</span>
                <span class="emoji icon-wiggle">❤</span>
                <span class="emoji icon-wiggle">💓</span>
                <span class="emoji icon-wiggle">💔</span>
                <span class="emoji icon-wiggle">💕</span>
                <span class="emoji icon-wiggle">💖</span>
                <span class="emoji icon-wiggle">💗</span>
                <span class="emoji icon-wiggle">💙</span>
                <span class="emoji icon-wiggle">💚</span>
                <span class="emoji icon-wiggle">💛</span>
                <span class="emoji icon-wiggle">🧡</span>
                <span class="emoji icon-wiggle">💜</span>
                <span class="emoji icon-wiggle">🖤</span>
                <span class="emoji icon-wiggle">💝</span>
                <span class="emoji icon-wiggle">💞</span>
                <span class="emoji icon-wiggle">💟</span>
                <span class="emoji icon-wiggle">❣</span>
                <span class="emoji icon-wiggle">💦</span>
                <span class="emoji icon-wiggle">💨</span>
                <span class="emoji icon-wiggle">💫</span>
                <span class="emoji icon-wiggle">🏁</span>
                <span class="emoji icon-wiggle">🚩</span>
                <span class="emoji icon-wiggle">🎌</span>
                <span class="emoji icon-wiggle">🏴</span>
                <span class="emoji icon-wiggle">🏳</span>
                <span class="emoji icon-wiggle">🏳️‍🌈</span>
                <span class="emoji icon-wiggle">🏴‍☠️</span>
            </div>
        </div>
        <div class="emoji-tab collapse" id="tab11">
            <div class="section-header">🔣 Symbols</div>
            <div class="emoji-row">
                <span class="emoji icon-wiggle">👍</span>
                <span class="emoji icon-wiggle">👎</span>
                <span class="emoji icon-wiggle">💪</span>
                <span class="emoji icon-wiggle">🤳</span>
                <span class="emoji icon-wiggle">👈</span>
                <span class="emoji icon-wiggle">👉</span>
                <span class="emoji icon-wiggle">☝</span>
                <span class="emoji icon-wiggle">👆</span>
                <span class="emoji icon-wiggle">🖕</span>
                <span class="emoji icon-wiggle">👇</span>
                <span class="emoji icon-wiggle">✌</span>
                <span class="emoji icon-wiggle">🤞</span>
                <span class="emoji icon-wiggle">🖖</span>
                <span class="emoji icon-wiggle">🤘</span>
                <span class="emoji icon-wiggle">🖐</span>
                <span class="emoji icon-wiggle">✋</span>
                <span class="emoji icon-wiggle">👌</span>
                <span class="emoji icon-wiggle">✊</span>
                <span class="emoji icon-wiggle">👊</span>
                <span class="emoji icon-wiggle">🤛</span>
                <span class="emoji icon-wiggle">🤜</span>
                <span class="emoji icon-wiggle">🤚</span>
                <span class="emoji icon-wiggle">👋</span>
                <span class="emoji icon-wiggle">🤟</span>
                <span class="emoji icon-wiggle">✍</span>
                <span class="emoji icon-wiggle">👏</span>
                <span class="emoji icon-wiggle">👐</span>
                <span class="emoji icon-wiggle">🙌</span>
                <span class="emoji icon-wiggle">🤲</span>
                <span class="emoji icon-wiggle">🙏</span>
                <span class="emoji icon-wiggle">🤝</span>
                <span class="emoji icon-wiggle">💅</span>
                <span class="emoji icon-wiggle">👂</span>
                <span class="emoji icon-wiggle">👃</span>
                <span class="emoji icon-wiggle">⚕️</span>
                <span class="emoji icon-wiggle">👣</span>
                <span class="emoji icon-wiggle">👀</span>
                <span class="emoji icon-wiggle">👁</span>
                <span class="emoji icon-wiggle">🧠</span>
                <span class="emoji icon-wiggle">👅</span>
                <span class="emoji icon-wiggle">👄</span>
                <span class="emoji icon-wiggle">💋</span>
                <span class="emoji icon-wiggle">👁️‍🗨️</span>
                <span class="emoji icon-wiggle">💤</span>
                <span class="emoji icon-wiggle">💢</span>
                <span class="emoji icon-wiggle">💬</span>
                <span class="emoji icon-wiggle">🗯</span>
                <span class="emoji icon-wiggle">💭</span>
                <span class="emoji icon-wiggle">♨</span>
                <span class="emoji icon-wiggle">🛑</span>
                <span class="emoji icon-wiggle">🕛</span>
                <span class="emoji icon-wiggle">🕧</span>
                <span class="emoji icon-wiggle">🕐</span>
                <span class="emoji icon-wiggle">🕜</span>
                <span class="emoji icon-wiggle">🕑</span>
                <span class="emoji icon-wiggle">🕝</span>
                <span class="emoji icon-wiggle">🕒</span>
                <span class="emoji icon-wiggle">🕞</span>
                <span class="emoji icon-wiggle">🕓</span>
                <span class="emoji icon-wiggle">🕟</span>
                <span class="emoji icon-wiggle">🕔</span>
                <span class="emoji icon-wiggle">🕠</span>
                <span class="emoji icon-wiggle">🕕</span>
                <span class="emoji icon-wiggle">🕡</span>
                <span class="emoji icon-wiggle">🕖</span>
                <span class="emoji icon-wiggle">🕢</span>
                <span class="emoji icon-wiggle">🕗</span>
                <span class="emoji icon-wiggle">🕣</span>
                <span class="emoji icon-wiggle">🕘</span>
                <span class="emoji icon-wiggle">🕤</span>
                <span class="emoji icon-wiggle">🕙</span>
                <span class="emoji icon-wiggle">🕥</span>
                <span class="emoji icon-wiggle">🕚</span>
                <span class="emoji icon-wiggle">🕦</span>
                <span class="emoji icon-wiggle">🌀</span>
                <span class="emoji icon-wiggle">🃏</span>
                <span class="emoji icon-wiggle">🀄</span>
                <span class="emoji icon-wiggle">🎴</span>
                <span class="emoji icon-wiggle">🔇</span>
                <span class="emoji icon-wiggle">🔈</span>
                <span class="emoji icon-wiggle">🔉</span>
                <span class="emoji icon-wiggle">🔊</span>
                <span class="emoji icon-wiggle">📢</span>
                <span class="emoji icon-wiggle">📣</span>
                <span class="emoji icon-wiggle">📯</span>
                <span class="emoji icon-wiggle">🔔</span>
                <span class="emoji icon-wiggle">🔕</span>
                <span class="emoji icon-wiggle">🎵</span>
                <span class="emoji icon-wiggle">🎶</span>
                <span class="emoji icon-wiggle">🏧</span>
                <span class="emoji icon-wiggle">🚮</span>
                <span class="emoji icon-wiggle">🚰</span>
                <span class="emoji icon-wiggle">♿</span>
                <span class="emoji icon-wiggle">🚹</span>
                <span class="emoji icon-wiggle">🚺</span>
                <span class="emoji icon-wiggle">🚻</span>
                <span class="emoji icon-wiggle">🚼</span>
                <span class="emoji icon-wiggle">🚾</span>
                <span class="emoji icon-wiggle">⚠</span>
                <span class="emoji icon-wiggle">🚸</span>
                <span class="emoji icon-wiggle">⛔</span>
                <span class="emoji icon-wiggle">🚫</span>
                <span class="emoji icon-wiggle">🚳</span>
                <span class="emoji icon-wiggle">🚭</span>
                <span class="emoji icon-wiggle">🚯</span>
                <span class="emoji icon-wiggle">🚱</span>
                <span class="emoji icon-wiggle">🚷</span>
                <span class="emoji icon-wiggle">🔞</span>
                <span class="emoji icon-wiggle">☢</span>
                <span class="emoji icon-wiggle">☣</span>
                <span class="emoji icon-wiggle">🛐</span>
                <span class="emoji icon-wiggle">⚛</span>
                <span class="emoji icon-wiggle">🕉</span>
                <span class="emoji icon-wiggle">✡</span>
                <span class="emoji icon-wiggle">☸</span>
                <span class="emoji icon-wiggle">☯</span>
                <span class="emoji icon-wiggle">✝</span>
                <span class="emoji icon-wiggle">☦</span>
                <span class="emoji icon-wiggle">☪</span>
                <span class="emoji icon-wiggle">☮</span>
                <span class="emoji icon-wiggle">🕎</span>
                <span class="emoji icon-wiggle">🔯</span>
                <span class="emoji icon-wiggle">♈</span>
                <span class="emoji icon-wiggle">♉</span>
                <span class="emoji icon-wiggle">♊</span>
                <span class="emoji icon-wiggle">♋</span>
                <span class="emoji icon-wiggle">♌</span>
                <span class="emoji icon-wiggle">♍</span>
                <span class="emoji icon-wiggle">♎</span>
                <span class="emoji icon-wiggle">♏</span>
                <span class="emoji icon-wiggle">♐</span>
                <span class="emoji icon-wiggle">♑</span>
                <span class="emoji icon-wiggle">♒</span>
                <span class="emoji icon-wiggle">♓</span>
                <span class="emoji icon-wiggle">⛎</span>
                <span class="emoji icon-wiggle">🔀</span>
                <span class="emoji icon-wiggle">🔁</span>
                <span class="emoji icon-wiggle">🔂</span>
                <span class="emoji icon-wiggle">▶</span>
                <span class="emoji icon-wiggle">⏩</span>
                <span class="emoji icon-wiggle">◀</span>
                <span class="emoji icon-wiggle">⏪</span>
                <span class="emoji icon-wiggle">🔼</span>
                <span class="emoji icon-wiggle">⏫</span>
                <span class="emoji icon-wiggle">🔽</span>
                <span class="emoji icon-wiggle">⏬</span>
                <span class="emoji icon-wiggle">⏹</span>
                <span class="emoji icon-wiggle">⏏</span>
                <span class="emoji icon-wiggle">🎦</span>
                <span class="emoji icon-wiggle">🔅</span>
                <span class="emoji icon-wiggle">🔆</span>
                <span class="emoji icon-wiggle">📶</span>
                <span class="emoji icon-wiggle">📳</span>
                <span class="emoji icon-wiggle">📴</span>
                <span class="emoji icon-wiggle">♻</span>
                <span class="emoji icon-wiggle">🔱</span>
                <span class="emoji icon-wiggle">📛</span>
                <span class="emoji icon-wiggle">🔰</span>
                <span class="emoji icon-wiggle">⭕</span>
                <span class="emoji icon-wiggle">✅</span>
                <span class="emoji icon-wiggle">☑</span>
                <span class="emoji icon-wiggle">✔</span>
                <span class="emoji icon-wiggle">✖</span>
                <span class="emoji icon-wiggle">❌</span>
                <span class="emoji icon-wiggle">❎</span>
                <span class="emoji icon-wiggle">➕</span>
                <span class="emoji icon-wiggle">➖</span>
                <span class="emoji icon-wiggle">➗</span>
                <span class="emoji icon-wiggle">➰</span>
                <span class="emoji icon-wiggle">➿</span>
                <span class="emoji icon-wiggle">〽</span>
                <span class="emoji icon-wiggle">✳</span>
                <span class="emoji icon-wiggle">✴</span>
                <span class="emoji icon-wiggle">❇</span>
                <span class="emoji icon-wiggle">‼</span>
                <span class="emoji icon-wiggle">⁉</span>
                <span class="emoji icon-wiggle">❓</span>
                <span class="emoji icon-wiggle">❔</span>
                <span class="emoji icon-wiggle">❕</span>
                <span class="emoji icon-wiggle">❗</span>
                <span class="emoji icon-wiggle">#️⃣</span>
                <span class="emoji icon-wiggle">0️⃣</span>
                <span class="emoji icon-wiggle">1️⃣</span>
                <span class="emoji icon-wiggle">2️⃣</span>
                <span class="emoji icon-wiggle">3️⃣</span>
                <span class="emoji icon-wiggle">4️⃣</span>
                <span class="emoji icon-wiggle">5️⃣</span>
                <span class="emoji icon-wiggle">6️⃣</span>
                <span class="emoji icon-wiggle">7️⃣</span>
                <span class="emoji icon-wiggle">8️⃣</span>
                <span class="emoji icon-wiggle">9️⃣</span>
                <span class="emoji icon-wiggle">🔟</span>
                <span class="emoji icon-wiggle">💯</span>
                <span class="emoji icon-wiggle">🔠</span>
                <span class="emoji icon-wiggle">🔡</span>
                <span class="emoji icon-wiggle">🔢</span>
                <span class="emoji icon-wiggle">🔣</span>
                <span class="emoji icon-wiggle">🔤</span>
                <span class="emoji icon-wiggle">🅰</span>
                <span class="emoji icon-wiggle">🆎</span>
                <span class="emoji icon-wiggle">🅱</span>
                <span class="emoji icon-wiggle">🆑</span>
                <span class="emoji icon-wiggle">🆒</span>
                <span class="emoji icon-wiggle">🆓</span>
                <span class="emoji icon-wiggle">ℹ</span>
                <span class="emoji icon-wiggle">🆔</span>
                <span class="emoji icon-wiggle">Ⓜ</span>
                <span class="emoji icon-wiggle">🆕</span>
                <span class="emoji icon-wiggle">🆖</span>
                <span class="emoji icon-wiggle">🅾</span>
                <span class="emoji icon-wiggle">🆗</span>
                <span class="emoji icon-wiggle">🅿</span>
                <span class="emoji icon-wiggle">🆘</span>
                <span class="emoji icon-wiggle">🆙</span>
                <span class="emoji icon-wiggle">🆚</span>
                <span class="emoji icon-wiggle">🈁</span>
                <span class="emoji icon-wiggle">🈂</span>
                <span class="emoji icon-wiggle">🈷</span>
                <span class="emoji icon-wiggle">🈶</span>
                <span class="emoji icon-wiggle">🈯</span>
                <span class="emoji icon-wiggle">🉐</span>
                <span class="emoji icon-wiggle">🈹</span>
                <span class="emoji icon-wiggle">🈚</span>
                <span class="emoji icon-wiggle">🈲</span>
                <span class="emoji icon-wiggle">🉑</span>
                <span class="emoji icon-wiggle">🈸</span>
                <span class="emoji icon-wiggle">🈴</span>
                <span class="emoji icon-wiggle">🈳</span>
                <span class="emoji icon-wiggle">㊗</span>
                <span class="emoji icon-wiggle">㊙</span>
                <span class="emoji icon-wiggle">🈺</span>
                <span class="emoji icon-wiggle">🈵</span>
                <span class="emoji icon-wiggle">▪</span>
                <span class="emoji icon-wiggle">▫</span>
                <span class="emoji icon-wiggle">◻</span>
                <span class="emoji icon-wiggle">◼</span>
                <span class="emoji icon-wiggle">◽</span>
                <span class="emoji icon-wiggle">◾</span>
                <span class="emoji icon-wiggle">⬛</span>
                <span class="emoji icon-wiggle">⬜</span>
                <span class="emoji icon-wiggle">🔶</span>
                <span class="emoji icon-wiggle">🔷</span>
                <span class="emoji icon-wiggle">🔸</span>
                <span class="emoji icon-wiggle">🔹</span>
                <span class="emoji icon-wiggle">🔺</span>
                <span class="emoji icon-wiggle">🔻</span>
                <span class="emoji icon-wiggle">💠</span>
                <span class="emoji icon-wiggle">🔲</span>
                <span class="emoji icon-wiggle">🔳</span>
                <span class="emoji icon-wiggle">⚪</span>
                <span class="emoji icon-wiggle">⚫</span>
                <span class="emoji icon-wiggle">🔴</span>
                <span class="emoji icon-wiggle">🔵</span>
            </div>
        </div>
        `;

    }

    function initEmojiTabs() {
        // Set Smileys as default active tab (using the 😀 emoji)
        $('.emoji-link').click(function (e) {
            e.preventDefault();

            $('.emoji-link').removeClass('active');
            $(this).addClass('active');

            $('.emoji-tab').removeClass('show');
            $('#' + $(this).data('tab')).addClass('show');
        });// Initialize drag scrolling for tabs
        // Initialize drag scrolling for tabs
        initDragScroll();
    }

    // Drag scroll functionality
    function initDragScroll() {
        const $tabContainer = $('.emoji-tab-btn');
        let isDown = false;
        let startX;
        let scrollLeft;
        let isHovering = false;
        let wheelTimeout;
        const scrollStep = 100; // Adjust this value for scroll sensitivity

        // Track hover state
        $tabContainer
            .on('mouseenter', function() {
                isHovering = true;
                $tabContainer.css('cursor', 'grab');
            })
            .on('mouseleave', function() {
                isHovering = false;
                isDown = false;
                $tabContainer.css('cursor', '');
            });

        // Mouse drag scrolling
        $tabContainer.on({
            'mousedown': function(e) {
                if (!isHovering) return;
                isDown = true;
                startX = e.pageX - $tabContainer.offset().left;
                scrollLeft = $tabContainer.scrollLeft();
                $tabContainer.css('cursor', 'grabbing');
                e.preventDefault();
            },
            'mouseup': function() {
                isDown = false;
                $tabContainer.css('cursor', isHovering ? 'grab' : '');
            },
            'mousemove': function(e) {
                if (!isDown || !isHovering) return;
                e.preventDefault();
                const x = e.pageX - $tabContainer.offset().left;
                const walk = (x - startX) * 2;
                $tabContainer[0].scrollLeft = scrollLeft - walk;
            }
        });

        // Smooth mousewheel horizontal scrolling
        $tabContainer.on('wheel', function(e) {
            if (!isHovering) return;

            e.preventDefault();
            e.stopPropagation();

            // Clear any pending scroll animations
            clearTimeout(wheelTimeout);
            $tabContainer.stop(true, false);

            const delta = e.originalEvent.deltaY;
            const currentScroll = $tabContainer[0].scrollLeft;
            const targetScroll = currentScroll + (delta > 0 ? scrollStep : -scrollStep);

            // Apply smooth scrolling
            $tabContainer.animate({
                scrollLeft: targetScroll
            }, 200, 'swing');

            // Prevent rapid wheel events from queueing too many animations
            wheelTimeout = setTimeout(() => {}, 100);
        });

        // Touch support with momentum
        let touchStartX = 0;
        let velocity = 0;
        let lastTime = 0;
        let frameId = null;

        $tabContainer.on({
            'touchstart': function(e) {
                isDown = true;
                touchStartX = e.originalEvent.touches[0].pageX;
                scrollLeft = $tabContainer.scrollLeft();
                velocity = 0;
                lastTime = performance.now();
                cancelAnimationFrame(frameId);
            },
            'touchend': function() {
                isDown = false;

                // Apply momentum if significant velocity
                if (Math.abs(velocity) > 2) {
                    const duration = Math.min(Math.abs(velocity) * 50, 1000);
                    const target = $tabContainer[0].scrollLeft + (velocity * 50);

                    $tabContainer.animate({
                        scrollLeft: target
                    }, duration, 'easeOutQuad');
                }
            },
            'touchmove': function(e) {
                if (!isDown) return;
                e.preventDefault();

                const touchX = e.originalEvent.touches[0].pageX;
                const now = performance.now();
                const deltaTime = now - lastTime;

                if (deltaTime > 0) {
                    const deltaX = touchX - touchStartX;
                    velocity = deltaX / deltaTime;
                    lastTime = now;
                }

                $tabContainer[0].scrollLeft = scrollLeft - (touchX - touchStartX);
                touchStartX = touchX;
                scrollLeft = $tabContainer[0].scrollLeft;
            }
        });
    }

    initEmojiTabs();

    $(document).on('click', '.emoji-dropdown-toggle', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const container = $(this).siblings('.emoji-container');
        $('.emoji-container').not(container).removeClass('show');
        container.toggleClass('show');

        // Smart positioning to prevent going off-screen
        const dropdownRect = container[0].getBoundingClientRect();
        if (dropdownRect.top < 0) {
            // If near top of viewport, show below instead
            container.css({
                'bottom': 'auto',
                'top': '100%',
                'margin-bottom': '0',
                'margin-top': '5px'
            });
        } else {
            // Default above-button position
            container.css({
                'bottom': '100%',
                'top': 'auto',
                'margin-bottom': '5px',
                'margin-top': '0'
            });
        }
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.emoji-dropdown').length) {
            $('.emoji-container').removeClass('show');
        }
    });

    // Prevent closing when clicking inside
    $(document).on('click', '.emoji-container, .emoji-container *', function(e) {
        e.stopPropagation();
    });

    // Close when pressing ESC
    $(document).on('keydown', function(e) {
        if (e.key === "Escape") {
            $('.emoji-container').removeClass('show');
        }
    });

    // Handle window resize
    $(window).on('resize', function() {
        $('.emoji-container.show').each(function() {
            const dropdown = $(this).closest('.emoji-dropdown');
            positionDropdown(dropdown);
        });
    });

    // Smart positioning function
    function positionDropdown(dropdown) {
        const container = dropdown.find('.emoji-container');
        const toggle = dropdown.find('.emoji-dropdown-toggle');
        const toggleRect = toggle[0].getBoundingClientRect();
        const viewportWidth = window.innerWidth;

        // Reset positioning
        container.removeClass('right-aligned');

        // Check if dropdown would go off-screen on the right
        if (toggleRect.left + 220 > viewportWidth) {
            container.addClass('right-aligned');
        }

        // Adjust for mobile viewports
        if (viewportWidth < 576) {
            container.css('max-height', '250px');
        }
    }

    $(document).on('click', '.emoji', function() {
        let emoji = $(this).text(); // Get clicked emoji
        let messageBox = $('textarea[name="message"]'); // Select textarea

        // Append emoji to textarea
        messageBox.val(messageBox.val() + emoji);
    })

    $(document).on('click', '.reaction', function() {
        const $dropdown = $(this).closest('.dropdown'); // Find closest dropdown
        let messageId = '';
        let reaction = '';

        if ($dropdown.length === 1) {
            messageId = $(this).closest('.dropdown').find('.react-toggle').data('message');
            reaction = $(this).data('type');
        } else {
            messageId = $(this).data('message');
            reaction = $(this).data('type');
        }

        sendReaction(messageId, reaction);
    });

    function sendReaction(messageId, reaction) {
        $.ajax({
            url: '{{ route("admin.chat.react") }}',
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                message_id: messageId,
                reaction: reaction
            },
            success: function(response) {
                if(response.status === 'success'){
                    return;
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

    let pinnedUpdate = null;

    function reloadPinned() {
        const chatId = $('.pinnedDisplay').data('chat');
        $.ajax({
            url: "{{ route('reloadad.chat.pinned') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                pinnedUpdate: JSON.stringify(pinnedUpdate),
                chat_id: chatId
            },
            success: function(response) {
                pinnedUpdate = response.pinnedUpdate;

                // Always update based on current pinned state
                const pinnedContainer = $('.pinned-body');

                if (response.pinned.length > 0) {
                    updatePinnedUI(response.pinned[0]);
                } else {
                    pinnedContainer.remove();
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    function updatePinnedUI(pin) {
        const container = $('.pinned-body');
        let photoHtml = "";
        let userPhoto = pin.photo ? `/upload/photo_bank/${pin.photo}` : '/upload/nophoto.jfif';
        photoHtml = `<img src="${userPhoto}" class="img-xs rounded-circle border" alt="user">`;

        // Create HTML if doesn't exist
        if (!container.length) {
            $('.pinnedDisplay').append(`
                <div class="pinned-body border-bottom pt-2 px-3">
                    <h5 class="text-muted"><i data-feather="paperclip" class="icon-sm icon-wiggle"></i> Latest Pinned Messages</h5>
                    <ul class="list-unstyled chat-list p-0 m-0 mb-1 px-1">
                        <li class="chat-item pe-1 mt-2 pinned-message"
                            id="pinnedMessage_${pin.chat_id}"
                            data-chat="${pin.chat_id}">
                            <a href="javascript:;" class="d-flex align-items-center">
                                <figure class="mb-0 me-2">
                                    ${photoHtml}
                                </figure>
                                <div class="d-flex align-items-center" style="width: 100% !important;">
                                    <div class="w-100 pe-4">
                                        <p class="text-body reply-span text-muted">${pin.user_name}</p>
                                        <div class="align-items-center">
                                        <p class="text-muted reply-bubble tx-13 w-100">
                                             ${pin.message !== null
                                                ? pin.message.replace(/\n/g, " ")  // Converts newlines to <br> for display
                                                : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}</span>
                                        </p>
                                        <p class="text-body m-0 reply-span text-muted">Pinned at ${pin.created_at}</p>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-end text-body">
                                        <i data-feather="chevron-down" class="icon-md text-secondary icon-wiggle"></i>
                                    </div>
                                </div>
                            </a>
                        </li>
                    </ul>
                </div>
            `);
        }

        // Update existing content
        $('.pinned-body').html(`
            <h5 class="text-muted"><i data-feather="paperclip" class="icon-sm icon-wiggle"></i> Latest Pinned Messages</h5>
            <ul class="list-unstyled chat-list p-0 m-0 mb-1 px-1">
                <li class="chat-item pe-1 mt-2 pinned-message"
                    id="pinnedMessage_${pin.chat_id}"
                    data-chat="${pin.chat_id}">
                    <a href="javascript:;" class="d-flex align-items-center">
                        <figure class="mb-0 me-2">
                            ${photoHtml}
                        </figure>
                        <div class="d-flex align-items-center" style="width: 100% !important;">
                            <div class="w-100 pe-4">
                                <p class="text-body reply-span text-muted">${pin.user_name}</p>
                                <div class="align-items-center">
                                <p class="text-muted reply-bubble tx-13 w-100">
                                     ${pin.message !== null
                                            ? pin.message.replace(/\n/g, " ")  // Converts newlines to <br> for display
                                            : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}</span>
                                </p>
                                <p class="text-body m-0 reply-span text-muted">Pinned at ${pin.created_at}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-end text-body">
                                <i data-feather="chevron-down" class="icon-md text-secondary icon-wiggle"></i>
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        `);

        feather.replace();
    }

    setInterval(reloadPinned, 1000);

    $(document).on('click', '.viewPinnedMessage', function() {
        $(`#pinnedMessageModal`).modal('show');
        var chat = $(this).data('chat');

        $.ajax({
            url: `{{ route('admin.chat.viewpinnedmessage') }}`,
            type: 'GET',
            data: {
                chat: chat
            },
            dataType: 'json',
            success: function (response) {
                // Display success message
                if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    $(`#pinnedMessageDisplay`).html('<h5>No Pinned Message</h5>');
                    return;
                } else if(response.status === 'success'){
                    var pinned = response.pinned;
                    var pin_html = `
                    <div class="pinned-body-view pt-2 px-3">
                        <ul class="list-unstyled chat-list p-0 m-0 mb-1 px-1">`;

                    if(pinned.length > 0){
                        pinned.forEach((pin, index) => {
                            let photoHtml = "";
                            let userPhoto = pin.photo ? `/upload/photo_bank/${pin.photo}` : '/upload/nophoto.jfif';
                            photoHtml = `<img src="${userPhoto}" class="img-xs rounded-circle border" alt="user">`;

                            pin_html += `
                            <li class="chat-item pinned-message" id="pinnedMessage_${pin.chat_id}" data-chat="${pin.chat_id}">
                                <span class="reply-span">Pinned By "<b>${pin.by_name}</b>"</span>
                                <div class="d-flex align-items-center py-1">
                                    <figure class="mb-0 me-2">
                                        ${photoHtml}
                                    </figure>
                                    <div class="d-flex flex-column flex-grow-1 text-truncate">
                                        <span class="reply-span">${pin.user_name}</span>
                                        <span class="reply-bubble text-truncate">
                                        ${pin.message !== null
                                            ? pin.message.replace(/\n/g, "<br>")  // Converts newlines to <br> for display
                                            : '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}</span>
                                        <div class="text-muted small ms-2">${pin.created_at}</div>
                                    </div>
                                    <div class="dropdown ms-2">
                                        <a href="#" class="text-muted" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical" class="icon-sm"></i>
                                        </a>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item unpinMessage" href="#" data-message="${pin.message_id}" data-chat="${chat}">
                                                <i data-feather="paperclip" class="icon-sm me-2"></i> Unpin
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mediaPinnedContainer-${pin.message_id} friend mb-2"></div>
                            </li>`;

                            if($(`.mediaPinnedContainer-${pin.message_id}`).length === 0){
                                checkMediaPinned(pin.message_id, false);
                            }

                            if (pinned.length > 1 && index !== pinned.length - 1) {
                                pin_html += `<div class="border-bottom border-secondary my-1"></div>`;
                            }
                        });
                    }



                    pin_html += `</ul></div>`;
                    $(`#pinnedMessageDisplay`).html(pin_html);

                    if(pinned.length > 0){
                        pinned.forEach((pin, index) => {
                            if($(`.mediaPinnedContainer-${pin.message_id}`).length === 1){
                                checkMediaPinned(pin.message_id, false);
                            }
                        });
                    }
                    feather.replace();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    function checkMediaPinned(messageId, isMe) {
        $.ajax({
            url: "{{ route('admin.chat.checkmessageattachment') }}",
            type: "GET",
            data: { message: messageId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'error') {
                    $(`.mediaPinnedContainer-${messageId}`).html(``);
                } else if (response.status === 'success' && response.attachments.length > 0) {
                    const container = $(`.mediaPinnedContainer-${messageId}`);
                    container.html(''); // Clear previous content

                    // Separate images and files
                    const images = response.attachments.filter(att => att.type.startsWith('image/'));
                    const files = response.attachments.filter(att => !att.type.startsWith('image/'));

                    // Process images
                    if (images.length > 0) {
                        const count = images.length;
                        let mediaHtml = `
                            <div class="message-attachment-grid" data-count="${count}">
                        `;

                        // Always show max 4 images
                        const imagesToShow = images.slice(0, 4);

                        imagesToShow.forEach((img, index) => {
                            mediaHtml += `
                                 <div class="grid-item convo-media-item" data-media-id="${img.id}" data-chat="${img.chat_id}" style="position:relative">
                                    <img src="/${img.path}"
                                        class="img-fluid rounded"
                                        alt="${img.name}"
                                        loading="lazy">
                                    ${index === 3 && count > 4 ?
                                        `<div class="more-images-overlay">+${count - 4}</div>` : ''}
                                </div>
                            `;
                        });

                        mediaHtml += `</div>`;
                        container.append(mediaHtml);
                    }

                    // Process non-image files
                    // In your checkMedia() success handler's file processing section:
                    if (files.length > 0) {
                        files.forEach(file => {
                            const fileUrl = `/${file.path}`; // Ensure correct path
                            const $bubble = $(
                                `<a href="${fileUrl}" download="${file.name}" class="file-bubble d-flex ${isMe ? 'me' : 'friend'}">
                                        <i class="icon-lg text-muted icon-wiggle me-2" data-feather="download-cloud"></i>
                                        <p class="m-0">${file.name}</p>
                                </a>`
                            );

                            // Add hover effects
                            $bubble.hover(
                                () => $bubble.css('opacity', '0.8'),
                                () => $bubble.css('opacity', '1')
                            );

                            container.append($bubble);
                        });

                        // Refresh Feather icons after dynamic content addition
                        feather.replace();
                    }
                }
            },
            error: function(xhr) {
                console.log('AJAX Error:', xhr.responseText);
            }
        });
    }

    $(document).on('click', '.convo-info-toggle', function() {
        $(this).toggleClass("active");
        $(".convo-info-body").toggleClass("show");
        $(".pinnedDisplay, .chat-body, .chat-footer").toggleClass("hide");
    });

    $(document).on('click', '.info-link', function(e) {
        e.preventDefault();

        $('.info-link').removeClass('active');
        $(this).addClass('active');

        $('.info-content').removeClass('show');
        $('#' + $(this).data('tab')).addClass('show');
    });

    $(document).on('click', '#searchBtn', function() {
        $('#convoInfo').addClass('d-none');
        $('#convoSearch').removeClass('d-none');
        $('#searchBtn').addClass('d-none');
        $('#backBtn').removeClass('d-none');
    });

    $(document).on('click', '#backBtn', function() {
        $('#convoSearch').addClass('d-none');
        $('#convoInfo').removeClass('d-none');
        $('#backBtn').addClass('d-none');
        $('#searchBtn').removeClass('d-none');
    });

    $(document).on('keyup', '#searchNick', function () {
        let searchText = $(this).val().toLowerCase().trim();

        $('.convo-participants').removeClass('d-none');

        $('.convo-participants').each(function () {
            let userName = $(this).attr('data-name').toLowerCase();

            if (!userName.includes(searchText)) {
                $(this).addClass('d-none');
            }
        });
    });

    $(document).on('keyup', '#searchToAdd', function () {
        let searchText = $(this).val().toLowerCase().trim();

        $('.search-to-add').removeClass('d-none');

        $('.search-to-add').each(function () {
            let userName = $(this).attr('data-name').toLowerCase();

            if (!userName.includes(searchText)) {
                $(this).addClass('d-none');
            }
        });
    });

    $(document).on('keyup', '#searchMember', function () {
        let searchText = $(this).val().toLowerCase().trim();

        $('.search-member-list').removeClass('d-none');

        $('.search-member-list').each(function () {
            let userName = $(this).attr('data-name').toLowerCase();

            if (!userName.includes(searchText)) {
                $(this).addClass('d-none');
            }
        });
    });

    $(document).on('click', '.submit-nickname', function() {
        var chat = $(this).data('chat');
        var user = $(this).data('user');
        var form = $(`#nicknameForm_${chat}_${user}`).serialize();
        form = form + `&chat_id=${chat}&user_id=${user}`;
        $.ajax({
            url: `{{ route('admin.chat.setnickname') }}`,
            type: 'POST',
            data: form,
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    chatDisplay(chat);
                } else if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('submit', '#convoPhotoForm', function(e) {
        var chat = $(this).data('chat');
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('admin.chat.setconvoimage') }}`,
            type: 'POST',
            data: formData,
            processData: false, // Don't process the data
            contentType: false, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    $('#convoPhotoForm')[0].reset();
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully set conversation photo'
                    });
                    chatDisplay(chat);
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

    $(document).on('click', '.clearConvoImage', function() {
        var chat = $(this).data('chat');
        $.ajax({
            url: `{{ route('admin.chat.unsetconvoimage') }}`,
            type: 'POST',
            data: {
                chat: chat
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully unset conversation photo'
                    });
                    chatDisplay(chat);
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

    $(document).on('submit', '#convoNameForm', function(e) {
        var chat = $(this).data('chat');
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('admin.chat.setconvoname') }}`,
            type: 'POST',
            data: formData,
            processData: false, // Don't process the data
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    $('#convoNameForm')[0].reset();
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully set conversation name'
                    });
                    chatDisplay(chat);
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

    $(document).on('click', '.clearConvoName', function() {
        var chat = $(this).data('chat');
        $.ajax({
            url: `{{ route('admin.chat.unsetconvoname') }}`,
            type: 'POST',
            data: {
                chat: chat
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully unset conversation name'
                    });
                    chatDisplay(chat);
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

    $(document).on('click', '.addMemberGroupSubmit', function() {
        var chat = $(this).data('chat');
        var user = $(this).data('user');
        $.ajax({
            url: `{{ route('admin.chat.addnewmembergroup') }}`,
            type: 'POST',
            data: {
                chat: chat,
                user: user
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                // Display success message
                if(response.status === 'success'){
                    Toast.fire({
                        icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                        title: 'Successfully add to conversation'
                    });
                    var users = response.users;

                    let photoHtml = "";
                    let userPhoto = users.photo ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif';
                    photoHtml = `<img src="${userPhoto}"
                        class="img-xs rounded-circle border mb-0" alt="user" style="width: 50px; height: 50px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">`;
                    $('.add-member-here').prepend(`
                    <div class="convo-participants d-flex justify-content-between align-items-start mb-2 search-member-list convo-member-${users.user_id}" data-name="${users.name}" style="height: 75px !important">
                        <div class="d-flex flex-grow-1 mb-0"> <!-- Added flex-grow-1 -->
                            <figure class="mb-0 me-2">
                                ${photoHtml}
                            </figure>
                            <div class="convo-participants-content d-flex flex-column justify-content-center">
                                    ${users.nickname !== null ? `
                                <p class="mb-0">${users.nickname}</p>
                                <span class="reply-span">Name: ${users.name}</span>
                                ` : `
                                <p class="mb-0">${users.name}</p>
                                `}
                            </div>
                        </div>
                        <div class="d-flex justify-content-center align-items-center h-100 mb-0" style="min-height: 100% !important">
                            <div class="dropdown">
                                <a type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="more-horizontal"></i>
                                </a>
                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                    ${!users.im_user ? `<a class="dropdown-item d-flex align-items-center" id="chatWithUser" data-user="${users.user_id}" data-name="${users.name}" href="javascript:;"><i data-feather="send" class="icon-sm me-2 icon-wiggle"></i> <span class="">Message</span></a>` : ``}
                                    ${users.im_admin && !users.is_creator && !users.im_user ?
                                    `
                                    <a class="dropdown-item d-flex align-items-center toggleAsAdminChat toggle-admin-${users.user_id}" href="javascript:;" data-type="${!users.is_admin ? `add` : `remove`}" data-user="${users.user_id}" data-chat="${chat}">
                                        <i data-feather="${!users.is_admin ? `shield` : `shield-off`}" class="icon-sm me-2 icon-wiggle"></i>
                                        <span class="">${!users.is_admin ? `Set as admin` : `Remove as admin`}</span>
                                    </a>
                                    ` : `
                                    `
                                    }
                                    ${users.im_admin && !users.is_admin && !users.is_creator && !users.im_user? `<a class="dropdown-item d-flex align-items-center kickAsAdminChat kick-convo-${users.user_id}" href="javascript:;" data-type="remove" data-user="${users.user_id}" data-chat="${chat}" href="javascript:;"><i data-feather="user-minus" class="icon-sm me-2 icon-wiggle"></i> <span class="">Remove member</span></a>` : ``}
                                    ${users.im_user ? `<a class="dropdown-item d-flex align-items-center leaveConversation" data-chat="${chat}" href="javascript:;"><i data-feather="log-out" class="icon-sm me-2 icon-wiggle"></i> <span class="">Leave Group Conversation</span></a>` : ``}
                                </div>
                            </div>
                        </div>
                    </div>
                    `);

                    $(`.user-to-add-${user}`).remove();
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

    $(document).on('click', '.toggleAsAdminChat', function(){
        var $this = $(this);
        var type = $(this).data('type');
        var chat = $(this).data('chat');
        var user = $(this).data('user');
        var question = type === 'add' ? 'make this user' : 'remove this user';
        Swal.fire({
            title: `Are you sure you want to ${question} as admin?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: `{{ route('admin.chat.toggleasadmin') }}`,
                    type: 'POST',
                    data: {
                        chat: chat,
                        user: user,
                        type: type
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function (response) {
                        // Display success message
                        if(response.status === 'success'){
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: response.message
                            });

                            var newType = (type === 'add') ? 'remove' : 'add';

                            // Update data attribute dynamically
                            $this.data('type', newType);
                            $this.attr('data-type', newType);

                            // Update the clicked button's HTML
                            $this.html(newType === 'add' ? `
                                <i data-feather="shield" class="icon-sm me-2 icon-wiggle"></i>
                                <span class="">Set as admin</span>
                            ` : `
                                <i data-feather="shield-off" class="icon-sm me-2 icon-wiggle"></i>
                                <span class="">Remove as admin</span>
                            `);
                            if(newType === 'add'){
                                $this.after(`
                                    <a class="dropdown-item d-flex align-items-center kickAsAdminChat kick-convo-${user}" href="javascript:;" data-type="remove" data-user="${user}" data-chat="${chat}">
                                        <i data-feather="user-minus" class="icon-sm me-2 icon-wiggle"></i>
                                        <span class="">Remove member</span>
                                    </a>
                                `);
                            } else if(newType === 'remove'){
                                $(`.kick-convo-${user}`).remove();
                            }
                            feather.replace();
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

    $(document).on('click', '.kickAsAdminChat', function() {
        var user = $(this).data('user');
        var chat = $(this).data('chat');

        Swal.fire({
            title: `Are you sure you want to remove user on this conversion?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: `{{ route('admin.chat.removefromgroup') }}`,
                    type: 'POST',
                    data: {
                        chat: chat,
                        user: user,
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function (response) {
                        // Display success message
                        if(response.status === 'success'){
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully removed member'
                            });

                            var users = response.users;
                            console.log(users);
                            let photoHtml = "";
                            let userPhoto = users.photo ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif';
                            photoHtml = `<img src="${userPhoto}"
                                class="img-xs rounded-circle border mb-0" alt="user" style="width: 50px; height: 50px; object-fit: cover; object-position: center; box-shadow: 6px 6px 0 0 rgba(101, 113, 255, 0.25) !important;">`;
                            $(`.convo-member-${user}`).remove();
                            $('.remove-member-here').prepend(`
                            <div class="convo-participants d-flex justify-content-between align-items-start search-to-add user-to-add-${users.id} mb-2" data-name="${users.name}" style="height: 75px !important">
                                <div class="d-flex flex-grow-1 mb-0"> <!-- Added flex-grow-1 -->
                                    <figure class="mb-0 me-2">
                                        ${photoHtml}
                                    </figure>
                                    <div class="convo-participants-content d-flex flex-column justify-content-center">
                                        <p class="mb-0">${users.name}</p>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center align-items-center h-100 mb-0" style="min-height: 100% !important">
                                    <a type="button" href="javascript:;" class="addMemberGroupSubmit" data-chat="${chat}" data-user="${users.id}">
                                        <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="user-plus"></i>
                                    </a>
                                </div>
                            </div>
                            `);
                            feather.replace();
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

    $(document).on('click', '.leaveConversation', function(){
        var chat = $(this).data('chat');

        Swal.fire({
            title: `Are you sure you want to leave this conversion?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirmed, proceed with AJAX request
                $.ajax({
                    url: `{{ route('admin.chat.leaveconversation') }}`,
                    type: 'POST',
                    data: {
                        chat: chat
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function (response) {
                        // Display success message
                        if(response.status === 'success'){
                            Toast.fire({
                                icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                                title: 'Successfully leave conversation'
                            });

                            $('#chats').load(location.href + ' #chats > *', function() {
                                let firstChat = $('#chatContainer').find('li:first-child a#viewChat');
                                if (firstChat.length > 0) {
                                    firstChat.trigger('click');
                                }
                                feather.replace(); // Reinitialize icons
                            });

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
    })

    $(document).on('click', '.chatMuteSubmit', function() {
        var chat = $(this).data('chat');
        var muted = parseInt($(this).attr('data-muted'));
        $.ajax({
            url: `{{ route('admin.chat.setmutedchat') }}`,
            type: 'POST',
            data: {
                chat_id: chat,
                muted: muted
            },
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function (response) {
                if(response.status === 'success'){
                    if(muted === 0){
                        $('.chatMuteSubmit').html(`
                            <i data-feather="bell" class="btn-icon-prepend"></i> Unmute
                        `);
                        $('.chatMuteSubmit').attr('data-muted', "1"); // Toggle to 0 (unmuted)
                    } else if (muted === 1) {
                        $('.chatMuteSubmit').html(`
                            <i data-feather="bell-off" class="btn-icon-prepend"></i> Mute
                        `);
                        $('.chatMuteSubmit').attr('data-muted', "0"); // Toggle to 1 (muted)
                    }
                    Toast.fire({
                        icon: 'success',
                        title: response.message,
                    });
                    feather.replace(); // Ensure icons update dynamically
                } else if(response.status === 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                    return;
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });

    $(document).on('input', '#searchMessage', function() {
        var value = $(this).val().trim();
        if (value.length > 0) {
            $('.showMessageGuide').text('Press "Enter" to search message').show();
        } else {
            $('.showMessageGuide').hide();
        }
    });

    $(document).on('keypress', '#searchMessage', function(e) {
        if (e.which === 13) { // Enter key
            $('.showMessageGuide').hide();
        }
    });

    let currentPageSearch = 1;
    let isLoadingSearch = false;
    let hasMore = false;

    $(document).on('keypress', '#searchMessage', function(e) {
        if (e.which === 13) {
            currentPageSearch = 1;
            const chat = $(this).data('chat');
            const $input = $(this);
            const message = ($input.val() || '').trim();

            if (!message) {
                $('.showMessageGuide').html('Please enter some value before searching').show();
                return;
            }

            loadMessages(chat, message);
        }
    });

    function loadMessages(chat, message, initialLoad = true) {
        if (isLoadingSearch) return;

        isLoadingSearch = true;
        $('.loading-indicator').show();

        $.ajax({
            url: `{{ route('admin.chat.searchmessagevalue') }}`,
            type: 'POST',
            data: {
                chat: chat,
                message: message,
                page: currentPageSearch
            },
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if (response.status === 'success') {
                    hasMore = response.next_page !== null;

                    // Only increment page if loading more
                    if (!initialLoad) currentPageSearch = response.current_page;
                    const html = response.messages.map(msg => `
                        <div class="message-card">
                            <div class="message-header">
                                <img src="${msg.user.avatar != null ? `/upload/photo_bank/${msg.user.avatar}` : '/upload/nophoto.jfif'}"
                                    class="user-avatar"
                                    alt="${msg.user?.name}'s avatar">
                                <div class="user-info">
                                    <span class="username">${msg.user.name}</span>
                                    <span class="timestamp">${new Date(msg.created_at).toLocaleString()}</span>
                                </div>
                                <div class="message-actions">
                                    <div class="dropdown">
                                        <a type="button" id="searched-action" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="icon-lg text-muted pb-3px icon-wiggle" data-feather="more-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu" aria-labelledby="searched-action">
                                            ${!msg.is_pinned ? `
                                            <a class="dropdown-item d-flex align-items-center pinMessage" href="javascript:;" data-message="${msg.id}" data-chat="${msg.chat_id}">
                                                <i data-feather="paperclip" class="icon-sm me-2"></i>
                                                <span>Pin Message</span>
                                            </a>
                                            ` : `
                                            <a class="dropdown-item d-flex align-items-center unpinMessage" href="javascript:;" data-message="${msg.id}" data-chat="${msg.chat_id}">
                                                <i data-feather="paperclip" class="icon-sm me-2"></i>
                                                <span>Unpin Message</span>
                                            </a>
                                            `}
                                            <a class="dropdown-item d-flex align-items-center unsendMessage" href="javascript:;" data-message="${msg.id}" data-content="${msg.message}">
                                                <i data-feather="trash-2" class="icon-sm me-2"></i>
                                                <span>Unsend Message</span>
                                            </a>
                                            <a class="dropdown-item d-flex align-items-center forwardMessage" data-message="${msg.id}" data-content="${msg.message}" data-chat="${msg.chat_id}" href="javascript:;">
                                                <i data-feather="share-2" class="icon-sm me-2"></i>
                                                <span>Forward Message</span>
                                            </a>
                                            <a class="dropdown-item d-flex align-items-center copy-text" data-message="${msg.message}" href="javascript:;">
                                                <i data-feather="copy" class="icon-sm me-2"></i>
                                                <span>Copy Text</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="message-content reply-bubble">${msg.message}</div>
                        </div>
                    `).join('');

                    $(document).on('click', '.copy-text', function() {
                        const messageContent = $(this).closest('.message-card').find('.message-content').text();

                        navigator.clipboard.writeText(messageContent).then(() => {
                            Toast.fire({
                                icon: 'success',
                                title: 'Copied!',
                                html: 'Message text copied to clipboard'
                            });
                        }).catch(err => {
                            console.error('Failed to copy:', err);
                            Toast.fire({
                                icon: 'error',
                                title: 'Copy Failed',
                                html: 'Could not copy text to clipboard'
                            });
                        });
                    });

                    if (initialLoad) {
                        $('.message-results').remove();
                        $('.searchedDisplay').html(`
                            <div class="message-results">${html}</div>
                            ${hasMore ? '<div class="loading-indicator"></div>' : ''}
                        `);
                    } else {
                        $('.message-results').append(html);
                        // Update loading indicator based on hasMore
                        if (!hasMore) $('.loading-indicator').remove();
                    }

                    feather.replace();
                    setupScrollListener();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            },
            complete: function() {
                isLoadingSearch = false;
                $('.loading-indicator').hide();
            }
        });
    }

    function setupScrollListener() {
        $('.searchedDisplay').off('scroll').on('scroll', function() {
            const $container = $(this);
            const scrollTop = $container.scrollTop();
            const scrollHeight = $container[0].scrollHeight;
            const clientHeight = $container.height();
            const chat = $('#searchMessage').data('chat');
            const message = $('#searchMessage').val().trim();

            if (scrollTop + clientHeight >= scrollHeight - 100 && hasMore && !isLoadingSearch) {
                currentPageSearch++;
                loadMessages(chat, message, false);
            }
        });
    }

    //endregion

    //region Meet
    $(document).on('click', '#createMeeting', function() {
        $('#createMeetModal').modal('show');
    });

    $('#meetForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        $.ajax({
            url: `{{ route('admin.chat.savemeeting') }}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': token
            },
            success: function(response) {
                if(response.status === 'success') {
                    $('#meetForm')[0].reset();
                    Toast.fire({
                        icon: 'success',
                        title: 'Successfully created meeting'
                    });
                    $('#createMeetModal').modal('hide');
                    $('#calls').load(location.href + ' #calls > *');
                } else {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                    });
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr.responseText);
                Toast.fire({
                    icon: 'error',
                    title: 'System Error'
                });
            }
        });
    });

    $(document).on('click', '.end-meeting-btn', async function(e) {
        const $btn = $(this);
        const room_id = $btn.data('room-id');
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        e.stopPropagation();

        try {
            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Ending...');

            const response = await $.ajax({
                url: `{{ route('admin.chat.removemeeting') }}`,
                type: 'POST',
                data: { room: room_id },
                headers: { 'X-CSRF-TOKEN': csrfToken }
            });

            if (jitsiApi) {
                jitsiApi.executeCommand('endConference');
                cleanupMeeting();
            }

            $('#calls').load(location.href + ' #calls > *');
            Toast.fire({
                icon: 'success',
                title: 'Meeting ended successfully',
                timer: 3000
            });
        } catch (error) {
            console.error('Error occurred:', xhr.responseText);
            console.error('Error occurred:', status);
            console.error('Error occurred:', error);
            Toast.fire({
                icon: 'error',
                title: 'Error ending meeting',
                text: error.message
            });
        } finally {
            $btn.prop('disabled', false).html('End Meeting');
            feather.replace();
        }
    });

    $(document).on('click', '.delete-meeting-btn', function(e) {
        const btn = $(this);
        const room_id = btn.data('room-id');
        e.stopPropagation();

        $.ajax({
            url: `{{ route('admin.chat.removemeeting') }}`,
            type: 'POST',
            data: { room: room_id, delete: 1 },
            headers: { 'X-CSRF-TOKEN': token },
            success: function() {
                $('#calls').load(location.href + ' #calls > *');
                Toast.fire({
                    icon: 'success',
                    title: 'Meeting deleted',
                    timer: 3000
                });
            },
            error: function(xhr, error, status){
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    });
    //endregion

    $(window).on("beforeunload", function () {
        navigator.sendBeacon("{{ route('admin.chat.removeishere') }}");
    });
});
</script>
@endsection