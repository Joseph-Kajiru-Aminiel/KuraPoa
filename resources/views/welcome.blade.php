@extends('layouts.public')

@section('title', 'KuraPoa | '.__('Kura yako, sauti yako.'))

@section('content')
    <header x-data="{ scrolled: false }" @scroll.window="scrolled = window.scrollY > 8" class="fixed inset-x-0 top-0 z-50 border-b border-transparent transition-shadow duration-200" :class="scrolled ? 'border-border bg-card/95 shadow-md backdrop-blur' : 'bg-card/95'">
        <nav class="mx-auto flex min-h-16 max-w-7xl items-center justify-between gap-2 px-4 py-2 sm:px-6 lg:px-8" aria-label="{{ __('Menyu kuu') }}">
            <x-logo />
            <div class="flex items-center gap-2 sm:gap-4">
                <x-language-switcher />
                <x-button :href="route('voter.login')" class="min-h-11 px-3 sm:px-5">{{ __('Piga kura sasa') }}</x-button>
            </div>
        </nav>
    </header>

    <main>
        <section class="relative overflow-hidden bg-gradient-to-br from-primary via-primary-hover to-ink">
            <div aria-hidden="true" class="absolute -right-32 -top-32 h-96 w-96 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="absolute -bottom-48 -left-20 h-[30rem] w-[30rem] rounded-full border border-white/10"></div>
            <div class="relative mx-auto grid min-h-[620px] max-w-7xl items-center gap-12 px-4 pb-16 pt-28 sm:px-6 sm:pb-20 lg:grid-cols-[1.15fr_0.85fr] lg:px-8 lg:pt-32">
                <div class="max-w-3xl">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold text-white">
                        <span class="h-2 w-2 rounded-full bg-accent"></span>
                        {{ __('Sauti ya wanafunzi, uchaguzi wa haki') }}
                    </span>
                    <h1 class="mt-7 text-4xl font-extrabold leading-tight tracking-tight text-white sm:text-5xl lg:text-6xl">
                        {{ __('Chagua viongozi.') }}<br>
                        <span class="text-accent">{{ __('Badili kesho.') }}</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-white">
                        {{ __('Piga kura kwa usalama, faragha na urahisi kupitia simu yako. Ushiriki wako unaimarisha uongozi bora chuoni.') }}
                    </p>
                    <p class="mt-4 text-sm font-semibold tracking-wide text-white">{{ __('Kura yako, sauti yako.') }}</p>
                </div>

                <div class="hidden lg:block">
                    <div class="mx-auto max-w-md rounded-[2rem] border border-white/15 bg-white/10 p-6 shadow-2xl backdrop-blur">
                        <div class="rounded-2xl bg-card p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-muted">{{ __('Uchaguzi wa wanafunzi') }}</p>
                                    <p class="mt-1 text-lg font-bold text-ink">{{ __('Sauti yako ni muhimu') }}</p>
                                </div>
                                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-primary-light text-primary">
                                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                        <path d="M5 8h14v12H5zM8 8V5h8v3m-7 4h6m-6 4h4" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </div>
                            <div class="mt-6 space-y-3">
                                <div class="flex items-center gap-3 rounded-xl border border-primary bg-primary-light p-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-primary font-bold text-white">AM</span>
                                    <span class="flex-1">
                                        <span class="block text-sm font-bold text-ink">Asha Mussa</span>
                                        <span class="text-xs text-muted">{{ __('Mgombea wa Rais') }}</span>
                                    </span>
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-accent text-sm font-bold text-ink" aria-label="{{ __('Amechaguliwa') }}">✓</span>
                                </div>
                                <div class="flex items-center gap-3 rounded-xl border border-border p-4">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-page font-bold text-body">JM</span>
                                    <span class="flex-1">
                                        <span class="block text-sm font-bold text-ink">Juma Mwakalinga</span>
                                        <span class="text-xs text-muted">{{ __('Mgombea wa Rais') }}</span>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-6 h-2 rounded-full bg-page"><div class="h-2 w-2/3 rounded-full bg-primary"></div></div>
                            <p class="mt-2 text-right text-xs font-medium text-muted">Nafasi 2 kati ya 3</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="jinsi-inavyofanya-kazi" class="bg-primary-light py-16 sm:py-20" data-reveal>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-section-title :eyebrow="__('Hatua rahisi')" :title="__('Jinsi inavyofanya kazi')" :description="__('Piga kura kwa hatua chache, kutoka popote ulipo.')" />
                <div class="mt-9 grid gap-5 md:grid-cols-3">
                    @foreach ([
                        ['number' => '01', 'title' => __('Ingia'), 'description' => __('Tumia namba yako ya usajili na msimbo wa kupigia kura uliopokea.')],
                        ['number' => '02', 'title' => __('Chagua'), 'description' => __('Chagua mgombea mmoja kwa kila nafasi ya uongozi.')],
                        ['number' => '03', 'title' => __('Thibitisha'), 'description' => __('Kagua chaguo lako kisha thibitisha kutuma kura yako.')],
                    ] as $step)
                        <x-card>
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary text-sm font-extrabold text-white">{{ $step['number'] }}</span>
                            <h3 class="mt-5 text-lg font-bold text-ink">{{ $step['title'] }}</h3>
                            <p class="mt-2 leading-7 text-body">{{ $step['description'] }}</p>
                        </x-card>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-accent-soft py-16 sm:py-20" data-reveal>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-section-title :eyebrow="__('Uaminifu na uwazi')" :title="__('Kwa nini KuraPoa')" :description="__('Teknolojia rahisi inayosaidia uchaguzi wa haki na ushiriki wa kila mwanafunzi.')" />
                <div class="mt-9 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ([
                        ['title' => __('Kura ni siri'), 'description' => __('Chaguo lako halihusishwi na taarifa zako binafsi.'), 'icon' => 'M12 11V7m0 8h.01M5 11h14v9H5zM8 11V8a4 4 0 1 1 8 0v3'],
                        ['title' => __('Mtu mmoja, kura moja'), 'description' => __('Kila mpiga kura anathibitishwa na anaweza kupiga kura mara moja.'), 'icon' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m16-13a4 4 0 0 1 0 8m6 5v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8'],
                        ['title' => __('Matokeo ya haraka'), 'description' => __('Matokeo yanapatikana uchaguzi unapofungwa.'), 'icon' => 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['title' => __('Rahisi kwenye simu'), 'description' => __('Muundo unaofanya kazi vizuri kwenye simu, kompyuta kibao na kompyuta.'), 'icon' => 'M7 2h10a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2Zm4 17h2'],
                    ] as $trust)
                        <x-card class="h-full">
                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-light text-primary">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                                    <path d="{{ $trust['icon'] }}" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <h3 class="mt-5 font-bold text-ink">{{ $trust['title'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-body">{{ $trust['description'] }}</p>
                        </x-card>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="bg-ink py-12 text-white" data-reveal>
            <div class="mx-auto grid max-w-7xl grid-cols-1 gap-8 px-4 text-center sm:grid-cols-3 sm:px-6 lg:px-8">
                @foreach ([['value' => $stats['elections'], 'label' => __('Chaguzi zilizofungwa')], ['value' => $stats['votes'], 'label' => __('Jumla ya kura zilizopigwa')], ['value' => $stats['institutions'], 'label' => __('Taasisi')]] as $stat)
                    <div>
                        <p class="text-4xl font-extrabold text-accent">{{ number_format($stat['value']) }}</p>
                        <p class="mt-2 text-sm font-semibold text-white">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="bg-primary-light py-16 sm:py-20" data-reveal>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                    <x-section-title :eyebrow="__('Chuo chako')" :title="__('Chaguzi zinazoendelea')" :description="__('Angalia chaguzi zinazoruhusu kura kwa sasa.')" />
                </div>
                <div class="mt-7 grid gap-4 md:grid-cols-2">
                    @forelse ($ongoingElections as $election)
                        <x-card class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <div class="flex items-center gap-2"><x-badge variant="success">{{ __('Wazi') }}</x-badge></div>
                                <h3 class="mt-3 font-bold text-ink">{{ $election->title }}</h3>
                                <p class="mt-1 text-sm text-muted">{{ __('Mwisho: :date', ['date' => $election->ends_at->locale(app()->getLocale())->isoFormat('LLL')]) }}</p>
                            </div>
                            <x-button :href="route('voter.login')">{{ __('Piga kura') }}</x-button>
                        </x-card>
                    @empty
                        <div class="md:col-span-2"><x-empty-state :title="__('Hakuna uchaguzi unaoendelea')" :description="__('Chaguzi zitakazopokea kura zitaonekana hapa.')" /></div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="bg-card py-16 sm:py-20" data-reveal>
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <x-section-title :eyebrow="__('Uwazi wa matokeo')" :title="__('Chaguzi zilizofungwa')" :description="__('Angalia matokeo ya chaguzi zilizokamilika.')" />
                <div class="mt-7 grid gap-4 md:grid-cols-2">
                    @forelse ($closedElections as $election)
                        <x-card class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                            <div>
                                <x-badge variant="dark">{{ __('Imefungwa') }}</x-badge>
                                <h3 class="mt-3 font-bold text-ink">{{ $election->title }}</h3>
                                <p class="mt-1 text-sm text-muted">{{ __('Ilimalizika :date', ['date' => $election->ends_at->locale(app()->getLocale())->isoFormat('LL')]) }}</p>
                            </div>
                            <x-button variant="secondary" :href="route('results.show', $election)">{{ __('Ona matokeo') }}</x-button>
                        </x-card>
                    @empty
                        <div class="md:col-span-2"><x-empty-state :title="__('Bado hakuna matokeo')" :description="__('Matokeo yataonekana hapa baada ya uchaguzi kufungwa.')" /></div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>

    <section class="bg-accent-soft py-12" data-reveal>
        <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-5 px-4 text-center sm:flex-row sm:text-left sm:px-6 lg:px-8">
            <div>
                <h2 class="text-2xl font-extrabold text-ink">{{ __('Je, chuo chako kinahitaji KuraPoa?') }}</h2>
                <p class="mt-2 text-body">{{ __('Wasiliana nasi ili ujifunze zaidi kuhusu KuraPoa.') }}</p>
            </div>
            @if ($whatsappNumber = \App\Support\SiteSetting::get('whatsapp_number'))
                <x-button :href="'https://wa.me/'.$whatsappNumber" target="_blank" rel="noopener noreferrer">{{ __('Wasiliana nasi kupitia WhatsApp') }}</x-button>
            @endif
        </div>
    </section>

    <section id="washirika" class="bg-page py-12 sm:py-16" data-reveal>
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-section-title :eyebrow="__('Washirika Wetu')" :title="__('Washirika Wetu')" :description="__('Mashirika yanayotuunga mkono katika kukuza ushiriki wa wanafunzi.')" />
            <div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @forelse ($sponsors as $sponsor)
                    <article class="overflow-hidden rounded-2xl border border-border bg-card shadow-soft transition duration-200 hover:-translate-y-1 hover:shadow-soft-hover">
                        <div class="flex h-36 items-center justify-center bg-primary-light p-5">
                            @if ($sponsor->logo)
                                <img src="{{ asset('storage/'.$sponsor->logo) }}" alt="{{ __('Nembo ya :name', ['name' => $sponsor->name]) }}" loading="lazy" class="h-24 w-full object-contain">
                            @else
                                <span aria-hidden="true" class="flex h-20 w-20 items-center justify-center rounded-full bg-primary text-3xl font-extrabold text-white">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($sponsor->name, 0, 1)) }}</span>
                            @endif
                        </div>
                        <div class="p-5">
                            <h3 class="min-h-12 font-bold text-ink">{{ $sponsor->name }}</h3>
                            @if ($sponsor->url)
                                <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" class="mt-3 inline-flex min-h-11 items-center gap-2 font-semibold text-primary hover:underline">
                                    {{ __('Tembelea') }}
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M7 17 17 7M8 7h9v9" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </a>
                            @endif
                        </div>
                    </article>
                @empty
                    <div class="sm:col-span-2 xl:col-span-4">
                        <x-empty-state :title="__('Bado hakuna washirika')" :description="__('Washirika unaowaongeza wataonekana hapa.')" />
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <footer class="bg-ink py-12 text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:px-6 md:grid-cols-2 lg:grid-cols-4 lg:px-8">
            <div>
                <x-logo light />
                <p class="mt-3 max-w-xs text-sm leading-6 text-white/80">{{ __('Kura yako, sauti yako.') }}</p>
                @if (\App\Support\SiteSetting::get('contact_email'))
                    <a href="mailto:{{ \App\Support\SiteSetting::get('contact_email') }}" class="mt-3 inline-flex min-h-11 items-center text-sm text-white underline underline-offset-4">{{ \App\Support\SiteSetting::get('contact_email') }}</a>
                @endif
            </div>
            <div>
                <h2 class="font-bold text-white">{{ __('Viungo') }}</h2>
                <ul class="mt-3 space-y-1">
                    <li><a href="{{ route('home') }}" class="inline-flex min-h-11 items-center text-sm text-white/80 hover:text-white">{{ __('Nyumbani') }}</a></li>
                    <li><a href="{{ route('home') }}#jinsi-inavyofanya-kazi" class="inline-flex min-h-11 items-center text-sm text-white/80 hover:text-white">{{ __('Jinsi inavyofanya kazi') }}</a></li>
                    <li><a href="{{ route('home') }}#washirika" class="inline-flex min-h-11 items-center text-sm text-white/80 hover:text-white">{{ __('Washirika Wetu') }}</a></li>
                    <li><a href="{{ route('login') }}" class="inline-flex min-h-11 items-center text-sm text-white/80 hover:text-white">{{ __('Kuingia kwa Msimamizi') }}</a></li>
                </ul>
            </div>
            @if ($sponsors->contains(fn ($sponsor) => $sponsor->logo))
                <div>
                    <h2 class="font-bold text-white">{{ __('Washirika Wetu') }}</h2>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        @foreach ($sponsors->whereNotNull('logo') as $sponsor)
                            @if ($sponsor->url)
                                <a href="{{ $sponsor->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $sponsor->name }}" class="rounded-lg bg-white/10 p-2 grayscale transition duration-200 hover:grayscale-0">
                                    <img src="{{ asset('storage/'.$sponsor->logo) }}" alt="{{ __('Nembo ya :name', ['name' => $sponsor->name]) }}" loading="lazy" class="h-10 w-16 object-contain">
                                </a>
                            @else
                                <span class="rounded-lg bg-white/10 p-2 grayscale transition duration-200 hover:grayscale-0">
                                    <img src="{{ asset('storage/'.$sponsor->logo) }}" alt="{{ __('Nembo ya :name', ['name' => $sponsor->name]) }}" loading="lazy" class="h-10 w-16 object-contain">
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            <div>
                <h2 class="font-bold text-white">{{ __('Tufuate') }}</h2>
                <div class="mt-4 flex flex-wrap gap-3">
                    @if ($youtubeUrl = \App\Support\SiteSetting::get('youtube_url'))
                        <a href="{{ $youtubeUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Tufuate YouTube') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 text-white transition-colors duration-150 hover:border-accent hover:text-accent">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.3 3.6-6.3 3.6Z"/></svg>
                        </a>
                    @endif
                    @if ($instagramUrl = \App\Support\SiteSetting::get('instagram_url'))
                        <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Tufuate Instagram') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 text-white transition-colors duration-150 hover:border-accent hover:text-accent">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><circle cx="18" cy="6" r="1" fill="currentColor"/></svg>
                        </a>
                    @endif
                    @if ($whatsappNumber = \App\Support\SiteSetting::get('whatsapp_number'))
                        <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('Wasiliana nasi kupitia WhatsApp') }}" class="inline-flex h-11 w-11 items-center justify-center rounded-full border border-white/30 text-white transition-colors duration-150 hover:border-accent hover:text-accent">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.5 4.1 1.6 5.9L.2 24l6.5-1.7a11.8 11.8 0 0 0 5.4 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.1-1.2-6.1-3.5-8.3ZM12.2 21.6h-.1a9.8 9.8 0 0 1-5-1.4l-.4-.2-3.9 1 1-3.8-.3-.4a9.8 9.8 0 0 1-1.5-5.2c0-5.4 4.4-9.8 9.9-9.8a9.8 9.8 0 0 1 7 2.9 9.8 9.8 0 0 1 2.9 7c0 5.4-4.4 9.9-9.8 9.9Zm5.4-7.4c-.3-.1-1.7-.8-2-.9-.3-.1-.5-.1-.7.2-.2.3-.7.9-.9 1-.2.2-.3.2-.6.1-.3-.1-1.2-.4-2.3-1.4-.8-.7-1.4-1.7-1.6-2-.2-.3 0-.4.1-.6.1-.1.3-.3.4-.5.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5 0-.1-.7-1.5-.9-2.1-.2-.5-.5-.4-.7-.4h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.4s1 2.8 1.1 3c.1.2 2 3.1 4.9 4.3.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.4.2-.7.2-1.3.1-1.4-.1-.1-.2-.2-.5-.3Z"/></svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
        <div class="mx-auto mt-10 max-w-7xl border-t border-white/15 px-4 pt-5 text-sm text-white/70 sm:px-6 lg:px-8">
            © {{ date('Y') }} KuraPoa. {{ __('Haki zote zimehifadhiwa.') }}
        </div>
    </footer>
@endsection
