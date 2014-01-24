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
    public function getUser()
    {
        return parent::getUser();
    }

    /**
     * @return Olry\MentalNote\Repository\EntryRepository
     */
    public function getEntryRepository()
    {
        return $this->getEm()->getRepository('\Olry\MentalNoteBundle\Entity\Entry');
    }

    /**
     * @return Olry\MentalNote\Repository\TagRepository
     */
    public function getTagRepository()
    {
        return $this->getEm()->getRepository('\Olry\MentalNoteBundle\Entity\Tag');
    }

    public function getEm()
    {
        return $this->getDoctrine()->getManager();
    }

}
