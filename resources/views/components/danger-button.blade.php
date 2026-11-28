<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center justify-center rounded-xl border border-danger bg-card px-5 py-2.5 text-sm font-semibold text-danger transition-colors duration-150 hover:bg-page focus-visible:ring-danger']) }}>
    {{ $slot }}
</button>
