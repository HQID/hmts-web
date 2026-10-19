<?php

namespace App\Console\Commands;

use App\Models\Borrowing;
use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ReconcileInventoryStock extends Command
{
    protected $signature = 'hmts:reconcile-inventory {--apply : Terapkan hasil yang konsisten (default dry run)}';

    protected $description = 'Konversi stok lama yang berkurang saat persetujuan menjadi stok fisik. Jalankan dalam maintenance.';

    public function handle(): int
    {
        $failed = false;
        foreach (Inventory::withoutGlobalScopes()->where('stock_version', 1)->orderBy('id')->cursor() as $inventory) {
            try {
                DB::transaction(function () use ($inventory) {
                    $inventory = Inventory::withoutGlobalScopes()->lockForUpdate()->findOrFail($inventory->id);
                    if ($inventory->stock_version !== 1) {
                        return;
                    }
                    $approved = $out = 0;
                    // Include historical periods; an active-period scope must not hide outstanding loans.
                    $borrowings = Borrowing::withoutGlobalScopes()->where('period_id', $inventory->period_id)->whereIn('status', ['disetujui', 'dipinjam', 'sedang_dipinjam'])->with('items')->get();
                    foreach ($borrowings as $borrowing) {
                        foreach ($borrowing->items as $item) {
                            if ($item->inventory_id !== $inventory->id) {
                                continue;
                            }
                            if ($borrowing->status === 'disetujui') {
                                $approved += $item->qty;
                            } else {
                                $out += $item->qty;
                            }
                        }
                    }
                    $physical = $inventory->available_qty + $approved;
                    if ($physical < 0 || $physical + $out > $inventory->total_qty) {
                        throw new RuntimeException('Selisih stok memerlukan rekonsiliasi manual.');
                    }
                    $this->line('Inventaris #'.$inventory->id.': '.$inventory->available_qty.' -> '.$physical.($this->option('apply') ? ' (diterapkan)' : ' (dry run)'));
                    if ($this->option('apply')) {
                        $inventory->forceFill(['available_qty' => $physical, 'stock_version' => 2])->save();
                    }
                });
            } catch (\Throwable $exception) {
                $failed = true;
                $this->error('Inventaris #'.$inventory->id.': '.$exception->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
