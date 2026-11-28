@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'min-h-11 rounded-xl border border-border bg-card px-4 py-2.5 text-sm text-ink placeholder:text-muted focus:border-primary focus:ring-primary']) }}>
