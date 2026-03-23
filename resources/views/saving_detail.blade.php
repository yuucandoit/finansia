<x-app-layout>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Back Button & Header -->
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <a href="{{ route('saving.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 mb-4 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                    Kembali ke Tabungan
                </a>
                <select name="year" id="yearlyFilter" class=" mb-4 bg-white border border-gray-200 text-gray-600 hover:bg-gray-50  py-2 rounded-xl text-sm font-medium whitespace-nowrap transition-all">
                    @foreach ($years as $y)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            
            
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">{{ $saving->name }} ({{ $year }})</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $saving->bank->name ?? 'Unknown Bank' }} • {{ $saving->account_number }}
                    </p>
                </div>
                
                <!-- Saldo Card -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-5 shadow-lg">
                    <p class="text-blue-100 text-sm font-medium mb-1">Saldo Saat Ini</p>
                    <p class="text-white text-3xl font-bold">Rp {{ number_format($saving->current_balance, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-slide-down">
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-green-800 font-semibold">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-400 hover:text-green-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-slide-down">
                <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-6 h-6 text-white">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-red-800 font-semibold">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-600">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif


        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Total Cash In -->
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-10 h-10 bg-green-600 text-white rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-green-700 uppercase tracking-wide">Total Pemasukan</span>
                </div>
                <p class="text-2xl font-bold text-green-700">
                    Rp {{ number_format($yearly_income ?? 0, 0, ',', '.') }}
                </p>
            </div>

            <!-- Total Cash Out -->
            <div class="bg-gradient-to-br from-red-50 to-orange-50 border border-red-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-10 h-10 bg-red-600 text-white rounded-xl flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6L9 12.75l4.286-4.286a11.948 11.948 0 014.306 6.43l.776 2.898m0 0l3.182-5.511m-3.182 5.51l-5.511-3.181" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold text-red-700 uppercase tracking-wide">Total Pengeluaran</span>
                </div>
                <p class="text-2xl font-bold text-red-700">
                    Rp {{ number_format($yearly_spending ?? 0, 0, ',', '.') }}
                </p>
            </div>

        </div>


        <!-- Transaction Line Income vs spending Chart -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
            <div class="text-center pt-4">
                <h3 class="text-lg font-bold text-gray-800">Pemasukan VS Pengeluaran</h3>
            </div>
            <div class="h-full p-8">
                <canvas id="income_vs_spend" style="width: 100%; height: 400px;"></canvas>
            </div>
        </div>
        
        <!-- Transactions List -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div id="accordion-container" class="space-y-4">
                <div class="animate-pulse p-4 text-gray-400">Loading transactions...</div>
            </div>
        </div>
            
    </main>
</x-app-layout>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const rawData = @json($income_vs_spending);

    // 1. Siapkan Label Nama Bulan
    const monthLabels = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
    ];

    // 2. Map data dari DB ke Array 12 Bulan (isi 0 jika data bulan tersebut tidak ada)
    const incomeData = Array(12).fill(0);
    const spendingData = Array(12).fill(0);

    rawData.forEach(item => {
        // Karena EXTRACT(MONTH) di Postgres mengembalikan 1-12, kita kurangi 1 untuk index array (0-11)
        const monthIndex = parseInt(item.month) - 1; 
        incomeData[monthIndex] = parseFloat(item.income);
        spendingData[monthIndex] = parseFloat(item.spending);
    });

    // 3. Inisialisasi Chart.js
    const ctx = document.getElementById('income_vs_spend').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [
                {
                    label: 'Income',
                    data: incomeData,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.3,
                    fill: true
                },
                {
                    label: 'Spending',
                    data: spendingData,
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.3,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) label += ': ';
                            if (context.parsed.y !== null) {
                                // Format ke mata uang (IDR)
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>

{{-- Accordion --}}
<script>
    async function loadMonths(year = "{{ $year }}") {
        const container = document.getElementById('accordion-container');
        const savingId = "{{ $saving->id }}";

        const response = await fetch(`/api/getMonthsApi/${savingId}?year=${year}`);
        const months = await response.json();

        container.innerHTML = months.map(m => `
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <button onclick="toggleMonth(${m.month_num}, ${year})" 
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-50">
                    <span class="font-bold text-gray-700">${m.month_name.trim()} - ${year}</span>
                    <div class="flex items-center gap-2">
                        <span class="text-xs bg-gray-100 px-2 py-1 rounded-lg text-gray-500">${m.total_count} Transaksi</span>
                        <svg id="icon-${m.month_num}" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </div>
                </button>
                <div id="content-${m.month_num}" class="hidden border-t border-gray-50 bg-gray-50/30">
                    <div id="list-${m.month_num}" class="p-2 space-y-2">
                        <p class="text-center py-4 text-sm text-gray-400">Loading data...</p>
                    </div>
                </div>
            </div>
        `).join('');
    }

    // 2. Fungsi dipanggil saat user klik Header Bulan
    async function toggleMonth(monthNum, year) {
        const content = document.getElementById(`content-${monthNum}`);
        const list = document.getElementById(`list-${monthNum}`);
        const icon = document.getElementById(`icon-${monthNum}`);
        const savingId = "{{ $saving->id }}";

        content.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');

        // Jika sedang dibuka dan datanya masih kosong (belum pernah di-fetch)
        if (!content.classList.contains('hidden') && list.dataset.loaded !== "true") {
            const response = await fetch(`/api/getTransactionApi/${savingId}?year=${year}&month=${monthNum}`);
            const data = await response.json();
            console.log(data);
            const transactions = Object.values(data)[0];
            // 1. Cari Objek Transaksi Terbesar (Income & Spending)
            const incomeTransactions = transactions.filter(t => t.type === 'Cash In');
            const spendingTransactions = transactions.filter(t => t.type === 'Cash Out');

            // Cari transaksi income paling besar
            const maxIncomeObj = incomeTransactions.reduce((prev, current) => 
                (parseFloat(prev.amount) > parseFloat(current.amount)) ? prev : current, {amount: 0, note: '-'});

            // Cari transaksi spending paling besar
            const maxSpendingObj = spendingTransactions.reduce((prev, current) => 
                (parseFloat(prev.amount) > parseFloat(current.amount)) ? prev : current, {amount: 0, note: '-'});

            // 2. Hitung Total
            const totalIncome = incomeTransactions.reduce((a, b) => a + parseFloat(b.amount), 0);
            const totalSpending = spendingTransactions.reduce((a, b) => a + parseFloat(b.amount), 0);

            // 3. Render List Transaksi (Gunakan mapping yang sudah ada sebelumnya)
            let html = transactions.map(item => `
                <div class="flex justify-between items-center bg-white p-3 rounded-lg border border-gray-50 shadow-sm">
                    <div class="flex flex-col">
                        <span class="text-sm font-semibold text-gray-800">${item.description || 'Transaksi'}</span>
                        <span class="text-[10px] text-gray-400">${item.transaction_date}</span>
                    </div>
                    <span class="font-bold ${item.type === 'Cash In' ? 'text-emerald-600' : 'text-rose-600'}">
                        ${item.type === 'Cash In' ? '+' : '-'} Rp ${parseFloat(item.amount).toLocaleString('id-ID')}
                    </span>
                </div>
            `).join('');

            // 4. Tambahkan Section Ringkasan Detail di Akhir
            html += `
                <div class="mt-4 space-y-3 pt-4 border-t border-dashed border-gray-200">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                            <p class="text-[10px] text-emerald-600 font-bold uppercase">Total Income</p>
                            <p class="text-sm font-bold text-emerald-700">Rp ${totalIncome.toLocaleString('id-ID')}</p>
                        </div>
                        <div class="bg-rose-50 p-3 rounded-xl border border-rose-100">
                            <p class="text-[10px] text-rose-600 font-bold uppercase">Total Spending</p>
                            <p class="text-sm font-bold text-rose-700">Rp ${totalSpending.toLocaleString('id-ID')}</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-3 rounded-xl border border-gray-100 space-y-2">
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Top Income:</span>
                            <span class="text-xs font-semibold text-emerald-600">${maxIncomeObj.description || '-'} (Rp ${parseFloat(maxIncomeObj.amount).toLocaleString('id-ID')})</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-[10px] font-bold text-gray-500 uppercase">Top Spent:</span>
                            <span class="text-xs font-semibold text-rose-600">${maxSpendingObj.description || '-'} (Rp ${parseFloat(maxSpendingObj.amount).toLocaleString('id-ID')})</span>
                        </div>
                    </div>
                </div>
            `;

            list.innerHTML = html;
            
            list.dataset.loaded = "true"; // Tandai agar tidak fetch ulang kalau di-toggle lagi
        }
    }

    document.addEventListener('DOMContentLoaded', () => loadMonths());
</script>

{{-- Filter --}}
<script>
    const yearlyFilter = document.getElementById('yearlyFilter');
    yearlyFilter.addEventListener('change', function() {
        const year = this.value;
        
        // 1. Ambil URL saat ini
        const url = new URL(window.location.href);
        
        // 2. Gunakan URLSearchParams untuk mengelola parameter
        // Ini akan otomatis mengganti ?year=X jika sudah ada, atau menambahkannya jika belum ada
        url.searchParams.set('year', year);
        
        // 3. Pindah ke URL yang sudah rapi
        window.location.href = url.toString();
    });
</script>
<style>
    /* Menghilangkan scrollbar tapi tetap bisa scroll */
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    
    /* Animasi untuk flash messages */
    @keyframes slide-down {
        from { 
            opacity: 0;
            transform: translateY(-20px); 
        }
        to { 
            opacity: 1;
            transform: translateY(0); 
        }
    }
    .animate-slide-down {
        animation: slide-down 0.3s ease-out;
    }
</style>