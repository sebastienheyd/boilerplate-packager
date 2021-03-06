{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'create'         => 'Add a {{ Str::singular($resource) }}',
        'create_success' => 'A {{ Str::singular($resource) }} has been added',
        'delete_confirm' => 'Confirm the deletion of the {{ Str::singular($resource) }}?',
        'delete_success' => '{{ ucfirst(Str::singular($resource)) }} has been deleted',
        'edit'           => 'Editing a {{ Str::singular($resource) }}',
        'list'           => 'List of {{ $resource }}',
        'save'           => 'Save',
        'title'          => '{{ ucfirst($resource) }}',
        'update_success' => 'The {{ Str::singular($resource) }} has been updated',
        'properties'     => [
@foreach($fields[$resource] as $field)
            '{{ $field }}' => '{{ ucfirst(str_replace(['_id', '_'], ['', ' '], $field)) }}',
@endforeach
@foreach($relations[$resource] as $type => $rels)
@foreach($rels as $value)
@if(in_array($type, ['hasMany', 'belongsToMany']))
            '{{ $value['method'] }}' => '{{ ucfirst($value['method']) }}',
@else
            '{{ Str::singular($value['method']) }}' => '{{ ucfirst(Str::singular($value['method'])) }}',
@endif
@endforeach
@endforeach
        ],
    ],
@endforeach
];
