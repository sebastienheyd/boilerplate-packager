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
    &lt;x-boilerplate::card>
        &lt;x-boilerplate::datatable name="{{ $resource }}" />
    &lt;/x-boilerplate::card>
@@endsection

@@push('js')
&lt;x-boilerplate::minify>
    <script>
        $(document).on('click', '[data-action="dt-show-element"]', function(e) {
            e.preventDefault();
            $.ajax({
                url: $(this).attr('href'),
                type: 'get',
                success: function(res) {
                    bootbox.dialog({
                        onEscape: true,
                        size: 'xl',
                        message: res
                    })
                }
            })
        })
    </script>
&lt;/x-boilerplate::minify>
@@endpush
