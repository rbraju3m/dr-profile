<?php

namespace App\Http\Controllers\Admin;

use App\Models\Post;
use App\Models\PostCategory;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostController extends ResourceController
{
    protected string $model = Post::class;

    protected string $viewPath = 'posts';

    protected string $routeName = 'admin.posts';

    protected string $labelKey = 'admin.nav.posts';

    protected array $searchable = ['title_en', 'title_bn'];

    protected array $mediaFields = ['image' => 'posts'];

    protected ?string $slugSource = 'title_en';

    protected function indexQuery(): Builder
    {
        $query = Post::query()->with('category')->orderByDesc('published_at')->orderByDesc('id');

        if (in_array($type = request()->string('type')->toString(), Post::TYPES, true)) {
            $query->where('type', $type);
        }

        return $query;
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.image'), 'type' => 'image', 'value' => fn (Post $p) => $p->imageUrl(), 'class' => 'w-20'],
            ['label' => 'Type', 'value' => fn (Post $p) => match ($p->type) {
                'event' => __('site.nav.events'),
                'blog' => __('site.nav.blog'),
                default => __('site.nav.news'),
            }],
            ['label' => __('admin.common.english'), 'type' => 'strong', 'key' => 'title_en'],
            ['label' => __('site.posts.category'), 'value' => fn (Post $p) => $p->category?->name_en ?? '—'],
            ['label' => __('site.posts.published_on'), 'value' => fn (Post $p) => $p->published_at?->format('d M Y') ?? '—'],
            ['label' => __('admin.common.published'), 'type' => 'bool', 'key' => 'is_published'],
        ];
    }

    protected function formData(?Model $record): array
    {
        return parent::formData($record) + [
            'categories' => PostCategory::active()->ordered()->pluck('name_en', 'id'),
        ];
    }

    protected function afterSave(Model $record, Request $request): void
    {
        // Author is stamped once, on creation, and never rewritten by later edits.
        if (! $record->author_id) {
            $record->forceFill(['author_id' => $request->user()?->id])->saveQuietly();
        }
    }

    protected function prepare(array $data, Request $request, Model $record): array
    {
        $data = parent::prepare($data, $request, $record);

        $data['tags'] = filled($request->input('tags'))
            ? collect(explode(',', $request->string('tags')))->map(fn ($t) => trim($t))->filter()->values()->all()
            : null;

        // Event columns are meaningless on news and blog rows.
        if (($data['type'] ?? null) !== 'event') {
            foreach (['event_start_at', 'event_end_at', 'event_venue_en', 'event_venue_bn', 'event_registration_url'] as $field) {
                $data[$field] = null;
            }
            $data['event_is_online'] = false;
        }

        return $data;
    }

    protected function rules(?Model $record): array
    {
        return [
            'type' => ['required', Rule::in(Post::TYPES)],
            'title_en' => ['required', 'string', 'max:200'],
            'title_bn' => ['nullable', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', Rule::unique('posts', 'slug')->ignore($record?->id)],
            'post_category_id' => ['nullable', Rule::exists('post_categories', 'id')],
            'excerpt_en' => ['nullable', 'string', 'max:600'],
            'excerpt_bn' => ['nullable', 'string', 'max:600'],
            'content_en' => ['nullable', 'string'],
            'content_bn' => ['nullable', 'string'],
            'image' => Uploads::imageRules(),
            'event_start_at' => ['nullable', 'date', 'required_if:type,event'],
            'event_end_at' => ['nullable', 'date', 'after_or_equal:event_start_at'],
            'event_venue_en' => ['nullable', 'string', 'max:200'],
            'event_venue_bn' => ['nullable', 'string', 'max:200'],
            'event_registration_url' => ['nullable', 'url', 'max:500'],
            'event_is_online' => ['boolean'],
            'reading_minutes' => ['nullable', 'integer', 'min:1', 'max:120'],
            'meta_title_en' => ['nullable', 'string', 'max:180'],
            'meta_title_bn' => ['nullable', 'string', 'max:180'],
            'meta_description_en' => ['nullable', 'string', 'max:300'],
            'meta_description_bn' => ['nullable', 'string', 'max:300'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'is_published' => ['boolean'],
        ];
    }
}
