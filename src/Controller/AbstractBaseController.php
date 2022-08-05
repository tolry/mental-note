<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\EntryRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of AbstractBaseController.
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
        return $this->getEm()->getRepository('\App\Entity\Entry');
    }

    /**
     * @return TagRepository
     */
    protected function getTagRepository()
    {
        return $this->getEm()->getRepository('\App\Entity\Tag');
    }

    protected function getEm()
    {
        return $this->getDoctrine()->getManager();
    }
}
