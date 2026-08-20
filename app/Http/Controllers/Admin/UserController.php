<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Support\Uploads;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends ResourceController
{
    protected string $model = User::class;

    protected string $viewPath = 'users';

    protected string $routeName = 'admin.users';

    protected string $labelKey = 'admin.nav.users';

    protected array $searchable = ['name', 'email'];

    protected array $mediaFields = ['avatar' => 'avatars'];

    protected function indexQuery(): Builder
    {
        return User::query()->orderBy('name');
    }

    protected function columns(): array
    {
        return [
            ['label' => __('admin.common.image'), 'type' => 'image', 'value' => fn (User $u) => $u->avatarUrl(), 'class' => 'w-20'],
            ['label' => __('admin.common.name'), 'type' => 'strong', 'key' => 'name'],
            ['label' => __('admin.common.email'), 'type' => 'muted', 'key' => 'email'],
            ['label' => __('admin.common.role'), 'key' => 'role'],
            ['label' => __('admin.common.last_login'), 'value' => fn (User $u) => $u->last_login_at?->diffForHumans() ?? '—'],
            ['label' => __('admin.common.active'), 'type' => 'bool', 'key' => 'is_active'],
        ];
    }

    protected function prepare(array $data, Request $request, Model $record): array
    {
        $data = parent::prepare($data, $request, $record);

        // Blank password on edit means "leave it alone".
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    /** Never let the last active admin lock everyone out, or delete yourself. */
    public function destroy(string $key): RedirectResponse
    {
        $user = $this->resolveRecord($key);

        $lastAdmin = $user->isAdmin()
            && User::where('role', 'admin')->where('is_active', true)->count() <= 1;

        if ($lastAdmin || $user->id === auth()->id()) {
            return back()->with('error', __('admin.auth.forbidden'));
        }

        return parent::destroy($key);
    }

    protected function rules(?Model $record): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($record?->id)],
            'password' => [$record ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'role' => ['required', Rule::in(User::ROLES)],
            'phone' => ['nullable', 'string', 'max:40'],
            'avatar' => Uploads::imageRules(),
            'is_active' => ['boolean'],
        ];
    }
}
