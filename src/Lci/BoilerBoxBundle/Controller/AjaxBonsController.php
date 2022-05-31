<?php

namespace Lci\BoilerBoxBundle\Controller;

use Lci\BoilerBoxBundle\Entity\Validation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Lci\BoilerBoxBundle\Form\Type\EquipementBATicketType;
use Lci\BoilerBoxBundle\Entity\Contact;
use Lci\BoilerBoxBundle\Form\Type\ContactType;
use Lci\BoilerBoxBundle\Entity\SiteBA;
use Lci\BoilerBoxBundle\Form\Type\SiteBAType;



class AjaxBonsController extends Controller
{
    // Fonction qui modifie le paramètre EnqueteNecessaire d'un bon
    public function setEnqueteAction()
    {
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
		$em 		= $this->getDoctrine()->getManager();
        $idBon 		= $_POST['identifiant'];
        $type 		= $_POST['type'];
        $sens 		= $_POST['sens'];
        $entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $user 		= $this->get('security.token_storage')->getToken()->getUser();
		$entity_validation 				= null;
		$entity_user_emetteur_du_bon	= null;
		$entities_users_validation 		= null;

        // Si le sens est false c'est que la checkbox est décochée : On définit le champs valide à 0 pour signifier que l'entité Validation est une Dé-Validation
        // Ajout du 02/12/2019 : Lors d'une dévalidation on informe l'ensemble des valideurs par mail.
        if ($sens == 'false') {
            switch ($type) {
                case 'technique':
					$entity_validation = $entity_bon->getValidationTechnique();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_TECHNIQUE');
                    break;
                case 'sav':
					$entity_validation = $entity_bon->getValidationSAV();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_SAV');
                    break;
                case 'pieces':
					$entity_validation = $entity_bon->getValidationHoraire();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_PIECES');
                    break;
                case 'facturation':
					$entity_validation = $entity_bon->getValidationFacturation();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_FACTURATION');
                    break;
                default:
                    break;
            }
			if ($entity_validation != null)
			{
				$entity_user_emetteur_du_bon = $entity_validation->getUser();
				$this->devalidation($entity_validation, $user);
				// Envoi du mail à l'ensemble du groupe de dévalidation
            	$this->sendMailDevalidation($type, $entity_bon, $entities_users_validation, $user, $entity_user_emetteur_du_bon);
			}
        } else {
            // Une Validation sur un bon d'intervention est effectuée
            $entity_validation = new Validation();
            $entity_validation->setValide(true)
                ->setDateDeValidation(new \Datetime())
                ->setType($type)
                ->setUser($user)
            ;
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
                    if ($entity_gestionnaire) {
                        $service_mail = $this->get('lci_boilerbox.mailing');
                        $service_mail->sendMailPieces($entity_bon->getSite()->getIntitule() . ' (' . $entity_bon->getNumeroAffaire() . ') ', $entity_bon->getNumeroBA(), $user->getLabel(), $entity_gestionnaire->getEmail());
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
    private function devalidation($entity_validation, $entity_user)
    {
        $entity_validation->setValide(false);
        $entity_validation->setDateDeValidation(new \Datetime());
        $entity_validation->setUser($entity_user);
        return $entity_validation;
    }


    public function archiveUnFichierDeBonAction()
    {
		$em 			= $this->getDoctrine()->getManager();
        $id_fichier_bon = $_POST['identifiant_fichier'];
        $entity_fichier = $em->getRepository('LciBoilerBoxBundle:Fichier')->find($id_fichier_bon);

        if ($entity_fichier->getArchive() == false) {
            $message_archivage = "Archivé par " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i');
            $entity_fichier->setArchive(true);
            $entity_fichier->setInformations($message_archivage);
        } else {
            $message_archivage = "Désarchivé par " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i');
            $entity_fichier->setArchive(false);
            $entity_fichier->setInformations($message_archivage);
        }
        $em->flush();
        return new Response();
    }

	// Recherches des informations du site selectionné dans le formulaire de création d'un BA
    public function getSiteBAEntityAction()
    {
		$em 				= $this->getDoctrine()->getManager();

        if (isset($_POST['id_site_ba'])) {
            $id_site_ba = $_POST['id_site_ba'];
        } else {
            $id_site_ba = 1;
        }
        $e_siteba = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($id_site_ba);

        // On retourne le fichier serialize
        $serializer = $this->get('serializer');
        $jsonContent = $serializer->serialize(
            $e_siteba,
            'json', array('groups' => array('groupContact'))
        );
        return new Response($jsonContent);
    }


	public function getGoogleMapAction()
	{
		$url;
		$em = $this->getDoctrine()->getManager();

        $id_site_ba = $_POST['id_site_ba'];
        $e_siteba = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($id_site_ba);

        // transformeUrlReverse
        $patternLatLng = '$^https?://www.google.com/maps/embed/v1/view\?key=APIKEY&center=(.+?),(.+?)&zoom.*$';
		$patternPlace = '$^https://www.google.com/maps/embed/v1/place\?key=APIKEY&q=(.+?)&zoom=.*$';
        if (preg_match($patternLatLng, $e_siteba->getLienGoogle(), $matches)) 
		{
            $url = 'latLng(' . $matches[1] . ',' . $matches[2] . ')';
        } else if (preg_match($patternPlace, $e_siteba->getLienGoogle(), $matches)) 
		{
        	$url = 'https://www.google.com/maps/place/' . $matches[1] . '/';
		} else {
			$url = $e_siteba->getLienGoogle();
		}

        // putApiKey
        $apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $pattern = '/APIKEY/';
        $url = preg_replace($pattern, $apiKey, $url);

        // putZoomApi
        $zoomApi = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur();
        $pattern = '/ZOOMAPI/';
        $url = preg_replace($pattern, $zoomApi, $url);

        return new Response($url);
	}



    // Fonction qui récupère l'url retournée par l'entité et extrait la latitude et la longitude
    private function transformeUrlReverse($url)
    {
        // https://www.google.com/maps/embed/v1/view?key=AIzaSyA4ceVB6W6udd67ihnRTeR_Oiip9tY_87s&center=49.26347329999999,-123.14046880000001&zoom=ZOOMAPI&maptype=satellite
        $patternLatLng = '$^https?://www.google.com/maps/embed/v1/view\?key=APIKEY&center=(.+?),(.+?)&zoom.*$';
        if (preg_match($patternLatLng, $url, $matches)) {
            return 'latLng(' . $matches[1] . ',' . $matches[2] . ')';
        }

        $patternPlace = '$^https://www.google.com/maps/embed/v1/place\?key=APIKEY&q=(.+?)&zoom=.*$';
        if (preg_match($patternPlace, $url, $matches)) {
            return 'https://www.google.com/maps/place/' . $matches[1] . '/';
        }

        return $url;
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


    private function sendMailDevalidation($type, $entity_bon, $entities_users, $entity_devalideur, $entity_user_emetteur)
    {
        $sujet = 'Dévalidation ' . $type;
        $titre = 'Dévalidation ' . $type . " pour l'affaire " . $entity_bon->getNumeroAffaire();

        $tab_destinataires = [];
        foreach ($entities_users as $entity_user) 
		{
			// On ne met pas le devalideur dans la liste des utilisateurs qui doivent recevoir le mail de dévalidation
			if ($entity_user->getId() != $entity_devalideur->getId())
			{
            	$tab_destinataires[] = $entity_user->getEmail();
			}
        }
		// Ajout de l'emetteur de la demande de validation au mail de dévalidation
		$tab_destinataires[] = $entity_user_emetteur->getEmail();

        $service_mail = $this->get('lci_boilerbox.mailing');

        $tab_contenu = array();
        $tab_contenu[] = '%PLe ' . date('d-m-Y à H\hi');
        $tab_contenu[] = ':%P';
        $tab_contenu[] = '%PUne dévalidation ';
        $tab_contenu[] = '%G' . $type . ' ';
        $tab_contenu[] = " a été effectuée sur le bon d'attachement de numéro " . $entity_bon->getNumeroBA() . ' par ' . $entity_devalideur->getUsername();
        $tab_contenu[] = '%M (' . $entity_devalideur->getEmail() . ')';
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
		$em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_contact_supp'])) 
		{
            $id_contact 	= $_POST['id_contact_supp'];
            $ent_contact 	= $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
            $em->remove($ent_contact);
            $em->flush();
            return new Response();
        }
    }


    public function modifierContactAction()
    {
		$em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_contact_modif'])) 
		{
            $id_contact 	= $_POST['id_contact_modif'];
            $ent_contact 	= $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
            $ent_contact->setNom($_POST['nomContact']);
            $ent_contact->setPrenom($_POST['prenomContact']);
            $ent_contact->setTelephone($_POST['telephoneContact']);
            $ent_contact->setMail($_POST['emailContact']);
            $ent_contact->setFonction($_POST['fonctionContact']);
            $ent_contact->setDateMaj(new \Datetime());
            $em->flush();
			echo 'Contact '.$_POST['nomContact'].' modifié';
            return new Response();
        }
    }


    public function archivageFichierSiteBAAction()
    {
		$em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_fichier']) && isset($_POST['archive'])) {
            $id_fichier 	= $_POST['id_fichier'];
            $archive 		= $_POST['archive'];
            $ent_fichier 	= $em->getRepository('LciBoilerBoxBundle:FichierSiteBA')->find($id_fichier);
            switch ($archive) {
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




    public function choixServiceAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();

        if (!($_POST['service'])) 
		{
            return new Response();
        } else {
            $role_service   = strtoupper('role_service_'.$_POST['service']);
        }

        $ents_user      			= $em->getRepository('LciBoilerBoxBundle:User')->findAll();
        $tab_des_membres_du_service = array();

        foreach($ents_user as $e_user)
        {
            if ($role_service != null)
            {
                if ($e_user->hasRole($role_service))
                {
                    array_push($tab_des_membres_du_service, $e_user->getId());
                }
            }
        }
        return new Response(json_encode($tab_des_membres_du_service));
    }


	// Fonction qui supprime un fichier d'un bon (appelé sur la page de saisie des bons)
	public function delFileAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		if ($_GET['id_file'])
		{
			$e_fichier = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Fichier')->find($_GET['id_file']);
			$em->remove($e_fichier);
			$em->flush();
			echo "Fichier supprimé";
		}
		return new Response();
	}

    // Fonction qui supprime un fichier d'un siteBA (appelé sur la page de saisie des bons)
    public function delFileSiteBAAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($_GET['id_file'])
        {
            $e_fichier = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:FichierSiteBA')->find($_GET['id_file']);
            $em->remove($e_fichier);
            $em->flush();
            echo "Fichier supprimé";
        }
        return new Response();
    }

		

	public function delEquipementAction()
    {
		$em = $this->getDoctrine()->getManager();

		if (isset($_POST['id_equipement'])) 
		{
			$e_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($_POST['id_equipement']);

			$em->remove($e_equipement);
			$em->flush();
       		echo "suppression de ".$_POST['id_equipement']." effectuée";
		}
        return new Response();
    }

    public function creerEquipementAction(Request $request)
    {
		$em 			= $this->getDoctrine()->getManager();
		$e_equipement 	= new EquipementBATicket();
		$f_equipement 	= $this->createForm(EquipementBATicketType::class, $e_equipement);
		$f_equipement->handleRequest($request);

        if ($request->isXMLHttpRequest())
        {
            if ($f_equipement->isSubmitted())
            {
                if ($f_equipement->isValid())
                {
					$e_siteBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($f_equipement->get('siteBA')->getData()->getId());
					$e_siteBA->addEquipementBATicket($e_equipement);

                    $em->persist($e_equipement);
                    $em->flush();

			        $reponse = array(
        		    	"message" => $e_equipement->getId()
        			);

					return new Response(json_encode($reponse));
                } 
                return $this->render('LciBoilerBoxBundle:Bons:creer_equipement.html.twig', [
                    'form_equipement'  	=> $f_equipement->createView(),
                    'id'            	=> $e_equipement->getId()
                ]);
            } 
        }
        return $this->render('LciBoilerBoxBundle:Bons:creer_equipement.html.twig', array(
            'form_equipement'  	=> $f_equipement->createView(),
            'id'            	=> $e_equipement->getId()
        ));
	}



    public function creerContactsSitesBAAction(Request $request)
    {
		$em 		= $this->getDoctrine()->getManager();
        $e_contact 	= new Contact();
        $f_contact 	= $this->createForm(ContactType::class, $e_contact);

        $f_contact->handleRequest($request);
		if ($request->isXMLHttpRequest()) 
		{
			if ($f_contact->isSubmitted()) 
			{
				// Si c'est une modification de contact on recrée le formulaire à partir du contact à modifier
				if ($f_contact->get('id')->getData() != null)
				{	
					$e_contact = $this->getDoctrine()->getRepository('LciBoilerBoxBundle:Contact')->find($f_contact->get('id')->getData());
					$f_contact = $this->createForm(ContactType::class, $e_contact);
					$f_contact->handleRequest($request);
				}
				if ($f_contact->isValid())
            	{
					$e_contact->setDateMaj(new \Datetime());
                	$em->persist($e_contact);
                	$em->flush();

                    $reponse = array(
                        "message" => $e_contact->getId()
                    );

                    return new Response(json_encode($reponse));
				}
				return $this->render('LciBoilerBoxBundle:Bons:creer_contact.html.twig', [
                    'form_contact' 	=> $f_contact->createView(),
					'id' 			=> $e_contact->getId()
                ]);
            } 
		}
        return $this->render('LciBoilerBoxBundle:Bons:creer_contact.html.twig', array(
			'form_contact' 	=> $f_contact->createView(),
          	'id' 			=> $e_contact->getId()
        ));
    }



    public function creerSiteBAAction(Request $request)
    {
        $em         		= $this->getDoctrine()->getManager();

		// Lecture de l'option de configuration [upload_max_filesize] pour l'indiquer dans la page html
		$max_upload_size	= ini_get('upload_max_filesize');

		// Clé google pour pouvoir utiliser les API de google
		$apiKey             = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();

        $e_siteBA  			= new SiteBA();
        $f_siteBA   		= $this->createForm(SiteBAType::class, $e_siteBA);

        return $this->render('LciBoilerBoxBundle:Bons:creer_siteBA.html.twig', array(
			'max_upload_size'	=> $max_upload_size,
            'apiKey'            => $apiKey,
            'form_siteBA'  		=> $f_siteBA->createView(),
            'id'            	=> $e_siteBA->getId()
        ));
    }


    // Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected function getErrorMessages(\Symfony\Component\Form\Form $form) 
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }


	public function getInfosContactAction()
	{
		$e_contact = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Contact')->find($_POST['id_contact']);
        // On retourne le fichier serialize
        $serializer = $this->get('serializer');
        $jsonContent = $serializer->serialize(
            $e_contact,
            'json', array('groups' => array('groupContact'))
        );
        return new Response($jsonContent);
	}

}
