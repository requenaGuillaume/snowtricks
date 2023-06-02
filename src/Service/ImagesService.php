<?php

namespace App\Service;

use App\Interface\ImagesInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesService
{

    // TODO  $folder in constructor ?
    public function removeOneImage(ImagesInterface $entity, string $image, string $folder): void
    {
        $entity->removeImage($image);

        unlink("$folder/$image");
    }

    public function removeAllImages(ImagesInterface $entity, string $folder)
    {
        foreach($entity->getImages() as $image){
            unlink("$folder/$image");
        }
    }

    public function addImages(ImagesInterface $entity, array $imagesToAdd, string $folder): void
    {
        $files = scandir($folder, SCANDIR_SORT_DESCENDING);
        $latestImageNumber = $this->getLastImageNumber($files);

        foreach($imagesToAdd as $image){
            ++$latestImageNumber;
            $extension = explode('.', $image->getClientOriginalName())[1];
            $imageName = "$latestImageNumber.$extension";

            /** @var UploadedFile $image */
            $image->move($folder, $imageName);

            $entity->addImage($imageName);
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