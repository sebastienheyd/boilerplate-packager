{!! '<?php' !!}

namespace {{ $namespace }};

use Illuminate\Database\Eloquent\Model;
@if (count($relations ?? []))
@foreach($relations as $type => $rels)
@foreach($rels as $rel)
@if(isset($namespaces[$rel['method']]))use {{ $namespaces[$rel['method']] }}\{{ $rel['model'] }};
@endif
@endforeach
@endforeach
@endif
@if (count($relations['belongsTo'] ?? []))use Illuminate\Database\Eloquent\Relations\BelongsTo;
@endif
@if (count($relations['belongsToMany'] ?? []))use Illuminate\Database\Eloquent\Relations\BelongsToMany;
@endif
@if (count($relations['hasMany'] ?? []))use Illuminate\Database\Eloquent\Relations\HasMany;
@endif
@if($hasSoftDelete)use Illuminate\Database\Eloquent\SoftDeletes;
@endif

class {{ $className }} extends Model
{
@if($hasSoftDelete)
    use SoftDeletes;

@endif
    protected $table    = '{{ $table }}';
    protected $fillable = ['{!! $fillable !!}'];
@if(!empty($dates))
    protected $dates    = ['{!! $dates !!}'];
@endif
@if(!empty($hidden))
    protected $hidden   = ['{!! $hidden !!}'];
@endif
@if (!$timestamps)
    public $timestamps  = false;
@endif
@if (count($relations['belongsTo'] ?? []))
@foreach($relations['belongsTo'] as $relation)

    /**
     * @return BelongsTo
     */
    public function {{ Str::singular($relation['method']) }}(): BelongsTo
    {
        return $this->belongsTo({{ $relation['model'] }}::class);
    }
@endforeach
@endif
@if (count($relations['belongsToMany'] ?? []))
@foreach($relations['belongsToMany'] as $relation)

    /**
     * @return BelongsToMany
     */
    public function {{ $relation['method'] }}(): BelongsToMany
    {
        return $this->belongsToMany({{ $relation['model'] }}::class);
    }
@endforeach
@endif
@if (count($relations['hasMany'] ?? []))
@foreach($relations['hasMany'] as $relation)

    /**
     * @return HasMany
     */
    public function {{ $relation['method'] }}(): HasMany
    {
        return $this->hasMany({{ $relation['model'] }}::class);
    }
@endforeach
@endif
}