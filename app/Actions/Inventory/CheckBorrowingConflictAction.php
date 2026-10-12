<?php

namespace App\Actions\Inventory;

use App\Models\Borrowing;
use App\Models\Inventory;
use Carbon\CarbonInterface;

class CheckBorrowingConflictAction
{
    public function isAvailable(Inventory $inventory, int $requestedQuantity, CarbonInterface $start, CarbonInterface $end, ?int $excludeBorrowingId = null): bool
    {
        $reservedQuantity = $inventory->borrowingItems()
            ->whereHas('borrowing', function ($query) use ($start, $end, $excludeBorrowingId) {
                $query->whereIn('status', ['disetujui', 'sedang_dipinjam'])
                    ->where('start_time', '<', $end)
                    ->where('expected_return_time', '>', $start)
                    ->when($excludeBorrowingId, fn ($query) => $query->where('borrowings.id', '!=', $excludeBorrowingId));
            })
            ->sum('qty');

        return ($inventory->total_qty - $reservedQuantity) >= $requestedQuantity;
    }
}
