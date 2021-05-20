@@extends('boilerplate::layout.index', [
    'title' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title'),
    'subtitle' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.list'),
    'breadcrumb' => [
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title'),
    ]
])

@@section('content')
    <div class="row">
        <div class="col-sm-12 mb-3">
            <span class="btn-group float-right">
                <a href="@{{ route("{!! $packageName.'.'.Str::singular($resource) !!}.create") }}" class="btn btn-primary">
                    @@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create')
                </a>
            </span>
        </div>
    </div>
    @@component('boilerplate::card')
        <div class="table-responsive">
            <table class="table table-striped table-hover va-middle" id="menus-table">
                <thead>
                    <tr>
@foreach($fields as $field)
                        <th>@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ $field['name'] }}')</th>
@endforeach
                        <th>{{-- buttons --}}</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @@endcomponent
@@endsection

@@include('boilerplate::load.datatables')

@@push('js')
    <script>
        dTable = $('#menus-table').DataTable({
            processing: true,
            serverSide: true,
            autoWidth: false,
            ajax: {
                url: '@{!! route('{{ $packageName }}.{{ Str::singular($resource) }}.datatable') !!}',
                type: 'post',
            },
            order: [[0, 'desc']],
            columns: [
@foreach($fields as $field)
                {data: '{!! $field['name'] !!}', name: '{!! $field['name'] !!}'},
@endforeach
                {
                    data: 'buttons',
                    name: 'buttons',
                    orderable: false,
                    searchable: false,
                    width: '60px',
                    class: "visible-on-hover text-nowrap"
                }
            ]
        })

        $(document).on('click', 'button[data-action=show]', function (e) {
            $.ajax({
                url: $(this).data('href'),
                type: 'get',
                success: function (res) {
                    bootbox.dialog({
                        onEscape: true,
                        size: 'xl',
                        message: res
                    })
                }
            })
        })

        $(document).on('click', 'button[data-action=delete]', function (e) {
            e.preventDefault()
            let url = $(this).data('href')
            bootbox.confirm("@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.delete_confirm')", function (res) {
                if (res === false) {
                    return
                }

                $.ajax({
                    url: url,
                    type: 'delete',
                    success: function (res) {
                        if (res.success) {
                            dTable.ajax.reload()
                            growl("@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.delete_success')", "success")
                        }
                    }
                })
            })
        })
    </script>
@@endpush
