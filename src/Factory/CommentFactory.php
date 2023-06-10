<?php

namespace App\Factory;

use App\Entity\Trick;
use App\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;

class CommentFactory
{
    public function __construct(private Security $security)
    {
    }

    public function createOne(Trick $trick, string $content): Comment
    {
        $comment = new Comment();
        $comment->setAuthor($this->security->getUser())
            ->setTrick($trick)
            ->setCreatedAt(\DateTimeImmutable::createFromMutable(new \DateTime()))
            ->setContent($content);

        return $comment;
    }
}
