<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<title>Head Forgot Password</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('../../../assets/vendors/core/core.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/css/demo1/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('../../../assets/images/favicon.png') }}" />
    <link rel="icon" href="{{ asset('upload/Tribo_Logo_Transparent.ico') }}" type="image/*" />
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style type="text/css">
        .authlogin-side-wrapper{
            width: 100%;
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            background-image: url({{ asset('upload/Software%20Delivery%20Illustration.jfif') }});

        }

        .feather-icon {
            width: 15px !important; /* Set icon size */
            height: 15px !important; /* Set icon size */
        }


    </style>
</head>
<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<div class="main-wrapper">
		<div class="page-wrapper full-page">
			<div class="page-content d-flex align-items-center justify-content-center">
				<div class="row w-100 mx-0 auth-page">
					<div class="col-md-8 col-xl-6 mx-auto">
						<div class="card">
							<div class="row">
                                <div class="col-md-4 pe-md-0">
                                <div class="authlogin-side-wrapper">

                                </div>
                                </div>
                                <div class="col-md-8 ps-md-0">
                                <div class="auth-form-wrapper px-4 py-5">
                                    <a href="#" class="noble-ui-logo logo-light d-block mb-2">Tribo<span>Corporation</span></a>
                                    <div class="choicesDiv">
                                        <h5 class="text-muted fw-normal mb-4">Choose forgot password method:</h5>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="radio" id="radioEmail">
                                            <label class="form-check-label" for="radioEmail">
                                            <i data-feather="mail" class="feather-icon"></i> Send OTP via Email
                                            </label>
                                        </div>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="radio" id="radioPhone">
                                            <label class="form-check-label" for="radioPhone">
                                            <i data-feather="smartphone" class="feather-icon"></i> Send OTP via SMS
                                            </label>
                                        </div>
                                        <a href="{{ route('head.login') }}" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Go Back
                                        </a>
                                    </div>
                                    <div class="emailDiv d-none">
                                        <h5 class="text-muted fw-normal mb-4">Enter your email in account:</h5>
                                        <div class="mb-3">
                                            <label for="emailInput" class="form-label">Email Address</label>
                                            <input type="text" class="form-control" id="emailInput" placeholder="Enter email address . . ." required autofocus>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0 sendOtpEmail">
                                            <i data-feather="send" class="feather-icon"></i> Send OTP
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0 goBackEmail">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Change Method
                                        </button>
                                    </div>
                                    <div class="phoneDiv d-none">
                                        <h5 class="text-muted fw-normal mb-4">Enter your phone number in account:</h5>
                                        <div class="mb-3">
                                            <label for="phoneInput" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="phoneInput" placeholder="Enter phone number . . ." required autofocus>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0 sendOtpPhone">
                                            <i data-feather="send" class="feather-icon"></i> Send OTP
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0 goBackPhone">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Change Method
                                        </button>
                                    </div>
                                    <div class="emailOtpDiv d-none">
                                        <h5 class="text-muted fw-normal mb-4">Enter the 6-digit OTP code sent to your email:</h5>
                                        <div class="mb-3">
                                            <input type="hidden" id="user_id_email" value="">
                                            <label for="emailOtpInput" class="form-label">OTP</label>
                                            <input type="text" class="form-control" id="emailOtpInput" placeholder="Enter otp . . ." required autofocus>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0 submitOtpEmail">
                                            <i data-feather="check-circle" class="feather-icon"></i> Submit
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0 goBackCancelOtpEmail">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Cancel OTP
                                        </button>
                                    </div>
                                    <div class="phoneOtpDiv d-none">
                                        <h5 class="text-muted fw-normal mb-4">Enter the 6-digit OTP code sent to your phone number:</h5>
                                        <div class="mb-3">
                                            <input type="hidden" id="user_id_phone" value="">
                                            <label for="phoneOtpInput" class="form-label">OTP</label>
                                            <input type="text" class="form-control" id="phoneOtpInput" placeholder="Enter otp . . ." required autofocus>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0 submitOtpPhone">
                                            <i data-feather="check-circle" class="feather-icon"></i> Submit
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0 goBackCancelOtpPhone">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Change Method
                                        </button>
                                    </div>
                                    <div class="newPasswordDiv d-none">
                                        <h5 class="text-muted fw-normal mb-4">Enter the 6-digit OTP code sent to your email:</h5>
                                        <input type="hidden" id="user_id" value="">
                                        <div class="mb-3">
                                            <label for="emailOtpInput" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="newPassword" placeholder="Enter new password . . ." required autofocus>
                                        </div>
                                        <div class="mb-3">
                                            <label for="emailOtpInput" class="form-label">Confirm Password</label>
                                            <input type="password" class="form-control" id="confirmPassword" placeholder="Enter confirm password . . ." required autofocus>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0 submitNewPassword">
                                            <i data-feather="check-circle" class="feather-icon"></i> Submit New Password
                                        </button>
                                        <button type="button" class="btn btn-primary btn-icon-text mb-2 mb-md-0 goBackCancelNewPassword">
                                            <i data-feather="arrow-left" class="feather-icon"></i> Cancel
                                        </button>
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

	<script src="{{ asset('../../../assets/vendors/core/core.js') }}"></script>
	<script src="{{ asset('../../../assets/vendors/feather-icons/feather.min.js') }}"></script>
	<script src="{{ asset('../../../assets/js/template.js') }}"></script>
    <script>
        feather.replace();  // This replaces <i data-feather="..."> with the corresponding SVG
        document.querySelectorAll('[data-feather]').forEach(function(icon) {
            icon.style.fontSize = "12px"; // Change the size as needed
        });

        $(document).ready(function() {
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

            $(document).on('change', '#radioPhone', function() {
                $('.choicesDiv').addClass('d-none');
                $('.phoneDiv').removeClass('d-none');
                $(this).prop('checked', false);
            });

            $(document).on('change', '#radioEmail', function() {
                $('.choicesDiv').addClass('d-none');
                $('.emailDiv').removeClass('d-none');
                $(this).prop('checked', false);
            });

            $(document).on('click', '.goBackEmail', function() {
                $('.emailDiv').addClass('d-none');
                $('.choicesDiv').removeClass('d-none');
            });

            $(document).on('click', '.goBackPhone', function() {
                $('.phoneDiv').addClass('d-none');
                $('.choicesDiv').removeClass('d-none');
            });

            $(document).on('click', '.sendOtpEmail', function() {
                var email = $('#emailInput').val();

                if (email === '') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>Please enter email before submitting</li></ul>'
                    });
                    return;
                }

                var $button = $(this);  // Store reference to the button

                $button.prop('disabled', true); // Disable the button

                Swal.fire({
                    title: 'Sending OTP',
                    html: 'Please wait while we send the OTP to your email...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("head.sendotpemail") }}', // Adjust route as necessary
                    method: 'POST',
                    data: {
                        email: email
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Toast.fire({ icon: 'success', title: `The OTP has been sent successfully to ${email}` });
                            $('.emailDiv').addClass('d-none');
                            $('.emailOtpDiv').removeClass('d-none');
                            $('#emailInput').val('');
                            $('#user_id_email').val(response.user_id);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                        }

                        $button.prop('disabled', false); // Enable the button after the AJAX call completes
                    },
                    error: function(xhr, error, status) {
                        Swal.close();
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);

                        $button.prop('disabled', false); // Enable the button in case of error as well
                    }
                });
            });

            $(document).on('click', '.sendOtpPhone', function() {
                var phone = $('#phoneInput').val();

                if (phone === '' || !/^\+?\d{10,15}$/.test(phone)) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>Please enter a valid phone number before submitting</li></ul>'
                    });
                    return;
                }

                var $button = $(this);  // Store reference to the button

                $button.prop('disabled', true); // Disable the button

                Swal.fire({
                    title: 'Sending OTP',
                    html: 'Please wait while we send the OTP to your phone...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route("head.sendotpphone") }}', // Adjust route as necessary
                    method: 'POST',
                    data: {
                        phone: phone
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.status === 'success') {
                            Toast.fire({ icon: 'success', title: `The OTP has been sent successfully to ${phone}` });
                            $('.phoneDiv').addClass('d-none');
                            $('.phoneOtpDiv').removeClass('d-none');
                            $('#phoneInput').val('');
                            $('#user_id_phone').val(response.user_id);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                        }

                        $button.prop('disabled', false); // Enable the button after the AJAX call completes
                    },
                    error: function(xhr, error, status) {
                        Swal.close();
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);

                        $button.prop('disabled', false); // Enable the button in case of error as well
                    }
                });
            });


            $(document).on('click', '.goBackCancelOtpEmail', function() {
                Swal.fire({
                    title: `Are you sure you want cancel?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes! I want to cancel it',
                    cancelButtonText: 'No, I don\'t want to'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Toast.fire({ icon: 'success', title: `Successfully cancelled` });
                        $('.emailDiv').addClass('d-none');
                        $('.emailOtpDiv').addClass('d-none');
                        $('.choicesDiv').removeClass('d-none');
                        $('#emailOtpInput').val('');
                    }
                });
            })

            $(document).on('click', '.submitOtpPhone', function() {
                var otp = $('#phoneOtpInput').val();
                var user_id_phone = $('#user_id_phone').val();

                if (otp === '') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>Please enter OTP before submitting</li></ul>'
                    });
                    return;
                }

                var $button = $(this);  // Store reference to the button

                $button.prop('disabled', true); // Disable the button

                $.ajax({
                    url: '{{ route("head.sendotpphone") }}', // Adjust route as necessary
                    method: 'POST',
                    data: {
                        otp: otp,
                        user_id_phone: user_id_phone
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Toast.fire({ icon: 'success', title: `Successfully matched the OTP` });
                            $('.phoneOtpDiv').addClass('d-none');
                            $('.newPasswordDiv').removeClass('d-none');
                            $('#user_id').val(response.user_id);
                            $('#phoneOtpInput').val('');
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                        }

                        $button.prop('disabled', false); // Enable the button after the AJAX call completes
                    },
                    error: function(xhr, error, status) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);

                        $button.prop('disabled', false); // Enable the button in case of error as well
                    }
                });
            })

            $(document).on('click', '.submitOtpEmail', function() {
                var otp = $('#emailOtpInput').val();
                var user_id_email = $('#user_id_email').val();

                if (otp === '') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>Please enter OTP before submitting</li></ul>'
                    });
                    return;
                }

                var $button = $(this);  // Store reference to the button

                $button.prop('disabled', true); // Disable the button

                $.ajax({
                    url: '{{ route("head.sendotpemail") }}', // Adjust route as necessary
                    method: 'POST',
                    data: {
                        otp: otp,
                        user_id_email: user_id_email
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Toast.fire({ icon: 'success', title: `Successfully matched the OTP` });
                            $('.emailOtpDiv').addClass('d-none');
                            $('.newPasswordDiv').removeClass('d-none');
                            $('#user_id').val(response.user_id);
                            $('#emailOtpInput').val('');
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                        }

                        $button.prop('disabled', false); // Enable the button after the AJAX call completes
                    },
                    error: function(xhr, error, status) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);

                        $button.prop('disabled', false); // Enable the button in case of error as well
                    }
                });
            });

            $(document).on('click', '.goBackCancelNewPassword', function() {
                Swal.fire({
                    title: `Are you sure you want cancel?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes! I want to cancel it',
                    cancelButtonText: 'No, I don\'t want to'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Toast.fire({ icon: 'success', title: `Successfully cancelled` });
                        $('.emailDiv').addClass('d-none');
                        $('.emailOtpDiv').addClass('d-none');
                        $('.newPasswordDiv').addClass('d-none');
                        $('.choicesDiv').removeClass('d-none');
                        $('#newPassword').val('');
                        $('#confirmPassword').val('');
                    }
                });
            });

            $(document).on('click', '.submitNewPassword', function() {
                var newPass = $('#newPassword').val();
                var confirmPass = $('#confirmPassword').val();
                var user_id = $('#user_id').val();

                if (newPass === '' || confirmPass === '') {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>Please enter value before submitting</li></ul>'
                    });
                    return;
                }

                if (newPass !== confirmPass) {
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: '<ul><li>New and Confirm Password didn\'t match please try again</li></ul>'
                    });
                    return;
                }

                var $button = $(this);  // Store reference to the button

                $button.prop('disabled', true); // Disable the button


                $.ajax({
                    url: '{{ route("head.submitnewpass") }}', // Adjust route as necessary
                    method: 'POST',
                    data: {
                        new_pass: newPass,
                        user_id: user_id,
                        set: 1
                    },
                    headers: {
                        'X-CSRF-TOKEN': token // Add CSRF token
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            $('.newPasswordDiv').addClass('d-none');
                            $('#newPassword').val('');
                            $('#confirmPassword').val('');

                            Swal.fire({
                                title: `Successfully change password, what would you like to do next?`,
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Direct login',
                                cancelButtonText: 'Go to login page'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $.ajax({
                                        url: '{{ route("head.submitnewpass") }}', // Adjust route as necessary
                                        method: 'POST',
                                        data: {
                                            login: response.login,
                                            password: response.password,
                                            redirect: 1
                                        },
                                        headers: {
                                            'X-CSRF-TOKEN': token // Add CSRF token
                                        },
                                        success: function(response) {
                                            if (response.status === 'success') {
                                                Swal.fire({
                                                    title: 'Successfully changed the password!',
                                                    text: 'You will be redirected to the dashboard page shortly.',
                                                    icon: 'success',
                                                    timer: 5000, // 5 seconds in milliseconds
                                                    timerProgressBar: true, // Shows a progress bar
                                                    didClose: () => {
                                                        window.location.href = '{{ route("user.login") }}'; // Redirect after 5 seconds
                                                    }
                                                });
                                            }
                                        },
                                        error: function(xhr, error, status) {
                                            console.error('Error occurred:', xhr.responseText);
                                            console.error('Error occurred:', status);
                                            console.error('Error occurred:', error);

                                            $button.prop('disabled', false); // Enable the button in case of error as well
                                        }
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Successfully changed the password!',
                                        text: 'You will be redirected to the login page shortly.',
                                        icon: 'success',
                                        timer: 5000, // 5 seconds in milliseconds
                                        timerProgressBar: true, // Shows a progress bar
                                        didClose: () => {
                                            window.location.href = '{{ route("user.login") }}'; // Redirect after 5 seconds
                                        }
                                    });
                                }
                            });
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: 'Error',
                                html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                            });
                        }

                        $button.prop('disabled', false); // Enable the button after the AJAX call completes
                    },
                    error: function(xhr, error, status) {
                        console.error('Error occurred:', xhr.responseText);
                        console.error('Error occurred:', status);
                        console.error('Error occurred:', error);

                        $button.prop('disabled', false); // Enable the button in case of error as well
                    }
                });
            });
        });
    </script>
</body>
</html>