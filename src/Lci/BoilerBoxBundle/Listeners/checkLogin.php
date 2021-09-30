<?php
///var/www/html/BoilerBox.fr/BoilerBox/src/Lci/BoilerBoxBundle/Listeners/checkLogin.php
namespace Lci\BoilerBoxBundle\Listeners;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class checkLogin {

    private $session;

	public function check_totp($user) {	
		
	}

    public function enregistreConnexion($typeConnexion, $dateConnexion, $token) {
        $fichier_logs = 'logs/connexions.log';
        //echo getcwd();
        $token_fichier_log = fopen($fichier_logs, 'a+');
         fputs($token_fichier_log,$dateConnexion." Connexion de ".$token->getUser()->getLabel()." [ ".$token->getUser()->getUsername()." ]\n");
        fclose($token_fichier_log);
        return;
    }


    // Vérification qu'une clé de double authentification existe ou pas. : Si elle n'existe pas on propose à l'utilisateur d'en créer une
    // Si elle existe on procede à la vérification de l'authentification
    public function is_totp_key_exist($session, $user) {
        $fichier_logs = 'logs/connexions.log';
        $token_fichier_log = fopen($fichier_logs, 'a+');
        if ($user->getTotpKey() != '') {
            $session->set('totp_auth', true);
            fputs($token_fichier_log, "Key exist\n");
        }else {
            $session->set('totp_auth', false);
            fputs($token_fichier_log, $user->getId()."No Key ".$session->get('totp_auth')."\n");
        }
        fclose($token_fichier_log);
    }


    public function check_totp() {
        $fichier_logs = 'logs/connexions.log';
         $token_fichier_log = fopen($fichier_logs, 'a+');
            fputs($token_fichier_log, "Check\n");
        fclose($token_fichier_log);
    }
}

