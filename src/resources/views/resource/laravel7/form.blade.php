<div class="col-6">
    &lt;x-boilerplate::card>
@foreach($fields as $field)
@if($field['name'] === 'id')
@continue
@endif
@if($field['type'] === 'string')
        &lt;x-boilerplate::input name="{!! $field['name'] !!}" label="{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{!! $field['name'] !!}" :value="${!! Str::singular($resource) !!}->{!! $field['name'] !!} ?? ''" />
@elseif($field['type'] === 'datetime' || $field['type'] === 'date')
        &lt;x-boilerplate::datetimepicker name="{!! $field['name'] !!}" label="{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{!! $field['name'] !!}" :value="${!! Str::singular($resource) !!}->{!! $field['name'] !!} ?? ''" :format="{!! $field['type'] === 'datetime' ? "__('boilerplate::date.YmdHis')"  : "__('boilerplate::date.Ymd')" !!}" />
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
            @@if(old('{{ $relation['method'] }}', isset(${{ Str::singular($resource) }}) ? ${{ Str::singular($resource) }}->{{ $relation['method'] }}->pluck('{{ $relation['idField'] }}')->toArray() : []))
                @@foreach(old('{{ $relation['method'] }}', ${{ Str::singular($resource) }}->{{ $relation['method'] }}->pluck('{{ $relation['idField'] }}')->toArray() ?? []) as $id)
                    <option value="@{{ $id }}" selected>@{{ \{!! $namespace !!}\Models\{!! Str::studly(Str::singular($relation['method'])) !!}::find($id)->{!! Str::singular($relation['labelField']) !!} }}</option>
                @@endforeach
            @@endif
        @@endcomponent
@break
@default
        @@component('boilerplate::select2', ['name' => '{{ Str::singular($relation['method']) }}_id', 'label' => '{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ Str::singular($relation['method']) }}', 'ajax' => route('{{ $packageName }}.{{ Str::singular($resource) }}.{{ $relation['method'] }}'), 'minimum-results-for-search' => 10{{ $relation['required'] ? '' : ", 'allow-clear' => true" }}])
            @@if(old('{{ Str::singular($relation['method']) }}_id', ${{ Str::singular($resource) }}->{{ Str::singular($relation['method']) }} ?? false))
                <option value="@{{ old('{!! Str::singular($relation['method']) !!}_id', ${!! Str::singular($resource) !!}->{!! Str::singular($relation['method']) !!}->{!! Str::singular($relation['idField']) !!}) }}" selected>@{{ \{!! $namespace !!}\Models\{!! Str::studly(Str::singular($relation['method'])) !!}::find(old('{!! Str::singular($relation['method']) !!}_id', ${!! Str::singular($resource) !!}->{!! Str::singular($relation['method']) !!}->{!! Str::singular($relation['idField']) !!}) )->{!! Str::singular($relation['labelField']) !!} }}</option>
            @@endif
        @@endcomponent
@break
@endswitch
@endforeach
@endforeach
    &lt;/x-boilerplate::card>
</div>