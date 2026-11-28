<x-app-layout>
    <x-slot name="header">
        <x-section-title title="Akaunti yangu" description="Simamia taarifa zako na usalama wa akaunti." />
    </x-slot>

    <div class="space-y-6">
        <x-card><div class="max-w-2xl">@include('profile.partials.update-profile-information-form')</div></x-card>
        <x-card><div class="max-w-2xl">@include('profile.partials.update-password-form')</div></x-card>
        <x-card><div class="max-w-2xl">@include('profile.partials.delete-user-form')</div></x-card>
    </div>
</x-app-layout>
