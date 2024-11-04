<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Entry;
use App\Form\Type\EntryType;
use App\Repository\EntryRepository;
use App\Thumbnail\ThumbnailService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class EntryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly EntryRepository $entryRepository,
        private readonly ThumbnailService $thumbnailService,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.environment%')] private readonly string $environment,
        #[Autowire('%kernel.project_dir%/../web')] private readonly string $documentRoot,
    ) {
    }

    #[Route(path: '/entry/{id}/toggle_pending.json', name: 'entry_toggle_pending', methods: ['POST'])]
    public function togglePendingAction(Entry $entry, Request $request)
    {
        $entry->setPending(!$entry->getPending());
        $this->em->flush();

        $filter = (array) $request->get('filter', []);

        return $this->redirect($this->generateUrl('homepage', ['filter' => $filter]));
    }

    #[Route(path: '/thumbnails/{id}_{width}x{height}.png', name: 'entry_thumbnail', methods: ['GET'])]
    public function thumbnailAction(Entry $entry, int $width, int $height, Request $request)
    {
        if (class_exists('Tideways\Profiler')) {
            \Tideways\Profiler::setServiceName('3rd-party');
        }

        $testEnvironment = $this->environment === 'test';

        $route = $request->getRequestUri();

        $pathNew = sprintf('%s/thumbnails/%d_%dx%d.png', $this->documentRoot, $entry->getId(), $width, $height);

        if (file_exists($pathNew) && $testEnvironment) {
            unlink($pathNew);
        }

        // for dev mode
        if (file_exists($pathNew) && !$testEnvironment) {
            $response = new BinaryFileResponse($pathNew);
            $this->logger->error($entry->getId() . ':: file already exists, controller should not be executed');

            return $response;
        }

        try {
            $this->thumbnailService->generate(
                $entry->getUrl(),
                $width,
                $height,
                (string) $entry->getId()
            );
        } catch (\Exception $e) {
            $this->logger->error('Exception: ' . $e->getMessage());

            $target = $this->documentRoot . '/images/placeholder_no-preview.png';
            symlink($target, $pathNew);
        }

        return $this->redirect($route);
    }

    #[Route(path: '/entry/create.html', name: 'entry_create', methods: ['GET', 'POST'])]
    #[Route(path: '/quick-add', name: 'entry_create_quick', methods: ['GET', 'POST'])]
    #[Template()]
    public function createAction(Request $request)
    {
        $backlink = $request->query->get('backlink');
        $entry = new Entry($this->getUser());

        if ($request->isMethod(Request::METHOD_GET)) {
            $url = $request->query->get('url');
            $entry->setUrl($url);
            $entry->setTitle($request->query->get('title'));
        }

        if ($entry->getUrl()) {
            $urlDuplicate = $this->entryRepository->urlAlreadyTaken(
                $this->getUser(),
                $entry->getUrl(),
                null
            );

            if ($urlDuplicate) {
                $this->addFlash(
                    'info',
                    sprintf(
                        'url was already added %s, redirected to edit form',
                        $urlDuplicate->getAge()
                    )
                );

                return $this->redirectToRoute('entry_edit', ['id' => $urlDuplicate->getId()]);
            }
        }

        $form = $this->createForm(EntryType::class, $entry);
        if ($this->processForm($form, $entry, $request)) {
            if (empty($backlink)) {
                return $this->redirect($this->generateUrl('homepage'));
            }

            return $this->redirect($backlink);
        }

        return [
            'form' => $form->createView(),
            'backlink' => $backlink,
        ];
    }

    #[Route(path: '/entry/{id}/edit.html', name: 'entry_edit', methods: ['GET', 'POST'])]
    #[Template()]
    public function editAction(Entry $entry, Request $request)
    {
        $backlink = $request->query->get('backlink');
        $form = $this->createForm(EntryType::class, $entry, ['url-readonly' => true]);

        if ($this->processForm($form, $entry, $request)) {
            if (empty($backlink)) {
                return $this->redirect($this->generateUrl('homepage'));
            }

            return $this->redirect($backlink);
        }

        return [
            'form' => $form->createView(),
            'entry' => $entry,
            'backlink' => $backlink,
        ];
    }

    #[Route(path: '/entry/{id}/delete.html', name: 'entry_delete', methods: ['GET', 'POST'])]
    #[Template()]
    public function deleteAction(Request $request, Entry $entry)
    {
        $filter = (array) $request->get('filter', []);
        $form = $this->createFormBuilder($entry)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getEm()->remove($entry);
            $this->getEm()->flush();

            return $this->redirect($this->generateUrl('homepage', ['filter' => $filter]));
        }

        return [
            'form' => $form->createView(),
            'entry' => $entry,
            'filter' => $filter,
        ];
    }

    #[Route(path: '/entry/{id}/visit', name: 'entry_visit', methods: ['POST'])]
    public function visitAction(Entry $entry)
    {
        $entry->addVisit();
        $this->getEm()->flush();

        return new Response('', 200);
    }

    private function processForm(Form $form, Entry $entry, Request $request): bool
    {
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entryRepository->save($entry);

            return true;
        }

        return false;
    }
}
