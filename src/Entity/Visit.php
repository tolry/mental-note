<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 */
#[ORM\Entity()]
#[ORM\Table(name: "visit")]
class Visit
{
    #[ORM\Id] #[ORM\Column(type: "integer")] #[ORM\GeneratedValue] private $id;

    /**
     *
     * @var Entry
     */
    #[ORM\ManyToOne(targetEntity: "Entry", inversedBy: "visits")] private $entry;

    #[ORM\Column(type: "datetime_immutable")] private $timestamp;

    public function __construct(DateTimeImmutable $timestamp, Entry $entry)
    {
        $this->timestamp = $timestamp;
        $this->entry = $entry;
    }
}
