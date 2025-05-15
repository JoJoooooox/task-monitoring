@extends('head.head_dashboard')
@section('head')

<style type="text/css">
        .card-photo{
            object-fit: cover; /* Ensures the image covers the container */
            object-position: center;
            width: 100%;
            height: 400px;
        }

        .card-top{
            background-color: #e6e6e6;
            box-shadow: 0 4px 6px -2px rgba(0, 0, 0, 0.5);
        }
</style>
<div class="page-content" id="page">
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card card-img rounded-3 div-hover">
                                <div class="card-body card-top rounded-top">
                                    <img class="card-photo rounded-3 shadow" src="{{ (!empty($profile->photo)) ? url('upload/photo_bank/'.$profile->photo) : url('upload/nophoto.jfif') }}" alt="" id="showImage">
                                </div>
                                <div class="card-body text-center">
                                <form id="photoForm" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row">
                                        <div class="col-12">
                                            <input type="file" id="image" name="photo" class="form-control mb-1"></input>
                                            <button type="button" name="photoSubmit" class="btn btn-primary mb-1 btn-hover">Save Changes</button>
                                        </div>
                                    </div>
                                </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 mb-3">
                            <h6 class="card-title">Profile Information</h6>
                            <form id="profileForm" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="username" class="form-control" id="username" aria-describedby="username" value="{{ $profile->username }}" disabled>
                                    <div id="username" class="form-text">We'll never share your username with anyone else.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email address</label>
                                    <input type="email" name="email" class="form-control" id="email" aria-describedby="email" value="{{ $profile->email }}" required>
                                    <div id="email" class="form-text">We'll never share your email with anyone else.</div>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="phone" name="phone" class="form-control" id="phone" aria-describedby="phone" value="{{ $profile->phone }}" required>
                                    <div id="phone" class="form-text">We'll never share your phone number with anyone else.</div>
                                </div>
                                <button type="button" name="profileSubmit" class="btn btn-primary float-end btn-hover">Save Changes</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Basic Information</h6>
                    <form id="basicForm" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Name</label>
                                    <input type="name" name="name" class="form-control numBlocker" id="name" aria-describedby="name" value="{{ $profile->name }}">
                                    <div id="name" class="form-text">We'll never share your name with anyone else.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3 me-2 mb-2 mb-md-0" id="dashboardDate">
                                    <label for="birthdate" class="form-label">Birth Date</label>
                                    <input type="date"
                                        name="birthdate"
                                        class="form-control"
                                        id="birthdate"
                                        value="{{ $profile->birthdate }}"
                                        max="{{ now()->subYears(18)->format('Y-m-d') }}"
                                        min="{{ now()->subYears(100)->format('Y-m-d') }}">
                                    <div id="birthdate" class="form-text">
                                        Must be between 18-100 years old. We'll never share your birth date.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="address" name="address" class="form-control" id="address" aria-describedby="address" value="{{ $profile->address }}">
                            <div id="address" class="form-text">We'll never share your address with anyone else.</div>
                        </div>
                        <button type="button" name="basicSubmit" class="btn btn-primary float-end btn-hover">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">Update Password</h6>
                    <form id="passForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" name="password" id="password" required>
                            <div class="form-text">Make sure that what you enter is your current password.</div>
                        </div>

                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" name="newPassword" id="newPassword" required>
                            <div class="form-text">Enter your new password. Use special characters like ( @, !, # )</div>
                            <div class="password-strength mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="password-strength-text" class="text-muted">Password strength</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirmPassword" id="confirmPassword" required>
                            <div class="form-text">Confirm the new password.</div>
                            <small id="password-match-text" class="d-block mt-1"></small>
                        </div>

                        <button type="button" name="passSubmit" class="btn btn-primary float-end btn-hover">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
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

<script>
$(document).ready(function() {
    // Password strength indicator
    // Password strength indicator
    $('#newPassword').on('input', function() {
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
        const match = $(this).val() === $('#newPassword').val();
        $('#password-match-text')
            .text(match ? '✓ Passwords match' : '✗ Passwords do not match')
            .toggleClass('text-success', match)
            .toggleClass('text-danger', !match);
    });

    function checkPasswordStrength(password) {
        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        if (strength <= 2) return { percentage: 33, class: 'bg-danger', text: 'Weak', textClass: 'text-danger' };
        if (strength <= 4) return { percentage: 66, class: 'bg-warning', text: 'Moderate', textClass: 'text-warning' };
        return { percentage: 100, class: 'bg-success', text: 'Strong', textClass: 'text-success' };
    }
});
</script>

<script type="text/javascript">
    function page(){
        $('#page').load(location.href + " #page > *");
    }

    $(document).ready(function(){
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
        $('#image').on('change', function(e){
            $('#showImage').attr('src', URL.createObjectURL(e.target.files[0]));
        });

        let token = $('meta[name="csrf-token"]').attr('content');

        $(document).on('click', '#photoForm button[name="photoSubmit"]', function(){
            let form = $('#photoForm')[0];
            let formData = new FormData(form);

            // AJAX request
            $.ajax({
                url: '{{ route("head.profile.photo") }}', // The route defined in Laravel
                type: 'POST', // Method type
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                },
                contentType: false, // Prevent jQuery from setting the content type header
                processData: false,
                success: function(response) {
                    if(response.status === 'success') {
                        Toast.fire({
                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Successfully save changes in profile picture'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.log(xhr.responseText);
                }
            });
        });

        $(document).on('click', '#profileForm button[name="profileSubmit"]', function() {
            $.ajax({
                url: '{{ route("head.profile.pinfo") }}', // The route defined in Laravel
                type: 'POST', // Method type
                data: $('#profileForm').serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                },
                success: function(response) {
                    if(response.status === 'success') {
                        Toast.fire({
                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Successfully save changes in profile information'
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
                    } else if(response.status === 'error'){
                        Toast.fire({
                            icon: 'error',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Error',
                            html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                }
            });
        });

        $('.numBlocker').on('keypress', function(e) {
            // Get the key code of the pressed key
            let charCode = e.which || e.keyCode;

            // If the character is a number (48-57), prevent input
            if (charCode >= 48 && charCode <= 57) {
                e.preventDefault(); // Prevent the input of the number
            }
        });

        $(document).on('click', '#basicForm button[name="basicSubmit"]', function() {
            $.ajax({
                url: '{{ route("head.profile.binfo") }}', // The route defined in Laravel
                type: 'POST', // Method type
                data: $('#basicForm').serialize(),
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                },
                success: function(response) {
                    if(response.status === 'success') {
                        Toast.fire({
                            icon: 'success',  // Can be 'success', 'error', 'warning', 'info', or 'question'
                            title: 'Successfully save changes in basic information'
                        });
                    } else {
                    // Handle other response statuses if needed
                        Toast.fire({
                            icon: 'warning',
                            title: 'An error occurred'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    // Handle errors
                    console.error(xhr);
                    console.error(status);
                    console.error(error);
                }
            });
        });


        $(document).on('click', 'button[name="passSubmit"]', function(e) {
            e.preventDefault();
            const form = $('#passForm');
            const submitBtn = $(this);

            $.ajax({
                url: '{{ route("head.profile.update") }}',
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        Toast.fire({
                            icon: 'success',
                            title: response.message
                        });
                        form.trigger('reset');
                    } else if (response.errors) {
                        Toast.fire({
                            icon: 'error',
                            title: response.errors.join('\n')
                        });
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    console.error(status);
                    console.error(error);
                },
            });
        });
    });
</script>
@endsection