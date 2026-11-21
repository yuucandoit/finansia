<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">🧮 Simulasi Proyeksi Tabungan (What-If)</h1>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">Hitung estimasi waktu tercapainya target Future Plan atau Dana Darurat berdasarkan kemampuan menabung bulanan Anda</p>
            </div>
        </div>

        <!-- Ringkasan Baseline Keuangan Anda -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Total Saldo Saat Ini</p>
                <p class="text-xl font-extrabold text-gray-800 mt-1">Rp {{ number_format($totalBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Rata-rata Pengeluaran / Bulan (3 bln)</p>
                <p class="text-xl font-extrabold text-rose-600 mt-1">Rp {{ number_format($avgMonthlyExpense, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-2xs">
                <p class="text-xs font-semibold text-gray-400">Saran Tabungan / Bulan</p>
                <p class="text-xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($suggestedMonthlySaving, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Panel Input Kalkulator -->
            <div class="lg:col-span-5 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-4">
                <div class="flex items-center gap-2 border-b border-gray-100 pb-3">
                    <button type="button" onclick="setSimMode('target')" id="sim-tab-target"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-indigo-600 text-white transition">
                        🎯 Target / Future Plan
                    </button>
                    <button type="button" onclick="setSimMode('emergency')" id="sim-tab-emergency"
                            class="flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                        🛡️ Dana Darurat
                    </button>
                </div>

                <!-- Preset Future Plan -->
                <div id="sim-plan-picker" class="space-y-1">
                    <label class="block text-xs font-bold text-gray-600">Ambil Target dari Future Plan (Opsional)</label>
                    <select id="sim-future-plan" onchange="applyPlanPreset(this)" class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 bg-white">
                        <option value="">-- Input Target Manual --</option>
                        @foreach($futurePlans as $fp)
                            <option value="{{ $fp['id'] }}"
                                    data-target="{{ $fp['remaining_amount'] > 0 ? $fp['remaining_amount'] : $fp['target_amount'] }}"
                                    data-current="{{ $fp['linked_balance'] }}">
                                {{ $fp['title'] }} (Sisa: Rp {{ number_format($fp['remaining_amount'], 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Preset Kelipatan Dana Darurat -->
                <div id="sim-emergency-picker" class="hidden space-y-1">
                    <label class="block text-xs font-bold text-gray-600">Target Kelipatan Pengeluaran Bulanan</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button type="button" onclick="setEmergencyMultiplier(3)" class="py-2 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100">3x Bulan</button>
                        <button type="button" onclick="setEmergencyMultiplier(6)" class="py-2 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100">6x Bulan</button>
                        <button type="button" onclick="setEmergencyMultiplier(12)" class="py-2 rounded-xl text-xs font-bold bg-indigo-50 text-indigo-700 hover:bg-indigo-100">12x Bulan</button>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Nominal Target (Rp)</label>
                    <input type="number" id="sim-target" value="50000000" min="100000" step="100000" oninput="runSimulation()"
                           class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Dana Awal Saat Ini (Rp)</label>
                    <input type="number" id="sim-current" value="{{ (int) $totalBalance }}" min="0" step="100000" oninput="runSimulation()"
                           class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Setoran Rutin / Bulan (Rp)</label>
                    <input type="number" id="sim-monthly" value="{{ (int) $suggestedMonthlySaving }}" min="10000" step="50000" oninput="runSimulation()"
                           class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-600 mb-1">Estimasi Imbal Hasil / Bunga Tahunan (%)</label>
                    <input type="number" id="sim-rate" value="4" min="0" max="50" step="0.5" oninput="runSimulation()"
                           class="w-full px-3.5 py-2.5 text-sm font-bold rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500">
                    <p class="text-[11px] text-gray-400 mt-1">Misal: 0% (tabungan biasa), 4–6% (RDPU / deposito).</p>
                </div>
            </div>

            <!-- Panel Hasil Proyeksi -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-gray-100 shadow-sm p-5 sm:p-6 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100">
                        <p class="text-[11px] font-bold text-indigo-600 uppercase">Estimasi Waktu</p>
                        <p id="res-months" class="text-2xl font-extrabold text-indigo-900 mt-1">-</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                        <p class="text-[11px] font-bold text-emerald-600 uppercase">Target Tercapai</p>
                        <p id="res-date" class="text-lg font-extrabold text-emerald-900 mt-1">-</p>
                    </div>
                    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100">
                        <p class="text-[11px] font-bold text-amber-600 uppercase">Total Bunga / Return</p>
                        <p id="res-interest" class="text-lg font-extrabold text-amber-900 mt-1">Rp 0</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-xs font-bold text-gray-700 mb-2">Milestone Proyeksi Saldo per Periode</h3>
                    <div class="max-h-72 overflow-y-auto border border-gray-100 rounded-2xl">
                        <table class="w-full text-xs text-left">
                            <thead class="bg-gray-50 text-gray-500 uppercase text-[10px] sticky top-0">
                                <tr>
                                    <th class="px-4 py-2.5">Bulan Ke-</th>
                                    <th class="px-4 py-2.5">Periode</th>
                                    <th class="px-4 py-2.5 text-right">Saldo Proyeksi</th>
                                    <th class="px-4 py-2.5 text-right">Progres</th>
                                </tr>
                            </thead>
                            <tbody id="sim-timeline-body" class="divide-y divide-gray-100"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        const BASE_AVG_EXPENSE = {{ (float) $avgMonthlyExpense }};

        function setSimMode(mode) {
            const tabTarget = document.getElementById('sim-tab-target');
            const tabEmerg = document.getElementById('sim-tab-emergency');
            const planPicker = document.getElementById('sim-plan-picker');
            const emergPicker = document.getElementById('sim-emergency-picker');

            if (mode === 'emergency') {
                tabEmerg.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-indigo-600 text-white transition';
                tabTarget.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition';
                planPicker.classList.add('hidden');
                emergPicker.classList.remove('hidden');
                setEmergencyMultiplier(6);
            } else {
                tabTarget.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-indigo-600 text-white transition';
                tabEmerg.className = 'flex-1 py-2 px-3 rounded-xl text-xs font-bold bg-gray-100 text-gray-600 hover:bg-gray-200 transition';
                planPicker.classList.remove('hidden');
                emergPicker.classList.add('hidden');
            }
        }

        function setEmergencyMultiplier(mult) {
            const base = BASE_AVG_EXPENSE > 0 ? BASE_AVG_EXPENSE : 3000000;
            document.getElementById('sim-target').value = Math.round(base * mult);
            runSimulation();
        }

        function applyPlanPreset(selectEl) {
            const opt = selectEl.options[selectEl.selectedIndex];
            if (!opt || !opt.value) return;
            const target = parseFloat(opt.getAttribute('data-target') || '0');
            const current = parseFloat(opt.getAttribute('data-current') || '0');
            if (target > 0) document.getElementById('sim-target').value = Math.round(target);
            document.getElementById('sim-current').value = Math.round(current);
            runSimulation();
        }

        function runSimulation() {
            const target = parseFloat(document.getElementById('sim-target').value || '0');
            let current = parseFloat(document.getElementById('sim-current').value || '0');
            const monthly = parseFloat(document.getElementById('sim-monthly').value || '0');
            const annualRate = parseFloat(document.getElementById('sim-rate').value || '0');

            const resMonths = document.getElementById('res-months');
            const resDate = document.getElementById('res-date');
            const resInterest = document.getElementById('res-interest');
            const tbody = document.getElementById('sim-timeline-body');

            if (target <= 0 || (monthly <= 0 && current < target)) {
                resMonths.textContent = '∞';
                resDate.textContent = 'Isi setoran bulanan';
                resInterest.textContent = 'Rp 0';
                tbody.innerHTML = '';
                return;
            }

            const monthlyRate = (annualRate / 100) / 12;
            let months = 0;
            let totalInterest = 0;
            const rows = [];
            const now = new Date();
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

            while (current < target && months < 360) {
                months++;
                const interest = current * monthlyRate;
                totalInterest += interest;
                current = current + interest + monthly;

                const d = new Date(now.getFullYear(), now.getMonth() + months, 1);
                const label = `${monthNames[d.getMonth()]} ${d.getFullYear()}`;
                const pct = Math.min(100, (current / target) * 100).toFixed(1);

                if (months <= 12 || months % 3 === 0 || current >= target) {
                    rows.push(`<tr>
                        <td class="px-4 py-2 font-bold text-gray-700">Bulan ${months}</td>
                        <td class="px-4 py-2 text-gray-500">${label}</td>
                        <td class="px-4 py-2 text-right font-bold text-gray-800">Rp ${Math.round(current).toLocaleString('id-ID')}</td>
                        <td class="px-4 py-2 text-right font-semibold text-indigo-600">${pct}%</td>
                    </tr>`);
                }
            }

            if (months === 0) {
                resMonths.textContent = '0 Bulan (Tercapai!)';
                resDate.textContent = 'Sekarang';
            } else if (months >= 360 && current < target) {
                resMonths.textContent = '> 30 Tahun';
                resDate.textContent = 'Perlu naikkan tabungan';
            } else {
                resMonths.textContent = `${months} Bulan`;
                const finish = new Date(now.getFullYear(), now.getMonth() + months, 1);
                resDate.textContent = `${monthNames[finish.getMonth()]} ${finish.getFullYear()}`;
            }

            resInterest.textContent = 'Rp ' + Math.round(totalInterest).toLocaleString('id-ID');
            tbody.innerHTML = rows.join('');
        }

        document.addEventListener('DOMContentLoaded', runSimulation);
    </script>
</x-app-layout>
