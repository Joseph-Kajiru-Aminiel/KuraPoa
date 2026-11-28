@props(['variant' => 'admin'])

<svg {{ $attributes->merge(['viewBox' => '0 0 96 80', 'fill' => 'none', 'aria-hidden' => 'true']) }}>
    @if ($variant === 'voter')
        <rect x="34" y="6" width="31" height="62" rx="8" fill="#FCFAF6" stroke="#0B3D2E" stroke-width="3"/>
        <rect x="39" y="13" width="21" height="43" rx="3" fill="#E5F0E7"/>
        <path d="M45 62h9" stroke="#0B3D2E" stroke-width="2.5" stroke-linecap="round"/>
        <circle cx="69" cy="50" r="15" fill="#F2B705" stroke="#0B3D2E" stroke-width="2"/>
        <path d="m62 50 5 5 9-10" stroke="#0B3D2E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M44 23h12M44 30h12M44 37h7" stroke="#0B4F3A" stroke-width="2" stroke-linecap="round"/>
    @elseif ($variant === 'recovery')
        <rect x="19" y="18" width="58" height="45" rx="8" fill="#FCFAF6" stroke="#0B3D2E" stroke-width="3"/>
        <path d="m23 24 25 19 25-19" stroke="#0B4F3A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
        <circle cx="70" cy="20" r="12" fill="#F2B705" stroke="#0B3D2E" stroke-width="2"/>
        <path d="M70 14v6l4 2" stroke="#0B3D2E" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    @elseif ($variant === 'verified')
        <circle cx="48" cy="39" r="29" fill="#E6F4EE" stroke="#0B3D2E" stroke-width="3"/>
        <path d="m34 39 9 9 19-20" stroke="#0B4F3A" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M28 70h40" stroke="#0B3D2E" stroke-width="3" stroke-linecap="round"/>
    @elseif ($variant === 'timeout')
        <circle cx="48" cy="39" r="29" fill="#FCFAF6" stroke="#0B3D2E" stroke-width="3"/>
        <path d="M48 22v18l12 7" stroke="#0B4F3A" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M38 5h20" stroke="#0B3D2E" stroke-width="3" stroke-linecap="round"/>
    @elseif ($variant === 'pending')
        <circle cx="48" cy="39" r="29" fill="#FCFAF6" stroke="#0B3D2E" stroke-width="3"/>
        <path d="M48 22v20" stroke="#0B4F3A" stroke-width="4" stroke-linecap="round"/>
        <circle cx="48" cy="51" r="2.5" fill="#F2B705"/>
        <path d="M28 70h40" stroke="#0B3D2E" stroke-width="3" stroke-linecap="round"/>
    @else
        <rect x="10" y="19" width="51" height="39" rx="6" fill="#FCFAF6" stroke="#0B3D2E" stroke-width="3"/>
        <path d="M17 49h37M21 43V31h9v12m5 0V26h9v17m5 0V35h5v8" stroke="#0B4F3A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M5 64h62" stroke="#0B3D2E" stroke-width="3" stroke-linecap="round"/>
        <circle cx="71" cy="50" r="15" fill="#F2B705" stroke="#0B3D2E" stroke-width="2"/>
        <path d="m64 50 5 5 9-10" stroke="#0B3D2E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
    @endif
</svg>
