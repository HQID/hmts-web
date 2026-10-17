<?php

namespace App\Actions\Inventory;

use App\Models\Borrowing;
use App\Models\Inventory;
use App\Models\Period;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateBorrowingRequestAction
{
    public function __construct(private readonly CheckBorrowingConflictAction $conflicts) {}

    public function execute(Period $period, Inventory $inventory, array $request, CarbonInterface $start, CarbonInterface $end): Borrowing
    {
        $request = Validator::make($request, [
            'requester_name' => 'required|string|min:3|max:255',
            'requester_nim' => 'required|string|min:5|max:30',
            'purpose' => 'required|string|min:5|max:5000',
        ])->validate();

        if ($inventory->period_id !== $period->id || ! $inventory->is_active) {
            throw ValidationException::withMessages(['inventory' => 'Alat tidak tersedia pada periode aktif.']);
        }

        if ($end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['expected_return_time' => 'Waktu pengembalian harus setelah waktu mulai.']);
        }

        if (! $this->conflicts->isAvailable($inventory, 1, $start, $end)) {
            throw ValidationException::withMessages(['inventory' => 'Alat sudah penuh pada rentang waktu yang dipilih.']);
        }

        return DB::transaction(function () use ($period, $inventory, $request, $start, $end) {
            $borrowing = Borrowing::query()->create([
                'period_id' => $period->id,
                'requester_name' => $request['requester_name'],
                'requester_nim' => $request['requester_nim'],
                'purpose' => $request['purpose'],
                'start_time' => $start,
                'expected_return_time' => $end,
                'status' => 'menunggu',
            ]);

            $borrowing->items()->create(['inventory_id' => $inventory->id, 'qty' => 1]);

            return $borrowing;
        });
    }
}
