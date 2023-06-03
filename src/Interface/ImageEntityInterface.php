<?php

namespace App\Interface;

interface ImageEntityInterface
{
    public function getImages(): array;

    public function setImages(array $images): self;

    public function addImage(string $image): self;

    public function removeImage(string $image): void;
    
}