<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DomCrawler\Crawler;

use Olry\MentalNoteBundle\Criteria\EntryCriteria;

class DefaultController extends AbstractBaseController
{

    private function getFilterCriteria($request)
    {
        $filter = (array) $this->getRequest()->get('filter', array());
        return new EntryCriteria($filter);
    }

    /**
     * @Route("/",name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        $criteria = $this->getFilterCriteria($this->getRequest());

        $pager = $this->getEntryRepository()->filter($this->getUser(), $criteria);

        return array(
            'pager'       => $pager,
            'criteria'    => $criteria,
            'active_menu' => 'entries',
        );
    }

    /**
     * @Route("/visit_regularly", name="visit_regularly")
     * @Template()
     */
    public function visitRegularlyAction()
    {
        $criteria = $this->getFilterCriteria($this->getRequest());

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
    public function urlMetainfoAction()
    {
        $url = $this->getRequest()->get('url');
        if(empty($url)){
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
        try{
            $crawler = new Crawler(file_get_contents($url));
            $title = $crawler->filterXPath('//head/title')->first()->text();
            return trim($title);
        } catch(\Exception $e) {
            return null;
        }
    }

    /**
     * @Route("/sidebar",name="sidebar")
     * @Template()
     */
    public function sidebarAction()
    {
        $criteria = $this->getFilterCriteria($this->getRequest());

        return array(
            'tags'       => $this->getEntryRepository()->getTagStats($this->getUser(), $criteria),
            'categories' => $this->getEntryRepository()->getCategoryStats($this->getUser(), $criteria),
            'criteria'   => $criteria,
        );
    }

}
