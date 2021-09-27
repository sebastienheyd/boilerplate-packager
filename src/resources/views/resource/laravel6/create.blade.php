@@extends('boilerplate::layout.index', [
    'title' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title'),
    'subtitle' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create'),
    'breadcrumb' => [
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title') => '{{ $packageName }}.{{ Str::singular($resource) }}.index',
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create')
    ]
])

@@section('content')
    @{!! Form::open(['route' => '{{ $packageName }}.{{ Str::singular($resource) }}.store', 'method' => 'post', 'autocomplete'=> 'off', 'id' => '{{ Str::singular($resource) }}-form']) !!}
        <div class="row py-2">
            <div class="col-12">
                @@include('{{ $packageName }}::{{ Str::singular($resource) }}.formButtons')
            </div>
        </div>
        <div class="row">
            @@include('{{ $packageName }}::{{ Str::singular($resource) }}.form')
        </div>
    @{!! Form::close() !!}
@@endsection
