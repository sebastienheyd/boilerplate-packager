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
    Route::post('{{ Str::singular($model) }}/datatable', ['as' => '{{ Str::singular($model) }}.datatable', 'uses' => '{{ Str::studly(Str::singular($model)) }}Controller@datatable']);
    Route::resource('{{ Str::singular($model) }}', '{{ Str::studly(Str::singular($model)) }}Controller');
@endforeach
});
