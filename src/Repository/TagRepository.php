<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $managerRegistry)
    {
        parent::__construct($managerRegistry, Tag::class);
    }

    public function search(string $query, User $user)
    {
        $qb = $this->createQueryBuilder('t');

        $qb
            ->andWhere('t IN (SELECT DISTINCT t2 FROM ' . Tag::class . ' t2 INNER JOIN t2.entries e WHERE e.user = :user)')
            ->setParameter('user', $user)
        ;

        if (strlen($query) > 0) {
            $qb
                ->andWhere('t.name LIKE :query')
                ->setParameter('query', "%{$query}%")
            ;
        }

        $qb->add('orderBy', 't.name ASC');

        return $qb;
    }
}
