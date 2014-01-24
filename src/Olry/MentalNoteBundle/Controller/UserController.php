<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DomCrawler\Crawler;

use Olry\MentalNoteBundle\Criteria\EntryCriteria;

use FOS\UserBundle\Form\Type\ChangePasswordFormType;

class UserController extends AbstractBaseController
{

    /**
     * @Route("/user/change-password",name="user_change_password")
     * @Template()
     */
    public function indexAction()
    {
        $user    = $this->getUser();
        $request = $this->getREquest();

        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.change_password.form.factory');

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
            $userManager = $this->container->get('fos_user.user_manager');

            $userManager->updateUser($user);

            $url = $this->container->get('router')->generate('homepage');
            return new RedirectResponse($url);
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
        );
    }

}
