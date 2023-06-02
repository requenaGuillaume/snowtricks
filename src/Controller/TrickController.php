<?php

namespace App\Controller;

use DateTime;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\Comment;
use App\Form\TrickFormType;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Service\ImagesService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class TrickController extends AbstractController
{
// TODO : l'image 42 n'a pas été supprimé du folder, mais bien suppr en base, investiguer
    public function __construct(
        private EntityManagerInterface $em,
        private ImagesService $imagesService,

        #[Autowire('%kernel.project_dir%/public/assets/images/tricks')]
        private $dir
    )
    {}

    #[Route('/trick/show/{slug}', name: 'app_trick', requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'])]
    public function show(
        ?Trick $trick, 
        Request $request, 
        CommentRepository $commentRepository, 
        PaginatorInterface $paginator
    ): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick page not found.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        // Creation de commentaires
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

        $pagination = $paginator->paginate(
            $commentRepository->findPaginationQuery($trick),
            $request->query->get('page', 1),
            10
        );

        return $this->render('trick/index.html.twig', [
            'trick' => $trick,
            'mainImage' => $trick->getMainImage(),
            'otherImages' => $trick->getSecondariesImages(),
            'commentForm' => $form->createView(),
            'pagination' => $pagination
        ]);
    }

    #[IsGranted('ROLE_USER')]
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
            $trick->setImages([]);
        }       

        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $formImages = $form['images']->getData();

            if(!$edit && !$formImages){
                $this->addFlash('danger', 'You must choose at least one image for the trick');
                return $this->redirectToRoute('app_trick_create');
            }

            $this->imagesService->addImages($trick, $formImages, $this->dir, $edit);

            // video - remplace dans l'url
            $formVideo = $form['video']->getData();

            if($formVideo){
                $trick->addVideo(str_replace('watch?v=', 'embed/',$formVideo ));
            }

            if(!$edit){
                // On rempli la new Trick()
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
            return $this->redirectToRoute('app_home');
        }

        return $this->render('trick/create_or_edit.html.twig', [
            'form' => $form->createView(),
            'edit' => $edit,
            'trick' => $trick
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/delete/{slug}',
        name: 'app_trick_delete',
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]']
    )]
    public function delete(Trick $trick): Response
    {
        $this->imagesService->removeAllImages($trick, $this->dir);

        $this->em->remove($trick);
        $this->em->flush();

        $this->addFlash('success', 'The trick has been deleted');
        return $this->redirectToRoute('app_home');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/edit/{slug}/remove-image/{image}', 
        name: 'app_trick_remove_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)']
    )]
    public function removeImage(Trick $trick, string $image): JsonResponse
    {
        // Gestion d'erreur
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new JsonResponse(null, 404);
        }

        if($trick->getMainImage() === $image){
            $this->addFlash('danger', 'You cannot delete the main image');
            return new JsonResponse(null, 403);
        }

        if(!in_array($image, $trick->getImages())){
            $this->addFlash('danger', 'This trick does not have this image');
            return new JsonResponse(null, 404);
        }

        $this->imagesService->removeOneImage($trick, $image, $this->dir);
        $this->em->flush();

        return new JsonResponse();
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/edit/{slug}/main-image/{image}', 
        name: 'app_trick_main_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)']
    )]
    public function setMainImage(Trick $trick, string $image): JsonResponse
    {
        //  Gestion d'erreur
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new JsonResponse(null, 404);
        }

        $trick->setMainImage($image);

        $this->em->flush();

        return new JsonResponse();
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/edit/{slug}/remove-video/{videoIndex}', 
        name: 'app_trick_remove_video',
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'videoIndex' => '\d+']
    )]
    public function removeVideo(Trick $trick, int $videoIndex): JsonResponse
    {
        // Gestion d'erreur
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new JsonResponse(null, 404);
        }
        
        $videos = $trick->getVideos();

        // Suppression d'une video
        if(!$videos || $videos[$videoIndex]){
            $this->addFlash('danger', 'Video not found');
            return new JsonResponse(null, 404);
        }

        $trick->removeVideo($videos[$videoIndex]);
        $this->em->flush();

        return new JsonResponse();
    }

}
