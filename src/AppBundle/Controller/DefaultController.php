<?php

namespace AppBundle\Controller;

use AppBundle\Criteria\EntryCriteria;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        );
    }

    /**
     * @Route("/url/metainfo",name="url_metainfo")
     * @Template()
     * @Method("GET")
     */
    public function urlMetainfoAction(Request $request)
    {
        $metainfoFactory = $this->get('app.factory.metainfo');
        $url = $request->get('url');
        if (empty($url)) {
            throw $this->createNotFoundException('no url given');
        }

        $info = $metainfoFactory->create($url);
        $urlDuplicate = ($this->getEntryRepository()->urlAlreadyTaken(
            $this->getUser(),
            $url,
            $request->get('edit_id')
        ) !== null);

        $metaInfo = [
            'title' => $info->getTitle(),
            'image_url' => $info->getImageUrl(),
            'category' => $info->getDefaultCategory(),
            'url_duplicate' => $urlDuplicate,
        ];

        return new JsonResponse($metaInfo);
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
