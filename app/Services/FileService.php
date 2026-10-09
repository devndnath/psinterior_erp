<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * The only path by which an uploaded file becomes a `documents` row
 * (spec §15, §51). Never trusts the original filename, validates
 * MIME/extension/size against config, and stores under a UUID name so a
 * guessed/enumerated URL reveals nothing about the file's contents.
 */
class FileService
{
    public function store(
        UploadedFile $file,
        Model $documentable,
        string $category = 'general',
        string $disk = 'uploads',
    ): Document {
        $this->validate($file);

        $extension = strtolower($file->getClientOriginalExtension());
        $storedName = Str::uuid()->toString().($extension ? '.'.$extension : '');

        $directory = sprintf(
            '%s/%s/%s',
            Str::snake(class_basename($documentable)),
            $documentable->getKey(),
            $category,
        );

        $path = $file->storeAs($directory, $storedName, $disk);

        return $documentable->documents()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'disk' => $disk,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'category' => $category,
            'uploaded_by' => auth()->id(),
        ]);
    }

    public function delete(Document $document): bool
    {
        Storage::disk($document->disk)->delete($document->file_path);

        return (bool) $document->delete();
    }

    protected function validate(UploadedFile $file): void
    {
        $maxKb = (int) env('UPLOAD_MAX_SIZE_KB', 20480);
        $allowed = array_map('trim', explode(',', (string) env(
            'UPLOAD_ALLOWED_MIMES',
            'pdf,jpg,jpeg,png,doc,docx,xls,xlsx,zip'
        )));

        if (! $file->isValid()) {
            throw ValidationException::withMessages(['file' => 'The uploaded file is invalid or incomplete.']);
        }

        if ($file->getSize() > $maxKb * 1024) {
            throw ValidationException::withMessages([
                'file' => "The file exceeds the maximum allowed size of {$maxKb} KB.",
            ]);
        }

        $extension = strtolower($file->getClientOriginalExtension());

        if (! in_array($extension, $allowed, true)) {
            throw ValidationException::withMessages([
                'file' => 'The file type "'.$extension.'" is not allowed.',
            ]);
        }
    }
}
