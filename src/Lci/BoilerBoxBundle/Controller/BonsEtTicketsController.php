<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;



use Lci\BoilerBoxBundle\Entity\BonsAttachement;
use Lci\BoilerBoxBundle\Entity\SiteBA;

use Lci\BoilerBoxBundle\Form\Type\SiteBAType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementCommentairesType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModificationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementValidationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification1Type;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification2Type;


use Lci\BoilerBoxBundle\Entity\Fichier;
use Lci\BoilerBoxBundle\Form\Type\FichierType;

use Lci\BoilerBoxBundle\Entity\Validation;
use Lci\BoilerBoxBundle\Form\Type\ValidationType;

use Lci\BoilerBoxBundle\Entity\ObjRechercheBonsAttachement;
use Lci\BoilerBoxBundle\Form\Type\ObjRechercheBonsAttachementType;

use Lci\BoilerBoxBundle\Entity\Contact;

use Lci\BoilerBoxBundle\Entity\Configuration;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Lci\BoilerBoxBundle\Form\Type\EquipementBATicketType;


use Symfony\Component\Form\FormError;

class BonsEtTicketsController extends Controller
{

    public function indexAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA')) {
            return $this->render('LciBoilerBoxBundle:Bons:index.html.twig');
        } else {
            return $this->visualiserAction(null, $request);
        }
    }


    public function visualiserAction($refresh = null, Request $request)
    {
        $filtre = false;
        if ($refresh !== null) 
		{
            $request->getSession()->remove('objRechercheBon');
        }
        // Si une recherche existe pour le bon affichage de la recherche
        if ($request->getSession()->has('objRechercheBon')) 
		{
            $entity_bon_recherche 	= $request->getSession()->get('objRechercheBon', null);

			// Modification de l'id du contact en nom de contact
			if ($entity_bon_recherche->getNomDuContact())
			{
				$e_contact = $this->getDoctrine()->getRepository('LciBoilerBoxBundle:Contact')->find($entity_bon_recherche->getNomDuContact());
				$entity_bon_recherche->setNomDuContact($e_contact->getNom());
			}

            $filtre 				= true;
            $entities_bons 			= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->rechercheDesBons($entity_bon_recherche);
        } else {
            // On vérifie quel est le type du compte.
            // Si il a les droits de gestion ba il peut visualiser tous les bons
            // Sinon il ne peut visualiser que ses bons
            if ($this->get('security.authorization_checker')->isGranted('ROLE_GESTION_BA')) 
			{
                // Affichage de tous les bons
                $entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->findAllByDtCreation();
            } else {
                // Affichage des bons de l'utilisateur courant
                $e_user_courant 		= $this->get('security.token_storage')->getToken()->getUser();
                $entity_bon_recherche 	= new ObjRechercheBonsAttachement();
                $entity_bon_recherche->setUser($e_user_courant);
                $entity_bon_recherche->setSaisie(false);

                $entities_bons 			= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->rechercheDesBons($entity_bon_recherche);
                //$entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->myFindByUser($e_user_courant);
            }
        }

        return $this->render('LciBoilerBoxBundle:Bons:form_visu_bons.html.twig', array(
            'filtre' => $filtre,
            'entities_bon' => $entities_bons
        ));
    }

	// Action du bouton reset de la recherche
	public function rechercherResetAction(Request $request)
	{
		$request->getSession()->remove('objRechercheBon');
		return $this->redirectToRoute('lci_bons_rechercher');
	}

    public function rechercherAction(Request $request)
    {
        // On envoi un formulaire de recherche pour pouvoir affiner la recherche des bons
        // Seulement pour le gestion ba : On autorise la recherche pour un utilisateur en particulier SINON recherche des bons de l'utilisateur courant.
        $session = $request->getSession();

        // Appel du formulaire de recherche : Necessite un objet BonsAttachement
        if ($session->has('objRechercheBon')) {
            $entity_bon_recherche = $session->get('objRechercheBon');
        } else {
            $entity_bon_recherche = new ObjRechercheBonsAttachement();
            //Valeurs par défaut du formulaire
            // Lors d'une nouvelle recherche, par défaut, on indique l'utilisateur courant comme Intervenant des bons à rechercher
            $entity_bon_recherche->setUser($this->get('security.token_storage')->getToken()->getUser());
            //$entity_bon_recherche->setDateMaxIntervention(date('Y-m-d'));
        }
        /*
            Utilisé pour résoudre l'erreur :
            Entities passed to the choice field must be managed. Maybe persist them in the entity manager?
        */
        if ($entity_bon_recherche->getUser()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getUser());
        }
        if ($entity_bon_recherche->getUserInitiateur()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getUserInitiateur());
        }
        if ($entity_bon_recherche->getValideur()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getValideur());
        }

        if ($entity_bon_recherche->getSite()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getSite());
        }

        $formulaire_bons_recherche = $this->createForm(ObjRechercheBonsAttachementType::class, $entity_bon_recherche);
        if ($request->getMethod() == 'POST') {
            if ($formulaire_bons_recherche->handleRequest($request)->isValid()) 
			{
				// Indique si lors de la selection de plusieurs Validations nous faisons un AND ou un OR
				$entity_bon_recherche->setConditionValidation('and');

                // Si un valideur est demandé mais qu'aucun service n'est selectionné, sélection de tous les services
                // Si la recherche sur la validation est effectuée, on analyse le sens de la validation (bon validé ou bon non validés par un service)
                // Si la recherche porte sur les bons non validés, on mets les valeurs des validations à false
                // Paramètre qui indique si la recherche se fait sur les bons validés (valeur par défaut) ou non validés
                if ($entity_bon_recherche->getSensValidation() === null) 	
				{
                    // Si aucun service de validation n'est selectionné mais qu'un valideur est renseigné, on recherche les bons validés par ce valideur dans tous les services (avec un OR)
                    if ($entity_bon_recherche->getValideur()) 
					{
                        $entity_bon_recherche->setValidationTechnique(true);
                        $entity_bon_recherche->setValidationPiece(true);
						$entity_bon_recherche->setValidationPieceFaite(true);
                        $entity_bon_recherche->setValidationSAV(true);
                        $entity_bon_recherche->setValidationFacturation(true);

						$entity_bon_recherche->setConditionValidation('or');
						$entity_bon_recherche->setSensValidation(true);
                    } else {
						// On ne fait pas de recherche sur une validation spécifique
                        $entity_bon_recherche->setValidationTechnique(false);
                        $entity_bon_recherche->setValidationPiece(false);
						$entity_bon_recherche->setValidationPieceFaite(false);
                        $entity_bon_recherche->setValidationSAV(false);
                        $entity_bon_recherche->setValidationFacturation(false);
                    }
                } else {
                    // Si une validation par service est demandées
                    // Si aucun service n'est selectionné, on considère qu'on recherche les bons de tous les services
                    if ((!$entity_bon_recherche->getValidationTechnique()) && (!$entity_bon_recherche->getValidationPiece()) && (!$entity_bon_recherche->getValidationSAV()) && (!$entity_bon_recherche->getValidationFacturation()) && (!$entity_bon_recherche->getValidationPieceFaite())) {
                        $entity_bon_recherche->setValidationTechnique(true);
                        $entity_bon_recherche->setValidationPiece(true);
						$entity_bon_recherche->setValidationPieceFaite(true);
                        $entity_bon_recherche->setValidationSAV(true);
                        $entity_bon_recherche->setValidationFacturation(true);
                    }
                }

                // Sauvegarde de l'objet recherche de bon d'attachement pour réaffichage des données lors de la prochaine requête
                $session->set('objRechercheBon', $entity_bon_recherche);
				
                return $this->redirectToRoute('lci_bons_visualiser');
            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:form_recherche_bons.html.twig', array(
            'form' => $formulaire_bons_recherche->createView()
        ));
    }


    // GESTION DES SITES POUR LES BONS D ATTACHEMENTS
    public function indexSiteAction()
    {
        return $this->render('LciBoilerBoxBundle:Bons:index_site_gestion.html.twig', array());
    }

    // Fonction qui permet de visualiser les informations d'un site
    public function visualiserSitesAction($idSiteActif)
    {
		
        $ents_sitesBA 		= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->findAll();
		if ($idSiteActif == null)
		{
			$idSiteActif = $ents_sitesBA[0]->getId();
		}
        $ent_siteBA_actif 	= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->find($idSiteActif);

        $ent_siteBA_actif->setLienGoogle($this->putZoomApi($this->putApiKey($ent_siteBA_actif->getLienGoogle())));


        //'https://www.google.com/maps/embed/v1/view?key=APIKEY&center='.trim($matches[1]).','.trim($matches[2]).'&zoom=ZOOMAPI&maptype=satellite';

        $latitude 	= $this->getLatLng('latitude', $ent_siteBA_actif->getLienGoogle());
        $longitude 	= $this->getLatLng('longitude', $ent_siteBA_actif->getLienGoogle());

        return $this->render('LciBoilerBoxBundle:Bons:visualiser_sitesBA.html.twig', array(
            'ents_sitesBA' 		=> $ents_sitesBA,
            'ent_siteBA_actif' 	=> $ent_siteBA_actif,
            'latitude' 			=> $latitude,
            'longitude' 		=> $longitude,
            'apiKey' 			=> $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur(),
            'zoomApi'		 	=> $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur()
        ));
    }

    private function putApiKey($url)
    {
        $apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $pattern = '/APIKEY/';
        return (preg_replace($pattern, $apiKey, $url));
    }

    private function putZoomApi($url)
    {
        $zoomApi = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur();
        $pattern = '/ZOOMAPI/';
        return (preg_replace($pattern, $zoomApi, $url));
    }

    private function getLatLng($type, $url)
    {
        $pattern = '/^latLng\((.+?),(.+?)\)$/';
        if (preg_match($pattern, $url, $matches)) {
            switch ($type) {
                case 'latitude':
                    return $matches[1];
                case 'longitude':
                    return $matches[2];
            }
        }
        return null;
    }


    // Création d'un fichier bat pour ouverture du dossier photos des BA
    public function creationFichierBatAction($id_bon = null)
    {
        $e_bon = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

        $filename       = 'chemin_vers_bi_'.$e_bon->getNumeroBA().'.bat';
        $filecontent    = "chcp 65001\nexplorer ".$e_bon->getCheminDossierPhotos();
        $response       = new Response($filecontent);

        // Create the disposition of the file
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename
        );

        // Set the content disposition
		$response->headers->set('Content-Type', 'application/bat');
        $response->headers->set('Content-Disposition', $disposition);

        // Dispatch request
        return $response;
    }

}
