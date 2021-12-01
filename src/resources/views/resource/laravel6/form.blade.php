<div class="col-6">
    @@component('boilerplate::card')
@foreach($fields as $field)
@if($field['name'] === 'id')
@continue
@endif
@if($field['type'] === 'datetime' || $field['type'] === 'date')
        @@component('boilerplate::datetimepicker', ['name' => '{!! $field['name'] !!}', 'class' => 'datetimepicker-input', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{!! $field['name'] !!}', 'value' => ${!! Str::singular($resource) !!}->{!! $field['name'] !!} ?? '', 'format' => {!! $field['type'] === 'datetime' ? "__('boilerplate::date.YmdHis')"  : "__('boilerplate::date.Ymd')" !!}])@@endcomponent
@elseif($field['type'] === 'boolean')
        @@component('boilerplate::icheck', ['name' => '{!! $field['name'] !!}', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{!! $field['name'] !!}', 'checked' => old('{!! $field['name'] !!}', ${!! Str::singular($resource) !!}->{!! $field['name'] !!} ?? false)])@@endcomponent
@else
        @@component('boilerplate::input', ['name' => '{!! $field['name'] !!}', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{!! $field['name'] !!}', 'value' => ${!! Str::singular($resource) !!}->{!! $field['name'] !!} ?? ''])@@endcomponent
@endif
@endforeach
@foreach($relations as $type => $rels)
@foreach($rels as $relation)
@switch($type)
@case('hasMany')
@break
@case('belongsToMany')
        @@component('boilerplate::select2', ['name' => '{{ $relation['method'] }}[]', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ $relation['method'] }}', 'ajax' => route('{{ $packageName }}.{{ Str::singular($resource) }}.{{ $relation['method'] }}'), 'multiple' => true])
            @@foreach(old('{{ $relation['method'] }}', isset(${{ Str::singular($resource) }}) ? ${{ Str::singular($resource) }}->{{ $relation['method'] }}->pluck('{{ $relation['idField'] }}')->toArray() : []) as $id)
                <option value="@{{ $id }}" selected>@{{ {!! isset($namespaces[$relation['method']]) ? '\\'.$namespaces[$relation['method']].'\\'.$relation['model'] : '\\'.$namespace.'\Models\\'.$relation['model'] !!}::find($id)->{!! Str::singular($relation['labelField']) !!} }}</option>
            @@endforeach
        @@endcomponent
@break
@default
        @@component('boilerplate::select2', ['name' => '{{ Str::singular($relation['method']) }}_id', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ Str::singular($relation['method']) }}', 'ajax' => route('{{ $packageName }}.{{ Str::singular($resource) }}.{{ $relation['method'] }}'), 'minimum-results-for-search' => 10{{ $relation['required'] ? '' : ", 'allow-clear' => true" }}])
            @@if(old('{{ Str::singular($relation['method']) }}_id', ${{ Str::singular($resource) }}->{{ Str::singular($relation['method']) }} ?? false))
                <option value="@{{ old('{!! Str::singular($relation['method']) !!}_id', ${!! Str::singular($resource) !!}->{!! Str::singular($relation['method']) !!}->{!! Str::singular($relation['idField']) !!}) }}" selected>@{{ ${!! Str::singular($resource) !!}->{!! Str::singular($relation['method']) !!}()->getRelated()->find(old('{!! Str::singular($relation['method']) !!}_id', ${!! Str::singular($resource) !!}->{!! Str::singular($relation['method']) !!}->{!! Str::singular($relation['idField']) !!}) )->{!! Str::singular($relation['labelField']) !!} }}</option>
            @@endif
        @@endcomponent
@break
@endswitch
@endforeach
@endforeach
    @@endcomponent
</div>