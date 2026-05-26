<?php

namespace App\Http\Controllers;

use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MediaFileController extends Controller
{
    /**
     * Fallback when the web server cannot resolve the public/media symlink (common on Windows).
     * Tries canonical storage/app/media first, then legacy storage/app/public/media if present.
     */
    public function __invoke(string $path): BinaryFileResponse
    {
        $relative = str_replace(["\0", '..'], '', $path);
        $relative = str_replace('\\', '/', $relative);

        foreach ([storage_path('app/media'), storage_path('app/public/media')] as $diskRoot) {
            if (! is_dir($diskRoot)) {
                continue;
            }

            $fullPath = $diskRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $diskReal = realpath($diskRoot);
            $fileReal = realpath($fullPath);

            if ($diskReal === false || $fileReal === false || ! str_starts_with($fileReal, $diskReal)) {
                continue;
            }

            if (is_file($fileReal)) {
                return response()->file($fileReal);
            }
        }

        abort(404);
    }
}
