{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'category' => 'Gestion des {{ $resource }}',
        'access'   => [
            'name'        => 'Accès à la gestion des {{ $resource }}',
            'description' => "Autorise l'accès à la liste, la création, l'édition et à la suppression des {{ $resource }}",
        ],
    ],
@endforeach
];
