"""
Simple Test Script for AI Helper
=================================
Quick test tanpa perlu command line arguments
"""

from ai_helper import FinancialAI

def test_basic():
    """Test basic AI functionality"""
    print("="*50)
    print("🧪 Testing AI Basic Chat")
    print("="*50)
    
    ai = FinancialAI()
    
    # Test 1: Basic question
    print("\n1️⃣ Test basic question:")
    response = ai.chat("What is 2+2?")
    print(f"   Q: What is 2+2?")
    print(f"   A: {response}")
    
    # Test 2: Financial question
    print("\n2️⃣ Test financial question:")
    response = ai.chat("Apa itu budget 50/30/20?")
    print(f"   Q: Apa itu budget 50/30/20?")
    print(f"   A: {response}")

def test_categorization():
    """Test transaction categorization"""
    print("\n" + "="*50)
    print("🏷️  Testing Transaction Categorization")
    print("="*50)
    
    ai = FinancialAI()
    
    transactions = [
        "Bayar listrik PLN",
        "Beli kopi Starbucks",
        "Bensin Pertamax",
        "Transfer ke tabungan",
        "Gaji bulanan",
        "Bayar Netflix"
    ]
    
    for desc in transactions:
        category = ai.categorize_transaction(desc)
        print(f"   {desc:30s} → {category}")

def test_spending_analysis():
    """Test spending pattern analysis"""
    print("\n" + "="*50)
    print("📊 Testing Spending Analysis")
    print("="*50)
    
    ai = FinancialAI()
    
    # Sample transactions
    import json
    transactions = [
        {"date": "01 Jan 2026", "description": "Kopi Starbucks", "amount_out": 50000, "amount_in": 0},
        {"date": "02 Jan 2026", "description": "Bensin", "amount_out": 100000, "amount_in": 0},
        {"date": "03 Jan 2026", "description": "Gaji", "amount_out": 0, "amount_in": 5000000},
        {"date": "05 Jan 2026", "description": "Makan siang", "amount_out": 75000, "amount_in": 0},
    ]
    
    transactions_json = json.dumps(transactions, indent=2)
    
    print("\nTransactions:")
    print(transactions_json)
    
    print("\nAI Analysis:")
    analysis = ai.analyze_spending_pattern(transactions_json)
    print(analysis)

if __name__ == "__main__":
    print("\n🚀 Starting AI Helper Tests...\n")
    
    try:
        # Run tests
        test_basic()
        test_categorization()
        test_spending_analysis()
        
        print("\n" + "="*50)
        print("✅ All tests completed!")
        print("="*50)
        
    except Exception as e:
        print(f"\n❌ Error during testing: {str(e)}")
        import traceback
        traceback.print_exc()
