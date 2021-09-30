<?php

/*
 * This file is part of the FOSUserBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lci\BoilerBoxBundle\Controller;

use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\UserBundle\Mailer\Mailer;

use Symfony\Component\EventDispatcher\EventDispatcher;


use FOS\UserBundle\Controller\ResettingController as BaseController;

/**
 * Controller managing the resetting of the password.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class LciResettingController extends BaseController
{
    private $eventDispatcher;
    private $formFactory;
    private $userManager;
    private $tokenGenerator;
    private $mailer;

    /**
     * @var int
     */
    private $retryTtl;

    public function __construct()
    {
    }

    /**
     * Request reset user password: submit form and send email.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sendEmailAction(Request $request)
    {
		$username = $request->request->get('username');
        $user = $this->myGetUser($username);
        $event = new GetResponseNullableUserEvent($user, $request);
		$this->eventDispatcher = new eventDispatcher();
        $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }
        if (null !== $user && !$user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.retry_ttl'))) {
			return $this->redirectToRoute('fos_user_resetting_send_email', [
    			'request' => $request
			], 307);
		} else if ($user == null) {
			return $this->render('FOSUserBundle:Resetting:userNotKnown.html.twig');
		} else {
			return $this->render('FOSUserBundle:Resetting:passwordAlreadyRequested.html.twig');
		}

        return new RedirectResponse($this->generateUrl('fos_user_resetting_check_email', array('username' => $username)));
    }

    /**
     * Tell the user to check his email provider.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function checkEmailAction(Request $request)
    {
        $username = $request->query->get('username');
		$user = $this->myGetUser($username);

        if (empty($username)) {
            // the user does not come from the sendEmail action
            return new RedirectResponse($this->generateUrl('fos_user_resetting_request'));
        }

        return $this->render('@FOSUser/Resetting/check_email.html.twig', array(
            'tokenLifetime' => ceil($this->retryTtl / 3600),
            'email' => $user->getEmail()
        ));
    }


	protected function myGetUser($username) {
 		$user = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByEmail($username);
        if ($user == null) {
            $user = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername($username);
        }
		return $user;
	}
}
