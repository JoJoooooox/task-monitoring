@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="row">
        <div class="col-xl-12 mb-3 stretch-card">
            <div class="card shadow-lg">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-baseline mb-2">
                <h6 class="card-title mb-0">System Log</h6>
                </div>
                <div class="table-responsive">
                <table class="table table-hover mb-0 text-center" id="LogTable">
                    <thead>
                    <tr>
                        <th class="pt-0">Name</th>
                        <th class="pt-0">Date</th>
                        <th class="pt-0">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @if(!empty($logs))
                            @foreach($logs as $log)
                            <tr>
                                <td>{{$log->name}}</td>
                                <td data-order="{{ $log->original_time }}">{{$log->formatted_time}}</td>
                                <td class="action-buttons">
                                    <button class="btn btn-primary mx-1 btn-hover viewActionTaken" data-name="{{$log->name}}" data-action="{{$log->action}}"><i data-feather="eye" class="icon-sm icon-wiggle"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="actionTakenModal" tabindex="-1" aria-labelledby="actionTakenModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Description</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="btn-close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title" id="nameLog">Name: </h5>
                            <p class="card-text" id="actionLog">Action Taken: </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-hover" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {

    $('#LogTable').DataTable({
        "aLengthMenu": [
            [10, 30, 50, -1],
            [10, 30, 50, "All"]
        ],
        "iDisplayLength": 10,
        "language": {
            search: ""
        },
        "order": [[1, "desc"]], // Sort by the second column (Date) in descending order
        "columnDefs": [
            {
                "targets": 1, // Target the Date column (second column, index 1)
                "type": "date", // Use date sorting
                "render": function(data, type, row) {
                    // For display, use the formatted time
                    if (type === 'display') {
                        return data;
                    }
                    // For sorting, use the original timestamp from data-order attribute
                    return $(row[1]).data('order') || data;
                }
            }
        ]
    });

    // Customize search and length controls
    $('#LogTable').each(function() {
        var datatable = $(this);
        var search_input = datatable.closest('.dataTables_wrapper').find('div[id$=_filter] input');
        search_input.attr('placeholder', 'Search');
        search_input.removeClass('form-control-sm');
        var length_sel = datatable.closest('.dataTables_wrapper').find('div[id$=_length] select');
        length_sel.removeClass('form-control-sm');
    });

    $(document).on('click', '.viewActionTaken', function() {
        var name = $(this).data('name');
        var action = $(this).data('action');

        $('#actionTakenModal').modal('show');
        $('#nameLog').html(name);
        $('#actionLog').html(action);
    });
})
</script>
@endsection