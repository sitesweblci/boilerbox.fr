<?php
//var/www/html/BoilerBox.fr/BoilerBox/src/Lci/BoilerBoxBundle/Listeners/checkLoginListener.php
namespace Lci\BoilerBoxBundle\Listeners;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Lci\UserBundle\Listeners\detectLogin;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

class checkLoginListener {
    protected $session;
    protected $detectLogin;
	protected $tokenSecurity;
	protected $serviceLog;
	protected $serviceRouter;
    protected $dateConnexion;


    public function __construct(Router $router, detectLogin $detectLogin, Session $session, TokenStorage $tokenSecurity, $serviceLog) {
		$this->detectLogin = $detectLogin;
        $this->session = $session;
		$this->tokenSecurity = $tokenSecurity;
		$this->serviceLog = $serviceLog;
		$this->serviceRouter = $router;

        $date = new \Datetime();
        $this->dateConnexion = $date->format('d-m-Y à H:i:s');
    }

    public function checkLogin(GetResponseEvent $event) {
		//$this->serviceLog->setLog('route : '.$event->getRequest()->get('_route'), 'connexions.log');
		if ($this->tokenSecurity->getToken()) {
			$user = $this->tokenSecurity->getToken()->getUser();
			if (gettype($user) != "string") {
				// Si la variable de session d'autorisation n'est pas définie a true on revoit vers la page de connexion
				if (
				    ($event->getRequest()->get('_route') !== 'fos_user_security_login') &&
                    ($event->getRequest()->get('_route') !== 'lci_boilerbox_accesSite') &&
                    ($event->getRequest()->get('_route') !== '') &&
                    ($event->getRequest()->get('_route') !== 'lci_boilerbox_index') &&
                    ($event->getRequest()->get('_route') !== 'lci_cgu_lecture')
                ) {
					if (($this->session->get('auth') === false) || ($this->session->get('auth') === null)) {
						$route = "fos_user_security_logout";
						if ($route != $event->getRequest()->get('_route')) {
							$url = $this->serviceRouter->generate($route);
							$response = new RedirectResponse($url);
							$event->setResponse($response);
						}
					}
				}
			}
		}
    }
}
