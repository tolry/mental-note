<?php

namespace Olry\MentalNoteBundle\Entity;

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
     * @var Entry
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
