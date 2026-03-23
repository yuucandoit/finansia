"""
AI Helper untuk Financial Analysis menggunakan Llama-cpp-python
================================================
File ini berisi helper functions untuk menggunakan LLM dalam
menganalisis data finansial, kategorisasi transaksi, dan insight generation.
"""

import sys
import json
from llama_cpp import Llama
import json

class FinancialAI:
    """Class untuk AI Financial Assistant menggunakan Llama model"""
    
    def __init__(self, model_path=None):
        """
        Initialize AI model
        Args:
            model_path: Optional custom model path. If None, will use default HuggingFace model
        """
        print("🤖 Loading AI model...")
        model_path = "/Users/macbook/Documents/model_ai/Qwen2.5-Coder-3B-Instruct-Q6_K.gguf" # Custom local model path
        try:
            if model_path:
                # Load from local path
                self.llm = Llama(model_path=model_path,n_ctx=2048)
            else:
                # Load from HuggingFace
                self.llm = Llama.from_pretrained(
                    repo_id="LiquidAI/LFM2.5-1.2B-Instruct-GGUF",
                    filename="LFM2.5-1.2B-Instruct-BF16.gguf",
                    verbose=False
                )
            print("✅ AI model loaded successfully!")
        except Exception as e:
            print(f"❌ Error loading AI model: {str(e)}")
            raise
    
    def chat(self, user_message, system_prompt=None):
        """
        Send a message to AI and get response
        
        Args:
            user_message: User's question or prompt
            system_prompt: Optional system instruction for AI behavior
            
        Returns:
            AI's response as string
        """
        messages = []
        
        if system_prompt:
            messages.append({
                "role": "system",
                "content": system_prompt
            })
        
        messages.append({
            "role": "user",
            "content": user_message
        })
        
        try:
            response = self.llm.create_chat_completion(
                messages=messages,
                temperature=0.7,
                max_tokens=512
            )
            return response['choices'][0]['message']['content']
        except Exception as e:
            return f"Error: {str(e)}"
    
    def categorize_transaction(self, json):
        with open(json, 'r') as f:
            transactions = json.load(f)
        # System prompt tetap sama seperti yang kamu buat
        system_prompt = """Kamu adalah AI ahli mesin kategori transaksi bank Indonesia.
    Tugasmu: Analisis deskripsi transaksi dan tentukan satu kategori yang paling relevan.

    Pola Identifikasi:
1. Gaji & Pendapatan: Ada kata 'Gaji', 'Payroll', 'Transfer Dari [Nama Perusahaan]', atau transfer masuk (amount_in) yang bersifat rutin.
2. Transportasi: Ada kata 'SHELL', 'PERTAMINA', 'SPBU', 'Grab', 'Gojek', 'e-money', 'Top-up toll', 'BP'.
3. Makanan & Minuman: Nama resto, cafe, 'Warung', 'Warteg', 'Kopi Kenangan', 'Gokana', 'Makan','cireng',.
4. Investasi & Tabungan: Ada keterangan 'Nabung', 'Reksadana', 'Bibit', 'Ajaib', atau transfer ke rekening tabungan pribadi lain.
5. Utilitas & Tagihan: Ada kata 'Axis', 'Telkomsel', 'Listrik', 'Internet', 'Admin Bank', 'Biaya Transfer'.
6. Hiburan & Hobi: Ada kata 'TIX ID', 'Cinema', 'Netflix', 'Spotify', 'Bioskop'.

Contoh:
- "SOLUSI INTEK INDONESIA Gaji Januari 2026" -> Gaji & Pendapatan
- "SHELL TJ BARAT" -> Transportasi
- "Warteg Putra Bahari" -> Makanan & Minuman
- "Nabung Reksadana" -> Investasi & Tabungan
- "Bayar Listrik PLN" -> Utilitas & Tagihan
- "TIX ID Pembelian" -> Hiburan & Hobi
- "Transfer" -> Investasi & Tabungan
- "Grab Food" -> Makanan & Minuman
- "Top-up e-money" -> Transportasi
- "Spotify Premium" -> Hiburan & Hobi
- "Internet Indihome" -> Utilitas & Tagihan
- "Cireng Goreng" -> Makanan & Minuman
- "Gojek Ride" -> Transportasi
- "Biaya Transfer" -> Utilitas & Tagihan
- "Kopi Kenangan" -> Makanan & Minuman
- "GOKANA TEPPAN" -> Makanan & Minuman
- "SPBU" -> Transportasi

    Aturan Ketat: Jawab HANYA dengan nama kategori. Jangan beri penjelasan."""
        print(f"\n Starting Categorizing Transaction... {json}")
        # Tambahkan temperature=0 agar AI konsisten dan tidak ngawur
        return self.chat(f"Deskripsi: {json}\nKategori:", system_prompt)
#     def categorize_transaction(self, transactions_json):
#         """
#         Kategorisasi transaksi berdasarkan deskripsi
        
#         Args:
#             description: Deskripsi transaksi (e.g., "Bayar listrik PLN")
            
#         Returns:
#             Kategori transaksi (e.g., "Utilitas & Tagihan")
#         """
#         system_prompt = """Kamu adalah AI ahli mesin kategori transaksi bank Indonesia.
# Tugasmu: Analisis deskripsi transaksi dan tentukan satu kategori yang paling relevan.

# Pola Identifikasi:
# 1. Gaji & Pendapatan: Ada kata 'Gaji', 'Payroll', 'Transfer Dari [Nama Perusahaan]', atau transfer masuk (amount_in) yang bersifat rutin.
# 2. Transportasi: Ada kata 'SHELL', 'PERTAMINA', 'SPBU', 'Grab', 'Gojek', 'e-money', 'Top-up toll', 'BP'.
# 3. Makanan & Minuman: Nama resto, cafe, 'Warung', 'Warteg', 'Kopi Kenangan', 'Gokana', 'Makan','cireng',.
# 4. Investasi & Tabungan: Ada keterangan 'Nabung', 'Reksadana', 'Bibit', 'Ajaib', atau transfer ke rekening tabungan pribadi lain.
# 5. Utilitas & Tagihan: Ada kata 'Axis', 'Telkomsel', 'Listrik', 'Internet', 'Admin Bank', 'Biaya Transfer'.
# 6. Hiburan & Hobi: Ada kata 'TIX ID', 'Cinema', 'Netflix', 'Spotify', 'Bioskop'.

# Contoh:
# - "SOLUSI INTEK INDONESIA Gaji Januari 2026" -> Gaji & Pendapatan
# - "SHELL TJ BARAT" -> Transportasi
# - "Warteg Putra Bahari" -> Makanan & Minuman
# - "Nabung Reksadana" -> Investasi & Tabungan
# - "Bayar Listrik PLN" -> Utilitas & Tagihan
# - "TIX ID Pembelian" -> Hiburan & Hobi
# - "Transfer" -> Investasi & Tabungan
# - "Grab Food" -> Makanan & Minuman
# - "Top-up e-money" -> Transportasi
# - "Spotify Premium" -> Hiburan & Hobi
# - "Internet Indihome" -> Utilitas & Tagihan
# - "Cireng Goreng" -> Makanan & Minuman
# - "Gojek Ride" -> Transportasi
# - "Biaya Transfer" -> Utilitas & Tagihan
# - "Kopi Kenangan" -> Makanan & Minuman
# - "GOKANA TEPPAN" -> Makanan & Minuman
# - "SPBU" -> Transportasi

# Aturan Ketat: Jawab HANYA dengan nama kategori. Jangan beri penjelasan."""
        
#         return self.chat(f"Kategorikan seluruh transaksi ini: {transactions_json}", system_prompt)
    
    def analyze_spending_pattern(self, transactions_json):
        """
        Analisis pola pengeluaran dari data transaksi
        
        Args:
            transactions_json: JSON string berisi list transaksi
            
        Returns:
            Insight dan recommendation
        """
        system_prompt = """Kamu adalah financial advisor yang memberikan insight tentang pola pengeluaran.
Berikan analisis singkat dan saran untuk pengelolaan keuangan yang lebih baik.
Gunakan bahasa Indonesia yang mudah dipahami."""
        
        user_prompt = f"""Berikut adalah data transaksi pengguna:
{transactions_json}

Berikan analisis pola pengeluaran dan saran finansial."""
        
        return self.chat(user_prompt, system_prompt)
    
    def suggest_budget(self, monthly_income, transactions_json):
        """
        Berikan saran budget allocation berdasarkan pendapatan dan transaksi
        
        Args:
            monthly_income: Pendapatan bulanan
            transactions_json: JSON string berisi list transaksi
            
        Returns:
            Saran budget allocation
        """
        system_prompt = """Kamu adalah financial planner yang membantu membuat budget plan.
Gunakan metode 50/30/20 atau sesuaikan dengan kondisi pengguna.
Berikan saran yang realistis dan praktis dalam bahasa Indonesia."""
        
        user_prompt = f"""Pendapatan bulanan: Rp {monthly_income:,.0f}
Transaksi terakhir:
{transactions_json}

Buatkan saran budget allocation untuk bulan depan."""
        
        return self.chat(user_prompt, system_prompt)


def main():
    """Demo penggunaan AI Helper"""
    
    if len(sys.argv) < 2:
        print("""
Usage:
  python ai_helper.py test              - Test basic AI functionality
  python ai_helper.py categorize <json> - Categorize a transaction
  python ai_helper.py analyze <json>    - Analyze spending pattern
        """)
        sys.exit(1)
    
    command = sys.argv[1].lower()
    
    # Initialize AI
    ai = FinancialAI()
    
    if command == "test":
        # Test basic functionality
        print("\n🧪 Testing AI...")
        response = ai.chat("What is the capital of France?")
        print(f"\n💬 AI Response: {response}\n")
        
    elif command == "categorize":
        if len(sys.argv) < 3:
            print("Error: Please provide transaction JSON")
            sys.exit(1)
        
        transactions = sys.argv[2]
        print(f"\n📝 Categorizing: {transactions}")
        category = ai.categorize_transaction(transactions)
        print(f"📂 Category: {category}\n")

        
    elif command == "analyze":
        if len(sys.argv) < 3:
            print("Error: Please provide transactions JSON")
            sys.exit(1)
        
        transactions = sys.argv[2]
        print("\n📊 Analyzing spending pattern...")
        analysis = ai.analyze_spending_pattern(transactions)
        print(f"\n💡 Analysis:\n{analysis}\n")
    
    else:
        print(f"Unknown command: {command}")
        sys.exit(1)


if __name__ == "__main__":
    main()
