<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $report['period_label'] }} - Finansia</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; margin: 28px; font-size: 12px; }
        h1 { margin: 0 0 4px 0; font-size: 20px; color: #1e3a8a; }
        h2 { margin: 22px 0 8px 0; font-size: 14px; border-bottom: 1px solid #cbd5e1; padding-bottom: 4px; color: #0f172a; }
        .meta { color: #64748b; margin-bottom: 18px; }
        .grid { display: flex; gap: 12px; margin-bottom: 16px; }
        .card { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; background: #f8fafc; }
        .card-label { font-size: 10px; text-transform: uppercase; color: #64748b; font-weight: bold; }
        .card-val { font-size: 15px; font-weight: bold; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; font-size: 11px; }
        .right { text-align: right; }
        .no-print { margin-bottom: 16px; }
        @media print { .no-print { display: none; } body { margin: 12px; } }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding:8px 14px; background:#234066; color:#fff; border:none; border-radius:6px; font-weight:bold; cursor:pointer;">
            🖨️ Cetak / Simpan sebagai PDF
        </button>
    </div>

    <h1>FINANSIA — {{ $report['period_label'] }}</h1>
    <div class="meta">
        Pemilik: <strong>{{ $user->name }}</strong> ({{ $user->email }}) • Periode: {{ $report['start_date'] }} s/d {{ $report['end_date'] }}
    </div>

    <div class="grid">
        <div class="card">
            <div class="card-label">Total Pemasukan</div>
            <div class="card-val" style="color:#059669;">+ Rp {{ number_format($report['total_income'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Total Pengeluaran</div>
            <div class="card-val" style="color:#e11d48;">- Rp {{ number_format($report['total_expense'], 0, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="card-label">Net Cashflow</div>
            <div class="card-val">Rp {{ number_format($report['net_cashflow'], 0, ',', '.') }} ({{ $report['savings_rate'] }}%)</div>
        </div>
        <div class="card">
            <div class="card-label">Kekayaan Bersih (Net Worth)</div>
            <div class="card-val" style="color:#1d4ed8;">Rp {{ number_format($report['net_worth_summary']['net_worth'], 0, ',', '.') }}</div>
        </div>
    </div>

    <h2>Pengeluaran per Kategori</h2>
    <table>
        <thead>
            <tr><th>Kategori</th><th class="right">Frekuensi</th><th class="right">Total (Rp)</th><th class="right">Porsi</th></tr>
        </thead>
        <tbody>
            @forelse($report['category_breakdown'] as $cat)
                <tr>
                    <td>{{ $cat['category'] }}</td>
                    <td class="right">{{ $cat['count'] }}x</td>
                    <td class="right">Rp {{ number_format($cat['total'], 0, ',', '.') }}</td>
                    <td class="right">{{ $cat['percentage'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="4">Tidak ada pengeluaran pada periode ini.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($report['budget_realization']))
        <h2>Realisasi Anggaran</h2>
        <table>
            <thead>
                <tr><th>Pos Anggaran</th><th class="right">Batas</th><th class="right">Terpakai</th><th class="right">Sisa</th><th class="right">Status</th></tr>
            </thead>
            <tbody>
                @foreach($report['budget_realization'] as $b)
                    <tr>
                        <td>{{ $b['name'] }}</td>
                        <td class="right">Rp {{ number_format($b['limit'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($b['spent'], 0, ',', '.') }}</td>
                        <td class="right">Rp {{ number_format($b['remaining'], 0, ',', '.') }}</td>
                        <td class="right">{{ strtoupper($b['status']) }} ({{ $b['percentage'] }}%)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2>Daftar Transaksi ({{ $report['transactions']->count() }})</h2>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Tipe</th><th>Deskripsi</th><th>Kategori</th><th class="right">Nominal (Rp)</th></tr>
        </thead>
        <tbody>
            @foreach($report['transactions'] as $tx)
                <tr>
                    <td>{{ $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '-' }}</td>
                    <td>{{ $tx->type }}</td>
                    <td>{{ $tx->description }}</td>
                    <td>{{ $tx->category->name ?? 'Lainnya' }}</td>
                    <td class="right">Rp {{ number_format((float)$tx->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
