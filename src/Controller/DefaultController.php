<?php

declare(strict_types=1);

namespace App\Controller;

use App\Criteria\EntryCriteria;
use App\Factory\MetainfoFactory;
use App\Repository\EntryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route(path: '/', name: 'homepage', methods: ['GET'])]
    #[Template()]
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

    #[Route(path: '/url/metainfo', name: 'url_metainfo', methods: ['GET'])]
    #[Template()]
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
        $excludeId = (int) $request->query->get('edit_id');
        $urlDuplicate = ($this->entryRepository->urlAlreadyTaken($this->getUser(), $url, $excludeId > 0 ? $excludeId : null) !== null);

        $metaInfo = [
            'title' => $info->getTitle(),
            'image_url' => $info->getImageUrl(),
            'category' => $info->getDefaultCategory(),
            'url_duplicate' => $urlDuplicate,
        ];

        return new JsonResponse($metaInfo);
    }

    #[Route(path: '/sidebar', name: 'default_sidebar', methods: ['GET'])]
    #[Template()]
    public function sidebarAction(Request $request)
    {
        $criteria = $this->getFilterCriteria($request);

        return [
            'tags' => $this->entryRepository->getTagStats($this->getUser(), $criteria),
            'categories' => $this->entryRepository->getCategoryStats($this->getUser(), $criteria),
            'criteria' => $criteria,
        ];
    }

    private function getFilterCriteria(Request $request)
    {
        return new EntryCriteria(
            tag: $request->query->get('filter[tag]'),
            query: $request->query->get('filter[query]'),
            category: $request->query->get('filter[category]'),
            limit: $request->query->getInt('filter[limit]', 20),
            page: $request->query->getInt('filter[page]', 1),
            pendingOnly: $request->query->getBoolean('filter[pendingOnly]'),
            sortOrder: $request->query->get('filter[sortOrder]', EntryCriteria::SORT_TIMESTAMP_DESC),
        );
    }
}
