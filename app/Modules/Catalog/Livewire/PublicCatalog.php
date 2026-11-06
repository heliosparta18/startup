<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Services\CatalogService;
use Livewire\Component;
use Livewire\WithPagination;

class PublicCatalog extends Component
{
    use WithPagination;

    public string $clientId;

    public string $search = '';

    public string $sediaan = '';

    protected $queryString = ['search', 'sediaan'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render(CatalogService $catalogService)
    {
        $obats = $catalogService->getCatalog($this->clientId, $this->search, $this->sediaan ?: null);

        return view('customer.catalog', [
            'obats' => $obats,
        ]);
    }
}
