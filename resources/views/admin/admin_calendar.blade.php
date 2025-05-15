@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content" id="page">
    <div class="row">
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-3 d-none d-md-block">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title mb-4">Calendar Events</h6>
                            <div class="row">
                                <div class="col-12 d-grid mb-2">
                                    <button type="button" class="btn convo-info-btn btn-sm" data-bs-toggle="collapse" data-bs-target="#collapsePrivate" aria-expanded="false" aria-controls="collapsePrivate">
                                        <i data-feather="user" class="cursor-pointer icon-sm icon-wiggle"></i> Private's Event
                                    </button>
                                </div>
                                <div class="col-12 mb-2 collapse"  id="collapsePrivate">
                                    <div id='private-events-external' class='external-events'>
                                        <h6 class="mb-2 text-muted">Private Events</h6>
                                        <div data-type="private" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Birthday Reminder</div>
                                        </div>
                                        <div data-type="private" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Personal Plan</div>
                                        </div>
                                        <div data-type="private" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Vacation Planning</div>
                                        </div>
                                        <div data-type="private" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Family Gathering</div>
                                        </div>
                                        <div data-type="private" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Party Event</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 d-grid mb-2">
                                    <button type="button" class="btn convo-info-btn btn-sm" data-bs-toggle="collapse" data-bs-target="#collapseAnnouncement" aria-expanded="false" aria-controls="collapseAnnouncement">
                                        <i data-feather="server" class="cursor-pointer icon-sm icon-wiggle"></i> Company Announcement Event
                                    </button>
                                </div>
                                <div class="col-12 collapse"  id="collapseAnnouncement">
                                    <div id='announcement-events-external' class='external-events'>
                                        <h6 class="mb-2 text-muted">Company Events</h6>
                                        <div data-type="announcement" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>All Department Head Meeting</div>
                                        </div>
                                        <div data-type="announcement" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Incoming New Project</div>
                                        </div>
                                        <div data-type="announcement" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Company Party</div>
                                        </div>
                                        <div data-type="announcement" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Company Activities</div>
                                        </div>
                                        <div data-type="announcement" class='fc-event fc-h-event fc-daygrid-event fc-daygrid-block-event'>
                                            <div class='fc-event-main'>Client Presentation Meetings</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-9">
                    <div class="card">
                        <div class="card-body">
                            <div id='fullcalendar'></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="fullCalModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 mb-0">
                    <h4 id="modalTitle1" class="modal-title"></h4>
                </div>
                <div id="modalBody1" class="modal-body m-0">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a class="btn btn-primary" id="eventUrl" href="#">View Page</a>
                </div>
            </div>
        </div>
    </div>

    <div id="createEventModal" class="modal fade">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 id="modalTitle2" class="modal-title">Add event</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><span class="visually-hidden">close</span></button>
                </div>
                <div id="modalBody2" class="modal-body">
                    <form id="eventForm">
                        <label for="eventTitle" class="form-label">Event Title</label>
                        <input type="text" id="eventTitle" placeholder="Event Title" class="form-control convo-info-text mb-2" required>
                        <label for="eventType" class="form-label">Event Type</label>
                        <select class="form-select convo-info-text mb-2" id="eventType" aria-label="Default select example">
                            <option selected value="" disabled>Open this select even type</option>
                            <option value="private">Private (Self Event)</option>
                            <option value="announcement">Announcment</option>
                        </select>
                        <label for="eventDescription" class="form-label">Event Description</label>
                        <textarea id="eventDescription" placeholder="Description" class="form-control convo-info-text mb-2"></textarea>
                        <div class="mb-2">
                            <label for="eventStart" class="form-label">Event Start</label>
                            <input type="datetime-local" id="eventStart" class="form-control convo-info-text" required>
                        </div>
                        <div class="mb-2">
                            <label for="eventEnd" class="form-label">Event End</label>
                            <input type="datetime-local" id="eventEnd" class="form-control convo-info-text" required>
                        </div>
                        <label for="eventColor" class="form-label">Event Color</label>
                        <div class="mb-2 d-flex">
                            <input type="color" id="eventColorHex" class="form-control form-control-color convo-info-text" value="#3a86ff">
                            <input type="text" name="color" id="eventColor" class="form-control convo-info-text ms-2" placeholder="rgb(58, 134, 255)" readonly>
                        </div>

                        <button type="submit" class="btn convo-info-btn float-end"><i data-feather="check" class="cursor-pointer icon-wiggle"></i> Save Event</button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
function page(){
    $('#page').load(location.href + " #page > *");
}

$(document).ready(function() {
    var baseUrl = "{{ url('/') }}";
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

    var Draggable = FullCalendar.Draggable;
    if (typeof calendar !== 'undefined') {
        calendar.destroy();
    }
    var calendarEl = document.getElementById('fullcalendar');
    var curYear = moment().format('YYYY');
    var curMonth = moment().format('MM');

    var hasDepartment = '{!! Auth::user()->department ? "yes" : "no"!!}';

    function setupDraggable(container) {
        new Draggable(container, {
            itemSelector: '.fc-event',
            eventData: function(eventEl) {
                let uniqueCode = `event-${Date.now()}-${Math.floor(Math.random() * 1000)}`;
                console.log("data-type:", eventEl.dataset.type);
                eventEl.dataset.uniqueCode = uniqueCode;
                return {
                    id: `${eventEl.dataset.type}-${uniqueCode}`,
                    title: eventEl.innerText,
                    type: eventEl.dataset.type,
                    color: eventEl.dataset.color || '#3a86ff',
                    borderColor: eventEl.dataset.border || '#2563eb',
                    extendedProps: {
                        source: eventEl.dataset.type || 'private',
                        name: `{{Auth::user()->name !== null ? Auth::user()->name : 'Unknown User'}}`,
                        photo: `{{Auth::user()->photo}}`,
                    }
                };
            }
        });
    }

    var containerPrivate = document.getElementById('private-events-external');
    setupDraggable(containerPrivate);

    var containerDepartment = document.getElementById('announcement-events-external');
    setupDraggable(containerDepartment);


    var calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            left: "prev,today,next",
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        droppable: true, // this allows things to be dropped onto the calendar
        fixedWeekCount: true,
        // height: 300,
        initialView: 'dayGridMonth',
        timeZone: 'Asia/Manila',
        hiddenDays:[],
        navLinks: 'true',
        // weekNumbers: true,
        // weekNumberFormat: {
        //   week:'numeric',
        // },
        dayMaxEvents: 3,
        events: [],
        eventSources: [
        ],
        events: function(fetchInfo, successCallback, failureCallback) {
            // Array to hold all events from different sources
            let allEvents = [];
            let requestsCompleted = 0;
            const totalRequests = 4; // Tasks, private events, department events

            // Function to check if all requests are done
            function checkCompletion() {
                requestsCompleted++;
                if (requestsCompleted === totalRequests) {
                    successCallback(allEvents);
                }
            }

            // 1. Fetch Tasks
            $.ajax({
                url: '{{route("admin.calendar.viewtaskdate")}}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    const taskEvents = response.map(event => ({
                        id: `task-${event.id}`,
                        title: event.title,
                        start: event.start,
                        end: event.end,
                        extendedProps: {
                            source: 'task-calendar',
                            task_id: event.id,
                            status: event.status,
                            type: event.type,
                            percentage: event.percentage,
                            department: event.department,
                            users: event.users,
                            myId: event.my_id,
                        },
                        color: event.status === 'Ongoing'
                            ? 'rgba(97, 97, 242, 0.25)'
                            : 'rgba(219, 80, 80, 0.25)',
                        borderColor: event.status === 'Ongoing'
                            ? 'rgb(97, 97, 242)'
                            : 'rgb(219, 80, 80)'
                    }));
                    allEvents = allEvents.concat(taskEvents);
                    checkCompletion();
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                    checkCompletion();
                }
            });

            // 2. Fetch Private Events
            $.ajax({
                url: '{{route("admin.calendar.viewprivateeventdate")}}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'nothing') {
                        const privateEvents = response.map(event => ({
                            id: `private-${event.id}`,
                            title: event.title,
                            type: event.type,
                            start: event.start,
                            end: event.end,
                            color: event.color,
                            borderColor: event.border,
                            extendedProps: {
                                source: event.type,
                                description: event.description,
                                name: event.user.name,
                                photo: event.user.photo,
                                event_id: event.id,
                                user_id: event.user_id
                            }
                        }));
                        allEvents = allEvents.concat(privateEvents);
                    }
                    checkCompletion();
                },
                error: function(xhr, status, error) {
                    console.error('Private Events AJAX Error:', error);
                    checkCompletion();
                }
            });

                // 3. Fetch Department Events
            $.ajax({
                url: '{{route("admin.calendar.viewdepartmenteventdate")}}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'nothing') {
                        const departmentEvents = response.map(event => {
                            const start = new Date(event.start);
                            const end = event.end ? new Date(event.end) : null;

                            return {
                                id: `department-${event.id}`,
                                title: event.title,
                                start: start,
                                end: end,
                                color: 'rgba(58, 134, 255, 0.25)',
                                borderColor: 'rgb(58, 134, 255)',
                                extendedProps: {
                                    source: 'department',
                                    description: event.description,
                                    name: event.user?.name || 'Unknown',
                                    photo: event.user?.photo,
                                    event_id: event.id,
                                    user_id: event.user_id
                                }
                            };
                        });
                        allEvents = allEvents.concat(departmentEvents);
                    }
                    checkCompletion();
                },
                error: function(xhr, status, error) {
                    console.error('Department Events AJAX Error:', error);
                    checkCompletion();
                }
            });


            $.ajax({
                url: '{{route("admin.calendar.viewannouncementeventdate")}}',
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.status !== 'nothing') {
                        const departmentEvents = response.map(event => {
                            const start = new Date(event.start);
                            const end = event.end ? new Date(event.end) : null;

                            return {
                                id: `announcement-${event.id}`,
                                title: '🎉 '+event.title,
                                start: start,
                                end: end,
                                color: 'rgba(58, 134, 255, 0.25)',
                                borderColor: 'rgb(58, 134, 255)',
                                extendedProps: {
                                    source: 'announcement',
                                    description: event.description,
                                    name: event.user?.name || 'Unknown',
                                    photo: event.user?.photo,
                                    event_id: event.id,
                                    user_id: event.user_id
                                }
                            };
                        });
                        allEvents = allEvents.concat(departmentEvents);
                    }
                    checkCompletion();
                },
                error: function(xhr, status, error) {
                    console.error('Department Events AJAX Error:', error);
                    checkCompletion();
                }
            });
        },
        eventReceive: function(info) {
            // info.event is the event that FullCalendar auto-added
            const droppedEvent = info.event;
            const eventType = droppedEvent.extendedProps?.type || 'default'; // Retrieve type safely
            console.log("Event Type:", eventType);
            const startDate = droppedEvent.start;
            const startDateISO = new Date(startDate.getTime() - (startDate.getTimezoneOffset() * 60000))
            .toISOString()
            .slice(0, 16);

            Swal.fire({
                title: `Add Event Details: ${droppedEvent.title}`,
                html: `
                    <form id="eventFormDrag">
                        <label for="eventTitle" class="form-label">Event Title</label>
                        <input type="text" id="eventTitleDrag" placeholder="Event Title" class="form-control convo-info-text mb-2" value="${droppedEvent.title}" required>
                        <input type="hidden" id="eventTypeDrag" value="${eventType}">

                        <label for="eventDescriptionDrag" class="form-label">Event Description</label>
                        <textarea id="eventDescriptionDrag" placeholder="Description" class="form-control convo-info-text mb-2"></textarea>

                        <div class="mb-2">
                            <label for="eventStart" class="form-label">Event Start</label>
                            <input type="datetime-local" id="eventStartDrag" class="form-control convo-info-text" value="${startDateISO}" required>
                        </div>

                        <div class="mb-2">
                            <label for="eventEnd" class="form-label">Event End</label>
                            <input type="datetime-local" id="eventEndDrag" class="form-control convo-info-text">
                        </div>

                        <label for="eventColor" class="form-label">Event Color</label>
                        <div class="mb-2 d-flex">
                            <input type="color" id="eventColorHexDrag" class="form-control form-control-color convo-info-text" value="#3a86ff">
                            <input type="text" id="eventColorDrag" class="form-control convo-info-text ms-2" placeholder="rgb(58, 134, 255)" readonly>
                        </div>

                        <!-- Hidden submit button to trigger form submission -->
                        <button type="submit" id="submitEventDrag" class="btn btn-primary" style="display: none;">Submit</button>
                    </form>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Add Event',
                cancelButtonText: 'Cancel',
                didOpen: () => {
                    document.getElementById('eventColorHexDrag').addEventListener('input',  function() {
                        const hex = this.value;
                        const rgb = hexToRgb(hex);
                        document.getElementById('eventColorDrag').value = rgb;
                    });
                    // Attach a submit event listener to the form
                    const form = document.getElementById('eventFormDrag');
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();
                        Swal.clickConfirm(); // Triggers the preConfirm and closes the modal
                    });

                    $('#eventFormDrag').submit(function(e) {
                        e.preventDefault();

                        const hex = document.getElementById('eventColorHexDrag').value;
                        const rgb = hexToRgb(hex);
                        const rgba = rgb.replace('rgb', 'rgba').replace(')', ', 0.25)'); // 0.2 opacity

                        var newEvent = {
                            title: $('#eventTitleDrag').val(),
                            type: $('#eventTypeDrag').val(),
                            start: $('#eventStartDrag').val(),
                            end: $('#eventEndDrag').val(),
                            color: rgba,
                            border: rgb,
                            description: $('#eventDescriptionDrag').val(),
                        };

                        $.ajax({
                            url: '{{route("admin.calendar.saveevent")}}',
                            type: 'POST',
                            data: newEvent, // Don't set content type
                            headers: {
                                'X-CSRF-TOKEN': token // Add CSRF token
                            },
                            success: function(response) {
                                if(response.status == 'success'){

                                    calendar.refetchEvents()
                                    $('#eventFormDrag')[0].reset();
                                    Toast.fire({
                                        icon: 'success',
                                        title: 'Event saved successfully'
                                    });
                                } else if(response.status == 'error'){
                                    Toast.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: '<ul>' + response.message.split('\n').map(line => `<li>${line}</li>`).join('') + '</ul>'
                                    });
                                    droppedEvent.remove();
                                }
                            },
                            error: function(xhr, status, error) {
                                console.error('Error occurred:', xhr.responseText);
                                console.error('Error occurred:', status);
                                console.error('Error occurred:', error);
                            }
                        });
                    });
                },
                preConfirm: () => {
                    document.getElementById('submitEventDrag').click(); // Manually trigger form submission
                    return false; // Prevent SweetAlert from closing automatically
                }
            }).then((result) => {
                if (result.isDismissed) {
                    droppedEvent.remove();
                }
            });
        },
        drop: function(info) {
            // No need to call info.revert(), as eventReceive handles the auto-add.
        },
        eventClick: function(info) {
            var event = info.event;

            if(event.extendedProps.source == 'task-calendar'){
                var task = event.extendedProps;
                $('#modalTitle1').html(event.title);
                var html_task = `
                    <div class="calendar-view-body p-0">
                        <div class="calendar-view-head row p-3 mb-2">
                            <div class="w-100 my-2 col-12 text-center"><p><b>${event.title}</b></p></div>
                            <span><b class="text-primary">Task Type</b>: ${task.type}</span>
                            <span><b class="text-primary">Task Status</b>: ${task.status}</span>
                            <div class="progress border border-dark" role="progressbar" aria-label="Default striped example" aria-valuenow="${task.percentage}" aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar progress-bar-striped progress-bar-animated" style="width: ${task.percentage}%">${task.percentage}%</div>
                            </div>
                        </div>
                        <div class="calendar-view-content pt-0">
                            <p>Department: ${task.department.name}</p>
                            <div class="input-group input-group-sm mb-2 convo-info-text" style="overflow: hidden;">
                                <span class="input-group-text">
                                    <i data-feather="search" class="cursor-pointer icon-wiggle"></i>
                                </span>
                                <input type="text" class="form-control form-control-sm" id="searchTaskUser" placeholder="Search here...">
                            </div>

                            `;
                            if(task.users.length > 0){
                                task.users.forEach(users => {
                                    html_task += `
                                    <div class="row m-0 p-0 calendar-view-user w-100" data-name="${users.name}">
                                        <div class="col-12 d-flex justify-content-start align-items-center mb-1">
                                            <img src="${users.photo !== null ? `/upload/photo_bank/${users.photo}` : '/upload/nophoto.jfif'}" alt="" class="rounded-circle" style="width: 50px; height: 50px">
                                            <p class="ms-2">${users.name}</p>
                                        </div>
                                        ${task.myId != users.user_id ? `
                                        <div class="col-12">
                                            <button type="button" class="btn convo-info-btn btn-sm float-end chatWithUser" data-user="${users.user_id}" data-name="${users.name}" data-task="${task.task_id}">
                                                <i data-feather="send" class="icon-sm cursor-pointer icon-wiggle"></i> Send Message
                                            </button>
                                        </div>` : ``}
                                    </div>`;
                                });
                            }
                            html_task += `
                        </div>
                    </div>
                `;
                $('#modalBody1').html(html_task);
                $('#eventUrl').html('View Task');
                $('#eventUrl').attr('href', '#test');
                $('#fullCalModal').modal("show");
                var observerTaskRoute = @json(route('admin.lvtasks', ['task' => '__TASK_ID__']));
                $('#fullCalModal').find('.modal-footer').html(`
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a class="btn btn-primary" id="eventUrl" href="${observerTaskRoute.replace('__TASK_ID__', task.task_id)}">View Page</a>
                `);
                feather.replace();
            }

            if(event.extendedProps.source == 'private' || event.extendedProps.source == 'department' || event.extendedProps.source == 'announcement'){
                var private = event.extendedProps;
                $('#modalTitle1').html(event.title);
                var html_task = `
                    <div class="calendar-view-body p-0"
                        <div class=" row p-3 mb-2">
                            <div class="col-12 d-flex justify-content-start align-items-center mb-1">
                                <img src="${private.photo !== null ? `/upload/photo_bank/${private.photo}` : '/upload/nophoto.jfif'}" alt="" class="rounded-circle" style="width: 50px; height: 50px">
                                <p class="ms-2">${private.name}</p>
                            </div>
                            <div class="w-100 my-2 col-12 text-center"><p><b>${event.title}</b></p></div>
                            ${private.description != null ?
                            `<div class="w-100 my-2 col-12 text-center"><span><b class="text-primary">Description</b>: ${private.description}</span></div>`
                            : ''}
                        </div>
                    </div>
                `;
                $('#modalBody1').html(html_task);
                $('#eventUrl').remove();
                $('#fullCalModal').modal("show");
                $('#fullCalModal').find('.modal-footer').html(`
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger removeEvent" data-event="${private.event_id}">Remove Event</button>
                `);

                feather.replace();
            }
        },
        dateClick: function(info) {
            $("#createEventModal").modal("show");
            setTimeout(() => {
                let dateStr = info.dateStr + "T00:00";
                console.log("Setting Event Start to:", dateStr);
                $('#eventStart').val(dateStr);
            }, 300);
        },
    });

    calendar.render();

    $(document).on('click', '.chatWithUser', function() {
        var user = $(this).data('user');
        var name = $(this).data('name');
        var task = $(this).data('task');
        $('#sendMessageChatModal').modal('show');
        $('#worktimeSettingsModalLabel').html(`Message ${name}`);
        $('#contact_id').val(user);
        $('#task_id').val(task);
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
                        html: `<ul><li>${reponse.message}</li></ul>`
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

    $(document).on('keyup', '#searchTaskUser', function () {
        let searchText = $(this).val().toLowerCase();

        $('.calendar-view-user').each(function () {
            let chatName = $(this).attr('data-name').toLowerCase();

            if (chatName.includes(searchText)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    document.getElementById('eventColorHex').addEventListener('input', function() {
        const hex = this.value;
        const rgb = hexToRgb(hex);
        document.getElementById('eventColor').value = rgb;
    });

    function hexToRgb(hex) {
        // Remove # if present
        hex = hex.replace('#', '');

        // Parse r, g, b values
        const r = parseInt(hex.substring(0, 2), 16);
        const g = parseInt(hex.substring(2, 4), 16);
        const b = parseInt(hex.substring(4, 6), 16);

        return `rgb(${r}, ${g}, ${b})`;
    }

    $('#eventForm').submit(function(e) {
        e.preventDefault();

        const hex = document.getElementById('eventColorHex').value;
        const rgb = hexToRgb(hex);
        const rgba = rgb.replace('rgb', 'rgba').replace(')', ', 0.25)'); // 0.2 opacity

        var newEvent = {
            title: $('#eventTitle').val(),
            type: $('#eventType').val(),
            start: $('#eventStart').val(),
            end: $('#eventEnd').val(),
            color: rgba,
            border: rgb,
            description: $('#eventDescription').val(),
        };

        const submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '{{route("admin.calendar.saveevent")}}',
            type: 'POST',
            data: newEvent, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function(response) {
                if(response.status == 'success'){

                    calendar.refetchEvents(); // Refresh calendar events
                    $('#createEventModal').modal('hide');
                    $('#eventForm')[0].reset();
                    Toast.fire({
                        icon: 'success',
                        title: 'Event saved successfully'
                    });
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `<ul><li>${response.message}</li></ul>`
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Save');
            }
        });
    });

    $(document).on('click', '.removeEvent', function() {
        var event = $(this).data('event');

        $.ajax({
            url: '{{route("admin.calendar.removeevent")}}',
            type: 'POST',
            data: {
                event: event
            }, // Don't set content type
            headers: {
                'X-CSRF-TOKEN': token // Add CSRF token
            },
            success: function(response) {
                if(response.status == 'success'){

                    calendar.refetchEvents(); // Refresh calendar events
                    $('#fullCalModal').modal('hide');
                    Toast.fire({
                        icon: 'success',
                        title: 'Event remove successfully'
                    });
                } else if(response.status == 'error'){
                    Toast.fire({
                        icon: 'error',
                        title: 'Error',
                        html: `<ul><li>${response.message}</li></ul>`
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error occurred:', xhr.responseText);
                console.error('Error occurred:', status);
                console.error('Error occurred:', error);
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-check"></i> Save');
            }
        });
    });
});

// npm package: fullcalendar
// github link: https://github.com/fullcalendar/fullcalendar

</script>
@endsection