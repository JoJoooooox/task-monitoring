<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
  	<meta name="description" content="Realtime Task Monitoring">
	<meta name="author" content="JosephU">
	<meta name="keywords" content="nobleui, bootstrap, bootstrap 5, bootstrap5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<title>Admin Dashboard</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" rel="stylesheet">
  <!-- End fonts -->

	<!-- core:css -->
	<link rel="stylesheet" href="{{ asset('assets/vendors/core/core.css') }}">
	<!-- endinject -->

	<!-- Plugin css for this page -->
	<link rel="stylesheet" href="{{ asset('assets/vendors/fullcalendar/main.min.css')}}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/flatpickr/flatpickr.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/owl.carousel/owl.carousel.min.css') }}">
  	<link rel="stylesheet" href="{{ asset('assets/vendors/owl.carousel/owl.theme.default.min.css') }}">
  	<link rel="stylesheet" href="{{ asset('assets/vendors/animate.css/animate.min.css') }}">
	<!-- End plugin css for this page -->

	<!-- inject:css -->
	<link rel="stylesheet" href="{{ asset('assets/fonts/feather-font/css/iconfont.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/flag-icon-css/css/flag-icon.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/mdi/css/materialdesignicons.min.css') }}">
	<!-- endinject -->

  <!-- Layout styles -->
	<link rel="stylesheet" href="{{ asset('assets/css/demo1/style.css') }}">
  <!-- End layout styles -->

	<link rel="shortcut icon" href="{{ asset('assets/images/favicon.png') }}" />

	<link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet"/>
	<link rel="stylesheet" href="{{ asset('assets/vendors/morris.js/morris.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/custom-css/observer.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css') }}">
	<link rel="icon" href="{{ asset('upload/Tribo_Logo_Transparent.ico') }}" type="image/*" />
</head>
<body>
	<script src="https://meet.jit.si/external_api.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
	<script src="{{ asset('assets/vendors/raphael/raphael.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/morris.js/morris.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/jquery-mousewheel/jquery.mousewheel.js') }}"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
	<script src="https://unpkg.com/grapesjs"></script>
	<script src="https://kit.fontawesome.com/5ed124e146.js" crossorigin="anonymous"></script>
	<script src="{{ asset('assets/tinymce/js/tinymce/tinymce.min.js') }}"></script>


	<div class="main-wrapper">
		<div id="allNotesDisplay" style="z-index: 1000;">

		</div>
		<div id="allFeedBackDisplay" style="z-index: 1000;">

		</div>
		<!-- partial:partials/_sidebar.html -->
        @include('admin.body.sidebar')

        <div class="page-wrapper">

            <!-- partial:partials/_navbar.html -->
            @include('admin.body.header')
            <!-- partial -->

            @yield('admin')

            <!-- partial:partials/_footer.html -->
            @include('admin.body.footer')
            <!-- partial -->

        </div>
		<div id="allChatsDisplay" style="z-index: 1000;">

		</div>
	</div>

	<!-- core:js -->
	<script src="{{ asset('assets/vendors/core/core.js') }}"></script>
	<!-- endinject -->

	<!-- Plugin js for this page -->
    <script src="{{ asset('assets/vendors/moment/moment.min.js')}}"></script>
	<script src="{{ asset('assets/vendors/fullcalendar/main.min.js')}}"></script>
    <script src="{{ asset('assets/vendors/flatpickr/flatpickr.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/chartjs/Chart.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/apexcharts/apexcharts.min.js') }}"></script>
	<script src="{{ asset('assets/vendors/owl.carousel/owl.carousel.min.js') }}"></script>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	<script src="{{ asset('assets/vendors/datatables.net/jquery.dataTables.js') }}"></script>
	<script src="{{ asset('assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js') }}"></script>
	<script src="{{ asset('assets/vendors/sortablejs/Sortable.min.js')}}"></script>
	<!-- End plugin js for this page -->

	<!-- inject:js -->
	<script src="{{ asset('assets/vendors/feather-icons/feather.min.js') }}"></script>
	<script src="{{ asset('assets/js/template.js') }}"></script>
	<!-- endinject -->

	<!-- Custom js for this page -->
    <script src="{{ asset('assets/js/dashboard-light.js') }}"></script>
	<script src="{{ asset('assets/js/morrisjs-light.js') }}"></script>
	<script src="{{ asset('assets/custom-js/admin.js') }}"></script>
	<script src="{{ asset('assets/vendors/chartjs/Chart.min.js') }}"></script>
  	<script src="{{ asset('assets/js/chartjs-light.js') }}"></script>

	<script>
	$(document).ready(function () {
        let inactivityTime = 30 * 60 * 1000; // 30 minutes
        let logoutTime = 60 * 1000; // 1 minute after alert
        let timeout;
        let logoutTimer;
		const tokenDashboard = $('meta[name="csrf-token"]').attr('content');
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
        function resetTimer() {
            clearTimeout(timeout);
            clearTimeout(logoutTimer);

            timeout = setTimeout(function () {
                Swal.fire({
                    title: "Are you still here?",
                    text: "You will be logged out in 1 minute if you do not respond.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "I'm here!",
                    cancelButtonText: "Logout",
                    timer: logoutTime,
                    timerProgressBar: true
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.timer || result.isDismissed) {
                        forceLogout();
                    }
                });

                logoutTimer = setTimeout(forceLogout, logoutTime);
            }, inactivityTime);
        }

        function forceLogout() {
            window.location.href = "/admin/logout"; // Update with your logout route
        }

		setInterval(() => {
			checkUrlUser();
		}, 3000);
		checkUrlUser();

		function checkUrlUser() {
			// Check if navigating to a different URL (not 100% reliable)
			const currentUrl = window.location.href;
			const allowedUrl = 'https://tribo.uno/admin/chat';

			if (currentUrl !== allowedUrl) {
				$.ajax({
					url: "{{ route('admin.chat.unsetchatishere') }}",
					type: "POST",
					noLoading: true,
					headers: {
                        'X-CSRF-TOKEN': tokenDashboard
                    },
					dataType: 'json',
					success: function(response) {
						if(response.status === 'success'){
							return;
						}
					},
					error: function(xhr) {
						console.log('AJAX Error:', xhr.responseText);
					}
				});
			} else {
				$.ajax({
					url: "{{ route('admin.chat.setchatishere') }}",
					type: "POST",
					noLoading: true,
					headers: {
                        'X-CSRF-TOKEN': tokenDashboard
                    },
					dataType: 'json',
					success: function(response) {
						if(response.status === 'success'){
							return;
						}
					},
					error: function(xhr) {
						console.log('AJAX Error:', xhr.responseText);
					}
				});
			}
		}

        // Detect activity and reset the timer
        $(document).on("mousemove keypress click scroll", function () {
            resetTimer();
        });

        resetTimer(); // Initialize timer on page load
		const profileUrl = 'https://tribo.uno/admin/profile';

		const chatcurrentUrl = window.location.href;
		const chatallowedUrl = 'https://tribo.uno/admin/chat';

		if(chatcurrentUrl != chatallowedUrl){
			let swalLoading = null;

			$(document).ajaxSend(function(event, jqXHR, ajaxOptions) {
				// Skip if:
				// 1. Not a POST request, OR
				// 2. The request has `noLoading: true`
				if (
					!ajaxOptions.type ||
					ajaxOptions.type.toUpperCase() !== 'POST' ||
					ajaxOptions.noLoading === true
				) {
					return;
				}

				swalLoading = Swal.fire({
					title: 'Processing...',
					html: 'Please wait...',
					allowOutsideClick: false,
					didOpen: () => Swal.showLoading()
				});
			});

			$(document).ajaxComplete(function() {
				if (swalLoading) {
					swalLoading.close();
					swalLoading = null;
				}
			});
		}

		if(chatcurrentUrl != chatallowedUrl && chatcurrentUrl !== profileUrl){
			//region All Chat
			function getAllChats(){
				$.ajax({
					url: `{{ route('admin.getallchats') }}`,
					type: 'GET',
					success: function(response) {
						if(response.status === 'exist'){
							var chat_html = `
							<button class="btn dashboard-chat-btn show"><i class="mdi mdi-message-outline icon-wiggle" style="font-size: 19px;"></i></button>
							<div class="dashboard-chat-container">
								<div class="dashboard-chat-container-action d-flex">
									<div class="input-group dashboard-chat-search">
										<input type="text" class="form-control" id="searchAllChats" placeholder="Search here...">
									</div>
									<button class="btn dashboard-chat-container-btn ms-2"><i class="mdi mdi-window-minimize icon-wiggle" style="font-size: 15px;"></i></button>
								</div>
								<div class="dashboard-chat-items">`;
									if(response.chats.length > 0){
										response.chats.forEach(chat => {
											chat_html += `
											<div class="dashboard-chat-content" id="dashboardChatList_${chat.chat_id}" data-chat="${chat.chat_id}" id="chat-div-${chat.chat_id}" data-name="${chat.name}">
												<div class="dashboard-chat-profile text-center me-2">
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
												</div>
												<div class="dashboard-chat-body">
													<div class="dashboard-chat-body-content">
														<p class="text-truncate">${chat.name}</p>
														<span class="text-muted text-truncate" style="font-size: 12px">
															${chat.unseen_count > 0 ? '<b>' : ''}
																${chat.from_message === chat.auth_id
																	? 'You :'
																	: (chat.type === 'group' ? 'Message :' : chat.name + ' :')}
																${chat.last_message !== null ? chat.last_message : 'Sent Attachment'}
															${chat.unseen_count > 0 ? '</b>' : ''}
														</span>
													</div>
													<span class="text-muted tx-13">${chat.last_message_time}</span>
												</div>
											</div>
											`;
										});
									}

							chat_html += `
								</div>`;
							if(response.convos.length > 0){
								response.convos.forEach(convo => {
									var chat_info = convo.chat_info;
									chat_html += `
									<div class="dashboard-chat-box" id="chat-box-${convo.chat_id}" data-chat="${convo.chat_id}">
										<div class="dashboard-chat-box-header">
											<div class="dashboard-chat-input-group">
												<button class="btn dashboard-chat-box-btn"><i class="mdi mdi-subdirectory-arrow-left icon-wiggle" style="font-size: 15px;"></i></button>
												<div class="dashboard-chat-input-group-text">`;
												if(convo.otherPart){
													convo.otherPart.forEach((other, index) => {
													chat_html += `
													<div class="dashboard-chat-input-profile text-center me-2">
														${other.type === 'group' && Array.isArray(other.other_photo) ?
															(convo.convo_photo === null ? `
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
															<img src="${convo.convo_photo ? `/${convo.convo_photo}` : `/upload/nophoto.jfif`}"
																class="img-xs rounded-circle border" alt="user" style="object-fit: cover; object-position: center;">
															`)
														: `
															<img src="${other.other_photo ? `/upload/photo_bank/${other.other_photo}` : `/upload/nophoto.jfif`}"
																class="img-xs rounded-circle border" alt="user" style="object-fit: cover; object-position: center;">
														`}
													</div>
													<div class="dashboard-chat-input-body">
														<p>${other.other_name}</p>
													</div>`;
													});
												}
												chat_html += `
												</div>
											</div>
										</div>
										<div class="dashboard-chat-box-body" id="dashboardMessageContainer_${convo.chat_id}" data-chat="${convo.chat_id}">`;
											let currentGroup = [];
											let currentUser = null;

											// Process each message to group by user
											convo.messages.forEach((item) => {
												if (item.user_id !== currentUser) {
													if (currentGroup.length > 0) {
														// Process the accumulated group
														processMessageGroup(currentGroup);
													}
													currentGroup = [item];
													currentUser = item.user_id;
												} else {
													currentGroup.push(item);
												}
											});

											// Process any remaining messages in the last group
											if (currentGroup.length > 0) {
												processMessageGroup(currentGroup);
											}
										chat_html += `
										</div>
										<div class="dashboard-chat-box-footer">
											<form id="chatAllForm_${convo.chat_id}">
												<input type="hidden" name="chat_id" value="${convo.chat_id}">
												<textarea name="message" class="form-control fixed-height-textarea" rows="1" placeholder="Type here..." id=""></textarea>
												<button type="button" class="btn btn-link text-primary btn-icon" id="sendToUserMessage" data-chat="${convo.chat_id}"><i class="mdi mdi-send icon-wiggle" style="font-size: 15px;"></i></button>
											</form>
										</div>
									</div>
									`;

								});
							}
							chat_html += `
							</div>
							`;

							$('#allChatsDisplay').html(chat_html);
						}
					},
					error: function(xhr, error, status){
						console.error('Error occurred:', xhr.responseText);
						console.error('Error occurred:', status);
						console.error('Error occurred:', error);
					}
				});
			}

			$(document).on('keyup', '#searchAllChats', function() {
				let searchText = $(this).val().toLowerCase();
				$('.dashboard-chat-content').each(function () {
					let chatName = $(this).attr('data-name').toLowerCase();

					if (chatName.includes(searchText)) {
						$(this).show();
					} else {
						$(this).hide();
					}
				});
			})

			function scrollToBottom() {
				const container = $('.dashboard-chat-box-body');
				container.scrollTop(container.prop('scrollHeight'));
			}

			$(document).on('click', '#sendToUserMessage', function () {
				var chat = $(this).data('chat');
				let form = $(`#chatAllForm_${chat}`)[0]; // Get the raw form element
				let formData = new FormData(form);
				$.ajax({
					url: `{{ route('admin.chat.sendmessage') }}`,
					type: 'POST',
					noLoading: true,
					data: formData,
					processData: false, // Don't process the data
					contentType: false, // Don't set content type
					headers: {
						'X-CSRF-TOKEN': token // Add CSRF token
					},
					success: function (response) {
						if(response.status === 'success'){
							$(`#chatAllForm_${chat}`)[0].reset();
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

			$(document).on('keydown', 'textarea[name="message"]', function (event) {
				if ((event.which === 13 || event.keyCode === 13) && !event.shiftKey) {

					event.preventDefault(); // Prevents new line in textarea
					const $form = $(this).closest('form'); // Find the closest form
					const message = $(this).val().trim();

					if (message !== "") {
						const $sendButton = $form.find('#sendToUserMessage');

						if ($sendButton.length) {
							$sendButton.trigger('click'); // Click send button if it exists
						} else if ($editButton.length) {
							$editButton.trigger('click'); // Click edit button if send button is missing
						}
					}
				}

			});

			let lastUpdate = null;

			function reloadChatList() {
				$.ajax({
					url: "{{ route('reloadad.chat.list') }}",
					type: "POST",
					noLoading: true,
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
									$(`#dashboardChatList_${chatId}`).remove(); // Remove the chat by ID
								});
							}
						} else if (response.status === 'chat_updated') {
							// Handle updated chat
							lastUpdate = response.lastUpdate;

							if (response.chat) {
								const chat = response.chat;
								const chatRow = $(`#dashboardChatList_${chat.chat_id}`);

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

			setInterval(reloadChatList, 5000);

			function addChatToList(chat){
				const chatList = $('.dashboard-chat-items'); // Replace with your chat list container ID
				const baseUrl = window.baseUrl;
				const chatRow = `
					<div class="dashboard-chat-content" id="dashboardChatList_${chat.chat_id}" data-chat="${chat.chat_id}" id="chat-div-${chat.chat_id}" data-name="${chat.name}">
						<div class="dashboard-chat-profile text-center me-2">
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
						</div>
						<div class="dashboard-chat-body">
							<div class="dashboard-chat-body-content">
								<p class="text-truncate">${chat.name}</p>
								<span class="text-muted text-truncate" style="font-size: 12px">
									${chat.unseen_count > 0 ? '<b>' : ''}
										${chat.from_message === chat.auth_id
											? 'You :'
											: (chat.type === 'group' ? 'Message :' : chat.name + ' :')}
										${chat.last_message !== null ? chat.last_message : 'Sent Attachment'}
									${chat.unseen_count > 0 ? '</b>' : ''}
								</span>
							</div>
							<span class="text-muted tx-13 chat-last-message-time">${chat.last_message_time}</span>
						</div>
					</div>
				`;
				chatList.prepend(chatRow);

				// Reinitialize Feather Icons (if needed)
				feather.replace();
			}

			function updateChatRow(chatRow, chat) {
				chatRow.html(`
				<div class="dashboard-chat-profile text-center me-2">
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
				</div>
				<div class="dashboard-chat-body">
					<div class="dashboard-chat-body-content">
						<p class="text-truncate">${chat.name}</p>
						<span class="text-muted text-truncate" style="font-size: 12px">
							${chat.unseen_count > 0 ? '<b>' : ''}
								${chat.from_message === chat.auth_id
									? 'You :'
									: (chat.type === 'group' ? 'Message :' : chat.name + ' :')}
								${chat.last_message !== null ? chat.last_message : 'Sent Attachment'}
							${chat.unseen_count > 0 ? '</b>' : ''}
						</span>
					</div>
					<span class="text-muted tx-13 chat-last-message-time">${chat.last_message_time}</span>
				</div>
				`);

				chatRow.attr('data-timestamp', chat.last_message_actual_time);

				// Get the current top chat's timestamp
				const chatContainer = $('.dashboard-chat-items');
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

			function processMessageGroup(group) {
				const firstMessage = group[0];
				const isMe = firstMessage.user_id === firstMessage.my_id;
				const senderPhoto = firstMessage.photo;
				let avatarUrl = '/upload/nophoto.jfif';

				if (senderPhoto) {
					avatarUrl = Array.isArray(senderPhoto)
						? `/upload/photo_bank/${senderPhoto[0]}`
						: `/upload/photo_bank/${senderPhoto}`;
				}

				const avatar = `<img src="${avatarUrl}" title="${firstMessage.user_name}" class="img-xs rounded-circle border">`;

				let firstVisibleIndex = group.findIndex(msg => msg.is_unsend != 1);
				var group_html = '';
				group_html += `
					<div class="dashboard-chat-bubble" data-user-id="${firstMessage.user_id}">
						<div class="dashboard-chat-bubble-profile">
							${avatar}
						</div>
						<div class="dashboard-chat-bubble-content">`;

				group.forEach((message, index) => {
					const isLast = index === group.length - 1;
					const isFirstVisible = index === firstVisibleIndex;
					group_html += buildDashboardMessage(message, isMe, isFirstVisible, isLast);
				});

				group_html += `</div></div>`;
				return group_html;
			}

			function buildDashboardMessage(message, isMe, isFirstMessage = true, showTimestamp = true) {
				if (message.is_unsend == 1 && message.user_id === message.my_id) {
					return ""; // Hide message for the sender only
				}

				if(message.status != 'announcement' && message.status != 'nickname'){
					let html = `
						<div class="d-flex flex-column message-container" data-message-id="${message.message_id}">
							${message.is_unsend != 2 ? `
							<p class="dashboard-chat-bubble-message message" id="dashboardMessageRow_${message.message_id}">
								${message.message !== null
									? message.message.replace(/\n/g, "<br>")  // Converts newlines to <br> for display
									: '<span><i data-feather="file" class="text-muted icon-md mb-2px"></i>  Attachment Sent</span>'}
							</p>
							` : `
							<p class="dashboard-chat-bubble-message">
								<i><b>${isMe ? '"You"' : `"${message.user_name}"`} Unsent Message</b></i>
							</p>
							`}
							${message.is_unsend != 2 && message.task_id != null ? `<div class="task-container-${message.message_id}"></div>` : ''}
							${message.is_unsend != 2 ? `<div class="media-container-${message.message_id}"></div>` : ''}
						</div>
					`;

					if($(`.media-container-${message.message_id}`).length === 0){
						checkMedia(message.message_id, isMe);
					}

					if($(`.task-container-${message.message_id}`).length === 0){
						buildTask(message.message_id, message.task_id, isMe, message.user_id);
					}
					return html;
				} else if (message.is_unsend != 1 && message.status == 'announcement'){
					return `
					<div class="d-flex flex-column message-container" data-message-id="${message.message_id}">
						<div class="d-flex align-items-center justify-content-start">
							<div class="message" id="dashboardMessageRow_${message.message_id}">
								<div class="announcement-content text-center reply-bubble d-flex">
									<span class="text-muted small me-2">${message.user_id === message.my_id ? '<b>You</b>' : `<b>${message.user_name}</b>`} ${message.message} </span>
								</div>
							</div>
						</div>
					</div>
					`;
				} else if (message.is_unsend != 1 && message.status == 'nickname'){
					return `
					<div class="d-flex flex-column message-container w-100" data-message-id="${message.message_id}">
						<div class="d-flex align-items-center justify-content-start">
							<div class="message" id="dashboardMessageRow_${message.message_id}">
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
						window.location.href = `/admin/etasks/${task}`;
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

			let chatUpdates = {};

			function reloadChatMessage() {
				if($('.dashboard-chat-box.show').length > 0){
					var chat = $('.dashboard-chat-box.show').data('chat');
					const activeChatId = localStorage.getItem('selectedChatId');
					$.ajax({
						url: "{{ route('reloadad.chat.message') }}",
						type: "POST",
						noLoading: true,
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
					if (!$(`#dashboardMessageContainer_${currentChatId} #dashboardMessageRow_${message.message_id}`).length) {
						appendMessage(message);
					}
				});

				// 2. Remove deleted messages
				chatData.deletedMessageIds?.forEach(id => {
					$(`#dashboardMessageContainer_${currentChatId} #dashboardMessageRow_${id}`).remove();
				});

				// 3. Update existing messages
				chatData.updatedMessages?.forEach(message => {
					const messageRow = $(`#dashboardMessageContainer_${currentChatId} #dashboardMessageRow_${message.message_id}`);
					if (messageRow.length) updateMessageRow(messageRow, message);
				});
			}

			function handleInitialLoad(chatId, messages) {
				const container = $(`#dashboardMessageContainer_${chatId}`);
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
						if ($(`#dashboardMessageContainer_${chatId} #dashboardMessageRow_${message.message_id}`).length === 0) {
							appendMessage(message);
						}
					});
				}

				// Remove deleted messages
				if (deletedMessageIds && deletedMessageIds.length) {
					deletedMessageIds.forEach(id => {
						$(`#dashboardMessageContainer_${chatId} #dashboardMessageRow_${id}`).remove();
					});
				}
			}

			function handleChatUpdated(chatId, messages) {
				if (messages && messages.length) {
					messages.forEach(message => {
						const messageRow = $(`#dashboardMessageContainer_${chatId} #dashboardMessageRow_${message.message_id}`);
						if (messageRow.length) {
							updateMessageRow(messageRow, message);
						}
					});
				}
			}

			setInterval(reloadChatMessage, 500);

			function appendMessage(message) {
				const containerId = `dashboardMessageContainer_${message.chat_id}`;
				const $container = $(`#${containerId}`);
				if (!$container.length) return;

				// Find existing groups and last message ID
				const $groups = $container.find('.dashboard-chat-bubble');
				const lastGroup = $groups.last();
				const lastMessageId = $container.find('.message-container').last().data('message-id');

				// Only process new messages
				if (lastMessageId >= message.message_id) return;

				// Grouping logic
				if (lastGroup.length && lastGroup.data('user-id') === message.user_id) {
					// Append to existing group
					const $content = lastGroup.find('.dashboard-chat-bubble-content');
					const isFirst = $content.children().length === 0;

					$content.append(buildDashboardMessage(message, message.user_id === message.my_id, isFirst));
				} else {
					// Create new group
					$container.append(processMessageGroup([message]));
				}

				// Scroll to bottom and refresh icons
				$container.scrollTop($container[0].scrollHeight);
				scrollToBottom();
				feather.replace();
			}

			function updateMessageRow(messageRow, message) {
				const $container = $(`#dashboardMessageContainer_${message.chat_id}`);
				const $messageContainer = $container.find(`.message-container[data-message-id="${message.message_id}"]`);

				if ($messageContainer.length) {
					// Preserve existing interactive elements
					const existingState = {
					reply: $messageContainer.find('.reply-container').html(),
					media: $messageContainer.find('.media-container').html(),
					dropdown: $messageContainer.find('.dropdown-menu').html()
					};

					// Rebuild message HTML
					const newHtml = $(buildDashboardMessage(message, message.user_id === message.my_id));

					// Restore preserved elements
					if (existingState.reply) newHtml.find('.reply-container').html(existingState.reply);
					if (existingState.media) newHtml.find('.media-container').html(existingState.media);
					if (existingState.dropdown) newHtml.find('.dropdown-menu').html(existingState.dropdown);

					$messageContainer.replaceWith(newHtml);
					feather.replace();
				}
			}

			$(document).on('click', '.dashboard-chat-btn', function() {
				$(this).removeClass('show');
				$('.dashboard-chat-container').addClass('show')
				$('.dashboard-chat-items').addClass('show')
				$('.dashboard-chat-container-action').addClass('show').addClass('mb-2');
			});

			$(document).on('click', '.dashboard-chat-container-btn', function() {
				$('.dashboard-chat-btn').addClass('show');
				$('.dashboard-chat-container').removeClass('show')
				$('.dashboard-chat-items').removeClass('show')
				$('.dashboard-chat-container-action').removeClass('show').removeClass('mb-2');
			});

			$(document).on('click', '.dashboard-chat-content', function() {
				var chat = $(this).data('chat');

				$('.dashboard-chat-box').each(function() {
					if ($(this).attr('id') != `chat-box-${chat}`) {
						$(this).removeClass('show');
					}
				})

				$('.dashboard-chat-items').removeClass('show');
				$('.dashboard-chat-container-action').removeClass('show').removeClass('mb-2');
				$(`#chat-box-${chat}`).addClass('show');
			});

			$(document).on('click', '.dashboard-chat-box-btn', function() {
				$('.dashboard-chat-items').addClass('show');
				$('.dashboard-chat-container-action').addClass('show').addClass('mb-2');
				$('.dashboard-chat-box').removeClass('show');
			});

			getAllChats();
			//endregion
		}
	});
	</script>
</body>
</html>