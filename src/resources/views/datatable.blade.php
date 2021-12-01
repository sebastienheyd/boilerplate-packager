{!! '<?php' !!}

namespace {{ $namespace }}\Datatables;

use Sebastienheyd\Boilerplate\Datatables\Button;
use Sebastienheyd\Boilerplate\Datatables\Column;
use Sebastienheyd\Boilerplate\Datatables\Datatable;
use {{ $namespace }}\Models\{{ $className }};

class {{ Str::plural($className) }}Datatable extends Datatable
{
    public $slug = '{{ Str::lower(Str::plural($className)) }}';

    public function datasource()
    {
        return {{ $className }}::query();
    }

    public function setUp()
    {
        $this->order('{{ $columns[0]['name'] }}', 'desc');
    }

    public function columns(): array
    {
        return [
@foreach($columns as $column)
@if(in_array($column['type'], ['date', 'datetime']))
            Column::add(__('{{ $packageName }}::resource.{{ Str::lower($className) }}.properties.{{ $column['name'] }}'))
                ->width('180px')
                ->data('{{ $column['name'] }}')
                ->dateFormat({!! $column['type'] === 'date' ? "__('boilerplate::date.Ymd')" : ''  !!}),
@elseif($column['name'] === 'id')
            Column::add(__('{{ $packageName }}::resource.{{ Str::lower($className) }}.properties.{{ $column['name'] }}'))
                ->width('60px')
                ->data('{{ $column['name'] }}'),
@elseif($column['type'] === 'text')
            Column::add(__('{{ $packageName }}::resource.{{ Str::lower($className) }}.properties.{{ $column['name'] }}'))
                ->data('{{ $column['name'] }}', function({{ $className }} ${{ Str::lower($className) }}) {
                    return \Str::limit(strip_tags(${{ Str::lower($className) }}->{{ $column['name'] }}), 40);
                }),
@else
            Column::add(__('{{ $packageName }}::resource.{{ Str::lower($className) }}.properties.{{ $column['name'] }}'))
                ->data('{{ $column['name'] }}'),
@endif

@endforeach
            Column::add()
                ->width('20px')
                ->actions(function ({{ $className }} ${{ Str::lower($className) }}) {
                    return implode([
                        Button::add()
                            ->route('{{ $packageName }}.{{ Str::lower($className) }}.show', ${{ Str::lower($className) }})
                            ->class('show-{{ Str::lower($className) }}')
                            ->color('default')
                            ->icon('eye')
                            ->make(),

                        Button::add()
                            ->route('{{ $packageName }}.{{ Str::lower($className) }}.edit', ${{ Str::lower($className) }})
                            ->class('edit-{{ Str::lower($className) }}')
                            ->color('primary')
                            ->icon('pencil-alt')
                            ->make(),

                        Button::add()
                            ->route('{{ $packageName }}.{{ Str::lower($className) }}.destroy', ${{ Str::lower($className) }})
                            ->class('delete-{{ Str::lower($className) }}')
                            ->color('danger')
                            ->icon('trash')
                            ->make(),
                    ]);
                }),
        ];
    }
}
