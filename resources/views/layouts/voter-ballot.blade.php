<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <title>@yield('title', __('Kura yako').' | KuraPoa')</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-cream">
        <header class="sticky top-0 z-30 border-b border-divider bg-card-cream/95 backdrop-blur print:hidden">
            <div class="mx-auto flex min-h-[72px] max-w-5xl items-center justify-between gap-3 px-4 sm:px-6">
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-divider bg-white">
                        @yield('institution-logo')
                    </div>
                    <x-logo compact class="hidden shrink-0 sm:inline-flex" />
                    <div class="min-w-0">
                        <p class="truncate text-sm font-extrabold text-forest">
                            {{ trim($__env->yieldContent('election-title', __('Kura yako'))) }}
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <p class="text-[11px] leading-4 text-muted-ink">{{ __('Kikao kitaisha baada ya dakika 15 za kutotumia.') }}</p>
                            @yield('test-badge')
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-1 sm:gap-2">
                    <x-language-switcher variant="auth" />
                    @if (session()->has('voter_id'))
                        <form method="POST" action="{{ route('voter.logout') }}" data-confirm="{{ __('Una uhakika unataka kutoka bila kupiga kura?') }}" onsubmit="return confirm(this.dataset.confirm)">
                            @csrf
                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl px-2.5 text-xs font-bold text-forest transition-colors hover:bg-cream focus-visible:ring-forest sm:px-3 sm:text-sm">
                                {{ __('Toka bila kupiga kura') }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-10">
            @if ($errors->any())
                <x-alert type="error" class="mb-6">
                    @foreach ($errors->all() as $error)
                        <p>{{ __($error) }}</p>
                    @endforeach
                </x-alert>
            @endif
            @yield('content')
        </main>
    </body>
</html>
