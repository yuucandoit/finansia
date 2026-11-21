<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\Debt;
use App\Models\Saving;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class FinancialReportService
{
    public const INDONESIAN_MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function buildReportData(User $user, string $mode = 'monthly', int $month = 0, int $year = 0): array
    {
        $month = ($month >= 1 && $month <= 12) ? $month : (int) now()->month;
        $year = ($year >= 2020 && $year <= 2099) ? $year : (int) now()->year;
        $mode = in_array($mode, ['monthly', 'yearly'], true) ? $mode : 'monthly';

        if ($mode === 'yearly') {
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfYear();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfYear();
            $periodLabel = "Laporan Tahunan {$year}";
        } else {
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            $monthName = self::INDONESIAN_MONTHS[$month] ?? $startDate->format('F');
            $periodLabel = "Laporan Bulanan — {$monthName} {$year}";
        }

        $transactions = Transaction::with(['category', 'savingAccount'])
            ->where('user_id', $user->id)
            ->whereDate('transaction_date', '>=', $startDate->toDateString())
            ->whereDate('transaction_date', '<=', $endDate->toDateString())
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Filter out paired internal transfers from income/expense totals
        $nonInternal = $transactions->filter(fn (Transaction $t) => !$t->transfer_group_id);

        $totalIncome = (float) $nonInternal->where('direction', Transaction::DIRECTION_IN)->sum('amount');
        $totalExpense = (float) $nonInternal
            ->where('direction', Transaction::DIRECTION_OUT)
            ->sum(fn (Transaction $t) => (float) $t->amount + (float) ($t->fee ?? 0));

        $netCashflow = $totalIncome - $totalExpense;
        $savingsRate = $totalIncome > 0 ? round(($netCashflow / $totalIncome) * 100, 1) : 0.0;

        // Category breakdown (Cash Out)
        $categoryBreakdown = $nonInternal
            ->where('direction', Transaction::DIRECTION_OUT)
            ->groupBy(fn (Transaction $t) => $t->category->name ?? 'Tanpa Kategori')
            ->map(function ($group, $catName) use ($totalExpense) {
                $total = (float) $group->sum(fn (Transaction $t) => (float) $t->amount + (float) ($t->fee ?? 0));
                return [
                    'category'   => $catName,
                    'count'      => $group->count(),
                    'total'      => $total,
                    'percentage' => $totalExpense > 0 ? round(($total / $totalExpense) * 100, 1) : 0.0,
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->toArray();

        // Budget realization (for monthly mode, or aggregated for yearly)
        $budgetQuery = Budget::with('items.category')
            ->where('user_id', $user->id)
            ->where('year', $year);

        if ($mode === 'monthly') {
            $budgetQuery->where('month', $month);
        }

        $budgets = $budgetQuery->get();
        $budgetItems = $budgets->flatMap(fn ($b) => $b->items);
        $budgetRealization = $budgetItems->map(function ($item) {
            return [
                'name'       => $item->name,
                'category'   => $item->category->name ?? 'Lainnya',
                'limit'      => (float) $item->amount,
                'spent'      => (float) $item->spent,
                'remaining'  => (float) $item->remaining,
                'percentage' => (float) $item->percentage,
                'status'     => $item->status,
            ];
        })->values()->toArray();

        // Net Worth Summary (Current Snapshot)
        $totalSavings = (float) Saving::where('user_id', $user->id)->sum('current_balance');
        $assets = Asset::where('user_id', $user->id)->get();
        $totalAssetsValue = (float) $assets->sum('current_value');
        $totalPayableDebt = (float) Debt::where('user_id', $user->id)
            ->where('type', Debt::TYPE_PAYABLE)
            ->where('status', '!=', Debt::STATUS_PAID)
            ->get()
            ->sum('remaining_amount');
        $netWorth = $totalSavings + $totalAssetsValue - $totalPayableDebt;

        return [
            'mode'               => $mode,
            'month'              => $month,
            'year'               => $year,
            'period_label'       => $periodLabel,
            'start_date'         => $startDate->toDateString(),
            'end_date'           => $endDate->toDateString(),
            'total_income'       => $totalIncome,
            'total_expense'      => $totalExpense,
            'net_cashflow'       => $netCashflow,
            'savings_rate'       => $savingsRate,
            'category_breakdown' => $categoryBreakdown,
            'budget_realization' => $budgetRealization,
            'net_worth_summary'  => [
                'total_savings' => $totalSavings,
                'total_assets'  => $totalAssetsValue,
                'total_debt'    => $totalPayableDebt,
                'net_worth'     => $netWorth,
            ],
            'transactions'       => $transactions,
        ];
    }

    public function exportXlsx(array $reportData): StreamedResponse
    {
        $rows = [];
        $rows[] = ['FINANSIA — LAPORAN KEUANGAN', $reportData['period_label']];
        $rows[] = ['Periode', $reportData['start_date'] . ' s/d ' . $reportData['end_date']];
        $rows[] = [];

        $rows[] = ['RINGKASAN ARUS KAS', 'Nominal (Rp)'];
        $rows[] = ['Total Pemasukan', $reportData['total_income']];
        $rows[] = ['Total Pengeluaran', $reportData['total_expense']];
        $rows[] = ['Selisih Bersih (Net Cashflow)', $reportData['net_cashflow']];
        $rows[] = ['Savings Rate (%)', $reportData['savings_rate'] . '%'];
        $rows[] = [];

        $nw = $reportData['net_worth_summary'];
        $rows[] = ['RINGKASAN KEKAYAAN BERSIH (NET WORTH)', 'Nominal (Rp)'];
        $rows[] = ['Total Saldo Rekening & Tabungan', $nw['total_savings']];
        $rows[] = ['Total Nilai Aset & Investasi', $nw['total_assets']];
        $rows[] = ['Total Kewajiban Hutang Aktif', $nw['total_debt']];
        $rows[] = ['Total Kekayaan Bersih (Net Worth)', $nw['net_worth']];
        $rows[] = [];

        $rows[] = ['PENGELUARAN PER KATEGORI', 'Jumlah Transaksi', 'Total (Rp)', 'Porsi (%)'];
        foreach ($reportData['category_breakdown'] as $cat) {
            $rows[] = [$cat['category'], $cat['count'], $cat['total'], $cat['percentage'] . '%'];
        }
        $rows[] = [];

        if (!empty($reportData['budget_realization'])) {
            $rows[] = ['REALISASI ANGGARAN', 'Kategori', 'Batas (Rp)', 'Terpakai (Rp)', 'Sisa (Rp)', 'Persentase', 'Status'];
            foreach ($reportData['budget_realization'] as $b) {
                $rows[] = [$b['name'], $b['category'], $b['limit'], $b['spent'], $b['remaining'], $b['percentage'] . '%', strtoupper($b['status'])];
            }
            $rows[] = [];
        }

        $rows[] = ['DAFTAR TRANSAKSI', 'Tipe', 'Kategori', 'Rekening', 'Nominal (Rp)', 'Biaya Admin (Rp)', 'Deskripsi'];
        foreach ($reportData['transactions'] as $tx) {
            $rows[] = [
                $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '-',
                $tx->type,
                $tx->category->name ?? 'Lainnya',
                $tx->savingAccount->name ?? '-',
                (float) $tx->amount,
                (float) ($tx->fee ?? 0),
                $tx->description,
            ];
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'fin_rep_') . '.xlsx';
        $this->buildSimpleXlsx($rows, $tempFile);

        $filename = 'laporan-keuangan-' . $reportData['mode'] . '-' . $reportData['year'] . ($reportData['mode'] === 'monthly' ? '-' . str_pad((string) $reportData['month'], 2, '0', STR_PAD_LEFT) : '') . '.xlsx';

        return response()->streamDownload(function () use ($tempFile) {
            readfile($tempFile);
            @unlink($tempFile);
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    protected function buildSimpleXlsx(array $rows, string $filePath): void
    {
        $zip = new ZipArchive();
        $zip->open($filePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
</Types>');

        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>');

        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>');

        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Laporan Keuangan" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>');

        $sheetRowsXml = '';
        foreach ($rows as $rIdx => $row) {
            $rowNum = $rIdx + 1;
            $sheetRowsXml .= '<row r="' . $rowNum . '">';
            foreach ($row as $cIdx => $val) {
                $colLetter = $this->colIndexToLetter($cIdx);
                $cellRef = $colLetter . $rowNum;
                if (is_int($val) || is_float($val)) {
                    $sheetRowsXml .= '<c r="' . $cellRef . '"><v>' . $val . '</v></c>';
                } else {
                    $escaped = htmlspecialchars((string) $val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                    $sheetRowsXml .= '<c r="' . $cellRef . '" t="inlineStr"><is><t>' . $escaped . '</t></is></c>';
                }
            }
            $sheetRowsXml .= '</row>';
        }

        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <sheetData>' . $sheetRowsXml . '</sheetData>
</worksheet>');

        $zip->close();
    }

    protected function colIndexToLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr(65 + ($index % 26)) . $letter;
            $index = intdiv($index, 26) - 1;
        }
        return $letter;
    }
}
