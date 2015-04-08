<?php

namespace Olry\MentalNoteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DomCrawler\Crawler;

use Olry\MentalNoteBundle\Criteria\EntryCriteria;

use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\Form\Model\ChangePassword;

class UserController extends AbstractBaseController
{

    /**
     * @Route("/user/change-password",name="user_change_password")
     * @Template()
     */
    public function indexAction()
    {
        $user    = $this->getUser();

        $form        = $this->container->get('fos_user.change_password.form');
        $formHandler = $this->container->get('fos_user.change_password.form.handler');

        if ($formHandler->process($user)) {
            $url = $this->container->get('router')->generate('homepage');

            return new RedirectResponse($url);
        }

        return array(
            'user' => $user,
            'form' => $form->createView(),
        );
    }
}
