<?php
/*
 * @author Tobias Olry <tobias.olry@gmail.com>
 */

namespace AppBundle\Twig;

use AppBundle\Entity\Category;

class MentalNoteTwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('mn_category', array($this, 'getCategoryInstance')),
        );
    }

    public function getCategoryInstance($category)
    {
        return new Category($category);
    }

    public function getName()
    {
        return 'mental_note';
    }
}
