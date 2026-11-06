<?php

namespace App\Modules\Catalog\Livewire;

use App\Modules\Catalog\Services\CatalogService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PublicCatalog extends Component
{
    use WithPagination;

    public string $clientId;

    public string $search = '';

    public string $sediaan = '';

    /**
     * @var array<int, string>
     */
    protected array $queryString = ['search', 'sediaan'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render(CatalogService $catalogService): View
    {
        $obats = $catalogService->getCatalog($this->clientId, $this->search, $this->sediaan ?: null);

        return view('customer.catalog', [
            'obats' => $obats,
        ]);
    }
}
