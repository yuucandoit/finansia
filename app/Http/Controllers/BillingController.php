<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function pricing(): View
    {
        $user = Auth::user();
        $plans = config('saas.plans');
        $subscriptions = $user
            ? $user->subscriptions()->latest('id')->limit(5)->get()
            : collect();

        return view('billing.pricing', compact('user', 'plans', 'subscriptions'));
    }

    public function checkout(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $user = $request->user();
        $cycle = $validated['billing_cycle'];
        $amount = $cycle === 'yearly'
            ? (float) config('saas.plans.pro.price_yearly', 390000)
            : (float) config('saas.plans.pro.price_monthly', 39000);

        $orderId = 'FIN-PRO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));
        $instantActivate = (bool) config('saas.sandbox_instant_activate', true);
        $expiresAt = $cycle === 'yearly' ? now()->addYear() : now()->addMonth();

        $sub = Subscription::create([
            'user_id'          => $user->id,
            'plan_tier'        => 'pro',
            'billing_cycle'    => $cycle,
            'amount'           => $amount,
            'status'           => $instantActivate ? 'active' : 'pending',
            'order_id'         => $orderId,
            'payment_provider' => 'sandbox',
            'paid_at'          => $instantActivate ? now() : null,
            'expires_at'       => $instantActivate ? $expiresAt : null,
        ]);

        if ($instantActivate) {
            $user->update([
                'plan_tier'       => 'pro',
                'plan_expires_at' => $expiresAt,
            ]);

            return redirect()->route('pricing.index')
                ->with('success', "Selamat! Paket Finansia Pro ({$cycle}) aktif hingga {$expiresAt->format('d M Y')} (Order: {$sub->order_id}).");
        }

        return redirect()->route('pricing.index')
            ->with('success', "Order {$sub->order_id} berhasil dibuat. Menunggu konfirmasi pembayaran.");
    }

    /**
     * Webhook pembayaran untuk aktivasi otomatis paket Pro (kompatibel Midtrans/Xendit/custom gateway).
     */
    public function webhook(Request $request): JsonResponse
    {
        $expectedSecret = config('saas.billing_webhook_secret');
        if (!empty($expectedSecret)) {
            $provided = (string) $request->header('X-Billing-Webhook-Secret', '');
            if (!hash_equals($expectedSecret, $provided)) {
                return response()->json(['status' => 'forbidden'], 403);
            }
        }

        $validated = $request->validate([
            'order_id' => 'required|string',
            'status'   => 'required|in:paid,settlement,cancelled,expired',
        ]);

        $sub = Subscription::with('user')->where('order_id', $validated['order_id'])->first();
        if (!$sub) {
            return response()->json(['status' => 'not_found'], 404);
        }

        if (in_array($validated['status'], ['paid', 'settlement'], true)) {
            $expiresAt = $sub->billing_cycle === 'yearly' ? now()->addYear() : now()->addMonth();
            $sub->update([
                'status'     => 'active',
                'paid_at'    => now(),
                'expires_at' => $expiresAt,
            ]);

            $sub->user->update([
                'plan_tier'       => 'pro',
                'plan_expires_at' => $expiresAt,
            ]);
        } else {
            $sub->update(['status' => $validated['status']]);
        }

        return response()->json([
            'status'   => 'ok',
            'order_id' => $sub->order_id,
            'sub_status' => $sub->fresh()->status,
        ]);
    }
}
