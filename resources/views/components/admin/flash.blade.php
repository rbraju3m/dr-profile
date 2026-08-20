@if (session('success') || session('error') || $errors->any())
    <div class="mb-6 space-y-3">
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition.opacity>
                <x-alert type="success" class="relative pe-10">
                    {{ session('success') }}
                    <button type="button" @click="show = false"
                            class="absolute end-3 top-3 opacity-50 transition hover:opacity-100"
                            aria-label="{{ __('admin.actions.cancel') }}">
                        <x-icon name="x" class="h-4 w-4"/>
                    </button>
                </x-alert>
            </div>
        @endif

        @if (session('error'))
            <x-alert type="error">{{ session('error') }}</x-alert>
        @endif

        @if ($errors->any())
            <x-alert type="error">
                <ul class="list-disc space-y-0.5 ps-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-alert>
        @endif
    </div>
@endif
