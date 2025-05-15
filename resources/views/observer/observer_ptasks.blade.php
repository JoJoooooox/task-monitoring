@extends('observer.observer_dashboard')
@section('observer')
<style>


</style>
<div class="page-content" id="panelTask" data-task="{{$task_id}}">
    <div class="row">
        <div class="col-12 modal-body-bg border border-primary mb-3 d-grid no-print">
            <button class="btn btn-primary" id="printButton">Print Task</button>
        </div>
        <div class="col-xl-12 modal-body-bg border border-primary mb-3">
            <div class="row px-4 py-2" id="myTabContentTwo">
                <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                    <div class="task-title">
                        @if(!empty('info'))
                        <h4>Task Title: <b class="ms-2 text-primary">{{$info->title}}</b><span class="badge text-bg-secondary ms-2">Type: <b class="ms-1">{{$info->type}}</b></span></h4>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <ul class="nav nav-tabs pageContainer no-print" role="tablist">
                    </ul>
                    <div class="tab-content border border-top-0 p-3 contentContainer">
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){

    $("#printButton").click(function () {
        Swal.fire({
            title: 'Are you sure you want to print this task?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to print it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                $(".tab-pane").addClass("show active");
                var content = $("#myTabContentTwo").html();
                var printWindow = window.open('', '', 'height=600,width=800');

                printWindow.document.write('<html><head><title>{{$info->title}}</title>');

                // Include Bootstrap & Custom Styles
                printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">');
                printWindow.document.write(`<style> @media print {
                    body {
                        zoom: 100%; /* Adjust if content is cut off */
                    }

                    /* Remove forced page breaks */
                    * {
                        page-break-inside: always !important;
                    }
                    .task-title {
                        position: sticky;
                        top: 0;
                        background: white;
                        padding: 10px;
                        border-bottom: 2px solid black;
                    }

                    /* Ensure tabs and sections are fully expanded */
                    .tab-content .tab-pane {
                        display: block !important;
                        visibility: visible !important;
                        height: auto !important;
                    }

                    /* Hide elements that shouldn't appear in print */
                    .no-print, .printButton {
                        display: none !important;
                    }

                    /* Make sure the entire content fits */
                    #panelTask {
                        width: 100%;
                        overflow: visible !important;
                    }
                } </style>`);
                // Use full asset URLs (Replace 'your-site.com' with actual domain)
                printWindow.document.write('<link rel="stylesheet" href="/assets/vendors/morris.js/morris.css">');
                printWindow.document.write('<link rel="stylesheet" href="/assets/custom-css/observer.css">');
                printWindow.document.write('<link rel="stylesheet" href="/assets/css/demo1/style.css">');
                printWindow.document.write('<link rel="stylesheet" href="/assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">');

                printWindow.document.write('</head><body>');
                printWindow.document.write(content);
                printWindow.document.write('</body></html>');

                printWindow.document.close();

                // Ensure styles are loaded before printing
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                    window.location.href = "{{ route('observer.tasks')}}";
                }, 500);
            }
        });

    });


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

    function initTinyMCE() {
        if (typeof tinymce !== 'undefined') {
            tinymce.remove('.typography-editor'); // Remove existing instances
        }
        if ($('.typography-editor').length) { // Check if the class exists in the DOM
            tinymce.init({
                selector: '.typography-editor', // Use a class instead of an ID
                height: 300,
                plugins: 'advlist autolink link image lists charmap preview anchor pagebreak ' +
                        'searchreplace wordcount visualblocks code fullscreen insertdatetime media ' +
                        'table emoticons template codesample',
                toolbar: 'undo redo | styles | bold italic underline | alignleft aligncenter alignright alignjustify | ' +
                        'bullist numlist outdent indent | link image | preview fullscreen | ' +
                        'forecolor backcolor emoticons',
                menubar: 'file edit view insert format tools table',
                content_style: 'body {font-family:Helvetica,Arial,sans-serif; font-size:16px}',
                setup: function (editor) {
                    editor.on('change', function () {
                        tinymce.triggerSave(); // Automatically updates the textarea
                    });
                }
            });
        } else {
            console.log('No typography-editor class found, TinyMCE not initialized.');
        }
    }

    initTinyMCE();

    $(document).on('change', '#file-upload', function() {
        var files = $(this).prop('files');
        var fileName = files.length > 1
            ? files.length + ' files selected'
            : files[0]?.name || 'No file selected';

        $('#file-selected').text(fileName);

        // Show remove button only if a file is selected
        if (files.length > 0) {
            $('#remove-file').show();
        }
    });

    $(document).on('click', '#remove-file', function() {
        $('#file-upload').val('');  // Clear file input
        $('#file-selected').text('No file selected');  // Reset text
        $(this).hide();  // Hide remove button
    });
    liveTask();

    function liveTask(active = null){
        var id = $('#panelTask').data('task');

        $.ajax({
            url: '{{ route("observer.tasks.gptasks") }}',
            method: 'GET',
            data: {
                task: id
            },
            dataType: 'json',
            success: function(response){
                var page_container = $('.pageContainer');
                var content_container = $('.contentContainer');
                var pager = response.page;
                var stepper = response.stepper;
                $('.pageContainer').empty();
                $('.contentContainer').empty();

                response.pagesWithContent.forEach((item, index) => {
                    let page = item.pages;
                    let contents = item.contents;
                    let page_count = page.id;
                    let isFirstPage = index === 0;
                    let activation = (active !== null && page_count === active) || (active === null && isFirstPage) ? 'active' : '';
                    let activationContent = (active !== null && page_count === active) || (active === null && isFirstPage) ? 'show active' : '';



                    var new_page_tab_html = `
                    <li class="nav-item pageCount" id="page_tab_count_${page_count}">
                        <a class="nav-link ${activation}" data-bs-toggle="tab" href="#newPage${page_count}" role="tab" aria-selected="false">
                            <form id="pageForm_${page.id}" data-temp="${id}">
                            @csrf
                                <div class="input-group">
                                    ${page.page_title === null
                                    ? '<span class="text-primary">No Title</span>'
                                    : '<span class="text-primary">'+page.page_title+'</span>'
                                    }
                                </div>
                            </form>
                        </a>
                    </li>`;

                    var content_html = '';
                    contents.forEach((content) => {
                        if (content.field_page === page.id) {
                            if(content.field_type === 'Radio'){
                                content_html += `
                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                    <div class="row">
                                        <div class="col-8 mb-3">
                                            <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                            <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                <label class="form-check-label" for="checkInline">
                                                    Required Field
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 radioContainer_${content.id}">
                                        </div>
                                    </div>
                                </div>
                                `;

                                getRadioField(content.id).then(function(radioFields) {
                                    $('.radioContainer_' + content.id).html(radioFields);
                                }).catch(function(error) {
                                    console.error('Error loading radio fields:', error);
                                });
                            } else if (content.field_type === 'Checkbox'){
                                content_html += `
                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                    <div class="row">
                                        <div class="col-8 mb-3">
                                            <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                            <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                <label class="form-check-label" for="checkInline">
                                                    Required Field
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="check_field_${content.id}" name="check_label_${content.id}" ${content.field_value !== "false" ? 'checked' : ''} disabled>
                                                <label class="form-check-label" for="check_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                `;
                            } else if (content.field_type === 'Text'){
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="text" value="${content.field_value === null ? '' : (content.field_value === 'false' ? '' : content.field_value)}" class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                            } else if (content.field_type === 'Textarea'){
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control" id="text_field_${content.id}" name="text_label_${content.id}" ${content?.field_pre_answer !== null ? 'required' : ''} disabled>${content.field_value === null ? '' : ((content.field_value === 'false' ? '' : content.field_value))}</textarea>

                                            </div>
                                        </div>
                                    </div>
                                    `;
                            } else if (content.field_type === 'File'){
                                var file = content.field_value;
                                let baseUrl = "{{ asset('') }}";
                                var fileDisplay = ``;
                                if (file !== 'false' && file) {
                                    let fileUrl = baseUrl + file;
                                    var fileName = file.split('/').pop(); // Extract filename
                                    var fileExtension = fileName.split('.').pop().toLowerCase();

                                    function getFileSize(fileUrl, callback) {
                                        $.ajax({
                                            url: fileUrl,
                                            type: 'HEAD', // Fetch only headers
                                            success: function (data, status, xhr) {
                                                let fileSize = xhr.getResponseHeader('Content-Length'); // Get file size in bytes
                                                callback(fileSize ? formatFileSize(fileSize) : 'Unknown');
                                            },
                                            error: function () {
                                                callback('Unknown');
                                            }
                                        });
                                    }

                                    function formatFileSize(bytes) {
                                        if (bytes < 1024) return bytes + " B";
                                        let units = ["KB", "MB", "GB", "TB"];
                                        let i = -1;
                                        do {
                                            bytes /= 1024;
                                            i++;
                                        } while (bytes > 1024);
                                        return bytes.toFixed(1) + " " + units[i];
                                    }

                                    if ($.inArray(fileExtension, ['jpg', 'jpeg', 'png', 'gif']) !== -1) {
                                        // Display images
                                        fileDisplay = `
                                            <div class="uploaded-files">
                                                <div class="files-container">
                                                    <div class="file-list">
                                                        <img src="${fileUrl}" alt="video file" class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                        <div class="file-meta">
                                                            <div class="meta-info">
                                                                <p>${fileName}</p>
                                                                <span class="file-size">Loading...</span>
                                                            </div>
                                                            <div class="status-check">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                            </div>
                                                        </div>
                                                        <a href="${fileUrl}" download="${fileName}" class="btn btn-primary mt-2">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        getFileSize(fileUrl, function (size) {
                                            $(".file-size").text(size); // Update the file size in the HTML
                                        });
                                    } else {
                                        let fileIcon = getFileIcon(fileExtension);

                                        fileDisplay = `
                                            <div class="uploaded-files">
                                                <div class="files-container">
                                                    <div class="file-list">
                                                        <img src="${fileIcon}" alt="${fileExtension} class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                        <div class="file-meta">
                                                            <div class="meta-info">
                                                                <p>${fileName}</p>
                                                                <span class="file-size">Loading...</span>
                                                            </div>
                                                            <div class="status-check">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                            </div>
                                                        </div>
                                                        <a href="${fileUrl}" download="${fileName}" class="btn btn-primary mt-2">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        getFileSize(fileUrl, function (size) {
                                            $(".file-size").text(size); // Update the file size in the HTML
                                        });
                                    }
                                }
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <div class="form-group mt-3">
                                                    <div class="contUpload">
                                                        <div class="upload">
                                                            <div class="up-container">
                                                                <div class="header">
                                                                    <div class="text">
                                                                        <h1>Upload and Attach Files</h1>
                                                                        <p>Upload and attach files to this project.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="upload-box" style="pointer-events: none; opacity: 0.5;">
                                                                    <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                    <span id="file-selected">${content.field_value}</span>
                                                                    <label for="file-upload" class="custom-file-upload">
                                                                        Click to upload<br>
                                                                        <input type="file" class="file" id="file-upload" class="drop_${content.id}" multiple disabled>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                    </label>
                                                                    <span>Maximum file size 5MB.</span>
                                                                </div>
                                                            ${fileDisplay}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                            } else if (content.field_type === 'Typography'){
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3" style="pointer-events: none; opacity: 0.5;">
                                                <label class="form-check-label" for="typography_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <textarea class="form-control border border-primary typography-editor" id="typography_${content.id}"  ${content?.field_pre_answer !== null ? 'required' : ''} disabled>${content.field_value === null ? '' : (content.field_value === 'false' ? '' : content.field_value)}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    setTimeout(() => {
                                        initTinyMCE();
                                    }, 100);
                            } else if (content.field_type === 'Date'){
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="date_field_${content.id}">
                                                    ${content.options}
                                                </label>
                                                <input type="date" class="form-control" value="${content.field_value}" id="date_field_${content.id}" name="date_label_${content.id}"  disabled>
                                            </div>
                                        </div>
                                    </div>
                                    `;
                            } else if(content.field_type === 'Dropdown'){
                                content_html += `
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_${content.id}">
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${content.field_label !== null ? content.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${content.field_description !== null ? content.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${content.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 downContainer_${content.id}">
                                            </div>
                                        </div>
                                    </div>
                                    `;

                                    getDownField(content.id).then(function(downFields) {
                                        $('.downContainer_' + content.id).html(downFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                            }
                        }
                    });

                    var new_content_page_html = `
                    <div class="tab-pane fade ${activationContent}" id="newPage${page_count}" role="tabpanel" data-temp="${page.template_id}">
                        <div class="row" id="fieldContainer${page_count}">
                            ${content_html}
                        </div>
                    </div>`;


                    // Append to DOM

                    page_container.append(new_page_tab_html);
                    content_container.append(new_content_page_html);

                    feather.replace();
                });

                document.querySelectorAll('.collapse').forEach(el => {
                    new bootstrap.Collapse(el, { toggle: false });
                });
            },
            error: function(xhr, error, status){
                console.error(xhr.responseText);
                console.error(error);
                console.error(status);
            }
        });
    }

    function getFileIcon(extension) {
        let icons = {
            'pdf': 'assets/icons/pdf-icon.png',
            'doc': 'assets/icons/word-icon.png',
            'docx': 'assets/icons/word-icon.png',
            'xls': 'assets/icons/excel-icon.png',
            'xlsx': 'assets/icons/excel-icon.png',
            'ppt': 'assets/icons/ppt-icon.png',
            'pptx': 'assets/icons/ppt-icon.png',
            'zip': 'assets/icons/zip-icon.png',
            'rar': 'assets/icons/zip-icon.png',
            'txt': 'assets/icons/txt-icon.png',
            'mp4': 'assets/icons/video-icon.png',
            'mp3': 'assets/icons/audio-icon.png'
        };

        return icons[extension] || 'assets/icons/default-file-icon.png'; // Default icon if no match
    }

    function getRadioField(id) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("observer.tasks.printgetradio") }}',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    var options = response.options;
                    var answer = response.input;
                    var radioHTML = '';

                    if (options && Object.keys(options).length > 0) {
                        $.each(options, function(contentId, optionSet) {


                            optionSet.options.forEach(function(option, index) {
                                let isChecked = (answer !== null && answer === option) ? 'checked' : '';

                                radioHTML += `
                                    <div class="form-check mb-2 radioCount">
                                        <input type="radio" class="form-check-input"
                                            name="radio_${contentId}"
                                            value="${option}" id="radio_${contentId}_${index}" ${isChecked} disabled>
                                        <label class="form-check-label" for="radio_${contentId}_${index}">${option}</label>
                                    </div>
                                `;
                            });
                        });
                    }

                    resolve(radioHTML);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    reject(error);
                }
            });
        });
    }

    function getDownField(id) {
        return new Promise(function (resolve, reject) {
            $.ajax({
                url: '{{ route("observer.tasks.printgetdown") }}',
                method: 'GET',
                data: { id: id },
                dataType: 'json',
                success: function(response) {
                    var options = response.options;
                    var answer = response.answer;
                    var downHTML = '';

                    if (options && Object.keys(options).length > 0) {
                        $.each(options, function(contentId, optionSet) {
                            downHTML += `
                            <select class="form-select" aria-label="Default select example" id="down_${contentId}" disabled>
                            `;
                            optionSet.options.forEach(function(option, index) {
                                let isSelected = (String(answer).trim().toLowerCase() === String(option).trim().toLowerCase()) ? 'selected' : '';
                                downHTML += `
                                    <option value="${option}" ${isSelected}>${option}</option>
                                `;
                            });
                        });
                    }
                    downHTML += `</select>`;
                    resolve(downHTML);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    reject(error);
                }
            });
        });
    }

    let lastUpdate = null; // Store the last update data

    // Function to reload the ongoing div
    function reloadOngoingDiv() {
        var id = $('#panelTask').data('task');
        $.ajax({
            url: "{{ route('observer.tasks.liveprintreloading') }}",
            type: "POST",
            noLoading: true,
            data: {
                _token: "{{ csrf_token() }}",
                lastUpdate: JSON.stringify(lastUpdate),
                id: id
            },
            success: function(response) {

                if (response.status === 'initial_load') {
                    lastUpdate = response.lastUpdate;
                } else if (response.status === 'fields_updated') {
                    // Handle updated task
                    lastUpdate = response.lastUpdate;

                    if (response.contents) {
                        response.contents.forEach(update => {
                            const field = update.field;
                            const fieldId = update.field.id;

                            var content_html = '';
                            if(field.field_type === 'Radio'){
                                content_html += `
                                <div class="row">
                                    <div class="col-8 mb-3">
                                        <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                        <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                    </div>
                                    <div class="col-12 mb-3">
                                        <div class="form-check form-check-inline">
                                            <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                            <label class="form-check-label" for="checkInline">
                                                Required Field
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-12 radioContainer_${field.id}">
                                    </div>
                                </div>
                                `;

                                getRadioField(field.id).then(function(radioFields) {
                                    $('.radioContainer_' + field.id).html(radioFields);
                                }).catch(function(error) {
                                    console.error('Error loading radio fields:', error);
                                });
                            } else if (field.field_type === 'Checkbox'){
                                content_html += `
                                    <div class="row">
                                        <div class="col-8 mb-3">
                                            <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                            <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-check form-check-inline">
                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                <label class="form-check-label" for="checkInline">
                                                    Required Field
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-12 mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="check_field_${field.id}" name="check_label_${field.id}" ${field.field_value == "on" ? 'checked' : ''} disabled>
                                                <label class="form-check-label" for="check_field_${field.id}">
                                                    ${field.options}
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else if (field.field_type === 'Text'){
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${field.id}">
                                                    ${field.options}
                                                </label>
                                                <input type="text" value="${!field.field_value || field.field_value === 'false' ? '' : field.field_value}" class="form-control" id="text_field_${field.id}" name="text_label_${field.id}" ${field?.field_pre_answer !== null ? 'required' : ''} disabled>
                                            </div>
                                        </div>
                                    `;
                            } else if (field.field_type === 'Textarea'){
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${field.id}">
                                                    ${field.options}
                                                </label>
                                                <textarea class="form-control" id="text_field_${field.id}" name="text_label_${field.id}" ${field?.field_pre_answer !== null ? 'required' : ''} disabled>${!field.field_value || field.field_value === 'false' ? '' : field.field_value}</textarea>

                                            </div>
                                        </div>
                                    `;
                            } else if (field.field_type === 'File'){
                                var file = field.field_value;
                                let baseUrl = "{{ asset('') }}";
                                var fileDisplay = ``;
                                if (file !== 'false' && file) {
                                    let fileUrl = baseUrl + file;
                                    var fileName = file.split('/').pop(); // Extract filename
                                    var fileExtension = fileName.split('.').pop().toLowerCase();

                                    function getFileSize(fileUrl, callback) {
                                        $.ajax({
                                            url: fileUrl,
                                            type: 'HEAD', // Fetch only headers
                                            success: function (data, status, xhr) {
                                                let fileSize = xhr.getResponseHeader('Content-Length'); // Get file size in bytes
                                                callback(fileSize ? formatFileSize(fileSize) : 'Unknown');
                                            },
                                            error: function () {
                                                callback('Unknown');
                                            }
                                        });
                                    }

                                    function formatFileSize(bytes) {
                                        if (bytes < 1024) return bytes + " B";
                                        let units = ["KB", "MB", "GB", "TB"];
                                        let i = -1;
                                        do {
                                            bytes /= 1024;
                                            i++;
                                        } while (bytes > 1024);
                                        return bytes.toFixed(1) + " " + units[i];
                                    }

                                    if ($.inArray(fileExtension, ['jpg', 'jpeg', 'png', 'gif']) !== -1) {
                                        // Display images
                                        fileDisplay = `
                                            <div class="uploaded-files">
                                                <div class="files-container">
                                                    <div class="file-list">
                                                        <img src="${fileUrl}" alt="video file" class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                        <div class="file-meta">
                                                            <div class="meta-info">
                                                                <p>${fileName}</p>
                                                                <span class="file-size">Loading...</span>
                                                            </div>
                                                            <div class="status-check">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                            </div>
                                                        </div>
                                                        <a href="${fileUrl}" download="${fileName}" class="btn btn-primary mt-2">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        getFileSize(fileUrl, function (size) {
                                            $(".file-size").text(size); // Update the file size in the HTML
                                        });
                                    } else {
                                        let fileIcon = getFileIcon(fileExtension);

                                        fileDisplay = `
                                            <div class="uploaded-files">
                                                <div class="files-container">
                                                    <div class="file-list">
                                                        <img src="${fileIcon}" alt="${fileExtension} class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                        <div class="file-meta">
                                                            <div class="meta-info">
                                                                <p>${fileName}</p>
                                                                <span class="file-size">Loading...</span>
                                                            </div>
                                                            <div class="status-check">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                            </div>
                                                        </div>
                                                        <a href="${fileUrl}" download="${fileName}" class="btn btn-primary mt-2">Download</a>
                                                    </div>
                                                </div>
                                            </div>
                                        `;

                                        getFileSize(fileUrl, function (size) {
                                            $(".file-size").text(size); // Update the file size in the HTML
                                        });
                                    }
                                }
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="text_field_${field.id}">
                                                    ${field.options}
                                                </label>
                                                <div class="form-group mt-3">
                                                    <div class="contUpload">
                                                        <div class="upload">
                                                            <div class="up-container">
                                                                <div class="header">
                                                                    <div class="text">
                                                                        <h1>Upload and Attach Files</h1>
                                                                        <p>Upload and attach files to this project.</p>
                                                                    </div>
                                                                </div>
                                                                <div class="upload-box" style="pointer-events: none; opacity: 0.5;">
                                                                    <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                    <span id="file-selected">${field.field_value}</span>
                                                                    <label for="file-upload" class="custom-file-upload">
                                                                        Click to upload<br>
                                                                        <input type="file" class="file" id="file-upload" class="drop_${field.id}" multiple disabled>
                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                                                    </label>
                                                                    <span>Maximum file size 5MB.</span>
                                                                </div>
                                                            ${fileDisplay}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                            } else if (field.field_type === 'Typography'){
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3" style="pointer-events: none; opacity: 0.5;">
                                                <label class="form-check-label" for="typography_${field.id}">
                                                    ${field.options}
                                                </label>
                                                <textarea class="form-control border border-primary typography-editor" id="typography_${field.id}"  ${field?.field_pre_answer !== null ? 'required' : ''} disabled>${!field.field_value || field.field_value === 'false' ? '' : field.field_value}</textarea>
                                            </div>
                                        </div>
                                    `;

                                    setTimeout(() => {
                                        initTinyMCE();
                                    }, 100);
                            } else if (field.field_type === 'Date'){
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <label class="form-check-label" for="date_field_${field.id}">
                                                    ${field.options}
                                                </label>
                                                <input type="date" class="form-control" id="date_field_${field.id}" value="${field.field_value}" name="date_label_${field.id}" disabled>
                                            </div>
                                        </div>
                                    `;
                            } else if(field.field_type === 'Dropdown'){
                                content_html += `
                                        <div class="row">
                                            <div class="col-8 mb-3">
                                                <h4>${field.field_label !== null ? field.field_label : ''}</h4>
                                                <span class="badge text-bg-secondary">${field.field_description !== null ? field.field_description : ''}</span>
                                            </div>
                                            <div class="col-12 mb-3">
                                                <div class="form-check form-check-inline">
                                                    <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" ${field.is_required === 1 ? 'checked' : ''} disabled>
                                                    <label class="form-check-label" for="checkInline">
                                                        Required Field
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="col-12 downContainer_${field.id}">
                                            </div>
                                        </div>
                                    `;

                                    getDownField(field.id).then(function(downFields) {
                                        $('.downContainer_' + field.id).html(downFields);
                                    }).catch(function(error) {
                                        console.error('Error loading radio fields:', error);
                                    });
                            }
                            $(`#field_${fieldId}`).html(content_html);
                        });
                    }
                } else if (response.status === 'no_changes') {
                    lastUpdate = response.lastUpdate;
                }
            },
            error: function(xhr, error, status) {
                console.log('AJAX Error:', xhr.responseText);
                console.log('AJAX Error:', error);
                console.log('AJAX Error:', status);
            }
        });
    }

    // Set interval to check for updates every 5 seconds
    setInterval(reloadOngoingDiv, 1000); // Adjust the interval as needed


});
</script>

@endsection