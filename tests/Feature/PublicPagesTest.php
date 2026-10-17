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
        $response->assertSee('01. KETUA');
        $response->assertSee('8 DIVISI PELAKSANA');
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
