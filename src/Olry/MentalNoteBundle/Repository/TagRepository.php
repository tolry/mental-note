<?php

namespace Olry\MentalNoteBundle\Repository;

use Doctrine\ORM\EntityRepository;

use Olry\MentalNoteBundle\Entity\User;
use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Entity\Tag;
use Olry\MentalNoteBundle\Entity\Category;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 */
class TagRepository extends EntityRepository
{

    public function getStats(User $user, $limit = 20)
    {
        $sql = "SELECT t.name, t.id, SUM(e.pending) AS pending, COUNT(e.id) AS total
            FROM \Olry\MentalNoteBundle\Entity\Entry e 
            INNER JOIN e.tags t
            WHERE e.user = :user 
            GROUP BY t.id
            ORDER BY total DESC";
        $query = $this->getEntityManager()->createQuery($sql);
        $query->setMaxResults($limit);
        $query->setParameter('user',$user->getId());

        return $query->getResult();
    }

    public function search($query)
    {

        $qb = $this->createQueryBuilder('t');

        if (strlen($query) > 0) {
            $qb->add("where", "t.name LIKE :query");
            $qb->setParameter('query', "%$query%");
        }

        $qb->add('orderBy', 't.name ASC');

        return $qb;
    }
}

