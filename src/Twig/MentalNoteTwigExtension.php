<?php

declare(strict_types=1);
// @author Tobias Olry <tobias.olry@gmail.com>

namespace App\Twig;

use App\Entity\Category;

class MentalNoteTwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('mn_category', [$this, 'getCategoryInstance']),
        ];
    }

    public function getCategoryInstance(string $category): Category
    {
        return new Category($category);
    }

    public function getName(): string
    {
        return 'mental_note';
    }
}
