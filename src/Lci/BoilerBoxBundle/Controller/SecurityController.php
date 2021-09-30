<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

use Otp\Otp;
use Otp\GoogleAuthenticator;
use ParagonIE\ConstantTime\Encoding;


/**
 * Controller managing security.
 *
 * @author Thibault Duplessis <thibault.duplessis@gmail.com>
 * @author Christophe Coevoet <stof@notk.org>
 */
class SecurityController extends Controller
{
    private $tokenManager;

    public function __construct(CsrfTokenManagerInterface $tokenManager = null)
    {
        $this->tokenManager = $tokenManager;
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function loginAction(Request $request)
    {
    	$translator = $this->get('translator');
    	// Récupération des informations de la date courante
    	$service_configuration  = $this->container->get('lci_boilerbox.configuration');
    	$tab_date = $service_configuration->maj_date();



        /** @var $session Session */
        $session = $request->getSession();

        $authErrorKey = Security::AUTHENTICATION_ERROR;
        $lastUsernameKey = Security::LAST_USERNAME;

        // get the error if any (works with forward and redirect -- see below)
        if ($request->attributes->has($authErrorKey)) {
            $error = $request->attributes->get($authErrorKey);
			$error = "1 Erreur d'identification";
        } elseif (null !== $session && $session->has($authErrorKey)) {
            //$error = $session->get($authErrorKey);
            $session->remove($authErrorKey);
			$error = "2 Erreur d'identification";
        } else {
            $error = null;
        }


		/*
        if (!$error instanceof AuthenticationException) {
            $error = null; // The value does not come from the security component.
        }
		*/


        // last username entered by the user
        $lastUsername = (null === $session) ? '' : $session->get($lastUsernameKey);

        $csrfToken = $this->tokenManager
            ? $this->tokenManager->getToken('authenticate')->getValue()
            : null;

    	return $this->renderLogin(array(
    	    'last_username' => $lastUsername,
    	    'error' => $translator->trans($error),
    	    'csrf_token' => $csrfToken,
    	    'leJour' => $tab_date['jour'],
    	    'lHeure' => $tab_date['heure'],
    	    'timestamp' => $tab_date['timestamp']
    	));
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        return $this->render('FOSUserBundle:Security:login.html.twig', $data);
		//return $this->render('LciBoilerBoxBundle:Connexion:login.html.twig', $data);
    }


}
