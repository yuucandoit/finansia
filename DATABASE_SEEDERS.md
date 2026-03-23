# Database Seeders - Finansia

## 📋 Overview

Dokumentasi ini menjelaskan seeder-seeder yang telah dibuat untuk aplikasi Finansia.

## 🏦 Bank Seeder

**File:** `database/seeders/BankSeeder.php`

### Data yang Dibuat:
- **105 Bank di Indonesia** termasuk:
  - ✅ Bank BUMN (BRI, Mandiri, BNI, BNI Syariah, BTN)
  - ✅ Bank Swasta Nasional Top Tier (BCA, CIMB Niaga, BTPN, Jago, OCBC NISP, dll)
  - ✅ Bank Syariah (BSI, BRI Syariah, Mega Syariah, dll)
  - ✅ Bank Pembangunan Daerah/BPD (Bank BJB, DKI, Jateng, Jatim, dll)
  - ✅ Bank Asing (Standard Chartered, ICBC, Mizuho, dll)
  - ✅ Bank Digital (Blu BCA, Allo, Neo Commerce, Seabank, dll)
  - ✅ E-Wallet & Payment Gateway (GoPay, OVO, Dana, ShopeePay, LinkAja)

### Struktur Data:
```php
[
    'code' => '002',  // Kode bank
    'name' => 'Bank BRI (Bank Rakyat Indonesia)',
]
```

## 💰 Savings Seeder

**File:** `database/seeders/SavingSeeder.php`

### Data yang Dibuat:
**7 Rekening Tabungan** untuk user_id 1:

1. **Tabungan Utama BRI**
   - Account Number: Format BRI (0001-01-XXXXXX-30-3)
   - Balance: Rp 5,000,000

2. **Tabungan Mandiri**
   - Account Number: Format Mandiri (144-00-XXXXXXX-8)
   - Balance: Rp 3,500,000

3. **Jago Kantong Utama**
   - Account Number: Format Jago (100XXXXXXXXX)
   - Balance: Rp 2,500,000

4. **BCA Tahapan**
   - Account Number: 10 digit
   - Balance: Rp 7,500,000

5. **BNI Taplus**
   - Account Number: Format BNI (0XXXXXXXXX)
   - Balance: Rp 4,200,000

6. **Jago - Dana Darurat**
   - Account Number: Format Jago (100XXXXXXXXX)
   - Balance: Rp 10,000,000

7. **BRI Simpedes**
   - Account Number: Format BRI (0001-01-XXXXXX-30-3)
   - Balance: Rp 1,500,000

**Total Saldo:** Rp 34,200,000

## 💳 Transaction Seeder

**File:** `database/seeders/TransactionSeeder.php`

### Data yang Dibuat:
**30 Transaksi** dengan berbagai tipe:

#### Tipe Transaksi:
1. **Cash In** (Pemasukan)
   - Gaji Bulanan
   - Bonus Kinerja
   - Uang Saku
   - Freelance Project
   - Hasil Investasi
   - Pendapatan Passive
   - Nominal: Rp 1,000,000 - Rp 10,000,000

2. **Cash Out** (Pengeluaran)
   - Belanja Bulanan
   - Bayar Listrik
   - Bayar Internet
   - Makan di Restoran
   - Beli Keperluan
   - Bensin Motor
   - Subscription Netflix
   - Bayar Gym
   - Nominal: Rp 10,000 - Rp 500,000

3. **Transfer**
   - Transfer ke Tabungan
   - Transfer ke Keluarga
   - Bayar Utang Teman
   - Bayar Kartu Kredit
   - Nominal: Rp 100,000 - Rp 2,000,000

4. **Tabungan**
   - Tabungan Darurat
   - Tabungan Liburan
   - Tabungan Gadget Baru
   - Tabungan Pendidikan
   - Nominal: Rp 50,000 - Rp 1,000,000

### Transaction Items:
- **Cash Out Transactions:** 2-6 items
- **Other Transactions:** 1-3 items

## 🛒 Transaction Items Seeder

**File:** `database/factories/Transaction_ItemFactory.php`

### Data yang Dibuat:
Item-item belanja dengan 15 kategori:

#### Bahan Makanan:
- Beras (kg)
- Minyak Goreng (liter)
- Gula Pasir (kg)
- Telur (kg)
- Ayam (kg)
- Daging Sapi (kg)

#### Kebutuhan Rumah:
- Sabun Mandi (pcs)
- Shampo (botol)
- Pasta Gigi (pcs)
- Deterjen (kg)

#### Minuman & Makanan:
- Kopi (pack)
- Teh (pack)
- Susu (kotak)
- Roti (pack)
- Buah-buahan (kg)

### Fitur:
- Random quantity (1-10)
- Harga dinamis sesuai range pasar
- Random adjustment 0-10% untuk diskon/pajak
- Total dan Grand Total otomatis

## 🚀 Cara Menggunakan

### Jalankan Semua Seeder:
```bash
php artisan migrate:fresh --seed
```

### Jalankan Seeder Spesifik:
```bash
# Bank saja
php artisan db:seed --class=BankSeeder

# Savings saja
php artisan db:seed --class=SavingSeeder

# Transactions saja
php artisan db:seed --class=TransactionSeeder
```

## 📊 Hasil Akhir

Setelah menjalankan `php artisan migrate:fresh --seed`:
- ✅ 1 User
- ✅ 105 Banks
- ✅ 7 Savings accounts
- ✅ 30 Transactions
- ✅ ~90-150 Transaction Items (tergantung random)

## 🔧 Customization

### Menambah Bank:
Edit `database/seeders/BankSeeder.php` dan tambahkan di array `$banks`:
```php
['code' => 'XXX', 'name' => 'Nama Bank'],
```

### Menambah Savings Account:
Edit `database/seeders/SavingSeeder.php` dan tambahkan di array `$savings`

### Mengubah Jumlah Transaksi:
Edit `database/seeders/TransactionSeeder.php`:
```php
Transaction::factory()
    ->count(50)  // Ubah dari 30 ke 50
    ->create()
```

## 📝 Notes

- Semua data dibuat untuk `user_id = 1`
- Transaction date: 30 hari terakhir
- Account numbers dibuat dengan format realistis sesuai bank
- Nominal transaksi disesuaikan dengan tipe transaksi
