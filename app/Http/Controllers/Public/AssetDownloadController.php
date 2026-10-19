<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\DesignAsset;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AssetDownloadController extends Controller
{
    public function __invoke(DesignAsset $asset): StreamedResponse
    {
        abort_unless($asset->is_public && $asset->disk === 'public', 404);
        abort_unless(Storage::disk('public')->exists($asset->path), 404);

        return Storage::disk('public')->download($asset->path, basename($asset->path));
    }
}
