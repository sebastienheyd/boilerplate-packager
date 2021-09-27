@@extends('boilerplate::layout.index', [
    'title' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title'),
    'subtitle' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.edit'),
    'breadcrumb' => [
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.title') => '{{ $packageName }}.{{ Str::singular($resource) }}.index',
        __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.edit')
    ]
])

@@section('content')
    @{!! Form::open(['route' => ['{{ $packageName }}.{{ Str::singular($resource) }}.update', ${{ Str::singular($resource) }}], 'method' => 'patch', 'autocomplete'=> 'off', 'id' => '{{ Str::singular($resource) }}-form']) !!}
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
