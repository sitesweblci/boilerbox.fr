<?php
namespace Lci\BoilerBoxBundle\Controller;

use Lci\BoilerBoxBundle\Entity\Validation;
use Lci\BoilerBoxBundle\Entity\FichierSiteBA;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;


class AjaxBonsController extends Controller {

// Fonction qui modifie le paramètre EnqueteNecessaire d'un bon
public function setEnqueteAction() {
	$idBon = $_POST['identifiant'];
	$entity_bon = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
	if ($entity_bon->getEnqueteNecessaire() == true) {
		$entity_bon->setEnqueteNecessaire(false);
	} else {
		$entity_bon->setEnqueteNecessaire(true);
	}
	$this->getDoctrine()->getManager()->flush();
	return new Response();
}

// Fonction qui effectue la validation ou la suppression d'une ancienne validation d'une catégorie d'un bon
public function setValidationAction() 
{
	$em = $this->getDoctrine()->getManager();

    $idBon = $_POST['identifiant'];
    $type = $_POST['type'];
    $sens = $_POST['sens'];

	$entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
	$user = $this->get('security.token_storage')->getToken()->getUser();

	// Si le sens est false c'est que la checkbox est décochée : On définit le champs valide à 0 pour signifier que l'entité Validation est une Dé-Validation
	// Ajout du 02/12/2019 : Lors d'une dévalidation on informe l'ensemble des valideurs par mail.
	if ($sens == 'false') {
		switch ($type) {
			case 'technique':
				$entity_validation = $this->devalidation($entity_bon->getValidationTechnique(), $user);
				$entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_TECHNIQUE');
				break;
        	case 'sav':
				$entity_validation = $this->devalidation($entity_bon->getValidationSAV(), $user);
				$entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_SAV');
            	break;
        	case 'pieces':
				$entity_validation = $this->devalidation($entity_bon->getValidationHoraire(), $user);
				$entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_PIECES');
        	    break;
        	case 'facturation':
				$entity_validation = $this->devalidation($entity_bon->getValidationFacturation(), $user);
				$entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_FACTURATION');
        	    break;
        	default:
        	    break;
		}
		// Envoi du mail à l'ensemble du groupe de dévalidation
		$this->sendMailDevalidation($type, $entity_bon, $entities_users_validation, $user);
	} else {
		// Une Validation sur un bon d'intervention est effectuée
		$entity_validation = new Validation();
		$entity_validation->setValide(true);
        $entity_validation->setDateDeValidation(new \Datetime);
		$entity_validation->setType($type);
		$entity_validation->setUser($user);
		switch ($type) {
			case 'technique':
				$entity_bon->setValidationTechnique($entity_validation);
				break;
			case 'sav':
				$entity_bon->setValidationSAV($entity_validation);
				break;
			case 'pieces':
				// Si c'est une validation de type pièce on informe le gestionnaire_de_pieces (designation du gestionnaire de pièces dans le fichier app/config/parameters.yml)
				$entity_bon->setValidationHoraire($entity_validation);
				$entity_gestionnaire = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername($this->container->getParameter('gestionnaire_pieces'));
				if ($entity_gestionnaire)
				{
					$service_mail = $this->get('lci_boilerbox.mailing');
					$service_mail->sendMailPieces($entity_bon->getSite()->getIntitule().' ('.$entity_bon->getNumeroAffaire().') ', $entity_bon->getNumeroBA(), $user->getLabel(), $entity_gestionnaire->getEmail());
				}
				break;
			case 'facturation':
				$entity_bon->setValidationFacturation($entity_validation);
				break;
			default:
				break;
		}
	    $em->persist($entity_bon);
	}
	$em->flush();
    return new Response();
}

// Fonction qui enregistre les informations de dévalidation 
private function devalidation($entity_validation, $entity_user) {
	$entity_validation->setValide(false);
	$entity_validation->setDateDeValidation(new \Datetime());
	$entity_validation->setUser($entity_user);
	return $entity_validation;
}


public function archiveUnFichierDeBonAction() {
    $id_fichier_bon = $_POST['identifiant_fichier'];
    $em = $this->getDoctrine()->getManager();
    $entity_fichier = $em->getRepository('LciBoilerBoxBundle:Fichier')->find($id_fichier_bon);
	if ($entity_fichier->getArchive() == false) {
		$message_archivage = "Archivé par ".$this->get('security.token_storage')->getToken()->getUser()->getLabel()." le ".date('d/m/Y à H:i');
		$entity_fichier->setArchive(true);
		$entity_fichier->setInformations($message_archivage);
	} else {
		$message_archivage = "Désarchivé par ".$this->get('security.token_storage')->getToken()->getUser()->getLabel()." le ".date('d/m/Y à H:i');
		$entity_fichier->setArchive(false);
		$entity_fichier->setInformations($message_archivage);
	}
	$em->flush();
	return new Response();
}


public function getSiteBAEntityAction() {
	$tab_fichier = null;
	if (isset($_POST['id_site_ba'])){
		$id_site_ba = $_POST['id_site_ba'];
	} else {
		$id_site_ba = 1;
	}
	$em = $this->getDoctrine()->getManager();
	$entity_siteba = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($id_site_ba);

	$tab_siteba[] = $entity_siteba->getId();
    $tab_siteba[] = $entity_siteba->getIntitule();
	$tab_siteba[] = $entity_siteba->getAdresse();
	$tab_siteba[] = $this->putZoomApi($this->putApiKey($this->transformeUrlReverse($entity_siteba->getLienGoogle())));
	$tab_siteba[] = $entity_siteba->getInformationsClient();
	$tab_contacts = array();
    foreach ($entity_siteba->getContacts() as $ent_contact) {
		$tab_contact = array();
		$tab_contact['id'] = $ent_contact->getId();
		$tab_contact['nom'] = $ent_contact->getNom();
		$tab_contact['prenom'] = $ent_contact->getPrenom();	
		$tab_contact['email'] = $ent_contact->getMail();
		$tab_contact['telephone'] = $ent_contact->getTelephone();
		$tab_contact['fonction'] = $ent_contact->getFonction();
		$tab_contact['maj']	= $ent_contact->getDateMaj()->format('d/m/Y');
		$tab_contacts[] = $tab_contact;
    }
	$tab_siteba[] = $tab_contacts;
	foreach ($entity_siteba->getFichiersJoint() as $ent_fichier) {
		$tab_fichier[] = $ent_fichier->getAlt();
	}

	if ($tab_fichier != null) {
		$tab_siteba[] = $tab_fichier;
	} else {
		$tab_siteba[] = null;
	}

	
	echo json_encode($tab_siteba);
	return new Response;
}



// Fonction qui récupère l'url retournée par l'entité et extrait la latitude et la longitude
private function transformeUrlReverse($url) {
	// https://www.google.com/maps/embed/v1/view?key=AIzaSyA4ceVB6W6udd67ihnRTeR_Oiip9tY_87s&center=49.26347329999999,-123.14046880000001&zoom=ZOOMAPI&maptype=satellite
	$patternLatLng = '$^https?://www.google.com/maps/embed/v1/view\?key=APIKEY&center=(.+?),(.+?)&zoom.*$';
	$patternPlace = '$^https://www.google.com/maps/embed/v1/place\?key=APIKEY&q=(.+?)&zoom=.*$';
    if (preg_match($patternLatLng, $url, $matches)) {
        return 'latLng('.$matches[1].','.$matches[2].')';
    } else if  (preg_match($patternPlace, $url, $matches)) {
		return 'https://www.google.com/maps/place/'.$matches[1].'/';
	} else {
		return $url;
	}
}


private function putApiKey($url) {
	$apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
	$pattern = '/APIKEY/';
	return (preg_replace($pattern, $apiKey, $url));
}


private function putZoomApi($url) {
    $zoomApi = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur();
    $pattern = '/ZOOMAPI/';
    return (preg_replace($pattern, $zoomApi, $url));
}


private function sendMailDevalidation($type, $entity_bon, $entities_users, $entity_devalideur) {
	$sujet = 'Dévalidation '.$type;
	$titre = 'Dévalidation '.$type." pour l'affaire ".$entity_bon->getNumeroAffaire();
	
    $tab_destinataires = [];
    foreach ($entities_users as $entity_user) {
            $tab_destinataires[] = $entity_user->getEmail();
    }

    $service_mail = $this->get('lci_boilerbox.mailing');

    $tab_contenu = array();
	$tab_contenu[] = '%PLe '.date('d-m-Y à H\hi');
	$tab_contenu[] = ':%P';	
	$tab_contenu[] = '%PUne dévalidation ';
	$tab_contenu[] = '%G'.$type.' ';
	$tab_contenu[] = " a été effectuée sur le bon d'attachement de numéro ".$entity_bon->getNumeroBA(). ' par '.$entity_devalideur->getUsername();
	$tab_contenu[] = '%M ('.$entity_devalideur->getEmail().')';
	$tab_contenu[] = '.%P';
    $tab_contenu[] = "<br /><br /><br />";
    $tab_contenu[] = "Cordialement.<br /><br />";
    $tab_contenu[] = "Assistance BoilerBox.<br />";
	$tab_contenu[] = "%MAssistance_IBC@lci-group.fr";
    $tab_contenu[] = "<br /><br /><br />";

    $service_mail->sendMailMultiDestinataires($sujet, $titre, $tab_contenu, $tab_destinataires);
    return 0;
}


public function supprimerContactAction()
{
    if (isset($_POST['id_contact_supp']))
    {
        $em = $this->getDoctrine()->getManager();
        $id_contact = $_POST['id_contact_supp'];
        $ent_contact = $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
        $em->remove($ent_contact);
        $em->flush();
        return new Response();
    }
}


public function modifierContactAction()
{
    if (isset($_POST['id_contact_modif']))
    {
        $em = $this->getDoctrine()->getManager();
        $id_contact = $_POST['id_contact_modif'];
        $ent_contact = $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
        $ent_contact->setNom($_POST['nomContact']);
        $ent_contact->setPrenom($_POST['prenomContact']);
        $ent_contact->setTelephone($_POST['telephoneContact']);
        $ent_contact->setMail($_POST['emailContact']);
        $ent_contact->setFonction($_POST['fonctionContact']);
        $ent_contact->setDateMaj(new \Datetime());
        $em->flush();
        return new Response();
    }
}


public function archivageFichierSiteBAAction()
{
	if (isset($_POST['id_fichier']) && isset($_POST['archive']))
    {
		$em = $this->getDoctrine()->getManager();
		$id_fichier =  $_POST['id_fichier'];
		$archive = $_POST['archive'];
		$ent_fichier = $em->getRepository('LciBoilerBoxBundle:FichierSiteBA')->find($id_fichier);
		switch($archive)
		{
			case true:
				$ent_fichier->setArchive(true);	
				break;
			case false:
				$ent_fichier->setArchive(false);
				break;
		}
		$em->flush();
	}
	return new Response();
}


}
