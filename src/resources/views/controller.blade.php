{!! '<?php' !!}

namespace {{ $namespace }}\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;
use Yajra\DataTables\DataTables;
use {{ $namespace }}\Models\{{ Str::studly(Str::singular($resource)) }};
@foreach($relations as $type => $rels)
@foreach($rels as $relation)
use {{ $namespace }}\Models\{{ $relation['model'] }};
@endforeach
@endforeach

class {{ Str::studly(Str::singular($resource)) }}Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    public function index()
    {
        return view('{{ $packageName }}::{{ Str::singular($resource) }}.list');
    }

    /**
     * Get datatable of the resource.
     *
     * @param DataTables $dataTables
     *
     * @link https://yajrabox.com/docs/laravel-datatables
     *
     * @throws Throwable
     * @return mixed
     */
    public function datatable(DataTables $dataTables)
    {
        return $dataTables->eloquent({{ Str::studly(Str::singular($resource)) }}::query())
            ->rawColumns(['buttons'])
            ->editColumn('buttons', function (${{ Str::singular($resource) }}) {
                return view('{{ $packageName }}::{{ Str::singular($resource) }}.listButtons', ['{{ Str::singular($resource) }}' => ${{ Str::singular($resource) }}])->render();
            })->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        return view('{{ $packageName }}::{{ Str::singular($resource) }}.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     *
     * @throws ValidationException
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
@foreach($fillable as $field)
            '{{ $field['name'] }}' => '{{ $field['rules'] }}',
@endforeach
        ],[],[
@foreach($fillable as $field)
            '{{ $field['name'] }}' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ $field['name'] }}'),
@endforeach
        ]);

@foreach($fillable as $field)
@if($field['type'] === 'boolean')
        $request->merge(['{{ $field['name'] }}' => $request->has('{{ $field['name'] }}')]);

@endif
@endforeach
        ${{ Str::singular($resource) }} = {{ Str::studly(Str::singular($resource)) }}::create($request->post());

@foreach($relations as $type => $rels)
@foreach($rels as $relation)
@if(in_array($type, ['hasMany', 'belongsToMany']))
        ${{ Str::singular($resource) }}->{{ $relation['method'] }}()->sync($request->post('{{ $relation['method'] }}'));

@endif
@endforeach
@endforeach
        return redirect()
            ->route('{{ $packageName }}.{{ Str::singular($resource) }}.edit', ${{ Str::singular($resource) }})
            ->with('growl', [__('{{ $packageName }}::resource.{{ Str::singular($resource) }}.create_success'), 'success']);
    }

    /**
     * Display the specified resource.
     *
     * @param  {{ Str::studly(Str::singular($resource)) }}  ${{ Str::singular($resource) }}
     * @return Application|Factory|Response|View
     */
    public function show({{ Str::studly(Str::singular($resource)) }} ${{ Str::singular($resource) }})
    {
        return view('{{ $packageName }}::{{ Str::singular($resource) }}.show', compact('{{ Str::singular($resource) }}'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  {{ Str::studly(Str::singular($resource)) }}  ${{ Str::singular($resource) }}
     * @return Application|Factory|Response|View
     */
    public function edit({{ Str::studly(Str::singular($resource)) }} ${{ Str::singular($resource) }})
    {
        return view('{{ $packageName }}::{{ Str::singular($resource) }}.edit', compact('{{ Str::singular($resource) }}'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  {{ Str::studly(Str::singular($resource)) }}   ${{ Str::singular($resource) }}
     *
     * @throws ValidationException
     * @return RedirectResponse
     */
    public function update(Request $request, {{ Str::studly(Str::singular($resource)) }} ${{ Str::singular($resource) }})
    {
        $this->validate($request, [
@foreach($fillable as $field)
            '{{ $field['name'] }}' => '{{ $field['rules'] }}',
@endforeach
        ],[],[
@foreach($fillable as $field)
            '{{ $field['name'] }}' => __('{{ $packageName }}::resource.{{ Str::singular($resource) }}.properties.{{ $field['name'] }}'),
@endforeach
        ]);

@foreach($fillable as $field)
@if($field['type'] === 'boolean')
        $request->merge(['{{ $field['name'] }}' => $request->has('{{ $field['name'] }}')]);

@endif
@endforeach
        ${{ Str::singular($resource) }}->update($request->post());

@foreach($relations as $type => $rels)
@foreach($rels as $relation)
@if(in_array($type, ['hasMany', 'belongsToMany']))
        ${{ Str::singular($resource) }}->{{ $relation['method'] }}()->sync($request->post('{{ $relation['method'] }}'));

@endif
@endforeach
@endforeach
        return redirect()
            ->route('{{ $packageName }}.{{ Str::singular($resource) }}.edit', ${{ Str::singular($resource) }})
            ->with('growl', [__('{{ $packageName }}::resource.{{ Str::singular($resource) }}.update_success'), 'success']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  {{ Str::studly(Str::singular($resource)) }}   ${{ Str::singular($resource) }}
     *
     * @throws Exception
     * @return JsonResponse
     */
    public function destroy({{ Str::studly(Str::singular($resource)) }} ${{ Str::singular($resource) }})
    {
        return response()->json(['success' => ${{ Str::singular($resource) }}->delete() ?? false]);
    }
@foreach($relations as $type => $rels)
@foreach($rels as $relation)

    /**
     * Get {{ $relation['method'] }} for select2.
     */
    public function {{ $relation['method'] }}(Request $request)
    {
        return response()->json([
            'results' => {{ $relation['model'] }}::selectRaw('{{ $relation['idField'] }} as id, {{ $relation['labelField'] }} as text')
                ->where('{{ $relation['labelField'] }}', 'like', $request->input('q').'%')
                ->get()->toArray()
        ]);
    }
@endforeach
@endforeach
}
