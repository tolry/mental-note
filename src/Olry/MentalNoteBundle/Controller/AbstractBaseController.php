<?php

namespace Olry\MentalNoteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of AbstractBaseController
 *
 * @author korgano
 */
class AbstractBaseController extends Controller
{

    /**
     * @return Olry\MentalNoteBundle\Entity\User
     */
    protected function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return Olry\MentalNote\Repository\EntryRepository
     */
    protected function getEntryRepository()
    {
        return $this->getEm()->getRepository('\Olry\MentalNoteBundle\Entity\Entry');
    }

    /**
     * @return Olry\MentalNote\Repository\TagRepository
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
