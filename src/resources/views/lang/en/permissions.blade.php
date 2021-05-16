{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'category' => '{{ ucfirst($resource) }} management',
        'access'   => [
            'name'        => 'Access to {{ $resource }} management',
            'description' => 'Allows access to list of {{ $resource }} and to edit, create and delete {{ $resource }}',
        ],
    ],
@endforeach
];
