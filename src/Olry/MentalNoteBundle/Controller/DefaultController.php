<?php

namespace Olry\MentalNoteBundle\Controller;

use Olry\MentalNoteBundle\Criteria\EntryCriteria;
use Olry\MentalNoteBundle\Url\MetaInfo;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractBaseController
{
    private function getFilterCriteria(Request $request)
    {
        $filter = (array) $request->get('filter', []);

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
     * @Route("/visit-regularly",name="visit_regularly")
     * @Template()
     * @Method("GET")
     */
    public function visitRegularlyAction(Request $request)
    {
        try {
            $criteria = EntryCriteria::createForVisitRegularly();
            $criteria->page = $request->query->getInt('page', 1);

            $pager = $this->getEntryRepository()->filter($this->getUser(), $criteria);
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $e) {
            return $this->redirectToRoute('homepage', ['page' => $criteria->page--]);
        }

        return [
            'pager' => $pager,
        ];
    }

    /**
     * @Route("/",name="homepage")
     * @Template()
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        try {
            $criteria = $this->getFilterCriteria($request);
            $pager = $this->getEntryRepository()->filter($this->getUser(), $criteria);
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $e) {
            $filter = (array) $request->get('filter', []);
            --$filter['page'];

            return $this->redirectToRoute('homepage', ['filter' => $filter]);
        }

        return [
            'pager' => $pager,
            'criteria' => $criteria,
            'active_menu' => 'entries',
            'add_url' => $this->get('session')->getFlashBag()->get('add_url'),
        ];
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

        $info = new MetaInfo($url);

        $metaInfo = [
            'title' => $info->getTitle(),
            'image_url' => $info->getImageUrl(),
            'category' => $info->getDefaultCategory(),
            'url_duplicate' => $this->getEntryRepository()->urlAlreadyTaken(
                $this->getUser(),
                $url,
                $request->get('edit_id')
            ),
        ];

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

        return [
            'tags' => $this->getEntryRepository()->getTagStats($this->getUser(), $criteria),
            'categories' => $this->getEntryRepository()->getCategoryStats($this->getUser(), $criteria),
            'criteria' => $criteria,
        ];
    }
}
