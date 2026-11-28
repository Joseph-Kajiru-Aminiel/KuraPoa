@csrf
<x-input name="title" label="Jina la uchaguzi" :value="old('title', $election->title ?? '')" required />
<div class="grid gap-5 sm:grid-cols-2">
    <x-input name="starts_at" label="Muda wa kuanza" type="datetime-local" :value="old('starts_at', isset($election) ? $election->starts_at->format('Y-m-d\TH:i') : '')" required />
    <x-input name="ends_at" label="Muda wa kumalizika" type="datetime-local" :value="old('ends_at', isset($election) ? $election->ends_at->format('Y-m-d\TH:i') : '')" required />
</div>
<div class="flex flex-col-reverse gap-3 pt-2 sm:flex-row sm:justify-end">
    <x-button variant="secondary" :href="isset($election) ? route('admin.elections.show', $election) : route('admin.elections.index')">Ghairi</x-button>
    <x-button type="submit">{{ isset($election) ? 'Hifadhi mabadiliko' : 'Unda uchaguzi' }}</x-button>
</div>
