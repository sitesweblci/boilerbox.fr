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

class BonsController extends Controller
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

    public function indexAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA')) {
            return $this->render('LciBoilerBoxBundle:Bons:index.html.twig');
        } else {
            return $this->visualiserAction(null, $request);
        }
    }

    public function saisieAction(Request $request)
    {
		date_default_timezone_set('Europe/Paris');

        $em 									= $this->getDoctrine()->getManager();
        $max_upload_size 						= ini_get('upload_max_filesize');
		$enregistrement_form_bon 				= null;

		// Html du formulaire du bon - permet de renvoyer les bonnes informations lors de la modification du site / des contacs d'un site
		$enregistrement_html_form_bon			= null;
		$tab_des_id_equipements_selectionnes 	= array();

		// Lors de la validation du formulaire de création d'équipement : La mise à true permet de réafficher automatiquement le formulaire de création d'équipement pour voir l'erreur
		$echec_creation_equipement 				= false;
        $apiKey 								= $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $es_sitesBA 							= $em->getRepository('LciBoilerBoxBundle:SiteBA')->findAll();

        // Création d'un formulaire de bon d'attachement +  Récupération de l'utilisateur courant pour définir l'initiateur d'un nouveau bon
        $e_bons_attachement = new BonsAttachement();
		$e_user_courant 						= $this->get('security.token_storage')->getToken()->getUser();
        $e_bons_attachement->setUserInitiateur($e_user_courant);

        // Création du formulaire de bon
		$f_bons_attachement = $this->createForm(BonsAttachementType::class, $e_bons_attachement);

		//Service utilitaire pour appel des fonctions capitalise ...
		$service_utilitaire						= $this->container->get('lci_boilerbox.utilitaires');

		// Création du formulaire des SitesBA
        $e_siteBA 			= new SiteBA();
        $e_siteBA_update 	= null;
        $id_last_site	 	= null;
        $f_siteBA = $this->createForm(SiteBAType::class, $e_siteBA, array(
            'action' => $this->generateUrl('lci_bons_saisie'),
            'method' => 'POST'
        ));

		// Recherche de l'ensemble des équipements
		$es_equipements = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->findAll();		

        // Si un formulaire : création de bon ou création / modification de site, de bon a été soumis (retour de type POST)
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
				$enregistrement_html_form_bon = $_POST['save_form_bon'];
			}

            // Si le formulaire de création de bon a été soumis (retour de type POST)
			$f_bons_attachement->handleRequest($request);

			// Si on ne doit pas sauvegarder (dans le cas ou le formulaire est envoyé pour raffraichir la liste de équipements)
			// On instancie la variable $id_last_site pour réafficher le bon en cours de création (et reselectionner le site choisi dans le bon pour déclancher son trigger change()
			if ($f_bons_attachement->isSubmitted())
			{
				if ($_POST['enregistrement'] == 'non')
				{
					$enregistrement_form_bon = false;
					if ($e_bons_attachement->getSite())
					{
						$id_last_site = $e_bons_attachement->getSite()->getId();
					}
				} else {
					$enregistrement_form_bon = true;
				}
			}

            if ($f_bons_attachement->isValid()) 
			{
                // On persist l'entité "Bon d'attachement" et par cascade l'entité" "Fichier"
                // On enregistre le tout en base
                try {
                    // Sauvegarde du bon
					// Gestion des équipements
                	foreach($_POST as $key => $variable_post)
                	{
                	    $pattern_equipement = '/equipement_/';
                	    if (preg_match($pattern_equipement, $key))
                	    {
                	        $tmp_e_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($variable_post);
                	        $tmp_e_equipement->setSiteBA($e_bons_attachement->getSite());
							// L'assignation se fait du coté du bon : Il gère l'assignation inverse
							$e_bons_attachement->addEquipementBATicket($tmp_e_equipement);
                	    }
                	}
					if($enregistrement_form_bon === true)
					{
						// On défini le type pour distinguer bon de ticket
						$e_bons_attachement->setType('bon');

						// Le numero BA est remplit par le techniciens

						// Enregistrement du bon
                    	$em->persist($e_bons_attachement);
                    	$em->flush();

						// Si tout c'est bien passé pour l'enregistrement du nouveau bon on réinitialise le tableau de id des équipements
						$tab_des_id_equipements_selectionnes    = array();

                    	// Envoi d'un mail à l'intervenant si il est définit
						if ($e_bons_attachement->getUser())
						{
							$this->envoiMailIntervention($e_bons_attachement);
						}
					}
                } catch (\Exception $e) {
					echo $e->getMessage();
					return new Response();
                }


                if($enregistrement_form_bon === true)
                {
                	// On renvoye à la page d'ajout d'un nouveau bon d'attachement avec envoi du message de confirmation d'enregsitrement du bon
                	$request->getSession()->getFlashBag()->add('info', 'Bon enregistré.');

                	// Création d'un nouveau formulaire de création de bon d'attachement
                	$e_bons_attachement = new BonsAttachement();
                	$e_bons_attachement->setUserInitiateur($e_user_courant);
                	$f_bons_attachement = $this->createForm(BonsAttachementType::class, $e_bons_attachement);
				} else {
					// Si l'enregistrement du bon ne doit pas être faite on enregistre l'entité en session 
					$_SESSION['e_bon'] = $e_bons_attachement;
				}

                return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
					'page'						=> 'bon',
                    'form' 						=> $f_bons_attachement->createView(),
                    'form_site' 				=> $f_siteBA->createView(),
                    'max_upload_size' 			=> $max_upload_size,
                    'es_sitesBA' 				=> $es_sitesBA,
                    'apiKey' 					=> $apiKey,
					'id_last_site'              => $id_last_site,
                	'es_equipements'    		=> $es_equipements,
                    'echec_creation_equipement' => $echec_creation_equipement,
					'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
					'enregistrement_html_form_bon'	=> $enregistrement_html_form_bon
                ));
            } else {
                // Soit le formulaire de création d'un bon n'est pas valide soit c'est un autre formulaire qui est envoyé, Soit c'est un rappel de la page aprés suppression d'equipement ($enregistrement_form_bon === false)
                if (($f_bons_attachement->isSubmitted()) || ($enregistrement_form_bon === false))
				{
					// On récupère les champs des nouveaux objet crées (contact/ équipement)
					$nouveau_type_creation =  $f_bons_attachement->get('typeNouveau')->getData();
					$nouvel_id_creation = $f_bons_attachement->get('idNouveau')->getData();
					$site_nouvelle_creation = $f_bons_attachement->get('siteNouveau')->getData();

					// Si le bon devait être enregsitré on affiche les erreurs du formulaire
					if($enregistrement_form_bon === true)
					{
						// Le formulaire de nouveau bon est donc soumis mais n'est pas valide
						$obj_erreurs = $f_bons_attachement->getErrors(true, false);
						$message_erreur = '';
						foreach($obj_erreurs as $obj => $error)
						{
							$message_erreur .= $error.' - ';
						}
                    	$request->getSession()->getFlashBag()->add('info', $message_erreur);
					}

					// On recrée le formulaire avec les données récues
					$f_bons_attachement = $this->createForm(BonsAttachementType::class, $e_bons_attachement);
					$f_bons_attachement->get('typeNouveau')->setData($nouveau_type_creation);
					$f_bons_attachement->get('idNouveau')->setData($nouvel_id_creation);
					$f_bons_attachement->get('siteNouveau')->setData($site_nouvelle_creation);

					// On récupère l'id du site pour le réafficher
					if ($e_bons_attachement->getSite())
					{
						if ($e_bons_attachement->getSite()->getId())
						{
							$id_last_site = $e_bons_attachement->getSite()->getId();
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
                    	            'action' => $this->generateUrl('lci_bons_saisie'),
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
                    	$retourTest = $this->checkEntiteSiteBA($e_siteBA);
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
                    	        'action' => $this->generateUrl('lci_bons_saisie'),
                    	        'method' => 'POST'
                    	    ));
                    	} else {
							// on affiche l'erreur qui empeche la validation du formulaire de bon si la demande d'enregistrement du formulaire est faite
							if ($enregistrement_form_bon === true)
							{
                    	    	$request->getSession()->getFlashBag()->add('info', $retourTest);
							}
                    	}
                }
                // On renvoi sur la page en indiquant le nom du site pour réaffichage de la page précédente
                return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
					'page'						=> 'bon',
                    'form' 						=> $f_bons_attachement->createView(),
                    'form_site' 				=> $f_siteBA->createView(),
                    'max_upload_size' 			=> $max_upload_size,
                    'es_sitesBA' 				=> $es_sitesBA,
                    'apiKey' 					=> $apiKey,
                    'id_last_site' 				=> $id_last_site,
                	'es_equipements'    		=> $es_equipements,
                    'echec_creation_equipement' => $echec_creation_equipement,
                    'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
                    'enregistrement_html_form_bon'  => $enregistrement_html_form_bon
                ));
            }
        } else {
            // Si le formulaire n'a pas encore été affiché
            return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
				'page'						=> 'bon',
                'form' 						=> $f_bons_attachement->createView(),
                'form_site' 				=> $f_siteBA->createView(),
                'max_upload_size' 			=> $max_upload_size,
                'es_sitesBA' 				=> $es_sitesBA,
                'apiKey' 					=> $apiKey,
				'id_last_site'              => $id_last_site,
				'es_equipements'			=> $es_equipements,
				'echec_creation_equipement' => $echec_creation_equipement,
                'tab_des_id_equipements_selectionnes' => $tab_des_id_equipements_selectionnes,
                'enregistrement_html_form_bon'  => $enregistrement_html_form_bon
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

    /* Seul l'initiateur du bon ou l'intervenant peuvent modifier un bon */
	/* ICI DEV  SUPP ?  supprimer aussi la route 
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
                // Retour vers la visualisation du bon
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
	*/


    // Affichage d'un bon pour la page d'affichage de la liste des fichiers du bon
    // Dans la page du bon on affiche également le forumlaire de validation du bon
    public function afficherUnBonAction(Request $request)
    {
        $em 					= $this->getDoctrine()->getManager();
        $form_message_erreur 	= "";
        $max_upload_size 		= ini_get('upload_max_filesize');

        // Si la requete est de type GET : Un rafraichissement de page est demandé. Récupération des anciennes informations
        if ($request->getMethod() == 'POST') 
		{
            if (isset($_POST['id_bon'])) 
			{
                $id_bon = $_POST['id_bon'];
                $request->getSession()->set('idBonAttachement', $id_bon);
            } else {
                // Si un fichier trop volumineux est envoyé :  Information APACHE : PHP.ini
                $form_message_erreur = 'Taille maximum du fichier autorisé : ' . ini_get('upload_max_filesize') . ' - ' . 'Taille maximum tous fichier compris : ' . ini_get('post_max_size');
                $id_bon = $request->getSession()->get('idBonAttachement', null);
            }
        } else {
            $id_bon = $request->getSession()->get('idBonAttachement', null);
        }
        if (!isset($id_bon)) {
            // Si la page est appelée sans passer par boilerbox
            return 'Page non disponible';
        }
        $e_bons_attachement 		= $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

		// On récupère l'intervenant avec modification du bon pour l'envoi de mail en cas de modification d'intervenant
		$e_tmp_intervenant = $e_bons_attachement->getUser();


        $f_validation 		= $this->createForm(BonsAttachementValidationType::class, $e_bons_attachement);
        $f_ba_commentaires 	= $this->createForm(BonsAttachementCommentairesType::class, $e_bons_attachement);
        $f_ba_modification	= $this->createForm(BonsAttachementModificationType::class, $e_bons_attachement);

        // Gestion de l'ajout de fichiers à un bon
        if ($request->getMethod() == 'POST') 
		{
            $f_ba_modification->handleRequest($request);
            if ($f_ba_modification->isSubmitted()) 
			{
                if ($f_ba_modification->isValid()) 
				{
					if ($e_bons_attachement->getUser() != $e_tmp_intervenant)
					{
						if ($e_bons_attachement->getUser())
						{
							$this->envoiMailIntervention($e_bons_attachement);
						}
					}
					$tab_des_equipements_modif = array();
                    foreach($_POST as $key => $variable_post)
                    {
                        $pattern_equipement = '/equipement_/';
                        if (preg_match($pattern_equipement, $key))
                        {
							array_push($tab_des_equipements_modif, $variable_post);
							// Si l'equipement n'est pas déjà affecté au bon , on l'ajoute
                            $e_tmp_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($variable_post);
							if (!$e_bons_attachement->getEquipementBATicket()->contains($e_tmp_equipement))
							{
								$e_tmp_equipement->setSiteBA($e_bons_attachement->getSite());
								$e_bons_attachement->addEquipementBATicket($e_tmp_equipement);
							}
                        }
                    }

                    foreach ($e_bons_attachement->getFichiersPdf() as $fichier) 
					{
                        if ($fichier->getBonAttachement() == null) 
						{
                            $fichier->setBonAttachement($e_bons_attachement);
                            $em->persist($fichier);
                            $fichier->setAlt($fichier->getAlt() . " ( " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i') . " )");
                            if ($fichier->getUrl() == null) {
                                $e_bons_attachement->removeFichiersPdf($fichier);
                                $em->detach($fichier);
                            }
                        }
                    }

                    $em->flush();
					// On récupères tous les équipements associés au bon et on vérifie qu'ils correspondent à ceux passés dans le formulaire
					$tab_des_equipements_presents = array();
					foreach($e_bons_attachement->getEquipementBATicket() as $e_equipement_ba_ticket_modif)
					{
						array_push($tab_des_equipements_presents, $e_equipement_ba_ticket_modif->getId());
					}

					foreach($tab_des_equipements_presents as $key => $id_equipement_present)
					{
						if (in_array($id_equipement_present, $tab_des_equipements_modif) == false)
						{
							$e_tmp_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($id_equipement_present);
							// Gère la relation inverse
							$e_bons_attachement->removeEquipementBATicket($e_tmp_equipement);
						}
					}
					$em->flush();

					// On recrée le formulaire des bons avec la prise en compte des modification sur les équipements
					$f_ba_modification  = $this->createForm(BonsAttachementModificationType::class, $e_bons_attachement);
                } else {
					if ($form_message_erreur != '')
					{
                    	$f_ba_modification->addError(new FormError($form_message_erreur));
					} else {
                    	$f_ba_modification->addError(new FormError($f_ba_modification->getErrors(true)));
					}
                }
            }
        }


        return $this->render('LciBoilerBoxBundle:Bons:form_visu_un_bon.html.twig', array(
			'page'						=> 'bon',
            'entity_bon' 				=> $e_bons_attachement,
            'form_validation' 			=> $f_validation->createView(),
            'form_modification' 		=> $f_ba_modification->createView(),
            'form_ajout_commentaires' 	=> $f_ba_commentaires->createView(),
            'max_upload_size' 			=> $max_upload_size,
			'commentaires'				=> $e_bons_attachement->getCommentaires(),
			'es_sitesBA'				=> $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->findAll(),
			'latitude'					=> $this->getLatLng('latitude', $e_bons_attachement->getSite()->getLienGoogle()),
			'longitude'					=> $this->getLatLng('longitude', $e_bons_attachement->getSite()->getLienGoogle()),
			'apiKey'                    => $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur(),
			'zoomApi'           		=> $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur()
        ));
    }


	private function envoiMailIntervention($e_bons_attachement)
	{
        $service_mailling       = $this->get('lci_boilerbox.mailing');
        $emetteur               = $e_bons_attachement->getUserInitiateur()->getEmail();
        $destinataire           = $e_bons_attachement->getUser()->getEmail();
        $sujet                  = "Affectation d'un nouveau bon d'attachement";
        $tab_message            = array();
        $tab_message['titre']   = "Une nouvelle intervention vous est affectée";
        $tab_message['site']    = $e_bons_attachement->getSite()->getIntitule() . " ( " . $e_bons_attachement->getNumeroAffaire() . " ) ";
        $messages_contact       = "";
        if (($e_bons_attachement->getNomDuContact() != null) || ($e_bons_attachement->getEmailContactClient() != null)) {
        	if ($e_bons_attachement->getNomDuContact() != null) {
        		$messages_contact = "Votre contact sur site est : " . $e_bons_attachement->getNomDuContact();
        		if ($e_bons_attachement->getEmailContactClient() != null) {
        			$messages_contact .= " ( " . $e_bons_attachement->getEmailContactClient() . " ) ";
        		}
        	} else if ($e_bons_attachement->getEmailContactClient() != null) {
        		$messages_contact .= "Le mail du contact sur site est : " . $e_bons_attachement->getEmailContactClient();
        	}
        } else {
        	$messages_contact = "Aucun contact sur site n'a été renseigné";
        }
        $tab_message['contact'] = $messages_contact;
        $liste_fichiers = "";
        foreach ($e_bons_attachement->getFichiersPdf() as $fichier) {
        	$liste_fichiers .= $fichier->getAlt() . ' ';
        }
        if ($liste_fichiers != "") {
        	$tab_message['fichiers'] = "Vous pouvez retrouver les fichiers suivants dans le bon d'attachement sur le site boilerbox.fr : $liste_fichiers";
        } else {
        	$tab_message['fichiers'] = "Aucun fichier n'a été importé pour ce bon";
        }
        // Envoi du mail à l'intervenant uniquement
        $service_mailling->sendMail($emetteur, $destinataire, $sujet, $tab_message);
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

    private function checkEntiteSiteBA($ent_siteBA)
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

}
