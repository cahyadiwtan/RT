<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetCategory;
use App\Services\AssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function __construct(protected AssetService $assetService) {}

    public function index(Request $request)
    {
        $query = Asset::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assets = $query->orderBy('asset_code')->paginate(20)->withQueryString();
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();

        return view('pengurus.assets.index', compact('assets', 'categories'));
    }

    public function create()
    {
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();
        $assetCode = $this->assetService->generateAssetCode();

        return view('pengurus.assets.create', compact('categories', 'assetCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['nullable', 'string', 'max:50'],
            'condition' => ['required', 'in:baik,rusak_ringan,rusak_berat,tidak_layak,hilang'],
            'status' => ['required', 'in:tersedia,dipinjam,dalam_perbaikan,tidak_aktif,hilang'],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_source' => ['nullable', 'string', 'max:255'],
            'acquisition_price' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $request->except('photo');
        $data['asset_code'] = $this->assetService->generateAssetCode();
        $data['created_by'] = auth()->id();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('assets', 'public');
        }

        DB::transaction(function () use ($data) {
            $asset = Asset::create($data);
            $this->assetService->recordMovement($asset, 'addition', $data['quantity'], 'Pendaftaran aset baru');
        });

        return redirect()->route('pengurus.assets.index')->with('success', 'Aset berhasil ditambahkan.');
    }

    public function show(Asset $asset)
    {
        $asset->load(['category', 'movements.performer', 'loans.resident']);
        $movements = $asset->movements()->orderBy('created_at', 'desc')->get();

        return view('pengurus.assets.show', compact('asset', 'movements'));
    }

    public function edit(Asset $asset)
    {
        $categories = AssetCategory::where('is_active', true)->orderBy('name')->get();

        return view('pengurus.assets.edit', compact('asset', 'categories'));
    }

    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'category_id' => ['required', 'exists:asset_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'quantity' => ['required', 'integer', 'min:0'],
            'unit' => ['nullable', 'string', 'max:50'],
            'condition' => ['required', 'in:baik,rusak_ringan,rusak_berat,tidak_layak,hilang'],
            'status' => ['required', 'in:tersedia,dipinjam,dalam_perbaikan,tidak_aktif,hilang'],
            'location' => ['nullable', 'string', 'max:255'],
            'acquisition_date' => ['nullable', 'date'],
            'acquisition_source' => ['nullable', 'string', 'max:255'],
            'acquisition_price' => ['nullable', 'numeric', 'min:0'],
            'current_value' => ['nullable', 'numeric', 'min:0'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
        ]);

        $data = $request->except('photo');

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('assets', 'public');
        }

        DB::transaction(function () use ($asset, $data) {
            $oldQuantity = $asset->quantity;
            $newQuantity = $data['quantity'];

            $asset->update($data);

            if ($newQuantity !== $oldQuantity) {
                $this->assetService->recordMovement(
                    $asset,
                    $newQuantity > $oldQuantity ? 'addition' : 'adjustment',
                    abs($newQuantity - $oldQuantity),
                    'Penyesuaian stok dari '.$oldQuantity.' ke '.$newQuantity,
                );
            }

            if (($data['condition'] ?? $asset->condition) !== $asset->getOriginal('condition')) {
                $this->assetService->recordMovement(
                    $asset,
                    'adjustment',
                    0,
                    'Perubahan kondisi dari '.$asset->getOriginal('condition').' ke '.($data['condition'] ?? $asset->condition),
                    $asset->getOriginal('condition'),
                    $data['condition'] ?? $asset->condition,
                );
            }
        });

        return redirect()->route('pengurus.assets.show', $asset)->with('success', 'Aset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        if ($asset->status === 'dipinjam') {
            return redirect()->route('pengurus.assets.index')->with('error', 'Aset tidak bisa dihapus karena sedang dipinjam.');
        }

        $asset->delete();

        return redirect()->route('pengurus.assets.index')->with('success', 'Aset berhasil dihapus.');
    }
}
