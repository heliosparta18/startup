@php
    $avatarUrl = $photoUrl ?? null;
    $avatarInitial = strtoupper(substr(trim((string) ($userName ?? 'U')), 0, 1)) ?: 'U';
    $sizeClass = $sizeClass ?? 'w-7 h-7 text-xs';
@endphp
<span class="relative inline-flex shrink-0 items-center justify-center rounded-full {{ $sizeClass }} bg-emerald-700 text-white font-bold select-none overflow-hidden border border-emerald-800/20">
    <span aria-hidden="true">{{ $avatarInitial }}</span>
    @if (! empty($avatarUrl))
        <img
            src="{{ $avatarUrl }}"
            alt=""
            class="absolute inset-0 w-full h-full object-cover"
            onerror="this.remove();"
        >
    @endif
</span>
