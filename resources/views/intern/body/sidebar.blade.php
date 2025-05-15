<nav class="sidebar">
    <div class="sidebar-header">
        <a href="{{ route('intern.dashboard') }}" class="sidebar-brand">
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
            <a href="{{ route('intern.dashboard') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="grid"></i>
            <span class="link-title">Dashboard</span>
            </a>
        </li>
        @if(Auth::user()->department)
        <li class="nav-item">
            <a href="{{ route('intern.personal_table') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="table"></i>
            <span class="link-title">Personal Table</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('intern.tasks') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="file-text"></i>
            <span class="link-title">Tasks</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('intern.calendar') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="calendar"></i>
            <span class="link-title">Calendar</span>
            </a>
        <li class="nav-item">
            <a href="{{ route('intern.department') }}" class="nav-link">
            <i class="link-icon icon-wiggle" data-feather="server"></i>
            <span class="link-title">Department</span>
            </a>
        </li>
        @endif
        <li class="nav-item">
            <a href="{{ route('intern.chat') }}" class="nav-link">
                <i class="link-icon icon-wiggle" data-feather="message-square"></i>
                <span class="link-title">Chat</span>
            </a>
        </li>
    </div>
</nav>