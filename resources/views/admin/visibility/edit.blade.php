<x-layouts.admin :title="__('admin.visibility.title')">
    <x-admin.page-header :title="__('admin.visibility.title')" :subtitle="__('admin.visibility.intro')"/>

    <form method="POST" action="{{ route('admin.visibility.update') }}" class="space-y-6">
        @csrf
        @method('PUT')

        <x-admin.card :title="__('admin.visibility.appearance.title')"
                      :subtitle="__('admin.visibility.appearance.intro')">
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach (App\Support\Theme::CHOICES as $choice)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3.5 transition has-[:checked]:border-primary-400 has-[:checked]:bg-primary-50/40">
                        <input type="radio" name="theme_default" value="{{ $choice }}"
                               @checked(old('theme_default', $theme) === $choice)
                               class="mt-0.5 h-4 w-4 border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-slate-800">{{ __('admin.visibility.appearance.'.$choice) }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ __('admin.visibility.appearance.'.$choice.'_hint') }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </x-admin.card>

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach ($groups as $group => $features)
                <x-admin.card :title="__('admin.visibility.groups.'.$group)"
                              :subtitle="__('admin.visibility.group_intro.'.$group)"
                              x-data="{
                                  all(on) { $el.querySelectorAll('input[type=checkbox]').forEach(i => i.checked = on) },
                              }">
                    <div class="mb-4 flex gap-2">
                        <button type="button" @click="all(true)" class="text-xs font-medium text-primary-700 underline underline-offset-4 hover:text-primary-900">
                            {{ __('admin.actions.show_all') }}
                        </button>
                        <button type="button" @click="all(false)" class="text-xs font-medium text-primary-700 underline underline-offset-4 hover:text-primary-900">
                            {{ __('admin.actions.hide_all') }}
                        </button>
                    </div>

                    <div class="space-y-2">
                        @foreach ($features as $key => $feature)
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3 transition has-[:checked]:border-primary-400 has-[:checked]:bg-primary-50/40">
                                <input type="hidden" name="features[{{ $key }}]" value="0">
                                <input type="checkbox" name="features[{{ $key }}]" value="1"
                                       @checked(old('features.'.$key, $feature['enabled']))
                                       class="mt-0.5 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                                <span class="min-w-0">
                                    <span class="block text-sm font-medium text-slate-800">
                                        {{ __('admin.visibility.features.'.$key) }}
                                    </span>
                                    @foreach ($feature['requires'] as $parent)
                                        <span class="mt-0.5 block text-xs text-slate-500">
                                            {{ __('admin.visibility.requires', ['feature' => __('admin.visibility.features.'.$parent)]) }}
                                        </span>
                                    @endforeach
                                </span>
                            </label>
                        @endforeach
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.form-actions/>
    </form>
</x-layouts.admin>
