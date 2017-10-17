<?php

namespace Olry\MentalNoteBundle\Controller;

use Olry\MentalNoteBundle\Entity\User;
use Olry\MentalNoteBundle\Repository\EntryRepository;
use Olry\MentalNoteBundle\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of AbstractBaseController
 *
 * @author Tobias Olry <tobias.olry@gmail.com>
 */
class AbstractBaseController extends Controller
{

    /**
     * @return User
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return EntryRepository
     */
    protected function getEntryRepository()
    {
        return $this->getEm()->getRepository('\Olry\MentalNoteBundle\Entity\Entry');
    }

    /**
     * @return TagRepository
     */
    protected function getTagRepository()
    {
        return $this->getEm()->getRepository('\Olry\MentalNoteBundle\Entity\Tag');
    }

    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
