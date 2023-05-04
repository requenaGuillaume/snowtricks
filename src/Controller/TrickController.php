<?php

namespace App\Controller;

use DateTime;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Form\TrickFormType;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\String\Slugger\AsciiSlugger;

class TrickController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em)
    {}

    #[Route('/trick/show/{slug}', name: 'app_trick', requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'])]
    public function show(?Trick $trick, Request $request, CommentRepository $commentRepository): Response
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

    #[Route('/trick/create', name: 'app_trick_create')]
    #[Route('/trick/edit/{slug}', name: 'app_trick_edit', requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'])]
    public function createOrEdit(?Trick $trick = null, Request $request): Response
    {
        $edit = false;

        if($request->attributes->get('_route') === 'app_trick_edit'){
            if(!$trick){
                $this->addFlash('danger', 'Trick not found');
                return $this->redirectToRoute('app_home');
            }

            $edit = true;
        }

        if(!$edit){
            $trick = new Trick();
        }       

        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $formImages = $form['images']->getData();

            if(!$edit && !$formImages){
                $this->addFlash('error', 'You must choose at least one image for the trick');
                return $this->redirectToRoute('app_trick_create');
            }

            $directory = __DIR__.'//../../public/assets/images/tricks';

            $files = scandir($directory, SCANDIR_SORT_DESCENDING);
   
            // TODO refacto -> getlastImageNumber
            $latestImageNumber = 0;
            foreach($files as $file){
                $imageNumber = explode('.', $file)[0];

                if($imageNumber > $latestImageNumber){
                    $latestImageNumber = $imageNumber;
                }
            }

            foreach($formImages as $image){
                ++$latestImageNumber;
                $extension = explode('.', $image->getClientOriginalName())[1];
                $imageName = "$latestImageNumber.$extension";

                /** @var UploadedFile $image */
                $image->move($directory, $imageName);

                if(!$edit){
                    $trick->setImages([]); // weird too xd
                }

                $trick->addImage($imageName);
            }

            if(!$edit){
                $title = $form['title']->getData();
                $slugger = new AsciiSlugger();
                $slug = $slugger->slug(strtolower($title)); 
                $trick->setSlug($slug)
                    ->setCreatedAt(new DateTimeImmutable())
                    ->setAuthor($this->getUser());
                $this->em->persist($trick);
                $this->addFlash('success', 'Trick has been successfully created');
            }else{
                $this->addFlash('success', 'Trick has been successfully updated');
            }

            $this->em->flush();
            return $this->redirectToRoute('app_trick', ['slug' => $trick->getSlug()]);
        }
        return $this->render('trick/create_or_edit.html.twig', [
            'form' => $form->createView(),
            'edit' => $edit,
            'trick' => $trick
        ]);
    }

    #[Route('/trick/edit/{slug}/remove-image/{image}', 
        name: 'app_trick_remove_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)']
    )]
    public function removeImage(Trick $trick, string $image): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new Response();
        }

        if(!in_array($image, $trick->getImages())){
            $this->addFlash('danger', 'This trick does not have this image');
            return new Response();
        }

        $trick->removeImage($image);
        $this->em->flush();

        return new Response();
    }

    #[Route('/trick/edit/{slug}/main-image/{image}', 
        name: 'app_trick_main_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)']
    )]
    public function setMainImage(Trick $trick, string $image): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new Response();
        }

        $trick->setMainImage($image);

        $this->em->flush();

        return new Response();
    }


    #[Route('/trick/edit/{slug}/remove-video/{videoIndex}', 
        name: 'app_trick_remove_image',
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'videoIndex' => '\d+']
    )]
    public function removeVideo(Trick $trick, int $videoIndex): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new Response();
        }

        $video = $trick->getVideos()[$videoIndex];

        if(!$video){
            $this->addFlash('danger', 'Video not found');
            return new Response();
        }

        $trick->removeVideo($video);
        $this->em->flush();

        return new Response();
    }
}
