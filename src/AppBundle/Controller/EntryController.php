<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\Entry;
use AppBundle\Form\Type\EntryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EntryController extends AbstractBaseController
{
    /**
     * @Route("/entry/{id}/toggle_pending.json",name="entry_toggle_pending")
     * @Method("POST")
     */
    public function togglePendingAction(Entry $entry, Request $request)
    {
        $entry->setPending(!$entry->getPending());
        $this->getEm()->flush();

        $filter = (array) $request->get('filter', []);

        return $this->redirect($this->generateUrl('homepage', ['filter' => $filter]));
    }

    /**
     * @Route("/thumbnails/{id}_{width}x{height}.png",name="entry_thumbnail")
     * @Method("GET")
     */
    public function thumbnailAction(Entry $entry, int $width, int $height)
    {
        \Tideways\Profiler::setServiceName('3rd-party');
        $documentRoot = $this->container->getParameter('kernel.root_dir') . '/../web';
        $route = $this->generateUrl('entry_thumbnail', ['id' => $entry->getId(), 'width' => $width, 'height' => $height]);

        $pathNew = sprintf('%s/thumbnails/%d_%dx%d.png', $documentRoot, $entry->getId(), $width, $height);

        // for dev mode
        if (file_exists($pathNew)) {
            $response = new BinaryFileResponse($pathNew);
            $this->get('logger')->error($entry->getId() . ':: file already exists, controller should not be executed');

            return $response;
        }

        try {
            $thumbnailService = $this->get('app.thumbnail_service');
            $thumbnailService->generate(
                $entry->getUrl(),
                $width,
                $height,
                (string) $entry->getId()
            );
        } catch (\Exception $e) {
            $this->get('logger')->error('Exception: ' . $e->getMessage());

            $target = $documentRoot . '/images/placeholder_no-preview.png';
            symlink($target, $pathNew);
        }

        return $this->redirect($route);
    }

    /**
     * @Route("/entry/create.html",name="entry_create")
     * @Route("/quick-add")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function createAction(Request $request)
    {
        $cache = $this->get('app.cache.metainfo');
        $backlink = $request->query->get('backlink');
        $entry = new Entry($this->getUser());

        if ($request->isMethod(Request::METHOD_GET)) {
            $url = $request->query->get('url');
            $entry->setUrl($url);
            $entry->setTitle($request->query->get('title'));
        }

        if ($entry->getUrl()) {
            $urlDuplicate = $this->getEntryRepository()->urlAlreadyTaken(
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

    /**
     * @Route("/entry/{id}/edit.html",name="entry_edit")
     * @Template()
     * @Method({"GET", "POST"})
     */
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

    /**
     * @Route("/entry/{id}/delete.html",name="entry_delete")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function deleteAction(Request $request, Entry $entry)
    {
        $filter = (array) $request->get('filter', []);
        $form = $this->createFormBuilder($entry)->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
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

    /**
     * @Route("/entry/{id}/visit",name="entry_visit")
     * @Method("POST")
     */
    public function visitAction(Entry $entry)
    {
        $entry->addVisit();
        $this->getEm()->flush();

        return new Response('', 200);
    }

    /**
     * @param Form    $form
     * @param Request $request
     */
    private function processForm(Form $form, Entry $entry, Request $request)
    {
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getEm()->persist($entry);
            $this->getEm()->flush();

            return true;
        }

        return false;
    }
}
