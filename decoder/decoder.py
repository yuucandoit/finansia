import sys
import pandas as pd
import msoffcrypto
import io
import json

def decrypt_file(file_path, password):
    """Fungsi umum untuk membuka proteksi file Excel.
    Akan mencoba dekripsi terlebih dahulu. Jika file tidak ter-enkripsi,
    akan langsung membaca file dengan pandas.
    """
    try:
        # Coba dekripsi file terlebih dahulu
        decrypted_file = io.BytesIO()
        with open(file_path, "rb") as f:
            file = msoffcrypto.OfficeFile(f)
            file.load_key(password=password)
            file.decrypt(decrypted_file)
            decrypted_file.seek(0)
            return pd.read_excel(decrypted_file)
    except Exception as e:
        # Jika error "Document is not encrypted", maka file tidak ter-enkripsi
        if "not encrypted" in str(e).lower():
            # Langsung baca file tanpa dekripsi
            return pd.read_excel(file_path)
        else:
            # Jika error lain (misal password salah), raise exception
            raise

def handle_mandiri(df):
    df_cleaned = df.iloc[16:].copy()

    # 2. Beri nama kolom temporary
    df_cleaned.columns = [
        'No', 'C1', 'C2', 'C3', 'Tanggal_Jam', 'C4', 'C5', 
        'Keterangan', 'C6', 'C7', 'C8', 'C9', 'C10', 'C11', 
        'C12', 'Dana_Masuk', 'C13', 'C14', 'Dana_Keluar', 'C15', 
        'C16', 'Saldo'
    ]

    # 3. Ambil kolom yang dibutuhkan saja
    df_useful = df_cleaned[['Tanggal_Jam', 'Keterangan', 'Dana_Masuk', 'Dana_Keluar', 'Saldo']].copy()

    # 4. Logic Forward Fill untuk Tanggal
    # Kita tandai baris yang berisi JAM (WIB), selain itu adalah TANGGAL
    df_useful['is_jam'] = df_useful['Tanggal_Jam'].str.contains('WIB', na=False)
    df_useful['tanggal'] = df_useful['Tanggal_Jam'].where(~df_useful['is_jam']).ffill()

    # 5. Bersihkan format angka (Mandiri pakai titik ribuan dan koma desimal)
    for col in ['Dana_Masuk', 'Dana_Keluar', 'Saldo']:
        df_useful[col] = df_useful[col].astype(str).str.replace('.', '').str.replace(',', '.')
        df_useful[col] = pd.to_numeric(df_useful[col], errors='coerce').fillna(0)

    # 6. Filter: Hanya ambil baris yang ada nominal uangnya (membuang baris jam saja)
    df_final = df_useful[(df_useful['Dana_Masuk'] > 0) | (df_useful['Dana_Keluar'] > 0)].copy()

    # 7. Merapikan Keterangan (buang newline \n agar JSON rapi)
    df_final['Keterangan'] = df_final['Keterangan'].str.replace('\n', ' ', regex=False).str.strip()

    # 8. Pilih & Rename kolom sesuai kebutuhan DB Finansia
    result = df_final[['tanggal', 'Keterangan', 'Dana_Masuk', 'Dana_Keluar', 'Saldo']].copy()
    result.columns = ['date', 'description', 'amount_in', 'amount_out', 'balance']

    return result

def handle_bri(df):
    # 1. Bersihkan spasi pada nama kolom (antisipasi bad formatting)
    df.columns = [str(col).strip() for col in df.columns]
    
    # 2. BRImo CSV biasanya langsung memiliki header di baris pertama.
    # Kita buat mapping berdasarkan nama kolom eksak di file Anda.
    
    # Pilih deskripsi yang paling lengkap (REMARK_CUSTOM di BRI paling informatif)
    desc_col = 'REMARK_CUSTOM' if 'REMARK_CUSTOM' in df.columns else 'DESK_TRAN'
    
    mapping = {
        'TGL_TRAN': 'date',
        desc_col: 'description',
        'MUTASI_KREDIT': 'amount_in',
        'MUTASI_DEBET': 'amount_out',
        'SALDO_AKHIR_MUTASI': 'balance'
    }
    
    # 3. Cek apakah kolom-kolom di atas ada di DataFrame
    available_cols = [c for c in mapping.keys() if c in df.columns]
    
    # Ambil hanya kolom yang kita butuhkan saja
    new_df = df[available_cols].copy()
    
    # Rename kolom ke format yang dimengerti PHP
    new_df = new_df.rename(columns=mapping)
    
    # 4. Formatting Data
    # Tanggal BRI: 2026-03-01 23:38:28 -> diubah ke 01 Mar 2026
    if 'date' in new_df.columns:
        new_df['date'] = pd.to_datetime(new_df['date']).dt.strftime('%d %b %Y')
    
    # Pastikan angka bersih (menghilangkan NaN atau string kosong)
    for col in ['amount_in', 'amount_out', 'balance']:
        if col in new_df.columns:
            new_df[col] = pd.to_numeric(new_df[col], errors='coerce').fillna(0)
            
    return new_df

def handle_bca(df):
    """Logic khusus untuk merapikan kolom BCA."""
    # BCA punya format yang berbeda lagi (biasanya ada baris kosong di awal)
    return df

def main():
    if len(sys.argv) < 4:
        print("Usage: python decoder.py <bank_type> <file_path> <password>")
        sys.exit(1)

    bank_type = sys.argv[1].lower()
    file_path = sys.argv[2]
    password = sys.argv[3]

    try:
        # 1. Dekripsi dulu
        raw_df = decrypt_file(file_path, password)

        # 2. Pilih handler berdasarkan tipe bank
        if bank_type == "mandiri":
            final_df = handle_mandiri(raw_df)
        elif bank_type == "bca":
            final_df = handle_bca(raw_df)
        elif bank_type == "bri":
            final_df = handle_bri(raw_df)
        else:
            print(f"Error: Bank {bank_type} belum didukung.")
            sys.exit(1)

        # 3. Output ke JSON (agar mudah dibaca oleh Laravel)
        print(final_df.to_json(orient="records"))

    except Exception as e:
        print(f"Error: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()