<?php

namespace Database\Seeders;

use App\Models\AssetCategory;
use App\Models\EventExpenseCategory;
use App\Models\House;
use App\Models\MonthlyFeeSetting;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Master Pengurus User
        $adminUser = User::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'pengurus',
            'is_active' => true,
        ]);

        // 2. Sample Houses & Residents
        $housesData = [
            ['block' => 'A', 'house_number' => '01', 'address' => 'Jl. Mawar No. 1'],
            ['block' => 'A', 'house_number' => '02', 'address' => 'Jl. Mawar No. 2'],
            ['block' => 'B', 'house_number' => '01', 'address' => 'Jl. Melati No. 1'],
            ['block' => 'B', 'house_number' => '02', 'address' => 'Jl. Melati No. 2'],
        ];

        $residentsData = [
            [
                'nik' => '3201010101010001',
                'nomor_kk' => '3201010101010000',
                'nama_lengkap' => 'Budi Santoso',
                'tempat_lahir' => 'Jakarta',
                'tanggal_lahir' => '1985-05-12',
                'jenis_kelamin' => 'L',
                'nomor_telepon' => '081234567890',
                'email' => 'budi@example.com',
                'status_warga' => 'aktif',
                'hubungan_dalam_keluarga' => 'kepala_keluarga',
                'is_verified' => true,
                'verified_at' => now(),
            ],
            [
                'nik' => '3201010101010002',
                'nomor_kk' => '3201010101010000',
                'nama_lengkap' => 'Siti Aminah',
                'tempat_lahir' => 'Bandung',
                'tanggal_lahir' => '1988-08-20',
                'jenis_kelamin' => 'P',
                'nomor_telepon' => '081234567891',
                'email' => 'siti@example.com',
                'status_warga' => 'aktif',
                'hubungan_dalam_keluarga' => 'istri',
                'is_verified' => true,
                'verified_at' => now(),
            ],
            [
                'nik' => '3201010101010003',
                'nomor_kk' => '3201010101010005',
                'nama_lengkap' => 'Ahmad Hidayat',
                'tempat_lahir' => 'Surabaya',
                'tanggal_lahir' => '1990-01-15',
                'jenis_kelamin' => 'L',
                'nomor_telepon' => '081234567892',
                'email' => 'ahmad@example.com',
                'status_warga' => 'aktif',
                'hubungan_dalam_keluarga' => 'kepala_keluarga',
                'is_verified' => true,
                'verified_at' => now(),
            ],
            [
                'nik' => '3201010101010004',
                'nomor_kk' => '3201010101010008',
                'nama_lengkap' => 'Dewi Lestari',
                'tempat_lahir' => 'Yogyakarta',
                'tanggal_lahir' => '1992-11-03',
                'jenis_kelamin' => 'P',
                'nomor_telepon' => '081234567893',
                'email' => 'dewi@example.com',
                'status_warga' => 'aktif',
                'hubungan_dalam_keluarga' => 'kepala_keluarga',
                'is_verified' => true,
                'verified_at' => now(),
            ],
        ];

        foreach ($housesData as $index => $hData) {
            $house = House::create($hData);
            if (isset($residentsData[$index])) {
                $rData = $residentsData[$index];
                $rData['house_id'] = $house->id;
                $resident = Resident::create($rData);

                // Create Warga User account
                $username = strtoupper($house->block).sprintf('%02d', (int) $house->house_number);
                User::create([
                    'resident_id' => $resident->id,
                    'username' => $username,
                    'password' => Hash::make('password'),
                    'role' => 'warga',
                    'is_active' => true,
                ]);
            }
        }

        // 3. Monthly Fee Setting
        MonthlyFeeSetting::create([
            'amount' => 30000,
            'effective_from' => '2026-01-01',
            'effective_until' => null,
            'description' => 'Iuran Rutin Kebersihan & Keamanan RT 2026',
            'is_active' => true,
        ]);

        // 4. Asset Categories
        $assetCategories = [
            'Perlengkapan Kegiatan',
            'Elektronik',
            'Kebersihan',
            'Keamanan',
            'Olahraga',
            'Furniture',
        ];
        foreach ($assetCategories as $cat) {
            AssetCategory::create([
                'name' => $cat,
                'description' => "Kategori aset {$cat}",
                'is_active' => true,
            ]);
        }

        // 5. Event Expense Categories
        $expenseCategories = [
            'Konsumsi',
            'Sewa Tempat',
            'Sewa Peralatan',
            'Dekorasi',
            'Transportasi',
            'Dokumentasi',
            'Hadiah',
            'Perlengkapan',
            'Administrasi',
            'Lainnya',
        ];
        foreach ($expenseCategories as $eCat) {
            EventExpenseCategory::create([
                'name' => $eCat,
                'description' => "Kategori pengeluaran {$eCat}",
                'is_active' => true,
            ]);
        }
    }
}
