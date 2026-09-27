<?php

namespace App\Actions;

use App\Models\Photo;
use App\Models\Profile;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class UpdateAvatarAction
{
    public function execute(
        Profile $profile,
        UploadedFile $avatar,
    ): Photo {
        $path = $avatar->store(
            'avatars',
            'public',
        );

        if ($path === false) {
            throw new RuntimeException(
                'Failed to store avatar.'
            );
        }

        try {
            return DB::transaction(function () use (
                $profile,
                $path
            ): Photo {
                $oldPhotos = $profile->photos()
                    ->get();

                $profile->photos()->delete();

                /** @var Photo $photo */
                $photo = $profile->photos()->create([
                    'path' => $path,
                    'disk' => 'public',
                    'alt_text' => $profile->user->name,
                    'sort_order' => 0,
                ]);

                foreach ($oldPhotos as $oldPhoto) {
                    Storage::disk($oldPhoto->disk)
                        ->delete($oldPhoto->path);
                }

                return $photo;
            });
        } catch (\Throwable $exception) {
            Storage::disk('public')->delete($path);

            throw $exception;
        }
    }
}