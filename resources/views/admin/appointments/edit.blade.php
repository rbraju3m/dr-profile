<x-layouts.admin :title="__('admin.actions.edit').' — '.$appointment->appointment_no">
    <x-admin.page-header :title="__('admin.actions.edit').' · '.$appointment->appointment_no"
                         :back="route('admin.appointments.show', $appointment)"/>

    <form method="POST" action="{{ route('admin.appointments.update', $appointment) }}" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <x-admin.card :title="__('admin.appointments.patient')">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.input name="patient_name" :label="__('site.booking.patient_name')" :value="$appointment->patient_name" required/>
                        <x-admin.input name="patient_phone" :label="__('site.booking.patient_phone')" :value="$appointment->patient_phone" required/>
                        <x-admin.input name="patient_email" type="email" :label="__('site.booking.patient_email')" :value="$appointment->patient_email"/>
                        <x-admin.input name="patient_age" type="number" :label="__('site.booking.patient_age')" :value="$appointment->patient_age"/>
                        <x-admin.select name="patient_gender" :label="__('site.booking.patient_gender')" :value="$appointment->patient_gender"
                                        :placeholder="__('admin.common.none')"
                                        :options="['male' => __('admin.gender.male'), 'female' => __('admin.gender.female'), 'other' => __('admin.gender.other')]"/>
                        <x-admin.input name="patient_address" :label="__('site.booking.patient_address')" :value="$appointment->patient_address"/>
                        <div class="sm:col-span-2">
                            <x-admin.textarea name="notes" :label="__('admin.appointments.notes')" :value="$appointment->notes" rows="3"/>
                        </div>
                        <div class="sm:col-span-2">
                            <x-admin.textarea name="admin_note" :label="__('admin.appointments.admin_note')" :value="$appointment->admin_note" rows="3"/>
                        </div>
                    </div>
                </x-admin.card>
            </div>

            <x-admin.card>
                <x-alert type="info">
                    The date, time and chamber cannot be edited here — cancel this appointment and book a new slot instead,
                    so the released time becomes available to other patients.
                </x-alert>
            </x-admin.card>
        </div>

        <div class="flex items-center gap-3 border-t border-slate-200 pt-6">
            <button type="submit" class="btn-primary btn-lg">
                <x-icon name="check" class="h-5 w-5"/>{{ __('admin.actions.save_changes') }}
            </button>
            <a href="{{ route('admin.appointments.show', $appointment) }}" class="btn-ghost">{{ __('admin.actions.cancel') }}</a>
        </div>
    </form>
</x-layouts.admin>
