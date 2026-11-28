<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CandidatePhotoService
{
    /**
     * @return array{photo: string, photo_thumbnail: string}
     */
    public function store(UploadedFile $photo): array
    {
        $imageSize = getimagesize($photo->getRealPath());
        if ($imageSize === false || $imageSize[0] > 4000 || $imageSize[1] > 4000) {
            throw ValidationException::withMessages(['photo' => __('Picha haikubaliki au ina vipimo vikubwa kupita kiasi.')]);
        }

        $contents = file_get_contents($photo->getRealPath());
        $source = $contents === false ? false : imagecreatefromstring($contents);
        if ($source === false) {
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kusomwa.')]);
        }

        try {
            $profile = $this->squareJpeg($source, 512, 88);
            $thumbnail = $this->squareJpeg($source, 160, 82);
        } finally {
            imagedestroy($source);
        }

        $basename = bin2hex(random_bytes(16));
        $profilePath = "candidates/{$basename}.jpg";
        $thumbnailPath = "candidates/thumbnails/{$basename}.jpg";

        if (! Storage::disk('public')->put($profilePath, $profile)) {
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kuhifadhiwa.')]);
        }

        if (! Storage::disk('public')->put($thumbnailPath, $thumbnail)) {
            Storage::disk('public')->delete($profilePath);
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kuhifadhiwa.')]);
        }

        return ['photo' => $profilePath, 'photo_thumbnail' => $thumbnailPath];
    }

    private function squareJpeg(\GdImage $source, int $size, int $quality): string
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $cropSize = min($sourceWidth, $sourceHeight);
        $sourceX = (int) (($sourceWidth - $cropSize) / 2);
        $sourceY = (int) (($sourceHeight - $cropSize) / 2);
        $image = imagecreatetruecolor($size, $size);

        if ($image === false) {
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kuchakatwa.')]);
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        imagefill($image, 0, 0, $white);
        if (! imagecopyresampled($image, $source, 0, 0, $sourceX, $sourceY, $size, $size, $cropSize, $cropSize)) {
            imagedestroy($image);
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kuchakatwa.')]);
        }

        ob_start();
        $encoded = imagejpeg($image, null, $quality);
        $contents = ob_get_clean();
        imagedestroy($image);

        if (! $encoded || ! is_string($contents) || $contents === '') {
            throw ValidationException::withMessages(['photo' => __('Picha haikuweza kuchakatwa.')]);
        }

        return $contents;
    }
}
