@extends('observer.observer_dashboard')
@section('observer')
@if($info->link_id !== null)
<nav class="settings-sidebar" id="dontInclude">
    <div class="sidebar-body border border-end-0 border-primary p-0" style="height: 500px; min-height: 500px;">
        <a href="#" class="settings-sidebar-toggler border border-end-0 border-primary">
            <i data-feather="file-text"></i>
        </a>
        <div class="theme-wrapper  p-0 m-0" style="width: 100%; height: 100%;">
            <div class="row  m-0 p-1" style="overflow-y: auto; overflow-x: hidden; width: 100%; max-height: 100%;">
                <h6 class="text-wrap my-2">Linked Task: <b class="text-primary">{{$linkedInfo->title}}</b></h6>
                @php
                    $tabLinkedContent = '';
                @endphp
                @if(!empty($pagesLinkedWithContent))
                    @foreach($pagesLinkedWithContent as $row)
                        @php
                            $contents = $row['contents'];
                            if(!empty($contents)){
                                foreach ($contents as $content) {
                                    $requiredInput = ($content->is_required == 1 ? 'checked' : '');
                                    if ($content->field_type === 'Radio') {
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 radioContainer_'.$content->id.'">
                                                    <input type="text" class="form-control" id="radio_'.$content->id.'" name="radio_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                    } else if ($content->field_type === 'Checkbox'){
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input" id="check_field_'.$content->id.'_'.$content->task_id.'" name="check_label_'.$content->id.'_'.$content->task_id.'" '.($content->answer === 'on' ? 'checked' : '').' disabled>
                                                        <label class="form-check-label" for="check_field_'.$content->id.'">
                                                            '.($content->options ?? '').'
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                    } else if ($content->field_type === 'Text'){
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label" for="text_field_'.$content->id.'">
                                                        '.($content->options ?? '').'
                                                    </label>
                                                    <input type="text" class="form-control" id="text_field_'.$content->id.'" name="text_label_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'" disabled>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                    } else if ($content->field_type === 'Textarea'){
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label" for="text_field_'.$content->id.'">
                                                        '.($content->options ?? '').'
                                                    </label>
                                                    <textarea class="form-control" id="text_field_'.$content->id.'" name="area_label_'.$content->id.'_'.$content->task_id.'" disabled>'.$content->answer.'</textarea>
                                                </div>
                                            </div>
                                        </div>
                                        ';
                                    } else if ($content->field_type === 'File'){
                                        $filePath = $content->answer; // Path to the uploaded file
                                        $fileUrl = asset($filePath); // Generate the full URL to the file
                                        $fileName = basename($filePath); // Extract the file name from the path
                                        $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file extension

                                        // Determine how to display the file based on its type
                                        $fileDisplay = '';
                                        if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                            // Display images
                                            $fileDisplay = '
                                                            <div class="uploaded-files">
                                                                <div class="files-container">
                                                                    <div class="file-list">
                                                                        <img src="' . $fileUrl . '" alt="video file" class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                                        <div class="file-meta">
                                                                            <div class="meta-info">
                                                                                <p>' . $fileName . '</p>
                                                                            </div>
                                                                            <div class="status-check">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                                            </div>
                                                                        </div>
                                                                        <a href="' . $fileUrl . '" download="' . $fileName . '" class="btn btn-primary mt-2">Download</a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            ';
                                        } else {
                                            // Display a download link for non-image files
                                            $fileDisplay = '<div class="uploaded-files">
                                                                <div class="files-container">
                                                                    <div class="file-list">
                                                                        <div class="file-meta">
                                                                            <div class="meta-info">
                                                                                <p>' . $fileName . '</p>
                                                                            </div>
                                                                            <div class="status-check">
                                                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                                            </div>
                                                                        </div>
                                                                        <a href="' . $fileUrl . '" download="' . $fileName . '" class="btn btn-primary mt-2">Download</a>
                                                                    </div>
                                                                </div>
                                                            </div>';
                                        }

                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label" for="text_field_'.$content->id.'">
                                                        '.($content->options).'
                                                    </label>
                                                    ' . (!empty($filePath) ? $fileDisplay : '') . '
                                                </div>
                                            </div>
                                        </div>';
                                    } else if ($content->field_type === 'Typography') {
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3" style="pointer-events: none; opacity: 0.5;">
                                                    <label class="form-check-label" for="typographyView_'.$content->id.'">
                                                        '.($content->options !== null ? $content->options : "").'
                                                    </label>
                                                    <textarea class="form-control border border-primary typography-editor" id="typographyView_'.$content->id.'" name="typography_'.$content->id.'_'.$content->task_id.'">'.$content->answer.'</textarea>
                                                </div>
                                            </div>
                                        </div>';
                                    } else if ($content->field_type === 'Date'){
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <label class="form-check-label" for="date_field_'.$content->id.'">
                                                        '.($content->options !== null ? $content->options : "").'
                                                    </label>
                                                    <input type="date" class="form-control" id="date_field_'.$content->id.'" name="date_label_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'" disabled>
                                                </div>
                                            </div>
                                        </div>';
                                    } else if ($content->field_type === 'Dropdown'){
                                        $tabLinkedContent .= '
                                        <div class="col-12 modal-body-bg mb-3 border border-primary">
                                            <div class="row">
                                                <div class="col-8 mb-3">
                                                    <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                    <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                </div>
                                                <div class="col-12 mb-3">
                                                    <div class="form-check form-check-inline">
                                                        <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                        <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                            Required Field
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="col-12 downContainer_'.$content->id.'">
                                                    <input type="text" class="form-control" id="text_field_'.$content->id.'" name="text_label_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'" disabled>
                                                </div>
                                            </div>
                                        </div>';
                                    }
                                }
                            } else {
                                $tabLinkedContent .= '
                                <div class="col-12 modal-body-bg mb-3 border border-primary">
                                    <div class="row text-center">
                                        <h3 class="m-3">There\'s no existing field on this page</h3>
                                    </div>
                                </div>
                                ';
                            }
                        @endphp
                    @endforeach
                @endif
                {!! $tabLinkedContent !!}
            </div>
        </div>
    </div>
</nav>
@else
<p>No linked content found.</p>
@endif
<div class="page-content" id="panelTask" data-task="{{$info->id}}" data-ustat="{{$info->user_status}}">
    <div class="row">
        <div class="col-xl-12 modal-body-bg border border-primary mb-3">
            <div class="row px-4 py-2">
                <div class="col-12 d-flex justify-content-between align-items-center mb-3">
                    <div>
                        @if(!empty('info'))
                        <h4>Task Title: <b class="ms-2 text-primary">{{$info->title}}</b><span class="badge text-bg-secondary ms-2">Type: <b class="ms-1">{{$info->type}}</b></span></h4>
                        @endif
                    </div>
                </div>
                <div class="col-lg-8 col-12 order-2 order-lg-1">
                    @php
                        use App\Models\Task_templates;
                        $pageOutput = ''; // Initialize an empty string for page tabs
                        $tabContent = ''; // Initialize an empty string for tab content
                        $page_count = 1;
                        $totalSteps = count($pagesWithContent);
                        $template = Task_templates::where('id', $info->template_id)->first();
                        $stepperEnabled = ($template->stepper === 'Yes') ? true : false;
                        $activeTab = $_GET['activeTab'] ?? null; // Get the active tab from the request
                    @endphp
                    @if($stepperEnabled)
                    <div class="row mx-4">
                        <div class="col-12 my-2 p-4 text-center border border-primary rounded-3">
                            <h5 class="text-primary"><i data-feather="info" class="icon-wiggle"></i> Information: Step By Step Task</h5>
                        </div>
                    </div>
                    @endif
                    @if(!empty($pagesWithContent))
                        @foreach($pagesWithContent as $steps => $row)
                            @php
                                $page = $row['pages']; // Page details
                                $contents = $row['contents'];

                                $activation = (!empty($active) && $page_count === $active) || (empty($active) && $page_count === 1) ? 'active' : '';
                                $activationContent = (!empty($active) && $page_count === $active) || (empty($active) && $page_count === 1) ? 'show active' : '';

                                $stepperPage = ($stepperEnabled ? (($steps === 0) ? 'active' : 'disabled') : $activation);
                                $stepperHref = ($stepperEnabled ? 'href="#step-'.$steps.'"' : 'href="#newPage'.$page->id.'"');
                                $stepperPageAria = ($activation ? 'true' : 'false');

                                $stepperTab = ($stepperEnabled ? (($steps === 0) ? 'show active' : '') : $activationContent);
                                if ($activeTab && $activeTab === 'step-' . $steps) {
                                    $stepperTab = 'show active'; // Reapply the active class
                                }
                                $stepperTabId = ($stepperEnabled ? 'id="step-'.$steps.'"' : 'id="newPage'.$page->id.'"');

                                $pageOutput .= '<li class="nav-item pageCount" id="page_tab_count_'.$page->id.'">
                                                    <a class="nav-link '.$stepperPage.'" data-step="'.$steps.'" data-bs-toggle="tab"  '.$stepperHref.' role="tab" aria-selected="'.$stepperPageAria.'">
                                                        <span class="text-primary">'.$page->page_title.'</span>
                                                    </a>
                                                </li>';




                                $tabContent .= '
                                <div class="tab-pane fade '.$stepperTab.'" '.$stepperTabId.'  role="tabpanel" data-task="'.$info->id.'" data-step="'.$steps.'">
                                    <div class="row" id="fieldContainer'.$page->id.'">
                                        <form action=""  class="row" id="formField_'.$page->id.'_'.$info->id.'" enctype="multipart/form-data">';
                                if(!empty($contents)){
                                    foreach ($contents as $content) {
                                        if ($content->field_page === $page->id) {
                                            $requiredInput = ($content->is_required == 1 ? 'checked' : '');
                                            if ($content->field_type === 'Radio') {
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 radioContainer_'.$content->id.'">';

                                                        $options = json_decode($content->options, true);

                                                        // Ensure $options is an array after decoding
                                                        if (!is_array($options)) {
                                                            $options = [];
                                                        }

                                                        $optionsArray = reset($options)['options'] ?? [];

                                                        if (!empty($optionsArray)){
                                                            foreach ($optionsArray as $index => $option){

                                                                $tabContent .= '<div class="form-check mb-2 radioCount">
                                                                    <input type="radio" class="form-check-input"
                                                                        name="radio_'. $content->id .'_'.$content->task_id.'"
                                                                        value="'. $option .'"
                                                                        id="radio_'. $content->id .'_'. $index .'" '.($content->answer === $option ? 'checked' : '').'>
                                                                    <label class="form-check-label" for="radio_'. $content->id .'_'. $index .'">'. $option .'</label>
                                                                </div>';
                                                            }
                                                        } else {
                                                            $tabContent .= '<p>No options available</p>';
                                                        }
                                                        $tabContent .= '
                                                        </div>
                                                    </div>
                                                </div>
                                                ';
                                            } else if ($content->field_type === 'Checkbox'){
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check">
                                                                <input type="checkbox" class="form-check-input" id="check_field_'.$content->id.'_'.$content->task_id.'" name="check_label_'.$content->id.'_'.$content->task_id.'" '.($content->answer === 'on' ? 'checked' : '').'>
                                                                <label class="form-check-label" for="check_field_'.$content->id.'">
                                                                    '.($content->options ?? '').'
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                ';
                                            } else if ($content->field_type === 'Text'){
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-check-label" for="text_field_'.$content->id.'">
                                                                '.($content->options ?? '').'
                                                            </label>
                                                            <input type="text" class="form-control" id="text_field_'.$content->id.'" name="text_label_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'">
                                                        </div>
                                                    </div>
                                                </div>
                                                ';
                                            } else if ($content->field_type === 'Textarea'){
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-check-label" for="text_field_'.$content->id.'">
                                                                '.($content->options ?? '').'
                                                            </label>
                                                            <textarea class="form-control" id="text_field_'.$content->id.'" name="area_label_'.$content->id.'_'.$content->task_id.'">'.$content->answer.'</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                ';
                                            } else if ($content->field_type === 'File'){
                                                $filePath = $content->answer; // Path to the uploaded file
                                                $fileUrl = asset($filePath); // Generate the full URL to the file
                                                $fileName = basename($filePath); // Extract the file name from the path
                                                $fileExtension = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file extension

                                                // Determine how to display the file based on its type
                                                $fileDisplay = '';
                                                if (in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                                    // Display images
                                                    $fileDisplay = '
                                                                    <div class="uploaded-files">
                                                                        <div class="files-container">
                                                                            <div class="file-list">
                                                                                <img src="' . $fileUrl . '" alt="video file" class="file-type" style="width: 150px; /* Set fixed width */ height: 150px; /* Set fixed height */ object-fit: contain display: block; margin: auto;">
                                                                                <div class="file-meta">
                                                                                    <div class="meta-info">
                                                                                        <p>' . $fileName . '</p>
                                                                                    </div>
                                                                                    <div class="status-check">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                                                    </div>
                                                                                </div>
                                                                                <a href="' . $fileUrl . '" download="' . $fileName . '" class="btn btn-primary mt-2">Download</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    ';
                                                } else {
                                                    // Display a download link for non-image files
                                                    $fileDisplay = '<div class="uploaded-files">
                                                                        <div class="files-container">
                                                                            <div class="file-list">
                                                                                <div class="file-meta">
                                                                                    <div class="meta-info">
                                                                                        <p>' . $fileName . '</p>
                                                                                    </div>
                                                                                    <div class="status-check">
                                                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                                                    </div>
                                                                                </div>
                                                                                <a href="' . $fileUrl . '" download="' . $fileName . '" class="btn btn-primary mt-2">Download</a>
                                                                            </div>
                                                                        </div>
                                                                    </div>';
                                                }

                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : '').'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : '').'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-check-label" for="text_field_'.$content->id.'">
                                                                '.($content->options).'
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
                                                                            <div class="upload-box">
                                                                                <button type="button" class="remove-file" id="remove-file">&times;</button>
                                                                                <span id="file-selected">' . (!empty($content->answer) ? $fileName : 'No file selected') . '</span>
                                                                                <label for="file-upload" class="custom-file-upload">
                                                                                    Click to upload<br>
                                                                                    <input type="file" id="file-upload" name="file_label_'.$content->id.'_'.$content->task_id.'" class="file drop_'.$content->id.'" accept="*">
                                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-upload">
                                                                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                        <polyline points="17 8 12 3 7 8"></polyline>
                                                                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                                                                    </svg>
                                                                                </label>
                                                                                <span>Maximum file size 5MB.</span>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            ' . (!empty($filePath) ? $fileDisplay : '') . '
                                                        </div>
                                                    </div>
                                                </div>';
                                            } else if ($content->field_type === 'Typography') {
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-check-label" for="typographyView_'.$content->id.'">
                                                                '.($content->options !== null ? $content->options : "").'
                                                            </label>
                                                            <textarea class="form-control border border-primary typography-editor" id="typographyView_'.$content->id.'" name="typography_'.$content->id.'_'.$content->task_id.'">'.$content->answer.'</textarea>
                                                        </div>
                                                    </div>
                                                </div>';

                                                $tabContent .= "
                                                <script>
                                                    setTimeout(() => {
                                                        initTinyMCE();
                                                    }, 100);
                                                </script>";
                                            } else if ($content->field_type === 'Date'){
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <label class="form-check-label" for="date_field_'.$content->id.'">
                                                                '.($content->options !== null ? $content->options : "").'
                                                            </label>
                                                            <input type="date" class="form-control" id="date_field_'.$content->id.'" name="date_label_'.$content->id.'_'.$content->task_id.'" value="'.$content->answer.'">
                                                        </div>
                                                    </div>
                                                </div>';
                                            } else if ($content->field_type === 'Dropdown'){
                                                $tabContent .= '
                                                <div class="col-12 modal-body-bg field_row mb-3 border border-primary" id="field_id_'.$content->field_page.'_'.$content->id.'">
                                                    <div class="row">
                                                        <div class="col-8 mb-3">
                                                            <h4>'.($content->field_label !== null ? $content->field_label : "").'</h4>
                                                            <span class="badge text-bg-secondary">'.($content->field_description !== null ? $content->field_description : "").'</span>
                                                        </div>
                                                        <div class="col-12 mb-3">
                                                            <div class="form-check form-check-inline">
                                                                <input type="checkbox" class="form-check-input" id="checkInline_'.$content->id.'" name="is_required" '.$requiredInput.' disabled>
                                                                <label class="form-check-label" for="checkInline_'.$content->id.'">
                                                                    Required Field
                                                                </label>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 downContainer_'.$content->id.'">
                                                            <select class="form-select" name="dropdown_'.$content->id.'_'.$content->task_id.'" aria-label="Default select example" id="down_'.$content->id.'">
                                                            <option selected value="'.$content->answer.'">'.(!empty($content->answer) ? 'Selected Answer: '.$content->answer : 'Please choose an option').'</option>';
                                                        $options = json_decode($content->options, true);

                                                        // Ensure $options is an array after decoding
                                                        if (!is_array($options)) {
                                                            $options = [];
                                                        }

                                                        $optionsArray = reset($options)['options'] ?? [];

                                                        if (!empty($optionsArray)){
                                                            foreach ($optionsArray as $index => $option){
                                                                $tabContent .= '
                                                                <option value="'. $option .'">'. $option .'</option>
                                                                ';
                                                            }
                                                        } else {
                                                            $tabContent .= '<option value=""></option>';
                                                        }

                                                        $tabContent .= '
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>';
                                            }
                                        }
                                    }
                                } else {
                                    $tabContent .= '
                                    <div class="col-12 modal-body-bg field_row mb-3 border border-primary">
                                        <div class="row text-center">
                                            <h3 class="m-3">There\'s no existing field on this page</h3>
                                        </div>
                                    </div>
                                    ';
                                }

                                $tabContent .= '<div class="col-12 d-grid mb-3"><button type="button" class="btn btn-secondary save-only" data-rel="'.$steps.'" data-page="'.$page->id.'" data-task="'.$info->id.'">Save Changes</button></div>';
                                if ($stepperEnabled && $steps === $totalSteps - 1){
                                    $tabContent .= '<div class="col-12 d-grid"><button type="button" class="btn btn-success finish-step" data-page="'.$page->id.'" data-task="'.$info->id.'">Submit To Check</button></div>';
                                } else if ($stepperEnabled && $steps !== $totalSteps - 1){
                                    $nextStep = $steps + 1;
                                    $tabContent .= '<div class="col-12 d-grid"><button type="button" class="btn btn-primary next-step" data-next="'.$nextStep.'" data-page="'.$page->id.'" data-task="'.$info->id.'">Next</button></div>';
                                } else if(!$stepperEnabled){
                                    $tabContent .= '<div class="col-12 d-grid"><button type="button" class="btn btn-success finish-step" data-page="'.$page->id.'" data-task="'.$info->id.'">Submit To Check</button></div>';
                                }
                                $tabContent .= '
                                        </form>
                                    </div>
                                </div>
                                ';
                                $page_count++;
                            @endphp
                        @endforeach
                    @endif
                    <ul class="nav nav-tabs pageContainer" role="tablist" {{ ($stepperEnabled ? 'id="wizardNav"' : '') }} id="wizardNav">
                        {!! $pageOutput !!}
                    </ul>
                    <div class="tab-content border border-top-0 p-3 contentContainer" id="myTabContent">
                        {!! $tabContent !!}
                    </div>
                </div>
                <div id="panelRequired" class="col-lg-4 modal-body-bg border border-primary mb-3 col-12 order-1 order-lg-2">
                    @php
                        $pageStatus = ''; // Initialize an empty string for page tabs
                        $inputFieldIds = $inputValues->pluck('field_id');
                        $page_count = 1;
                    @endphp
                    @if(!empty($pagesWithContent))
                        @foreach($pagesWithContent as $row)
                            @php
                                $page = $row['pages']; // Page details
                                $contents = $row['contents'];
                                $hasRequiredFields = false;

                                $pageStatus .= '
                                    <div class="col-12 mb-3">
                                        <div class="card shadow rounded-3">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    '.$page->page_title.'
                                                </h6>
                                                <div class="row">
                                                    <div class="d-flex flex-wrap gap-1">
                                                ';

                                                if(!empty($contents)){
                                                    foreach ($contents as $content) {
                                                        if ($content->is_required === 1) {
                                                            $hasRequiredFields = true;
                                                            $inputStatus = ($inputFieldIds->contains($content->id) ? 'bg-primary border border-1 border-white text-white' : 'border border-1 border-primary text-primary bg-white' );
                                                            $inputVal = ($inputFieldIds->contains($content->id) ? ': Field Complete' : ': Field No Value' );
                                                            $pageStatus .= '
                                                            <span class="badge '.$inputStatus.' d-flex align-items-center justify-content-center"
                                                                style="
                                                                    display: flex;
                                                                    width: 65px; /* Fixed width */
                                                                    height: 40px; /* Fixed height */
                                                                    border-radius: 8px;
                                                                    padding: 5px;
                                                                    text-align: center;"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-placement="top"
                                                                title="'.$content->field_label.' '.$inputVal.'">

                                                                <span style="
                                                                    display: block;
                                                                    overflow: hidden;
                                                                    text-overflow: ellipsis;
                                                                    white-space: nowrap;
                                                                    max-width: 100%; /* Ensures text fits within the badge */
                                                                    font-size: 10px;">
                                                                    '.$content->field_label.'
                                                                </span>
                                                            </span>
                                                            ';
                                                        }
                                                    }
                                                }

                                if (!$hasRequiredFields) {
                                    $pageStatus .= '
                                        <div class="col-12 m-3 text-center text-primary">
                                            <h6>There\'s no existing required field on this page.</h6>
                                        </div>
                                    ';
                                }

                                $pageStatus .= '
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ';



                                $page_count++;
                            @endphp
                        @endforeach
                    @endif
                    <h5 class="card-title mb-2">Required Field Status</h5>
                    <div class="row">
                        {!! $pageStatus !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function(){


    function pageContainer(){
        $(`#panelRequired`).load(location.href + ` #panelRequired > *`, function() {
            // Reinitialize Bootstrap tooltips after reloading
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    }

    let token = $('meta[name="csrf-token"]').attr('content');
    let activeTab = $('#myTabContent .tab-pane.show.active').attr('data-step') || $('#myTabContent .tab-pane.show.active').attr('id');

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

    // Initialize TinyMCE on page load
    initTinyMCE();

    function reloadContent(activeTab) {
        $('#myTabContent').load(location.href + '?activeTab=' + activeTab + ' #myTabContent > *', function() {
            if (activeTab) {
                // Hide all tabs
                $('.tab-pane').removeClass('show active');

                // Show the active tab
                $(`#myTabContent .tab-pane[data-step="${activeTab}"]`).addClass('show active');
                $(`#myTabContent .tab-pane[data-step="${activeTab}"]`).attr('aria-selected', 'true');

                // Update the navigation link
                $(`.nav-link[data-step="${activeTab}"]`).addClass('active').attr('aria-selected', 'true');
            }

            tinymce.remove('.typography-editor');
            setTimeout(() => {
                initTinyMCE();
            }, 10);
        });
    }

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

    function saveFieldInput(page, task, from){
        return new Promise((resolve, reject) => {
            console.log(`saveFieldInput called with page=${page}, task=${task}, from=${from}`);

            var form = $(`#formField_${page}_${task}`)[0];
            if (!form) {
                console.error("Form not found:", `#formField_${page}_${task}`);
                resolve(false);
                return;
            }

            var form = $(`#formField_${page}_${task}`)[0];
            var formData = new FormData(form); // Include all form data, including files
            formData.append('from', from);
            $.ajax({
                url: '{{ route("observer.etasks.save") }}',
                method: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from processing the data
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': token  // Include the CSRF token in the request header
                },
                success: function(response) {
                    let formDataEntries = [];
                    formData.forEach((value, key) => {
                        formDataEntries.push(`${key}: ${value}`);
                    });
                    console.log("AJAX success response:", response);
                    if(response.status === 'success') {
                        pageContainer();
                        resolve(true);
                        console.log("AJAX success response redirect:", response.redirect);
                        if (response.redirect) {
                            window.location.href = response.redirect;
                        }
                    } else if(response.status === 'error') {
                        Toast.fire({
                            icon: 'error',
                            title: 'Complete the required field',
                            text: response.message
                        });
                        $('.next-step, .save-only, .finish-step').prop('disabled', false);
                        resolve(false); // Stop next step
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                    resolve(false);
                }
            });
        });
    }

    $(document).on('click', '.save-only', function() {
        var page = $(this).data('page');
        var task = $(this).data('task');
        activeTab = $('#myTabContent .tab-pane.show.active').attr('data-step') || $('#myTabContent .tab-pane.show.active').attr('id');
        $('.next-step').prop('disabled', true);
        $('.save-only').prop('disabled', true);
        $('.finish-step').prop('disabled', true);
        console.log('clicked')
        saveFieldInput(page, task, "save").then(success => {
            if (success) {
                reloadContent(activeTab);

                Toast.fire({
                    icon: 'success',
                    title: 'Successfully Saved',
                });
                $('.next-step').prop('disabled', false);
                $('.save-only').prop('disabled', false);
                $('.finish-step').prop('disabled', false);
            }
        });
    })

    $(document).on("click", ".next-step", function () {
        let nextStep = $(this).data("next");
        var page = $(this).data('page');
        var task = $(this).data('task');

        $('.next-step, .save-only, .finish-step').prop('disabled', true);

        saveFieldInput(page, task, "next").then(success => {
            if (success) {

                activeTab = nextStep;

                $(".nav-link.active").removeClass("active");
                $(".tab-pane.show.active").removeClass("show active");

                // Enable and activate the next nav item
                let nextTab = $(`.nav-link[data-step="${nextStep}"]`);
                nextTab.removeClass("disabled").addClass("active").attr('aria-selected', 'true');

                // Show the next step content
                $(".tab-pane").removeClass("show active");
                $("#step-" + nextStep).addClass("show active");


                reloadContent(nextStep);
                $(`#step-${nextStep}`).addClass('show active');

                nextTab.tab("show");
                setTimeout(() => {
                    $('.next-step, .save-only, .finish-step').prop('disabled', false);
                }, 500);
            }
        });
    });

    $(document).on("click", ".finish-step", function () {
        var page = $(this).data('page');
        var task = $(this).data('task');

        Swal.fire({
            title: 'Are you sure you want to submit task?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes! I want to submit it',
            cancelButtonText: 'No, I don\'t want to'
        }).then((result) => {
            if (result.isConfirmed) {
                saveFieldInput(page, task, "finish").then(success => {
                    if (success) {
                        console.log("Save successful");
                        $('.finish-step').prop('disabled', true);
                    } else {
                        console.log("Save failed");
                    }
                });
            }
        });
    });

    var uStat = $("#panelTask").data("ustat");

    var isWindowActive = true;
    var idleTime = 0;
    var idleLimit = 300;
    var wasIdle = true;
    let lastStatus = "";

    var isInsidePanel = false;

    if ($("#panelTask").length > 0) {
        isInsidePanel = true;
        if (uStat !== "Overtime") {
            updateUserStatus("working");
        }
    }

    $(document).on("mouseenter", "#panelTask", function () {
        isInsidePanel = true;
        resetIdleTime();
        if (uStat !== "Overtime") {
            updateUserStatus("working");
        }
    });

    $(document).on("mouseleave", "#panelTask", function () {
        isInsidePanel = false;
    });

    $(window).on("focus", function () {
        isWindowActive = true;
        if (isInsidePanel && uStat !== "Overtime") {
            resetIdleTime();
            updateUserStatus("working");
        }
    });

    $(window).on("blur", function () {
        isWindowActive = false;
        if (uStat !== "Overtime") {
            updateUserStatus("visibility_switch");
        }
    });

    $(document).on("visibilitychange", function () {
        isWindowActive = !document.hidden;
        if (uStat !== "Overtime") {
            updateUserStatus(document.hidden ? "visibility_switch" : "working");
        }
        if (!document.hidden && isInsidePanel) resetIdleTime();
    });

    function resetIdleTime() {
        idleTime = 0;
        if (wasIdle && uStat !== "Overtime") {
            updateUserStatus("working");
            wasIdle = false;
        }
    }

    // Only reset idle time when interacting **inside** `#panelTask`
    $(document).on("mousemove keypress click scroll touchstart", function (e) {
        if (isWindowActive && isInsidePanel && uStat !== "Overtime") resetIdleTime();
    });

    // Start the idle timer only if inside `#panelTask`
    setInterval(function () {
        if (isWindowActive && isInsidePanel && uStat !== "Overtime") {
            if (idleTime >= idleLimit && !wasIdle) {
                updateUserStatus("idle");
                wasIdle = true;
            } else {
                idleTime++;
            }
        }
    }, 1000);
    var isOvertime = (uStat === "Overtime"); // Check if user is in overtime

    // Intercept all page navigation clicks
    $(document).on("click", "a, button", function (e) {
        var url = $(this).attr("href"); // Get the URL

        if (isOvertime && url && !$(this).hasClass("ignore-leave") && !$(this).closest("#dontInclude").length && !$(this).closest('#panelTask').length  && !$(this).closest(".dropdown-toggle").length && !$(this).closest(".sidebar-toggler").length && !$(this).closest(".sidebar-toggler").length)  {
            e.preventDefault(); // Stop navigation

            Swal.fire({
                title: "Do you want to continue your overtime session later?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes, continue later",
                cancelButtonText: "No, leave now"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                } else {
                    updateUserStatus("beforeunload", true);
                    window.location.href = url;
                }
            });
        }
    });

    $(window).on("beforeunload", function (e) {
        var task_id = $('#panelTask').data('task');

        if (uStat === "Overtime") {
            window.location.href = url;
        }
        updateUserStatus("beforeunload", true);
    });



    function updateUserStatus(eventType, isBeforeUnload = false){
        var task_id = $('#panelTask').data('task');

        // Don't update if the user is not inside #panelTask
        if (!isInsidePanel && eventType !== "beforeunload") return;

        let status =
            eventType === "beforeunload" ? 'not working' :
            (eventType === 'idle' || eventType === 'visibility_switch' ? 'idle' : 'working');

        if (status === lastStatus) return; // Avoid redundant updates
        lastStatus = status;

        let formData = new FormData();
        formData.append('user_status', status);
        formData.append('task_id', task_id);

        if (isBeforeUnload) {
            navigator.sendBeacon("{{ route('observer.etasks.userstatuschecking') }}", formData);
        } else {
            $.ajax({
                url: '{{ route("observer.etasks.userstatuschecking") }}',
                method: 'POST',
                data: formData,
                dataType: 'json',
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': token
                },
                success: function(response) {
                    console.log("Status updated:", response);
                },
                error: function(xhr, status, error) {
                    console.error('Error occurred:', xhr.responseText);
                    console.error('Error occurred:', status);
                    console.error('Error occurred:', error);
                }
            });
        }
    }

});
</script>

@endsection