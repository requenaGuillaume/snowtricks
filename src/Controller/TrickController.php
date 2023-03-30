<?php

namespace App\Controller;

use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TrickController extends AbstractController
{
    #[Route('/trick/show/{slug}', name: 'app_trick', requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'])]
    public function index(Trick $trick): Response
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

        return $this->render('trick/index.html.twig', [
            'trick' => $trick,
            'mainImage' => $mainImage,
            'otherImages' => $otherImages
        ]);
    }
}
