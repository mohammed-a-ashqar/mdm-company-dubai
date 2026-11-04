<?php

namespace App\Http\Livewire;

use App\Models\Quotation;
use Livewire\Component;
use Livewire\WithPagination;

class QuotationSearch extends Component
{
    use WithPagination;

    public $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function paginationView(): string
    {
        return 'livewire.pagination';
    }

    public function render()
    {
        $quotations = Quotation::query()
            ->withCount('items')
            ->when($this->search !== '', fn ($q) => $q->where('customer_name', 'like', '%' . $this->search . '%'))
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.quotation-search', compact('quotations'));
    }
}
