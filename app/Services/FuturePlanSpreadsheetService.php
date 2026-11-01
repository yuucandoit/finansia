<?php

namespace App\Services;

use App\Models\FuturePlan;
use App\Models\FuturePlanItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class FuturePlanSpreadsheetService
{
    /**
     * Header standar untuk ekspor dan templat.
     */
    public const HEADERS = [
        'No / Hierarki',
        'Nama Item',
        'Jumlah (Qty)',
        'Harga Satuan (Rp)',
        'Total Estimasi (Rp)',
        'Status Selesai',
        'Tanggal Mulai',
        'Tanggal Selesai',
        'Catatan / Spesifikasi',
        'Nama Induk',
    ];

    /**
     * Data contoh untuk file template.
     */
    public const SAMPLE_ROWS = [
        [
            'no'           => '1',
            'title'        => 'Mahar Pernikahan',
            'quantity'     => 1,
            'unit_price'   => 0,
            'total'        => 0,
            'is_completed' => 'Belum',
            'start_date'   => '2027-01-01',
            'end_date'     => '2027-06-30',
            'notes'        => 'Grup utama kebutuhan mahar',
            'parent_name'  => '',
        ],
        [
            'no'           => '1.1',
            'title'        => 'Emas Logam Mulia 25 Gram',
            'quantity'     => 1,
            'unit_price'   => 30000000,
            'total'        => 30000000,
            'is_completed' => 'Belum',
            'start_date'   => '2027-01-05',
            'end_date'     => '2027-04-30',
            'notes'        => 'Target / plafon emas 25g Antam',
            'parent_name'  => 'Mahar Pernikahan',
        ],
        [
            'no'           => '1.1.1',
            'title'        => 'Cicil Emas 5 Gram (Tahap 1)',
            'quantity'     => 1,
            'unit_price'   => 6000000,
            'total'        => 6000000,
            'is_completed' => 'Sudah',
            'start_date'   => '2027-01-10',
            'end_date'     => '2027-01-25',
            'notes'        => 'Sudah terbeli via Butik Antam',
            'parent_name'  => 'Emas Logam Mulia 25 Gram',
        ],
        [
            'no'           => '1.1.2',
            'title'        => 'Cicil Emas 5 Gram (Tahap 2)',
            'quantity'     => 1,
            'unit_price'   => 6000000,
            'total'        => 6000000,
            'is_completed' => 'Belum',
            'start_date'   => '2027-02-01',
            'end_date'     => '2027-02-28',
            'notes'        => 'Rencana pembelian bulan depan',
            'parent_name'  => 'Emas Logam Mulia 25 Gram',
        ],
        [
            'no'           => '2',
            'title'        => 'Catering Pernikahan',
            'quantity'     => 500,
            'unit_price'   => 75000,
            'total'        => 37500000,
            'is_completed' => 'Belum',
            'start_date'   => '2027-05-01',
            'end_date'     => '2027-06-25',
            'notes'        => 'Paket buffet 500 porsi include dekorasi meja',
            'parent_name'  => '',
        ],
    ];

    /**
     * Ekspor daftar item dari Future Plan ke CSV (UTF-8 dengan BOM agar aman dibuka di Excel).
     */
    public function exportCsv(FuturePlan $futurePlan): StreamedResponse
    {
        $rows = $this->prepareExportRows($futurePlan);
        $fileName = 'future-plan-' . Str::slug($futurePlan->title ?: 'detail') . '-items.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            // Tulis UTF-8 BOM agar Excel di Windows & Mac membaca karakter dengan baik
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, self::HEADERS);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['no'],
                    $row['title'],
                    $row['quantity'],
                    $row['unit_price'],
                    $row['total'],
                    $row['is_completed'],
                    $row['start_date'] ?? '',
                    $row['end_date'] ?? '',
                    $row['notes'] ?? '',
                    $row['parent_name'] ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Unduh template kosong berisikan contoh format data dalam CSV.
     */
    public function exportTemplateCsv(): StreamedResponse
    {
        $fileName = 'template-future-plan-items.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, self::HEADERS);

            foreach (self::SAMPLE_ROWS as $row) {
                fputcsv($handle, [
                    $row['no'],
                    $row['title'],
                    $row['quantity'],
                    $row['unit_price'],
                    $row['total'],
                    $row['is_completed'],
                    $row['start_date'] ?? '',
                    $row['end_date'] ?? '',
                    $row['notes'] ?? '',
                    $row['parent_name'] ?? '',
                ]);
            }

            fclose($handle);
        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Ekspor ke file OpenXML Spreadsheet (.xlsx) murni tanpa pustaka eksternal.
     */
    public function exportXlsx(FuturePlan $futurePlan): StreamedResponse
    {
        $rows = $this->prepareExportRows($futurePlan);
        $fileName = 'future-plan-' . Str::slug($futurePlan->title ?: 'detail') . '-items.xlsx';

        $tempPath = $this->generateXlsxFile($rows, $futurePlan->title);

        return response()->streamDownload(function () use ($tempPath) {
            readfile($tempPath);
            @unlink($tempPath);
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Unduh template format dalam .xlsx.
     */
    public function exportTemplateXlsx(): StreamedResponse
    {
        $fileName = 'template-future-plan-items.xlsx';
        $tempPath = $this->generateXlsxFile(self::SAMPLE_ROWS, 'Template Rencana');

        return response()->streamDownload(function () use ($tempPath) {
            readfile($tempPath);
            @unlink($tempPath);
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Memproses import file (bisa .xlsx, .xls, .csv, atau .txt).
     *
     * @param UploadedFile $file
     * @param FuturePlan   $futurePlan
     * @param string       $mode 'append' (default) atau 'replace'
     * @return array Ringkasan hasil import ['total_imported' => int, 'root_items' => int, 'child_items' => int]
     */
    public function import(UploadedFile $file, FuturePlan $futurePlan, string $mode = 'append'): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $rawRows = [];

        if (in_array($extension, ['xlsx', 'xls'])) {
            $rawRows = $this->readXlsxRows($file->getRealPath());
        } else {
            $rawRows = $this->readCsvRows($file->getRealPath());
        }

        if (empty($rawRows)) {
            throw new \InvalidArgumentException('File kosong atau format data tidak dapat dibaca.');
        }

        return $this->processImportRows($rawRows, $futurePlan, $mode);
    }

    /**
     * Menyiapkan baris data berurutan Depth-First dengan kode hierarki numerik 1, 1.1, 1.1.1, dst.
     */
    public function prepareExportRows(FuturePlan $futurePlan): array
    {
        $items = FuturePlanItem::getFlatTreeForPlan($futurePlan->id);

        $rows = [];
        $counters = []; // Stack pencacah per depth
        $idToCode = [];
        $idToTitle = [];

        // Petakan nama dan relasi item
        foreach ($items as $item) {
            $idToTitle[$item->id] = $item->title;
        }

        foreach ($items as $item) {
            $depth = (int) ($item->depth ?? 0);

            // Perbarui pencacah tingkat kedalaman
            if (!isset($counters[$depth])) {
                $counters[$depth] = 1;
            } else {
                $counters[$depth]++;
            }

            // Bersihkan pencacah yang lebih dalam
            foreach (array_keys($counters) as $d) {
                if ($d > $depth) {
                    unset($counters[$d]);
                }
            }

            // Bangun nomor kode hirarki: e.g. "1.2.1"
            $codeParts = [];
            for ($d = 0; $d <= $depth; $d++) {
                $codeParts[] = $counters[$d] ?? 1;
            }
            $hierarchyCode = implode('.', $codeParts);
            $idToCode[$item->id] = $hierarchyCode;

            $parentTitle = $item->parent_id && isset($idToTitle[$item->parent_id])
                ? $idToTitle[$item->parent_id]
                : '';

            $subtotal = (float) ($item->quantity * (float) $item->unit_price);

            $startDate = $item->start_date ? $item->start_date->format('Y-m-d') : '';
            $endDate = $item->end_date ? $item->end_date->format('Y-m-d') : '';

            $rows[] = [
                'no'           => $hierarchyCode,
                'title'        => $item->title,
                'quantity'     => (int) $item->quantity,
                'unit_price'   => (float) $item->unit_price,
                'total'        => $subtotal,
                'is_completed' => $item->is_completed ? 'Sudah' : 'Belum',
                'start_date'   => $startDate,
                'end_date'     => $endDate,
                'notes'        => $item->notes ?? '',
                'parent_name'  => $parentTitle,
            ];
        }

        return $rows;
    }

    /**
     * Membaca baris-baris dari file CSV.
     */
    protected function readCsvRows(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false || trim($content) === '') {
            return [];
        }

        // Hapus BOM UTF-8 bila ada
        if (str_starts_with($content, "\xEF\xBB\xBF")) {
            $content = substr($content, 3);
        }

        // Deteksi delimiter: koma, titik koma, atau tab
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = ',';
        $semicolonCount = substr_count($firstLine, ';');
        $commaCount = substr_count($firstLine, ',');
        $tabCount = substr_count($firstLine, "\t");

        if ($semicolonCount > $commaCount && $semicolonCount >= $tabCount) {
            $delimiter = ';';
        } elseif ($tabCount > $commaCount && $tabCount > $semicolonCount) {
            $delimiter = "\t";
        }

        $lines = preg_split('/\r\n|\r|\n/', trim($content));
        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }
            $parsed = str_getcsv($line, $delimiter);
            $rows[] = array_map(fn ($c) => trim($c ?? ''), $parsed);
        }

        return $rows;
    }

    /**
     * Membaca file .xlsx menggunakan ZipArchive dan SimpleXML bawaan PHP.
     */
    protected function readXlsxRows(string $filePath): array
    {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            throw new \RuntimeException('Gagal membuka file Excel (.xlsx). Pastikan file tidak terkunci atau rusak.');
        }

        // 1. Baca shared strings jika ada
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $xml = @simplexml_load_string($sharedStringsXml);
            if ($xml && isset($xml->si)) {
                foreach ($xml->si as $si) {
                    if (isset($si->t)) {
                        $sharedStrings[] = (string) $si->t;
                    } elseif (isset($si->r)) {
                        // Rich text run
                        $text = '';
                        foreach ($si->r as $r) {
                            $text .= (string) $r->t;
                        }
                        $sharedStrings[] = $text;
                    } else {
                        $sharedStrings[] = '';
                    }
                }
            }
        }

        // 2. Baca worksheet pertama (sheet1.xml)
        $sheetXmlContent = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXmlContent === false) {
            // Coba cari sheet pertama jika namanya berbeda
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if ($stat && str_starts_with($stat['name'], 'xl/worksheets/sheet') && str_ends_with($stat['name'], '.xml')) {
                    $sheetXmlContent = $zip->getFromIndex($i);
                    break;
                }
            }
        }

        $zip->close();

        if ($sheetXmlContent === false) {
            throw new \RuntimeException('Worksheet tidak ditemukan di dalam file Excel.');
        }

        $sheetXml = @simplexml_load_string($sheetXmlContent);
        if (!$sheetXml || !isset($sheetXml->sheetData->row)) {
            return [];
        }

        $rows = [];

        foreach ($sheetXml->sheetData->row as $row) {
            $rowNum = (int) ($row['r'] ?? count($rows) + 1);
            $rowData = [];

            foreach ($row->c as $cell) {
                $cellRef = (string) ($cell['r'] ?? '');
                $colLetters = preg_replace('/[0-9]/', '', $cellRef);
                $colIndex = !empty($colLetters) ? $this->colLettersToIndex($colLetters) : count($rowData);

                $cellType = (string) ($cell['t'] ?? '');
                $val = '';

                if ($cellType === 's') {
                    // Shared string index
                    $strIdx = (int) ((string) $cell->v);
                    $val = $sharedStrings[$strIdx] ?? '';
                } elseif ($cellType === 'inlineStr') {
                    $val = (string) ($cell->is->t ?? '');
                } else {
                    $val = (string) ($cell->v ?? '');
                }

                $rowData[$colIndex] = trim($val);
            }

            if (!empty($rowData)) {
                // Pastikan indeks array terurut rapi dari 0 tanpa celah kosong
                $maxCol = max(array_keys($rowData));
                $normalizedRow = [];
                for ($c = 0; $c <= $maxCol; $c++) {
                    $normalizedRow[$c] = $rowData[$c] ?? '';
                }
                $rows[] = $normalizedRow;
            }
        }

        return $rows;
    }

    /**
     * Memproses baris-baris hasil parsing untuk dimasukkan ke database dengan menjaga hierarki parent-child.
     */
    protected function processImportRows(array $rawRows, FuturePlan $futurePlan, string $mode): array
    {
        // 1. Ekstrak header dan pemetaan kolom
        $headerRow = array_shift($rawRows);
        $colMap = $this->mapColumns($headerRow);

        if (!isset($colMap['title'])) {
            throw new \InvalidArgumentException('Kolom "Nama Item" wajib ada pada file.');
        }

        // Mulai transaksi database agar atomik
        return DB::transaction(function () use ($rawRows, $futurePlan, $mode, $colMap) {
            if ($mode === 'replace') {
                $futurePlan->items()->delete();
            }

            // Dapatkan order awal tertinggi
            $currentMaxOrder = (int) $futurePlan->items()->max('order');

            // Struktur peta memori untuk menghubungkan parent_id
            $codeToId = [];
            $titleToId = [];
            $depthStack = []; // [depthLevel => itemId]

            // Jika mode append, daftarkan item yang sudah ada agar bisa dijadikan induk
            if ($mode !== 'replace') {
                $existingItems = $futurePlan->items()->get(['id', 'title', 'parent_id', 'order']);
                foreach ($existingItems as $ex) {
                    $normTitle = strtolower(trim($ex->title));
                    if (!isset($titleToId[$normTitle])) {
                        $titleToId[$normTitle] = $ex->id;
                    }
                }
            }

            $importedCount = 0;
            $rootCount = 0;
            $childCount = 0;

            foreach ($rawRows as $rowIndex => $cols) {
                // Ambil nilai per kolom
                $rawTitle = $cols[$colMap['title']] ?? '';
                if (trim($rawTitle) === '') {
                    // Lewati baris kosong tanpa nama item
                    continue;
                }

                // Hitung indentasi dari title jika ada (misal: "  Sub Item" atau "- Sub Item")
                $indentDepth = $this->detectIndentationDepth($rawTitle);
                $cleanTitle = $this->sanitizeTitle($rawTitle);

                $hierarchyCode = isset($colMap['no']) ? trim($cols[$colMap['no']] ?? '') : '';
                $quantity = isset($colMap['quantity']) ? $this->parseQuantity($cols[$colMap['quantity']] ?? 1) : 1;
                $unitPrice = isset($colMap['unit_price']) ? $this->parsePrice($cols[$colMap['unit_price']] ?? 0) : 0.0;
                $isCompleted = isset($colMap['is_completed']) ? $this->parseCompleted($cols[$colMap['is_completed']] ?? '') : false;
                $startDate = isset($colMap['start_date']) ? $this->parseDate($cols[$colMap['start_date']] ?? '') : null;
                $endDate = isset($colMap['end_date']) ? $this->parseDate($cols[$colMap['end_date']] ?? '') : null;
                $notes = isset($colMap['notes']) ? trim($cols[$colMap['notes']] ?? '') : null;
                $parentName = isset($colMap['parent_name']) ? trim($cols[$colMap['parent_name']] ?? '') : '';

                // Resolusi Parent ID
                $resolvedParentId = $this->resolveParentId(
                    $hierarchyCode,
                    $parentName,
                    $indentDepth,
                    $codeToId,
                    $titleToId,
                    $depthStack
                );

                $currentMaxOrder++;

                $newItem = FuturePlanItem::create([
                    'future_plan_id' => $futurePlan->id,
                    'parent_id'      => $resolvedParentId,
                    'title'          => $cleanTitle,
                    'quantity'       => $quantity,
                    'unit_price'     => $unitPrice,
                    'is_completed'   => $isCompleted,
                    'start_date'     => $startDate,
                    'end_date'       => $endDate,
                    'notes'          => !empty($notes) ? $notes : null,
                    'order'          => $currentMaxOrder,
                ]);

                // Simpan ke peta memori untuk baris-baris berikutnya
                if (!empty($hierarchyCode)) {
                    $codeToId[$hierarchyCode] = $newItem->id;
                }

                $normTitle = strtolower(trim($cleanTitle));
                if (!isset($titleToId[$normTitle])) {
                    $titleToId[$normTitle] = $newItem->id;
                }

                // Catat ke depth stack
                $derivedDepth = $this->calculateDepthFromCodeOrIndent($hierarchyCode, $indentDepth, $resolvedParentId !== null);
                $depthStack[$derivedDepth] = $newItem->id;

                // Hapus depth yang lebih dalam dari stack
                foreach (array_keys($depthStack) as $d) {
                    if ($d > $derivedDepth) {
                        unset($depthStack[$d]);
                    }
                }

                $importedCount++;
                if ($resolvedParentId === null) {
                    $rootCount++;
                } else {
                    $childCount++;
                }
            }

            return [
                'total_imported' => $importedCount,
                'root_items'     => $rootCount,
                'child_items'    => $childCount,
            ];
        });
    }

    /**
     * Memetakan header kolom dari file ke atribut FuturePlanItem.
     */
    protected function mapColumns(array $headers): array
    {
        $map = [];

        foreach ($headers as $index => $rawHeader) {
            $header = strtolower(trim((string) $rawHeader));
            $header = preg_replace('/[^a-z0-9]/', '', $header); // Buang simbol, spasi, kurung

            if (in_array($header, ['no', 'nomor', 'hierarki', 'hierarchy', 'kode', 'code', 'level', 'id'])) {
                if (!isset($map['no'])) $map['no'] = $index;
            } elseif (in_array($header, ['namaitem', 'nama', 'item', 'title', 'rincian', 'uraian', 'kebutuhan', 'task'])) {
                if (!isset($map['title'])) $map['title'] = $index;
            } elseif (in_array($header, ['jumlahqty', 'jumlah', 'qty', 'quantity', 'kuantitas', 'banyaknya', 'vol', 'volume'])) {
                if (!isset($map['quantity'])) $map['quantity'] = $index;
            } elseif (in_array($header, ['hargasatuanrp', 'hargasatuan', 'harga', 'unitprice', 'price', 'biaya', 'satuan', 'estimasiharga'])) {
                if (!isset($map['unit_price'])) $map['unit_price'] = $index;
            } elseif (in_array($header, ['statusselesai', 'status', 'selesai', 'iscompleted', 'completed', 'terbeli', 'sudahterbeli', 'done'])) {
                if (!isset($map['is_completed'])) $map['is_completed'] = $index;
            } elseif (in_array($header, ['tanggalmulai', 'tglmulai', 'startdate', 'start', 'mulai', 'waktumulai'])) {
                if (!isset($map['start_date'])) $map['start_date'] = $index;
            } elseif (in_array($header, ['tanggalselesai', 'tglselesai', 'enddate', 'finishdate', 'end', 'finish', 'selesaitarget', 'waktuselesai', 'targetselesai', 'deadline'])) {
                if (!isset($map['end_date'])) $map['end_date'] = $index;
            } elseif (in_array($header, ['catatanspesifikasi', 'catatan', 'spesifikasi', 'keterangan', 'notes', 'memo', 'spek'])) {
                if (!isset($map['notes'])) $map['notes'] = $index;
            } elseif (in_array($header, ['namainduk', 'induk', 'parent', 'parentname', 'indukitem', 'kategori'])) {
                if (!isset($map['parent_name'])) $map['parent_name'] = $index;
            }
        }

        return $map;
    }

    /**
     * Resolusi parent ID dengan multi-level fallback:
     * 1. Hierarki Code (e.g. 1.1.2 -> parent adalah 1.1)
     * 2. Nama Induk (e.g. "Mahar Pernikahan")
     * 3. Indentasi / Depth Stack
     */
    protected function resolveParentId(
        string $hierarchyCode,
        string $parentName,
        int $indentDepth,
        array $codeToId,
        array $titleToId,
        array $depthStack
    ): ?int {
        // 1. Cek dari Kode Hierarki (contoh: "1.2.3" -> induk "1.2")
        if (!empty($hierarchyCode) && str_contains($hierarchyCode, '.')) {
            $lastDotPos = strrpos($hierarchyCode, '.');
            if ($lastDotPos !== false) {
                $parentCode = substr($hierarchyCode, 0, $lastDotPos);
                if (isset($codeToId[$parentCode])) {
                    return $codeToId[$parentCode];
                }
            }
        }

        // 2. Cek dari Kolom Nama Induk
        if (!empty($parentName)) {
            $normParent = strtolower(trim($parentName));
            if (isset($titleToId[$normParent])) {
                return $titleToId[$normParent];
            }
            // Jika nama induk ternyata dimasukkan dalam bentuk kode nomor (misal "1" atau "1.1")
            if (isset($codeToId[$parentName])) {
                return $codeToId[$parentName];
            }
        }

        // 3. Cek dari Indentasi Simbol / Tab jika ada
        if ($indentDepth > 0 && isset($depthStack[$indentDepth - 1])) {
            return $depthStack[$indentDepth - 1];
        }

        return null;
    }

    /**
     * Hitung kedalaman dari kode numerik atau indentasi.
     */
    protected function calculateDepthFromCodeOrIndent(string $hierarchyCode, int $indentDepth, bool $hasParent): int
    {
        if (!empty($hierarchyCode) && str_contains($hierarchyCode, '.')) {
            return substr_count($hierarchyCode, '.');
        }

        if ($indentDepth > 0) {
            return $indentDepth;
        }

        return $hasParent ? 1 : 0;
    }

    /**
     * Deteksi kedalaman indentasi teks (misal: "  - Item", "> Sub Item", atau tab).
     */
    protected function detectIndentationDepth(string $text): int
    {
        $depth = 0;

        // Cek awalan tab atau 2-4 spasi
        if (preg_match('/^(\s+)/', $text, $matches)) {
            $spaceCount = strlen($matches[1]);
            $depth = max(1, (int) round($spaceCount / 2));
        }

        // Cek simbol bullet atau cabang
        $trimmed = ltrim($text);
        if (preg_match('/^([>\-↳*•]+)/', $trimmed, $bulletMatches)) {
            $bulletLen = strlen($bulletMatches[1]);
            $depth = max($depth, $bulletLen);
        }

        return $depth;
    }

    /**
     * Bersihkan karakter bullet atau indentasi dari nama item.
     */
    protected function sanitizeTitle(string $text): string
    {
        $clean = trim($text);
        // Hapus awalan bullet umum seperti "> ", "- ", "↳ ", "* ", "• "
        $clean = preg_replace('/^[>\-↳*•\s]+/u', '', $clean);
        return trim($clean) !== '' ? trim($clean) : trim($text);
    }

    /**
     * Normalisasi nilai quantity.
     */
    protected function parseQuantity(mixed $value): int
    {
        if (is_numeric($value)) {
            return max(1, (int) $value);
        }

        $str = preg_replace('/[^0-9]/', '', (string) $value);
        return !empty($str) ? max(1, (int) $str) : 1;
    }

    /**
     * Normalisasi nilai harga rupiah menjadi float bersih.
     */
    protected function parsePrice(mixed $value): float
    {
        if (is_numeric($value)) {
            return max(0.0, (float) $value);
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return 0.0;
        }

        // Buang "Rp", spasi, dan karakter non-angka kecuali koma dan titik
        $clean = preg_replace('/[^\d.,]/', '', $raw);

        // Kasus format Indonesia: "30.000.000,00" atau "30.000.000"
        if (substr_count($clean, '.') > 1) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } elseif (str_contains($clean, '.') && str_contains($clean, ',')) {
            if (strrpos($clean, ',') > strrpos($clean, '.')) {
                // "1.500,50" -> 1500.50
                $clean = str_replace('.', '', $clean);
                $clean = str_replace(',', '.', $clean);
            } else {
                // "1,500.50" -> 1500.50
                $clean = str_replace(',', '', $clean);
            }
        } elseif (str_contains($clean, ',')) {
            // "50000,00" -> 50000.00
            $clean = str_replace(',', '.', $clean);
        }

        return max(0.0, (float) $clean);
    }

    /**
     * Normalisasi status centang selesai/terbeli.
     */
    protected function parseCompleted(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $str = strtolower(trim((string) $value));
        return in_array($str, ['1', 'true', 'ya', 'yes', 'sudah', 'selesai', 'terbeli', 'lunas', 'v', 'x', 'check', 'done']);
    }

    /**
     * Normalisasi tanggal format Y-m-d atau null.
     */
    protected function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        // Jika Excel numeric date serial
        if (is_numeric($raw) && (int) $raw > 20000 && (int) $raw < 60000) {
            $unixTime = ((int) $raw - 25569) * 86400;
            return gmdate('Y-m-d', $unixTime);
        }

        try {
            return \Carbon\Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Konversi huruf kolom Excel (e.g. A, B, ..., Z, AA) menjadi indeks 0-based.
     */
    protected function colLettersToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $len = strlen($letters);
        $idx = 0;
        for ($i = 0; $i < $len; $i++) {
            $idx = $idx * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $idx - 1;
    }

    /**
     * Konversi indeks 0-based menjadi huruf kolom Excel (0 -> A, 27 -> AB).
     */
    protected function indexToColLetters(int $index): string
    {
        $col = '';
        while ($index >= 0) {
            $col = chr(($index % 26) + ord('A')) . $col;
            $index = intdiv($index, 26) - 1;
        }
        return $col;
    }

    /**
     * Membuat file .xlsx OpenXML yang valid dan rapi.
     */
    protected function generateXlsxFile(array $rows, string $sheetTitle = 'Future Plan'): string
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'fp_xlsx_');
        $zip = new ZipArchive();

        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file spreadsheet sementara.');
        }

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
        $zip->addFromString('_rels/.rels', $rootRels);

        // 3. xl/_rels/workbook.xml.rels
        $workbookRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);

        // 4. xl/workbook.xml
        $safeSheetName = htmlspecialchars(mb_substr($sheetTitle ?: 'Future Plan', 0, 31), ENT_XML1);
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbookXml);

        // 5. xl/styles.xml (Gaya Header & Baris)
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><name val="Calibri"/><sz val="11"/></font>
    <font><b/><name val="Calibri"/><sz val="11"/><color rgb="FF1F2937"/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFE5E7EB"/></left>
      <right style="thin"><color rgb="FFE5E7EB"/></right>
      <top style="thin"><color rgb="FFE5E7EB"/></top>
      <bottom style="thin"><color rgb="FFE5E7EB"/></bottom>
    </border>
  </borders>
  <cellXfs count="4">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="center" vertical="center"/>
    </xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1" applyAlignment="1">
      <alignment horizontal="right" vertical="center"/>
    </xf>
  </cellXfs>
</styleSheet>';
        $zip->addFromString('xl/styles.xml', $stylesXml);

        // 6. xl/worksheets/sheet1.xml
        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <cols>
    <col min="1" max="1" width="16" customWidth="1"/>
    <col min="2" max="2" width="38" customWidth="1"/>
    <col min="3" max="3" width="14" customWidth="1"/>
    <col min="4" max="4" width="20" customWidth="1"/>
    <col min="5" max="5" width="22" customWidth="1"/>
    <col min="6" max="6" width="16" customWidth="1"/>
    <col min="7" max="7" width="18" customWidth="1"/>
    <col min="8" max="8" width="18" customWidth="1"/>
    <col min="9" max="9" width="35" customWidth="1"/>
    <col min="10" max="10" width="28" customWidth="1"/>
  </cols>
  <sheetData>';

        // Baris 1: Header
        $sheetXml .= '<row r="1" ht="28" customHeight="1">';
        foreach (self::HEADERS as $colIdx => $headerText) {
            $cellRef = $this->indexToColLetters($colIdx) . '1';
            $safeText = htmlspecialchars($headerText, ENT_XML1);
            $sheetXml .= '<c r="' . $cellRef . '" s="1" t="inlineStr"><is><t>' . $safeText . '</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Baris 2+: Data
        $rowNum = 2;
        foreach ($rows as $item) {
            $sheetXml .= '<row r="' . $rowNum . '" ht="22" customHeight="1">';

            // A: No (Center)
            $sheetXml .= '<c r="A' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['no'] ?? ''), ENT_XML1) . '</t></is></c>';

            // B: Title (Left)
            $sheetXml .= '<c r="B' . $rowNum . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['title'] ?? ''), ENT_XML1) . '</t></is></c>';

            // C: Quantity (Center)
            $sheetXml .= '<c r="C' . $rowNum . '" s="2"><v>' . (int) ($item['quantity'] ?? 1) . '</v></c>';

            // D: Unit Price (Right)
            $sheetXml .= '<c r="D' . $rowNum . '" s="3"><v>' . (float) ($item['unit_price'] ?? 0) . '</v></c>';

            // E: Total (Right)
            $sheetXml .= '<c r="E' . $rowNum . '" s="3"><v>' . (float) ($item['total'] ?? 0) . '</v></c>';

            // F: Status (Center)
            $sheetXml .= '<c r="F' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['is_completed'] ?? 'Belum'), ENT_XML1) . '</t></is></c>';

            // G: Tanggal Mulai (Center)
            $sheetXml .= '<c r="G' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['start_date'] ?? ''), ENT_XML1) . '</t></is></c>';

            // H: Tanggal Selesai (Center)
            $sheetXml .= '<c r="H' . $rowNum . '" s="2" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['end_date'] ?? ''), ENT_XML1) . '</t></is></c>';

            // I: Notes (Left)
            $sheetXml .= '<c r="I' . $rowNum . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['notes'] ?? ''), ENT_XML1) . '</t></is></c>';

            // J: Parent (Left)
            $sheetXml .= '<c r="J' . $rowNum . '" s="0" t="inlineStr"><is><t>' . htmlspecialchars((string) ($item['parent_name'] ?? ''), ENT_XML1) . '</t></is></c>';

            $sheetXml .= '</row>';
            $rowNum++;
        }

        $sheetXml .= '</sheetData></worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        return $tempPath;
    }
}

