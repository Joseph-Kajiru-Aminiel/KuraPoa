@extends('layouts.admin')

@section('title', $election->title)
@section('page-heading', $election->title)

@section('content')
    @php
        $status = $election->isClosed() ? 'closed' : $election->status;
        $variant = ['draft' => 'neutral', 'scheduled' => 'warning', 'open' => 'success', 'closed' => 'dark'][$status];
        $label = ['draft' => __('Rasimu'), 'scheduled' => __('Imepangwa'), 'open' => __('Wazi'), 'closed' => __('Imefungwa')][$status];
    @endphp

    @if (session('import_summary'))
        <x-alert type="success" class="mb-6">
            Uingizaji umekamilika: wapiga kura {{ session('import_summary.imported') }},
            marudio {{ session('import_summary.duplicates') }},
            safu zisizo sahihi {{ session('import_summary.invalid') }}.
            @if (count(session('import_errors', [])))
                <a class="ml-2 font-semibold underline" href="{{ route('admin.voters.import-errors') }}">{{ __('Pakua ripoti ya makosa ya safu') }}</a>
            @endif
        </x-alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.7fr)_minmax(280px,0.8fr)]">
        <x-card>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-sm font-semibold text-muted">Taarifa za uchaguzi</p>
                    <h2 class="mt-2 text-xl font-bold text-ink">{{ $election->title }}</h2>
                </div>
                <x-badge :variant="$variant">{{ $label }}</x-badge>
            </div>
            <dl class="mt-6 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Unaanza</dt>
                    <dd class="mt-1 font-semibold text-body">{{ $election->starts_at->format('d/m/Y H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-muted">Unaisha</dt>
                    <dd class="mt-1 font-semibold text-body">{{ $election->ends_at->format('d/m/Y H:i') }}</dd>
                </div>
            </dl>
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($election->status === 'draft')
                    <x-button variant="secondary" :href="route('admin.elections.edit', $election)">Hariri taarifa</x-button>
                    <form method="POST" action="{{ route('admin.elections.destroy', $election) }}" onsubmit="return confirm('Una uhakika unataka kufuta uchaguzi huu?')">
                        @csrf
                        @method('DELETE')
                        <x-button type="submit" variant="danger">Futa uchaguzi</x-button>
                    </form>
                @endif
                @if ($election->status === 'draft')
                    <form method="POST" action="{{ route('admin.elections.open', $election) }}" onsubmit="return confirm('Fungua uchaguzi sasa? Baada ya kufunguliwa nafasi na wagombea hawataweza kuhaririwa.')">
                        @csrf
                        <x-button type="submit">Fungua uchaguzi</x-button>
                    </form>
                @elseif (in_array($election->status, ['scheduled', 'open'], true))
                    <form method="POST" action="{{ route('admin.elections.close', $election) }}" onsubmit="return confirm('Funga uchaguzi? Hatua hii itafanya matokeo yaonekane kwa umma.')">
                        @csrf
                        <x-button type="submit" variant="secondary">Funga uchaguzi</x-button>
                    </form>
                @endif
            </div>
        </x-card>

        <x-card>
            <p class="text-sm font-semibold text-muted">Mahudhurio ya moja kwa moja</p>
            <p class="mt-3 text-3xl font-extrabold tracking-tight text-ink">{{ number_format($voted) }}</p>
            <p class="mt-1 text-sm text-muted">kati ya {{ number_format($registered) }} waliosajiliwa</p>
            <div class="mt-4 h-2 overflow-hidden rounded-full bg-page" role="progressbar" aria-label="Asilimia ya wapiga kura waliohudhuria" aria-valuenow="{{ $registered ? round($voted / $registered * 100) : 0 }}" aria-valuemin="0" aria-valuemax="100">
                <div class="h-full rounded-full bg-primary transition-all duration-200" style="width: {{ $registered ? min(100, round($voted / $registered * 100)) : 0 }}%"></div>
            </div>
        </x-card>
    </div>

    <section class="mt-8">
        <x-section-title title="Nafasi na wagombea" description="Ongeza na hariri nafasi pamoja na taarifa za wagombea." />
        @if ($election->status === 'draft')
            <x-card class="mt-4">
                <h3 class="font-semibold text-ink">Ongeza nafasi</h3>
                <form method="POST" action="{{ route('admin.positions.store', $election) }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1"><x-input name="title" label="Jina la nafasi" placeholder="Mfano: Rais" required /></div>
                    <x-button type="submit">Ongeza nafasi</x-button>
                </form>
            </x-card>
        @endif

        <div class="mt-4 space-y-5">
            @forelse ($election->positions as $position)
                <x-card>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <form method="POST" action="{{ route('admin.positions.update', [$election, $position]) }}" class="flex flex-1 flex-col gap-3 sm:flex-row sm:items-end">
                            @csrf
                            @method('PUT')
                            <div class="flex-1"><x-input name="title" label="Nafasi" :value="$position->title" required :disabled="$election->status !== 'draft'" /></div>
                            @if ($election->status === 'draft')
                                <x-button type="submit" variant="secondary">Hifadhi nafasi</x-button>
                            @endif
                        </form>
                        @if ($election->status === 'draft')
                            <form method="POST" action="{{ route('admin.positions.destroy', [$election, $position]) }}" onsubmit="return confirm('Futa nafasi hii na wagombea wake?')">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger">Futa</x-button>
                            </form>
                        @endif
                    </div>

                    <div class="mt-5 grid gap-4 lg:grid-cols-2">
                        @forelse ($position->candidates as $candidate)
                            <div class="rounded-2xl border border-border bg-page p-4">
                                <form method="POST" action="{{ route('admin.candidates.update', [$election, $position, $candidate]) }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <div class="flex items-center gap-3">
                                        <x-candidate-avatar :candidate="$candidate" size="md" />
                                        <div class="min-w-0 flex-1"><x-input name="name" :id="'candidate-name-'.$candidate->id" label="Jina la mgombea" :value="$candidate->name" required :disabled="$election->status !== 'draft'" /></div>
                                    </div>
                                    <x-input name="reg_number" :id="'candidate-reg-'.$candidate->id" label="Namba ya usajili" :value="$candidate->reg_number" required :disabled="$election->status !== 'draft'" />
                                    <div>
                                        <label for="manifesto-{{ $candidate->id }}" class="mb-2 block text-sm font-semibold text-ink">Ilani fupi</label>
                                        <textarea id="manifesto-{{ $candidate->id }}" name="manifesto" rows="3" maxlength="2000" @disabled($election->status !== 'draft') class="min-h-24 w-full rounded-xl border border-border bg-card px-4 py-3 text-sm text-ink focus:border-primary focus:ring-primary">{{ old('manifesto', $candidate->manifesto) }}</textarea>
                                    </div>
                                    <div
                                        x-data="photoUploader(@js(($candidate->photo_thumbnail ?: $candidate->photo) ? asset('storage/'.($candidate->photo_thumbnail ?: $candidate->photo)) : null))"
                                        class="space-y-2"
                                    >
                                        <label for="photo-{{ $candidate->id }}" class="mb-2 block text-sm font-semibold text-ink">Badili picha (si lazima)</label>
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-primary-light p-2">
                                                <img x-show="preview" x-cloak :src="preview" alt="{{ __('Onyesho la picha ya :name', ['name' => $candidate->name]) }}" class="h-full w-full rounded-xl object-cover">
                                                <span x-show="!preview" x-cloak class="text-center text-xs text-muted">{{ __('Hakuna picha iliyochaguliwa') }}</span>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <input id="photo-{{ $candidate->id }}" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" x-ref="photoInput" @change="handlePhotoChange($event)" @disabled($election->status !== 'draft') class="min-h-11 w-full rounded-xl border border-border bg-card px-3 py-2 text-sm text-body focus:border-primary focus:ring-primary">
                                                <p class="mt-1 text-xs text-muted">{{ __('Picha ya JPG, PNG au WEBP; hadi MB 3.') }}</p>
                                            </div>
                                        </div>
                                        @if ($candidate->photo || $candidate->photo_thumbnail)
                                            <label class="inline-flex min-h-11 items-center gap-2 text-sm font-medium text-body">
                                                <input type="checkbox" name="remove_photo" value="1" x-model="removePhoto" @change="togglePhotoRemoval()" @disabled($election->status !== 'draft') class="rounded border-border text-primary focus:ring-primary">
                                                {{ __('Ondoa picha iliyopo') }}
                                            </label>
                                        @endif
                                        <button type="button" x-show="objectUrl" x-cloak @click="clearPhotoSelection()" class="min-h-11 text-sm font-semibold text-danger underline">{{ __('Ondoa picha mpya') }}</button>
                                    </div>
                                    @if ($election->status === 'draft')
                                        <div class="flex flex-wrap gap-2">
                                            <x-button type="submit" variant="secondary">Hifadhi</x-button>
                                            <button type="submit" form="delete-candidate-{{ $candidate->id }}" class="min-h-11 rounded-xl px-3 text-sm font-semibold text-danger hover:bg-white">Futa mgombea</button>
                                        </div>
                                    @endif
                                </form>
                                @if ($election->status === 'draft')
                                    <form id="delete-candidate-{{ $candidate->id }}" method="POST" action="{{ route('admin.candidates.destroy', [$election, $position, $candidate]) }}" onsubmit="return confirm('Futa mgombea huyu?')">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-muted">Bado hakuna mgombea katika nafasi hii.</p>
                        @endforelse
                    </div>

                    @if ($election->status === 'draft')
                        <form method="POST" action="{{ route('admin.candidates.store', [$election, $position]) }}" enctype="multipart/form-data" class="mt-5 grid gap-4 rounded-2xl border border-dashed border-border p-4 sm:grid-cols-2">
                            @csrf
                            <div class="sm:col-span-2"><h4 class="font-semibold text-ink">Ongeza mgombea</h4></div>
                            <x-input name="name" :id="'new-candidate-name-'.$position->id" label="Jina kamili" required />
                            <x-input name="reg_number" :id="'new-candidate-reg-'.$position->id" label="Namba ya usajili" required />
                            <div x-data="photoUploader()" class="space-y-2">
                                <label for="new-photo-{{ $position->id }}" class="mb-2 block text-sm font-semibold text-ink">Picha (si lazima)</label>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-primary-light p-2">
                                        <img x-show="preview" x-cloak :src="preview" alt="{{ __('Onyesho la picha ya mgombea') }}" class="h-full w-full rounded-xl object-cover">
                                        <span x-show="!preview" x-cloak class="text-center text-xs text-muted">{{ __('Hakuna picha iliyochaguliwa') }}</span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <input id="new-photo-{{ $position->id }}" name="photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" x-ref="photoInput" @change="handlePhotoChange($event)" class="min-h-11 w-full rounded-xl border border-border bg-card px-3 py-2 text-sm text-body focus:border-primary focus:ring-primary">
                                        <p class="mt-1 text-xs text-muted">{{ __('Picha ya JPG, PNG au WEBP; hadi MB 3.') }}</p>
                                    </div>
                                </div>
                                <button type="button" x-show="objectUrl" x-cloak @click="clearPhotoSelection()" class="min-h-11 text-sm font-semibold text-danger underline">{{ __('Ondoa picha mpya') }}</button>
                            </div>
                            <div class="sm:col-span-2">
                                <label for="new-manifesto-{{ $position->id }}" class="mb-2 block text-sm font-semibold text-ink">Ilani fupi (si lazima)</label>
                                <textarea id="new-manifesto-{{ $position->id }}" name="manifesto" rows="3" maxlength="2000" class="min-h-24 w-full rounded-xl border border-border bg-card px-4 py-3 text-sm text-ink focus:border-primary focus:ring-primary"></textarea>
                            </div>
                            <div class="sm:col-span-2"><x-button type="submit">Ongeza mgombea</x-button></div>
                        </form>
                    @endif
                </x-card>
            @empty
                <x-empty-state title="Bado hakuna nafasi" description="Ongeza nafasi za uongozi ambazo wanafunzi watapigia kura." />
            @endforelse
        </div>
    </section>

    <section class="mt-8">
        <x-section-title title="Wapiga kura" description="Ingiza orodha ya wapiga kura kwa faili la CSV lenye safu reg_number, name, phone." />
        @if ($election->status === 'draft')
            <x-card class="mt-4">
                <form method="POST" action="{{ route('admin.voters.import', $election) }}" enctype="multipart/form-data" class="flex flex-col gap-4 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="csv" class="mb-2 block text-sm font-semibold text-ink">Faili la wapiga kura (CSV)</label>
                        <input id="csv" name="csv" type="file" accept=".csv,.xlsx,text/csv" required class="min-h-11 w-full rounded-xl border border-border bg-card px-3 py-2 text-sm text-body focus:border-primary focus:ring-primary">
                    </div>
                    <x-button type="submit">Ingiza na pakua misimbo</x-button>
                </form>
                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <a class="text-sm font-semibold text-primary underline" href="{{ route('admin.voters.template') }}">{{ __('Pakua kiolezo cha CSV') }}</a>
                    <p class="text-xs leading-5 text-muted">{{ __('Safu zinazotumika: reg_number, name, phone, department_code, course_code, class_name. Misimbo ya tarakimu 6 itapakuliwa mara moja; ihifadhi kwa usalama.') }}</p>
                </div>
            </x-card>
        @endif

        <x-card padding="none" class="mt-4">
            <div class="flex flex-col gap-4 border-b border-border p-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="font-semibold text-ink">{{ number_format($election->voters()->count()) }} waliosajiliwa</p>
                <form method="GET" action="{{ route('admin.elections.show', $election) }}" class="flex gap-2">
                    <label class="sr-only" for="voter-search">Tafuta mpiga kura</label>
                    <input id="voter-search" name="q" value="{{ $search }}" placeholder="Tafuta jina au namba" class="min-h-11 min-w-0 rounded-xl border border-border px-3 text-sm focus:border-primary focus:ring-primary">
                    <x-button type="submit" variant="secondary">Tafuta</x-button>
                </form>
            </div>
            @if ($voters->isEmpty())
                <div class="p-5"><x-empty-state title="Hakuna wapiga kura" description="Ingiza faili la CSV ili kusajili wapiga kura wa uchaguzi huu." /></div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[520px] text-left text-sm">
                        <thead class="bg-page text-xs font-semibold uppercase tracking-wide text-muted">
                            <tr><th scope="col" class="px-5 py-3">Jina</th><th scope="col" class="px-5 py-3">Namba ya usajili</th><th scope="col" class="px-5 py-3">Hali</th></tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($voters as $voter)
                                <tr>
                                    <td class="px-5 py-4 font-medium text-ink">{{ $voter->name }}</td>
                                    <td class="px-5 py-4 text-body">{{ $voter->reg_number }}</td>
                                    <td class="px-5 py-4">
                                        @if ($voter->has_voted)
                                            <x-badge variant="success">Amepiga kura</x-badge>
                                        @else
                                            <x-badge>Hajapiga kura</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="border-t border-border px-5 py-4">{{ $voters->links() }}</div>
            @endif
        </x-card>
    </section>
@endsection
