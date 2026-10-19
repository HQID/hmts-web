<?php

namespace App\Actions\Inventory;

use App\Models\Borrowing;
use App\Models\Inventory;
use App\Models\Period;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateBorrowingStatusAction
{
    public function __construct(private readonly CheckBorrowingConflictAction $conflicts) {}

    public function execute(int $id, string $status, ?string $note, User $actor): Borrowing
    {
        Gate::forUser($actor)->authorize('manage-command-center');
        $periodId = Period::current()?->id;
        $inventoryIds = Borrowing::where('period_id', $periodId)->findOrFail($id)->items()->orderBy('inventory_id')->pluck('inventory_id')->all();

        return DB::transaction(function () use ($id, $status, $note, $actor, $periodId, $inventoryIds) {
            // Lock stock before any consistent read: MySQL REPEATABLE READ must see the previous reservation commit.
            $inventories = Inventory::withoutGlobalScopes()->where('period_id', $periodId)
                ->whereIn('id', $inventoryIds)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $borrowing = Borrowing::withoutGlobalScopes()->where('period_id', $periodId)->lockForUpdate()->findOrFail($id);
            if (! in_array($status, $borrowing->allowedStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status peminjaman tidak valid. Muat ulang data sebelum mencoba lagi.']);
            }

            $items = $borrowing->items()->orderBy('inventory_id')->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['status' => 'Pengajuan tidak memiliki alat.']);
            }

            foreach ($items as $item) {
                $inventory = $inventories->get($item->inventory_id);
                if (! $inventory || $item->qty < 1) {
                    throw ValidationException::withMessages(['status' => 'Data alat pada pengajuan tidak valid.']);
                }
                if ($inventory->stock_version !== 2) {
                    throw ValidationException::withMessages(['status' => 'Stok lama perlu direkonsiliasi oleh pengelola sebelum transaksi.']);
                }

                if ($status === 'disetujui') {
                    if (! $this->conflicts->isAvailable($inventory, $item->qty, $borrowing->start_time, $borrowing->expected_return_time, $borrowing->id)) {
                        throw ValidationException::withMessages(['status' => 'Stok atau jadwal '.$inventory->name.' tidak tersedia.']);
                    }
                } elseif ($status === 'dipinjam') {
                    if (! $inventory->is_active || $inventory->condition !== 'baik' || $inventory->available_qty < $item->qty || now()->lt($borrowing->start_time) || now()->gte($borrowing->expected_return_time)) {
                        throw ValidationException::withMessages(['status' => 'Alat fisik atau waktu serah-terima belum tersedia.']);
                    }
                    $inventory->decrement('available_qty', $item->qty);
                } elseif ($status === 'dikembalikan') {
                    if ($inventory->available_qty + $item->qty > $inventory->total_qty) {
                        throw ValidationException::withMessages(['status' => 'Stok alat tidak konsisten. Periksa data sebelum melanjutkan.']);
                    }
                    $inventory->increment('available_qty', $item->qty);
                }
            }

            $borrowing->update([
                'status' => $status,
                'admin_note' => $note,
                'verified_by_user_id' => $actor->id,
                'returned_at' => $status === 'dikembalikan' ? now() : null,
            ]);

            return $borrowing;
        }, 3);
    }
}
