<?php

namespace App\Actions\Inventory;

use App\Models\Inventory;
use Carbon\CarbonInterface;

class CheckBorrowingConflictAction
{
    public function isAvailable(Inventory $inventory, int $requestedQuantity, CarbonInterface $start, CarbonInterface $end, ?int $excludeBorrowingId = null): bool
    {
        if ($requestedQuantity < 1 || ! $inventory->is_active || $inventory->condition !== 'baik' || $inventory->stock_version !== 2 || $end->lessThanOrEqualTo($start)) {
            return false;
        }
        $capacity = min($inventory->total_qty, $inventory->available_qty + $inventory->checkedOutQuantity());

        return $capacity - $this->peakReserved($inventory, $start, $end, $excludeBorrowingId) >= $requestedQuantity;
    }

    public function peakReserved(Inventory $inventory, CarbonInterface $start, CarbonInterface $end, ?int $excludeBorrowingId = null): int
    {
        $items = $inventory->borrowingItems()->with('borrowing')->whereHas('borrowing', function ($q) use ($start, $end, $excludeBorrowingId) {
            $q->whereIn('status', ['disetujui', 'dipinjam', 'sedang_dipinjam'])->where('start_time', '<', $end)
                ->where(fn ($q) => $q->where('expected_return_time', '>', $start)->orWhere(fn ($q) => $q->whereIn('status', ['dipinjam', 'sedang_dipinjam'])->where('expected_return_time', '<=', now())))
                ->when($excludeBorrowingId, fn ($q) => $q->where('borrowings.id', '!=', $excludeBorrowingId));
        })->get();
        $events = [];
        foreach ($items as $item) {
            $borrowing = $item->borrowing;
            $from = max($start->timestamp, $borrowing->start_time->timestamp);
            $overdue = in_array($borrowing->status, ['dipinjam', 'sedang_dipinjam'], true) && $borrowing->expected_return_time->lte(now());
            $until = $overdue ? $end->timestamp : min($end->timestamp, $borrowing->expected_return_time->timestamp);
            if ($from >= $until) {
                continue;
            }
            $events[$from] = ($events[$from] ?? 0) + $item->qty;
            $events[$until] = ($events[$until] ?? 0) - $item->qty;
        }
        ksort($events, SORT_NUMERIC);
        $current = $peak = 0;
        foreach ($events as $delta) {
            $current += $delta;
            $peak = max($peak, $current);
        }

        return $peak;
    }
}
