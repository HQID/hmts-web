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
        $response->assertSee('Bagus Wicaksono');
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
        $response->assertSee('M. Rayhan Pratama');
        $response->assertSee('Dimas Arya Nugraha');
        $response->assertSee('Fadel Muhammad');
        $response->assertSee('Amanda Putri Lestari');
        $response->assertSee('Siti Nurhaliza');
        $response->assertSee('Bagus Wicaksono');
        $response->assertSee('Reza Pratama');
        $response->assertSee('Annisa Salsabila');
        $response->assertSee('Irfan Ramadhan');
        $response->assertSee('Kevin Alfarizi');
        $response->assertSee('Nadia Rahmawati');
        $response->assertSee('Muhammad Tegar');
        $response->assertSee('Fahri Hidayat');
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
