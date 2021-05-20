<table class="table table-striped table-sm">
    <tbody>
    @@foreach(array_keys(${{ Str::singular($resource) }}->getAttributes()) as $attribute)
        <tr>
            <td scope="row"><strong>@{{ $attribute }}</strong></td>
            <td>@{!! nl2br(e(${{ Str::singular($resource) }}->$attribute)) !!}</td>
        </tr>
    @@endforeach
    </tbody>
</table>
