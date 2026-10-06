<?php

namespace App\Actions;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class UploadEventPhotosAction
{
    /**
     * @param  array<int, UploadedFile>  $photos
     */
    public function execute(
        Event $event,
        array $photos,
    ): void {
        $existingCount = $event->photos()->count();

        if ($existingCount + count($photos) > 8) {
            throw new RuntimeException(
                'An event can have no more than 8 photos.'
            );
        }

        $storedPaths = [];

        try {
            foreach ($photos as $index => $photo) {
                $path = $photo->store(
                    'events/'.$event->id,
                    'public',
                );

                if ($path === false) {
                    throw new RuntimeException(
                        'Failed to store event photo.'
                    );
                }

                $storedPaths[] = $path;
            }

            DB::transaction(function () use (
                $event,
                $storedPaths,
                $existingCount
            ): void {
                foreach ($storedPaths as $index => $path) {
                    $event->photos()->create([
                        'path' => $path,
                        'disk' => 'public',
                        'alt_text' => $event->title,
                        'sort_order' => $existingCount + $index,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            foreach ($storedPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $exception;
        }
    }
}
