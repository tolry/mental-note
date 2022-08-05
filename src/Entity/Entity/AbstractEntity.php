<?php

declare(strict_types=1);

namespace App\Entity;

use DateTimeImmutable;
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
     * @ORM\Column(type="datetime_immutable")
     */
    protected $created;

    /**
     * @ORM\Column(type="datetime_immutable")
     */
    protected $updated;

    /**
     * @ORM\PrePersist
     */
    public function prePersist(): void
    {
        $this->created = new DateTimeImmutable();
        $this->updated = new DateTimeImmutable();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate(): void
    {
        $this->updated = new DateTimeImmutable();
    }

    public function getCreated(): DateTimeImmutable
    {
        return $this->created;
    }

    public function getUpdated(): DateTimeImmutable
    {
        return $this->updated;
    }
}
