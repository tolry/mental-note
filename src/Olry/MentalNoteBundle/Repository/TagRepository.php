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

    public function search($query, User $user)
    {

        $qb = $this->createQueryBuilder('t');

        if (strlen($query) > 0) {
            $qb
                ->andWhere("t.name LIKE :query")
                ->andWhere("t IN (SELECT DISTINCT t2 FROM \Olry\MentalNoteBundle\Entity\Tag t2 INNER JOIN t2.entries e WHERE e.user = :user)")
                ->setParameter('query', "%$query%")
                ->setParameter('user', $user)
            ;
        }

        $qb->add('orderBy', 't.name ASC');

        return $qb;
    }
}
