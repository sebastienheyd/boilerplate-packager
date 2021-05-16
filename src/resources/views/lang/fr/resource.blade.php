{!! '<?php' !!}

return [
@foreach($resources as $resource)
    '{{ Str::singular($resource) }}' => [
        'create'         => 'Ajouter un {{ Str::singular($resource) }}',
        'create_success' => 'Un {{ Str::singular($resource) }} a été ajouté',
        'delete_confirm' => 'Confirmer la suppression du {{ Str::singular($resource) }} ?',
        'delete_success' => 'Le {{ Str::singular($resource) }} a été supprimé',
        'edit'           => 'Éditer un {{ Str::singular($resource) }}',
        'id'             => 'Id',
        'label'          => 'Libellé',
        'list'           => 'Liste des {{ $resource }}',
        'save'           => 'Enregistrer',
        'title'          => '{{ ucfirst($resource) }}',
        'update_success' => 'Le {{ Str::singular($resource) }} a été mis à jour',
        'properties'     => [
@foreach($fields[$resource] as $field)
            '{{ $field }}' => '{{ ucfirst(str_replace(['_id', '_'], ['', ' '], $field)) }}',
@endforeach
@foreach($relations[$resource] as $relation)
@foreach($relation as $type => $value)
            '{{ $value['method'] }}' => '{{ ucfirst($value['method']) }}',
@endforeach
@endforeach
        ],
    ],
@endforeach
];
