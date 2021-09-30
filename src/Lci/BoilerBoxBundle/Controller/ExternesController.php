<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;


class ExternesController extends Controller {

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


// Récupére les bons d'attachement dont les enquêtes sont demandées
// Retourne les données sous forme de téléchargement de fichier csv
// Modifie le champs Enquete du bon pour indiquer que l'enquête est transmise.
/**
 * @Security("has_role('ROLE_AUTO_ENQUETE')")
**/
public function getNewEnquetesAction() {
	$em = $this->getDoctrine()->getManager();
	$entities_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->findBy(array('enqueteNecessaire' => true, 'enqueteFaite' => false));
	$en_tete_du_fichier_csv = array('Bon', 'Affaire', 'Site', 'Signature', 'Intervenant', 'Client');
	$lignes_du_fichiers_csv = array();
	$tab_des_bons_telecharges = array();
	foreach($entities_bon as $entity) {
		$lignes_du_fichiers_csv[] = array($entity->getNumeroBA(), 
										  $entity->getNumeroAffaire(), 
										  $entity->getNomDuSite(),
										  $entity->getDateSignature()->format('d/m/Y'),
										  $entity->getUser()->getLabel(),
										  $entity->getEmailContactClient()
									);
		$tab_des_bons_telecharges[] = $entity->getId();
	}
	// Appel d'un service pour créer le fichier pdf 
	$service_fichiers = $this->container->get('lci_files');
	$repertoire_destination = 'bonsAttachement/enquetes/';
	$nom_du_fichier_csv = 'lci_enquete_sur_bon_attachement';

	// Si des enquêtes sont à faire : Création du fichier csv et récupération de l'objet response (qui permet le téléchargement du fichier car le dernier paramétre = true)
	if ($lignes_du_fichiers_csv != null) {
		$response = $service_fichiers->creerFichierCsv($en_tete_du_fichier_csv, $lignes_du_fichiers_csv, $repertoire_destination, $nom_du_fichier_csv, true);
		// Modification en base de donnée des enquêtes déclarées dans le fichier pour indiquer qu'elles ont été téléchargées
		foreach($tab_des_bons_telecharges as $id_bon_attachement) {
			$bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon_attachement);
			$bon->setEnqueteFaite(true);
		}
		$em->flush();
	} else {
		// Si aucune nouvelle enquête n'est à faire : Retour d'un message 
		$response = $this->render('LciBoilerBoxBundle:Bons:aucune_enquete.html.twig');
	}
	return $response;
}


}
