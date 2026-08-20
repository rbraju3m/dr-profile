<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Shared CRUD for the admin resources.
 *
 * Each child declares its model, view folder, route name and validation rules;
 * everything below — listing, search, pagination, file handling, slugging and
 * flash messages — is written once here. The index table is rendered from the
 * columns() definition, while every resource keeps its own hand-written form.
 */
abstract class ResourceController extends Controller
{
    /** @var class-string<Model> */
    protected string $model;

    /** Blade folder under resources/views/admin. */
    protected string $viewPath;

    /** Route name prefix, e.g. "admin.services". */
    protected string $routeName;

    /** Translation key for the resource label, e.g. "admin.nav.services". */
    protected string $labelKey;

    /** Columns searched by the index search box. */
    protected array $searchable = [];

    /** Upload columns => storage folder. */
    protected array $mediaFields = [];

    /** Source column for an auto-generated slug, when the table has one. */
    protected ?string $slugSource = null;

    protected int $perPage = 20;

    /** The only flags the listing switches may change. */
    public const TOGGLEABLE = [
        'is_active', 'is_published', 'is_featured',
        'accepts_online_booking', 'show_in_footer', 'is_read',
    ];

    /** Whether rows can be dragged into a manual order. */
    protected bool $reorderable = false;

    /** @return array<int, array{key?: string, label: string, type?: string, value?: callable, class?: string}> */
    abstract protected function columns(): array;

    abstract protected function rules(?Model $record): array;

    // ---------------------------------------------------------------- CRUD

    public function index(Request $request): View
    {
        $search = $request->string('q')->trim()->toString();

        $records = $this->indexQuery()
            ->when($search && $this->searchable, function (Builder $query) use ($search) {
                $query->where(function (Builder $inner) use ($search) {
                    foreach ($this->searchable as $column) {
                        $inner->orWhere($column, 'like', "%{$search}%");
                    }
                });
            })
            ->paginate($this->perPage)
            ->withQueryString();

        return view('admin.resource.index', [
            'records' => $records,
            'reorderable' => $this->reorderable && $records->currentPage() === 1,
            'columns' => $this->columns(),
            'routeName' => $this->routeName,
            'label' => __($this->labelKey),
            'search' => $search,
            'searchable' => (bool) $this->searchable,
        ]);
    }

    public function create(): View
    {
        return view("admin.{$this->viewPath}.form", $this->formData(null));
    }

    public function store(Request $request): RedirectResponse
    {
        $record = new $this->model;
        $data = $this->validated($request, null);

        $record->fill($this->prepare($data, $request, $record))->save();
        $this->afterSave($record, $request);

        return redirect()
            ->route("{$this->routeName}.index")
            ->with('success', __('admin.flash.created', ['item' => __($this->labelKey)]));
    }

    public function edit(string $record): View
    {
        return view("admin.{$this->viewPath}.form", $this->formData($this->resolveRecord($record)));
    }

    public function update(Request $request, string $record): RedirectResponse
    {
        $record = $this->resolveRecord($record);
        $data = $this->validated($request, $record);

        $record->fill($this->prepare($data, $request, $record))->save();
        $this->afterSave($record, $request);

        return redirect()
            ->route("{$this->routeName}.index")
            ->with('success', __('admin.flash.updated', ['item' => __($this->labelKey)]));
    }

    public function destroy(string $key): RedirectResponse
    {
        $record = $this->resolveRecord($key);

        foreach ($this->mediaFields as $field => $folder) {
            app(MediaService::class)->delete($record->{$field});
        }

        $record->delete();

        return redirect()
            ->route("{$this->routeName}.index")
            ->with('success', __('admin.flash.deleted', ['item' => __($this->labelKey)]));
    }

    /**
     * Persist a new hand-made order.
     *
     * The row ids arrive in the order they now appear. Anything not belonging
     * to this resource is ignored rather than trusted, and the writes go in one
     * transaction so a half-applied order cannot survive a failure.
     */
    public function reorder(Request $request): JsonResponse
    {
        abort_unless($this->reorderable, 404);

        $ids = $request->validate([
            'ids' => ['required', 'array', 'max:500'],
            'ids.*' => ['required', 'integer'],
        ])['ids'];

        $known = $this->model::query()->whereIn('id', $ids)->pluck('id')->all();

        DB::transaction(function () use ($ids, $known) {
            foreach (array_values($ids) as $position => $id) {
                if (in_array($id, $known, true)) {
                    $this->model::query()->whereKey($id)->update(['sort_order' => $position]);
                }
            }
        });

        return response()->json(['ok' => true, 'count' => count($known)]);
    }

    /**
     * Flip a boolean straight from the listing.
     *
     * Only the flags below can be switched, whatever the request asks for —
     * otherwise this endpoint would happily set any column on any row.
     */
    public function toggle(Request $request, string $record): JsonResponse
    {
        $column = $request->validate([
            'column' => ['required', 'string', Rule::in(self::TOGGLEABLE)],
        ])['column'];

        $model = $this->resolveRecord($record);

        abort_unless(array_key_exists($column, $model->getAttributes()), 404);

        $model->forceFill([$column => ! $model->{$column}])->save();

        return response()->json([
            'value' => (bool) $model->{$column},
        ]);
    }

    // ------------------------------------------------------------- Hooks

    /**
     * Resolve the route's "record" segment to a model.
     *
     * Implicit binding cannot help here: the base controller type-hints the
     * abstract Model, so each child resolves against its own class using
     * whatever getRouteKeyName() it declares (id for most, slug for others).
     */
    protected function resolveRecord(string $key): Model
    {
        $instance = new $this->model;

        return $this->model::query()
            ->where($instance->getRouteKeyName(), $key)
            ->firstOrFail();
    }

    protected function indexQuery(): Builder
    {
        return $this->model::query()->orderByDesc('id');
    }

    /** Extra variables every form view receives. */
    protected function formData(?Model $record): array
    {
        return [
            'record' => $record,
            'routeName' => $this->routeName,
            'label' => __($this->labelKey),
        ];
    }

    protected function validated(Request $request, ?Model $record): array
    {
        return $request->validate($this->rules($record));
    }

    /** Runs after the model is saved — override for relations or pivots. */
    protected function afterSave(Model $record, Request $request): void
    {
        //
    }

    /**
     * Normalise the validated payload: handle uploads, generate a slug and
     * coerce empty strings to null so the database stores real nulls.
     */
    protected function prepare(array $data, Request $request, Model $record): array
    {
        $media = app(MediaService::class);

        foreach ($this->mediaFields as $field => $folder) {
            $data[$field] = $media->replace(
                $request->file($field),
                $record->{$field},
                $folder,
                $request->boolean("remove_{$field}"),
            );
        }

        if ($this->slugSource) {
            $data['slug'] = $this->makeSlug($data, $record);
        }

        return array_map(fn ($value) => $value === '' ? null : $value, $data);
    }

    protected function makeSlug(array $data, Model $record): string
    {
        $provided = $data['slug'] ?? null;
        $base = Str::slug($provided ?: ($data[$this->slugSource] ?? '')) ?: Str::random(8);

        $slug = $base;
        $suffix = 2;

        while ($this->model::where('slug', $slug)->whereKeyNot($record->getKey())->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
