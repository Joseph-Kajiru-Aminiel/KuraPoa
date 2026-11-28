@props(['variant' => 'admin'])

@php
    $isVoter = $variant === 'voter';
@endphp

<div class="min-h-screen bg-cream">
    <main class="mx-auto min-h-screen max-w-[1600px] bg-cream lg:grid lg:grid-cols-[40%_60%]">
        <aside class="relative isolate overflow-hidden rounded-b-[2rem] bg-gradient-to-br from-forest via-panel to-forest px-5 pb-10 pt-5 text-white lg:flex lg:min-h-screen lg:rounded-none lg:flex-col lg:justify-between lg:px-11 lg:py-12 xl:px-16">
            <div aria-hidden="true" class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full border border-white/10"></div>
            <div aria-hidden="true" class="pointer-events-none absolute -bottom-36 -left-24 h-80 w-80 rounded-full border border-white/10"></div>

            <div class="relative z-10 mx-auto w-full max-w-lg">
                <x-logo light />

                <div class="mt-7 lg:mt-16">
                    <p class="text-2xl font-extrabold leading-tight sm:text-3xl lg:text-5xl">
                        {{ __('Upigaji kura wa wanafunzi, kwa uadilifu.') }}
                    </p>
                    <p class="mt-2 text-sm font-bold text-accent sm:text-base">
                        {{ __('Kura yako, sauti yako.') }}
                    </p>
                    <p class="mt-4 max-w-md text-sm leading-6 text-white/85 sm:text-base sm:leading-7">
                        {{ $isVoter ? __('Kila kura ina umuhimu. Chagua viongozi wako kwa usalama na uaminifu.') : __('Usimamizi wa uchaguzi, kwa uwazi.') }}
                    </p>
                </div>

                <div class="mt-10 hidden space-y-4 lg:block">
                    @foreach ([__('Kura yako inalindwa kwa usiri.'), __('Mchakato unaoeleweka kwa kila mwanafunzi.'), __('Uongozi bora huanza na ushiriki wako.')] as $message)
                        <div class="flex items-center gap-3 rounded-2xl border border-white/15 bg-white/10 p-4">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15" aria-hidden="true">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path d="m5 12 4 4L19 6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <span class="text-sm font-semibold text-white">{{ $message }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="relative z-10 mt-7 hidden text-xs text-white/70 lg:block">
                © {{ date('Y') }} KuraPoa. {{ __('Haki zote zimehifadhiwa.') }}
            </p>
        </aside>

        <section class="relative z-10 -mt-5 min-w-0 bg-cream px-4 pb-8 sm:px-7 lg:mt-0 lg:flex lg:min-h-screen lg:flex-col lg:px-10 lg:py-8 xl:px-16">
            <div class="mx-auto w-full max-w-lg">
                <header class="flex min-h-12 items-center justify-between gap-3">
                    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-2 rounded-lg px-2 text-sm font-semibold text-forest transition-colors hover:bg-white/60 focus-visible:ring-forest">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                            <path d="m15 18-6-6 6-6M9 12h11" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        {{ __('Rudi nyumbani') }}
                    </a>
                    <x-language-switcher variant="auth" />
                </header>

                <div class="-mt-3 lg:mt-6">
                    {{ $slot }}
                </div>

                <footer class="mt-8 text-center text-xs text-muted-ink">
                    © {{ date('Y') }} KuraPoa. {{ __('Haki zote zimehifadhiwa.') }}
                </footer>
            </div>
        </section>
    </main>
</div>
