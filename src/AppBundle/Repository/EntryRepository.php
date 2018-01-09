<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

use AppBundle\Entity\User;
use AppBundle\Entity\Entry;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Category;

use AppBundle\Criteria\EntryCriteria;

use Doctrine\ORM\QueryBuilder;
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
        $qb->select('e')
            ->from(Entry::class, 'e')
            ->leftJoin('e.tags', 't')
            ->andWhere('e.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->orderBy('e.id', 'DESC')
            ;

        switch ($criteria->sortOrder) {
            case EntryCriteria::SORT_TIMESTAMP_DESC:
                $qb->orderBy('e.id', 'DESC');
                break;
            case EntryCriteria::SORT_TIMESTAMP_ASC:
                $qb->orderBy('e.id', 'ASC');
                break;
        }

        if ($includeVisits) {
            $qb->select('e, v')
                ->leftJoin('e.visits', 'v');
        }

        if ($criteria->category) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $criteria->category);
        }

        if (! empty($criteria->tag)) {
            $qb->andWhere('t.name = :tag')
                ->setParameter('tag', $criteria->tag);
        }

        if ($criteria->pendingOnly) {
            $qb->andWhere('e.pending = 1');
        }

        if ($criteria->query) {
            $this->addFulltextSearch($qb, $criteria->query);
        }

        return $qb;
    }

    public function filter(User $user, EntryCriteria $criteria)
    {
        $qb = $this->getQueryBuilder($user, $criteria);
        $pager = new Pagerfanta(new DoctrineORMAdapter($qb));

        $pager
            ->setMaxPerPage($criteria->limit)
            ->setCurrentPage($criteria->page);

        return $pager;
    }

    public function getCategoryStats(User $user, EntryCriteria $criteria)
    {
        $criteria = clone $criteria;
        $criteria->category    = null;
        $criteria->pendingOnly = false;

        $innerQb = $this->getQueryBuilder($user, $criteria, $includeVisits = false);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('en.category, SUM(en.pending) AS pending, COUNT(en.id) AS total')
            ->from(Entry::class, 'en')
            ->andWhere('en.id IN (' . $innerQb->getDql() . ')')
            ->add('groupBy', 'en.category')
            ->add('orderBy', 'en.category ASC');

        $qb->setParameters($innerQb->getParameters());

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

        $innerQb = $this->getQueryBuilder($user, $criteria, $includeVisits = false);

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('tag.name, tag.id, SUM(en.pending) AS pending, COUNT(en.id) AS total')
            ->from(Tag::class, 'tag')
            ->innerJoin('tag.entries', 'en')
            ->andWhere('en.id IN (' . $innerQb->getDql() . ')')
            ->add('groupBy', 'tag.id')
            ->add('orderBy', 'pending DESC');

        $qb->setParameters($innerQb->getParameters());
        $qb->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function urlAlreadyTaken(User $user, $url, $excludeId)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')
            ->from(Entry::class, 'e')
            ->andWhere('e.user = :user')
            ->andWhere('e.url = :url')
            ->setParameter('user', $user)
            ->setParameter('url', $url)
            ;

        if ($excludeId) {
            $qb
                ->andWhere('e.id <> :excludeId')
                ->setParameter('excludeId', $excludeId)
                ;
        }

        $entries = $qb->getQuery()->getResult();

        return (count($entries) > 0);
    }

    private function addFulltextSearch(QueryBuilder $qb, $query)
    {
        if (empty($query)) {
            return;
        }

        $partNumber = 1;
        foreach (explode(' ', $query) as $word) {
            if (empty($word)) {
                continue;
            }

            $word = '%' . strtolower($word) . '%';
            $parameterName = ':querypart' . $partNumber++;
            $qb->andWhere("(LOWER(t.name) LIKE $parameterName OR e.title LIKE $parameterName or e.url LIKE $queryVariable)")
                ->setParameter($parameterName, $word);
        }
    }
}
