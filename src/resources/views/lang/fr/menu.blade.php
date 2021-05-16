{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'title' => 'Gestion des {{ $resource }}',
        'index' => 'Liste des {{ $resource }}',
        'add'   => 'Ajouter un {{ Str::singular($resource) }}',
    ],
@endforeach
];
