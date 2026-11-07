<div>
    {{-- Public Catalog Livewire View --}}
    @foreach ($obats as $obat)
        <div>{{ $obat->nama_obat }}</div>
    @endforeach
    {{ $obats->links() }}
</div>
