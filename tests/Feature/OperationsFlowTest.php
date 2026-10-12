<?php

namespace Tests\Feature;

use App\Models\Aspiration;
use App\Models\Borrowing;
use App\Models\BorrowingItem;
use App\Models\Inventory;
use App\Models\Period;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationsFlowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): array
    {
        $period = Period::create(['name' => '2026/2027', 'start_year' => 2026, 'end_year' => 2027, 'is_active' => true]);
        return [$period, User::factory()->create(['role' => 'admin'])];
    }

    public function test_admin_can_approve_borrowing_and_stock_is_reserved(): void
    {
        [$period, $admin] = $this->admin();
        $inventory = Inventory::create(['period_id' => $period->id, 'name' => 'Theodolite', 'code' => 'INV-001', 'total_qty' => 2, 'available_qty' => 2, 'condition' => 'baik']);
        $borrowing = Borrowing::create(['period_id' => $period->id, 'requester_name' => 'Sinta', 'requester_nim' => 'F123', 'purpose' => 'Praktikum', 'start_time' => now(), 'expected_return_time' => now()->addDay(), 'status' => 'menunggu']);
        BorrowingItem::create(['borrowing_id' => $borrowing->id, 'inventory_id' => $inventory->id, 'qty' => 1]);

        $this->actingAs($admin)->post(route('app.operations.operate', ['borrowings', $borrowing->id]), ['status' => 'disetujui'])
            ->assertRedirect();

        $this->assertDatabaseHas('borrowings', ['id' => $borrowing->id, 'status' => 'disetujui', 'verified_by_user_id' => $admin->id]);
        $this->assertDatabaseHas('inventories', ['id' => $inventory->id, 'available_qty' => 1]);
    }

    public function test_admin_can_moderate_an_anonymous_aspiration(): void
    {
        [$period, $admin] = $this->admin();
        $aspiration = Aspiration::create(['period_id' => $period->id, 'tracking_code' => 'ASP-2026-ABC123', 'category' => 'Fasilitas', 'message' => 'Perbaiki lampu.']);

        $this->actingAs($admin)->post(route('app.operations.operate', ['aspirations', $aspiration->id]), ['status' => 'ditanggapi', 'response' => 'Sudah diteruskan.'])
            ->assertRedirect();

        $this->assertDatabaseHas('aspirations', ['id' => $aspiration->id, 'status' => 'ditanggapi', 'response' => 'Sudah diteruskan.', 'responded_by_user_id' => $admin->id]);
    }

    public function test_regular_admin_cannot_manage_periods(): void
    {
        [, $admin] = $this->admin();

        $this->actingAs($admin)->post(route('app.operations.store', 'periods'), ['name' => '2027/2028', 'start_year' => 2027, 'end_year' => 2028])
            ->assertForbidden();
    }
}
