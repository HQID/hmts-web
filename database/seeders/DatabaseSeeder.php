<?php

namespace Database\Seeders;

use App\Models\CashAccount;
use App\Models\Division;
use App\Models\Inventory;
use App\Models\MeetingNote;
use App\Models\Member;
use App\Models\Period;
use App\Models\Program;
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

        // 3. Divisi BPH (Badan Pengurus Harian)
        $bphDivision = Division::updateOrCreate(
            [
                'period_id' => $period->id,
                'name' => 'BPH',
            ],
            [
                'period_id' => $period->id,
                'full_name' => 'Badan Pengurus Harian',
                'code' => 'BPH',
                'description' => 'Badan Pengurus Harian HMTS FT-UNTAD: Pimpinan umum, koordinasi internal-eksternal, administrasi, dan kebendaharaan.',
                'sort_order' => 0,
            ]
        );

        $bphData = [
            [
                'name' => 'Muhammad Farhan Mubarak',
                'nim' => 'F11122045',
                'position' => 'Ketua',
                'batch' => "'23",
                'bio' => 'Penanggung jawab umum arah kebijakan & kepemimpinan organisasi',
                'photo_path' => 'images/members/ketua.png',
                'sort_order' => 1,
            ],
            [
                'name' => 'Kifli',
                'nim' => 'F11122087',
                'position' => 'Ketua 1',
                'batch' => "'23",
                'bio' => 'Koordinator bidang internal keorganisasian & sinkronisasi divisi',
                'photo_path' => 'images/members/ketua1.png',
                'sort_order' => 2,
            ],
            [
                'name' => 'Erzy',
                'nim' => 'F11122103',
                'position' => 'Ketua 2',
                'batch' => "'23",
                'bio' => 'Koordinator bidang eksternal, kemitraan lembaga, & keprofesian',
                'photo_path' => 'images/members/ketua2.png',
                'sort_order' => 3,
            ],
            [
                'name' => 'Indah',
                'nim' => 'F11123014',
                'position' => 'Sekretaris Umum',
                'batch' => "'23",
                'bio' => 'Tata kelola persuratan, inventaris arsip, & administrasi kesekretariatan',
                'photo_path' => 'images/members/sekum.png',
                'sort_order' => 4,
            ],
            [
                'name' => 'Tiara',
                'nim' => 'F11123059',
                'position' => 'Bendahara Umum',
                'batch' => "'23",
                'bio' => 'Manajemen perbendaharaan kas, transparansi anggaran, & laporan keuangan',
                'photo_path' => 'images/members/bendum.png',
                'sort_order' => 5,
            ],
        ];

        foreach ($bphData as $bph) {
            Member::updateOrCreate(
                [
                    'period_id' => $period->id,
                    'nim' => $bph['nim'],
                ],
                array_merge($bph, ['period_id' => $period->id, 'division_id' => $bphDivision->id])
            );
        }

        // 4. 8 Divisi Pelaksana Resmi
        $divisionsData = [
            [
                'name' => 'Ristek',
                'full_name' => 'Divisi Ristek',
                'code' => 'DIVISI 01',
                'description' => 'Inovasi rekayasa konstruksi, material beton, dan teknologi rekayasa sipil.',
                'sort_order' => 1,
                'coordinator' => 'Yani',
                'coord_nim' => 'F11123002',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/ristek.png',
            ],
            [
                'name' => 'Infokom',
                'full_name' => 'Divisi Infokom',
                'code' => 'DIVISI 02',
                'description' => 'Pengelolaan media resmi, publikasi digital, dan dokumentasi visual.',
                'sort_order' => 2,
                'coordinator' => 'Syauqi',
                'coord_nim' => 'F11123041',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/infokom.png',
            ],
            [
                'name' => 'Penalaran',
                'full_name' => 'Divisi Penalaran',
                'code' => 'DIVISI 03',
                'description' => 'Pendampingan lomba jembatan (KJI/KBGI), bimbingan akademik, & tutor sebaya.',
                'sort_order' => 3,
                'coordinator' => 'Dava',
                'coord_nim' => 'F11123089',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/penalaran.png',
            ],
            [
                'name' => 'FKMTSI',
                'full_name' => 'Divisi FKMTSI',
                'code' => 'DIVISI 04',
                'description' => 'Hubungan koordinasi FKMTSI Wilayah VIII dan temu wicara nasional.',
                'sort_order' => 4,
                'coordinator' => 'Dhani',
                'coord_nim' => 'F11123110',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/fkmtsi.png',
            ],
            [
                'name' => 'Hublua',
                'full_name' => 'Divisi Hublua',
                'code' => 'DIVISI 05',
                'description' => 'Sinergi dengan instansi PUPR, BUMN Karya, LPJK, dan jejaring alumni.',
                'sort_order' => 5,
                'coordinator' => 'Juven',
                'coord_nim' => 'F11123023',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/hublua.png',
            ],
            [
                'name' => 'Bursa',
                'full_name' => 'Divisi Bursa',
                'code' => 'DIVISI 06',
                'description' => 'Pengadaan merchandise resmi, atribut keteknikan, dan kemandirian dana.',
                'sort_order' => 6,
                'coordinator' => 'Dinay',
                'coord_nim' => 'F11123067',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/bursa.png',
            ],
            [
                'name' => 'Advokasi',
                'full_name' => 'Divisi Advokasi',
                'code' => 'DIVISI 07',
                'description' => 'Penjaringan aspirasi civitas, advokasi sarana lab, dan perlindungan hak mahasiswa.',
                'sort_order' => 7,
                'coordinator' => 'Nathan',
                'coord_nim' => 'F11123078',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/advokasi.png',
            ],
            [
                'name' => 'Kaderisasi',
                'full_name' => 'Divisi Kaderisasi',
                'code' => 'DIVISI 08',
                'description' => 'Pembinaan karakter moral calon insinyur, pelatihan kepemimpinan, & jiwa korsa.',
                'sort_order' => 8,
                'coordinator' => 'Farhan',
                'coord_nim' => 'F11123095',
                'coord_batch' => "'23",
                'coord_photo' => 'images/members/kaderisasi.png',
            ],
        ];

        foreach ($divisionsData as $divData) {
            $division = Division::updateOrCreate(
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
            Member::updateOrCreate(
                [
                    'period_id' => $period->id,
                    'nim' => $divData['coord_nim'],
                ],
                [
                    'period_id' => $period->id,
                    'division_id' => $division->id,
                    'name' => $divData['coordinator'],
                    'nim' => $divData['coord_nim'],
                    'position' => 'Koordinator Divisi ' . $divData['name'],
                    'batch' => $divData['coord_batch'],
                    'photo_path' => $divData['coord_photo'] ?? 'images/members/placeholder.svg',
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

        // 5. Akun Kas Utama
        CashAccount::firstOrCreate(
            ['period_id' => $period->id, 'code' => 'KAS-BPH-01'],
            [
                'name' => 'Kas Operasional BPH HMTS',
                'current_balance' => 0,
                'is_active' => true,
            ]
        );

        // 6. Program Kerja Termasuk Kategori Internal
        foreach ([
            [
                'title' => 'Rapat Kerja & Upgrading Pengurus',
                'category' => 'internal',
                'description' => 'Konsolidasi internal kepengurusan, penetapan roadmap program kerja tahunan, dan peningkatan kapasitas manajerial pengurus HMTS FT-UNTAD.',
                'starts_at' => now()->addDays(7)->setTime(9, 0),
                'ends_at' => now()->addDays(7)->setTime(16, 0),
                'location' => 'Ruang Sidang Jurusan Teknik Sipil',
                'status' => 'rencana',
            ],
            [
                'title' => 'Pelatihan Software Rekayasa Struktur SAP2000',
                'category' => 'workshop',
                'description' => 'Pelatihan intensif pemodelan analisis struktur bangunan gedung bertingkat tahan gempa untuk mahasiswa teknik sipil.',
                'starts_at' => now()->addDays(14)->setTime(8, 30),
                'ends_at' => now()->addDays(15)->setTime(15, 30),
                'location' => 'Laboratorium Komputasi Teknik Sipil',
                'status' => 'rencana',
            ],
            [
                'title' => 'Civil Bridge Competition & Mentoring KJI/KBGI',
                'category' => 'competition',
                'description' => 'Bimbingan teknis perancangan jembatan dan simulasi pembebanan jembatan balsa menuju Kompetisi Jembatan Indonesia.',
                'starts_at' => now()->addDays(21)->setTime(10, 0),
                'ends_at' => now()->addDays(21)->setTime(17, 0),
                'location' => 'Laboratorium Bahan & Konstruksi',
                'status' => 'rencana',
            ],
        ] as $prog) {
            Program::firstOrCreate(
                ['period_id' => $period->id, 'title' => $prog['title']],
                $prog
            );
        }
    }
}
