<?php

namespace App\Service;

use App\InterfaceClass\ImageEntityInterface;
use App\InterfaceClass\ImageServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesService implements ImageServiceInterface
{
    public function __construct(private string $folder)
    {
    }

    public function removeOneImage(ImageEntityInterface $entity, string $image): void
    {
        $entity->removeImage($image);

        unlink("{$this->folder}/$image");
    }

    public function removeAllImages(ImageEntityInterface $entity): void
    {
        foreach ($entity->getImages() as $image) {
            unlink("{$this->folder}/$image");
        }
    }

    public function addImages(ImageEntityInterface $entity, array $imagesToAdd): void
    {
        $files = scandir($this->folder, SCANDIR_SORT_DESCENDING);
        $latestImageNumber = $this->getLastImageNumber($files);

        foreach ($imagesToAdd as $image) {
            ++$latestImageNumber;
            $extension = explode('.', $image->getClientOriginalName())[1];
            $imageName = "$latestImageNumber.$extension";

            /** @var UploadedFile $image */
            $image->move($this->folder, $imageName);

            $entity->addImage($imageName);
        }
    }

    private function getLastImageNumber(array|bool $files): int
    {
        $latestImageNumber = 0;

        foreach ($files as $file) {
            $imageNumber = explode('.', $file)[0];

            if ($imageNumber > $latestImageNumber) {
                $latestImageNumber = $imageNumber;
            }
        }

        return $latestImageNumber;
    }
}
