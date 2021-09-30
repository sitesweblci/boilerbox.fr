<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Otp\Otp;
use Otp\GoogleAuthenticator;
use ParagonIE\ConstantTime\Encoding;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BoilerBoxController extends Controller {

/**
 * Récupérer la véritable adresse IP d'un visiteur
*/
function get_ip() {
	// IP si internet partagé
	if (isset($_SERVER['HTTP_CLIENT_IP'])) {
	    return $_SERVER['HTTP_CLIENT_IP'];
	}
	// IP derrière un proxy
	elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	    return $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	// Sinon : IP normale
	else {
	    return $_SERVER['REMOTE_ADDR'];
	}
}


public function indexAction($name) {
	// Si l'utilisateur a les droits ROLE_AUTO_ENQUETE : On le redirige automatiquement vers la page de téléchargement des enquêtes
	if ($this->get('security.authorization_checker')->isGranted('ROLE_AUTO_ENQUETE')) {
		return $this->redirectToRoute('lci_bons_externe_enquete');
	}
    return $this->render('LciBoilerBoxBundle:Connexion:login.html.twig', array('name' => $name));
}



public function accesSiteAction($id_site=null, SessionInterface $session, Request $request) 
{
	$em = $this->container->get('doctrine')->getManager();

/* TEST DU CHECK DES SITES 
	$ent_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find(234);
    $this->container->get('lci_boilerbox.utilitaires')->checkAccesSite($ent_site);
    return new Response();
*/

	// Variable ajoutée pour retourner sur l'ancien site lors du dev
	$nouveau_lien = $session->get('nouveau_lien', true);

	$response = new Response();
	//$session = $this->session;
	// Indique si l'authentification double facteur est utilisée
	$auth_double = false;

	$acces_autorise = false;
	if ($session->get('auth') === true) {
		$acces_autorise = true;
	}
	if ($this->get('security.authorization_checker')->isGranted('ROLE_AUTO_ENQUETE')) {
        return $this->redirectToRoute('lci_bons_externe_enquete');
    }

	// Afficher l'adresse IP
    // Récupération de la liste des sites autorisés pour l'utilisateur connecté
    $userLog = $session->get('userLog', array());
    if (empty($userLog)) {
        return $this->redirect($this->generateUrl('fos_user_security_login'));
    }
    $user = $em->getRepository('LciBoilerBoxBundle:User')->findOneBy(array('username' => $userLog['login']));
	
	if ($request->getMethod() == 'POST') {
		// Si la clé d'Authentification double facteur est donnée : 
		// 1 On vérifie si la variable de session du code barre existe. SI oui, on enregistre dans les paramètres de l'utilisateur (c'est que c'est la première authentification en double facteur)
		// 2 On vérifie qu'elle n'a pas déjà été utilisée et si elle est correcte
		if (isset($_POST['totp_key'])) {
			$key = $_POST['totp_key'];
			// 1
			if ($session->get('totp_secret') != null) {
           	    $user->setTotpKey($session->get('totp_secret'));
           	    $em->flush();
           	}
			// 2		
			if ($request->cookies->get('key_google_used')  != null) {
				if ($key == $request->cookies->get('key_google_used')) {
					$request->getSession()->getFlashBag()->add('info', "Code déjà utilisé. Veuillez patienter jusqu'à l'obtention d'un nouveau code.");
					return $this->render('LciBoilerBoxBundle:Connexion:totp.html.twig');
				} 
			}
			// Vérification de la clé double facteur / Soit dans la variable utilisateur si elle existe, soit en variable de session
			$otp = new Otp();
			$secret = $user->getTotpKey();
			if ($otp->checkTotp(Encoding::base32DecodeUpper($secret), $key)) {
				// Correct Key. On indique que la clé a été utilisée pour éviter le replay attack : Ceci dans un cookies pour conserver l'information entre les sessions
				$cookie = new Cookie('key_google_used', $key, time() + 30, '/', null, false, false);
				$response->headers->setCookie($cookie);

				// On autorise l'accès au site
				$acces_autorise = true;
				// Si la variable est définie à false, c'est que nous somme dans le cas de la 1ere demande d'authentification en double facteur
				// Cette variable de session est analysée par un listener pour autoriser ou non l'accès aux pages du site
				if ($session->get('totp_auth') == false) {
					$session->set('totp_auth', true);
				}
			} else {
				$request->getSession()->getFlashBag()->add('info', "Code incorrect.");		
				if ($session->get('auth_code_error') === null) {
					$session->set('auth_code_error', 1);
				} else {
					$session->set('auth_code_error', $session->get('auth_code_error') + 1);
				}
				if ($session->get('auth_code_error') < 3) {
					if ($session->get('auth_code_error')== 2) {
						if ($session->get('totp_auth') == false) {
							$request->getSession()->getFlashBag()->add('info', " - Attention, suite à un nouveau code erroné la demande d'authentification sera annulée.");
						} else {
							 $request->getSession()->getFlashBag()->add('info', " - Dernière tentative.");
						}
					}
					if ($session->get('totp_auth') == false) {
						// Si l'utilisateur ne s'est jamais connecté on lui repropose le QrCode
                        $url_qrCode = $user->getQrCode();
                        return $this->render('LciBoilerBoxBundle:Connexion:totp.html.twig', array(
                            'qrcode' => $url_qrCode
                        ));
					} else {
						return $this->render('LciBoilerBoxBundle:Connexion:totp.html.twig');
					}
				} else {
					// Gestion du cas ou 3 erreur ont été effectuées alors que la demande d'authentification par double facteur vient d'être faite. 
					// On annule la demande en double facteur
					if ($session->get('totp_auth') == false) {
						$user->setTotpKey('');
						$em->flush();
					}
					// Deconnexion
					return $this->redirect($this->generateUrl("fos_user_security_logout"));
				}
			}
		} else {
			// Sinon 
			// redirection vers la page de login du site distant
			return new Response();
		}
	}

	$champs_de_tri = 'affaire';
	$ordre_de_tri = 'ASC';
	if (isset($_GET['champs_de_tri'])) {
		$champs_de_tri = $_GET['champs_de_tri'];
		$ordre_de_tri = $_GET['ordre_de_tri'];
	}
	// On place les paramètres de tri en variables de session pour les récupérer lors d'une request Ajax : "AjaxController.php" (getUserListeSitesAction)
	$_SESSION['champs_de_tri'] = $champs_de_tri;
	$_SESSION['ordre_de_tri'] = $ordre_de_tri;

	// Recup de la liste des sites accessibles par l'utilisateur
	$liste_sites = $em->getRepository('LciBoilerBoxBundle:site')->myFindByUser($user, $champs_de_tri, $ordre_de_tri);
	$label = $user->getLabel();
    $service_configuration = $this->container->get('lci_boilerbox.configuration');
    $tab_date = $service_configuration->maj_date();
	$nombre_problemes_affectes = $service_configuration->getNombreProblemesNonClos();
	// Recherche de la date de test de ping
	$date_test_acces_sites = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('date_test_de_disponibilite')->getValeur();

	if ($acces_autorise === false) {
		// Aucune clé double facteur enregistrée/ On propose le bouton de création de clé
		// Cette variable de session est instancié par le Listener de login ou lors de la premiere demande d'authentification double facteur
		if ($session->get('totp_auth') === false) {
			// On accorde l'accès aux pages
			$session->set('auth', true);
			// On indique que la connexion ne se fait pas en double auth
			$auth_double = false;

			if ($nouveau_lien == true) 
			{
				// L'identifiant de site ($id_site) est passé en paramètre GET, on le fournit à la page de connexion pour automatiser l'affichage de la popup du site
				return $this->render('LciBoilerBoxBundle:Connexion:accueil.html.twig',array(
            	    'auth_double' => $auth_double,
            	    'label' => $label,
            	    'liste_sites' => $liste_sites,
            	    'leJour' => $tab_date['jour'],
            	    'lHeure' => $tab_date['heure'],
            	    'timestamp' => $tab_date['timestamp'],
                	'nombre_problemes_affectes' => $nombre_problemes_affectes,
               	 	'dateAccess' => $date_test_acces_sites,
					'champs_de_tri' => $champs_de_tri,
					'ordre_de_tri' => $ordre_de_tri,
					'idSite' => $id_site
            	));
			} else {
        		return $this->render('LciBoilerBoxBundle:Connexion:logSites.html.twig',array(
					'auth_double' => $auth_double,
        		    'label' => $label,
        		    'liste_sites' => $liste_sites,
        		    'leJour' => $tab_date['jour'],
        		    'lHeure' => $tab_date['heure'],
        		    'timestamp' => $tab_date['timestamp'],
        		    'nombre_problemes_affectes' => $nombre_problemes_affectes,
        		    'dateAccess' => $date_test_acces_sites
        		));
			}
		} else {
			// Une clé double facteur existe mais n'a pas été validée : On demande le code double facteur 
			return $this->render('LciBoilerBoxBundle:Connexion:totp.html.twig');
		} 
	} else {
		// Une clé double facteur existe et a été validée ou on revient sur la page après avoir recu une précédente validation d'accès au site

		// On fait le if pour éviter de réécrire la variable à chaque retour sur la page d'accueil
		if ($session->get('auth') == false) {	
			$session->set('auth', true);
		}
		// après avoir recu une précédente validation d'accès au site : On indique que la validation se fait sans clé
		if ($session->get('totp_auth') === false) {
			// Affichage du bouton 'Activer l'authentification'
			$auth_double = false;
		} else {
			// Affichage du bouton 'Désactiver l'authentification'
			$auth_double = true;
		}
		
		if ($nouveau_lien == true) 
		{
			// L'identifiant de site ($id_site) est passé en paramètre GET, on le fournit à la page de connexion pour automatiser l'affichage de la popup du site
        	$response->setContent($this->renderView('LciBoilerBoxBundle:Connexion:accueil.html.twig',array(
        	    'auth_double' => $auth_double,
        	    'label' => $label,
        	    'liste_sites' => $liste_sites,
        	    'leJour' => $tab_date['jour'],
        	    'lHeure' => $tab_date['heure'],
        	    'timestamp' => $tab_date['timestamp'],
        	    'nombre_problemes_affectes' => $nombre_problemes_affectes,
        	    'dateAccess' => $date_test_acces_sites,
				'champs_de_tri' => $champs_de_tri,
                'ordre_de_tri' => $ordre_de_tri,
				'idSite' => $id_site
        	)));
		} else {
			$response->setContent($this->renderView('LciBoilerBoxBundle:Connexion:logSites.html.twig',array(
        	    'auth_double' => $auth_double,
        	    'label' => $label,
        	    'liste_sites' => $liste_sites,
        	    'leJour' => $tab_date['jour'],
        	    'lHeure' => $tab_date['heure'],
        	    'timestamp' => $tab_date['timestamp'],
        	    'nombre_problemes_affectes' => $nombre_problemes_affectes,
        	    'dateAccess' => $date_test_acces_sites
        	)));
		}
		return $response;
	}
}


// Fonction qui initialise la création d'une clé double facteur
// Ou
// Désactive l'authentification double facteur (si authentification déja activé)
public function activationAuthDoubleFacteurAction(SessionInterface $session) 
{
    $em = $this->container->get('doctrine')->getManager();
    $user = $this->get('security.token_storage')->getToken()->getUser();

	// Désactivation de l'authentification double facteur (si authentification déja activé)
	if ($user->getTotpKey()) {
		$user->setTotpKey(null);
		$em->flush();
		return $this->redirect($this->generateUrl('fos_user_security_logout'));
	}

	// Initialisation de la création d'une clé double facteur
	// Récupération d'une clé secrette
	$secret = GoogleAuthenticator::generateRandom();
	// On place la clé secrete en variable de session pour l'enregistrer dans le compte utilisateur lors de la validation du code
	$session->set('totp_secret', $secret);

	// Mise de la clé dans le champs totpKey de l'utilisateur
	// Création du QRCode
    $user_email = $user->getEmail();
    $url_qrcode = GoogleAuthenticator::getQrCodeUrl('totp', "BoilerBox ($user_email)", $secret);
	// On inscrit le qrcode dans les paramètres de l'utilisateur
	$user->setQrCode($url_qrcode);
	$em->flush();
	return $this->render('LciBoilerBoxBundle:Connexion:totp.html.twig', array(
                'qrcode' => $url_qrcode
    ));

}

// AJAX : Enregistrement des parametres de log
public function defineUserLogAction(SessionInterface $session) {
	$userLog['login'] = $_POST['login'];
	$userLog['password'] = $_POST['password']; 
	//$session->set('userLog', $userLog);
    $session->set('userLog', $userLog);
	return new Response();
}

}

