<?php

declare(strict_types=1);

namespace App\Controller;

use App\Criteria\EntryCriteria;
use App\Factory\MetainfoFactory;
use App\Repository\EntryRepository;
use App\Url\MetaInfo;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DefaultController extends AbstractController
{
    public function __construct(
        private readonly MetainfoFactory $metaInfoFactory,
        private readonly EntryRepository $entryRepository,
    ) {
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
            $pager = $this->entryRepository->filter($this->getUser(), $criteria);
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $e) {
            $filter = (array) $request->get('filter', []);
            --$filter['page'];

            return $this->redirectToRoute('homepage', ['filter' => $filter]);
        }

        return [
            'pager' => $pager,
            'criteria' => $criteria,
        ];
    }

    /**
     * @Route("/url/metainfo",name="url_metainfo")
     * @Template()
     * @Method("GET")
     */
    public function urlMetainfoAction(Request $request)
    {
        if (class_exists('Tideways\Profiler')) {
            \Tideways\Profiler::setServiceName('3rd-party');
        }

        $url = $request->get('url');
        if (empty($url)) {
            throw $this->createNotFoundException('no url given');
        }

        $info = $this->metaInfoFactory->create($url);
        $urlDuplicate = ($this->getEntryRepository()->urlAlreadyTaken(
            $this->getUser(),
            $url,
            $request->query->getInt('edit_id')
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
     * @Route("/sidebar",name="default_sidebar")
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    private function getFilterCriteria(Request $request)
    {
        $filter = (array) $request->get('filter', []);

        return new EntryCriteria($filter);
    }
}
