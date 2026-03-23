# 🧠 Decoder - Financial AI & Decoder

Modul ini berisi **decoder** untuk e-statement bank dan **AI helper** untuk analisis finansial menggunakan Large Language Model (LLM).

## 📦 Installation

1. **Activate virtual environment**:
```bash
cd decoder
source .venv/bin/activate
```

2. **Install dependencies**:
```bash
pip install -r requirements.txt
```

> **Note**: `llama-cpp-python` mungkin memerlukan waktu untuk compile. Jika ingin compile dengan GPU support, ikuti [dokumentasi official](https://github.com/abetlen/llama-cpp-python).

## 🔧 Usage

### 1. **Decoder** - Parse E-Statement Bank

```bash
python decoder.py <bank_type> <file_path> <password>
```

**Contoh**:
```bash
# Mandiri (encrypted)
python decoder.py mandiri /path/to/statement.xlsx mypassword123

# BRI (non-encrypted)
python decoder.py bri /path/to/statement.xlsx ""
```

**Supported Banks**:
- ✅ Mandiri (encrypted/non-encrypted)
- 🚧 BRI (in progress)
- 🚧 BCA (coming soon)

---

### 2. **AI Helper** - Financial Analysis

#### Test AI
```bash
python ai_helper.py test
```

#### Categorize Transaction
```bash
python ai_helper.py categorize "Bayar listrik PLN"
# Output: Utilitas & Tagihan

python ai_helper.py categorize "Beli kopi Starbucks"
# Output: Makanan & Minuman
```

#### Analyze Spending Pattern
```bash
python ai_helper.py analyze '[{"description":"Beli kopi","amount":50000},{"description":"Bensin motor","amount":100000}]'
```

---

## 🔗 Integration dengan Laravel

### Import E-Statement (TransactionController.php)

File `TransactionController.php` sudah menggunakan decoder:

```php
// Command yang dijalankan
$command = "{$venvPath} {$scriptPath} {$bankType} {$filePathEscaped} {$password}";
exec($command, $output, $returnCode);
```

### Integrate AI untuk Auto-Categorization

Tambahkan di `TransactionController.php` setelah import:

```php
// Setelah decode berhasil, gunakan AI untuk kategorisasi
$aiCommand = "{$venvPath} decoder/ai_helper.py categorize " . escapeshellarg($trans['description']);
exec($aiCommand, $aiOutput);
$category = trim(implode("", $aiOutput));

Transaction::create([
    'category' => $category,  // Auto-categorized by AI
    // ... field lainnya
]);
```

---

## 🤖 AI Features

`FinancialAI` class menyediakan:

1. **`categorize_transaction(description)`**
   - Auto-categorize transaksi
   - Return: kategori (e.g., "Makanan & Minuman")

2. **`analyze_spending_pattern(transactions_json)`**
   - Analisis pola pengeluaran
   - Return: insight & recommendations

3. **`suggest_budget(monthly_income, transactions_json)`**
   - Saran budget allocation (50/30/20 rule)
   - Return: budget plan

---

## 📝 Example Integration (Python)

```python
from ai_helper import FinancialAI

# Initialize AI
ai = FinancialAI()

# Categorize single transaction
category = ai.categorize_transaction("Bayar listrik PLN")
print(category)  # "Utilitas & Tagihan"

# Analyze spending
transactions = [
    {"description": "Kopi", "amount": 50000},
    {"description": "Bensin", "amount": 100000}
]
analysis = ai.analyze_spending_pattern(json.dumps(transactions))
print(analysis)
```

---

## 🛠 Troubleshooting

### Error: "Document is not encrypted"
✅ **Fixed!** Decoder sekarang bisa handle file encrypted & non-encrypted.

### llama-cpp-python installation failed
```bash
# Try with specific version
pip install llama-cpp-python==0.2.77

# Or build from source with CUDA (for GPU)
CMAKE_ARGS="-DLLAMA_CUBLAS=on" pip install llama-cpp-python
```

### Model download too slow
Model akan di-download otomatis dari HuggingFace. Jika lambat:
- Download manual dari: https://huggingface.co/LiquidAI/LFM2.5-1.2B-Instruct-GGUF
- Simpan di folder `decoder/models/`
- Update `ai_helper.py` untuk load dari local path

---

## 📊 Future Enhancements

- [ ] Support BCA & BRI decoder
- [ ] Advanced spending insights with charts
- [ ] Budget tracking & alerts
- [ ] Multi-language support
- [ ] Larger LLM models for better accuracy
- [ ] Fine-tune model dengan Indonesian financial data

---

## 📄 License

Part of Finansia Application
