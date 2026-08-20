<x-admin.form-shell :record="$record" :route-name="$routeName" :label="$label" files>
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.card :title="__('admin.nav.chambers')">
                <div class="space-y-4">
                    <x-admin.bilingual name="name" :label="__('admin.nav.chambers')" :record="$record" required/>
                    <x-admin.bilingual name="address" :label="__('site.chamber.address')" :record="$record" type="textarea" rows="3"/>
                    <x-admin.bilingual name="city" label="City" :record="$record"/>
                    <x-admin.bilingual name="note" :label="__('site.booking.notes')" :record="$record" type="textarea" rows="3"/>
                </div>
            </x-admin.card>

            <x-admin.card title="Map">
                <div class="space-y-4">
                    <x-admin.input name="map_url" label="Google Maps link" :value="$record?->map_url" placeholder="https://maps.app.goo.gl/…"/>
                    <x-admin.textarea name="map_embed" label="Map embed HTML" :value="$record?->map_embed" rows="4" rich
                                      hint="Paste the &lt;iframe&gt; snippet from Google Maps → Share → Embed a map."/>
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card>
                <div class="space-y-4">
                    <x-admin.image-upload name="image" :label="__('admin.common.image')" :current="$record?->imageUrl()"/>
                    <x-admin.input name="room_no" :label="__('site.chamber.room')" :value="$record?->room_no"/>
                    <x-admin.input name="phone" :label="__('site.chamber.phone')" :value="$record?->phone"/>
                    <x-admin.input name="appointment_phone" :label="__('site.chamber.appointment_phone')" :value="$record?->appointment_phone"/>
                    <x-admin.input name="consultation_fee" type="number" step="0.01" :label="__('site.chamber.consultation_fee')" :value="$record?->consultation_fee"/>
                    <x-admin.input name="followup_fee" type="number" step="0.01" :label="__('site.chamber.followup_fee')" :value="$record?->followup_fee"/>
                    <x-admin.input name="sort_order" type="number" :label="__('admin.common.order')" :value="$record?->sort_order ?? 0"/>
                    <x-admin.input name="slug" label="Slug" :value="$record?->slug" :hint="__('admin.common.slug_hint')"/>
                </div>
            </x-admin.card>

            <x-admin.card>
                <div class="space-y-3">
                    <x-admin.toggle name="accepts_online_booking" label="Accept online booking"
                                    :value="$record?->accepts_online_booking ?? true"
                                    hint="Turn off for chambers where serials are issued at the counter."/>
                    <x-admin.toggle name="is_active" :label="__('admin.common.active')" :value="$record?->is_active ?? true"/>
                </div>
            </x-admin.card>

            @if ($record)
                <a href="{{ route('admin.chambers.schedules.index', $record) }}" class="btn-secondary w-full">
                    <x-icon name="clock" class="h-4 w-4"/>{{ __('admin.schedules.title') }}
                </a>
            @endif
        </div>
    </div>
</x-admin.form-shell>
