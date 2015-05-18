<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

class TagController extends AbstractBaseController
{

    /**
     * @Route("/tags",name="tag_search")
     * @Template()
     */
    public function indexAction()
    {
        $request    = $this->getRequest();

        $tags = $this
            ->getTagRepository()
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

