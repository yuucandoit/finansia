<x-app-layout>
    <div class="py-6 bg-gradient-to-br from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Header Section - Total Balance -->
            <div class="mb-6">
                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl shadow-2xl p-8 text-white relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute top-0 right-0 w-64 h-64 bg-white opacity-5 rounded-full -mr-32 -mt-32"></div>
                    <div class="absolute bottom-0 left-0 w-48 h-48 bg-white opacity-5 rounded-full -ml-24 -mb-24"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <p class="text-blue-100 text-sm font-medium mb-2">Total Saldo</p>
                                <h2 class="text-4xl font-extrabold tracking-tight flex items-center ">
                                    <span class="balance-amount">
                                         Rp ••••••••
                                    </span>
                                    <span class="showing-btn w-6 h-6 ml-3 text-white/70 cursor-pointer" title="Tampilkan/Sembunyikan Saldo">
                                        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M2.42012 12.7132C2.28394 12.4975 2.21584 12.3897 2.17772 12.2234C2.14909 12.0985 2.14909 11.9015 2.17772 11.7766C2.21584 11.6103 2.28394 11.5025 2.42012 11.2868C3.54553 9.50484 6.8954 5 12.0004 5C17.1054 5 20.4553 9.50484 21.5807 11.2868C21.7169 11.5025 21.785 11.6103 21.8231 11.7766C21.8517 11.9015 21.8517 12.0985 21.8231 12.2234C21.785 12.3897 21.7169 12.4975 21.5807 12.7132C20.4553 14.4952 17.1054 19 12.0004 19C6.8954 19 3.54553 14.4952 2.42012 12.7132Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            <path d="M12.0004 15C13.6573 15 15.0004 13.6569 15.0004 12C15.0004 10.3431 13.6573 9 12.0004 9C10.3435 9 9.0004 10.3431 9.0004 12C9.0004 13.6569 10.3435 15 12.0004 15Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </h2>
                            </div>
                            <button class="bg-white/20 backdrop-blur-sm hover:bg-white/30 border border-white/30 text-white py-2.5 px-5 rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg hover:shadow-xl">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 inline mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                Refresh
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mt-6">
                            <!-- Cash In Card -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-green-400/30 rounded-xl flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-green-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-blue-100 text-xs font-medium">Pemasukan Bulan Ini</p>
                                        <p class="text-white font-bold text-lg mt-0.5">Rp {{ number_format($cashInThisMonth ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Cash Out Card -->
                            <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-4 border border-white/20">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-red-400/30 rounded-xl flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-red-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-blue-100 text-xs font-medium">Pengeluaran Bulan Ini</p>
                                        <p class="text-white font-bold text-lg mt-0.5">Rp {{ number_format($cashOutThisMonth ?? 0, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                <!-- Cash Flow Chart (Bar) -->
                <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Cash Flow</h3>
                            <p class="text-sm text-gray-500">7 Hari Terakhir</p>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded"></div>
                                <span class="text-xs font-medium text-gray-600">Cash In</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-red-500 rounded"></div>
                                <span class="text-xs font-medium text-gray-600">Cash Out</span>
                            </div>
                        </div>
                    </div>
                    <div class="h-72">
                        <canvas id="cashFlowChart"></canvas>
                    </div>
                </div>

                <!-- Expense Categories Chart (Donut) -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-bold text-gray-800">Kategori Pengeluaran</h3>
                        <p class="text-sm text-gray-500">Top 5 Bulan Ini</p>
                    </div>
                    <div class="h-72 flex items-center justify-center">
                        <canvas id="expenseDonutChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Balance Trend Chart (Line) -->
            <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Trend Saldo</h3>
                        <p class="text-sm text-gray-500">30 Hari Terakhir</p>
                    </div>
                    <span class="text-xs bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg font-semibold">
                        @if(count($balanceTrend) >= 2)
                            @php
                                $trend = end($balanceTrend) - reset($balanceTrend);
                                $isPositive = $trend >= 0;
                            @endphp
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 inline {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                                @if($isPositive)
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                                @endif
                            </svg>
                            <span class="{{ $isPositive ? 'text-green-600' : 'text-red-600' }}">
                                {{ $isPositive ? '+' : '' }}{{ number_format(abs($trend), 0, ',', '.') }}
                            </span>
                        @endif
                    </span>
                </div>
                <div class="h-80">
                    <canvas id="balanceTrendChart"></canvas>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-100">
                    <h3 class="text-lg font-bold text-gray-800">Transaksi Terakhir</h3>
                    <a href="{{ route('transaction.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-semibold hover:underline">
                        Lihat Semua →
                    </a>
                </div>
                
                <div class="divide-y divide-gray-100">
                    @forelse($recentTransactions as $transaction)
                        @php
                            $isCashIn = $transaction->type == 'Cash In';
                        @endphp
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-4 flex-1 min-w-0">
                                    <div class="flex-shrink-0 w-12 h-12 {{ $isCashIn ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 {{ $isCashIn ? 'text-green-600' : 'text-red-600' }}">
                                            @if($isCashIn)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
                                            @endif
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-bold text-gray-800 truncate">{{ $transaction->description }}</h4>
                                        <p class="text-xs text-gray-500">
                                            {{ $transaction->type }} • {{ $transaction->created_at->format('d M Y, H:i') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 ml-4">
                                    <p class="font-bold text-lg {{ $isCashIn ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $isCashIn ? '+' : '-' }} Rp {{ number_format($transaction->amount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-16 h-16 mx-auto mb-3 opacity-50">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                            </svg>
                            <p class="font-medium">Belum ada transaksi</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // On load js
        document.addEventListener("DOMContentLoaded", function() {
             // function showing eye toggle
            document.querySelectorAll('.showing-btn').forEach(function(button) {
                button.addEventListener('click', function() {
                    const balanceAmount = this.previousElementSibling;
                    if (balanceAmount.textContent.includes('••••••••')) {
                            // Show the balance
                        balanceAmount.textContent = 'Rp {{ number_format($totalBalance ?? 0, 0, ',', '.') }}';
                    } else {
                        // Hide the balance
                        balanceAmount.textContent = 'Rp ••••••••';
                    }
                });
            });
        });

        // Helper function
        function formatRupiah(number) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(number);
        }

        // 1. Cash Flow Bar Chart
        const cashFlowCtx = document.getElementById('cashFlowChart').getContext('2d');
        new Chart(cashFlowCtx, {
            type: 'bar',
            data: {
                labels: @json($last7Days),
                datasets: [
                    {
                        label: 'Cash In',
                        data: @json($cashInData),
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderRadius: 8,
                        borderSkipped: false,
                    },
                    {
                        label: 'Cash Out',
                        data: @json($cashOutData),
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderRadius: 8,
                        borderSkipped: false,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000) + 'K';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 } }
                    }
                }
            }
        });

        // 2. Expense Donut Chart
        const expenseCtx = document.getElementById('expenseDonutChart').getContext('2d');
        const expenseLabels = @json($expenseLabels);
        const expenseData = @json($expenseData);
        
        new Chart(expenseCtx, {
            type: 'doughnut',
            data: {
                labels: expenseLabels.length > 0 ? expenseLabels : ['Belum ada data'],
                datasets: [{
                    data: expenseData.length > 0 ? expenseData : [1],
                    backgroundColor: [
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(250, 204, 21, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(59, 130, 246, 0.8)'
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { size: 11 },
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        callbacks: {
                            label: function(context) {
                                if (expenseData.length === 0) return 'Belum ada data';
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.raw / total) * 100).toFixed(1);
                                return context.label + ': ' + formatRupiah(context.raw) + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // 3. Balance Trend Line Chart
        const balanceCtx = document.getElementById('balanceTrendChart').getContext('2d');
        new Chart(balanceCtx, {
            type: 'line',
            data: {
                labels: @json($last30Days),
                datasets: [{
                    label: 'Saldo',
                    data: @json($balanceTrend),
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        borderRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return 'Saldo: ' + formatRupiah(context.raw);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                            },
                            font: { size: 11 }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { 
                            font: { size: 10 },
                            maxRotation: 0,
                            autoSkip: true,
                            maxTicksLimit: 10
                        }
                    }
                }
            }
        });
    </script>
</x-app-layout>