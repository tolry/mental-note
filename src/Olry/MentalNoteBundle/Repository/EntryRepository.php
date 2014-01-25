<?php

namespace Olry\MentalNoteBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;

use Olry\MentalNoteBundle\Entity\User;
use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Entity\Tag;
use Olry\MentalNoteBundle\Entity\Category;

use Olry\MentalNoteBundle\Criteria\EntryCriteria;

use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 */
class EntryRepository extends EntityRepository
{

    public function getQueryBuilder(User $user, EntryCriteria $criteria, $includeVisits = true)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e, t')
            ->from('Olry\MentalNoteBundle\Entity\Entry', 'e')
            ->leftJoin('e.tags', 't')
            ->andWhere('e.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->add('orderBy', 'e.created DESC');

        if ($includeVisits) {
            $qb->select('e, t, v')
                ->leftJoin('e.visits', 'v');
        }

        if ($criteria->category) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $criteria->category);
        }

        if ( ! empty($criteria->tag)) {
            $qb->andWhere('t.name = :tag')
                ->setParameter('tag', $criteria->tag);
        }

        if ($criteria->pendingOnly) {
            $qb->andWhere('e.pending = 1');
        }

        if ($criteria->query) {
            $parts = explode(' ', $criteria->query);
            $partNumber = 1;
            foreach ($parts as $query) {
                if (empty($query)) {
                    continue;
                }

                $query = '%' . $query . '%';
                $queryVariable = ':query' . $partNumber++;
                $qb->andWhere("(t.name LIKE $queryVariable OR e.title LIKE $queryVariable or e.url LIKE $queryVariable)")
                    ->setParameter($queryVariable, $query);
            }
        }

        return $qb;
    }

    public function filter(User $user, EntryCriteria $criteria)
    {
        $qb = $this->getQueryBuilder($user, $criteria);
        $adapter = new DoctrineORMAdapter($qb);
        $pager   = new Pagerfanta($adapter);

        $pager->setMaxPerPage($criteria->limit)
              ->setCurrentPage($criteria->page);

        return $pager;
    }

    public function getCategoryStats(User $user, EntryCriteria $criteria)
    {
        $criteria = clone $criteria;
        $criteria->category    = null;
        $criteria->pendingOnly = false;

        $qb = $this->getQueryBuilder($user, $criteria, $includeVisits = false);
        $qb->select('e.category, SUM(e.pending) AS pending, COUNT(e.id) AS total')
            ->add('groupBy', 'e.category')
            ->add('orderBy', 'e.category ASC');

        $data = array();
        foreach ($qb->getQuery()->getResult() as $row) {
            $row['category'] = new Category($row['category']);
            $data[] = $row;
        }
        return $data;
    }

    public function getTagStats(User $user, EntryCriteria $criteria, $limit = 20)
    {
        $criteria              = clone $criteria;
        $criteria->tag         = null;
        $criteria->pendingOnly = false;

        $qb = $this->getQueryBuilder($user, $criteria, $includeVisits = false);
        $qb->select('t.name, t.id, SUM(e.pending) AS pending, COUNT(e.id) AS total')
            ->andWhere('t.name IS NOT NULL')
            ->add('groupBy', 't.id')
            ->add('orderBy', 'total DESC');

        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

}
