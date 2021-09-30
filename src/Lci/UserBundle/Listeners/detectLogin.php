<?php
// srv/www/htdocs/Symfony/src/Lci/UserBundle/Listeners/detectLogin.php
namespace Lci\UserBundle\Listeners;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class detectLogin {

	private $session;

    // Sauvegarde de la date de connexion
    public function enregistreConnexion($typeConnexion, $dateConnexion, $token, $session) {
        $fichier_logs = 'logs/connexions.log';
        //echo getcwd();
        $token_fichier_log = fopen($fichier_logs, 'a+');
        // Enregistrement du token dans le fichier
        switch ($typeConnexion) {
            case 'SUCCESS':
                fputs($token_fichier_log,$dateConnexion." Connexion de ".$token->getUser()->getLabel()." [ ".$token->getUser()->getUsername()." ]\n");
                break;
            case 'FAILED':
                fputs($token_fichier_log,$dateConnexion." Tentative connexion (en erreur) avec l'identifiant [ ".$_POST['_username']." ] et le mot de passe [ ".$_POST['_password']." ]\n");
			    $flashbag = $session->getFlashBag();
            	$flashbag->add('erreur', 'Login ou mot de passe incorrect');
                break;
            case 'LOGOUT':
                fputs($token_fichier_log,$dateConnexion." Déconnexion de ".$token->getUser()->getLabel()." [ ".$token->getUser()->getUsername()." ]\n");
                break;
        }
        fclose($token_fichier_log);
        return;
    }

	// Vérification qu'une clé de double authentification existe ou pas. : Si elle n'existe pas on propose à l'utilisateur d'en créer une
	// Si elle existe on procede à la vérification de l'authentification
	public function verif_totp_key($session, $user) {
		if ($user->getTotpKey() != '') {
			$session->set('totp_auth', true);
		}else {
			$session->set('totp_auth', false);
		}
	}

   	public function is_totp_key_exist($user) {
		if ($user->getTotpKey() != '') {
			return false;
		} else {
			return true;
		}
    }

}

