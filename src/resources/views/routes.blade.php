{!! '<?php' !!}

$default = [
    'prefix'     => config('boilerplate.app.prefix', '').'/{{ $package }}',
    'domain'     => config('boilerplate.app.domain', ''),
    'middleware' => [
        'web',
        'boilerplatelocale',
        'boilerplateauth',
        'ability:admin,backend_access,{{ $package }}_access'
    ],
    'as'         => '{{ $package }}.',
    'namespace'  => '\{!! $namespace !!}\Controllers',
];

Route::group($default, function () {
@foreach($models as $model)
    // {{ Str::studly($model) }} routes
@foreach($relations[$model] as $type => $resources)
@foreach($resources as $resource)
    Route::post('{{ Str::singular($model) }}/{{ $resource['method'] }}', ['as' => '{{ Str::singular($model) }}.{{ $resource['method'] }}', 'uses' => '{{ Str::studly(Str::singular($model)) }}Controller{!! '@'.$resource['method'] !!}']);
@endforeach
@endforeach
    Route::post('{{ Str::singular($model) }}/datatable', ['as' => '{{ Str::singular($model) }}.datatable', 'uses' => '{{ Str::studly(Str::singular($model)) }}Controller@datatable']);
    Route::resource('{{ Str::singular($model) }}', '{{ Str::studly(Str::singular($model)) }}Controller');
@endforeach
});
