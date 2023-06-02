<?php

namespace App\Service;

use App\Entity\Trick;

class ImagesService
{

    // Replace all Trick $trick by all entity type ?
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

    // TODO fix l'ajout de plusieurs images
    public function addImages(Trick $trick ,array $imagesToAdd, string $folder, bool $edit): void
    {
        $files = scandir($folder, SCANDIR_SORT_DESCENDING);
        $latestImageNumber = $this->getLastImageNumber($files);

        foreach($imagesToAdd as $image){
            ++$latestImageNumber;
            $extension = explode('.', $image->getClientOriginalName())[1];
            $imageName = "$latestImageNumber.$extension";

            /** @var UploadedFile $image */
            $image->move($folder, $imageName);

            if(!$edit){
                $trick->setImages([]);
            }

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