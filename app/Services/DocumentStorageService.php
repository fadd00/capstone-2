<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentStorageService
{
    public const DISK = 'local';

    public function store(UploadedFile $file, string $field): string
    {
        return $file->store("pendaftaran/{$field}", self::DISK);
    }

    public function delete(?string $path): bool
    {
        if ($path && Storage::disk(self::DISK)->exists($path)) {
            return Storage::disk(self::DISK)->delete($path);
        }

        return false;
    }

    public function exists(?string $path): bool
    {
        return $path ? Storage::disk(self::DISK)->exists($path) : false;
    }
}
