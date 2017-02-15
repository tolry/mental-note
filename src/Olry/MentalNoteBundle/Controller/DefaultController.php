<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Olry\MentalNoteBundle\Criteria\EntryCriteria;

class DefaultController extends AbstractBaseController
{

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function getFilterCriteria(Request $request)
    {
        $filter = (array) $request->get('filter', array());

        return new EntryCriteria($filter);
    }

    /**
     * @Route("/quick-add",name="quick_add")
     * @Template()
     * @Method("GET")
     */
    public function quickAddAction(Request $request)
    {
        if ($request->get('url')) {
            $this->addFlash('add_url', $request->get('url'));

            return $this->redirectToRoute('homepage');
        }

        throw $this->createNotFoundException('missing url parameter');
    }

    /**
     * @Route("/",name="homepage")
     * @Template()
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $criteria = $this->getFilterCriteria($request);

        try {
            $pager = $this->getEntryRepository()->filter($this->getUser(), $criteria);
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $e) {
            $filter = (array) $request->get('filter', array());
            $filter['page']--;

            return $this->redirectToRoute('homepage', ['filter' => $filter]);
        }

        return array(
            'pager'       => $pager,
            'criteria'    => $criteria,
            'active_menu' => 'entries',
            'add_url'     => $this->get('session')->getFlashBag()->get('add_url'),
        );
    }

    /**
     * @Route("/url/metainfo",name="url_metainfo")
     * @Template()
     * @Method("GET")
     */
    public function urlMetainfoAction(Request $request)
    {
        $url = $request->get('url');
        if (empty($url)) {
            throw $this->createNotFoundException('no url given');
        }

        $info = new \Olry\MentalNoteBundle\Url\MetaInfo($url);

        $metaInfo = array(
            'title' => $info->getTitle(),
            'image_url' => $info->getImageUrl(),
            'category' => $info->getDefaultCategory(),
        );

        return new Response(\json_encode($metaInfo));
    }

    /**
     * @Route("/sidebar",name="sidebar")
     * @Method("GET")
     * @Template()
     */
    public function sidebarAction(Request $request)
    {
        $criteria = $this->getFilterCriteria($request);

        return array(
            'tags'       => $this->getEntryRepository()->getTagStats($this->getUser(), $criteria),
            'categories' => $this->getEntryRepository()->getCategoryStats($this->getUser(), $criteria),
            'criteria'   => $criteria,
        );
    }
}
