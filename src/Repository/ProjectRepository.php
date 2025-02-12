<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 * 
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    public function save(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Project $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retrieve recent projects.
     *
     * @param int $limit
     * @return Project[]
     */
    public function findRecentProjects(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.creationDate', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filter projects by specific criteria.
     *
     * @param array $criteria
     * @return Project[]
     */
    public function findByCriteria(array $criteria): array
    {
        $qb = $this->createQueryBuilder('p');
        foreach ($criteria as $field => $value) {
            $qb->andWhere("p.$field = :$field")
               ->setParameter($field, $value);
        }
        return $qb->getQuery()->getResult();
    }
}
