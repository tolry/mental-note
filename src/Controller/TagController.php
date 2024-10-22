<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\TagRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractController
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }
    /**
     * @Route("/tags",name="tag_search")
     * @Template()
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $tags = $this
            ->tagRepository
            ->search($request->get('query'), $this->getUser())
            ->select('t.name')
            ->getQuery()
            ->getScalarResult()
        ;

        $tags = array_map('current', $tags);

        $response = new Response(json_encode($tags));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
