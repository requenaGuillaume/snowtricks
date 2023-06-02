<?php

namespace App\Service;

use App\Entity\Trick;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesService
{

    // TODO Replace all Trick $trick by all entity type ? + $folder in constructor ?
    public function removeOneImage(Trick $trick, string $image, string $folder): void
    {
        $trick->removeImage($image);

        unlink("$folder/$image");
    }

    public function removeAllImages(Trick $trick, string $folder)
    {
        foreach($trick->getImages() as $image){
            unlink("$folder/$image");
        }
    }

    public function addImages(Trick $trick, array $imagesToAdd, string $folder): void
    {
        $files = scandir($folder, SCANDIR_SORT_DESCENDING);
        $latestImageNumber = $this->getLastImageNumber($files);

        foreach($imagesToAdd as $image){
            ++$latestImageNumber;
            $extension = explode('.', $image->getClientOriginalName())[1];
            $imageName = "$latestImageNumber.$extension";

            /** @var UploadedFile $image */
            $image->move($folder, $imageName);

            $trick->addImage($imageName);
        }
    }

    private function getLastImageNumber(array|bool $files): int
    {
        $latestImageNumber = 0;

        foreach($files as $file){
            $imageNumber = explode('.', $file)[0];

            if($imageNumber > $latestImageNumber){
                $latestImageNumber = $imageNumber;
            }
        }

        return $latestImageNumber;
    }

}