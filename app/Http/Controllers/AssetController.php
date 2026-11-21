<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Debt;
use App\Models\Saving;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(): View
    {
        $userId = Auth::id();

        $assets = Asset::where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        $totalAssetValue = (float) $assets->sum('current_value');
        $totalAssetCost = (float) $assets->sum('total_cost');
        $totalUnrealizedGain = $totalAssetValue - $totalAssetCost;
        $totalGainPercentage = $totalAssetCost > 0
            ? round(($totalUnrealizedGain / $totalAssetCost) * 100, 2)
            : 0.0;

        $totalSavingsBalance = (float) Saving::where('user_id', $userId)->sum('current_balance');
        $totalPayableDebt = (float) Debt::where('user_id', $userId)
            ->where('type', Debt::TYPE_PAYABLE)
            ->where('status', '!=', Debt::STATUS_PAID)
            ->get()
            ->sum('remaining_amount');

        $netWorth = $totalSavingsBalance + $totalAssetValue - $totalPayableDebt;
        $assetTypes = Asset::TYPES;

        return view('asset.index', compact(
            'assets',
            'assetTypes',
            'totalAssetValue',
            'totalAssetCost',
            'totalUnrealizedGain',
            'totalGainPercentage',
            'totalSavingsBalance',
            'totalPayableDebt',
            'netWorth'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                   => 'required|string|max:150',
            'asset_type'             => 'required|in:' . implode(',', array_keys(Asset::TYPES)),
            'unit_label'             => 'required|string|max:30',
            'quantity'               => 'required|numeric|min:0.0001',
            'buy_price_per_unit'     => 'required|numeric|min:0',
            'current_price_per_unit' => 'required|numeric|min:0',
            'notes'                  => 'nullable|string|max:255',
        ]);

        Asset::create([
            'user_id'                => Auth::id(),
            'name'                   => trim($validated['name']),
            'asset_type'             => $validated['asset_type'],
            'unit_label'             => trim($validated['unit_label']),
            'quantity'               => $validated['quantity'],
            'buy_price_per_unit'     => $validated['buy_price_per_unit'],
            'current_price_per_unit' => $validated['current_price_per_unit'],
            'notes'                  => !empty($validated['notes']) ? trim($validated['notes']) : null,
            'last_valued_at'         => now(),
        ]);

        return redirect()->route('asset.index')->with('success', 'Aset & investasi berhasil ditambahkan!');
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless((int) $asset->user_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'name'                   => 'required|string|max:150',
            'asset_type'             => 'required|in:' . implode(',', array_keys(Asset::TYPES)),
            'unit_label'             => 'required|string|max:30',
            'quantity'               => 'required|numeric|min:0.0001',
            'buy_price_per_unit'     => 'required|numeric|min:0',
            'current_price_per_unit' => 'required|numeric|min:0',
            'notes'                  => 'nullable|string|max:255',
        ]);

        $asset->update([
            'name'                   => trim($validated['name']),
            'asset_type'             => $validated['asset_type'],
            'unit_label'             => trim($validated['unit_label']),
            'quantity'               => $validated['quantity'],
            'buy_price_per_unit'     => $validated['buy_price_per_unit'],
            'current_price_per_unit' => $validated['current_price_per_unit'],
            'notes'                  => !empty($validated['notes']) ? trim($validated['notes']) : null,
            'last_valued_at'         => now(),
        ]);

        return redirect()->route('asset.index')->with('success', 'Data aset berhasil diperbarui!');
    }

    public function updatePrice(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless((int) $asset->user_id === (int) Auth::id(), 403);

        $validated = $request->validate([
            'current_price_per_unit' => 'required|numeric|min:0',
        ]);

        $asset->update([
            'current_price_per_unit' => $validated['current_price_per_unit'],
            'last_valued_at'         => now(),
        ]);

        return redirect()->route('asset.index')->with('success', "Harga pasar terkini untuk \"{$asset->name}\" berhasil diperbarui!");
    }

    public function destroy(Asset $asset): RedirectResponse
    {
        abort_unless((int) $asset->user_id === (int) Auth::id(), 403);

        $asset->delete();

        return redirect()->route('asset.index')->with('success', 'Aset berhasil dihapus.');
    }
}
