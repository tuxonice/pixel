<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function findValidCategories(): array
    {
        $mediaFolder = public_path('media');
        return array_filter(scandir($mediaFolder), function($folder) use($mediaFolder) {
            $fullPath = $mediaFolder . DIRECTORY_SEPARATOR . $folder;
            return !in_array($folder, ['.', '..']) && is_dir($fullPath);
        });
    }
}
