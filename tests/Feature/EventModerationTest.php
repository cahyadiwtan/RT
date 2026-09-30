<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventExpenseCategory;
use App\Models\House;
use App\Models\Resident;
use App\Models\User;
use App\Services\EventFinancialService;
use App\Services\EventModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventModerationTest extends TestCase
{
    use RefreshDatabase;

    private function pengurus(): User
    {
        return User::create([
            'username' => 'pengurus' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'pengurus',
        ]);
    }

    private function warga(): User
    {
        return User::create([
            'username' => 'warga' . uniqid(),
            'password' => bcrypt('password'),
            'role' => 'warga',
        ]);
    }

    private function resident(): Resident
    {
        $n = random_int(1, 9999);
        $house = House::create([
            'block' => 'A',
            'house_number' => str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'ditempati',
        ]);

        return Resident::create([
            'house_id' => $house->id,
            'nik' => '320101010101' . str_pad((string) $n, 5, '0', STR_PAD_LEFT),
            'nama_lengkap' => 'Budi Santoso',
        ]);
    }

    private function event(string $fundingTyype = 'free'): Event
    {
        $owner = $this->pengurus();

        return Event::create([
            'event_code' => 'EVT-' . date('Y') . '-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'title' => 'Kerja Bakti ' . uniqid(),
            'slug' => 'kerja-bakti-' . uniqid(),
            'funding_type' => $fundingTyype,
            'required_payment' => $fundingTyype === 'free' ? 0 : 50000,
            'status' => 'published',
            'visibility' => 'summary',
            'created_by' => $owner->id,
        ]);
    }

    public function test_participant_payment_creates_mirror_income_and_void_reverses_both(): void
    {
        $admin = $this->pengurus();
        $event = $this->event('resident_fee');
        $resident = $this->resident();

        $service = new EventModerationService;
        $participant = $service->addParticipant($event, $resident->id);

        $this->assertNotNull($participant);
        $payment = $service->storePayment($event, [
            'event_participant_id' => $participant->id,
            'payment_date' => '2026-08-01',
            'amount' => 50000,
            'payment_method' => 'cash',
        ], $admin->id);

        $this->assertDatabaseHas('event_payments', ['id' => $payment->id, 'status' => 'active']);
        $this->assertDatabaseHas('event_income', [
            'event_id' => $event->id,
            'linked_payment_id' => $payment->id,
            'amount' => 50000,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('event_participants', ['id' => $participant->id, 'payment_status' => 'paid']);

        $service->voidPayment($event, $payment, $admin->id);

        $this->assertDatabaseHas('event_payments', ['id' => $payment->id, 'status' => 'void']);
        $this->assertDatabaseHas('event_income', [
            'event_id' => $event->id,
            'linked_payment_id' => $payment->id,
            'status' => 'void',
        ]);
        $this->assertDatabaseHas('event_participants', ['id' => $participant->id, 'payment_status' => 'unpaid']);
    }

    public function test_void_linked_income_is_rejected(): void
    {
        $admin = $this->pengurus();
        $event = $this->event('resident_fee');
        $resident = $this->resident();

        $service = new EventModerationService;
        $participant = $service->addParticipant($event, $resident->id);
        $payment = $service->storePayment($event, [
            'event_participant_id' => $participant->id,
            'payment_date' => '2026-08-01',
            'amount' => 50000,
        ], $admin->id);

        $mirror = \App\Models\EventIncome::where('linked_payment_id', $payment->id)->firstOrFail();

        $this->expectException(\Exception::class);
        $service->voidIncome($event, $mirror, $admin->id);
    }

    public function test_add_participant_rejects_duplicate(): void
    {
        $event = $this->event('resident_fee');
        $resident = $this->resident();

        $service = new EventModerationService;
        $first = $service->addParticipant($event, $resident->id);
        $duplicate = $service->addParticipant($event, $resident->id);

        $this->assertNotNull($first);
        $this->assertNull($duplicate);
        $this->assertSame(1, $event->participants()->count());
    }

    public function test_bulk_add_participants_adds_and_skips_duplicates(): void
    {
        $event = $this->event('resident_fee');
        $r1 = $this->resident();
        $r2 = $this->resident();

        $service = new EventModerationService;
        $service->addParticipant($event, $r1->id);

        $added = $service->bulkAddParticipants($event, [$r1->id, $r2->id]);

        $this->assertSame(1, $added);
        $this->assertSame(2, $event->participants()->count());
    }

    public function test_store_income_records_audit_log(): void
    {
        $admin = $this->pengurus();
        $event = $this->event();

        $service = new EventModerationService;
        $income = $service->storeIncome($event, [
            'income_type' => 'donation',
            'description' => 'Sumbangan',
            'amount' => 100000,
            'income_date' => '2026-08-02',
        ], $admin->id);

        $this->assertDatabaseHas('event_income', ['id' => $income->id, 'status' => 'active']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_EVENT_INCOME',
            'auditable_type' => \App\Models\EventIncome::class,
            'auditable_id' => $income->id,
        ]);
    }

    public function test_store_expense_records_audit_log(): void
    {
        $admin = $this->pengurus();
        $event = $this->event();
        $category = EventExpenseCategory::create(['name' => 'Konsumsi']);

        $service = new EventModerationService;
        $expense = $service->storeExpense($event, [
            'category_id' => $category->id,
            'description' => 'Snack',
            'amount' => 50000,
            'expense_date' => '2026-08-02',
        ], $admin->id);

        $this->assertDatabaseHas('event_expenses', ['id' => $expense->id, 'status' => 'active']);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'CREATE_EVENT_EXPENSE',
            'auditable_type' => \App\Models\EventExpense::class,
            'auditable_id' => $expense->id,
        ]);
    }

    public function test_budget_crud(): void
    {
        $event = $this->event();
        $category = EventExpenseCategory::create(['name' => 'Dekorasi']);

        $service = new EventModerationService;
        $budget = $service->storeBudget($event, [
            'category_id' => $category->id,
            'description' => 'Dekorasi panggung',
            'estimated_amount' => 200000,
            'notes' => 'Awal',
        ]);

        $this->assertDatabaseHas('event_budgets', ['id' => $budget->id, 'estimated_amount' => 200000]);

        $service->updateBudget($budget, ['estimated_amount' => 250000, 'actual_amount' => 240000]);
        $this->assertDatabaseHas('event_budgets', ['id' => $budget->id, 'estimated_amount' => 250000, 'actual_amount' => 240000]);

        $service->removeBudget($budget);
        $this->assertDatabaseMissing('event_budgets', ['id' => $budget->id]);
    }

    public function test_update_participant_status(): void
    {
        $event = $this->event();
        $resident = $this->resident();

        $service = new EventModerationService;
        $participant = $service->addParticipant($event, $resident->id);

        $service->updateParticipantStatus($participant, 'attended');

        $this->assertDatabaseHas('event_participants', ['id' => $participant->id, 'participation_status' => 'attended']);
    }

    public function test_mirror_income_is_not_double_counted_in_financial_summary(): void
    {
        $admin = $this->pengurus();
        $event = $this->event('resident_fee');
        $resident = $this->resident();

        $service = new EventModerationService;
        $participant = $service->addParticipant($event, $resident->id);
        $service->storePayment($event, [
            'event_participant_id' => $participant->id,
            'payment_date' => '2026-08-01',
            'amount' => 50000,
        ], $admin->id);

        $financial = new EventFinancialService;

        $this->assertSame(50000.0, $financial->getTotalIncome($event));
    }

    public function test_warga_cannot_access_event_moderation_actions(): void
    {
        $wargaUser = $this->warga();
        $event = $this->event();

        $this->actingAs($wargaUser)->get('/pengurus/events')->assertStatus(403);
        $this->actingAs($wargaUser)->post('/pengurus/events', [])->assertStatus(403);
        $this->actingAs($wargaUser)->post("/pengurus/events/{$event->id}/participants", [])->assertStatus(403);
    }

    public function test_dead_index_routes_return_404(): void
    {
        $admin = $this->pengurus();
        $event = $this->event();

        $this->actingAs($admin)->get("/pengurus/events/{$event->id}/participants")->assertStatus(405);
        $this->actingAs($admin)->get("/pengurus/events/{$event->id}/payments")->assertStatus(405);
        $this->actingAs($admin)->get("/pengurus/events/{$event->id}/incomes")->assertStatus(405);
        $this->actingAs($admin)->get("/pengurus/events/{$event->id}/expenses")->assertStatus(405);
        $this->actingAs($admin)->get("/pengurus/events/{$event->id}/budgets")->assertStatus(405);
    }
}
