<a href="@{{ route('{!! $packageName.'.'.Str::singular($resource) !!}.index') }}" class="btn btn-default" data-toggle="tooltip" title="@@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.list')">
    <span class="far fa-arrow-alt-circle-left text-muted"></span>
</a>
<span class="btn-group float-right">
    <button type="submit" class="btn btn-primary btn-preview">
        @@lang('{{ $packageName }}::resource.{{ Str::singular($resource) }}.save')
    </button>
</span>
