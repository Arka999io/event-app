<?php

namespace App\Actions;

use App\Models\Event;
use App\Models\Photo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DeleteEventPhotoAction
{
    public function execute(
        Event $event,
        Photo $photo,
    ): void {
        DB::transaction(function () use ($event, $photo): void {
            $disk = $photo->disk;
            $path = $photo->path;

            $photo->delete();

            $event->photos()
                ->orderBy('sort_order')
                ->get()
                ->values()
                ->each(function (Photo $photo, int $index): void {
                    $photo->update([
                        'sort_order' => $index,
                    ]);
                });

            Storage::disk($disk)->delete($path);
        });
    }
}
