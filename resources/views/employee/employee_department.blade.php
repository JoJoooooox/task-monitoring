@extends('employee.employee_dashboard')
@section('employee')
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
                            <div class="col my-3">
                                <h6 class="card-title mb-0">{{$dept->name}}</h6>
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
            data: formData,
            noLoading: true,
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
</script>
@endsection