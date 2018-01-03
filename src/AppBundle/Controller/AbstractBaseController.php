<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Repository\EntryRepository;
use AppBundle\Repository\TagRepository;
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
        return $this->getEm()->getRepository('\AppBundle\Entity\Entry');
    }

    /**
     * @return TagRepository
     */
    protected function getTagRepository()
    {
        return $this->getEm()->getRepository('\AppBundle\Entity\Tag');
    }

    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
