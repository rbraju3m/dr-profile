<x-layouts.public :title="__('site.contact.heading')">
    <x-page-hero :title="__('site.contact.heading')" :subtitle="__('site.contact.subheading')"
                 :breadcrumbs="[__('site.nav.contact') => null]"/>

    <section class="section bg-slate-50">
        <div class="container-page grid gap-10 lg:grid-cols-12">
            <div class="lg:col-span-7">
                <div class="card p-6 sm:p-8">
                    <h2 class="text-xl font-bold tracking-tight">{{ __('site.contact.form_heading') }}</h2>

                    @if (session('success'))
                        <x-alert type="success" class="mt-5">{{ session('success') }}</x-alert>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
                        @csrf

                        {{-- Honeypot --}}
                        <div class="hidden" aria-hidden="true">
                            <label for="website">Website</label>
                            <input id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <label for="name" class="field-label">{{ __('site.contact.name') }} <span class="text-rose-500">*</span></label>
                            <input id="name" name="name" value="{{ old('name') }}" required autocomplete="name"
                                   class="field-input @error('name') ring-rose-400 @enderror">
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="phone" class="field-label">{{ __('site.contact.phone') }} <span class="text-rose-500">*</span></label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel"
                                   placeholder="01712345678" autocomplete="tel"
                                   class="field-input tabular-nums @error('phone') ring-rose-400 @enderror">
                            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="field-label">{{ __('site.contact.email') }}</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                                   class="field-input @error('email') ring-rose-400 @enderror">
                            @error('email') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="subject" class="field-label">{{ __('site.contact.subject') }}</label>
                            <input id="subject" name="subject" value="{{ old('subject') }}" class="field-input">
                            @error('subject') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="message" class="field-label">{{ __('site.contact.message') }} <span class="text-rose-500">*</span></label>
                            <textarea id="message" name="message" rows="5" required
                                      class="field-input @error('message') ring-rose-400 @enderror">{{ old('message') }}</textarea>
                            @error('message') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <x-alert type="warning">{{ __('site.contact.emergency_note') }}</x-alert>
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="btn-primary btn-lg w-full sm:w-auto">
                                <x-icon name="mail" class="h-5 w-5"/>{{ __('site.actions.send_message') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <aside class="lg:col-span-5 space-y-5">
                <div class="card p-6">
                    <h2 class="text-base font-semibold">{{ __('site.footer.contact') }}</h2>

                    <ul class="mt-4 space-y-3.5 text-sm">
                        @if ($doctor->hotline)
                            <li class="flex gap-3">
                                <x-icon name="phone" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                <div>
                                    <p class="text-xs text-slate-500">{{ __('site.contact.hotline') }}</p>
                                    <a href="tel:{{ $doctor->hotline }}" class="font-medium tabular-nums text-slate-800 hover:text-primary-700">{{ bn_digits($doctor->hotline) }}</a>
                                </div>
                            </li>
                        @endif
                        @if ($doctor->email)
                            <li class="flex gap-3">
                                <x-icon name="mail" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                <div class="min-w-0">
                                    <p class="text-xs text-slate-500">{{ __('site.contact.email') }}</p>
                                    <a href="mailto:{{ $doctor->email }}" class="break-all font-medium text-slate-800 hover:text-primary-700">{{ $doctor->email }}</a>
                                </div>
                            </li>
                        @endif
                        @if ($doctor->whatsapp)
                            <li class="flex gap-3">
                                <x-icon name="whatsapp" class="mt-0.5 h-4 w-4 shrink-0 text-primary-500"/>
                                <div>
                                    <p class="text-xs text-slate-500">WhatsApp</p>
                                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $doctor->whatsapp) }}" target="_blank" rel="noopener noreferrer"
                                       class="font-medium tabular-nums text-slate-800 hover:text-primary-700">{{ bn_digits($doctor->whatsapp) }}</a>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>

                @foreach ($chambers as $chamber)
                    <div class="card p-6">
                        <h3 class="flex items-center gap-2 text-base font-semibold">
                            <x-icon name="building" class="h-4 w-4 text-primary-500"/>
                            {{ $chamber->tr('name') }}
                        </h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-500">{{ $chamber->tr('address') }}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @if ($chamber->phone)
                                <a href="tel:{{ $chamber->phone }}" class="btn-secondary px-3 py-1.5 text-xs">
                                    <x-icon name="phone" class="h-3.5 w-3.5"/>{{ bn_digits($chamber->phone) }}
                                </a>
                            @endif
                            @feature('chambers')
                                <a href="{{ route('chambers.show', $chamber) }}" class="btn-ghost px-3 py-1.5 text-xs">
                                    {{ __('site.actions.view_details') }}
                                </a>
                            @endfeature
                        </div>
                    </div>
                @endforeach
            </aside>
        </div>
    </section>
</x-layouts.public>
