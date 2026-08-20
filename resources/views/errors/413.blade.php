{{--
    Standalone rather than extending a layout: this renders before the session
    starts, so anything depending on request state or flashed data is unsafe here.
--}}
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('site.errors.413_title') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @fonts
    @vite(['resources/css/app.css'])
</head>
<body class="grid min-h-screen place-items-center bg-slate-100 p-4">
    <div class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm">
        <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-amber-100 text-amber-700">
            <x-icon name="alert-triangle" class="h-7 w-7"/>
        </span>

        <h1 class="mt-5 text-xl font-bold text-slate-900">{{ __('site.errors.413_title') }}</h1>

        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $message }}</p>

        <ul class="mt-5 space-y-2 rounded-xl bg-slate-50 p-4 text-start text-sm text-slate-600">
            <li class="flex gap-2"><span aria-hidden="true">·</span>{{ __('site.errors.413_tip_resize') }}</li>
            <li class="flex gap-2"><span aria-hidden="true">·</span>{{ __('site.errors.413_tip_fewer') }}</li>
        </ul>

        <a href="{{ $back ?? url('/') }}" class="btn-primary mt-6">
            <x-icon name="arrow-left" class="h-4 w-4 rtl:rotate-180"/>{{ __('site.actions.back') }}
        </a>
    </div>
</body>
</html>
