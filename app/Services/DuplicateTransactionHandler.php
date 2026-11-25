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
     * Ekstrak kode referensi unik bank dari deskripsi (QRIS, ID#, REF, BI-FAST, dll).
     */
    public function extractReferenceIds(string $description): array
    {
        $refs = [];
        $patterns = [
            '/\bQRIS[A-Z]*\d{6,}(?:#\d+)?/i',
            '/\bID#\s*([A-Z0-9-]{6,})/i',
            '/\bREF(?:ERENSI)?[:#\s-]*([A-Z0-9-]{6,})/i',
            '/\b(?:TRX|TXN|INV|VA)[-_#:\s]*([A-Z0-9]{6,})/i',
            '/#(\d{8,})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $description, $matches)) {
                foreach ($matches[0] as $idx => $fullMatch) {
                    $token = !empty($matches[1][$idx]) ? $matches[1][$idx] : $fullMatch;
                    $normalized = strtoupper(preg_replace('/\s+/', '', $token));
                    if (strlen($normalized) >= 6) {
                        $refs[] = $normalized;
                    }
                }
            }
        }

        return array_values(array_unique($refs));
    }

    /**
     * Memeriksa apakah dua deskripsi transaksi dianggap cocok (identik atau sangat serupa).
     */
    public function isDescriptionMatching(string $desc1, string $desc2): bool
    {
        $refs1 = $this->extractReferenceIds($desc1);
        $refs2 = $this->extractReferenceIds($desc2);

        if (!empty($refs1) && !empty($refs2)) {
            $shared = array_intersect($refs1, $refs2);
            return !empty($shared);
        }

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
        ?string $transactionDate = null,
        ?string $type = null
    ): ?Transaction {
        $amountRounded = round($amount, 2);

        $query = Transaction::query()
            ->where('user_id', $userId)
            ->where('savings_id', $savingsId)
            ->where('amount', $amountRounded);

        if ($type !== null && $type !== '') {
            $query->where('type', $type);
        }

        $targetDate = $transactionDate ? Carbon::parse($transactionDate)->startOfDay() : null;
        if ($targetDate) {
            $query->whereBetween('transaction_date', [
                $targetDate->copy()->subDays(2)->toDateString(),
                $targetDate->copy()->addDays(2)->toDateString(),
            ]);
        }

        $candidates = $query->get();

        // Cari prioritas kecocokan tanggal yang sama terlebih dahulu
        $similarCandidate = null;
        $targetDateStr = $targetDate ? $targetDate->format('Y-m-d') : null;

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
            $incomingType = $item['type'] ?? null;
            $incomingDate = !empty($item['transaction_date'])
                ? Carbon::parse($item['transaction_date'])->startOfDay()
                : null;
            $incomingDateStr = $incomingDate ? $incomingDate->format('Y-m-d') : null;

            $matchedTx = null;
            $candidates = $groupedCandidates[$amtKey] ?? [];

            // 1. Coba cari kecocokan dengan transaksi tipe & tanggal yang sama persis di database
            foreach ($candidates as $cand) {
                if (in_array($cand->id, $claimedTxIds, true)) {
                    continue;
                }
                if ($incomingType && $cand->type !== $incomingType) {
                    continue;
                }

                if ($this->isDescriptionMatching($description, $cand->description)) {
                    $candDateStr = $cand->transaction_date ? Carbon::parse($cand->transaction_date)->format('Y-m-d') : null;
                    if ($incomingDateStr && $candDateStr === $incomingDateStr) {
                        $matchedTx = $cand;
                        break;
                    }
                }
            }

            // 2. Jika tidak ada tanggal sama persis, cari kandidat dalam rentang ±2 hari (settlement date)
            if (!$matchedTx && $incomingDate) {
                foreach ($candidates as $cand) {
                    if (in_array($cand->id, $claimedTxIds, true)) {
                        continue;
                    }
                    if ($incomingType && $cand->type !== $incomingType) {
                        continue;
                    }
                    if (!$cand->transaction_date) {
                        continue;
                    }

                    $candDate = Carbon::parse($cand->transaction_date)->startOfDay();
                    if (abs($incomingDate->diffInDays($candDate, false)) > 2) {
                        continue;
                    }

                    if ($this->isDescriptionMatching($description, $cand->description)) {
                        $matchedTx = $cand;
                        break;
                    }
                }
            }

            // 3. Cek juga apakah duplikat dengan baris sebelumnya di dalam file impor yang sama
            $batchKey = ($incomingType ?? '') . '|' . $this->normalizeDescription($description) . '|' . $amtKey . '|' . ($incomingDateStr ?? '');
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
                    : "Kemungkinan duplikat (beda tanggal ±2 hari): tercatat pada {$candDateFormatted} (Rp " . number_format($amount, 0, ',', '.') . ")";

                $item['is_duplicate'] = true;
                $item['duplicate_type'] = $isExactDate ? 'exact' : 'possible';
                $item['duplicate_reason'] = $reason;
                $item['matched_transaction'] = [
                    'id'                     => $matchedTx->id,
                    'transaction_date'       => $candDateStr,
                    'transaction_date_label' => $candDateFormatted,
                    'description'            => $matchedTx->description,
                    'amount'                 => (float) $matchedTx->amount,
                ];

                // Exact duplicate -> auto-uncheck; possible (±2 hari) -> tetap tercentang dengan peringatan
                $item['is_selected'] = $isExactDate ? false : ($item['is_selected'] ?? true);
                $duplicateCount++;

            } elseif ($isDuplicateInBatch) {
                $item['is_duplicate'] = true;
                $item['duplicate_type'] = 'batch';
                $item['duplicate_reason'] = 'Baris transaksi kembar dalam file ini (tetap dicentang jika memang 2 transaksi terpisah)';
                $item['matched_transaction'] = null;
                $item['is_selected'] = $item['is_selected'] ?? true;
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

