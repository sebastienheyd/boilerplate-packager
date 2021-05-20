{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'create'         => 'Ajouter un {{ Str::singular($resource) }}',
        'create_success' => 'Un {{ Str::singular($resource) }} a été ajouté',
        'delete_confirm' => 'Confirmer la suppression du {{ Str::singular($resource) }} ?',
        'delete_success' => 'Le {{ Str::singular($resource) }} a été supprimé',
        'edit'           => 'Éditer un {{ Str::singular($resource) }}',
        'list'           => 'Liste des {{ $resource }}',
        'save'           => 'Enregistrer',
        'title'          => '{{ ucfirst($resource) }}',
        'update_success' => 'Le {{ Str::singular($resource) }} a été mis à jour',
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
