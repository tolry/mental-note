<?php

namespace Olry\MentalNoteBundle\Entity;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 * @ORM\Entity(repositoryClass="Olry\MentalNoteBundle\Repository\TagRepository")
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
     **/
    private $entries;

    /**
     * Get name.
     *
     * @return name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param name the value to set.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }
}
