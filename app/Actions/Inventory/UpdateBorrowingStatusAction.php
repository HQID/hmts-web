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

        return DB::transaction(function () use ($id, $status, $note, $actor) {
            $borrowing = Borrowing::where('period_id', Period::current()?->id)->lockForUpdate()->findOrFail($id);
            if (! in_array($status, $borrowing->allowedStatuses(), true)) {
                throw ValidationException::withMessages(['status' => 'Perubahan status peminjaman tidak valid. Muat ulang data sebelum mencoba lagi.']);
            }

            $items = $borrowing->items()->orderBy('inventory_id')->get();
            if ($items->isEmpty()) {
                throw ValidationException::withMessages(['status' => 'Pengajuan tidak memiliki alat.']);
            }

            $inventories = Inventory::where('period_id', $borrowing->period_id)
                ->whereIn('id', $items->pluck('inventory_id'))->orderBy('id')->lockForUpdate()->get()->keyBy('id');

            foreach ($items as $item) {
                $inventory = $inventories->get($item->inventory_id);
                if (! $inventory || $item->qty < 1) {
                    throw ValidationException::withMessages(['status' => 'Data alat pada pengajuan tidak valid.']);
                }

                if ($status === 'disetujui') {
                    if (! $inventory->is_active || $inventory->available_qty < $item->qty || ! $this->conflicts->isAvailable($inventory, $item->qty, $borrowing->start_time, $borrowing->expected_return_time, $borrowing->id)) {
                        throw ValidationException::withMessages(['status' => 'Stok atau jadwal '.$inventory->name.' tidak tersedia.']);
                    }
                    $inventory->decrement('available_qty', $item->qty);
                } elseif ($status === 'dikembalikan' || ($status === 'ditolak' && $borrowing->status === 'disetujui')) {
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
