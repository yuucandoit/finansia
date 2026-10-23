<?php

namespace App\Services;

use App\Models\Saving;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DuplicateTransactionHandler
{
    /**
     * Normalisasi string deskripsi untuk pencocokan toleran:
     * - Case-insensitive (lowercase)
     * - Menggabungkan multiple whitespace menjadi satu spasi
     * - Membuang whitespace di awal dan akhir
     */
    public function normalizeDescription(string $description): string
    {
        $desc = mb_strtolower($description, 'UTF-8');
        $desc = preg_replace('/\s+/', ' ', $desc);

        return trim($desc);
    }

    /**
     * Memeriksa apakah dua deskripsi transaksi dianggap cocok (identik atau sangat serupa).
     */
    public function isDescriptionMatching(string $desc1, string $desc2): bool
    {
        $norm1 = $this->normalizeDescription($desc1);
        $norm2 = $this->normalizeDescription($desc2);

        if ($norm1 === '' || $norm2 === '') {
            return false;
        }

        // 1. Cocok persis setelah normalisasi
        if ($norm1 === $norm2) {
            return true;
        }

        $len1 = mb_strlen($norm1);
        $len2 = mb_strlen($norm2);

        // 2. Salah satu string memuat string lainnya (jika panjang cukup signifikan)
        if ($len1 >= 5 && $len2 >= 5) {
            if (str_contains($norm1, $norm2) || str_contains($norm2, $norm1)) {
                return true;
            }
        }

        // 3. Similaritas teks jika panjang memadai
        if ($len1 >= 8 && $len2 >= 8) {
            similar_text($norm1, $norm2, $percent);
            if ($percent >= 85.0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Memeriksa kesetaraan dua nilai nominal.
     */
    public function isAmountMatching(float $amount1, float $amount2): bool
    {
        return abs(round($amount1, 2) - round($amount2, 2)) < 0.01;
    }

    /**
     * Mencari satu transaksi duplikat di database untuk user dan rekening bank yang sama.
     *
     * @param int $userId ID Pengguna
     * @param int $savingsId ID Rekening (Saving)
     * @param string $description Nama/keterangan transaksi
     * @param float $amount Nominal transaksi
     * @param string|null $transactionDate Tanggal transaksi opsional (Y-m-d)
     * @return Transaction|null
     */
    public function findDuplicate(
        int $userId,
        int $savingsId,
        string $description,
        float $amount,
        ?string $transactionDate = null
    ): ?Transaction {
        $amountRounded = round($amount, 2);

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->where('savings_id', $savingsId)
            ->where('amount', $amountRounded);

        $candidates = $query->get();

        // Cari prioritas kecocokan tanggal yang sama terlebih dahulu
        $similarCandidate = null;
        $targetDateStr = $transactionDate ? Carbon::parse($transactionDate)->format('Y-m-d') : null;

        foreach ($candidates as $candidate) {
            if ($this->isDescriptionMatching($description, $candidate->description)) {
                $candDateStr = $candidate->transaction_date ? Carbon::parse($candidate->transaction_date)->format('Y-m-d') : null;

                if ($targetDateStr && $candDateStr === $targetDateStr) {
                    return $candidate;
                }

                if (!$similarCandidate) {
                    $similarCandidate = $candidate;
                }
            }
        }

        return $similarCandidate;
    }

    /**
     * Deteksi duplikasi untuk satu batch transaksi hasil impor secara efisien.
     *
     * @param int $userId ID Pengguna
     * @param int $savingsId ID Rekening (Saving)
     * @param array $transactions Daftar transaksi hasil ekstraksi
     * @param array $options Konfigurasi tambahan
     * @return array [
     *     'transactions'    => array,
     *     'duplicate_count' => int,
     *     'new_count'       => int,
     * ]
     */
    public function detectBatch(
        int $userId,
        int $savingsId,
        array $transactions,
        array $options = []
    ): array {
        if (empty($transactions)) {
            return [
                'transactions'    => [],
                'duplicate_count' => 0,
                'new_count'       => 0,
            ];
        }

        // Ambil seluruh nominal unik dalam batch untuk query kandidat efisien
        $amounts = [];
        foreach ($transactions as $t) {
            $amt = round((float) ($t['amount'] ?? 0), 2);
            if ($amt > 0) {
                $amounts[] = $amt;
            }
        }
        $amounts = array_values(array_unique($amounts));

        // Ambil data transaksi relevan dari database dalam 1 kali query
        $existingTransactions = empty($amounts)
            ? collect([])
            : Transaction::query()
                ->where('user_id', $userId)
                ->where('savings_id', $savingsId)
                ->whereIn('amount', $amounts)
                ->get(['id', 'savings_id', 'amount', 'description', 'transaction_date', 'type']);

        // Kelompokkan kandidat di memori berdasarkan nominal
        $groupedCandidates = [];
        foreach ($existingTransactions as $tx) {
            $amtKey = (string) round((float) $tx->amount, 2);
            $groupedCandidates[$amtKey][] = $tx;
        }

        $processed = [];
        $duplicateCount = 0;
        $newCount = 0;
        $claimedTxIds = [];
        $seenInBatch = [];

        foreach ($transactions as $item) {
            $amount = (float) ($item['amount'] ?? 0);
            $amtKey = (string) round($amount, 2);
            $description = trim($item['description'] ?? '');
            $incomingDateStr = !empty($item['transaction_date'])
                ? Carbon::parse($item['transaction_date'])->format('Y-m-d')
                : null;

            $matchedTx = null;
            $candidates = $groupedCandidates[$amtKey] ?? [];

            // 1. Coba cari kecocokan dengan transaksi tanggal yang sama di database
            foreach ($candidates as $cand) {
                if (in_array($cand->id, $claimedTxIds, true)) {
                    continue;
                }

                if ($this->isDescriptionMatching($description, $cand->description)) {
                    $candDate = $cand->transaction_date ? Carbon::parse($cand->transaction_date)->format('Y-m-d') : null;
                    if ($incomingDateStr && $candDate === $incomingDateStr) {
                        $matchedTx = $cand;
                        break;
                    }
                }
            }

            // 2. Jika tidak ada yang tanggalnya sama persis, cari kecocokan nama dan jumlah pada tanggal berbeda
            if (!$matchedTx) {
                foreach ($candidates as $cand) {
                    if (in_array($cand->id, $claimedTxIds, true)) {
                        continue;
                    }

                    if ($this->isDescriptionMatching($description, $cand->description)) {
                        $matchedTx = $cand;
                        break;
                    }
                }
            }

            // 3. Cek juga apakah duplikat dengan baris sebelumnya di dalam file impor yang sama
            $batchKey = $this->normalizeDescription($description) . '|' . $amtKey . '|' . ($incomingDateStr ?? '');
            $isDuplicateInBatch = isset($seenInBatch[$batchKey]);

            if ($matchedTx) {
                $claimedTxIds[] = $matchedTx->id;
                $candDateFormatted = $matchedTx->transaction_date
                    ? Carbon::parse($matchedTx->transaction_date)->translatedFormat('d M Y')
                    : '-';

                $candDateStr = $matchedTx->transaction_date
                    ? Carbon::parse($matchedTx->transaction_date)->format('Y-m-d')
                    : null;

                $isExactDate = ($incomingDateStr && $candDateStr === $incomingDateStr);

                $reason = $isExactDate
                    ? "Transaksi serupa sudah tercatat di rekening ini pada tanggal yang sama ({$candDateFormatted})"
                    : "Transaksi serupa sudah tercatat di rekening ini pada {$candDateFormatted} (Rp " . number_format($amount, 0, ',', '.') . ")";

                $item['is_duplicate'] = true;
                $item['duplicate_type'] = $isExactDate ? 'exact' : 'similar';
                $item['duplicate_reason'] = $reason;
                $item['matched_transaction'] = [
                    'id'                     => $matchedTx->id,
                    'transaction_date'       => $candDateStr,
                    'transaction_date_label' => $candDateFormatted,
                    'description'            => $matchedTx->description,
                    'amount'                 => (float) $matchedTx->amount,
                ];

                // Otomatis uncheck transaksi yang duplikat
                $item['is_selected'] = false;
                $duplicateCount++;

            } elseif ($isDuplicateInBatch) {
                $item['is_duplicate'] = true;
                $item['duplicate_type'] = 'batch';
                $item['duplicate_reason'] = 'Baris transaksi kembar terdeteksi di dalam file impor yang sama';
                $item['matched_transaction'] = null;
                $item['is_selected'] = false;
                $duplicateCount++;

            } else {
                $item['is_duplicate'] = false;
                $item['duplicate_type'] = null;
                $item['duplicate_reason'] = null;
                $item['matched_transaction'] = null;
                $item['is_selected'] = $item['is_selected'] ?? true;
                $newCount++;
            }

            $seenInBatch[$batchKey] = true;
            $processed[] = $item;
        }

        return [
            'transactions'    => $processed,
            'duplicate_count' => $duplicateCount,
            'new_count'       => $newCount,
        ];
    }
}

