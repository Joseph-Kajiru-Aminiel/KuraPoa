@props([
    'status' => __('Salama'),
    'statusVariant' => 'secure',
    'illustration' => 'admin',
    'title',
    'description',
])

<article @class([
    'auth-card relative rounded-[20px] border-2 border-forest bg-card-cream p-5 transition-shadow duration-200 sm:p-8',
    'animate-auth-card-nudge' => $errors->any(),
])>
    <div class="flex justify-end">
        <x-status-badge :variant="$statusVariant">{{ $status }}</x-status-badge>
    </div>

    <x-auth-illustration :variant="$illustration" class="mx-auto mt-2 h-20 w-24 {{ $illustration === 'verified' ? 'animate-receipt-check-pop' : '' }}" />

    <h1 class="mt-3 text-center text-2xl font-extrabold leading-tight text-ink sm:text-[1.7rem]">
        {{ $title }}
    </h1>
    <p class="mx-auto mt-2 max-w-md text-center text-sm leading-6 text-muted-ink">
        {{ $description }}
    </p>

    <div class="mt-6">
        {{ $slot }}
    </div>

    @if (isset($help))
        <div class="mt-4 text-center text-sm leading-6 text-muted-ink">
            {{ $help }}
        </div>
    @endif

    @if (isset($note))
        <div class="mt-5 border-t border-dashed border-divider pt-4 text-center text-xs leading-5 text-muted-ink">
            {{ $note }}
        </div>
    @endif
</article>
