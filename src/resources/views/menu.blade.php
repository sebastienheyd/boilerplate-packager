{!! '<?php' !!}

namespace {{ Str::studly($vendor) }}\{{ Str::studly($packageName) }}\Menu;

use Sebastienheyd\Boilerplate\Menu\Builder;

class {{ Str::studly($packageName) }}Menu
{
    public function make(Builder $menu)
    {
@foreach($models as $model)
@php($model = Str::singular($model))
        $menu->add(__('{{ $packageName }}::menu.{{ $model }}.title'), [
            'permission' => '{{ $model }}_access',
            'icon'       => 'cube'     // https://fontawesome.com/icons?d=gallery&m=free
        ])->id('{{ Str::snake(Str::studly($packageName)) }}_{{ $model }}')->activeIfRoute('{{ $packageName }}.{{ $model }}.*')->order(100);

        $menu->addTo('{{ Str::snake(Str::studly($packageName)) }}_{{ $model }}', __('{{ $packageName }}::menu.{{ $model }}.index'), [
            'route'      => '{{ $packageName }}.{{ $model }}.index',
            'permission' => '{{ $model }}_access'
        ])->activeIfRoute(['{{ $packageName }}.{{ $model }}.index', '{{ $packageName }}.{{ $model }}.edit']);

        $menu->addTo('{{ Str::snake(Str::studly($packageName)) }}_{{ $model }}', __('{{ $packageName }}::menu.{{ $model }}.add'), [
            'route'      => '{{ $packageName }}.{{ $model }}.create',
            'permission' => '{{ $model }}_access'
        ])->activeIfRoute(['{{ $packageName }}.{{ $model }}.add']);

@endforeach
    }
}
