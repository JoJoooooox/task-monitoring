<style>
img{
    object-fit: cover; /* Ensures the image covers the container */
    object-position: center;
}
</style>
<nav class="navbar">
    <a href="#" class="sidebar-toggler">
        <i data-feather="menu"></i>
    </a>
    <div class="navbar-content">
        <form class="search-form">
            <div class="input-group">
                <div class="input-group-text">
                    <i data-feather="search"></i>
                </div>
                <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
            </div>
        </form>
        <ul class="navbar-nav">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle icon-wiggle markAsReadNotification" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i data-feather="bell"></i>
                    <div class="indicator allNotificationIndicator d-none">
                        <div class="circle"></div>
                    </div>
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="notificationDropdown">
                    <div class="px-3 py-2 d-flex align-items-center justify-content-between border-bottom">
                        <p id="allNotificationCount"></p>
                        <a href="javascript:;" class="text-muted clearMyNotification">Clear all</a>
                    </div>
                    <div class="p-1" id="allNotificationOutput" style="max-height: 600px; overflow-y:auto; overflow-x:hidden">

                    </div>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <img class="wd-30 ht-30 rounded-circle" src="{{ (!empty(Auth::user()->photo)) ? url('upload/photo_bank/'.Auth::user()->photo) : url('upload/nophoto.jfif') }}" alt="profile">
                </a>
                <div class="dropdown-menu p-0" aria-labelledby="profileDropdown">
                    <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                        <div class="mb-3">
                            <img class="wd-80 ht-80 rounded-circle" src="{{ (!empty(Auth::user()->photo)) ? url('upload/photo_bank/'.Auth::user()->photo) : url('upload/nophoto.jfif') }}" alt="">
                        </div>
                        <div class="text-center">
                            <p class="tx-16 fw-bolder">{{ Auth::user()->name }}</p>
                            <p class="tx-12 text-muted">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <ul class="list-unstyled p-1">
                        <li class="dropdown-item py-2">
                            <a href="{{ route('intern.profile') }}" class="text-body ms-0">
                                <i class="me-2 icon-md icon-wiggle" data-feather="user"></i>
                                <span>Profile</span>
                            </a>
                        </li>
                        <li class="dropdown-item py-2">
                            <a href="{{ route('intern.logout') }}" class="text-body ms-0">
                                <i class="me-2 icon-md icon-wiggle" data-feather="log-out"></i>
                                <span>Log Out</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
        </ul>
    </div>
</nav>

<script>
$(document).ready(function () {
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

    let lastNotifId = 0;
    let totalCount = 0;

    function getAllNotif(){
        $.ajax({
            url: `{{ route('intern.getallnotification') }}`,
            type: 'GET',
            dataType: 'json',
            data: { last_id: lastNotifId },
            success: function(response) {
                var notif_html = '';
                if(response.status === 'exist' && response.notif.length > 0){
                    // Reverse to prepend oldest new notification first
                    var reversedNotif = response.notif;
                    reversedNotif.forEach(noti => {
                        notif_html += `
                        <a href="javascript:;" class="dropdown-item d-flex align-items-center py-2" style="max-width: 300px;">
                            <div class="wd-30 ht-30 d-flex align-items-center justify-content-center bg-primary rounded-circle me-3 icon-wiggle">
                                <i class="icon-sm text-white" data-feather="${
                                    noti.type === 'info' ? 'info' :
                                    noti.type === 'warning' ? 'alert-triangle' :
                                    noti.type === 'success' ? 'check-circle' :
                                    noti.type === 'error' ? 'cloud-off' :
                                    'bell' // default icon
                                }"></i>
                            </div>
                            <div class="flex-grow-1 me-2">
                                <p class="text-truncate" style="max-width: 210px;" title="${noti.message}">${noti.message}</p>
                                <p class="tx-12 text-muted">${noti.timeago}</p>
                            </div>
                        </a>
                        `;
                    });
                    $('#allNotificationOutput').prepend(notif_html);
                    feather.replace(); // Refresh icons

                    // Update last ID and total count
                    lastNotifId = response.notif[0].id;
                    totalCount += response.notif.length;
                    $('#allNotificationCount').html(`${totalCount} Notifications`);

                    // Update unread indicator
                    if(response.has_unread) {
                        $('.allNotificationIndicator').removeClass('d-none');
                    } else {
                        $('.allNotificationIndicator').addClass('d-none');
                    }
                } else if(response.status === 'exist') {
                    // No new notifications, update indicator if needed
                    if(response.has_unread) {
                        $('.allNotificationIndicator').removeClass('d-none');
                    } else {
                        $('.allNotificationIndicator').addClass('d-none');
                    }
                }
            },
            error: function(xhr, error, status) {
                console.error('Error:', xhr.responseText);
            }
        });
    }

    // Initial fetch
    getAllNotif();

    // Poll every 60 seconds
    setInterval(getAllNotif, 3000);

    $(document).on('click', '.markAsReadNotification', function() {
        $.ajax({
            url: `{{ route('intern.markasreadednotification') }}`,
            type: 'POST',
            noLoading: true,
            data: {
                read: 1
            },
            headers: {
                'X-CSRF-TOKEN': token
            },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    $('.allNotificationIndicator').addClass('d-none');
                }
            },
            error: function(xhr, error, status){
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    })

    $(document).on('click', '.clearMyNotification', function() {
        $.ajax({
            url: `{{ route('intern.clearnotification') }}`,
            type: 'POST',
            noLoading: true,
            data: {
                read: 1
            },
            headers: {
                'X-CSRF-TOKEN': token
            },
            dataType: 'json',
            success: function(response) {
                if(response.status == 'success'){
                    $('.allNotificationIndicator').addClass('d-none');
                }
            },
            error: function(xhr, error, status){
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            }
        });
    })
});
</script>