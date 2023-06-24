<?php

namespace App\Repository;

use App\Entity\Trick;
use App\Entity\Comment;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use App\InterfaceClass\PaginableRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Comment>
 *
 * @method Comment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Comment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Comment[]    findAll()
 * @method Comment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CommentRepository extends ServiceEntityRepository implements PaginableRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Comment::class);
    }

    public function save(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Comment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findCountForPagination(object $trick): int
    {
        return $this->createQueryBuilder('c')
            ->select('count(c.id)')
            ->where('c.trick = :trick')
            ->setParameter(':trick', $trick)
            ->getQuery()
            ->getSingleScalarResult();
        ;
    }

    public function findPagination(object $trick, int $maxResults, int $offset): array
    {
        return $this->createQueryBuilder('c')
            ->where('c.trick = :trick')
            ->orderBy('c.createdAt', 'DESC')
            ->setParameter('trick', $trick)
            ->setMaxResults($maxResults)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult()
        ;
    }
}
