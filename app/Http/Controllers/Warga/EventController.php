<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\EventFinancialService;
use Illuminate\Http\Request;

class EventController extends Controller
{
    protected EventFinancialService $financialService;

    public function __construct(EventFinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index()
    {
        $events = Event::whereIn('status', ['published', 'registration_open', 'ongoing', 'completed'])
            ->withCount('participants')
            ->orderBy('start_at', 'desc')
            ->paginate(15);

        return view('warga.events.index', compact('events'));
    }

    public function show(Event $event)
    {
        $event->load(['participants.resident', 'incomes', 'expenses.category', 'budgets.category']);

        $summary = $this->financialService->getEventFinancialSummary($event);

        $myPayment = null;
        if (auth()->user()->resident) {
            $myPayment = $event->participants()
                ->where('resident_id', auth()->user()->resident->id)
                ->first();
        }

        return view('warga.events.show', compact('event', 'summary', 'myPayment'));
    }
}
