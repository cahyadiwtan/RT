<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\AssetMovement;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function generateAssetCode(): string
    {
        $last = Asset::orderBy('id', 'desc')->value('asset_code');

        if ($last && preg_match('/AST-(\d{4})/', $last, $m)) {
            $next = (int) $m[1] + 1;
        } else {
            $next = 1;
        }

        return 'AST-'.str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function recordMovement(Asset $asset, string $type, int $quantity, ?string $notes = null, ?string $conditionBefore = null, ?string $conditionAfter = null, ?string $fromLocation = null, ?string $toLocation = null): AssetMovement
    {
        return AssetMovement::create([
            'asset_id' => $asset->id,
            'type' => $type,
            'quantity' => $quantity,
            'from_location' => $fromLocation ?? $asset->location,
            'to_location' => $toLocation,
            'condition_before' => $conditionBefore,
            'condition_after' => $conditionAfter,
            'notes' => $notes,
            'performed_by' => auth()->id(),
        ]);
    }

    public function adjustStock(Asset $asset, int $newQuantity, ?string $notes = null): void
    {
        $difference = $newQuantity - $asset->quantity;

        DB::transaction(function () use ($asset, $newQuantity, $difference, $notes) {
            $asset->update(['quantity' => $newQuantity]);

            $this->recordMovement(
                $asset,
                $difference > 0 ? 'addition' : 'adjustment',
                abs($difference),
                $notes ?? 'Penyesuaian stok dari '.$asset->quantity.' ke '.$newQuantity,
            );
        });
    }

    public function getAssetReport(): array
    {
        $totalAssets = Asset::count();
        $totalValue = (float) Asset::sum('current_value');
        $totalBorrowed = Asset::where('status', 'dipinjam')->count();
        $totalDamaged = Asset::whereIn('condition', ['rusak_ringan', 'rusak_berat', 'tidak_layak'])->count();

        return [
            'total_assets' => $totalAssets,
            'total_value' => $totalValue,
            'total_borrowed' => $totalBorrowed,
            'total_damaged' => $totalDamaged,
        ];
    }
}
