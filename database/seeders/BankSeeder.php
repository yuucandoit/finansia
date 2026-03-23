<?php

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            // Bank BUMN
            ['code' => '002', 'name' => 'Bank BRI (Bank Rakyat Indonesia)'],
            ['code' => '008', 'name' => 'Bank Mandiri'],
            ['code' => '009', 'name' => 'Bank BNI (Bank Negara Indonesia)'],
            ['code' => '427', 'name' => 'Bank BNI Syariah'],
            ['code' => '200', 'name' => 'Bank BTN (Bank Tabungan Negara)'],
            
            // Bank Swasta Nasional - Top Tier
            ['code' => '014', 'name' => 'Bank BCA (Bank Central Asia)'],
            ['code' => '022', 'name' => 'Bank CIMB Niaga'],
            ['code' => '213', 'name' => 'Bank BTPN'],
            ['code' => '213', 'name' => 'Bank Jago'],
            ['code' => '028', 'name' => 'Bank OCBC NISP'],
            ['code' => '011', 'name' => 'Bank Danamon'],
            ['code' => '016', 'name' => 'Bank Maybank Indonesia'],
            ['code' => '426', 'name' => 'Bank Mega'],
            ['code' => '153', 'name' => 'Bank Sinarmas'],
            ['code' => '019', 'name' => 'Bank Panin'],
            ['code' => '013', 'name' => 'Bank Permata'],
            ['code' => '023', 'name' => 'Bank UOB Indonesia'],
            ['code' => '037', 'name' => 'Bank Artha Graha'],
            ['code' => '046', 'name' => 'Bank DBS Indonesia'],
            ['code' => '441', 'name' => 'Bank Bukopin'],
            ['code' => '031', 'name' => 'Bank BII Maybank'],
            ['code' => '472', 'name' => 'Bank Jago (Artos)'],
            
            // Bank Syariah
            ['code' => '451', 'name' => 'Bank Syariah Indonesia (BSI)'],
            ['code' => '422', 'name' => 'Bank BRI Syariah'],
            ['code' => '506', 'name' => 'Bank Mega Syariah'],
            ['code' => '484', 'name' => 'Bank KEB Hana Indonesia'],
            ['code' => '425', 'name' => 'Bank Jabar Banten Syariah'],
            ['code' => '494', 'name' => 'Bank Raya Indonesia'],
            ['code' => '521', 'name' => 'Bank Bukopin Syariah'],
            ['code' => '535', 'name' => 'Bank BCA Syariah'],
            ['code' => '564', 'name' => 'Bank Mandiri Taspen'],
            
            // Bank Pembangunan Daerah (BPD)
            ['code' => '110', 'name' => 'Bank BJB (Bank Jabar Banten)'],
            ['code' => '111', 'name' => 'Bank DKI Jakarta'],
            ['code' => '112', 'name' => 'Bank BPD DIY (Yogyakarta)'],
            ['code' => '113', 'name' => 'Bank Jateng (Jawa Tengah)'],
            ['code' => '114', 'name' => 'Bank Jatim (Jawa Timur)'],
            ['code' => '115', 'name' => 'Bank BPD Jambi'],
            ['code' => '116', 'name' => 'Bank Aceh Syariah'],
            ['code' => '117', 'name' => 'Bank Sumut (Sumatera Utara)'],
            ['code' => '118', 'name' => 'Bank Nagari (Sumatera Barat)'],
            ['code' => '119', 'name' => 'Bank Riau Kepri'],
            ['code' => '120', 'name' => 'Bank Sumsel Babel'],
            ['code' => '121', 'name' => 'Bank Lampung'],
            ['code' => '122', 'name' => 'Bank Kalsel (Kalimantan Selatan)'],
            ['code' => '123', 'name' => 'Bank Kalbar (Kalimantan Barat)'],
            ['code' => '124', 'name' => 'Bank Kaltimtara'],
            ['code' => '125', 'name' => 'Bank Kalteng (Kalimantan Tengah)'],
            ['code' => '126', 'name' => 'Bank Sulselbar'],
            ['code' => '127', 'name' => 'Bank Sulutgo'],
            ['code' => '128', 'name' => 'Bank BPD Bali'],
            ['code' => '129', 'name' => 'Bank NTB (Nusa Tenggara Barat)'],
            ['code' => '130', 'name' => 'Bank NTT (Nusa Tenggara Timur)'],
            ['code' => '131', 'name' => 'Bank Maluku Malut'],
            ['code' => '132', 'name' => 'Bank Papua'],
            ['code' => '133', 'name' => 'Bank Bengkulu'],
            ['code' => '134', 'name' => 'Bank Sulteng (Sulawesi Tengah)'],
            ['code' => '135', 'name' => 'Bank Sultra (Sulawesi Tenggara)'],
            ['code' => '137', 'name' => 'Bank Banten'],
            
            // Bank Asing
            ['code' => '042', 'name' => 'Bank of Tokyo Mitsubishi UFJ'],
            ['code' => '050', 'name' => 'Standard Chartered Bank'],
            ['code' => '054', 'name' => 'Bank Mizuho Indonesia'],
            ['code' => '057', 'name' => 'Bank of America'],
            ['code' => '068', 'name' => 'Bank ICBC Indonesia'],
            ['code' => '069', 'name' => 'Bank China Construction Bank'],
            ['code' => '076', 'name' => 'Bank Mayapada'],
            ['code' => '087', 'name' => 'Bank Ekonomi Raharja (Mestika)'],
            ['code' => '089', 'name' => 'Bank Haga'],
            ['code' => '095', 'name' => 'Bank J Trust Indonesia'],
            ['code' => '097', 'name' => 'Bank Rabobank Indonesia'],
            
            // Bank Digital & Modern
            ['code' => '501', 'name' => 'Bank Digital BCA (Blu)'],
            ['code' => '567', 'name' => 'Bank Allo'],
            ['code' => '490', 'name' => 'Bank Neo Commerce (BNC)'],
            ['code' => '503', 'name' => 'Bank Nationalnobu'],
            ['code' => '405', 'name' => 'Bank Victoria Syariah'],
            ['code' => '212', 'name' => 'Bank Woori Saudara'],
            ['code' => '547', 'name' => 'Bank Seabank Indonesia'],
            
            // Lainnya
            ['code' => '212', 'name' => 'Bank Muamalat'],
            ['code' => '405', 'name' => 'Bank Victoria'],
            ['code' => '459', 'name' => 'Bank Bisnis Internasional'],
            ['code' => '466', 'name' => 'Bank Sri Partha'],
            ['code' => '485', 'name' => 'Bank BPR KS (BPR Ks)'],
            ['code' => '490', 'name' => 'Bank Agro'],
            ['code' => '491', 'name' => 'Bank SBI Indonesia'],
            ['code' => '498', 'name' => 'Bank Royal Indonesia'],
            ['code' => '503', 'name' => 'Bank Dinar Indonesia'],
            ['code' => '513', 'name' => 'Bank Ina Perdana'],
            ['code' => '517', 'name' => 'Bank Harfa'],
            ['code' => '520', 'name' => 'Bank Prima Master'],
            ['code' => '526', 'name' => 'Bank Dipo International'],
            ['code' => '531', 'name' => 'Bank Anglomas International'],
            ['code' => '523', 'name' => 'Bank Sahabat Sampoerna'],
            ['code' => '542', 'name' => 'Bank Maspion Indonesia'],
            ['code' => '548', 'name' => 'Bank Multiarter Sentosa'],
            ['code' => '553', 'name' => 'Bank Mayora Indonesia'],
            ['code' => '555', 'name' => 'Bank Index Selindo'],
            ['code' => '558', 'name' => 'Bank Eksekutif International'],
            ['code' => '559', 'name' => 'Centratama Nasional Bank'],
            ['code' => '562', 'name' => 'Bank Fama International'],
            ['code' => '566', 'name' => 'Bank Oke Indonesia'],
            ['code' => '945', 'name' => 'Bank Artos Indonesia'],
            
            // E-Wallet & Payment Gateway
            ['code' => '911', 'name' => 'GoPay'],
            ['code' => '912', 'name' => 'OVO'],
            ['code' => '913', 'name' => 'Dana'],
            ['code' => '914', 'name' => 'ShopeePay'],
            ['code' => '915', 'name' => 'LinkAja'],
        ];

        foreach ($banks as $bank) {
            Bank::create($bank);
        }

        $this->command->info('✅ Created ' . count($banks) . ' banks successfully!');
    }
}
