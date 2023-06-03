<?php

namespace App\Controller;

use App\Entity\Trick;
use App\Form\TrickFormType;
use App\Form\CommentFormType;
use App\Service\ImagesService;
use App\Factory\CommentFactory;
use App\Factory\TrickFactory;
use App\Repository\CommentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TrickController extends AbstractController
{

    public function __construct(private EntityManagerInterface $em, private ImagesService $imagesService)
    {}

    #[Route('/trick/show/{slug}',
        name: 'app_trick', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'],
        methods: ['GET', 'POST'])
    ]
    public function show(        
        Request $request,
        CommentRepository $commentRepository,
        CommentFactory $commentFactory,
        PaginatorInterface $paginator,
        ?Trick $trick, 
    ): Response
    {
        if(!$trick){
            $this->addFlash('danger', 'Trick page not found.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $comment = $commentFactory->createOne($trick, $form->get('content')->getData());
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
    #[Route('/trick/create', name: 'app_trick_create', methods: ['GET', 'POST'])]
    #[Route('/trick/edit/{slug}', 
        name: 'app_trick_edit', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'],
        methods: ['GET', 'POST'])
    ]
    public function createOrEdit(Request $request, TrickFactory $trickFactory, ?Trick $trick = null): Response
    {
        $edit = $this->isEditMode($request);

        if(!$edit){
            $trick = $trickFactory->createOneEmpty();
        }

        if($edit && !$trick){
            $this->addFlash('danger', 'Trick not found');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(TrickFormType::class, $trick);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $formImages = $form['images']->getData();

            if(!$edit && !$formImages){
                $this->addFlash('danger', 'You must choose at least one image for the trick');
                return $this->redirectToRoute('app_trick_create');
            }

            $this->imagesService->addImages($trick, $formImages);

            $formVideo = $form['video']->getData();

            if($formVideo){
                $trick->addVideo(str_replace('watch?v=', 'embed/', $formVideo));
            }

            if(!$edit){
                $slugger = new AsciiSlugger();
                $slug = $slugger->slug(strtolower($form['title']->getData())); 
                $trickFactory->fillMissingFields($trick, $slug);                
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
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]'],
        methods: ['GET'])
    ]
    public function delete(Trick $trick): RedirectResponse
    {
        // TODO ajout gestion d'erreur $trick not found
        $this->imagesService->removeAllImages($trick);
        $this->em->remove($trick);
        $this->em->flush();

        $this->addFlash('success', 'The trick has been deleted');
        return $this->redirectToRoute('app_home');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/edit/{slug}/remove-image/{image}', 
        name: 'app_trick_remove_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)'],
        methods: ['GET']
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

        $this->imagesService->removeOneImage($trick, $image);
        $this->em->flush();

        return new JsonResponse();
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/trick/edit/{slug}/main-image/{image}', 
        name: 'app_trick_main_image', 
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'image' => '\d+\.{1}(jpg|jpeg|png)'],
        methods: ['GET']
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
        requirements: ['slug' => '[a-z0-9][a-z0-9-]{0,}[a-z0-9]', 'videoIndex' => '\d+'],
        methods: ['GET']
    )]
    public function removeVideo(Trick $trick, int $videoIndex): JsonResponse
    {
        // Gestion d'erreur
        if(!$trick){
            $this->addFlash('danger', 'Trick not found');
            return new JsonResponse(null, 404);
        }
        
        $videos = $trick->getVideos();

        // Gestion d'erreur - Suppression d'une video
        if(!$videos || !$videos[$videoIndex]){
            $this->addFlash('danger', 'Video not found');
            return new JsonResponse(null, 404);
        }

        $trick->removeVideo($videos[$videoIndex]);
        $this->em->flush();

        return new JsonResponse();
    }


    private function isEditMode(Request $request): bool
    {
        $edit = false;

        if($request->attributes->get('_route') === 'app_trick_edit'){
            $edit = true;
        }

        return $edit;
    }

}
