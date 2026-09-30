<?php

namespace Tests\Feature;

use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\Resident;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_fifo_payment_allocation_and_credit_balance(): void
    {
        $house = House::create(['block' => 'A', 'house_number' => '01', 'status' => 'ditempati']);
        $resident = Resident::create([
            'house_id' => $house->id,
            'nik' => '3201010101010001',
            'nama_lengkap' => 'Budi Santoso',
        ]);
        $admin = User::create([
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);

        // Create 3 bills of 30,000 each: June, July, August 2026
        $billJune = MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-06',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
        ]);

        $billJuly = MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-07',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
        ]);

        $billAug = MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-08',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
        ]);

        $service = new PaymentService;

        // Record payment of 70,000
        $payment = $service->recordPayment([
            'house_id' => $house->id,
            'resident_id' => $resident->id,
            'payment_date' => '2026-08-10',
            'amount' => 70000,
            'payment_method' => 'cash',
        ], $admin->id);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'amount' => 70000, 'status' => 'active']);

        // Check allocations: June (30k - paid), July (30k - paid), August (10k paid / 20k remaining)
        $this->assertDatabaseHas('monthly_bills', ['id' => $billJune->id, 'paid_amount' => 30000, 'remaining_amount' => 0, 'status' => 'paid']);
        $this->assertDatabaseHas('monthly_bills', ['id' => $billJuly->id, 'paid_amount' => 30000, 'remaining_amount' => 0, 'status' => 'paid']);
        $this->assertDatabaseHas('monthly_bills', ['id' => $billAug->id, 'paid_amount' => 10000, 'remaining_amount' => 20000, 'status' => 'partial']);
    }

    public function test_void_payment_reverses_allocations(): void
    {
        $house = House::create(['block' => 'B', 'house_number' => '01', 'status' => 'ditempati']);
        $admin = User::create(['username' => 'admin2', 'password' => bcrypt('password'), 'role' => 'pengurus']);

        $bill = MonthlyBill::create([
            'house_id' => $house->id,
            'billing_period' => '2026-06',
            'amount' => 30000,
            'paid_amount' => 0,
            'remaining_amount' => 30000,
            'status' => 'unpaid',
        ]);

        $service = new PaymentService;
        $payment = $service->recordPayment([
            'house_id' => $house->id,
            'payment_date' => '2026-06-05',
            'amount' => 30000,
        ], $admin->id);

        $this->assertDatabaseHas('monthly_bills', ['id' => $bill->id, 'status' => 'paid']);

        // Void the payment
        $service->voidPayment($payment->id, $admin->id);

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'void']);
        $this->assertDatabaseHas('monthly_bills', ['id' => $bill->id, 'paid_amount' => 0, 'remaining_amount' => 30000, 'status' => 'unpaid']);
    }
}
