<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DomCrawler\Crawler;
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
     * @Route("/visit_regularly", name="visit_regularly")
     * @Template()
     */
    public function visitRegularlyAction(Request $request)
    {
        $criteria = $this->getFilterCriteria($request);

        $pager = $this->getEntryRepository()->filter($this->getUser(), $criteria);

        return array(
            'pager' => $pager,
            'criteria' => $criteria,
            'active_menu' => 'visit_regularly',
        );
    }

    /**
     * @Route("/url/metainfo",name="url_metainfo")
     * @Template()
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

    protected function getTitle($url)
    {
        if (preg_match('%https?://i\.imgur.com/([0-9a-z]+)\.(jpe?g|gif|png)%i', $url, $matches)) {
            $url = "http://imgur.com/" . $matches[1];
        }

        try {
            $crawler = new Crawler(file_get_contents($url));
            $title = $crawler->filterXPath('//head/title')->first()->text();

            return trim($title);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @Route("/sidebar",name="sidebar")
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
