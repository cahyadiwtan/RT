<?php

namespace App\Http\Controllers\Pengurus;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use App\Services\CashFlowService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService, protected CashFlowService $cashFlowService) {}

    public function index()
    {
        return redirect()->route('pengurus.reports.iuran');
    }

    public function iuran(Request $request)
    {
        $period = $request->input('billing_period');
        $block = $request->input('block');
        $status = $request->input('status');

        $bills = $this->reportService->getIuranReport($period, $block, $status);
        $summary = $this->reportService->getIuranSummary($period, $block, $status);
        $periods = $this->reportService->getAvailablePeriods();
        $blocks = $this->reportService->getAvailableBlocks();

        return view('pengurus.reports.iuran', compact('bills', 'summary', 'periods', 'blocks'));
    }

    public function payments(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $method = $request->input('method');
        $houseId = $request->input('house_id') ? (int) $request->input('house_id') : null;

        $payments = $this->reportService->getPaymentReport($dateFrom, $dateTo, $method, $houseId);
        $summary = $this->reportService->getPaymentSummary($dateFrom, $dateTo, $method, $houseId);

        return view('pengurus.reports.payments', compact('payments', 'summary'));
    }

    public function arrears()
    {
        $bills = $this->reportService->getArrearsReport();
        $summary = $this->reportService->getArrearsSummary();

        return view('pengurus.reports.arrears', compact('bills', 'summary'));
    }

    public function deposits()
    {
        $rows = $this->reportService->getDepositReport();
        $summary = $this->reportService->getDepositSummary();

        return view('pengurus.reports.deposits', compact('rows', 'summary'));
    }

    public function cashflow(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');

        $summary = $this->cashFlowService->getSummary($dateFrom, $dateTo);
        $monthly = $this->cashFlowService->getMonthly($dateFrom, $dateTo);
        $transactions = $this->cashFlowService->getTransactions($dateFrom, $dateTo, $type);

        return view('pengurus.reports.cashflow', compact('summary', 'monthly', 'transactions'));
    }

    public function assets()
    {
        $assets = $this->reportService->getAssetReport();
        $summary = $this->reportService->getAssetSummary();

        return view('pengurus.reports.assets', compact('assets', 'summary'));
    }

    public function export(Request $request, string $type)
    {
        $filename = "laporan-{$type}-".now()->format('Y-m-d').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($type, $request) {
            $handle = fopen('php://output', 'w');

            match ($type) {
                'iuran' => $this->exportIuran($handle, $request),
                'pembayaran' => $this->exportPayments($handle, $request),
                'tunggakan' => $this->exportArrears($handle),
                'deposit' => $this->exportDeposits($handle),
                'inventaris' => $this->exportAssets($handle),
                default => null,
            };

            fclose($handle);
        }, 200, $headers);
    }

    private function exportIuran($handle, Request $request): void
    {
        fputcsv($handle, ['Periode', 'Rumah', 'Tagihan', 'Dibayar', 'Sisa', 'Status']);

        $rows = $this->reportService->getIuranRows(
            $request->input('billing_period'),
            $request->input('block'),
            $request->input('status'),
        );

        foreach ($rows as $bill) {
            fputcsv($handle, [
                $bill->billing_period,
                $bill->house?->full_address ?? '-',
                $bill->amount,
                $bill->paid_amount,
                $bill->remaining_amount,
                $bill->status,
            ]);
        }
    }

    private function exportPayments($handle, Request $request): void
    {
        fputcsv($handle, ['Tanggal', 'Rumah', 'Pembayar', 'Metode', 'Nominal', 'Referensi']);

        $rows = $this->reportService->getPaymentRows(
            $request->input('date_from'),
            $request->input('date_to'),
            $request->input('method'),
            $request->input('house_id') ? (int) $request->input('house_id') : null,
        );

        foreach ($rows as $payment) {
            fputcsv($handle, [
                $payment->payment_date->format('d/m/Y'),
                $payment->house?->full_address ?? '-',
                $payment->resident?->nama_lengkap ?? 'Warga',
                $payment->payment_method,
                $payment->amount,
                $payment->reference_number ?? '-',
            ]);
        }
    }

    private function exportArrears($handle): void
    {
        fputcsv($handle, ['Rumah', 'Warga', 'Periode', 'Sisa Tunggakan']);

        $rows = $this->reportService->getArrearsRows();

        foreach ($rows as $bill) {
            $wargaNames = $bill->house->residents->pluck('nama_lengkap')->implode(', ');
            fputcsv($handle, [
                $bill->house?->full_address ?? '-',
                $wargaNames ?: '-',
                $bill->billing_period,
                $bill->remaining_amount,
            ]);
        }
    }

    private function exportDeposits($handle): void
    {
        fputcsv($handle, ['Rumah', 'Warga', 'Saldo Deposit', 'Pembayaran Terakhir']);

        $rows = $this->reportService->getDepositRows();

        foreach ($rows as $row) {
            $wargaNames = $row['house']?->residents->pluck('nama_lengkap')->implode(', ');
            fputcsv($handle, [
                $row['house']?->full_address ?? '-',
                $wargaNames ?: '-',
                $row['credit_balance'],
                $row['last_payment_date'] ? date('d/m/Y', strtotime($row['last_payment_date'])) : '-',
            ]);
        }
    }

    private function exportAssets($handle): void
    {
        fputcsv($handle, ['Kode', 'Nama', 'Kategori', 'Jumlah', 'Kondisi', 'Status', 'Lokasi']);

        $rows = $this->reportService->getAssetRows();

        foreach ($rows as $asset) {
            fputcsv($handle, [
                $asset->asset_code,
                $asset->name,
                $asset->category?->name ?? '-',
                $asset->quantity.' '.$asset->unit,
                $asset->condition,
                $asset->status,
                $asset->location ?? '-',
            ]);
        }
    }
}
