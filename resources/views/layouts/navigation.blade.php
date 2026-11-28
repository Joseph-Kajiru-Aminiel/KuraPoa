<nav x-data="{ open: false }" class="border-b border-border bg-card">
    <div class="mx-auto flex min-h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-8">
            <x-logo :href="route('admin.dashboard')" />
            <div class="hidden items-center gap-2 sm:flex">
                <a href="{{ route('admin.dashboard') }}" class="min-h-11 inline-flex items-center rounded-xl px-3 text-sm font-semibold text-body hover:bg-page">Dashibodi</a>
                <a href="{{ route('admin.elections.index') }}" class="min-h-11 inline-flex items-center rounded-xl px-3 text-sm font-semibold text-body hover:bg-page">Chaguzi</a>
                <a href="{{ route('admin.results.index') }}" class="min-h-11 inline-flex items-center rounded-xl px-3 text-sm font-semibold text-body hover:bg-page">Matokeo</a>
            </div>
        </div>
        <div class="hidden items-center gap-4 sm:flex">
            <span class="max-w-48 truncate text-sm font-semibold text-body">{{ Auth::user()->name }}</span>
            <a href="{{ route('profile.edit') }}" class="min-h-11 inline-flex items-center text-sm font-semibold text-primary hover:underline">Akaunti</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="min-h-11 rounded-xl border border-primary px-4 text-sm font-semibold text-primary hover:bg-primary-light">Toka</button>
            </form>
        </div>
        <button type="button" class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-xl text-ink hover:bg-page sm:hidden" @click="open = !open" :aria-expanded="open.toString()" aria-label="Fungua menyu">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </button>
    </div>
    <div x-show="open" x-cloak class="border-t border-border px-4 py-3 sm:hidden">
        <div class="space-y-1">
            <a href="{{ route('admin.dashboard') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm font-semibold text-body">Dashibodi</a>
            <a href="{{ route('admin.elections.index') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm font-semibold text-body">Chaguzi</a>
            <a href="{{ route('admin.results.index') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm font-semibold text-body">Matokeo</a>
            <a href="{{ route('profile.edit') }}" class="block min-h-11 rounded-xl px-3 py-3 text-sm font-semibold text-body">Akaunti</a>
        </div>
        <div class="mt-3 border-t border-border pt-3">
            <p class="px-3 text-sm font-semibold text-ink">{{ Auth::user()->name }}</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-2">
                @csrf
                <button type="submit" class="min-h-11 w-full rounded-xl px-3 text-left text-sm font-semibold text-primary">Toka kwenye akaunti</button>
            </form>
        </div>
    </div>
</nav>
