<?php

namespace App\Interface;

interface ImageServiceInterface
{
    public function removeOneImage(ImageEntityInterface $entity, string $image): void;

    public function removeAllImages(ImageEntityInterface $entity): void;

    public function addImages(ImageEntityInterface $entity, array $imagesToAdd): void;    
}