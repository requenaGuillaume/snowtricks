<?php

namespace App\Factory;

use App\Entity\Trick;
use App\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;

class TrickFactory
{

    public function __construct(private Security $security)
    {
        
    }

    public function createOneEmpty(): Trick
    {
        $trick = new Trick();
        $trick->setImages([]);

        return $trick;
    }

    public function fillMissingFields(Trick $trick, string $slug): Trick
    {
        $trick->setSlug($slug)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setAuthor($this->security->getUser());

        return $trick;
    }

}