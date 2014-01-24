<?php

namespace Olry\MentalNoteBundle\Entity;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 * @ORM\Entity
 * @ORM\Table(name="visit")
 */
class Visit
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Entry", inversedBy="visits")
     * @var Olry\MentalNoteBundle\Entity\Entry
     */
    private $entry;

    /**
     * @ORM\Column(type="datetime")
     */
    private $timestamp;


    public function __construct(\DateTime $timestamp, Entry $entry)
    {
        $this->timestamp = $timestamp;
        $this->entry = $entry;
    }
}
