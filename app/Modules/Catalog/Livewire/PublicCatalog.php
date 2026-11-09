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

    public string $kategori = '';

    /**
     * @var array<string, array<string, string>>
     */
    protected array $queryString = [
        'search' => ['except' => ''],
        'sediaan' => ['except' => ''],
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

    public function filterSediaan(string $jenis): void
    {
        $this->sediaan = ($this->sediaan === $jenis) ? '' : $jenis;
        $this->resetPage();
    }

    public function render(CatalogService $catalogService): View
    {
        $obats = $catalogService->getCatalog(
            $this->clientId,
            $this->search ?: null,
            $this->sediaan ?: null,
            $this->kategori ?: null
        );

        return view('customer.catalog', [
            'obats' => $obats,
            'sediaanList' => $catalogService->getDaftarSediaan(),
            'client' => $this->clientId,
        ])->layout('components.layouts.customer', [
            'title' => 'Katalog Obat - OmniPharma Network',
            'client' => $this->clientId,
        ]);
    }
}
