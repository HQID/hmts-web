<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('HMTS FT-UNTAD');
        $response->assertSee('Himpunan Mahasiswa');
        $response->assertSee('Teknik Sipil');
        $response->assertSee('#WeAreTheChampions');
        $response->assertSee('https://www.instagram.com/hmts.tadulako/');
        $response->assertSee('https://youtube.com/@hmtstadulako?si=PJiOm9hgBSJuHWC3', false);
        $response->assertSee('https://www.tiktok.com/@hmts.tadulako', false);
        $response->assertSee('https://x.com/hmtstadulako?s=21', false);
        $response->assertDontSee('Program Kerja Pilihan');
    }

    public function test_about_page_returns_successful_response(): void
    {
        $response = $this->get('/tentang');

        $response->assertStatus(200);
        $response->assertSee('Kepengurusan HMTS FT-UNTAD');
        $response->assertSee('KETUA HIMPUNAN');
        $response->assertSee('KOORDINATOR DIVISI');
        $response->assertSee('DIVISI RISTEK');
        $response->assertSee('DIVISI INFOKOM');
        $response->assertSee('DIVISI PENALARAN');
        $response->assertSee('DIVISI FKMTSI');
        $response->assertSee('DIVISI HUBLUA');
        $response->assertSee('DIVISI BURSA');
        $response->assertSee('DIVISI ADVOKASI');
        $response->assertSee('DIVISI KADERISASI');
        $response->assertSee('Yani');
        $response->assertSee('images/members');
        $response->assertDontSee('01. KETUA');
        $response->assertDontSee('8 DIVISI PELAKSANA');
        $response->assertDontSee('DIVISI 01');

        // Visi & Misi assertions
        $response->assertSee('Terciptanya HMTS FT-UNTAD yang berdaya saing');
        $response->assertSee('aspirasi mahasiswa teknik sipil');
        $response->assertSee('kegiatan-kegiatan dalam bidang ketekniksipilan');
        $response->assertSee('kebijakan dan isu-isu yang berkembang');
        $response->assertSee('penalaran keilmuan');

        // Makna Lambang removal assertions
        $response->assertDontSee('Makna Lambang HMTS');
        $response->assertDontSee('Rangka Segitiga Truss');
        $response->assertDontSee('Kuning Helm Keselamatan');

        // Mars HMTS assertions
        $response->assertSee('Mars HMTS FT-UNTAD');
        $response->assertSee('Mahasiswa Teknik Sipil');
        $response->assertSee('Tulang punggung peradaban');
        $response->assertSee('Suara Panggilanmu');
        $response->assertSee('Ibu pertiwi ini baktiku untukmu');
        $response->assertSee('https://www.youtube.com/embed/6A_Picg1NnE');
    }

    public function test_about_page_renders_seeded_database_with_bph_division(): void
    {
        $this->seed();

        $response = $this->get('/tentang');

        $response->assertStatus(200);
        $response->assertSee('Muhammad Farhan Mubarak');
        $response->assertSee('Kifli');
        $response->assertSee('Erzy');
        $response->assertSee('Indah');
        $response->assertSee('Tiara');
        $response->assertSee('Yani');
        $response->assertSee('Syauqi');
        $response->assertSee('Dava');
        $response->assertSee('Dhani');
        $response->assertSee('Juven');
        $response->assertSee('Dinay');
        $response->assertSee('Nathan');
        $response->assertSee('Farhan');
    }

    public function test_programs_page_returns_successful_response(): void
    {
        $response = $this->get('/kegiatan');

        $response->assertStatus(200);
        $response->assertSee('Program Kerja');
        $response->assertSee('Tidak ada program kerja');
    }

    public function test_services_page_returns_successful_response(): void
    {
        $response = $this->get('/layanan');

        $response->assertStatus(200);
        $response->assertSee('Layanan Mahasiswa');
        $response->assertSee('Pinjam Alat Lab');
    }
}
