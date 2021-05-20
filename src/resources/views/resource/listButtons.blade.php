<button data-action="show" data-href="@{{ route('{!! $packageName.'.'.Str::singular($resource) !!}.show', ${!! Str::singular($resource) !!}) }}" class="btn btn-sm btn-default">
    <span class="fa fa-eye"></span>
</button>
<a href="@{{ route('{!! $packageName.'.'.Str::singular($resource) !!}.edit', ${!! Str::singular($resource) !!}) }}" class="btn btn-sm btn-primary">
    <span class="fa fa-pencil-alt"></span>
</a>
<button data-action="delete" data-href="@{{ route('{!! $packageName.'.'.Str::singular($resource) !!}.destroy', ${!! Str::singular($resource) !!}) }}" class="btn btn-sm btn-danger">
    <span class="fa fa-trash"></span>
</button>
