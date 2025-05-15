<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        TRI<span>BO</span>
        </a>
        <div class="sidebar-toggler not-active">
        <span></span>
        <span></span>
        <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
        <li class="nav-item nav-category">Main</li>
        <li class="nav-item">
            <a href="{{ route('admin.dashboard') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="grid"></i>
            <span class="link-title">Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.tasks') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="file-text"></i>
            <span class="link-title">Tasks</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.calendar') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="calendar"></i>
            <span class="link-title">Calendar</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.department') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="server"></i>
            <span class="link-title">Department</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.chat') }}" class="nav-link">
                <i class="link-icon icon-wiggle" data-feather="message-square"></i>
                <span class="link-title">Chat</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.users') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="users"></i>
            <span class="link-title">Users Account</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.log') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="book"></i>
            <span class="link-title">System Log</span>
            </a>
        </li>
    </div>
</nav>