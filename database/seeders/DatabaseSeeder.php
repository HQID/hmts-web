<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Member;
use App\Models\Inventory;
use App\Models\Period;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengurus Inti
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@hmts-untad.ac.id'],
            [
                'name' => 'Ketua Umum HMTS FT-UNTAD',
                'password' => Hash::make('password123'),
                'role' => 'superadmin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'pengurus@hmts-untad.ac.id'],
            [
                'name' => 'BPH Pengurus Harian HMTS',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );

        // 2. Periode Aktif 2026/2027 (Tanpa nama kabinet)
        $period = Period::firstOrCreate(
            ['name' => '2026/2027'],
            [
                'start_year' => 2026,
                'end_year' => 2027,
                'is_active' => true,
            ]
        );

        // 3. Badan Pengurus Harian (BPH: 1 Ketua & 4 Pimpinan Harian)
        $bphData = [
            [
                'name' => 'M. Rayhan Pratama',
                'nim' => 'F11122045',
                'role_type' => 'ketua',
                'position' => 'Ketua',
                'batch' => "'22",
                'bio' => 'Penanggung jawab umum arah kebijakan & kepemimpinan organisasi',
                'sort_order' => 1,
            ],
            [
                'name' => 'Dimas Arya Nugraha',
                'nim' => 'F11122087',
                'role_type' => 'bph',
                'position' => 'Ketua 1',
                'batch' => "'22",
                'bio' => 'Koordinator bidang internal keorganisasian & sinkronisasi divisi',
                'sort_order' => 2,
            ],
            [
                'name' => 'Fadel Muhammad',
                'nim' => 'F11122103',
                'role_type' => 'bph',
                'position' => 'Ketua 2',
                'batch' => "'22",
                'bio' => 'Koordinator bidang eksternal, kemitraan lembaga, & keprofesian',
                'sort_order' => 3,
            ],
            [
                'name' => 'Amanda Putri Lestari',
                'nim' => 'F11123014',
                'role_type' => 'bph',
                'position' => 'Sekretaris Umum',
                'batch' => "'23",
                'bio' => 'Tata kelola persuratan, inventaris arsip, & administrasi kesekretariatan',
                'sort_order' => 4,
            ],
            [
                'name' => 'Siti Nurhaliza',
                'nim' => 'F11123059',
                'role_type' => 'bph',
                'position' => 'Bendahara Umum',
                'batch' => "'23",
                'bio' => 'Manajemen perbendaharaan kas, transparansi anggaran, & laporan keuangan',
                'sort_order' => 5,
            ],
        ];

        foreach ($bphData as $bph) {
            Member::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'nim' => $bph['nim'],
                ],
                array_merge($bph, ['period_id' => $period->id, 'division_id' => null])
            );
        }

        // 4. 8 Divisi Pelaksana Resmi
        $divisionsData = [
            [
                'name' => 'Ristek',
                'full_name' => 'Riset & Teknologi',
                'code' => 'DIVISI 01',
                'description' => 'Inovasi rekayasa konstruksi, material beton, dan teknologi rekayasa sipil.',
                'sort_order' => 1,
                'coordinator' => 'Bagus Wicaksono',
                'coord_nim' => 'F11123002',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Infokom',
                'full_name' => 'Informasi & Komunikasi',
                'code' => 'DIVISI 02',
                'description' => 'Pengelolaan media resmi, publikasi digital, dan dokumentasi visual.',
                'sort_order' => 2,
                'coordinator' => 'Reza Pratama',
                'coord_nim' => 'F11123041',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Penalaran',
                'full_name' => 'Penalaran Ilmiah & Prestasi',
                'code' => 'DIVISI 03',
                'description' => 'Pendampingan lomba jembatan (KJI/KBGI), bimbingan akademik, & tutor sebaya.',
                'sort_order' => 3,
                'coordinator' => 'Annisa Salsabila',
                'coord_nim' => 'F11123089',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'FKMTSI',
                'full_name' => 'Forum Komunikasi Mahasiswa Teknik Sipil Indonesia',
                'code' => 'DIVISI 04',
                'description' => 'Hubungan koordinasi FKMTSI Wilayah VIII dan temu wicara nasional.',
                'sort_order' => 4,
                'coordinator' => 'Irfan Ramadhan',
                'coord_nim' => 'F11123110',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Hublua',
                'full_name' => 'Hubungan Luar & Alumni',
                'code' => 'DIVISI 05',
                'description' => 'Sinergi dengan instansi PUPR, BUMN Karya, LPJK, dan jejaring alumni.',
                'sort_order' => 5,
                'coordinator' => 'Kevin Alfarizi',
                'coord_nim' => 'F11123023',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Bursa',
                'full_name' => 'Kewirausahaan Sipil',
                'code' => 'DIVISI 06',
                'description' => 'Pengadaan merchandise resmi, atribut keteknikan, dan kemandirian dana.',
                'sort_order' => 6,
                'coordinator' => 'Nadia Rahmawati',
                'coord_nim' => 'F11123067',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Advokasi',
                'full_name' => 'Advokasi & Kesejahteraan Mahasiswa',
                'code' => 'DIVISI 07',
                'description' => 'Penjaringan aspirasi civitas, advokasi sarana lab, dan perlindungan hak mahasiswa.',
                'sort_order' => 7,
                'coordinator' => 'Muhammad Tegar',
                'coord_nim' => 'F11123078',
                'coord_batch' => "'23",
            ],
            [
                'name' => 'Kaderisasi',
                'full_name' => 'Pengembangan Sumber Daya Mahasiswa',
                'code' => 'DIVISI 08',
                'description' => 'Pembinaan karakter moral calon insinyur, pelatihan kepemimpinan, & jiwa korsa.',
                'sort_order' => 8,
                'coordinator' => 'Fahri Hidayat',
                'coord_nim' => 'F11123095',
                'coord_batch' => "'23",
            ],
        ];

        foreach ($divisionsData as $divData) {
            $division = Division::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'name' => $divData['name'],
                ],
                [
                    'period_id' => $period->id,
                    'full_name' => $divData['full_name'],
                    'code' => $divData['code'],
                    'description' => $divData['description'],
                    'sort_order' => $divData['sort_order'],
                ]
            );

            // Koordinator Divisi
            Member::firstOrCreate(
                [
                    'period_id' => $period->id,
                    'nim' => $divData['coord_nim'],
                ],
                [
                    'period_id' => $period->id,
                    'division_id' => $division->id,
                    'name' => $divData['coordinator'],
                    'nim' => $divData['coord_nim'],
                    'role_type' => 'coordinator',
                    'position' => 'Koordinator Divisi ' . $divData['name'],
                    'batch' => $divData['coord_batch'],
                    'sort_order' => 1,
                ]
            );
        }

        foreach ([
            ['name' => 'Total Station Sokkia CX-105', 'code' => 'INV-TS-001', 'category' => 'Instrumen Survei Digital', 'specification' => 'Akurasi 5 detik, EDM reflectorless 500m.', 'total_qty' => 3],
            ['name' => 'Theodolite Digital Topcon DT-200', 'code' => 'INV-TD-001', 'category' => 'Pengukuran Sudut & Poligon', 'specification' => 'Pembacaan optik digital, tahan debu IP66.', 'total_qty' => 5],
            ['name' => 'Waterpass Otomatis Nikon AC-2S', 'code' => 'INV-WP-001', 'category' => 'Pengukuran Beda Tinggi', 'specification' => 'Lensa 24x dan kompensator otomatis.', 'total_qty' => 6],
            ['name' => 'Concrete Hammer Test Schmidt', 'code' => 'INV-HT-001', 'category' => 'Uji Beton', 'specification' => 'Tipe N untuk pengujian non-destruktif.', 'total_qty' => 2],
        ] as $inventory) {
            Inventory::firstOrCreate(['code' => $inventory['code']], array_merge($inventory, [
                'period_id' => $period->id,
                'available_qty' => $inventory['total_qty'],
                'condition' => 'baik',
                'is_active' => true,
            ]));
        }
    }
}
