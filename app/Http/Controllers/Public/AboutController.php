<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Member;
use App\Models\Period;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AboutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function index(Request $request): View
    {
        $periodName = '2026/2027';
        $ketua = null;
        $bphList = [];
        $divisions = [];

        try {
            $period = Period::current();
            if ($period) {
                $periodName = $period->name;
            }

            // Ambil Divisi BPH (Ketua & 4 Pimpinan Harian)
            $bphDivision = Division::with(['members' => fn ($query) => $query->orderBy('sort_order')])
                ->where('name', 'BPH')
                ->first();

            if ($bphDivision && $bphDivision->members->isNotEmpty()) {
                $ketua = $bphDivision->members->first();
                $bphList = $bphDivision->members->slice(1)->values();
            }

            // Ambil 8 Divisi Pelaksana (selain Divisi BPH)
            $divisions = Division::with(['members' => fn ($query) => $query->orderBy('sort_order')])
                ->where('name', '!=', 'BPH')
                ->orderBy('sort_order')
                ->get();
        } catch (\Throwable $e) {
            // Fallback graceful jika database belum dikonfigurasi
        }

        // Fallback data jika database belum di-seed
        if (!$ketua) {
            $ketua = (object)[
                'name' => 'M. Rayhan Pratama',
                'nim' => 'F11122045',
                'batch' => "'22",
                'position' => 'Ketua Himpunan',
                'bio' => 'Penanggung jawab umum arah kebijakan & kepemimpinan organisasi',
                'photo_url' => asset('images/members/ketua.svg'),
            ];
        }

        if (empty($bphList) || count($bphList) === 0) {
            $bphList = collect([
                (object)[
                    'name' => 'Dimas Arya Nugraha',
                    'nim' => 'F11122087',
                    'batch' => "'22",
                    'position' => 'Ketua 1',
                    'bio' => 'Koordinator bidang internal keorganisasian & sinkronisasi divisi.',
                    'photo_url' => asset('images/members/ketua1.svg'),
                ],
                (object)[
                    'name' => 'Fadel Muhammad',
                    'nim' => 'F11122103',
                    'batch' => "'22",
                    'position' => 'Ketua 2',
                    'bio' => 'Koordinator bidang eksternal, kemitraan lembaga, & keprofesian.',
                    'photo_url' => asset('images/members/ketua2.svg'),
                ],
                (object)[
                    'name' => 'Amanda Putri Lestari',
                    'nim' => 'F11123014',
                    'batch' => "'23",
                    'position' => 'Sekretaris Umum',
                    'bio' => 'Tata kelola persuratan, inventaris arsip, & administrasi kesekretariatan.',
                    'photo_url' => asset('images/members/sekum.svg'),
                ],
                (object)[
                    'name' => 'Siti Nurhaliza',
                    'nim' => 'F11123059',
                    'batch' => "'23",
                    'position' => 'Bendahara Umum',
                    'bio' => 'Manajemen perbendaharaan kas, transparansi anggaran, & laporan keuangan.',
                    'photo_url' => asset('images/members/bendum.svg'),
                ],
            ]);
        }

        if (empty($divisions) || count($divisions) === 0) {
            $divisions = collect([
                (object)[
                    'code' => 'DIVISI 01',
                    'name' => 'Ristek',
                    'full_name' => 'Divisi Ristek',
                    'description' => 'Inovasi rekayasa konstruksi, material beton, dan teknologi rekayasa sipil.',
                    'coordinator' => (object)[
                        'name' => 'Bagus Wicaksono',
                        'nim' => 'F11123002',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Ristek',
                        'photo_url' => asset('images/members/ristek.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 02',
                    'name' => 'Infokom',
                    'full_name' => 'Divisi Infokom',
                    'description' => 'Pengelolaan media resmi, publikasi digital, dan dokumentasi visual.',
                    'coordinator' => (object)[
                        'name' => 'Reza Pratama',
                        'nim' => 'F11123041',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Infokom',
                        'photo_url' => asset('images/members/infokom.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 03',
                    'name' => 'Penalaran',
                    'full_name' => 'Divisi Penalaran',
                    'description' => 'Pendampingan lomba jembatan (KJI/KBGI), bimbingan akademik, & tutor sebaya.',
                    'coordinator' => (object)[
                        'name' => 'Annisa Salsabila',
                        'nim' => 'F11123089',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Penalaran',
                        'photo_url' => asset('images/members/penalaran.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 04',
                    'name' => 'FKMTSI',
                    'full_name' => 'Divisi FKMTSI',
                    'description' => 'Hubungan koordinasi FKMTSI Wilayah VIII dan temu wicara nasional.',
                    'coordinator' => (object)[
                        'name' => 'Irfan Ramadhan',
                        'nim' => 'F11123110',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi FKMTSI',
                        'photo_url' => asset('images/members/fkmtsi.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 05',
                    'name' => 'Hublua',
                    'full_name' => 'Divisi Hublua',
                    'description' => 'Sinergi dengan instansi PUPR, BUMN Karya, LPJK, dan jejaring alumni.',
                    'coordinator' => (object)[
                        'name' => 'Kevin Alfarizi',
                        'nim' => 'F11123023',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Hublua',
                        'photo_url' => asset('images/members/hublua.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 06',
                    'name' => 'Bursa',
                    'full_name' => 'Divisi Bursa',
                    'description' => 'Pengadaan merchandise resmi, atribut keteknikan, dan kemandirian dana.',
                    'coordinator' => (object)[
                        'name' => 'Nadia Rahmawati',
                        'nim' => 'F11123067',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Bursa',
                        'photo_url' => asset('images/members/bursa.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 07',
                    'name' => 'Advokasi',
                    'full_name' => 'Divisi Advokasi',
                    'description' => 'Penjaringan aspirasi civitas, advokasi sarana lab, dan perlindungan hak mahasiswa.',
                    'coordinator' => (object)[
                        'name' => 'Muhammad Tegar',
                        'nim' => 'F11123078',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Advokasi',
                        'photo_url' => asset('images/members/advokasi.svg'),
                    ],
                ],
                (object)[
                    'code' => 'DIVISI 08',
                    'name' => 'Kaderisasi',
                    'full_name' => 'Divisi Kaderisasi',
                    'description' => 'Pembinaan karakter moral calon insinyur, pelatihan kepemimpinan, & jiwa korsa.',
                    'coordinator' => (object)[
                        'name' => 'Fahri Hidayat',
                        'nim' => 'F11123095',
                        'batch' => "'23",
                        'position' => 'Koordinator Divisi Kaderisasi',
                        'photo_url' => asset('images/members/kaderisasi.svg'),
                    ],
                ],
            ]);
        }

        return view('public.about', compact('periodName', 'ketua', 'bphList', 'divisions'));
    }
}
