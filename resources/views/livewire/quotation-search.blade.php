<div>
    <input type="text" wire:model="search" placeholder="Search by company..." class="form-control mb-3" />

    <div class="table-responsive">
        <table class="table table-centered table-nowrap mb-0">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Company</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotations as $quotation)
                    <tr>
                        <td>{{ $quotations->firstItem() + $loop->index }}</td>
                        <td>{{ $quotation->customer_name }}</td>
                        <td>{{ $quotation->quotation_date->format('Y-m-d') }}</td>
                        <td>{{ $quotation->items_count }}</td>
                        <td style="display: inline-block">
                            <div class="button-list" style="display: flex; flex-wrap: wrap; gap: 4px;">
                                <a href="{{ route('quotation.show', $quotation->id) }}" class="btn btn-success waves-effect waves-light btn-sm">View</a>
                                <a href="{{ route('quotation.edit', $quotation->id) }}" class="btn btn-secondary waves-effect waves-light btn-sm">Edit</a>
                                <form action="{{ route('quotation.delete', $quotation->id) }}" id="deletequotation{{ $quotation->id }}" method="POST">
                                    @csrf
                                    <button type="button" onclick="JSconfirmQuotation(event, {{ $quotation->id }})" class="btn btn-danger waves-effect waves-light btn-sm">Delete</button>
                                </form>
                                <a href="{{ route('quotation.download', $quotation->id) }}" class="btn btn-primary waves-effect waves-light btn-sm">PDF</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No quotations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        {{ $quotations->links() }}
    </div>
</div>
