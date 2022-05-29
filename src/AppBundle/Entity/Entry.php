<?php

declare(strict_types=1);
// @author Tobias Olry <tobias.olry@gmail.com>

namespace AppBundle\Entity;

use AppBundle\Url\Info;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryRepository")
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
     * @ORM\Column(type="string", length=2048)
     * @Assert\Url
     */
    private $url;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $title;

    /**
     * the category, any of the self::CATEGORY_* constants.
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
     */
    private $tags;

    /**
     * @ORM\OneToMany(targetEntity="Visit", mappedBy="entry", cascade={"all"})
     */
    private $visits;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User
     */
    private $user;

    public function __construct(User $user)
    {
        $this->tags = [];
        $this->user = $user;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): void
    {
        $this->url = $url;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title): void
    {
        $this->title = $title;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category): void
    {
        $this->category = $category;
    }

    public function getPending()
    {
        return $this->pending;
    }

    public function setPending($pending): void
    {
        $this->pending = $pending;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags): void
    {
        $this->tags = $tags;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    public function getUrlInfo()
    {
        return new Info($this->url);
    }

    public function addVisit(DateTimeImmutable $timestamp = null): void
    {
        if (!$timestamp) {
            $timestamp = new DateTimeImmutable();
        }
        $this->visits[] = new Visit($timestamp, $this);
    }

    public function getAge()
    {
        $seconds = time() - $this->getCreated()->format('U');
        $ranges = [
            'today' => 3600 * 24,
            'this week' => 3600 * 24 * 7,
            '> a week ago' => 3600 * 24 * 14,
            '> two weeks ago' => 3600 * 24 * 30,
            '> a month ago' => 3600 * 24 * 60,
            '> two months ago' => 3600 * 24 * 365,
            '> a year ago' => 3600 * 24 * 730,
        ];

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
