<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <title>@yield('title', 'Dashibodi') | KuraPoa</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div x-data="{ menuOpen: false }" class="min-h-screen bg-page lg:flex">
            <div x-show="menuOpen" x-cloak class="fixed inset-0 z-40 bg-ink/50 lg:hidden" @click="menuOpen = false" aria-hidden="true"></div>

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-ink bg-ink px-5 py-6 transition-transform duration-200 lg:static lg:z-auto lg:translate-x-0"
                :class="menuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                aria-label="Menyu kuu"
            >
                <x-logo :href="route('admin.dashboard', request()->only('institution_id'))" light />
                <nav class="mt-8 flex-1 space-y-6 overflow-y-auto" aria-label="{{ __('Menyu kuu') }}">
                    @foreach ([
                        __('Dashibodi') => [['label' => __('Dashibodi'), 'route' => 'admin.dashboard']],
                        __('Uchaguzi') => [['label' => __('Chaguzi'), 'route' => 'admin.elections.index'], ['label' => __('Matokeo'), 'route' => 'admin.results.index']],
                        __('Watu') => [['label' => __('Wapiga kura'), 'route' => 'admin.voters.index'], ['label' => __('Wagombea'), 'route' => 'admin.candidates.index']],
                        __('Taasisi') => [['label' => __('Taarifa za taasisi'), 'route' => 'admin.institution.edit'], ['label' => __('Muundo wa taasisi'), 'route' => 'admin.institution.structure']],
                    ] as $group => $items)
                        <div>
                            <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-white/60">{{ $group }}</p>
                            @foreach ($items as $item)
                                @php($active = request()->routeIs($item['route']) || ($item['route'] === 'admin.institution.structure' && request()->routeIs('admin.institution.structure.*')))
                                <a href="{{ route($item['route'], request()->only('institution_id')) }}" @click="menuOpen = false"
                                    @class([
                                        'flex min-h-11 items-center rounded-xl px-4 text-sm font-semibold transition-colors duration-150',
                                        'bg-accent text-ink' => $active,
                                        'text-white/85 hover:bg-white/10 hover:text-white' => ! $active,
                                    ])>{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                    @endforeach
                    @if (auth()->user()->is_super_admin)
                        <div>
                            <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-white/60">{{ __('Mfumo') }}</p>
                            <a href="{{ route('admin.sponsors.index', request()->only('institution_id')) }}" @click="menuOpen = false"
                                @class(['flex min-h-11 items-center rounded-xl px-4 text-sm font-semibold transition-colors duration-150', 'bg-accent text-ink' => request()->routeIs('admin.sponsors.*'), 'text-white/85 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.sponsors.*')])>{{ __('Washirika Wetu') }}</a>
                            @if (auth()->user()->is_super_admin)
                                <a href="{{ route('admin.settings.edit', request()->only('institution_id')) }}" @click="menuOpen = false"
                                    @class(['mt-1 flex min-h-11 items-center rounded-xl px-4 text-sm font-semibold transition-colors duration-150', 'bg-accent text-ink' => request()->routeIs('admin.settings.*'), 'text-white/85 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.settings.*')])>{{ __('Mipangilio') }}</a>
                            @endif
                            <a href="{{ route('admin.audit.index', request()->only('institution_id')) }}" @click="menuOpen = false"
                                @class(['mt-1 flex min-h-11 items-center rounded-xl px-4 text-sm font-semibold transition-colors duration-150', 'bg-accent text-ink' => request()->routeIs('admin.audit.*'), 'text-white/85 hover:bg-white/10 hover:text-white' => ! request()->routeIs('admin.audit.*')])>{{ __('Kumbukumbu za ukaguzi') }}</a>
                        </div>
                    @endif
                </nav>
                <div class="mt-4 rounded-2xl bg-white/10 p-4">
                    <p class="truncate text-sm font-semibold text-white">{{ auth()->user()->name }}</p>
                    <p class="mt-1 truncate text-xs text-white/70">{{ auth()->user()->email }}</p>
                    <form method="POST" action="{{ route('logout') }}" class="mt-3">
                        @csrf
                        <button type="submit" class="min-h-11 w-full rounded-xl border border-white/50 px-3 text-left text-sm font-semibold text-white transition-colors duration-150 hover:bg-white/10 focus-visible:ring-primary">
                            {{ __('Toka kwenye akaunti') }}
                        </button>
                    </form>
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <header class="sticky top-0 z-30 flex min-h-16 items-center justify-between border-b border-border bg-card/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <div class="flex items-center gap-3">
                        <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl text-ink hover:bg-page focus-visible:ring-primary lg:hidden" @click="menuOpen = !menuOpen" :aria-expanded="menuOpen.toString()" aria-label="Fungua menyu">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                        <div>
                            <p class="text-xs font-medium text-muted">{{ __('Kura yako, sauti yako.') }}</p>
                            <h1 class="text-base font-bold text-ink sm:text-lg">@yield('page-heading', __('Dashibodi'))</h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        @if (auth()->user()->is_super_admin && $adminInstitutions->isNotEmpty())
                            <form method="GET" action="{{ url()->current() }}" class="hidden sm:block">
                                @foreach (request()->except('institution_id') as $key => $value)
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endforeach
                                <label class="sr-only" for="admin-institution">{{ __('Chagua taasisi') }}</label>
                                <select id="admin-institution" name="institution_id" onchange="this.form.submit()" class="min-h-11 rounded-xl border-border text-sm">
                                    <option value="">{{ __('Taasisi zote') }}</option>
                                    @foreach ($adminInstitutions as $institution)
                                        <option value="{{ $institution->id }}" @selected(request()->integer('institution_id') === $institution->id)>{{ $institution->name }}</option>
                                    @endforeach
                                </select>
                            </form>
                        @endif
                        <x-language-switcher />
                        <span class="hidden max-w-48 truncate text-sm font-semibold text-body sm:block">{{ auth()->user()->name }}</span>
                    </div>
                </header>

                <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
                    @if (session('success'))
                        <x-alert type="success" class="mb-6">{{ session('success') }}</x-alert>
                    @endif
                    @if ($errors->any())
                        <x-alert type="error" class="mb-6">
                            <p class="font-semibold">Tafadhali rekebisha yafuatayo:</p>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    @endif
                    @yield('content')
                </main>
            </div>
        </div>
    </body>
</html>
