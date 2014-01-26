<?php

namespace Olry\MentalNoteBundle\Entity;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Doctrine\ORM\Mapping as ORM;

use Olry\MentalNoteBundle\Entity\User;
use Olry\MentalNoteBundle\Entity\Visit;
use Olry\MentalNoteBundle\Url\MetaInfo;
use Olry\MentalNoteBundle\Url\Info;

/**
 * @author Tobias Olry (tobias.olry@web.de)
 * @ORM\Entity(repositoryClass="Olry\MentalNoteBundle\Repository\EntryRepository")
 * @ORM\Table(name="entry")
 * @UniqueEntity(fields={"url", "user"}, message="url already in database")
 */
class Entry extends AbstractEntity
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500, unique=true)
     * @Assert\Url
     */
    private $url;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private $title;

    /**
     * the category, any of the self::CATEGORY_* constants
     *
     * @ORM\Column(type="string")
     */
    private $category = Category::READ;

    /**
     * @ORM\Column(type="boolean")
     */
    private $pending = true;

    /**
     * @ORM\ManyToMany(targetEntity="Tag", cascade={"persist"}, inversedBy="entries")
     * @ORM\JoinTable(name="entry_has_tag")
     **/
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity="Visit", mappedBy="entry", cascade={"all"})
     */
    private $visits;

    /**
     * @var string
     */
    private $tagsString = null;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @var Olry\MentalNoteBundle\Entity\User
     */
    private $user = null;

    public function __construct()
    {
        $this->tags = array();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl( $url )
    {
        $this->url = $url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle( $title )
    {
        $this->title = $title;
    }

    public function getCategory()
    {
        if (!empty($this->category)) {
            return new Category($this->category);
        }
    }

    public function setCategory( $category )
    {
        $this->category = $category;
    }

    public function getPending()
    {
        return $this->pending;
    }

    public function setPending( $pending )
    {
        $this->pending = $pending;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags( $tags )
    {
        $this->tags = $tags;
    }

    public function getId()
    {
        return $this->id;
    }


    /**
     * @return Olry\MentalNoteBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param Olry\MentalNoteBundle\Entity\User $user 
     */
    public function setUser(\Olry\MentalNoteBundle\Entity\User $user)
    {
        $this->user = $user;
    }

    public function getUrlMetaInfo()
    {
        return new MetaInfo($this->url);
    }

    public function getUrlInfo()
    {
        return new Info($this->url);
    }

    public function addVisit(\DateTime $timestamp = null)
    {
        if (! $timestamp) {
            $timestamp = new \DateTime();
        }
        $this->visits[] = new Visit($timestamp, $this);
    }

    public function getAge()
    {
        $seconds = time() - $this->getCreated()->format('U');
        $ranges = array(
            'today'            => 3600 * 24,
            'this week'        => 3600 * 24 * 7,
            '> a week ago'     => 3600 * 24 * 14,
            '> two weeks ago'  => 3600 * 24 * 30,
            '> a month ago'    => 3600 * 24 * 60,
            '> two months ago' => 3600 * 24 * 365,
            '> a year ago'     => 3600 * 24 * 730,
        );

        foreach ($ranges as $range => $maxSeconds) {
            if ($seconds < $maxSeconds) {
                return $range;
            }
        }

        return '> two years ago';
    }

    public function getVisits()
    {
        return $this->visits;
    }

    public function getDomain()
    {
        return $this->getUrlInfo()->getDomain();
    }

}

