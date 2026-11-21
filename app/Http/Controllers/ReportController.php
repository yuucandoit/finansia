<?php

namespace App\Http\Controllers;

use App\Services\FinancialReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request, FinancialReportService $reportService): View
    {
        $mode = $request->query('mode', 'monthly');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $report = $reportService->buildReportData(Auth::user(), $mode, $month, $year);
        $months = FinancialReportService::INDONESIAN_MONTHS;

        return view('report.index', compact('report', 'months'));
    }

    public function exportExcel(Request $request, FinancialReportService $reportService): StreamedResponse
    {
        $mode = $request->query('mode', 'monthly');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $report = $reportService->buildReportData(Auth::user(), $mode, $month, $year);

        return $reportService->exportXlsx($report);
    }

    public function printPdf(Request $request, FinancialReportService $reportService): View
    {
        $mode = $request->query('mode', 'monthly');
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $report = $reportService->buildReportData(Auth::user(), $mode, $month, $year);
        $user = Auth::user();

        return view('report.pdf', compact('report', 'user'));
    }
}
