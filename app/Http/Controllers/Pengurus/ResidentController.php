<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\UpdateResidentRequest;
use App\Models\House;
use App\Models\MonthlyBill;
use App\Models\Resident;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function index(Request $request)
    {
        $query = Resident::with(['house', 'user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nik', 'like', "%{$search}%")
                    ->orWhere('nomor_kk', 'like', "%{$search}%");
            });
        }

        if ($request->filled('house_id')) {
            $query->where('house_id', $request->house_id);
        }

        if ($request->filled('status_warga')) {
            $query->where('status_warga', $request->status_warga);
        }

        if ($request->filled('is_verified')) {
            $query->where('is_verified', $request->is_verified === '1');
        }

        $residents = $query->orderBy('nama_lengkap', 'asc')->paginate(15)->withQueryString();
        $houses = House::orderBy('block')->orderBy('house_number')->get();

        $arrearsCountByHouse = MonthlyBill::whereIn('status', ['unpaid', 'partial'])
            ->select('house_id', \DB::raw('COUNT(*) as total'))
            ->groupBy('house_id')
            ->pluck('total', 'house_id')
            ->toArray();

        $arrearsDetailsByHouse = MonthlyBill::whereIn('status', ['unpaid', 'partial'])
            ->select('house_id', 'billing_period', 'amount', 'paid_amount', 'remaining_amount', 'status')
            ->orderBy('billing_period', 'desc')
            ->get()
            ->groupBy('house_id');

        return view('pengurus.residents.index', compact('residents', 'houses', 'arrearsCountByHouse', 'arrearsDetailsByHouse'));
    }

    public function create()
    {
        $houses = House::orderBy('block')->orderBy('house_number')->get();

        return view('pengurus.residents.create', compact('houses'));
    }

    public function store(StoreResidentRequest $request)
    {
        $data = $request->validated();
        if (! empty($data['is_verified']) && $data['is_verified']) {
            $data['verified_at'] = now();
        }

        Resident::create($data);

        return redirect()->route('pengurus.residents.index')
            ->with('success', 'Data warga berhasil ditambahkan.');
    }

    public function edit(Resident $resident)
    {
        $houses = House::orderBy('block')->orderBy('house_number')->get();

        return view('pengurus.residents.edit', compact('resident', 'houses'));
    }

    public function update(UpdateResidentRequest $request, Resident $resident)
    {
        $data = $request->validated();
        if (! empty($data['is_verified']) && $data['is_verified'] && ! $resident->is_verified) {
            $data['verified_at'] = now();
        }

        $resident->update($data);

        return redirect()->route('pengurus.residents.index')
            ->with('success', 'Data warga berhasil diperbarui.');
    }

    public function toggleVerify(Resident $resident)
    {
        $newStatus = ! $resident->is_verified;
        $resident->update([
            'is_verified' => $newStatus,
            'verified_at' => $newStatus ? now() : null,
        ]);

        $statusText = $newStatus ? 'diverifikasi' : 'batal diverifikasi';

        return back()->with('success', "Data warga {$resident->nama_lengkap} telah {$statusText}.");
    }

    public function destroy(Resident $resident)
    {
        $resident->delete();

        return redirect()->route('pengurus.residents.index')
            ->with('success', 'Data warga berhasil dihapus.');
    }
}
