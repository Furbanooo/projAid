<?php

namespace App\Repository;

use App\Entity\Like;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Like>
 * 
 * @method Like|null find($id, $lockMode = null, $lockVersion = null)
 * @method Like|null findOneBy(array $criteria, array $orderBy = null)
 * @method Like[]    findAll()
 * @method Like[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Like::class);
    }

    public function save(Like $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Like $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Count the number of likes on a given project.
     *
     * @param int $projectId
     * @return int
     */
    public function countLikesByProject(int $projectId): int
    {
        return $this->createQueryBuilder('l')
            ->select('count(l.id)')
            ->andWhere('l.project = :projectId')
            ->setParameter('projectId', $projectId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Check if a user has already liked a project.
     *
     * @param int $projectId
     * @param int $userId
     * @return bool
     */
    public function hasUserLikedProject(int $projectId, int $userId): bool
    {
        return (bool) $this->createQueryBuilder('l')
            ->andWhere('l.project = :projectId')
            ->andWhere('l.user = :userId')
            ->setParameter('projectId', $projectId)
            ->setParameter('userId', $userId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
