<?php

namespace Olry\MentalNoteBundle\Controller;

use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Form\Type\EntryType;
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
     * @param Form $form
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
    public function thumbnailAction(Entry $entry, $width, $height)
    {
        $documentRoot = $this->container->getParameter('kernel.root_dir').'/../web';
        $route        = $this->generateUrl('entry_thumbnail', array('id' => $entry->getId(), 'width' => $width, 'height' => $height));

        $pathNew = sprintf('%s/thumbnails/%d_%dx%d.png', $documentRoot, $entry->getId(), $width, $height);

        // for dev mode
        if (file_exists($pathNew)) {
            $response = new BinaryFileResponse($pathNew);
            $this->get('logger')->error($entry->getId().':: file already exists, controller should not be executed');

            return $response;
        }

        try {
            $thumbnailService = $this->get('olry_mental_note.thumbnail_service');
            $thumbnailService->generate($entry->getUrl(), $width, $height, $entry->getId());

            return $this->redirect($route);
        } catch (\Exception $e) {
            $this->get('logger')->error('Exception: '.$e->getMessage());

            $target = $documentRoot."/images/placeholder_no-preview.png";
            symlink($target, $pathNew);

            return $this->redirect("/images/placeholder_no-preview.png", 301);
        }
    }

    /**
     * @Route("/entry/create.html",name="entry_create")
     * @Route("/quick-add")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function createAction(Request $request)
    {
        $cache = $this->get('olry_mental_note.cache.metainfo');
        $backlink = $request->query->get('backlink');
        $entry = new Entry($this->getUser());

        if ($request->isMethod(Request::METHOD_GET)) {
            $url = $request->query->get('url');
            $entry->setUrl($url);
            $entry->setTitle($request->query->get('title'));
            $cache->set($url, 'preview', $request->query->get('preview'));
        }

        $form = $this->createForm(EntryType::class, $entry);
        if ($this->processForm($form, $entry, $request)) {
            if (empty($backlink)) {
                return $this->redirect($this->generateUrl('homepage'));
            }

            return $this->redirect($backlink);
        }

        return array(
            'form' => $form->createView(),
            'backlink' => $backlink,
        );
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

        return array(
            'form'  => $form->createView(),
            'entry' => $entry,
            'backlink' => $backlink,
        );
    }

    /**
     * @Route("/entry/{id}/delete.html",name="entry_delete")
     * @Template()
     * @Method({"GET", "POST"})
     */
    public function deleteAction(Request $request, Entry $entry)
    {
        $filter  = (array) $request->get('filter', array());
        $form    = $this->createFormBuilder($entry)->getForm();

        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->getEm()->remove($entry);
            $this->getEm()->flush();

            return $this->redirect($this->generateUrl('homepage', array('filter' => $filter)));
        }

        return [
            'form'   => $form->createView(),
            'entry'  => $entry,
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
}
