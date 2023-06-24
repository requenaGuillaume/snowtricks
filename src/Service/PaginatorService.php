<?php

namespace App\Service;

use App\InterfaceClass\HasPaginablePropertyInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\InterfaceClass\PaginableEntityInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class PaginatorService
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {
    }

    public function paginate(
        string $entityClassToPaginate,
        HasPaginablePropertyInterface $subjectEntity,
        int $maxResults
    ): array {
        $repository = $this->em->getRepository($entityClassToPaginate);
        $total = $repository->findCountForPagination($subjectEntity);

        $request = $this->requestStack->getCurrentRequest();

        $currentPage = $this->getUrlPageNumber($request);

        $numberOfPages =  intval(ceil($total / $maxResults));

        if ($currentPage > $numberOfPages) {
            $currentPage = $numberOfPages;
        }

        $offset = $maxResults * ($currentPage - 1);

        $currentEntities = $repository->findPagination($subjectEntity, $maxResults, $offset);

        return [
            'previousPage' => $currentPage - 1,
            'currentPage' => $currentPage,
            'nextPage' => $currentPage + 1,
            'numberOfPages' => $numberOfPages,
            'currentEntities' => $currentEntities
        ];
    }

    private function getUrlPageNumber(Request $request): int
    {
        $pageNumber = intval($request->query->get('page'));

        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }

        return $pageNumber;
    }
}
