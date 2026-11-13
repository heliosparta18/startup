<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Services\CatalogService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PublicCatalog extends Component
{
    use WithPagination;

    public string $clientId = '';

    public string $search = '';

    public string $sediaan = '';

    public string $kategori = 'Obat';

    public string $subKategori = '';

    /**
     * @var array<string, array<string, string>>
     */
    protected array $queryString = [
        'search' => ['except' => ''],
        'sediaan' => ['except' => ''],
        'kategori' => ['except' => 'Obat'],
        'subKategori' => ['except' => ''],
    ];

    public function mount(string $client = ''): void
    {
        $this->clientId = $client;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSediaan(): void
    {
        $this->resetPage();
    }

    public function updatingKategori(): void
    {
        $this->subKategori = '';
        $this->resetPage();
    }

    public function updatingSubKategori(): void
    {
        $this->resetPage();
    }

    public function selectCategory(string $kategori): void
    {
        $this->kategori = $kategori;
        $this->subKategori = '';
        $this->resetPage();
    }

    public function selectSubCategory(string $subKategori): void
    {
        $this->subKategori = ($this->subKategori === $subKategori) ? '' : $subKategori;
        $this->resetPage();
    }

    public function filterSubKategori(string $subKategori): void
    {
        $this->selectSubCategory($subKategori);
    }

    public function filterSediaan(string $jenis): void
    {
        $this->sediaan = ($this->sediaan === $jenis) ? '' : $jenis;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->sediaan = '';
        $this->subKategori = '';
        $this->resetPage();
    }

    public function render(CatalogService $catalogService): View
    {
        $obats = $catalogService->getCatalog(
            $this->clientId,
            $this->search ?: null,
            $this->sediaan ?: null,
            $this->kategori ?: null,
            $this->subKategori ?: null,
            15
        );

        return view('customer.catalog', [
            'obats' => $obats,
            'kategoriList' => $catalogService->getDaftarKategori(),
            'subkategoriList' => $catalogService->getDaftarSubkategori($this->kategori, $this->clientId),
            'sediaanList' => $catalogService->getDaftarSediaan(),
            'client' => $this->clientId,
            'kategoriAktif' => $this->kategori,
            'subKategoriAktif' => $this->subKategori,
        ])->layout('components.layouts.customer', [
            'title' => 'Katalog Obat & Kesehatan - OmniPharma Network',
            'client' => $this->clientId,
        ]);
    }
}
