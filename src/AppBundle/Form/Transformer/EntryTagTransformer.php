<?php

declare(strict_types=1);

namespace AppBundle\Form\Transformer;

use AppBundle\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;

class EntryTagTransformer implements DataTransformerInterface
{
    private $tagRepository;

    /**
     * @param EntityManager
     */
    public function __construct(EntityRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function transform($tags)
    {
        if (empty($tags)) {
            return '';
        }

        $tagNames = [];
        foreach ($tags as $tag) {
            $tagNames[] = $tag->getName();
        }

        return implode(', ', $tagNames);
    }

    public function reverseTransform($tagsString)
    {
        $tagNames = explode(',', $tagsString);
        $tags = new ArrayCollection();

        if (!is_array($tagNames) || empty($tagNames)) {
            return $tags;
        }

        $tagNames = array_map('trim', $tagNames);
        $tagNames = array_filter(
            $tagNames,
            function ($tag) {
                return !empty($tag);
            }
        );

        foreach ($tagNames as $tagName) {
            $tag = $this->tagRepository->findOneByName($tagName);
            if (!$tag) {
                $tag = new Tag();
                $tag->setName($tagName);
            }

            $tags->add($tag);
        }

        return $tags;
    }
}
