<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaydayController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payday_day'            => 'required|integer|min:1|max:31',
            'payday_weekend_adjust' => 'nullable|boolean',
        ]);

        $request->user()->update([
            'payday_day'            => (int) $validated['payday_day'],
            'payday_weekend_adjust' => (bool) ($validated['payday_weekend_adjust'] ?? false),
        ]);

        return redirect()->back()->with(
            'payday_status',
            "Tanggal gajian diperbarui ke setiap tanggal {$validated['payday_day']}."
        );
    }
}
