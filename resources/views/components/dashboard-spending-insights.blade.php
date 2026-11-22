@props(['insights'])

@php
    $safe = $insights['safe_to_spend'] ?? [];
    $mom = $insights['mom_insights'] ?? [];
    $heatmap = $insights['heatmap'] ?? [];

    $statusBadge = match($safe['status'] ?? 'safe') {
        'over'  => ['label' => 'Over Limit', 'cls' => 'bg-rose-100 text-rose-700 border-rose-200'],
        'tight' => ['label' => 'Waspada / Ketat', 'cls' => 'bg-amber-100 text-amber-700 border-amber-200'],
        default => ['label' => 'Aman Terkendali', 'cls' => 'bg-emerald-100 text-emerald-700 border-emerald-200'],
    };

    $levelClasses = [
        0 => 'bg-gray-100 text-gray-500 border-gray-200',
        1 => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        2 => 'bg-amber-100 text-amber-800 border-amber-200',
        3 => 'bg-orange-200 text-orange-900 border-orange-300',
        4 => 'bg-rose-500 text-white border-rose-600 font-extrabold',
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-12 gap-4 sm:gap-6 mb-6">
    <!-- 1. Daily Safe-to-Spend + MoM Highlights -->
    <div class="lg:col-span-7 flex flex-col gap-4 sm:gap-5">
        <!-- Daily Safe-to-Spend Card -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">Daily Safe-to-Spend</p>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 mt-0.5">Batas Aman Belanja Harian</h3>
                </div>
                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $statusBadge['cls'] }}">
                    {{ $statusBadge['label'] }}
                </span>
            </div>

            <div class="mt-4 flex flex-col sm:flex-row sm:items-end justify-between gap-3 p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100">
                <div>
                    <p class="text-xs text-indigo-700 font-semibold">Maksimal belanja per hari (sisa {{ $safe['days_remaining'] ?? 1 }} hari)</p>
                    <p class="text-2xl sm:text-3xl font-extrabold text-indigo-950 mt-1">
                        Rp {{ number_format($safe['daily_safe'] ?? 0, 0, ',', '.') }}<span class="text-xs font-semibold text-indigo-600"> / hari</span>
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-[11px] text-gray-500">Belanja hari ini</p>
                    <p class="text-sm font-extrabold {{ ($safe['today_over'] ?? false) ? 'text-rose-600' : 'text-gray-800' }}">
                        Rp {{ number_format($safe['spent_today'] ?? 0, 0, ',', '.') }}
                    </p>
                </div>
            </div>

            <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500">
                <span>Basis: <strong class="text-gray-700">{{ $safe['pool_label'] ?? 'Anggaran Bulan Ini' }}</strong></span>
                <span>Sisa dana: <strong class="{{ ($safe['remaining_pool'] ?? 0) < 0 ? 'text-rose-600' : 'text-emerald-700' }}">Rp {{ number_format($safe['remaining_pool'] ?? 0, 0, ',', '.') }}</strong></span>
            </div>

            @if(!empty($safe['payday_cycle']))
                @php $pc = $safe['payday_cycle']; @endphp
                <div class="mt-2.5 pt-2.5 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-[11px]">
                    <span class="text-gray-500">
                        ⏳ <strong>{{ $pc['days_to_payday'] }} hari</strong> menuju Payday (Tgl {{ $pc['payday_day'] }}) • {{ $pc['label'] }}
                    </span>
                    @if(!empty($pc['survival_mode']))
                        <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 font-bold">Mode Hemat Akhir Siklus</span>
                    @endif
                </div>
            @endif
        </div>

        <!-- MoM Spending Insights Card -->
        <div class="bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100 flex-1">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800">Spending Insights (vs {{ $mom['last_month_label'] ?? 'Bulan Lalu' }})</h3>
                    <p class="text-xs text-gray-500">Perubahan pengeluaran per kategori dibanding bulan lalu</p>
                </div>
            </div>

            @if(!empty($mom['top_spike']) || !empty($mom['biggest_saving']))
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mb-3">
                    @if(!empty($mom['top_spike']))
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-100">
                            <p class="text-[10px] font-bold uppercase text-rose-600">Lonjakan Tertinggi</p>
                            <p class="text-xs font-bold text-rose-900 mt-0.5 truncate">{{ $mom['top_spike']['category'] }}</p>
                            <p class="text-[11px] text-rose-700">
                                +Rp {{ number_format($mom['top_spike']['delta_amount'], 0, ',', '.') }}
                                @if($mom['top_spike']['delta_pct'] !== null)
                                    (+{{ $mom['top_spike']['delta_pct'] }}%)
                                @else
                                    (Pos baru)
                                @endif
                            </p>
                        </div>
                    @endif
                    @if(!empty($mom['biggest_saving']))
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                            <p class="text-[10px] font-bold uppercase text-emerald-600">Penghematan Terbesar</p>
                            <p class="text-xs font-bold text-emerald-900 mt-0.5 truncate">{{ $mom['biggest_saving']['category'] }}</p>
                            <p class="text-[11px] text-emerald-700">
                                Turun Rp {{ number_format(abs($mom['biggest_saving']['delta_amount']), 0, ',', '.') }}
                                @if($mom['biggest_saving']['delta_pct'] !== null)
                                    ({{ $mom['biggest_saving']['delta_pct'] }}%)
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            @if(!empty($mom['items']))
                <div class="space-y-2">
                    @foreach(array_slice($mom['items'], 0, 4) as $mItem)
                        <div class="flex items-center justify-between gap-2 text-xs py-1.5 border-b border-gray-50 last:border-0">
                            <span class="font-semibold text-gray-700 truncate">{{ $mItem['category'] }}</span>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="font-bold text-gray-800">Rp {{ number_format($mItem['current_amount'], 0, ',', '.') }}</span>
                                @if($mItem['trend'] === 'up')
                                    <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 text-[10px] font-bold">▲ +{{ $mItem['delta_pct'] }}%</span>
                                @elseif($mItem['trend'] === 'down')
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-700 text-[10px] font-bold">▼ {{ $mItem['delta_pct'] }}%</span>
                                @elseif($mItem['trend'] === 'new')
                                    <span class="px-1.5 py-0.5 rounded bg-indigo-50 text-indigo-700 text-[10px] font-bold">Baru</span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded bg-gray-100 text-gray-500 text-[10px] font-bold">0%</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-xs text-gray-400 text-center py-4">Catat pengeluaran bulan ini untuk melihat perbandingan otomatis vs bulan lalu.</p>
            @endif
        </div>
    </div>

    <!-- 2. Spending Heatmap & No-Spend Day Tracker -->
    <div class="lg:col-span-5 bg-white rounded-2xl shadow-lg p-4 sm:p-6 border border-gray-100 flex flex-col justify-between">
        <div>
            <div class="flex items-start justify-between gap-2 mb-3">
                <div>
                    <h3 class="text-base sm:text-lg font-bold text-gray-800">Kalender Pengeluaran</h3>
                    <p class="text-xs text-gray-500">{{ $heatmap['month_label'] ?? '' }} • Heatmap Harian</p>
                </div>
                <div class="text-right">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[11px] font-bold">
                        🌿 {{ $heatmap['no_spend_days'] ?? 0 }} No-Spend Day
                    </span>
                    @if(($heatmap['no_spend_streak'] ?? 0) >= 2)
                        <p class="text-[10px] font-bold text-emerald-600 mt-1">🔥 Streak {{ $heatmap['no_spend_streak'] }} hari hemat!</p>
                    @endif
                </div>
            </div>

            <!-- Weekday Headers -->
            <div class="grid grid-cols-7 gap-1.5 text-center text-[10px] font-bold text-gray-400 mb-1.5">
                <span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span>
            </div>

            <!-- Calendar Cells -->
            <div class="grid grid-cols-7 gap-1.5">
                @for($blank = 0; $blank < ($heatmap['first_weekday_offset'] ?? 0); $blank++)
                    <div class="h-9"></div>
                @endfor

                @foreach(($heatmap['days'] ?? []) as $dayCell)
                    @php
                        $cellCls = $dayCell['is_future']
                            ? 'bg-gray-50/50 text-gray-300 border-dashed border-gray-100'
                            : ($levelClasses[$dayCell['level']] ?? $levelClasses[0]);
                        $todayRing = $dayCell['is_today'] ? 'ring-2 ring-indigo-500 ring-offset-1' : '';
                        $tooltip = $dayCell['is_future']
                            ? "Tgl {$dayCell['day']}: Belum tiba"
                            : "Tgl {$dayCell['day']}: Rp " . number_format($dayCell['amount'], 0, ',', '.');
                    @endphp
                    <div title="{{ $tooltip }}"
                         class="h-9 rounded-lg border text-xs flex flex-col items-center justify-center transition {{ $cellCls }} {{ $todayRing }}">
                        <span class="leading-none font-semibold">{{ $dayCell['day'] }}</span>
                        @if(!$dayCell['is_future'] && $dayCell['amount'] <= 0)
                            <span class="w-1 h-1 rounded-full bg-emerald-500 mt-0.5"></span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2 text-[11px] text-gray-500">
            <div class="flex items-center gap-1.5">
                <span>Hemat</span>
                <span class="w-3 h-3 rounded bg-gray-100 border border-gray-200 inline-block"></span>
                <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-200 inline-block"></span>
                <span class="w-3 h-3 rounded bg-amber-100 border border-amber-200 inline-block"></span>
                <span class="w-3 h-3 rounded bg-orange-200 border border-orange-300 inline-block"></span>
                <span class="w-3 h-3 rounded bg-rose-500 inline-block"></span>
                <span>Boros</span>
            </div>
            @if(!empty($heatmap['max_day']))
                <span>Puncak: <strong class="text-rose-600">Tgl {{ $heatmap['max_day']['day'] }} (Rp {{ number_format($heatmap['max_day']['amount'], 0, ',', '.') }})</strong></span>
            @endif
        </div>
    </div>
</div>
