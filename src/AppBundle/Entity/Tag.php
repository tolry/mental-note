<?php

declare(strict_types=1);

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 * @ORM\Table(name="tag")
 */
class Tag extends AbstractEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, options={"collation"="utf8_bin"})
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Entry", mappedBy="tags")
     */
    private $entries;

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Get name.
     *
     * @return string name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param name the value to set
     * @param mixed $name
     */
    public function setName($name): void
    {
        $this->name = $name;
    }

    /**
     * @return int id
     */
    public function getId()
    {
        return $this->id;
    }
}
