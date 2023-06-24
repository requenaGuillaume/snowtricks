<?php

namespace App\InterfaceClass;

interface PaginableRepositoryInterface
{
    public function findCountForPagination(object $object): int;

    public function findPagination(object $object, int $maxResults, int $offset): array;
}
