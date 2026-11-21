<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryRule;
use App\Models\CreditCard;
use App\Models\Debt;
use App\Models\FuturePlan;
use App\Models\RecurringTransaction;
use App\Models\Saving;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function download(Request $request): StreamedResponse
    {
        $user = $request->user();
        $userId = $user->id;

        $payload = [
            'exported_at'            => now()->toIso8601String(),
            'account'                => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email],
            'savings'                => Saving::where('user_id', $userId)->get()->toArray(),
            'categories'             => Category::where('user_id', $userId)->get()->toArray(),
            'category_rules'         => CategoryRule::where('user_id', $userId)->get()->toArray(),
            'transactions'           => Transaction::with('items')->where('user_id', $userId)->get()->toArray(),
            'budgets'                => Budget::with('items')->where('user_id', $userId)->get()->toArray(),
            'recurring_transactions' => RecurringTransaction::where('user_id', $userId)->get()->toArray(),
            'debts'                  => Debt::with('payments')->where('user_id', $userId)->get()->toArray(),
            'assets'                 => Asset::where('user_id', $userId)->get()->toArray(),
            'credit_cards'           => CreditCard::with('installments')->where('user_id', $userId)->get()->toArray(),
            'future_plans'           => FuturePlan::with(['items', 'contributions', 'activities'])
                ->where('user_id', $userId)
                ->get()
                ->toArray(),
        ];

        $filename = 'finansia-backup-' . now()->format('Y-m-d') . '.json';

        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json; charset=utf-8']);
    }

    /**
     * Dry-run preview file backup JSON sebelum dipulihkan ke akun pengguna.
     */
    public function previewRestore(Request $request): RedirectResponse
    {
        $request->validate([
            'backup_file' => 'required|file|max:10240',
        ]);

        $raw = file_get_contents($request->file('backup_file')->getRealPath());
        $decoded = json_decode((string) $raw, true);

        if (!is_array($decoded)) {
            return redirect()->route('profile.edit')->with('backup_error', 'File bukan JSON yang valid.');
        }

        $knownKeys = ['savings', 'categories', 'category_rules', 'transactions', 'assets', 'debts', 'credit_cards'];
        $hasAnyKey = collect($knownKeys)->contains(fn($k) => array_key_exists($k, $decoded) && is_array($decoded[$k]));

        if (!$hasAnyKey) {
            return redirect()->route('profile.edit')->with('backup_error', 'Struktur file backup Finansia tidak dikenali.');
        }

        $summary = [
            'exported_at'    => $decoded['exported_at'] ?? '-',
            'savings'        => count($decoded['savings'] ?? []),
            'categories'     => count($decoded['categories'] ?? []),
            'category_rules' => count($decoded['category_rules'] ?? []),
            'transactions'   => count($decoded['transactions'] ?? []),
            'assets'         => count($decoded['assets'] ?? []),
            'debts'          => count($decoded['debts'] ?? []),
            'credit_cards'   => count($decoded['credit_cards'] ?? []),
        ];

        $request->session()->put('backup_restore_payload', $decoded);
        $request->session()->put('backup_restore_summary', $summary);

        return redirect()->route('profile.edit')->with('backup_status', 'Preview backup siap! Periksa ringkasan di bawah lalu klik Konfirmasi Pulihkan.');
    }

    /**
     * Eksekusi pemulihan data dari payload JSON yang sudah dipreview (idempotent & aman).
     */
    public function executeRestore(Request $request): RedirectResponse
    {
        $payload = $request->session()->get('backup_restore_payload');
        if (!is_array($payload)) {
            return redirect()->route('profile.edit')->with('backup_error', 'Tidak ada data preview backup. Silakan unggah file JSON terlebih dahulu.');
        }

        $userId = (int) $request->user()->id;
        $restored = [
            'categories'     => 0,
            'savings'        => 0,
            'category_rules' => 0,
            'transactions'   => 0,
            'assets'         => 0,
            'debts'          => 0,
            'credit_cards'   => 0,
        ];

        DB::transaction(function () use ($payload, $userId, &$restored) {
            $categoryIdMap = [];
            foreach (($payload['categories'] ?? []) as $cat) {
                if (empty($cat['name'])) continue;
                $existing = Category::where('user_id', $userId)
                    ->where('name', $cat['name'])
                    ->where('type', $cat['type'] ?? 'Cash Out')
                    ->first();

                if ($existing) {
                    if (!empty($cat['id'])) $categoryIdMap[$cat['id']] = $existing->id;
                    continue;
                }

                $created = Category::create([
                    'user_id' => $userId,
                    'name'    => $cat['name'],
                    'type'    => $cat['type'] ?? 'Cash Out',
                    'color'   => $cat['color'] ?? 'blue',
                    'icon'    => $cat['icon'] ?? 'fa-tag',
                ]);
                if (!empty($cat['id'])) $categoryIdMap[$cat['id']] = $created->id;
                $restored['categories']++;
            }

            $savingIdMap = [];
            foreach (($payload['savings'] ?? []) as $sav) {
                if (empty($sav['name'])) continue;
                $existing = Saving::where('user_id', $userId)->where('name', $sav['name'])->first();
                if ($existing) {
                    if (!empty($sav['id'])) $savingIdMap[$sav['id']] = $existing->id;
                    continue;
                }

                $created = Saving::create([
                    'user_id'         => $userId,
                    'bank_id'         => $sav['bank_id'] ?? null,
                    'name'            => $sav['name'],
                    'account_number'  => $sav['account_number'] ?? '-',
                    'current_balance' => (float) ($sav['current_balance'] ?? 0),
                ]);
                if (!empty($sav['id'])) $savingIdMap[$sav['id']] = $created->id;
                $restored['savings']++;
            }

            foreach (($payload['category_rules'] ?? []) as $rule) {
                $mappedCatId = $categoryIdMap[$rule['category_id'] ?? null] ?? null;
                if (empty($rule['keyword']) || !$mappedCatId) continue;

                $exists = CategoryRule::where('user_id', $userId)
                    ->where('keyword', $rule['keyword'])
                    ->where('category_id', $mappedCatId)
                    ->exists();
                if ($exists) continue;

                CategoryRule::create([
                    'user_id'     => $userId,
                    'keyword'     => $rule['keyword'],
                    'match_type'  => $rule['match_type'] ?? 'contains',
                    'category_id' => $mappedCatId,
                    'priority'    => (int) ($rule['priority'] ?? 10),
                    'is_active'   => (bool) ($rule['is_active'] ?? true),
                ]);
                $restored['category_rules']++;
            }

            foreach (($payload['transactions'] ?? []) as $tx) {
                $mappedSavId = $savingIdMap[$tx['savings_id'] ?? null] ?? null;
                if (!$mappedSavId || empty($tx['description'])) continue;

                $txDate = substr((string) ($tx['transaction_date'] ?? now()->toDateString()), 0, 10);
                $amount = (float) ($tx['amount'] ?? 0);

                $exists = Transaction::where('user_id', $userId)
                    ->where('savings_id', $mappedSavId)
                    ->where('description', $tx['description'])
                    ->where('amount', $amount)
                    ->whereDate('transaction_date', $txDate)
                    ->exists();
                if ($exists) continue;

                Transaction::create([
                    'user_id'          => $userId,
                    'savings_id'       => $mappedSavId,
                    'category_id'      => $categoryIdMap[$tx['category_id'] ?? null] ?? null,
                    'type'             => $tx['type'] ?? 'Cash Out',
                    'direction'        => $tx['direction'] ?? null,
                    'description'      => $tx['description'],
                    'amount'           => $amount,
                    'fee'              => $tx['fee'] ?? null,
                    'transaction_date' => $txDate,
                    'is_manual'        => (bool) ($tx['is_manual'] ?? true),
                ]);
                $restored['transactions']++;
            }

            foreach (($payload['assets'] ?? []) as $asset) {
                if (empty($asset['name'])) continue;
                $exists = Asset::where('user_id', $userId)->where('name', $asset['name'])->exists();
                if ($exists) continue;

                Asset::create([
                    'user_id'       => $userId,
                    'name'          => $asset['name'],
                    'type'          => $asset['type'] ?? 'gold',
                    'unit'          => $asset['unit'] ?? 'gram',
                    'quantity'      => (float) ($asset['quantity'] ?? 0),
                    'purchase_price'=> (float) ($asset['purchase_price'] ?? 0),
                    'current_price' => (float) ($asset['current_price'] ?? 0),
                    'notes'         => $asset['notes'] ?? null,
                ]);
                $restored['assets']++;
            }

            foreach (($payload['debts'] ?? []) as $debt) {
                if (empty($debt['person_name'])) continue;
                $exists = Debt::where('user_id', $userId)
                    ->where('person_name', $debt['person_name'])
                    ->where('total_amount', (float) ($debt['total_amount'] ?? 0))
                    ->exists();
                if ($exists) continue;

                Debt::create([
                    'user_id'      => $userId,
                    'type'         => $debt['type'] ?? Debt::TYPE_PAYABLE,
                    'person_name'  => $debt['person_name'],
                    'description'  => $debt['description'] ?? null,
                    'total_amount' => (float) ($debt['total_amount'] ?? 0),
                    'paid_amount'  => (float) ($debt['paid_amount'] ?? 0),
                    'due_date'     => !empty($debt['due_date']) ? substr((string) $debt['due_date'], 0, 10) : null,
                    'status'       => $debt['status'] ?? Debt::STATUS_UNPAID,
                ]);
                $restored['debts']++;
            }

            foreach (($payload['credit_cards'] ?? []) as $cc) {
                if (empty($cc['name'])) continue;
                $exists = CreditCard::where('user_id', $userId)->where('name', $cc['name'])->exists();
                if ($exists) continue;

                $createdCard = CreditCard::create([
                    'user_id'         => $userId,
                    'name'            => $cc['name'],
                    'provider'        => $cc['provider'] ?? null,
                    'last_four'       => $cc['last_four'] ?? null,
                    'credit_limit'    => (float) ($cc['credit_limit'] ?? 0),
                    'current_balance' => (float) ($cc['current_balance'] ?? 0),
                    'billing_day'     => (int) ($cc['billing_day'] ?? 1),
                    'due_day'         => (int) ($cc['due_day'] ?? 15),
                    'is_active'       => (bool) ($cc['is_active'] ?? true),
                ]);

                foreach (($cc['installments'] ?? []) as $ins) {
                    if (empty($ins['title'])) continue;
                    $createdCard->installments()->create([
                        'user_id'        => $userId,
                        'title'          => $ins['title'],
                        'total_amount'   => (float) ($ins['total_amount'] ?? 0),
                        'monthly_amount' => (float) ($ins['monthly_amount'] ?? 0),
                        'total_months'   => (int) ($ins['total_months'] ?? 1),
                        'paid_months'    => (int) ($ins['paid_months'] ?? 0),
                        'start_date'     => !empty($ins['start_date']) ? substr((string) $ins['start_date'], 0, 10) : now()->toDateString(),
                        'status'         => $ins['status'] ?? 'active',
                    ]);
                }

                $restored['credit_cards']++;
            }
        });

        $request->session()->forget(['backup_restore_payload', 'backup_restore_summary']);

        $summaryText = collect($restored)
            ->filter(fn($v) => $v > 0)
            ->map(fn($v, $k) => "{$k}: {$v}")
            ->implode(', ');

        return redirect()->route('profile.edit')->with(
            'backup_status',
            $summaryText !== '' ? "Pemulihan selesai ({$summaryText})." : 'Semua data di file backup sudah ada di akun Anda (tidak ada duplikat ditambahkan).'
        );
    }
}
