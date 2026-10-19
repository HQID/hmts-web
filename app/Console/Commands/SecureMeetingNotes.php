<?php

namespace App\Console\Commands;

use App\Models\MeetingNote;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SecureMeetingNotes extends Command
{
    protected $signature = 'hmts:secure-meeting-notes {--apply : Pindahkan berkas setelah backup dan verifikasi}';

    protected $description = 'Periksa/pindahkan lampiran notulensi publik ke disk privat (default: dry run).';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = Storage::disk('private');
        $failed = false;
        foreach (MeetingNote::withoutGlobalScopes()->whereNotNull('file_path')->cursor() as $note) {
            try {
                $manifestPath = 'migration-backups/meeting-notes/'.$note->id.'/manifest.json';
                if ($note->file_disk === 'private') {
                    if (! $private->exists($manifestPath)) {
                        continue;
                    }
                    $manifest = json_decode($private->get($manifestPath), true, flags: JSON_THROW_ON_ERROR);
                    if (! $public->exists($manifest['source']) || MeetingNote::withoutGlobalScopes()->where('file_disk', 'public')->where('file_path', $manifest['source'])->exists()) {
                        continue;
                    }
                    if (hash('sha256', $public->get($manifest['source'])) !== $manifest['sha256'] || hash('sha256', $private->get($note->file_path)) !== $manifest['sha256'] || hash('sha256', $private->get($manifest['backup'])) !== $manifest['sha256']) {
                        throw new RuntimeException('Salinan berubah; pemulihan perlu diperiksa.');
                    }
                    $this->line('Notulensi #'.$note->id.': membersihkan salinan publik tersisa'.($this->option('apply') ? '' : ' (dry run)'));
                    if ($this->option('apply') && ! $public->delete($manifest['source'])) {
                        throw new RuntimeException('Salinan publik gagal dihapus.');
                    }

                    continue;
                }
                $source = $note->file_path;
                if (! $public->exists($source)) {
                    throw new RuntimeException('Berkas sumber tidak ditemukan.');
                }
                $this->line('Notulensi #'.$note->id.($this->option('apply') ? ': memindahkan' : ': siap dipindahkan (dry run)'));
                if (! $this->option('apply')) {
                    continue;
                }
                $contents = $public->get($source);
                $path = 'meeting-notes/'.$note->id.'/'.basename($source);
                $backup = 'migration-backups/meeting-notes/'.$note->id.'/'.hash('sha256', $contents).'-'.basename($source);
                foreach ([$backup, $path] as $target) {
                    if (! $private->put($target, $contents) || hash('sha256', $private->get($target)) !== hash('sha256', $contents)) {
                        throw new RuntimeException('Backup/salinan privat gagal diverifikasi.');
                    }
                }
                if (! $private->put($manifestPath, json_encode(['source' => $source, 'backup' => $backup, 'sha256' => hash('sha256', $contents)], JSON_THROW_ON_ERROR))) {
                    throw new RuntimeException('Manifest pemindahan gagal disimpan.');
                }
                DB::transaction(function () use ($note, $source, $path) {
                    $fresh = MeetingNote::withoutGlobalScopes()->lockForUpdate()->findOrFail($note->id);
                    if ($fresh->file_disk !== 'public' || $fresh->file_path !== $source) {
                        throw new RuntimeException('Notulensi berubah; ulangi pemeriksaan.');
                    }
                    $fresh->update(['file_disk' => 'private', 'file_path' => $path]);
                });
                if (! MeetingNote::withoutGlobalScopes()->where('file_disk', 'public')->where('file_path', $source)->exists() && ! $public->delete($source)) {
                    throw new RuntimeException('Salinan publik gagal dihapus: '.$source);
                }
            } catch (\Throwable $exception) {
                $this->error('Notulensi #'.$note->id.': '.$exception->getMessage());
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
