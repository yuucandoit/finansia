<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🛡️ Admin SaaS & Kuota AI</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Pantau pertumbuhan pengguna, konversi paket Pro, estimasi MRR, dan pemakaian API AI</p>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-800 text-sm font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- KPI SaaS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Total Pengguna</p>
                <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalUsers }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Pelanggan Pro Aktif</p>
                <p class="text-2xl font-extrabold text-indigo-600 mt-1">{{ $activeProUsers }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Estimasi MRR</p>
                <p class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($estimatedMrr, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Request AI (Bulan Ini)</p>
                <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ $aiRequestsThisMonth }}</p>
            </div>
        </div>

        <!-- Tabel User & Kontrol Tier -->
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-base font-bold text-gray-800">Daftar Pengguna & Kuota AI</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-[10px]">
                        <tr>
                            <th class="px-5 py-3">Pengguna</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Paket</th>
                            <th class="px-5 py-3 text-right">AI Bulan Ini</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($users as $u)
                            <tr>
                                <td class="px-5 py-3">
                                    <p class="font-bold text-gray-800">{{ $u->name }}</p>
                                    <p class="text-[11px] text-gray-400">{{ $u->email }}</p>
                                </td>
                                <td class="px-5 py-3 font-semibold text-gray-600">{{ $u->role ?? 'user' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase {{ $u->isPro() ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ $u->isPro() ? 'Pro' : 'Free' }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right font-bold text-gray-700">{{ $u->monthly_ai_count ?? 0 }} req</td>
                                <td class="px-5 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.saas.users.toggle-tier', $u) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1.5 rounded-lg text-[11px] font-bold border transition {{ $u->plan_tier === 'pro' ? 'bg-gray-100 text-gray-700 border-gray-200 hover:bg-gray-200' : 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700' }}">
                                            {{ $u->plan_tier === 'pro' ? 'Turunkan ke Free' : 'Berikan Pro' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $users->links() }}
            </div>
        </div>
    </main>
</x-app-layout>
