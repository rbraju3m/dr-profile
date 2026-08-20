<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('admin.auth.sign_in') }} — {{ __('admin.panel') }}</title>
    <meta name="robots" content="noindex, nofollow">
    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="grid min-h-screen place-items-center bg-primary-950 p-4">
    <div class="w-full max-w-sm">
        <div class="mb-8 text-center">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-primary-600 text-white">
                <x-icon name="heart-pulse" class="h-7 w-7"/>
            </span>
            <h1 class="mt-4 text-xl font-bold text-white">{{ __('admin.panel') }}</h1>
            <p class="mt-1 text-sm text-primary-200">{{ __('admin.auth.welcome') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4 rounded-2xl bg-white p-6 shadow-xl">
            @csrf

            @if ($errors->any())
                <x-alert type="error">{{ $errors->first() }}</x-alert>
            @endif

            <x-admin.input name="email" type="email" :label="__('admin.auth.email')" required
                           autocomplete="username" autofocus/>

            <x-admin.input name="password" type="password" :label="__('admin.auth.password')" required
                           autocomplete="current-password"/>

            <label class="flex items-center gap-2.5 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1"
                       class="h-4 w-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500">
                {{ __('admin.auth.remember') }}
            </label>

            <button type="submit" class="btn-primary btn-lg w-full">{{ __('admin.auth.sign_in') }}</button>
        </form>

        <p class="mt-6 text-center text-xs text-primary-300">
            <a href="{{ route('home') }}" class="hover:text-white">← {{ __('admin.nav.view_site') }}</a>
        </p>
    </div>
</body>
</html>
