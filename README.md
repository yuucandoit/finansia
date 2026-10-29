<div align="center">
  <img src="public/assets/logo/logofinansiabaru_512x512.png" alt="Finansia Logo" width="140" style="border-radius: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 12px;">

  # Finansia
  ### *Smart Personal Finance & AI Financial Assistant*

  <p align="center">
    Aplikasi manajemen keuangan pribadi modern berbasis <strong>Laravel 13</strong>, <strong>Tailwind CSS</strong>, <strong>PostgreSQL</strong>, dan <strong>OpenRouter AI</strong>.<br>
    Dilengkapi pencatatan transaksi percakapan otomatis, analisis finansial cerdas, serta dekripsi e-statement mutasi bank resmi.
  </p>

  <p align="center">
    <a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-13.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 13"></a>
    <a href="https://php.net"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+"></a>
    <a href="https://www.postgresql.org"><img src="https://img.shields.io/badge/PostgreSQL-5432-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL"></a>
    <a href="https://tailwindcss.com"><img src="https://img.shields.io/badge/Tailwind_CSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS"></a>
    <a href="https://www.python.org"><img src="https://img.shields.io/badge/Python-3.10+-3776AB?style=for-the-badge&logo=python&logoColor=white" alt="Python"></a>
    <a href="https://openrouter.ai"><img src="https://img.shields.io/badge/OpenRouter-AI_Engine-6366F1?style=for-the-badge&logo=openai&logoColor=white" alt="OpenRouter AI"></a>
  </p>
</div>

---

## 📌 Daftar Isi
- [Tentang Finansia](#-tentang-finansia)
- [Fitur Utama](#-fitur-utama)
  - [1. Asisten Keuangan AI](#1--asisten-keuangan-ai-interactive-ai-chatbot)
  - [2. Dashboard Finansia Interaktif](#2--dashboard-finansia-interaktif)
  - [3. Manajemen Tabungan & Multi-Rekening](#3--manajemen-tabungan--multi-rekening-saving)
  - [4. Pencatatan & Manajemen Transaksi](#4--pencatatan--manajemen-transaksi)
  - [5. Smart E-Statement Decoder & Deteksi Duplikasi](#5--smart-e-statement-decoder--anti-duplicate-transaction-handler)
  - [6. Future Planner & Hierarchical Task Items](#6--future-planner-rencana-masa-depan--spreadsheet-outline-task-tree)
  - [7. Perencanaan Anggaran Bulanan (Budgeting)](#7--perencanaan-anggaran-bulanan-budgeting)
  - [8. Autentikasi & Keamanan Pengguna](#8--autentikasi--keamanan-pengguna)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Prasyarat Sistem](#-prasyarat-sistem)
- [Panduan Instalasi](#-panduan-instalasi)
  - [1. Clone Repository & Install Dependencies](#1-clone-repository--install-dependencies)
  - [2. Konfigurasi Environment (.env)](#2-konfigurasi-environment-env)
  - [3. Setup Python Virtual Environment (Decoder)](#3-setup-python-virtual-environment-decoder)
  - [4. Migrasi & Seed Database](#4-migrasi--seed-database)
  - [5. Build Asset Frontend](#5-build-asset-frontend)
  - [6. Menjalankan Aplikasi](#6-menjalankan-aplikasi)
- [Struktur Proyek](#-struktur-proyek)
- [Konfigurasi AI & OpenRouter](#-konfigurasi-ai--openrouter)

---

## 💡 Tentang Finansia

**Finansia** dirancang untuk mempermudah siapa saja dalam memonitor dan mengelola kesehatan finansial harian. Berbeda dari aplikasi keuangan konvensional yang merepotkan saat input data, Finansia menyediakan **Asisten AI** yang memungkinkan pengguna mencatat transaksi hanya dengan mengetik bahasa sehari-hari secara santai (misal: *"aku abis beli kopi seharga 25ribu"* atau *"barusan makan ramen 45rb pake GoPay"*), bertanya tentang kondisi pengeluaran (*"bulan ini jajan makan udah habis berapa?"*), serta mengimpor mutasi rekening berformat Excel resmi dari berbagai bank di Indonesia tanpa repot membuka proteksi sandi secara manual.

---

## ✨ Fitur Utama

### 1. 🤖 Asisten Keuangan AI (*Interactive AI Chatbot*)
* **Floating Widget Kanan Bawah**: Komponen obrolan mengambang yang bersih, dapat dibuka/tutup dengan mudah melalui *Floating Action Button (FAB)*.
* **Pencatatan Percakapan Cerdas**: Cukup ketik transaksi dalam gaya bahasa santai, AI otomatis mengekstrak:
  * **Jenis Transaksi**: Pemasukan (*Cash In*) atau Pengeluaran (*Cash Out*).
  * **Nominal Uang**: Mendeteksi ragam penulisan nominal (`25ribu`, `45rb`, `30k`, `1.5jt`, `50.000`).
  * **Deskripsi Otomatis**: Menghasilkan catatan transaksi yang bersih (misal: *"aku abis beli kopi 25rb"* $\rightarrow$ **`Beli Kopi`**).
  * **Pencocokan Rekening**: Menghubungkan secara otomatis ke rekening tabungan pengguna yang terdaftar di database.
  * **Pencocokan Kategori**: Mengelompokkan transaksi ke kategori relevan (*Makanan & Minuman*, *Transportasi*, *Belanja*, dll.).
* **Tanya Jawab Finansial (*Financial Context Q&A*)**:
  * Pengguna dapat bertanya mengenai kondisi keuangannya, contoh: *"Bulan ini jajan makan udah habis berapa?"* atau *"Berapa sisa kas bulan ini?"*.
  * Asisten menjawab berdasarkan **snapshot data transaksi riil** pengguna secara akurat dan to-the-point.
* **Validasi Konfirmasi Pengguna**: Sebelum data masuk ke database, AI menampilkan kartu konfirmasi dengan dropdown rekening dan input deskripsi yang bisa diedit langsung. Pengguna memiliki kendali penuh:
  * Tombol **`[Batal]`**: Transaksi tidak akan disimpan ke database.
  * Tombol **`[Konfirmasi & Simpan]`**: Transaksi disimpan ke tabel `transactions` dan saldo rekening otomatis disesuaikan secara atomik.
* **Dual-Engine (OpenRouter + Smart Local Heuristic)**: Jika koneksi API eksternal sedang lambat atau kuota habis, parser heuristik lokal bahasa Indonesia akan otomatis mengambil alih sehingga aplikasi tetap 100% andal.

### 2. 📊 Dashboard Finansia Interaktif
* **Ringkasan Saldo & Arus Kas**:
  * Total Saldo Gabungan (*Running Balance* seluruh rekening).
  * Total Pemasukan (*Cash In*) bulan berjalan.
  * Total Pengeluaran (*Cash Out*) bulan berjalan.
* **3 Grafik Data Real-Time (Chart.js)**:
  1. **Cash Flow Bar Chart**: Grafik batang perbandingan harian arus kas masuk (hijau) vs keluar (merah) 7 hari terakhir.
  2. **Top 5 Expense Donut Chart**: Proporsi pengeluaran per kategori bulan ini lengkap dengan persentase.
  3. **30-Day Balance Trend Line Chart**: Grafik tren saldo riil harian selama 30 hari terakhir dengan indikator arah pergerakan.
* **Aktivitas Terbaru**: Daftar 5 transaksi terakhir lengkap dengan nominal Rupiah dan timestamp.

### 3. 💳 Manajemen Tabungan & Multi-Rekening (*Saving*)
* **CRUD Lengkap Rekening**: Tambah rekening, lihat daftar berbentuk kartu modern, ubah data, dan hapus rekening dengan modal konfirmasi aman.
* **105+ Master Data Bank**: Terintegrasi daftar bank BUMN, Swasta, Syariah, BPD, Bank Digital (Jago, Blu, Seabank), serta E-Wallet (GoPay, OVO, Dana, ShopeePay, LinkAja).
* **Halaman Detail Rekening (`/saving/detail/{id}`)**:
  * Filter riwayat transaksi per tahun.
  * Ringkasan tahunan (*Total Income* vs *Total Spending*).
  * Grafik **Tren Saldo Jan - Des** dan grafik **Pemasukan vs Pengeluaran**.
  * **Accordion Mutasi Bulanan**: Menampilkan rincian transaksi per bulan via API internal, lengkap dengan highlight transaksi terbesar (*Top Income* & *Top Spending*).

### 4. 📝 Pencatatan & Manajemen Transaksi
* **Pencatatan Fleksibel**: Mendukung 4 jenis transaksi: **Cash In**, **Cash Out**, **Transfer Antar Rekening**, dan **Tabungan**.
* **Pindah Dana (Transfer)**: Otomatis memotong saldo rekening asal dan menambah rekening tujuan secara bersamaan dalam satu transaksi database atomik (`DB::transaction`).
* **Penyaringan & Pencarian**: Tab filter cepat (*Semua*, *Pemasukan*, *Pengeluaran*, *Tabungan*), pencarian teks/nominal, serta paginasi 15 transaksi per halaman.

### 5. 📥 Smart E-Statement Decoder & Anti-Duplicate Transaction Handler
* **Dukungan Format Excel & PDF**: Mampu mengimpor mutasi rekening elektronik resmi berformat `.xlsx`, `.xls`, dan `.pdf`.
* **Dukungan Password E-Statement**: Mendekripsi file e-statement bank yang terproteksi sandi secara otomatis.
* **Multi-Bank Parser**:
  * Bank Mandiri (Livin')
  * Bank BRI (BRImo)
  * Bank BCA
  * Bank Jago & E-Wallet
* **Engine Deteksi Transaksi Duplikat Cerdas (`DuplicateTransactionHandler`)**:
  * Otomatis memindai kecocokan nama/deskripsi transaksi, nominal, tanggal, dan rekening bank yang sama pada data mutasi yang diimpor.
  * Menampilkan banner peringatan duplikasi, visual highlight amber, badge `⚠️ Duplikat`, dan filter tab khusus `⚠️ Duplikat`.
  * Tombol instan **`[Pilih Hanya Transaksi Baru]`** untuk membatalkan pilihan (*uncheck*) seluruh transaksi yang sudah pernah tercatat di database, mencegah risiko *double counting* saldo tabungan.

### 6. 🎯 Future Planner (Rencana Masa Depan & Spreadsheet Outline Task Tree)
* **Master Future Plan**: Buat rencana finansial atau proyek masa depan (misal: *"Setup Ruang Kerja 2027"*, *"Liburan ke Jepang"*, *"Renovasi Rumah"*), lengkap dengan deskripsi, target tanggal selesai, status, dan label warna.
* **Kolaborasi Multi-Pengguna & Kode Unik (Share Code)**:
  * Setiap rencana otomatis memiliki kode unik unik 6-karakter (misal: **`FP-A8B9C1`**) untuk dibagikan ke anggota tim atau keluarga.
  * Tombol **`[🔗 Join dengan Kode]`** di dashboard memungkinkan pengguna lain bergabung ke rencana kolaborasi secara instan melalui input kode unik atau link undangan langsung.
  * **Undang Anggota Langsung**: Pemilik dapat menambahkan pengguna lain secara langsung via email terdaftar dengan pilihan peran (*Editor* atau *Viewer*).
* **Indikator Pengguna Aktif Real-Time (Online Presence)**:
  * Di pojok kanan atas halaman rencana, terdapat widget status kehadiran aktif yang menampilkan avatar berbentuk **lingkaran dengan huruf pertama nama pengguna** (inisial kapital).
  * Dilengkapi titik hijau berkedip (*live pulsing dot*) dan tooltip detail nama/peran untuk mengetahui siapa saja yang sedang membuka dan mengedit rencana secara bersamaan.
  * Sinkronisasi otomatis di latar belakang melalui mekanisme *heartbeat polling interval* (setiap 20 detik).
* **Breakdown Item Bertingkat (Parent-Child WBS Tree)**:
  * Struktur tugas dan kebutuhan bercabang bertingkat tanpa batas (Level 1, Level 2, Level 3).
  * Dilengkapi konektor visual pohon (`└──`, `│ └──`), badge level, dan tombol lipat/buka cabang (*expand/collapse*).
* **Input Baris Langsung (Inline Row Entry)**:
  * Menambahkan item kebutuhan langsung pada baris tabel layaknya di **Notion** atau **Excel**—tanpa perlu membuka pop-up modal formulir terpisah.
  * Cukup ketik nama item, kuantitas (Qty), harga satuan, dan tekan **Enter** pada keyboard untuk menyimpan seketika.
* **Rollup Biaya Otomatis**:
  * Subtotal pada baris item induk (*parent*) secara otomatis merupakan akumulasi SUM dari seluruh sub-cabang di bawahnya (*effective subtotal*).
* **Navigasi Cabang 1-Klik**:
  * Tombol **`+ Sub`**: Membuka baris input anak tepat di bawah item induk yang dipilih.
  * Tombol **`↳ Sub` (Indent)**: 1-klik mengubah item menjadi sub-cabang dari baris di atasnya.
  * Tombol **`↰ Utama` (Outdent)**: 1-klik menaikkan tingkat cabang kembali ke level utama (*root*).
  * Tombol **`✎ Edit`**: Mengubah baris tabel menjadi mode input sel langsung untuk revisi cepat.
* **Checklist Pembelian Real-Time (AJAX)**:
  * Centang kotak (*checkbox*) pada item yang telah terbeli: teks otomatis diberi efek coret (*strike-through*) dan metrik realisasi terbarui tanpa *reload* halaman.
* **Baris Total Keseluruhan (Sticky Grand Total)**:
  * Menampilkan akumulasi total biaya keseluruhan rencana, total unit barang (Qty), perbandingan dana yang sudah terbeli vs sisa kebutuhan dana, serta persentase progres pemenuhan.
* **Mode Tampilan Widescreen (Full Width)**:
  * Tampilan default membentang penuh di seluruh layar monitor widescreen untuk pengalaman spreadsheet yang lapang, dilengkapi tombol toggle *Mode Full Width / Mode Boxed*.
* **REST API Lengkap**: Terintegrasi endpoint `/api/v1/future-planner` lengkap dengan aksi kolaborasi (`join`, `heartbeat`, `members`) yang siap dihubungkan ke aplikasi mobile (Flutter / Kotlin Multiplatform).

### 7. 📊 Perencanaan Anggaran Bulanan (Budgeting)
* **Alokasi Pos Anggaran**: Tentukan batas pengeluaran per pos atau kategori bulanan (kebutuhan primer, hiburan, investasi, dll.).
* **Integrasi AI Multi-Budget Parsing**: Mampu mengenali daftar anggaran sekaligus dari teks percakapan biasa dan menyimpannya secara otomatis.
* **Monitoring Realisasi Anggaran**: Tampilan progress bar interaktif dengan indikator visual jika alokasi pengeluaran mendekati atau melampaui batas (*overbudget*).

### 8. 🔐 Autentikasi & Keamanan Pengguna
* Sistem autentikasi lengkap dengan Laravel Breeze (Login, Register, Logout, Reset Password).
* Manajemen Profil pengguna (ubah nama, email, dan hapus akun).
* **Multi-Tenant Data Isolation**: Seluruh data rekening, mutasi, anggaran, dan future plan terisolasi secara ketat berdasarkan `user_id` milik pengguna yang sedang login.
* **Role Master Mind / Admin**: Fitur persetujuan pengguna baru (*User Approvals*) khusus administrator terotorisasi.

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
| :--- | :--- |
| **Backend Framework** | [Laravel 13.x](https://laravel.com/) (PHP 8.2+) |
| **Database** | [PostgreSQL](https://www.postgresql.org/) (dengan support soft deletes & transactions) |
| **Frontend UI** | Blade Templating, [Tailwind CSS](https://tailwindcss.com/), [Vite](https://vitejs.dev/) |
| **Interaktivitas UI** | Vanilla JS, [Alpine.js](https://alpinejs.dev/), jQuery, [Chart.js](https://www.chartjs.org/) |
| **AI / LLM Service** | [OpenRouter API](https://openrouter.ai/) (Model: `nex-agi/nex-n2.5-mini:free`, `inclusionai/ling-3.0-flash-fin:free`) |
| **Decoder Engine** | Python 3 (`pandas`, `msoffcrypto-tool`, `openpyxl`) |
| **Application Server** | Laravel Built-in / [FrankenPHP Octane](https://frankenphp.dev/) |

---

## 📋 Prasyarat Sistem

Pastikan perangkat Anda telah terpasang:
* **PHP >= 8.2** dengan ekstensi `pdo_pgsql`, `pgsql`, `curl`, `mbstring`, `openssl`, `bcmath`, `fileinfo`.
* **Composer >= 2.x**
* **Node.js >= 18.x** & **NPM**
* **PostgreSQL >= 14**
* **Python >= 3.10**

---

## 🚀 Panduan Instalasi

### 1. Clone Repository & Install Dependencies
```bash
# Clone repository
git clone https://github.com/wahyusnjy/finansia.git
cd finansia

# Install dependency PHP
composer install

# Install dependency Node.js
npm install
```

### 2. Konfigurasi Environment (.env)
Salin berkas `.env.example` menjadi `.env` dan generate application key:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan pengaturan database dan AI pada berkas `.env`:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=finansia
DB_USERNAME=postgres
DB_PASSWORD=secret

# Konfigurasi AI OpenRouter
OPENROUTER_API_KEY=sk-or-v1-your-api-key-here
OPENROUTER_PRIMARY_MODEL=nex-agi/nex-n2.5-mini:free
OPENROUTER_FALLBACK_MODEL=inclusionai/ling-3.0-flash-fin:free
```

### 3. Setup Python Virtual Environment (Decoder)
Layanan dekripsi file Excel bank membutuhkan environment Python:
```bash
# Masuk ke direktori decoder
cd decoder

# Buat virtual environment
python3 -m venv .venv

# Aktifkan virtual environment
# macOS/Linux:
source .venv/bin/activate
# Windows:
# .venv\Scripts\activate

# Install dependensi decoder
pip install -r requirements.txt

# Kembali ke root project
cd ..
```

### 4. Migrasi & Seed Database
Jalankan migrasi database beserta seeder bawaan (Bank, Kategori, Rekening Contoh, dan Transaksi):
```bash
php artisan migrate --seed
```

> **Catatan**: Seeder akan membuat 105 data Bank, 12 Kategori, rekening tabungan awal, dan data mutasi transaksi pengujian untuk akun pengguna utama (`user_id = 1`).

### 5. Build Asset Frontend
Kompilasi CSS Tailwind dan JavaScript menggunakan Vite:
```bash
# Mode development (live reload)
npm run dev

# Atau mode production
npm run build
```

### 6. Menjalankan Aplikasi
Jalankan server aplikasi Laravel:
```bash
# Opsi 1: Menggunakan server PHP Artisan bawaan
php artisan serve

# Opsi 2: Menggunakan FrankenPHP Octane (High Performance)
php artisan octane:start --server=frankenphp
```

Buka peramban Anda di `http://127.0.0.1:8000`.

---

## 📁 Struktur Proyek

```plaintext
finansia/
├── app/
│   ├── Http/Controllers/
│   │   ├── AiChatController.php          # Controller router AI (Q&A, parsing anggaran, transaksi)
│   │   ├── BudgetController.php          # Controller pos anggaran bulanan
│   │   ├── DashboardController.php       # Controller ringkasan & chart dashboard
│   │   ├── FuturePlanController.php      # Controller Future Planner & inline row items
│   │   ├── SavingController.php          # Controller CRUD & detail tabungan
│   │   ├── TransactionController.php     # Controller transaksi & import statement
│   │   └── Api/
│   │       ├── ...
│   │       └── FuturePlanController.php  # REST API Future Planner untuk aplikasi mobile
│   ├── Models/
│   │   ├── Bank.php                      # Model master data bank
│   │   ├── Budget.php                    # Model pos anggaran pengeluaran
│   │   ├── Category.php                  # Model kategori transaksi
│   │   ├── FuturePlan.php                # Model master rencana masa depan
│   │   ├── FuturePlanItem.php            # Model item bertingkat (parent-child WBS tree)
│   │   ├── Saving.php                    # Model rekening tabungan
│   │   ├── Transaction.php               # Model mutasi transaksi keuangan
│   │   └── User.php                      # Model pengguna aplikasi
│   └── Services/
│       ├── DuplicateTransactionHandler.php # Engine pendeteksi transaksi duplikat import
│       ├── FinancialContextService.php   # Service snapshot keuangan untuk AI
│       ├── OpenRouterService.php         # Service OpenRouter LLM + Fallback NLP
│       └── PdfImportService.php          # Service parser e-statement PDF bank
├── config/
│   └── services.php                      # Konfigurasi integrasi third-party & OpenRouter
├── database/
│   ├── migrations/                       # Berkas migrasi database PostgreSQL
│   └── seeders/                          # Master seeder (Bank, Category, Saving, Transaction)
├── decoder/
│   ├── .venv/                            # Python virtual environment
│   ├── decoder.py                        # Script decoder & decryptor Excel e-statement
│   ├── pdf_extractor.py                  # Script ekstraksi mutasi e-statement PDF
│   └── requirements.txt                  # Dependensi Python (pandas, msoffcrypto, pypdf)
├── resources/
│   ├── css/                              # Berkas CSS & Tailwind
│   ├── js/                               # Berkas JavaScript & Alpine/jQuery setup
│   └── views/
│       ├── components/
│       │   └── ai-chatbot.blade.php      # Komponen UI Chatbot AI mengambang
│       ├── dashboard.blade.php           # Tampilan dashboard keuangan interaktif
│       ├── future-planner/
│       │   ├── index.blade.php           # Tampilan daftar kartu rencana & metrik KPI
│       │   └── show.blade.php            # Tampilan spreadsheet outline & task tree inline
│       ├── saving.blade.php              # Tampilan daftar rekening & modal CRUD
│       ├── saving_detail.blade.php       # Tampilan rincian & grafik rekening
│       └── transaction.blade.php         # Tampilan tabel transaksi & modal import
└── routes/
    ├── api.php                           # Endpoint API REST v1
    └── web.php                           # Rute web aplikasi utama
```

---

## 🧠 Konfigurasi AI & OpenRouter

Finansia menggunakan **OpenRouter API** untuk memproses pemahaman bahasa alami (*Natural Language Understanding*).

### Model yang Direkomendasikan
| Tipe | Rekomendasi Model | Keunggulan |
| :--- | :--- | :--- |
| **Primary** | `nex-agi/nex-n2.5-mini:free` | Sangat cepat (respon < 0.6s), akurat mengenali format JSON. |
| **Fallback** | `inclusionai/ling-3.0-flash-fin:free` | Model finansial berkecepatan tinggi, akurat untuk perhitungan nominal uang. |

### Mekanisme Anti-Gagal (*Fallback Resilience*)
Jika OpenRouter mengalami gangguan upstream (*rate limit* / 429 atau kuota habis), Finansia secara otomatis beralih ke **Intelligent Local Indonesian Parser** di `OpenRouterService.php`. Parser ini menggunakan ekspresi reguler cerdas untuk membersihkan kata santai, mendeteksi nominal (`25ribu`, `45rb`, `30k`), mencocokkan rekening pengguna, dan merangkum deskripsi tanpa menimbulkan kegagalan pada antarmuka pengguna.

---

## 🤝 Kontribusi

Kontribusi selalu diterima dengan senang hati!
1. Fork repository ini.
2. Buat branch fitur baru (`git checkout -b feature/FiturKeren`).
3. Commit perubahan Anda (`git commit -m 'Menambahkan fitur keren'`).
4. Push ke branch (`git push origin feature/FiturKeren`).
5. Buat Pull Request baru.

---

## 📄 Lisensi

Aplikasi ini dilisensikan di bawah lisensi terbuka [MIT License](LICENSE).
Semua hak cipta dan merek dagang adalah milik masing-masing pemiliknya.
