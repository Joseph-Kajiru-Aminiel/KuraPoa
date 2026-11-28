<section class="space-y-4">
    <header>
        <h2 class="text-lg font-bold text-ink">Futa akaunti</h2>
        <p class="mt-1 text-sm leading-6 text-muted">Akaunti ikifutwa, taarifa zake zitaondolewa kabisa. Thibitisha nenosiri lako kabla ya kuendelea.</p>
    </header>

    <form method="POST" action="{{ route('profile.destroy') }}" class="space-y-4" onsubmit="return confirm('Una uhakika unataka kufuta akaunti yako?')">
        @csrf
        @method('DELETE')
        <x-input name="password" label="Thibitisha nenosiri" type="password" required autocomplete="current-password" />
        <div class="flex justify-end">
            <x-button type="submit" variant="danger">Futa akaunti</x-button>
        </div>
    </form>
</section>
