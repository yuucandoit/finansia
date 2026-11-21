<x-app-layout>
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <div class="text-center max-w-2xl mx-auto space-y-2">
            <span class="inline-flex items-center px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-bold">
                Finansia Plans & Billing
            </span>
            <h1 class="text-3xl font-extrabold text-gray-900">Pilih Paket Sesuai Kebutuhan Keuangan Anda</h1>
            <p class="text-sm text-gray-500">Mulai gratis untuk pencatatan harian, atau upgrade ke Pro untuk AI Copilot tanpa batas, Telegram Bot, & kolaborasi penuh.</p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-semibold text-center">
                {{ session('success') }}
            </div>
        @endif

        @if($user)
            <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5 shadow-2xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <p class="text-xs text-gray-500">Status Paket Anda Saat Ini</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-base font-extrabold text-gray-900">{{ $user->isPro() ? 'Finansia Pro' : 'Starter (Gratis)' }}</span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $user->isPro() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $user->isPro() ? 'Aktif' : 'Free Tier' }}
                        </span>
                    </div>
                    @if($user->plan_expires_at && $user->isPro())
                        <p class="text-xs text-gray-500 mt-0.5">Berlaku hingga {{ $user->plan_expires_at->format('d M Y') }}</p>
                    @endif
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs text-gray-500">Pemakaian AI Bulan Ini</p>
                    <p class="text-sm font-extrabold text-indigo-700">
                        {{ $user->monthlyAiUsageCount() }} / {{ $user->isPro() ? 'Unlimited' : ($plans['free']['monthly_ai_limit'] ?? 30) }} request
                    </p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Free Plan -->
            <div class="bg-white rounded-3xl border border-gray-200 p-6 sm:p-8 flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900">Starter (Gratis)</h2>
                    <p class="text-3xl font-extrabold text-gray-900">Rp 0 <span class="text-sm font-medium text-gray-400">/ selamanya</span></p>
                    <ul class="space-y-2.5 text-sm text-gray-600">
                        <li>✓ Pencatatan transaksi, anggaran & rekonsiliasi saldo</li>
                        <li>✓ Hingga {{ $plans['free']['max_savings'] ?? 5 }} rekening & {{ $plans['free']['max_future_plans'] ?? 3 }} Future Plan</li>
                        <li>✓ {{ $plans['free']['monthly_ai_limit'] ?? 30 }} permintaan AI Advisor / bulan</li>
                        <li>✓ Daily Safe-to-Spend & Kalender Pengeluaran</li>
                        <li>✓ Backup & Restore JSON pribadi</li>
                    </ul>
                </div>
                <div class="pt-4 border-t border-gray-100">
                    <span class="block w-full text-center py-2.5 rounded-xl bg-gray-100 text-gray-600 text-xs font-bold uppercase">
                        {{ $user && !$user->isPro() ? 'Paket Aktif Anda' : 'Paket Dasar' }}
                    </span>
                </div>
            </div>

            <!-- Pro Plan -->
            <div class="bg-gradient-to-br from-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-xl flex flex-col justify-between space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold">Finansia Pro</h2>
                        <span class="px-2.5 py-1 rounded-full bg-amber-400 text-slate-900 text-[10px] font-extrabold uppercase">Paling Populer</span>
                    </div>
                    <div>
                        <p class="text-3xl font-extrabold">Rp {{ number_format($plans['pro']['price_monthly'] ?? 39000, 0, ',', '.') }} <span class="text-sm font-normal text-indigo-200">/ bulan</span></p>
                        <p class="text-xs text-indigo-300 mt-1">Atau hemat 2 bulan: Rp {{ number_format($plans['pro']['price_yearly'] ?? 390000, 0, ',', '.') }} / tahun</p>
                    </div>
                    <ul class="space-y-2.5 text-sm text-indigo-100">
                        <li>✓ <strong>Unlimited</strong> AI Financial Advisor, OCR Struk & AI Future Plan</li>
                        <li>✓ <strong>Unlimited</strong> Rekening, Future Plan & Dompet Bersama</li>
                        <li>✓ Telegram Bot Copilot (Catat via Chat & Pengingat Harian)</li>
                        <li>✓ Export Laporan Excel & Print PDF Profesional</li>
                        <li>✓ Smart Rules & Split Transaksi tanpa batas</li>
                    </ul>
                </div>

                <div class="pt-4 border-t border-white/10 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                    <form method="POST" action="{{ route('billing.checkout') }}">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="monthly">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-white text-indigo-900 hover:bg-indigo-50 text-xs font-extrabold uppercase transition">
                            Upgrade Bulanan
                        </button>
                    </form>
                    <form method="POST" action="{{ route('billing.checkout') }}">
                        @csrf
                        <input type="hidden" name="billing_cycle" value="yearly">
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-400 text-slate-900 hover:bg-amber-300 text-xs font-extrabold uppercase transition">
                            Upgrade Tahunan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        @if($subscriptions->isNotEmpty())
            <div class="bg-white rounded-3xl border border-gray-100 p-5 sm:p-6 shadow-2xs space-y-3">
                <h3 class="text-sm font-bold text-gray-800">Riwayat Langganan Anda</h3>
                <div class="space-y-2">
                    @foreach($subscriptions as $sub)
                        <div class="flex flex-wrap items-center justify-between gap-2 text-xs p-3 rounded-xl bg-gray-50 border border-gray-100">
                            <div>
                                <span class="font-mono font-bold text-gray-800">{{ $sub->order_id }}</span>
                                <span class="text-gray-500 ml-2">Pro ({{ $sub->billing_cycle }})</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-800">Rp {{ number_format((float) $sub->amount, 0, ',', '.') }}</span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $sub->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $sub->status }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </main>
</x-app-layout>
