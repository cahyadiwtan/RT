<?php

namespace App\Http\Controllers\Warga;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAspirationRequest;
use App\Models\Aspiration;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class AspirationController extends Controller
{
    public function index()
    {
        $resident = Auth::user()->resident;
        $aspirations = collect();

        if ($resident) {
            $aspirations = Aspiration::where('resident_id', $resident->id)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
        }

        return view('warga.aspirations.index', compact('aspirations'));
    }

    public function create()
    {
        return view('warga.aspirations.create');
    }

    public function store(StoreAspirationRequest $request)
    {
        $resident = Auth::user()->resident;

        if (! $resident) {
            return redirect()->route('warga.aspirations.index')
                ->withErrors(['error' => 'Data warga Anda tidak ditemukan.']);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();
            $attachmentPath = $file->storeAs('attachments', $filename, 'public');
        }

        $aspiration = Aspiration::create([
            'resident_id' => $resident->id,
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'status' => 'submitted',
            'attachment' => $attachmentPath,
        ]);

        // Catat ke Audit Log
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'CREATE_ASPIRATION',
            'auditable_type' => Aspiration::class,
            'auditable_id' => $aspiration->id,
            'new_values' => [
                'title' => $aspiration->title,
                'category' => $aspiration->category,
                'status' => $aspiration->status,
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('warga.aspirations.index')
            ->with('success', 'Aspirasi Anda berhasil dikirimkan.');
    }

    public function show(Aspiration $aspiration)
    {
        Gate::authorize('view', $aspiration);

        $aspiration->load(['updates.user.resident', 'resident']);

        return view('warga.aspirations.show', compact('aspiration'));
    }
}
