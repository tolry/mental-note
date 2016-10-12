<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Form\Type\EntryType;

class EntryController extends AbstractBaseController
{

    /**
     * @param \Symfony\Component\Form\Form $form
     * @param \Symfony\Component\HttpFoundation\Request $request
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
     */
    public function thumbnailAction(Entry $entry, $width, $height)
    {
        $documentRoot = $this->container->getParameter('kernel.root_dir') . '/../web';
        $route        = $this->generateUrl('entry_thumbnail', array('id' => $entry->getId(), 'width' => $width, 'height' => $height));

        $pathNew = sprintf('%s/thumbnails/%d_%dx%d.png', $documentRoot, $entry->getId(), $width, $height);

        // for dev mode
        if (file_exists($pathNew)) {
            $response = new BinaryFileResponse($pathNew);
            $this->get('logger')->error($entry->getId() . ':: file already exists, controller should not be executed');

            return $response;
        }

        try {
            $thumbnailService = $this->get('olry_mental_note.thumbnail_service');
            $thumbnailService->generate($entry->getUrl(), $width, $height, $entry->getId());

            return $this->redirect($route);
        } catch (\Exception $e) {
            $this->get('logger')->error('Exception: ' . $e->getMessage());

            return $this->redirect("https://placehold.it/${width}x${height}?text=no%20preview", 301);
        }
    }

    /**
     * @Route("/entry/create.html",name="entry_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $entry = new Entry($this->getUser());
        $form  = $this->createForm(EntryType::class, $entry);

        if ($this->processForm($form, $entry, $request)) {
            return new Response('created', 201);
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/entry/{id}/edit.html",name="entry_edit")
     * @Template()
     */
    public function editAction(Entry $entry, Request $request)
    {
        $form = $this->createForm(EntryType::class, $entry);

        if ($this->processForm($form, $entry, $request)) {
            return new Response('changed', 201);
        }

        return array(
            'form'  => $form->createView(),
            'entry' => $entry,
        );
    }

    /**
     * @Route("/entry/{id}/delete.html",name="entry_delete")
     * @Template()
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

        return array(
            'form'   => $form->createView(),
            'entry'  => $entry,
            'filter' => $filter,
        );

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
