<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAspirationUpdateRequest;
use App\Models\Aspiration;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AspirationController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Aspiration::class);

        $query = Aspiration::with('resident');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('resident', function ($rq) use ($search) {
                        $rq->where('nama_lengkap', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $aspirations = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('pengurus.aspirations.index', compact('aspirations'));
    }

    public function show(Aspiration $aspiration)
    {
        Gate::authorize('view', $aspiration);

        $aspiration->load(['resident.house', 'updates.user.resident']);

        return view('pengurus.aspirations.show', compact('aspiration'));
    }

    public function updateStatus(StoreAspirationUpdateRequest $request, Aspiration $aspiration)
    {
        Gate::authorize('update', $aspiration);

        DB::transaction(function () use ($request, $aspiration) {
            $oldStatus = $aspiration->status;

            // Update status utama
            $aspiration->update([
                'status' => $request->status,
            ]);

            // Buat history update
            $aspiration->updates()->create([
                'user_id' => Auth::id(),
                'status' => $request->status,
                'comment' => $request->comment,
            ]);

            // Catat Audit Log
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPDATE_ASPIRATION',
                'auditable_type' => Aspiration::class,
                'auditable_id' => $aspiration->id,
                'old_values' => [
                    'status' => $oldStatus,
                ],
                'new_values' => [
                    'status' => $request->status,
                    'comment' => $request->comment,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        });

        return redirect()->route('pengurus.aspirations.show', $aspiration->id)
            ->with('success', 'Status aspirasi berhasil diperbarui.');
    }
}
