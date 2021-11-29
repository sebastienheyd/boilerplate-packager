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
        @@component('boilerplate::datatable', ['name' => "'".$resource."'"])@@endcomponent
    @@endcomponent
@@endsection

@@push('js')
@@component('boilerplate::minify')
    <script>
        $(document).on('click', 'a.show-{{ Str::singular($resource) }}', function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('href'),
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

        $(document).on('click', 'a.delete-{{ Str::singular($resource) }}', function(e) {
            e.preventDefault();
            let url = $(this).attr('href')
            bootbox.confirm("@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.delete_confirm')", function (res) {
                if (res === false) {
                    return
                }

                $.ajax({
                    url: url,
                    type: 'delete',
                    success: function (res) {
                        if (res.success) {
                            dt{{ Str::studly($resource) }}.ajax.reload();
                            growl("@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.delete_success')", "success")
                        }
                    }
                })
            })
        })
    </script>
@@endcomponent
@@endpush
