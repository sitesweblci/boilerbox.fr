<?php
// srv/www/htdocs/Symfony/src/Lci/UserBundle/Listener/logoutListener.php
namespace Lci\UserBundle\Listeners;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class logoutListener implements LogoutHandlerInterface {
    protected $userManager;
   	protected $detectLogin;
	protected $dateDeconnexion; 
	protected $session;	

    public function __construct(UserManagerInterface $userManager, $detectLogin, $session){
        $this->userManager = $userManager;
		$this->detectLogin = $detectLogin;
		$this->session = $session;
        $date = new \Datetime();
        $this->dateDeconnexion = $date->format('d-m-Y à H:i:s');
    }
    
    public function logout(Request $Request, Response $Response, TokenInterface $Token) {
        // ..
        // Here goes the logic that you want to execute when the user logouts
        // ..
        $this->detectLogin->enregistreConnexion('LOGOUT', $this->dateDeconnexion, $Token, $this->session);
    }
}


