<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Models\Event;
use App\Models\EventBudget;
use App\Models\EventExpense;
use App\Models\EventIncome;
use App\Models\EventParticipant;
use App\Models\EventPayment;
use App\Services\EventFinancialService;
use App\Services\EventModerationService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EventController extends Controller
{
    protected EventModerationService $moderation;

    protected EventFinancialService $financial;

    public function __construct(EventModerationService $moderation, EventFinancialService $financial)
    {
        $this->moderation = $moderation;
        $this->financial = $financial;
    }

    public function index(Request $request)
    {
        $query = Event::withCount('participants');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('event_code', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $events = $query->orderBy('start_at', 'desc')->paginate(15)->withQueryString();

        return view('pengurus.events.index', compact('events'));
    }

    public function create()
    {
        return view('pengurus.events.create');
    }

    public function store(StoreEventRequest $request)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);
        $data['event_code'] = $this->generateEventCode();

        $this->moderation->storeEvent($data, auth()->id());

        return redirect()->route('pengurus.events.index')
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event)
    {
        $event->load(['participants.resident', 'incomes', 'expenses.category', 'budgets.category']);

        $summary = $this->financial->getEventFinancialSummary($event);

        return view('pengurus.events.show', compact('event', 'summary'));
    }

    public function edit(Event $event)
    {
        return view('pengurus.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event)
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['title']);

        $this->moderation->updateEvent($event, $data);

        return redirect()->route('pengurus.events.show', $event)
            ->with('success', 'Event berhasil diperbarui.');
    }

    public function destroy(Event $event)
    {
        $this->moderation->removeEvent($event);

        return redirect()->route('pengurus.events.index')
            ->with('success', 'Event berhasil dihapus.');
    }

    public function updateStatus(Request $request, Event $event)
    {
        $request->validate([
            'status' => 'required|in:draft,published,registration_open,ongoing,completed,cancelled,closed',
        ]);

        $this->moderation->updateStatus($event, $request->status);

        return back()->with('success', "Status event diubah menjadi {$request->status}.");
    }

    public function storeParticipant(Request $request, Event $event)
    {
        $request->validate([
            'resident_id' => 'required|exists:residents,id',
            'payment_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $participant = $this->moderation->addParticipant(
            $event,
            $request->resident_id,
            $request->payment_amount,
            $request->notes
        );

        if ($participant === null) {
            return back()->withErrors(['error' => 'Warga ini sudah terdaftar sebagai peserta.']);
        }

        return back()->with('success', 'Peserta berhasil ditambahkan.');
    }

    public function destroyParticipant(Event $event, EventParticipant $participant)
    {
        $this->moderation->removeParticipant($participant);

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    public function updateParticipantStatus(Request $request, Event $event, EventParticipant $participant)
    {
        $request->validate([
            'participation_status' => 'required|in:invited,registered,confirmed,cancelled,attended,absent',
        ]);

        $this->moderation->updateParticipantStatus($participant, $request->participation_status);

        return back()->with('success', 'Status peserta berhasil diperbarui.');
    }

    public function bulkParticipants(Request $request, Event $event)
    {
        $request->validate([
            'resident_ids' => 'required|array',
            'resident_ids.*' => 'exists:residents,id',
        ]);

        $added = $this->moderation->bulkAddParticipants($event, $request->resident_ids);

        return back()->with('success', "{$added} peserta berhasil ditambahkan.");
    }

    public function storePayment(Request $request, Event $event)
    {
        $request->validate([
            'event_participant_id' => 'required|exists:event_participants,id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->moderation->storePayment($event, $request->all(), auth()->id());

        return back()->with('success', 'Pembayaran event berhasil dicatat.');
    }

    public function voidPayment(Request $request, Event $event, EventPayment $payment)
    {
        try {
            $this->moderation->voidPayment($event, $payment, auth()->id());

            return back()->with('success', 'Pembayaran event berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeIncome(Request $request, Event $event)
    {
        $request->validate([
            'income_type' => 'required|in:resident_fee,donation,sponsor,rt_fund,other',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'income_date' => 'required|date',
            'source' => 'nullable|string|max:255',
            'reference_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->moderation->storeIncome($event, $request->all(), auth()->id());

        return back()->with('success', 'Pemasukan event berhasil dicatat.');
    }

    public function voidIncome(Request $request, Event $event, EventIncome $income)
    {
        try {
            $this->moderation->voidIncome($event, $income, auth()->id());

            return back()->with('success', 'Pemasukan event berhasil dibatalkan.');
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeExpense(Request $request, Event $event)
    {
        $request->validate([
            'category_id' => 'required|exists:event_expense_categories,id',
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'vendor' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $this->moderation->storeExpense($event, $request->all(), auth()->id());

        return back()->with('success', 'Pengeluaran event berhasil dicatat.');
    }

    public function voidExpense(Request $request, Event $event, EventExpense $expense)
    {
        $this->moderation->voidExpense($event, $expense, auth()->id());

        return back()->with('success', 'Pengeluaran event berhasil dibatalkan.');
    }

    public function storeBudget(Request $request, Event $event)
    {
        $request->validate([
            'category_id' => 'required|exists:event_expense_categories,id',
            'description' => 'nullable|string|max:255',
            'estimated_amount' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->moderation->storeBudget($event, $request->all());

        return back()->with('success', 'Anggaran berhasil ditambahkan.');
    }

    public function updateBudget(Request $request, Event $event, EventBudget $budget)
    {
        $request->validate([
            'estimated_amount' => 'required|numeric|min:0',
            'actual_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $this->moderation->updateBudget($budget, $request->only(['estimated_amount', 'actual_amount', 'notes']));

        return back()->with('success', 'Anggaran berhasil diperbarui.');
    }

    public function destroyBudget(Event $event, EventBudget $budget)
    {
        $this->moderation->removeBudget($budget);

        return back()->with('success', 'Anggaran berhasil dihapus.');
    }

    private function generateEventCode(): string
    {
        $year = date('Y');
        $lastEvent = Event::where('event_code', 'like', "EVT-{$year}-%")
            ->orderBy('event_code', 'desc')
            ->first();

        if ($lastEvent) {
            $lastNumber = (int) substr($lastEvent->event_code, -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        return "EVT-{$year}-{$newNumber}";
    }
}
