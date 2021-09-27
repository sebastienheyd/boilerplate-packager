@@extends('boilerplate::layout.index', [
    'title' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title'),
    'subtitle' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create'),
    'breadcrumb' => [
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title') => '{{ $packageName }}.{{ Str::singular($resource) }}.index',
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create')
    ]
])

@@section('content')
    &lt;x-boilerplate::form route="{{ $packageName }}.{{ Str::singular($resource) }}.store" id="{{ Str::singular($resource) }}-form" autocomplete="off">
        <div class="row py-2">
            <div class="col-12">
                @@include('{{ $packageName }}::{{ Str::singular($resource) }}.formButtons')
            </div>
        </div>
        <div class="row">
            @@include('{{ $packageName }}::{{ Str::singular($resource) }}.form')
        </div>
    &lt;/x-boilerplate::form>
@@endsection
