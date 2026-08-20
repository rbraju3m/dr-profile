<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label">
    <p class="mb-6 text-sm text-slate-500">{{ __('admin.exceptions.intro') }}</p>

    <div x-data="{ available: {{ old('is_available', $record?->is_available ?? false) ? 'true' : 'false' }} }"
         class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.select name="chamber_id" :label="__('admin.nav.chambers')" :options="$chambers"
                                    :value="$record?->chamber_id" :placeholder="__('admin.exceptions.all_chambers')"
                                    hint="Leave blank when the doctor is away from every chamber that day."/>

                    <x-admin.input name="date" type="date" :label="__('site.booking.step_date')" required
                                   :value="$record?->date?->toDateString()"/>

                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 p-3.5 transition has-[:checked]:border-primary-400 has-[:checked]:bg-primary-50/50">
                        <input type="hidden" name="is_available" value="0">
                        <input type="checkbox" name="is_available" value="1" x-model="available"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                        <span>
                            <span class="block text-sm font-medium text-slate-800">{{ __('admin.exceptions.extra') }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">
                                Leave unticked to close the chamber for this date.
                            </span>
                        </span>
                    </label>

                    <div x-show="available" x-cloak class="grid gap-4 sm:grid-cols-3">
                        <x-admin.input name="start_time" type="time" :label="__('admin.schedules.start')"
                                       :value="$record?->start_time ? substr($record->start_time, 0, 5) : null"/>
                        <x-admin.input name="end_time" type="time" :label="__('admin.schedules.end')"
                                       :value="$record?->end_time ? substr($record->end_time, 0, 5) : null"/>
                        <x-admin.input name="slot_minutes" type="number" :label="__('admin.schedules.slot_minutes')"
                                       :value="$record?->slot_minutes ?? 20"/>
                    </div>

                    <x-admin.bilingual name="reason" :label="__('admin.exceptions.reason')" :record="$record"/>
                </div>
            </x-admin.card>
        </div>

        <x-admin.card>
            <x-alert type="info">
                A chamber-specific entry overrides the all-chambers entry for the same date.
            </x-alert>
        </x-admin.card>
    </div>
</x-admin.form-shell>
