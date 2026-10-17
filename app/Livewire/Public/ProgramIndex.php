<?php

namespace App\Livewire\Public;

use App\Models\Program;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ProgramIndex extends Component
{
    public string $selectedCategory = 'all';

    public ?int $activeProgramId = null;

    public function setCategory(string $category): void
    {
        $this->selectedCategory = $category;
    }

    public function openTorModal(int $programId): void
    {
        Program::findOrFail($programId);
        $this->activeProgramId = $programId;
    }

    public function closeTorModal(): void
    {
        $this->activeProgramId = null;
    }

    public function render()
    {
        return view('livewire.public.program-index', [
            'categories' => [
                'all' => 'Semua Kategori',
                'internal' => 'Internal Organisasi',
                'competition' => 'Kompetisi & Lomba',
                'workshop' => 'Workshop & Pelatihan',
                'community' => 'Pengabdian Masyarakat',
                'seminar' => 'Seminar Akademik',
            ],
            'filteredPrograms' => Program::query()->when($this->selectedCategory !== 'all', fn ($query) => $query->where('category', $this->selectedCategory))->orderBy('starts_at')->get(),
            'activeProgram' => $this->activeProgramId ? Program::find($this->activeProgramId) : null,
        ]);
    }
}
