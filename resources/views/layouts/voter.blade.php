<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <title>@yield('title', 'Kupiga kura') | KuraPoa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-page">
        <header class="border-b border-border bg-card print:hidden">
            <div class="mx-auto flex min-h-16 max-w-3xl items-center justify-between px-4 sm:px-6">
                <x-logo />
                <div class="flex items-center gap-2">
                    <x-language-switcher />
                    @if (session()->has('voter_id'))
                    <form method="POST" action="{{ route('voter.logout') }}">
                        @csrf
                        <button type="submit" class="min-h-11 rounded-xl px-3 text-sm font-semibold text-primary hover:bg-primary-light focus-visible:ring-primary">
                            {{ __('Toka') }}
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </header>
        <main class="mx-auto max-w-3xl px-4 py-6 sm:px-6 sm:py-10">
            @if ($errors->any())
                <x-alert type="error" class="mb-6">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </x-alert>
            @endif
            @yield('content')
        </main>
    </body>
</html>
