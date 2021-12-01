{!! '<?php' !!}

@foreach($models as $model)
use \{!! $namespace !!}\Controllers\{{ Str::studly(Str::singular($model)) }}Controller;
@endforeach

$default = [
    'prefix'     => config('boilerplate.app.prefix', '').'/{{ $package }}',
    'domain'     => config('boilerplate.app.domain', ''),
    'as'         => '{{ $package }}.',
    'middleware' => [
        'web',
        'boilerplatelocale',
        'boilerplateauth',
        'ability:admin,backend_access,{{ $package }}_access'
    ],
];

Route::group($default, function () {
@foreach($models as $model)
    // {{ Str::studly($model) }} routes
@foreach($relations[$model] as $type => $resources)
@foreach($resources as $resource)
    Route::post('{{ Str::singular($model) }}/{{ $resource['method'] }}', [{{ Str::studly(Str::singular($model)) }}Controller::class, '{{ $resource['method'] }}'])->name('{{ Str::singular($model) }}.{{ $resource['method'] }}');
@endforeach
@endforeach
    Route::resource('{{ Str::singular($model) }}', {{ Str::studly(Str::singular($model)) }}Controller::class);
@endforeach
});
