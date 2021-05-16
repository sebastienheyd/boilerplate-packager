{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'title' => '{{ ucfirst($resource) }} management',
        'index' => 'List of {{ $resource }}',
        'add'   => 'Add {{ Str::singular($resource) }}',
    ],
@endforeach
];
