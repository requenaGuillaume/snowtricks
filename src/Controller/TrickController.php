<?php

namespace App\Controller;

use DateTime;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em)
    {}

    #[Route('/trick/show/{slug}', name: 'app_trick', requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'])]
    public function index(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick page not found.');
            return $this->redirectToRoute('app_home');
        }

        $allImages = $trick->getImages();
        $mainImage = $allImages[0];
        $otherImages = [];

        for($i = 1; $i < count($allImages); ++$i){
            $otherImages[] = $allImages[$i];
        }

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();

            $comment = new Comment();
            $comment->setAuthor($this->getUser())
                ->setTrick($trick)
                ->setCreatedAt(DateTimeImmutable::createFromMutable(new DateTime()))
                ->setContent($content);

            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', 'Your comment has been sent');
        }

        $comments = $commentRepository->findBy([
                'trick' => $trick
            ],
            [
                'createdAt' => 'DESC'
            ]
        );

        return $this->render('trick/index.html.twig', [
            'trick' => $trick,
            'mainImage' => $mainImage,
            'otherImages' => $otherImages,
            'commentForm' => $form->createView(),
            'comments' => $comments
        ]);
    }
}
