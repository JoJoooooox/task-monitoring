<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">

	<title>Observer Log-In</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="{{ asset('../../../assets/vendors/core/core.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('../../../assets/css/demo1/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('../../../assets/images/favicon.png') }}" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="{{ asset('upload/Tribo_Logo_Transparent.ico') }}" type="image/*" />
    <style type="text/css">
        .authlogin-side-wrapper{
            width: 100%;
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            background-image: url({{ asset('upload/Software%20Delivery%20Illustration.jfif') }});

        }
    </style>
</head>
<body>
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
                                    <h5 class="text-muted fw-normal mb-4">Welcome back! Log in to your account.</h5>
                                    <form class="forms-sample" method="POST" action="{{ route('login') }}">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="login" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="login" placeholder="Username / Email Address / Phone Number" name="login" :value="old('login')" required autofocus autocomplete="username">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" id="password" autocomplete="current-password" placeholder="Password">
                                    </div>
                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="authCheck">
                                        <label class="form-check-label" for="authCheck">
                                        Remember me
                                        </label>
                                    </div>
                                    <div>
                                        <button type="submit" class="btn btn-outline-primary btn-icon-text mb-2 mb-md-0">
                                            Login
                                        </button>
                                        <a href="{{ route('observer.forgot') }}" class="btn btn-primary btn-icon-text mb-2 mb-md-0">
                                            Forgot Password
                                        </a>
                                    </div>
                                    </form>
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
    @if(session('login_error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            });

            Toast.fire({
                icon: 'error',
                title: '{{ session('login_error') }}'
            });
        });
    </script>
    @endif
</body>
</html>