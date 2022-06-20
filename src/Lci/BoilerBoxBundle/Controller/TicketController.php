<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Lci\BoilerBoxBundle\Entity\BonsAttachement;
use Lci\BoilerBoxBundle\Entity\Contact;
use Lci\BoilerBoxBundle\Entity\Configuration;
use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Lci\BoilerBoxBundle\Entity\Fichier;
use Lci\BoilerBoxBundle\Entity\SiteBA;
use Lci\BoilerBoxBundle\Entity\Validation;


use Lci\BoilerBoxBundle\Form\Type\EquipementBATicketType;
use Lci\BoilerBoxBundle\Form\Type\FichierType;
use Lci\BoilerBoxBundle\Form\Type\SiteBAType;
use Lci\BoilerBoxBundle\Form\Type\TicketIncidentType;
use Lci\BoilerBoxBundle\Form\Type\TicketIncidentCommentairesType;
use Lci\BoilerBoxBundle\Form\Type\TicketIncidentModificationType;
use Lci\BoilerBoxBundle\Form\Type\TicketIncidentValidationType;
use Lci\BoilerBoxBundle\Form\Type\ValidationType;


use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementCommentairesType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModificationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementValidationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification1Type;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification2Type;

use Lci\BoilerBoxBundle\Entity\ObjRechercheBonsAttachement;
use Lci\BoilerBoxBundle\Form\Type\ObjRechercheBonsAttachementType;


use Symfony\Component\Form\FormError;

class TicketController extends Controller
{
    /**
     * Récupérer la véritable adresse IP d'un visiteur
     */
    function get_ip()
    {
        // IP si internet partagé
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } // IP derrière un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } // Sinon : IP normale
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }



    public function saisieAction(Request $request)
    {
		date_default_timezone_set('Europe/Paris');
		$numero_de_ticket						= null;
        $em 									= $this->getDoctrine()->getManager();
        $max_upload_size 						= ini_get('upload_max_filesize');
		$enregistrement_form_ticket 				= null;

		// Html du formulaire du ticket - permet de renvoyer les bonnes informations lors de la modification du site / des contacs d'un site
		$enregistrement_html_form_ticket		= null;
		$tab_des_id_equipements_selectionnes 	= array();

		// Lors de la validation du formulaire de création d'équipement : La mise à true permet de réafficher automatiquement le formulaire de création d'équipement pour voir l'erreur
		$echec_creation_equipement 				= false;
        $apiKey 								= $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $es_sitesBA 							= $em->getRepository('LciBoilerBoxBundle:SiteBA')->findAll();

        // Création de l'entité du ticket + Récupération de l'utilisateur courant pour en définir l'initiateur
        $e_ticket 		= new BonsAttachement();
        $e_ticket->setDateInitialisation(new \Datetime());
        $e_ticket->setDateDebutIntervention(new \Datetime());


		$e_user_courant = $this->get('security.token_storage')->getToken()->getUser();
        $e_ticket->setUserInitiateur($e_user_courant);

		// Création du formulaire de ticket 
        $f_ticket = $this->createForm(TicketIncidentType::class, $e_ticket);

		// Création du formulaire des SitesBA
        $e_siteBA 			= new SiteBA();
        $e_siteBA_update 	= null;
        $id_last_site	 	= null;
        $f_siteBA = $this->createForm(SiteBAType::class, $e_siteBA, array(
            'action' => $this->generateUrl('saisie_ticket'),
            'method' => 'POST'
        ));

		// Recherche de l'ensemble des équipements
		$es_equipements = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->findAll();		

        // Si un formulaire : création de ticket ou création / modification de site, de ticket a été soumis (retour de type POST)
        if ($request->getMethod() == 'POST') 
		{
            // Sauvegarde des id des équipements selectionnés pour les re sélectionner
            foreach($_POST as $key => $variable_post)
            {
                $pattern_equipement = '/equipement_/';
                if (preg_match($pattern_equipement, $key))
                {
                    $tmp_e_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($variable_post);
                    array_push($tab_des_id_equipements_selectionnes, $tmp_e_equipement->getId());
                }
            }

			// Envoyé lors de l'envoi du formulaire de site
			if (isset($_POST['save_form_bon']))
			{
				$enregistrement_html_form_ticket = $_POST['save_form_bon'];
			}

            // Si le formulaire de création de ticket a été soumis (retour de type POST)
			$f_ticket->handleRequest($request);

			// Si on ne doit pas sauvegarder (dans le cas ou le formulaire est envoyé pour raffraichir la liste de équipements)
			// On instancie la variable $id_last_site pour réafficher le ticket en cours de création (et reselectionner le site choisi dans le ticket pour déclancher son trigger change()
			if ($f_ticket->isSubmitted())
			{
				if ($_POST['enregistrement'] == 'non')
				{
					$enregistrement_form_ticket = false;
					if ($e_ticket->getSite())
					{
						$id_last_site = $e_ticket->getSite()->getId();
					}
				} else {
					$enregistrement_form_ticket = true;
				}
			}

            if ($f_ticket->isValid()) 
			{
                // On persist l'entité "Ticket" et par cascade l'entité" "Fichier"
                // On enregistre le tout en base
                try {
                    // Sauvegarde du ticket
					// Gestion des équipements
                	foreach($_POST as $key => $variable_post)
                	{
                	    $pattern_equipement = '/equipement_/';
                	    if (preg_match($pattern_equipement, $key))
                	    {
                	        $tmp_e_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($variable_post);
                	        $tmp_e_equipement->setSiteBA($e_ticket->getSite());
							// L'assignation se fait du coté du ticket : Il gère l'assignation inverse
							$e_ticket->addEquipementBATicket($tmp_e_equipement);
                	    }
                	}
					if($enregistrement_form_ticket === true)
					{
						// On défini le type pour distinguer ticket d'un bon
						$e_ticket->setType('ticket');
						$e_ticket->setTypeIntervention('Incident');

						// Le numero du ticket s'incremente automatiquement
						// 	recherche du dernier numéro de ticket en base
						// 	incrémentation du numéro trouvé
						$numero_de_ticket = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->myFindLastNumeroBA('ticket');
						$numero_de_ticket ++;
						$e_ticket->setNumeroBA($numero_de_ticket);

						// Enregistrement du ticket
                    	$em->persist($e_ticket);
                    	$em->flush();

						// Si tout c'est bien passé pour l'enregistrement du nouveau ticket on réinitialise le tableau de id des équipements
						$tab_des_id_equipements_selectionnes    = array();

                    	// Envoi d'un mail à l'intervenant
                    	$service_mailling 		= $this->get('lci_boilerbox.mailing');
                    	$emetteur 				= $e_ticket->getUserInitiateur()->getEmail();
                    	$destinataire 			= $e_ticket->getUser()->getEmail();
                    	$sujet 					= "Affectation d'un nouveau ticket d'incident";
                    	$tab_message 			= array();
                    	$tab_message['titre'] 	= "Un nouveau ticket d'incident vous est affectée";
                    	$tab_message['site'] 	= $e_ticket->getSite()->getIntitule() . " ( " . $e_ticket->getNumeroAffaire() . " ) ";
                    	$messages_contact 		= "";
                    	if (($e_ticket->getNomDuContact() != null) || ($e_ticket->getEmailContactClient() != null)) {
                    	    if ($e_ticket->getNomDuContact() != null) {
                    	        $messages_contact = "Votre contact sur site est : " . $e_ticket->getNomDuContact();
                    	        if ($e_ticket->getEmailContactClient() != null) {
                    	            $messages_contact .= " ( " . $e_ticket->getEmailContactClient() . " ) ";
                    	        }
                    	    } else if ($e_ticket->getEmailContactClient() != null) {
                    	        $messages_contact .= "Le mail du contact sur site est : " . $e_ticket->getEmailContactClient();
                    	    }
                    	} else {
                    	    $messages_contact = "Aucun contact sur site n'a été renseigné";
                    	}
                    	$tab_message['contact'] = $messages_contact;
                    	$liste_fichiers = "";
                    	foreach ($e_ticket->getFichiersPdf() as $fichier) {
                    	    $liste_fichiers .= $fichier->getAlt() . ' ';
                    	}
                    	if ($liste_fichiers != "") {
                    	    $tab_message['fichiers'] = "Vous pouvez retrouver les fichiers suivants dans le ticket d'incident sur le site boilerbox.fr : $liste_fichiers";
                    	} else {
                    	    $tab_message['fichiers'] = "Aucun fichier n'a été importé pour ce ticket";
                    	}
						// Envoi du mail à l'intervenant uniquement
                    	$service_mailling->sendMail($emetteur, $destinataire, $sujet, $tab_message);
						// ICI DEV : Faire unenvoi de mail au client également
					}
                } catch (\Exception $e) {
					echo $e->getMessage();
					return new Response();
                }


                if($enregistrement_form_ticket === true)
                {
                	// On renvoye à la page d'ajout d'un nouveau ticket avec envoi du message de confirmation d'enregsitrement du ticket
                	$request->getSession()->getFlashBag()->add('info', "Ticket d'incident $numero_de_ticket enregistré.");

                	// Création d'un nouveau formulaire de création de ticket
                	$e_ticket = new BonsAttachement();
                	$e_ticket->setUserInitiateur($e_user_courant);
                	$f_ticket = $this->createForm(TicketIncidentType::class, $e_ticket);
				} else {
					// Si l'enregistrement du ticket ne doit pas être faite on enregistre l'entité en session 
					$_SESSION['e_ticket'] = $e_ticket;
				}

                return $this->render('LciBoilerBoxBundle:Tickets:form_saisie_ticket.html.twig', array(
					'page'						=> 'ticket',
                    'form' 						=> $f_ticket->createView(),
                    'form_site' 				=> $f_siteBA->createView(),
                    'max_upload_size' 			=> $max_upload_size,
                    'es_sitesBA' 				=> $es_sitesBA,
                    'apiKey' 					=> $apiKey,
					'id_last_site'              => $id_last_site,
                	'es_equipements'    		=> $es_equipements,
                    'echec_creation_equipement' => $echec_creation_equipement,
					'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
					'enregistrement_html_form_bon'	=> $enregistrement_html_form_ticket
                ));
            } else {
                // Soit le formulaire de création d'un ticket n'est pas valide soit c'est un autre formulaire qui est envoyé, Soit c'est un rappel de la page aprés suppression d'equipement ($enregistrement_form_ticket === false)
                if (($f_ticket->isSubmitted()) || ($enregistrement_form_ticket === false))
				{
					// On récupère les champs des nouveaux objet crées (contact/ équipement)
					$nouveau_type_creation =  $f_ticket->get('typeNouveau')->getData();
					$nouvel_id_creation = $f_ticket->get('idNouveau')->getData();
					$site_nouvelle_creation = $f_ticket->get('siteNouveau')->getData();

					// Si le ticket devait être enregistré on affiche les erreurs du formulaire
					if($enregistrement_form_ticket === true)
					{
						// Le formulaire de nouveau ticket est donc soumis mais n'est pas valide
						$obj_erreurs = $f_ticket->getErrors(true, false);
						$message_erreur = '';
						foreach($obj_erreurs as $obj => $error)
						{
							$message_erreur .= $error.' - ';
						}
                    	$request->getSession()->getFlashBag()->add('info', $message_erreur);
					}

					// On recrée le formulaire avec les données récues
					$f_ticket= $this->createForm(TicketIncidentType::class, $e_ticket);
					$f_ticket->get('typeNouveau')->setData($nouveau_type_creation);
					$f_ticket->get('idNouveau')->setData($nouvel_id_creation);
					$f_ticket->get('siteNouveau')->setData($site_nouvelle_creation);

					// On récupère l'id du site pour le réafficher
					if ($e_ticket->getSite())
					{
						if ($e_ticket->getSite()->getId())
						{
							$id_last_site = $e_ticket->getSite()->getId();
						}
					}
                } else {
                    	// Le formulaire de nouveau site ou de modification de site est passé
                    	// Si un identifiant de site est passé : C'est le formulaire de modification de site qui est passé => Mise à jour de l'entité
                    	// Pour cela on enregistre les informations du formulaire dans une nouvelle entité et on met a jour l'entité à modifier
						
                    	if (isset($_POST['id_site_ba'])) 
						{
                    	    if ($_POST['id_site_ba'] != "") 
							{
                    	        $e_siteBA_update = new SiteBA();
                    	        $f_siteBA = $this->createForm(SiteBAType::class, $e_siteBA_update, array(
                    	            'action' => $this->generateUrl('saisie_ticket'),
                    	            'method' => 'POST'
                    	        ));
								// Enregistrement de l'id du site modifié pour le ré selectionner dans la page HTML après modification
                    	        $id_last_site = $_POST['id_site_ba'];
                    	    }
                    	}

                    	// J'instancie l'entité $e_siteBA_update avec les valeurs du formulaire de site reçues
                    	// Retourne une erreur (non bloquante car pas de test de validation effectué : data.intitule 	Cette valeur est déjà utilisée.
                    	$f_siteBA->handleRequest($request);

                    	// !! On ne test pas que le formulaire soit correct avec un form->isValid donc il nous faut intercepter l'exception DBAL en cas d'erreur
                    	if ($e_siteBA_update != null) 
						{
                    	    $e_siteBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($_POST['id_site_ba']);
                    	    // Seule la modification du nom du site n'est pas permise
                    	    $e_siteBA->setIntitule($e_siteBA_update->getIntitule());
                    	    $e_siteBA->setAdresse($e_siteBA_update->getAdresse());
                    	    $e_siteBA->setLienGoogle($this->transformeUrl($e_siteBA_update->getLienGoogle()));
                    	    $e_siteBA->setInformationsClient($e_siteBA_update->getInformationsClient());
                    	    foreach ($e_siteBA_update->getFichiersJoint() as $ent_fichier) 
							{
								$ent_fichier->setUserInitiateur($this->getUser()->getLabel());
                    	        $e_siteBA->addFichiersJoint($ent_fichier);
                    	    }
                    	    foreach ($e_siteBA_update->getContacts() as $ent_contact) {
                    	        $e_siteBA->addContact($ent_contact);
                    	    }
                    	    // Maintenant que j'ai modifié l'entité siteBA je détach l'entité update pour ne pas que doctrine tente de l'enregistrer en base : Sinon erreur car doublon avec l'entité siteBA
                    	    $em->detach($e_siteBA_update);
                    	} else {
							foreach ($e_siteBA->getFichiersJoint() as $e_fichier)
                            {
                                $e_fichier->setUserInitiateur($this->getUser()->getLabel());
                                $e_siteBA->addFichiersJoint($e_fichier);
                            }
                    	    $e_siteBA->setLienGoogle($this->transformeUrl($e_siteBA->getLienGoogle()));
                    	}
                    	// J'effectue moi même la validation des paramètres
                    	$retourTest = $this->testEntiteSiteBA($e_siteBA);
                    	if ($retourTest === 0) 
						{
                    	    // Si tous les paramètres sont corrects je met à jour l'entité en base de données
                    	    // L'entité est persisté pour gerer le cas ou c'est un nouvelle entité
                    	    $em->persist($e_siteBA);
                    	    try {
								// Enregistrement du siteBA en base
                    	        $em->flush();
								// Mise en commentaire pour réafficher le site nouvellement créé
								$id_last_site = $e_siteBA->getId();
                    	        // Si il y a une demande d'ajout de sauvegarde des contacts
                    	        if (isset($_POST['site_ba']['contacts'])) 
								{
                    	            foreach ($_POST['site_ba']['contacts'] as $tab_contact) 
									{
                    	                $ent_contact = new Contact();
                    	                $ent_contact->setNom(strtoupper($tab_contact['nom']));
                                        $ent_contact->setPrenom($service_utilitaire->capitalizeFirstLetter($tab_contact['prenom']));
                    	                $ent_contact->setTelephone($tab_contact['telephone']);
                    	                $ent_contact->setMail($tab_contact['mail']);
                    	                $ent_contact->setFonction($tab_contact['fonction']);
                    	                $ent_contact->setDateMaj(new \Datetime());
                    	                $em->persist($ent_contact);
                    	                $e_siteBA->addContact($ent_contact);
                    	            }
                    	            $em->flush();
                    	        }
                    	        $request->getSession()->getFlashBag()->add('info', 'Site ' . $e_siteBA->getIntitule() . ' enregistré');
                    	    } catch (\Doctrine\DBAL\DBALException $e) {
                    	        $request->getSession()->getFlashBag()->add('info', "Erreur d'importation");
                    	        $request->getSession()->getFlashBag()->add('info', $e->getMessage());
                    	    }
                    	    // Création d'un nouveau formulaire de création de site
							// Mise en commentaire pour réafficher le site nouvellement créé
                    	    //$e_siteBA = new SiteBA();
                    	    $f_siteBA = $this->createForm(SiteBAType::class, $e_siteBA, array(
                    	        'action' => $this->generateUrl('saisie_ticket'),
                    	        'method' => 'POST'
                    	    ));
                    	} else {
							// on affiche l'erreur qui empeche la validation du formulaire de ticket si la demande d'enregistrement du formulaire est faite
							if ($enregistrement_form_ticket === true)
							{
                    	    	$request->getSession()->getFlashBag()->add('info', $retourTest);
							}
                    	}
                }
                // On renvoi sur la page en indiquant le nom du site pour réaffichage de la page précédente
                return $this->render('LciBoilerBoxBundle:Tickets:form_saisie_ticket.html.twig', array(
                    'page'                      => 'ticket',
                    'form' 						=> $f_ticket->createView(),
                    'form_site' 				=> $f_siteBA->createView(),
                    'max_upload_size' 			=> $max_upload_size,
                    'es_sitesBA' 				=> $es_sitesBA,
                    'apiKey' 					=> $apiKey,
                    'id_last_site' 				=> $id_last_site,
                	'es_equipements'    		=> $es_equipements,
                    'echec_creation_equipement' => $echec_creation_equipement,
                    'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
                    'enregistrement_html_form_bon'  => $enregistrement_html_form_ticket
                ));
            }
        } else {
            // Si le formulaire n'a pas encore été affiché
            return $this->render('LciBoilerBoxBundle:Tickets:form_saisie_ticket.html.twig', array(
                'page'                      => 'ticket',
                'form' 						=> $f_ticket->createView(),
                'form_site' 				=> $f_siteBA->createView(),
                'max_upload_size' 			=> $max_upload_size,
                'es_sitesBA' 				=> $es_sitesBA,
                'apiKey' 					=> $apiKey,
				'id_last_site'              => $id_last_site,
				'es_equipements'			=> $es_equipements,
				'echec_creation_equipement' => $echec_creation_equipement,
                'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
                'enregistrement_html_form_bon'  => $enregistrement_html_form_ticket
            ));
        }
    }

    // Fonction qui récupère l'url retournée par google map et extrait la partie recherche
    private function transformeUrl($lienGoogle)
    {

        $pattern = '$^https?://www.google.com/maps/place/(.+?)/$';
        $pattern2 = '$^https?://place(.+)$';
        $patternLatLng = '$^latLng\((.+),(.+)\)$';
        if (preg_match($pattern, $lienGoogle, $matches)) {
            return 'https://www.google.com/maps/embed/v1/place?key=APIKEY&q=' . $matches[1] . '&zoom=ZOOMAPI&maptype=satellite';
        }

        if (preg_match($pattern2, $lienGoogle, $matches)) {
            return 'https://www.google.com/maps/embed/v1/place?key=APIKEY&q=place_id:' . $matches[1] . '&zoom=ZOOMAPI&maptype=satellite';
        }

        if (preg_match($patternLatLng, $lienGoogle, $matches)) {
            //return 'https://www.google.com/maps/embed/v1/view?key=APIKEY&center='.trim($matches[1]).','.trim($matches[2]).'&zoom=ZOOMAPI&maptype=satellite';
            return $lienGoogle;
        }

        return null;
    }

    /* Seul l'initiateur du ticket ou l'intervenant peuvent modifier un ticket */
    public function modifierUnBonAction($idBon, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $current_user = $this->get('security.token_storage')->getToken()->getUser();

        if (!($current_user == $entity_bon->getUser()) && !($current_user == $entity_bon->getUserInitiateur()) && !($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA'))) {
            $request->getSession()->getFlashBag()->add('info', "Seul l'initiateur ou l'intervenant peuvent modifier le bon");
            return $this->redirectToRoute('lci_bons_attachements');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA')) {
            $form = $this->createForm(BonsAttachementModification1Type::class, $entity_bon);
        } else {
            $form = $this->createForm(BonsAttachementModification2Type::class, $entity_bon);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Bon ' . $entity_bon->getNumeroBA() . ' modifié.');
                $_POST['id_bon'] = $entity_bon->getId();
                // Retour vers la visualisation du ticket
                return $this->afficherUnBonAction($request);
            } else {
                $request->getSession()->getFlashBag()->add('info', $form->getErrors(true));
            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:form_modification_bons.html.twig', array(
            'form' => $form->createView(),
            'idBon' => $entity_bon->getId()
        ));
    }


    // Affichage d'un ticket pour la page d'affichage de la liste des fichiers du ticket
    // Dans la page du ticket on affiche également le forumlaire de validation du ticket
    public function afficherUnTicketAction(Request $request)
    {
        $em 					= $this->getDoctrine()->getManager();
        $form_message_erreur 	= "";
        $max_upload_size 		= ini_get('upload_max_filesize');

        // Si la requete est de type GET : Un rafraichissement de page est demandé. Récupération des anciennes informations
        if ($request->getMethod() == 'POST') 
		{
            if (isset($_POST['id_ticket'])) 
			{
                $id_ticket = $_POST['id_ticket'];
                $request->getSession()->set('idTicketIncident', $id_ticket);
            } else {
                // Si un fichier trop volumineux est envoyé :  Information APACHE : PHP.ini
                $form_message_erreur = 'Taille maximum du fichier autorisé : ' . ini_get('upload_max_filesize') . ' - ' . 'Taille maximum tous fichier compris : ' . ini_get('post_max_size');
                $id_ticket= $request->getSession()->get('idTicketIncident', null);
            }
        } else {
            $id_ticket= $request->getSession()->get('idTicketIncident', null);
        }
        if (!isset($id_ticket)) {
            // Si la page est appelée sans passer par boilerbox
            return 'Page non disponible';
        }
        $e_ticket 		= $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_ticket);

        $f_validation 		= $this->createForm(TicketIncidentValidationType::class, $e_ticket);
        $f_ba_commentaires 	= $this->createForm(TicketIncidentCommentairesType::class, $e_ticket);
        $f_ba_modification	= $this->createForm(TicketIncidentModificationType::class, $e_ticket);

        // Gestion de l'ajout de fichiers à un bon
        if ($request->getMethod() == 'POST') 
		{
            $f_ba_modification->handleRequest($request);
            if ($f_ba_modification->isSubmitted()) 
			{
                if ($f_ba_modification->isValid()) 
				{
					$tab_des_equipements_modif = array();
                    foreach($_POST as $key => $variable_post)
                    {
                        $pattern_equipement = '/equipement_/';
                        if (preg_match($pattern_equipement, $key))
                        {
							array_push($tab_des_equipements_modif, $variable_post);
							// Si l'equipement n'est pas déjà affecté au bon , on l'ajoute
                            $e_tmp_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($variable_post);
							if (!$e_ticket->getEquipementBATicket()->contains($e_tmp_equipement))
							{
								$e_tmp_equipement->setSiteBA($e_ticket->getSite());
								$e_ticket->addEquipementBATicket($e_tmp_equipement);
							}
                        }
                    }

                    foreach ($e_ticket->getFichiersPdf() as $fichier) 
					{
                        if ($fichier->getBonAttachement() == null) 
						{
                            $fichier->setBonAttachement($e_ticket);
                            $em->persist($fichier);
                            $fichier->setAlt($fichier->getAlt() . " ( " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i') . " )");
                            if ($fichier->getUrl() == null) {
                                $e_ticket->removeFichiersPdf($fichier);
                                $em->detach($fichier);
                            }
                        }
                    }

                    $em->flush();
					// On récupères tous les équipements associés au bon et on vérifie qu'ils correspondent à ceux passés dans le formulaire
					$tab_des_equipements_presents = array();
					foreach($e_ticket->getEquipementBATicket() as $e_equipement_ba_ticket_modif)
					{
						array_push($tab_des_equipements_presents, $e_equipement_ba_ticket_modif->getId());
					}

					foreach($tab_des_equipements_presents as $key => $id_equipement_present)
					{
						if (in_array($id_equipement_present, $tab_des_equipements_modif) == false)
						{
							$e_tmp_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($id_equipement_present);
							// Gère la relation inverse
							$e_ticket->removeEquipementBATicket($e_tmp_equipement);
						}
					}
					$em->flush();

					// On recree le formulaire des bons avec la prise en compte des modification sur les équipements
					$f_ba_modification  = $this->createForm(BonsAttachementModificationType::class, $e_ticket);
                } else {
                    $f_ba_modification->addError(new FormError($form_message_erreur));
                }
            }
        }


        return $this->render('LciBoilerBoxBundle:Tickets:form_visu_un_ticket.html.twig', array(
            'entity_bon' 				=> $e_ticket,
            'form_validation' 			=> $f_validation->createView(),
            'form_modification' 		=> $f_ba_modification->createView(),
            'form_ajout_commentaires' 	=> $f_ba_commentaires->createView(),
            'max_upload_size' 			=> $max_upload_size,
			'commentaires'				=> $e_ticket->getCommentaires(),
			'es_sitesBA'				=> $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->findAll(),
			'latitude'					=> $this->getLatLng('latitude', $e_ticket->getSite()->getLienGoogle()),
			'longitude'					=> $this->getLatLng('longitude', $e_ticket->getSite()->getLienGoogle()),
			'apiKey'                    => $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur(),
			'zoomApi'           		=> $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur()
        ));
    }

    public function ajoutCommentairesAction($idBon, Request $request)
    {
        $e_user_courant = $this->get('security.token_storage')->getToken()->getUser();
        $em 			= $this->getDoctrine()->getManager();
        $e_bon 			= $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $commentaires 	= $e_bon->getCommentaires();
        $form 			= $this->createForm(BonsAttachementCommentairesType::class, $e_bon);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $nouveaux_commentaires = ucfirst($e_bon->getCommentaires());
                $e_bon->setCommentaires($commentaires . "<div class='bons_commentaires_titre'>Par " . $e_user_courant->getLabel() . " le " . date('d/m/Y H:i:s') . "</div><div class='bons_commentaires_text'>" . $nouveaux_commentaires . "</div>");
                $em->flush();
            } else {
                echo $form->getErrors();
                throw new \Exception();
            }
        }
        return $this->redirectToRoute('lci_bons_afficher_unbon');
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

                // Sauvegarde de l'objet recherche de ticket pour réaffichage des données lors de la prochaine requête
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

    private function testEntiteSiteBA($ent_siteBA)
    {
        $intitule = $ent_siteBA->getIntitule();
        if (($intitule == null) || ($intitule == "")) {
            return "Veuillez indiquer un nom de site";
        }
        $adresse = $ent_siteBA->getAdresse();
        if (($adresse == null) || ($adresse == "")) {
            return "Veuillez indiquer une adresse pour le site";
        }

        if (isset($_POST['site_ba']['contacts'])) 
		{
        	foreach ($_POST['site_ba']['contacts'] as $tab_contact) 
			{
				if ($tab_contact['nom'] == '')
				{ 
					return "Veuillez indiquer un nom au contact";
				}
				if (($tab_contact['telephone'] == '') && ($tab_contact['mail'] == ''))
				{
					return "Veuillez indiquer un email ou un téléphone au contact";
				}
			}
		}
        /*
        $lien = $ent_siteBA->getLienGoogle();
        if (($lien == null) || ($lien == "")) {
            return "Veuillez indiquer un lien google map";
        }
        */
        return 0;
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
