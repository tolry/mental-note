<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 *
 * @ORM\MappedSuperclass
 * @ORM\HasLifecycleCallbacks
 */
abstract class AbstractEntity
{
    /**
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated = new DateTime();
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function getUpdated(): DateTime
    {
        return $this->updated;
    }

    public function setUpdated(DateTime $updated): void
    {
        $this->updated = $updated;
    }
}
