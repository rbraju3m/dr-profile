<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-admin.input name="name" label="Name" :value="$record?->name" required/>
                    <x-admin.input name="email" type="email" :label="__('admin.auth.email')" :value="$record?->email" required/>
                    <x-admin.input name="password" type="password" :label="__('admin.auth.password')"
                                   :required="! $record" autocomplete="new-password"
                                   :hint="$record ? 'Leave blank to keep the current password.' : 'At least 8 characters.'"/>
                    <x-admin.input name="password_confirmation" type="password" label="Confirm password"
                                   :required="! $record" autocomplete="new-password"/>
                    <x-admin.input name="phone" label="Phone" :value="$record?->phone"/>
                    <x-admin.select name="role" label="Role" required :value="$record?->role ?? 'editor'"
                                    :options="['admin' => 'Admin — full access', 'editor' => 'Editor — content and appointments']"/>
                </div>
            </x-admin.card>
        </div>

        <x-admin.card>
            <div class="space-y-4">
                <x-admin.image-upload name="avatar" label="Avatar" :current="$record?->avatarUrl()"/>
                <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"
                                hint="Deactivated accounts are signed out immediately."/>
            </div>
        </x-admin.card>
    </div>
</x-admin.form-shell>
