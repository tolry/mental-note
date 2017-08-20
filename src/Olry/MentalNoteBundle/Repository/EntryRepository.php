<?php

namespace Olry\MentalNoteBundle\Repository;

use Doctrine\ORM\EntityRepository;

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
        $qb->select('e')
            ->from(Entry::class, 'e')
            ->leftJoin('e.tags', 't')
            ->andWhere('e.user = :user_id')
            ->setParameter('user_id', $user->getId())
            ->orderBy('e.id', 'DESC')
            ;

        if ($includeVisits) {
            $qb->select('e, v')
                ->leftJoin('e.visits', 'v');
        }

        switch ($criteria->sortOrder) {
            case EntryCriteria::SORT_TIMESTAMP_DESC:
                $qb->orderBy('e.id', 'DESC');
                break;
            case EntryCriteria::SORT_TIMESTAMP_ASC:
                $qb->orderBy('e.id', 'ASC');
                break;
        }

        if ($criteria->category) {
            $qb->andWhere('e.category = :category')
                ->setParameter('category', $criteria->category);
        }

        if ($criteria->mode === EntryCriteria::MODE_NO_REGULARLY) {
            $qb->andWhere('e.category <> :category_mode')
                ->setParameter('category_mode', Category::VISIT_REGULARLY);
        } elseif ($criteria->mode === EntryCriteria::MODE_ONLY_REGULARLY) {
            $qb->andWhere('e.category = :category_mode')
                ->setParameter('category_mode', Category::VISIT_REGULARLY);
        }

        if (! empty($criteria->tag)) {
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

                $query = '%' . strtolower($query) . '%';
                $queryVariable = ':query' . $partNumber++;
                $qb->andWhere("(LOWER(t.name) LIKE $queryVariable OR e.title LIKE $queryVariable or e.url LIKE $queryVariable)")
                    ->setParameter($queryVariable, $query);
            }
        }

        return $qb;
    }

    public function filter(User $user, EntryCriteria $criteria)
    {
        $qb = $this->getQueryBuilder($user, $criteria);
        $adapter = new DoctrineORMAdapter($qb);
        $pager = new Pagerfanta($adapter);

        $pager
            ->setMaxPerPage($criteria->limit)
            ->setCurrentPage($criteria->page);

        return $pager;
    }

    public function filterWithoutPager(User $user, EntryCriteria $criteria)
    {
        $qb = $this->getQueryBuilder($user, $criteria);

        $entries = $qb->getQuery()->getResult();
        if ($criteria->sortOrder == EntryCriteria::SORT_LAST_VISIT_ASC) {
            usort($entries, function (Entry $entryA, Entry $entryB) {
                return ($entryA->getLastVisitTimestamp() < $entryB->getLastVisitTimestamp())
                    ? -1
                    : 1;
            });
        }

        return $entries;
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
}
