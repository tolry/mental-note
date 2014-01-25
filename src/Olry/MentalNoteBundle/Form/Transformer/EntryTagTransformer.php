<?php
/*
 * @author Tobias Olry <tobias.olry@gmail.com>
 */

namespace Olry\MentalNoteBundle\Form\Transformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\ORM\EntityRepository;

use Doctrine\Common\Collections\ArrayCollection;

use Olry\MentalNoteBundle\Entity\Tag;

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
            return "";
        }

        $tagNames = array();
        foreach ($tags as $tag) {
            $tagNames[] = $tag->getName();
        }

        return implode(', ', $tagNames);
    }

    public function reverseTransform($tagsString)
    {
        $tagNames = explode(',', $tagsString);
        $tags     = new ArrayCollection();

        if (!is_array($tagNames) || empty($tagNames)) {
            return $tags;
        }

        $tagNames = array_map('trim', $tagNames);

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


