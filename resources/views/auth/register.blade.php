<x-guest-layout>
    @section('title', 'Usajili umefungwa | KuraPoa')
    <x-section-title title="Usajili wa wasimamizi umefungwa" description="Akaunti za wasimamizi huundwa na msimamizi mkuu wa mfumo." />
    <div class="mt-6">
        <x-button variant="secondary" :href="route('login')">Rudi kuingia</x-button>
    </div>
</x-guest-layout>
