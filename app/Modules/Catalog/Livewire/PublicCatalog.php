<?php

namespace App\Modules\Catalog\Livewire;

use App\Models\Client;
use App\Modules\Catalog\Services\CatalogService;
use App\Modules\Catalog\Services\CustomerSessionService;
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

    /**
     * Beralih ke cabang apotek lain dan memperbarui sesi serta preferensi pengguna.
     */
    public function switchBranch(string $branchSlug, ?CustomerSessionService $sessionService = null): mixed
    {
        $sessionService = $sessionService ?: app(CustomerSessionService::class);
        $client = Client::where('slug', $branchSlug)
            ->orWhere('id_client', $branchSlug)
            ->first();

        if ($client) {
            $sessionService->setActiveBranch($client->id_client);
            session([
                'active_branch' => $client->slug,
                'active_client' => $client->slug,
            ]);
            $this->clientId = $client->slug;

            return $this->redirect(route('customer.catalog', ['client' => $client->slug]));
        }

        return null;
    }

    public function render(CatalogService $catalogService): View
    {
        $obats = $catalogService->getCatalog(
            $this->clientId,
            $this->search ?: null,
            $this->sediaan ?: null,
            $this->kategori ?: null,
            $this->subKategori ?: null,
            15,
            true // Mode katalog terpadu multi-cabang (Unified Catalog)
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
            'fullWidth' => true,
        ]);
    }
}
